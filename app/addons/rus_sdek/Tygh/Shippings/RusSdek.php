<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Shippings;

use Tygh\Http;
use Tygh\Registry;

class RusSdek
{
    public static $url;

    public static function arraySimpleXml($name, $data, $type = 'simple')
    {
        $xml = '<'.$name.' ';
        foreach ($data as $key => $value) {
            if (isset($value)) {
                $value = fn_html_escape($value);
                $xml .= $key .'="' . $value .'" ';
            }
        }

        if ($type == 'open') {
            $xml .= '>';
        } else {
            $xml .= '/>';
        }

        return $xml;
    }

    public static function resultXml($response)
    {
        $result = array(
            'error' => false,
            'msg' => false,
            'number' => false,
        );

        $xml_result = simplexml_load_string($response);

        if (empty($xml_result)) {
            $result['error'] = true;

            return $result;
        }
        $attribut = $xml_result->children()->getName();

        $_result = json_decode(json_encode((array) $xml_result), true);

        foreach ($_result[$attribut] as $k) {
            $data = !empty($k['@attributes']) ? $k['@attributes'] : $k;

            if (!empty($data['Msg'])) {
                if (!empty($data['ErrorCode'])) {
                    $result['msg'] = $data['Msg'];
                    fn_set_notification('E', __('notice'), $data['Msg']);
                    if ($data['ErrorCode'] == 'ERR_ORDER_NOTFIND' || $data['ErrorCode'] == 'ERR_ORDER_DUBL_EXISTS') {
                        $result['error'] = false;
                    } else {
                        $result['error'] = true;
                    }
                } else {
                    $result['msg'] = $data['Msg'];
                    fn_set_notification('N', __('notice'), $data['Msg']);
                }

            } elseif (!empty($data['DispatchNumber'])) {
                $result['number'] = $data['DispatchNumber'];
            }
        }

        return $result;
    }

    public static function orderStatusXml($data_auth, $order_id = 0, $shipment_id = 0)
    {
        $data_status = array();
        if (!empty($order_id)) {
            unset($data_auth['Number']);
            unset($data_auth['OrderCount']);
        } else {
            $period_sdek = $data_auth['ChangePeriod'];
            unset($data_auth['ChangePeriod']);
        }

        $data_auth['ShowHistory'] = "1";

        $xml = '            ' . RusSdek::arraySimpleXml('StatusReport', $data_auth, 'open');

        if (!empty($order_id)) {
            $order_sdek = array (
                'Number' => $order_id . '_' . $shipment_id,
                'Date' => $data_auth['Date']
            );
            $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_sdek);
        } elseif (!empty($period_sdek)) {
            $xml .= '            ' . RusSdek::arraySimpleXml('ChangePeriod', $period_sdek);
        }

        $xml .= '            ' . '</StatusReport>';

        $response = RusSdek::SdekXmlRequest('https://integration.cdek.ru/status_report_h.php', $xml, $data_auth);

        $_result = json_decode(json_encode((array) simplexml_load_string($response)), true);

        if (!empty($_result['Order'])) {
            $_result = empty($_result['Order']['@attributes']) ? $_result['Order'] : array($_result['Order']);
            foreach ($_result as $data_order) {
                if (empty($data_order['@attributes']['ErrorCode'])) {
                    $order = explode('_', $data_order['@attributes']['Number']);
                    $d_status = !empty($data_order['Status']['State']) ? $data_order['Status']['State'] : $data_order['Status'];
                    if (!empty($d_status['@attributes'])) {
                        unset($d_status['@attributes']);
                    }
                    foreach ($d_status as $state) {
                        $data_status[$order[0] . '_' . $order[1] . '_' . $state['@attributes']['Code']] = array(
                            'id' => $state['@attributes']['Code'],
                            'order_id' => $order[0],
                            'shipment_id' => $order[1],
                            'timestamp' => strtotime($state['@attributes']['Date']),
                            'status' => $state['@attributes']['Description'],
                            'city_code' => $state['@attributes']['CityCode'],
                            'city_name' => db_get_field("SELECT a.city FROM ?:rus_city_sdek_descriptions as a LEFT JOIN ?:rus_cities_sdek as b ON a.city_id=b.city_id WHERE b.city_code = ?s", $state['@attributes']['CityCode']),
                            'date' => date("d-m-Y", strtotime($state['@attributes']['Date'])),
                        );
                    }
                }
            }
        }

        return $data_status;
    }

    public static function SdekCityId($location)
    {
        // [tennishouse]
        $_result = '';

        if (!empty($location['city_id']) && !empty($location['city_id_type']) && $location['city_id_type'] == 'sdek') {
            $_result = $location['city_id'];

        } elseif (!empty($location['country']) && !empty($location['city'])) {
        // [tennishouse]
            if (preg_match('/^[a-zA-Z]+$/',$location['city'])) {
                $lang_code = 'en';
            } else {
                $lang_code = 'ru';
            }

            if (($lang_code == 'en') && (Registry::get('addons.rus_cities.status') == 'A')) {
                $d_city = db_get_field("SELECT a.city FROM ?:rus_city_descriptions as a LEFT JOIN ?:rus_city_descriptions as b ON a.city_id = b.city_id WHERE a.lang_code = ?s and b.lang_code = ?s and b.city LIKE ?l ", 'ru', $lang_code, $location['city']);
                if (!empty($d_city)) {
                    $location['city'] = $d_city;
                    $lang_code = 'ru';
                }
            }

            $condition = db_quote(" d.lang_code = ?s AND d.city LIKE ?l AND c.status = ?s", $lang_code , $location['city'] . "%", 'A');
            if (!empty($location['country'])) {
                $condition .= db_quote(" AND c.country_code = ?s", $location['country']);
            }
            if (!empty($location['state'])) {
                $condition .= db_quote(" AND c.state_code = ?s", $location['state']);
            }

            $result = db_get_hash_array("SELECT c.city_code, c.state_code, d.city FROM ?:rus_city_sdek_descriptions as d LEFT JOIN ?:rus_cities_sdek as c ON c.city_id = d.city_id WHERE ?p ORDER BY city_code ASC", 'city_code', $condition);

            if (count($result) == 1) {
                reset($result);
                $_result = key($result);
            } elseif (count($result) > 1) {
                if (!empty($location['state'])) {
                    foreach ($result as $c_code => $c_data) {
                        if ($location['state'] != $c_data['state_code']) {
                            unset($result[$c_code]);
                        }
                    }
                }

                if (count($result) == 1) {
                    reset($result);
                    $_result = key($result);
                } elseif (count($result) > 1) {
                    $max_match = $city_id = 0;
                    ksort($result);
                    foreach ($result as $c_code => $c_data) {
                        $prc = round(strlen($location['city'])/strlen($c_data['city']), 2) * 100;
                        if ($prc > $max_match) {
                            $max_match = $prc;
                            $city_id = $c_code;
                        }
                    }
                    $_result = $city_id;
                }
            }
        }

        return $_result;
    }

    public static function SdekXmlRequest($url, $xml, $params_request)
    {
        $url = $url
            . '?account=' . $params_request['Account']
            . '&secure=' . $params_request['Secure']
            . '&datefirst=' . $params_request['Date'];

        $xml_request = array(
            'xml_request' => '<?xml version="1.0" encoding="UTF-8" ?>' . $xml
        );

        if (empty($params_request['wait'])) {
            $extra = array(
                'request_timeout' => 2,
                'timeout' => 1
            );
        }

        $response = Http::post($url, $xml_request, $extra);

        return $response;
    }

    public static function SdekRequest($url, $data, $type = 'post', $extra = array())
    {
        $token = RusSdek::GetAccessToken();

        if (empty($token)) {
            return false;
        }

        $extra['headers'][] = 'Authorization: Bearer ' . $token;
        $response = Http::$type($url, $data, $extra);

        return RusSdek::SdekResponse($response);
    }

    public static function SdekResponse($response)
    {
        $result = array(
            'error' => false,
            'msg' => false,
            'response' => false,
        );

        $_result = json_decode($response, true);

        if (empty($_result) || !empty($_result['error'])) {
            $result['error'] = true;
            if (!empty($_result['message'])) {
                fn_set_notification('E', __('notice'), $_result['message']);
            }

            return $result;
        }
        if (!empty($_result['entity'])) {
            $result['response'] = $_result['entity'];
        }
        if (!empty($_result['requests'])) {
            foreach ($_result['requests'] as $data) {
                if (!empty($data['errors'])) {
                    foreach ($data['errors'] as $err) {
                        if (!empty($err['code'])) {
                            $result['msg'] = $err['message'];
                            fn_set_notification('E', __('notice'), $err['message']);
                            $result['error'] = true;
                        } else {
                            $result['msg'] = $err['message'];
                            fn_set_notification('N', __('notice'), $err['message']);
                        }
                    }
                }
            }
        }

        return $result;
    }

    public static function SdekPvzOffices($city, $cache_only = false, $packages = array())
    {
        $_offices = fn_get_session_data('sdek_offices');
        $offices = array();

        if (!empty($_offices[$city['cityid']])) {
            $offices = $_offices[$city['cityid']];
        } elseif (empty($cache_only)) {
            $extra = array();
            if (AREA == 'C') {
                $extra = array(
                    'request_timeout' => 5,
                    'timeout' => 5
                );
            } else {
                $extra = array(
                    'request_timeout' => 3,
                    'timeout' => 3
                );
            }
            $result = Http::get('https://integration.cdek.ru/pvzlist/v1/xml', $city, $extra);
            $xml = simplexml_load_string($result);
            if (!empty($xml)) {
                $count = count($xml->Pvz);
                if ($count != 0) {
                    $offices = array();
                    if ($count == 1) {
                        foreach($xml->Pvz->attributes() as $_key => $_value){
                            $code = (string) $xml->Pvz['Code'];
                            $offices[$code][$_key] = (string) $_value;
                        }
                    } else {
                        foreach($xml->Pvz as $key => $office) {
                            $code = (string) $office['Code'];
                            foreach($office->attributes() as $_key => $_value){
                                $offices[$code][$_key] = (string) $_value;
                            }
                        }
                    }
                }
            }
            $_offices[$city['cityid']] = $offices;
            fn_set_session_data('sdek_offices', $_offices);
        }

        if (!empty($offices) && !empty($packages)) {
            $postomat_allowed = true;
            foreach ($packages as $pcg) {
                if (!empty($pcg['shipping_params']['box_length']) || !empty($pcg['shipping_params']['box_width']) || !empty($pcg['shipping_params']['box_height'])) {
                    $box = array_filter(array($pcg['shipping_params']['box_length'], 0, $pcg['shipping_params']['box_width'], $pcg['shipping_params']['box_height']));
                    rsort($box);
                    $allowed = array_filter(array(Registry::get('addons.rus_sdek.max_length'), Registry::get('addons.rus_sdek.max_width'), Registry::get('addons.rus_sdek.max_height')));
                    rsort($allowed);
                    foreach ($box as $dim) {
                        $_dim = array_shift($allowed);
                        if (empty($_dim) || $dim >= $_dim) {
                            $postomat_allowed = false;
                            break 2;
                        }
                    }
                }
            }
            if (empty($postomat_allowed)) {
                foreach ($offices as $i => $office) {
                    if ($office['Type'] == 'POSTAMAT') {
                        unset($offices[$i]);
                    }
                }
            }
        }

        return $offices;
    }

    public static function SdekAddStatusOrders($date_status)
    {
        foreach ($date_status as $d_status) {
            $status_id = db_get_row('SELECT id FROM ?:rus_sdek_status WHERE id = ?i and order_id = ?i and shipment_id = ?i ', $d_status['id'], $d_status['order_id'], $d_status['shipment_id']);
            if (empty($status_id)) {
                $register_id = db_query('INSERT INTO ?:rus_sdek_status ?e', $d_status);
            }
        }
    }

    public static function SdekAddStatusOrdersV2($date_status, $order_id, $shipment_id)
    {
        foreach ($date_status as $d_status) {

            // $dateobj = \DateTime::createFromFormat(\Datetime::ISO8601, $d_status['date_time']);
            // $timestamp = $dateobj->getTimestamp();

            $timestamp = strtotime($d_status['date_time']);
            $status_id = db_get_row('SELECT id FROM ?:rus_sdek_status WHERE code = ?s and order_id = ?i and shipment_id = ?i AND timestamp = ?i', $d_status['code'], $order_id, $shipment_id, $timestamp);
            if (empty($status_id)) {
                $_data = array(
                    'order_id' => $order_id,
                    'shipment_id' => $shipment_id,
                    'timestamp' => $timestamp,
                    'status' => $d_status['name'],
                    'city_name' => $d_status['city'],
                    'code' => $d_status['code']
                );
                $register_id = db_query('INSERT INTO ?:rus_sdek_status ?e', $_data);
            }
        }
    }

    public static function SdekDataAuth($params_shipping)
    {
        $data_auth = array();
        $data_shipping = fn_get_shipping_info($params_shipping['shipping_id'], DESCR_SL);
        $account = $data_shipping['service_params']['authlogin'];
        $secure_password = $data_shipping['service_params']['authpassword'];

        if (!empty($secure_password) && !empty($account)) {
            $secure = md5($params_shipping['Date'] . '&' . $secure_password);
            $data_auth['Date'] = $params_shipping['Date'];
            $data_auth['Account'] = $account;
            $data_auth['Secure'] = $secure;
        } else {
            fn_set_notification('E', __('notice'), __('shippings.sdek.account_password_error'));
        }

        return $data_auth;
    }

    public static function GetAccessToken()
    {
        if (!empty($_SESSION['auth']['sdek_auth']) && $_SESSION['auth']['sdek_auth']['expires_in'] >= time()) {
            return $_SESSION['auth']['sdek_auth']['token'];
        } else {
            unset($_SESSION['auth']['sdek_auth']);
            $data = array(
                'grant_type' => 'client_credentials',
                'client_id' => Registry::get('addons.rus_sdek.client_id'),
                'client_secret' => Registry::get('addons.rus_sdek.client_secret')
            );
            $extra = array(
                'request_timeout' => 2,
                'timeout' => 1
            );
            $result = json_decode(Http::post('https://api.cdek.ru/v2/oauth/token?parameters', $data, $extra), true);

            if (!empty($result['access_token'])) {
                $_SESSION['auth']['sdek_auth'] = array(
                    'token' => $result['access_token'],
                    'expires_in' => time() + $result['expires_in']
                );

                return $result['access_token'];
            }
        }

        return false;
    }

    public static function PullCities($country_code)
    {
        $token = RusSdek::GetAccessToken();
        $cities_total = array(
            'received' => 0,
            'saved' => 0,
            'deleted' => 0
        );
        if (!empty($token)) {
            $data = array(
                'country_codes' => $country_code,
                'size' => 1000,
                'page' => 0
            );
            $extra = array(
                'request_timeout' => 2,
                'timeout' => 1,
                'headers' => array('Authorization: Bearer ' . $token),
            );
            $cities = db_get_hash_array("SELECT a.*, b.* FROM ?:rus_cities_sdek AS a LEFT JOIN ?:rus_city_sdek_descriptions AS b ON b.city_id = a.city_id WHERE a.country_code = ?s", 'city_code', $country_code);

            $state_codes = $reveived_codes = array();
            $last_code = '';

            while (true) {
                $response = false;
                while (!is_array($response) || (!empty($response[0]) && $last_code != '' && $last_code != $response[$data['page'] - 1]['code'])) {
                    $response = json_decode(Http::get('https://api.cdek.ru/v2/location/cities', $data, $extra), true);
                }

                if (!empty($response) && empty($response['errors'])) {
                    foreach ($response as $city) {

                        $last_code = $city['code'];
                        if (in_array($city['code'], $reveived_codes)) {
                            continue;
                        }
                        $cities_total['received']++;
                        $reveived_codes[] = $city['code'];
                        $_data = array(
                            'country_code' => $country_code,
                            'city_code' => $city['code'],
                            'state' => 'A'
                        );
                        if (!empty($city['region_code'])) {
                            if ($city['country_code'] == 'RU') {
                                if (isset($state_codes[$city['region_code']])) {
                                    $_data['state_code'] = $state_codes[$city['region_code']];
                                } else {
                                    $res = fn_find_state_match($city['region']);
                                    if (!empty($res['code'])) {
                                        $state_codes[$city['region_code']] = $_data['state_code'] = $res['code'];
                                    } else {
                                        $state_codes[$city['region_code']] = $_data['state_code'] = '';
                                    }
                                }
                            }
                            $_data['region_code'] = $city['region_code'];
                            $_data['region'] = $city['region'];
                        } elseif ($city['country_code'] == 'RU') {
                            continue;
                        }

                        if (!empty($cities[$city['code']])) {
                            $_data['city_id'] = $cities[$city['code']]['city_id'];
                            unset($cities[$city['code']]);
                        }
                        $_data['city_id'] = db_query("REPLACE INTO ?:rus_cities_sdek ?e", $_data);

                        if (!empty($_data['city_id'])) {
                            $cities_total['saved']++;
                            $_data['city'] = $city['city'];
                            $_data['lang_code'] = 'ru';
                            db_query("REPLACE INTO ?:rus_city_sdek_descriptions ?e", $_data);
                        }

                    }
                }

                if (empty($response)) {
                    break;
                }
                $data['page']++;
                $data['size']--;
            }

            if (!empty($cities)) {

                $cities_total['deleted'] = $cities;
                $to_delete = array();
                foreach ($cities as $ct) {
                    $to_delete[] = $ct['city_id'];
                }

                db_query("DELETE FROM ?:rus_cities_sdek WHERE city_id IN (?n)", $to_delete);
                db_query("DELETE FROM ?:rus_city_sdek_descriptions WHERE city_id IN (?n)", $to_delete);
            }
        }

        return $cities_total;
    }

    public static function PullAllCities()
    {

        $trash = db_get_fields("SELECT city_id FROM ?:rus_cities_sdek WHERE country_code = ''");
        if (!empty($trash)) {
            db_query("DELETE FROM ?:rus_cities_sdek WHERE city_id IN (?n)", $trash);
            db_query("DELETE FROM ?:rus_city_sdek_descriptions WHERE city_id IN (?n)", $trash);
        }

        $countries = db_get_fields("SELECT code FROM ?:countries WHERE status = 'A'");

        $cities_total = array();
        foreach ($countries as $code) {
            $cities_total[$code] = RusSdek::PullCities($code);
        }

        return $cities_total;
    }

}
