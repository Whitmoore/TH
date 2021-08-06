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

use Tygh\Registry;
use Tygh\Http;
use Tygh\Shippings\RusSdek;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'autocomplete_city') {

    $result = array();

    $kladr_cities = fn_request_kladr($_REQUEST['city']);

    $country_condition = db_quote(" AND c.country_code != 'RU'");
    $limit = 10;
    if (!is_array($kladr_cities)) {
        $country_condition = '';
    } elseif (!empty($kladr_cities)) {
        $result = $kladr_cities;
        $limit = 12 - count($kladr_cities);
    }

    $sdek_cities = db_get_hash_array("SELECT c.city_code, c.state_code, c.country_code, d.region, d.city, d.region, cd.country FROM ?:rus_city_sdek_descriptions as d LEFT JOIN ?:rus_cities_sdek as c ON c.city_id = d.city_id LEFT JOIN ?:country_descriptions AS cd ON cd.code = c.country_code AND cd.lang_code = 'ru' WHERE d.lang_code = 'ru' AND d.city LIKE ?l AND c.status = 'A' $country_condition LIMIT ?i", 'city_code', $_REQUEST['city'] . "%", $limit);

    if (!empty($sdek_cities)) {
        foreach ($sdek_cities as $i => $city) {
            $result[] = array(
                'city_id' => $city['city_code'],
                'city_id_type' => 'sdek',
                'value' => $city['city'],
                'city' => $city['city'],
                'label' => $city['city'] . (!empty($city['region']) ? ', ' . $city['region'] : '')/* . $city['name']*/ . ', ' . $city['country'],
                'country_code' => $city['country_code'],
                'country' => $city['country'],
                'state' => !empty($city['state_code']) ? $city['state_code'] : ''
            );
        }
    }

    Registry::get('ajax')->assign('cities', $result);
    exit;

} elseif ($mode == 'autocomplete_country') {

    $params = $_REQUEST;

    if (defined('AJAX_REQUEST') && $params['q']) {

        $select = array();

        if (preg_match('/^[a-zA-Z]+$/',$params['q'])) {
            $lang_code = 'en';
        } else {
            $lang_code = 'ru';
        }

        $search = trim($params['q']) . "%";

        $countries = db_get_array("SELECT d.country, c.code FROM ?:country_descriptions as d LEFT JOIN ?:countries as c ON c.code = d.code WHERE country LIKE ?l AND lang_code = ?s AND c.status = 'A'" , $search , $lang_code);

        if (!empty($countries)) {
            foreach ($countries as $country) {
                $select[] = array(
                    'code' => $country['code'],
                    'value' => $country['country'],
                    'label' => $country['country'],
                );
            }
        }

        Registry::get('ajax')->assign('autocomplete', $select);
        exit();
    }

}
