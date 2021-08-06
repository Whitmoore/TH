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

class Multiship implements IService
{
    /**
     * Abailability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

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

    public $sid;

    public $price_id = 0;
    public $pickuppoint_id = 0;

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
     * Gets error message from shipping service server
     *
     * @param string $response
     * @internal param string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        return '';
    }

    /**
     * Sets data to internal class variable
     *
     * @param  array      $shipping_info
     * @return array|void
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
        $this->company_id = Registry::get('runtime.company_id');

        $group_key = $shipping_info['keys']['group_key'];
        $shipping_id = $shipping_info['keys']['shipping_id'];

        if (isset($_SESSION['cart']['shippings_extra']['price_id'][$group_key][$shipping_id])) {
            $this->price_id = $_SESSION['cart']['shippings_extra']['price_id'][$group_key][$shipping_id];
        }

        if (isset($_SESSION['cart']['shippings_extra']['pickuppoint_id'][$group_key][$shipping_id])) {
            $this->pickuppoint_id = $_SESSION['cart']['shippings_extra']['pickuppoint_id'][$group_key][$shipping_id];
        }

    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $request_data = array();

        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);

        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];

        $request_data['url'] = 'https://multiship.ru/openAPI_v3/searchDeliveryList';

        $package_size = $this->getSizePackage($this->_shipping_info['package_info']);

        $request_data['data'] = array(
            'client_id' => Registry::get('addons.rus_multiship.client_id'),

            'city_from' => $origination['city'],
            'city_to' => !empty($location['city']) ? $location['city'] : '',
            'weight' => $weight_data['plain'],
            'height' => $package_size['height'],
            'width' => $package_size['width'],
            'length' => $package_size['length'],
            'total_cost' => $this->_shipping_info['package_info']['C'],
        );

        $request_data['data']['secret_key'] = $this->getSecretKey(Registry::get('addons.rus_multiship.key_search_delivery_list'), $request_data['data']);

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

        if (!isset($this->_shipping_info['service_params']['logs']) || $this->_shipping_info['service_params']['logs'] != 'Y') {
            Registry::set('log_cut', true);
        }

        $response = Http::post($data['url'], $data['data']);

        return json_decode($response, true);
    }

    private function _fillSessionData($response, $delivery_index)
    {
        $group_key = $this->_shipping_info['keys']['group_key'];
        $shipping_id = $this->_shipping_info['keys']['shipping_id'];

        $last_to_day = -1;
        $last_from_day = -1;
        $last_day = 1;

        $days_same = array();
        $same_index = 0;

        foreach ($response['data'][$delivery_index]['schedules'] as $key_day => $day) {

            $first_div = reset($day);

            if ($first_div['from'] == $last_from_day && $first_div['to'] == $last_to_day) {
                $days_same[$same_index]['last_day'] = $key_day;
                $last_day = $key_day;

            } else {
                $same_index++;
                $days_same[$same_index]['first_day'] = $key_day;
                $days_same[$same_index]['last_day'] = $key_day;
                $days_same[$same_index]['from'] = $first_div['from'];
                $days_same[$same_index]['to'] = $first_div['to'];

                if ($first_div['from'] == $first_div['to']) {
                    $days_same[$same_index]['all_day'] = true;
                }

                $last_day = $key_day;
            }

            $last_from_day = $first_div['from'] ;
            $last_to_day = $first_div['to'];
        }

        if ($last_day < 7) {
            $same_index++;
            $days_same[$same_index]['first_day'] = $last_day + 1;
            $days_same[$same_index]['last_day'] = 7;
            $days_same[$same_index]['from'] = false;
        }

        foreach ($days_same as $key => $day) {
            if ($day['last_day'] == 7) {
                $days_same[$key]['last_day'] = 0;
            }

            if ($day['first_day'] == 7) {
                $days_same[$key]['first_day'] = 0;
            }
        }

        $response['data'][$delivery_index]['schedule_days'] = $days_same;

        $_SESSION['cart']['shippings_extra']['index'][$group_key][$shipping_id] = $delivery_index;
        $_SESSION['cart']['shippings_extra']['data'][$group_key][$shipping_id] = $response['data'][$delivery_index];
        $_SESSION['cart']['shippings_extra']['package_size'][$group_key] = $this->getSizePackage($this->_shipping_info['package_info']);

        return true;
    }

    /**
     * Gets shipping cost and information about possible errors
     *
     * @param string $response
     * @internal param string $resonse Reponse from Shipping service server
     * @return array Shipping cost and errors
     */
    public function processResponse($response)
    {
        if (isset($response['status']) && $response['status'] == 'ok') {

            $first_delivery = reset($response['data']);
            if (!empty($first_delivery)) {
                $delivery_cost = $first_delivery['cost'];
                $delivery_index = 0;

                if (empty($this->price_id)) {
                    // Find min delivery cost
                    foreach ($response['data'] as $key_delivery => $delivery) {
                        if ($delivery['cost'] < $delivery_cost) {
                            $delivery_index = $key_delivery;
                            $delivery_cost = $delivery['cost_with_rules'];
                        }
                    }

                } else {
                    foreach ($response['data'] as $key_delivery => $delivery) {
                        if ($delivery['price_id'] == $this->price_id && (empty($delivery['pickuppoint_id']) || ($delivery['pickuppoint_id'] == $this->pickuppoint_id))) {

                            $delivery_index = $key_delivery;
                            $delivery_cost = $delivery['cost_with_rules'];

                            break;
                        }
                    }
                }

                $this->_fillSessionData($response, $delivery_index);
                $return = array(
                    'cost' => $this->convertCurrencies($delivery_cost),
                    'error' => false,
                );

            } else {
                $return = array(
                    'cost' => false,
                    'error' => false,
                );
            }

        } else {
            $return = array(
                'cost' => false,
                'error' => true,
            );
        }

        return $return;
    }

    private function getSecretKey($methodKey, $data)
    {
        $preparedData = $this->getRecursiveContent($data) . $methodKey;
        $secretKey = md5($preparedData);

        return $secretKey;
    }

    private function getRecursiveContent($data)
    {
        $secretKey = '';
        $keys = array_keys($data);
        sort($keys);

        foreach ($keys as $key) {
            if (!is_array($data[$key])) {
                $secretKey .= $data[$key];
            } else {
                $secretKey .= $this->getRecursiveContent($data[$key]);
            }
        }

        return $secretKey;
    }

    private function getSizePackage($shipping_info)
    {
        $shipping_settings = $this->_shipping_info['service_params'];

        $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : 10;
        $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : 10;
        $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : 10;

        $package_size = array(
            'length' => 20,
            'width' => 15,
            'height' => 10,
        );

        if (!empty($shipping_info['packages'])) {

            $box_data = array();
            foreach ($shipping_info['packages'] as $package) {
                $box_data[] = array(
                    empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'],
                    empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'],
                    empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height']
                );
            }

            $sort_box_data = array();
            foreach ($box_data as $box) {
                arsort($box);
                $sort_box_data[] = array_values($box);
            }

            $lenght_data = array();
            $width_data = array();
            $height_data = array();
            foreach ($sort_box_data as $box) {
                $lenght_data[] = $box[0];
                $width_data[] = $box[1];
                $height_data[] = $box[2];
            }

            $package_size = array(
                'length' => max($lenght_data),
                'width' => max($width_data),
                'height' => array_sum($height_data),
            );
        }

        return $package_size;
    }

    private function convertCurrencies($price, $from_currency = 'RUB')
    {
        if (CART_PRIMARY_CURRENCY != $from_currency) {
            $currencies = Registry::get('currencies');

            if (isset($currencies[$from_currency])) {
                $currency = $currencies[$from_currency];
                $price = $price * floatval($currency['coefficient']);
                $price = fn_format_price($price, '', $currency['decimals']);
            }
        }

        return $price;
    }
}
