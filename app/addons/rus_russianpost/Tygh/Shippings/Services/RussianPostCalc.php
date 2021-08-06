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

// rus_build_pack dbazhenov

namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Http;

/**
 * UPS shipping service
 */
class RussianPostCalc implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * Maximum allowed requests to Russian Post server
     *
     * @var integer $_max_num_requests
     */
    private $_max_num_requests = 2;

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    private function _internalError($error)
    {
        $this->_error_stack[] = $error;
    }

    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
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

        $shipping_type = $this->_shipping_info['service_params']['shipping_type'];
        $response = json_decode($response, true);

        if (!empty($response)) {
            if ($response['msg']['type'] == 'done') {

                $res = array();
                foreach ($response['calc'] as $i => $calc) {
                    if (!empty($calc['cost'])) {
                        $cost = $calc['cost'];
                        if (CART_PRIMARY_CURRENCY != 'RUB') {
                            $cost = fn_rus_russianpost_format_price_down($cost, 'RUB');
                        }

                        $res[$i] = $cost;
                    }
                }
                asort($res);
                $res_key = array_key_first($res);
                
                if ($res_key !== false) {
                    $return['cost'] = $res[$res_key];
                    $return['delivery_time'] = $response['calc'][$res_key]['days'] . ' ' . __('days');
                    $rates = array(
                        'price' => $cost,
                        'date' => $response['calc'][$res_key]['days'] . ' ' . __('days')
                    );

                    $this->_fillSessionData($rates);
                }
//                 foreach ($response['calc'] as $calc) {
//                     if ($calc['type'] == $shipping_type) {
// 
//                         $cost = $calc['cost'];
//                         if (CART_PRIMARY_CURRENCY != 'RUB') {
//                             $cost = fn_rus_russianpost_format_price_down($cost, 'RUB');
//                         }
// 
//                         $return['cost'] = $cost;
//                         $return['delivery_time'] = $calc['days'] . ' ' . __('days');
//                         $rates = array(
//                             'price' => $cost,
//                             'date' => $calc['days'] . ' ' . __('days')
//                         );
//                         break;
//                     }
//                 }
// 
//                 $this->_fillSessionData($rates);

            } else {
                $return['error'] = $this->processErrors($response);
            }
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
    public function processErrors($response)
    {
        $error = __('error_occurred');
        if ($response['msg']['type'] == 'error') {
            $error = $response['msg']['text'];
        }

        return $error;
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
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];
        $cost = $this->_shipping_info['package_info']['C'];

        if (CART_PRIMARY_CURRENCY != 'RUB') {
            $cost = fn_rus_russianpost_format_price_down($cost, 'RUB');
        }

        $url = 'http://russianpostcalc.ru/api_v1.php';
        $request = array(
            'apikey' => $this->_shipping_info['service_params']['user_key'],
            'method' => 'calc',
            'from_index' => !empty($origination['zipcode']) ? $origination['zipcode'] : '',
            'to_index' => !empty($location['zipcode']) ? $location['zipcode'] : '',
            'weight' => $weight_data['plain'],
            'ob_cennost_rub' => $cost,
        );

        $all_to_md5 = $request;
        $all_to_md5[] = $this->_shipping_info['service_params']['user_key_password'];
        $request['hash'] = md5(implode("|", $all_to_md5));

        $request_data = array(
            'method' => 'post',
            'url' => $url,
            'data' => $request,
        );

        return $request_data;
    }

    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {

        $data = $this->getRequestData();
        $key = $data['data']['hash'];
        $rp_data = fn_get_session_data($key);

        if (empty($rp_data)) {
            // Russian post server works very unstably, that is why we cannot use multithreading and should use cycle. !!! NO, THANK YOU
            $extra = array(
                'request_timeout' => 2,
                'timeout' => 1
            );
            $response = Http::get($data['url'], $data['data'], $extra);
            $res = json_decode($response, true);

            if ($res['msg']['type'] != 'done') {
                $this->_internalError(__('error_occurred'));
            }
            fn_set_session_data($key, $response);
        } else {
            $response = $rp_data;
        }

        return $response;
    }
}
