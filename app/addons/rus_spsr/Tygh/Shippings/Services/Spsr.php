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

namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Registry;
use Tygh\Http;
use Tygh\Shippings\SpsrFrame;

/**
 * Spsr shipping service
 */
class Spsr implements IService
{
    /**
     * Abailability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;
    
    private $SID = 0;

    private $version = "1.0";

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    /**
     * Current Company id environment
     *
     * @var int $company_id
     */
    public $company_id = 0;

    public $city_id;

    /**
     * Collects errors during preparing and processing request
     *
     * @param string $error
     */
    private function _internalError($error)
    {
        $this->_error_stack[] = $error;
    }

    /**
     * Checks if shipping service allows to use multithreading
     *
     * @return bool true if allow
     */
    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }

    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
        $this->company_id = Registry::get('runtime.company_id');
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        static $request_data = NULL;

        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $amount = $this->_shipping_info['package_info']['C'];
        
        $weight = round($weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);
        $packages_count = 1;
        if (!empty($this->_shipping_info['package_info']['packages'])) {
            $packages = $this->_shipping_info['package_info']['packages'];
            $packages_count = count($packages);
            if ($packages_count > 0) {
                foreach ($packages as $id => $package) {
                    $weight_ar = fn_expand_weight($package['weight']);
                    $weight = round($weight_ar['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

                    $amount = $package['cost'];
                    // fix for stupid sdek api that cant handle multiple packages
                    break;
                }
            }
        }
        
        $shipping_settings = $this->_shipping_info['service_params'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];
        $ruble = Registry::get('currencies.RUB');

        if ($origination['country'] != 'RU') {
            $this->_internalError(__('shippings.spsr.country_error'));
        }

        if (empty($ruble) || $ruble['is_primary'] == 'N') {
            $this->_internalError(__('shippings.spsr.activation_error'));
        }

        $this->city_id = $_code = SpsrFrame::SpsrCityId($location);
        $_code_sender = $shipping_settings['from_city_id'];
        $url = ($shipping_settings['mode'] == 'L') ? 'https://api.spsr.ru' : 'https://api.spsr.ru/test';
        $login = ($shipping_settings['mode'] == 'L') ? $shipping_settings['login'] : 'test';
        $psw = ($shipping_settings['mode'] == 'L') ? $shipping_settings['password'] : 'test';
        $icn = ($shipping_settings['mode'] == 'L') ? $shipping_settings['icn'] : '7600010711';
        
        if (!empty($shipping_settings['authlogin'])) {
            $post['authLogin'] = $shipping_settings['authlogin'];
            $post['secure'] = !empty($shipping_settings['authpassword']) ? md5($post['dateExecute']."&".$shipping_settings['authpassword']): '';
        }

        $post['TARIFFCOMPUTE_2'] = '';
        $post['ToCity'] = (int) $_code;
        $post['FromCity'] = (int) $_code_sender;
        $post['Weight'] = $weight;
        $post['Nature'] = '2';
        $post['Amount'] = $amount;
        $post['AmountCheck'] = '0';
        $post['SMS'] = '0';
        $post['SMS_Recv'] = '0';
        $post['BeforeSignal'] = '0';
        $post['PlatType'] = '1';
        $post['DuesOrder'] = '0';
        $post['ByHand'] = '1';
        $post['icd'] = '0';
        $post['ToBeCalledFor'] = '0';
        $post['Weight35'] = ($post['Weight'] > 35) ? '1' : '0';
        $post['Weight80'] = ($post['Weight'] > 80) ? '1' : '0';
        $post['Weight200'] = ($post['Weight'] > 200) ? '1' : '0';
        
        $post['GabarythB'] = '0';
        $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : 50;
        $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : 30;
        $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : 20;

        $GabarythB = '0';
        if (!empty($this->_shipping_info['package_info']['packages'])) {
            $packages = $this->_shipping_info['package_info']['packages'];
            $packages_count = count($packages);
            if ($packages_count > 0) {
                foreach ($packages as $id => $package) {
                    $package_length = empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'];
                    $package_width = empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'];
                    $package_height = empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height'];
                    if ($package_length + $package_width + $package_height > 180) {
                        $GabarythB = '1';
                    }
                }
            } else {
                if ($length + $width + $height > 180) {
                    $GabarythB = '1';
                }
            }
        } else {
            if ($length + $width + $height > 180) {
                $GabarythB = '1';
            }
        }
        $post['ICN'] = $icn;

        $request_data = array(
            'login' => $login,
            'psw' => $psw,
            'url' => $url,
            'amount' => $packages_count,
            'data' => $post
        );

        return $request_data;
    }

    private function startSession($login, $psw, $url)
    {
        $data = array(
            'Login' => $login,
            'Pass' => $psw,
            'UserAgent' => 'TennisHouse',
        );
        $xml = '<p:Params Name="WALogin" Ver="1.0" xmlns:p="http://spsr.ru/webapi/WA/1.0" />' . SpsrFrame::arraySimpleXml('Login', $data);
        
        $params = array(
            'suffix' => 'usermanagment/login/1.0',
            'url' => $url
        );
        $response = SpsrFrame::SpsrXmlRequest($xml, $params);
        if (!empty($response) && $response['error'] == '0' && !empty($response['data'][0]['SID'])) {
            $this->SID = $response['data'][0]['SID'];
        } else {
            $this->_internalError(!empty($response['data'][0]['ErrorMessageRU']) ? $response['data'][0]['ErrorMessageRU'] : $response['data'][0]['ErrorMessageEN']);
        }
    }
    
    private function finishSession($login, $sid, $url)
    {
        $data = array(
            'Login' => $login,
            'SID' => $sid
        );
        $xml = '<p:Params Name="WALogout" Ver="1.0" xmlns:p="http://spsr.ru/webapi/WA/1.0" />' . SpsrFrame::arraySimpleXml('Logout', $data);
        
        $params = array(
            'suffix' => 'usermanagment/logout/1.0',
            'url' => $url
        );
        $response = SpsrFrame::SpsrXmlRequest($xml, $params);
    }
    
    private function SpsrRequest($data)
    {
        $result = array();
        $this->startSession($data['login'], $data['psw'], $data['url']);
        if (!empty($this->SID)) {
            $data['data']['SID'] = $this->SID;
            $extra = array(
                'request_timeout' => 2,
                'timeout' => 1
            );
            $response = Http::get($data['r_url'], $data['data'], $extra);
            $this->finishSession($data['login'], $this->SID, $data['url']);
        }
        
        return $response;
    }
    
    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {
        $data = $this->getRequestData();
        $data['r_url'] = 'http://www.cpcr.ru/cgi-bin/postxml.pl';
        $key = md5(json_encode($data['data']));
        $spsr_data = fn_get_session_data($key);

        if (empty($spsr_data)) {
            $response = $this->SpsrRequest($data);
            fn_set_session_data($key, $response);
        } else {
            $response = $spsr_data;
        }
        $response = array('amount' => $data['amount'], 'response' => $response);

        return $response;
    }

     /**
     * Gets shipping cost and information about possible errors
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return array  Shipping cost and errors
     */
    public function processResponse($response)
    {
        $return = array(
            'cost' => false,
            'error' => false,
            'delivery_time' => '',
        );

        $amount = !empty($response['amount']) ? $response['amount'] : 1;
        $response = $response['response'];
        $response = simplexml_load_string($response);
        $_result = json_decode(json_encode((array) $response), true);

        $shipping_info = $this->_shipping_info;
        
        if (empty($_result['Error']) && empty($this->_error_stack) && !empty($_result['Tariff']) && $_result['Tariff']['Total_Dost'] != 'Error') {

            $rates = array();
            $dp = explode('-', $_result['Tariff']['DP']);
            $rates = array(
                'price' => $_result['Tariff']['Total_Dost'] * $amount,
                'date' => $dp[0] . (($dp[0] != $dp[1]) ? '-' . $dp[1] . ' ' : ' ') . __('days')
            );

            $this->_fillSessionData($rates);

            if (empty($this->_error_stack) && !empty($rates['price'])) {
                $return['cost'] = $rates['price'];
                $return['delivery_time'] = $rates['date'];
            } else {
                $this->_internalError(__('xml_error'));
//                 $return['error'] = $_result['Error'];
            }

        } else {
            $return['error'] = $this->processErrors($_result);
        }

        return $return;
    }

    private function _fillSessionData($rates = array())
    {
        $shipping_info = $this->_shipping_info;

        if (isset($shipping_info['keys']['group_key']) && !empty($shipping_info['keys']['shipping_id'])) {
            $group_key = $shipping_info['keys']['group_key'];
            $shipping_id = $shipping_info['keys']['shipping_id'];

            if (!empty($rates['date'])) {
                $_SESSION['cart']['shippings_extra']['data'][$group_key][$shipping_id]['delivery_time'] = $rates['date'];
            }
        }

        return true;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($result_array)
    {
        $return = false;

        if (!empty($result_array['Error'])) {
            $return = $result_array['Error'];
        }

        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $error) {
                $return .= '; ' . $error;
            }
        }

        return $return;
    }
}
