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

/**
 * Dostavista shipping service
 */
class Dostavista implements IService
{
    /**
     * Abailability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    private $version = "1.0";

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();
    private $packages_amount = array();

    protected static $_error_descriptions = array(
    );

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
        $shipping_settings = $this->_shipping_info['service_params'];
        $location = $this->_shipping_info['package_info']['location'];
        $origination = $this->_shipping_info['package_info']['origination'];
        if (empty($shipping_settings['token'])) {
            $this->_internalError(__('shippings.dostavista.token_error'));
        }
        // if ($location['state'] != $origination['state']) {
        //     $this->_internalError(__('shippings.dostavista.different_regions'));
        // }
        $post = array(
            'matter' => $shipping_settings['default_matter'],
            'vehicle_type_id' => $shipping_settings['vehicle_type'],
            'total_weight_kg' => round($weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3),
            'insurance_amount' => $this->_shipping_info['package_info']['C'],
            'is_client_notification_enabled' => false,
            'is_contact_person_notification_enabled' => true,
            'is_route_optimizer_enabled' => true,
            'loaders_count' => 0,
            'backpayment_details' => '4564789-309579',
            'is_motobox_required' => false,
            'payment_method' => 'non_cash',
            // 'bank_card_id' => ,
            'points' => array(
                array(
                    'address' => $origination['city'] . ', ' . $origination['address'],
                    'contact_person' => array(
                        'phone' => $origination['phone'],
                    ),
                    // 'client_order_id' => ,
                    // 'latitude' => ,
                    // 'longitude' => ,
                    // 'required_start_datetime' => ,
                    // 'required_finish_datetime' => ,
                    // 'taking_amount' => ,
                    // 'buyout_amount' => ,
                    // 'note' => ,
                    'is_order_payment_here' => false,
                    // 'building_number' => ,
                    // 'entrance_number' => ,
                    // 'intercom_code' => ,
                    // 'floor_number' => ,
                    // 'apartment_number' => ,
                    // 'invisible_mile_navigation_instructions' => ,
                    // 'is_cod_cash_voucher_required' => ,
                ),
                array(
                    'address' => $location['city'] . ', ' . $location['address'],
                    'contact_person' => array(
                        'phone' => $location['phone'],
                        'name' => $location['firstname'] . (!empty($location['firstname']) ? ' ' : '') . $location['lastname']
                    ),
                    // 'client_order_id' => ,
                    // 'latitude' => ,
                    // 'longitude' => ,
                    // 'required_start_datetime' => ,
                    // 'required_finish_datetime' => ,
                    // 'taking_amount' => $this->_shipping_info['package_info']['C'],
                    // 'buyout_amount' => ,
                    // 'note' => ,
                    // 'is_order_payment_here' => true,
                    // 'building_number' => ,
                    // 'entrance_number' => ,
                    // 'intercom_code' => ,
                    // 'floor_number' => ,
                    // 'apartment_number' => ,
                    // 'invisible_mile_navigation_instructions' => ,
                    // 'is_cod_cash_voucher_required' => true,
                    // 'packages' => array()
                )
            )

        );

        $url = 'https://robotapitest.dostavista.ru/api/business/1.1/calculate-order';

        $request_data = array(
            'method' => 'post',
            'url' => $url,
            'data' => json_encode($post),
            'headers' => array('Content-Type: application/json',  'X-DV-Auth-Token: ' . $shipping_settings['token'])
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

        $key = md5($data['data']);
        $dostavista_data = fn_get_session_data($key);

        if (empty($dostavista_data)) {
            $extra = array(
                'headers' => $data['headers'],
                'request_timeout' => 2,
                'timeout' => 1
            );
            $response = Http::post($data['url'], $data['data'], $extra);
            fn_set_session_data($key, $response);
        } else {
            $response = $dostavista_data;
        }

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
            'available_payments' => array(),
            'delivery_time' => '',
        );

        $result = json_decode($response);
        $result_array = json_decode(json_encode($result), true);

        $return['error'] = $this->processErrors($result_array);

        if (empty($this->_error_stack) && empty($return['error'])) {

            $rates = $this->_getRates($result_array);

            $this->_fillSessionData($rates);

            if (!empty($rates['price'])) {
                $return['cost'] = $rates['price'];
                $return['delivery_time'] = $rates['date'];
                $return['available_payments'] = $rates['available_payments'];
            } else {
                $this->_internalError(__('xml_error'));
                $return['error'] = $this->processErrors($result_array);
            }

        }

        return $return;
    }

    private function _getRates($response)
    {
        $rates = array();
        if (!empty($response['order']['payment_amount'])) {
            $rates['price'] = $response['order']['payment_amount'];
            if ($this->_shipping_info['service_params']['transfer_fee_included'] == 'N') {
                $rates['price'] -= $response['order']['money_transfer_fee_amount'];
            }
            $rates['date'] = '0-1' .  __('days');
            $rates['available_payments'] = array(
                'payment_on_delivery' => 'Y'
            );
        }

        return $rates;
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
        // Parse JSON message returned by the sdek post server.
        $return = false;

        if (!empty($result_array['errors'])) {
            $return .= $this->parseParameters($result_array, 'errors');
        }
        if (!empty($result_array['warnings'])) {
            $return .= $this->parseParameters($result_array, 'warnings');
        }

        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $error) {
                $return .= '; ' . $error;
            }
        }

        return $return;
    }

    private function parseParameters($result_array, $type)
    {
        $return = '';
        foreach ($result_array[$type] as $i => $wrng) {
            $return .= (!empty($return) ? '; ' : '') . $wrng;
            if ($wrng == 'invalid_parameters' && !empty($result_array['parameter_' . $type])) {
                $info = '';
                foreach ($result_array['parameter_' . $type] as $i => $par_wrngs) {
                    foreach ($par_wrngs as $wrngs) {
                        $info .= (!empty($info) ? '; ' : '') . $i . ': ' . (is_array($wrngs) ? (array_key_first($wrngs) . ' - ' . $wrngs[array_key_first($wrngs)][0]) : $wrngs);
                    }
                }
                if ($info != '') {
                    $return .= '(' . $info . ')';
                }
            }
        }

        return $return;
    }

}
