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

use Tygh\FeaturesCache;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Shippings\RusSdek;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & $_SESSION['cart'];

if ($mode == 'get_offices') {

    if (!empty($_REQUEST['user_data']) && !empty($_REQUEST['shipping_ids']) && isset($_REQUEST['group_key'])) {
        $params = array(
            'country' => $_REQUEST['user_data']['s_country'],
            'state' => $_REQUEST['user_data']['s_state'],
            'city' => $_REQUEST['user_data']['s_city'],
        );
        $city_code = RusSdek::SdekCityId($params);
        if (!empty($city_code)) {
            $params = array(
                'cityid' => $city_code
            );
            $cart['product_groups'][$_REQUEST['group_key']]['shippings'][$_REQUEST['shipping_ids'][$_REQUEST['group_key']]]['data']['offices'] = RusSdek::SdekPvzOffices($params);
            Registry::get('view')->assign('group_key', $_REQUEST['group_key']);
            Registry::get('view')->assign('cart', $cart);
            Registry::get('view')->assign('shipping', $cart['product_groups'][$_REQUEST['group_key']]['shippings'][$_REQUEST['shipping_ids'][$_REQUEST['group_key']]]);
            if (isset($cart['select_office'])) {
                Registry::get('view')->assign('select_office', $cart['select_office']);
            }
            Registry::get('view')->display('addons/rus_sdek/components/shipping_method.tpl');
        }
    }
    exit;
}

if ($mode == 'select_office') {
    if (!empty($_REQUEST['form'])) {
        $data = json_decode($_REQUEST['form'], true);
        if (empty($data['country_code']) && !empty($data['country'])) {
            $dt = array(
                'q' => $data['country']
            );
            list($countries,) = fn_get_countries($dt);
            if (!empty($countries[0])) {
                $data['country_code'] = $countries[0]['code'];
            }
        }
        if (empty($data['state']) && !empty($data['state_raw']) && !empty($data['country_code'])) {
            $state = fn_find_state_match($data['state_raw'], $data['country_code']);
            if (!empty($state)) {
                $data['state'] = $state['code'];
            }
        }
        $country = $data['country'];
        $data['country'] = $data['country_code'];
        $city_id = RusSdek::SdekCityId($data);
        $offices = array();
        if (!empty($city_id)) {
            $params = array(
                'cityid' => $city_id
            );
            $offices = RusSdek::SdekPvzOffices($params);
            if (empty($data['city_id'])) {
                $data['city_id'] = $city_id;
                $data['city_id_type'] = 'sdek';
            }
        }
        $data['country'] = $country;
        Registry::get('ajax')->assign('preselected_data', $data);
        Registry::get('view')->assign('offices', $offices);
        Registry::get('view')->assign('city', $data['city']);
        Registry::get('view')->display('addons/rus_sdek/components/select_office.tpl');
    }
    exit;
}
