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

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_sdek_get_orders_totals_post(&$totals, $paid_statuses, $join, $condition, $group)
{
    $net_data = db_get_hash_array("SELECT ?:orders.order_id, ?:orders.total, ?:orders.net_total, ?:orders.net_subtotal, ?:orders.net_shipping, ?:orders.net_payment, SUM(?:rus_sdek_register.net_shipping) AS net_shipping_final, SUM(?:rus_sdek_register.net_payment) AS net_payment_final FROM ?:orders $join WHERE ?:orders.status IN (?a) $condition $group ", 'order_id', $paid_statuses);

    $net_totally = $total_income = 0;
    foreach ($net_data as $order) {
        if ($order['net_subtotal'] > 0) {
            $net_shipping = $order['net_shipping_final'] > 0 ? $order['net_shipping_final'] : $order['net_shipping'];
            $net_payment = $order['net_payment_final'] > 0 ? $order['net_payment_final'] : $order['net_payment'];
            $net_totally += $order['net_subtotal'] + $net_shipping + $net_payment;
            $total_income += $order['total'] - $order['net_subtotal'] - $net_shipping - $net_payment;
        } else {
            $net_totally += $order['net_total'];
            $total_income += $order['total'] - $order['net_total'];
        }
    }
    $totals['net_totally'] = $net_totally;
    $totals['total_income'] = $total_income;
}

function fn_rus_sdek_get_orders($params, &$fields, $sortings, $condition, &$join, &$group, $get_totals)
{
    if (!empty($get_totals)) {
        $fields[] = "IF(?:rus_sdek_register.order_id IS NOT NULL, SUM(?:rus_sdek_register.net_shipping), NULL) AS net_shipping_final";
        $fields[] = "IF(?:rus_sdek_register.order_id IS NOT NULL, SUM(?:rus_sdek_register.net_payment), NULL) AS net_payment_final";
        $join .= " LEFT JOIN ?:rus_sdek_register ON ?:rus_sdek_register.order_id = ?:orders.order_id";
        if (strpos($group, '?:orders.order_id') === false) {
            $group .= ($group == '' ? ' GROUP BY' : ',') . " ?:orders.order_id ";
        }
    }
}

function fn_rus_sdek_get_order_info(&$order, $additional_data)
{
    if (AREA == 'A' && $order['net_subtotal'] > 0) {
        $net_data = db_get_row("SELECT SUM(net_shipping) AS net_shipping, SUM(net_payment) AS net_payment FROM ?:rus_sdek_register WHERE order_id = ?i", $order['order_id']);
        $net_shipping = $net_data['net_shipping'] ?? $order['net_shipping'];
        $net_payment = $net_data['net_payment'] ?? $order['net_payment'];
        $net_total = $order['net_subtotal'] + $net_shipping + $net_payment;
        if ($net_total != $order['net_total']) {
            $order['net_total_org'] = $order['net_total'];
            $order['net_total'] = $net_total;
        }
    }

}

function fn_get_city_offices($city_id)
{
    if (!empty($city_id)) {
        return RusSdek::SdekPvzOffices(array('cityid' => $city_id));
    }
}

function fn_rus_sdek_install()
{
//     $sdek_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`) VALUES ('ROOT', 'sdek_enabled', '7', '0', 'C', 'Y', '130', 'Y')");
//
//     foreach (fn_get_translation_languages() as $lang_code => $v) {
//         db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES (?i, 'O', ?s, 'Включить СДЭК', '')", $sdek_id, $lang_code);
//     }
//
//     $service = array(
//         'status' => 'A',
//         'module' => 'sdek',
//         'code' => '1',
//         'sp_file' => '',
//         'description' => 'СДЭК',
//     );
//
//     $service_id = db_query('INSERT INTO ?:shipping_services ?e', $service);
//     $service['service_id'] = $service_id;
//
//     foreach (fn_get_translation_languages() as $lang_code => $v) {
//         $service['lang_code'] = $lang_code;
//         db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
//     }
}

function fn_rus_sdek_uninstall()
{
//     $sdek_id = db_get_field('SELECT object_id FROM ?:settings_objects WHERE name = ?s', 'sdek_enabled');
//
//     db_query('DELETE FROM ?:settings_objects WHERE object_id = ?i', $sdek_id);
//     db_query('DELETE FROM ?:settings_descriptions WHERE object_id = ?i', $sdek_id);
//
//     $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'sdek');
//     db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
//     db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
//
//     db_query('DROP TABLE IF EXISTS ?:rus_cities_sdek');
//     db_query('DROP TABLE IF EXISTS ?:rus_city_sdek_descriptions');
//     db_query('DROP TABLE IF EXISTS ?:rus_sdek_products');
//     db_query('DROP TABLE IF EXISTS ?:rus_sdek_register');
//     db_query('DROP TABLE IF EXISTS ?:rus_sdek_status');
}

function fn_rus_sdek_update_cart_by_data_post(&$cart, $new_cart_data, $auth)
{
    if (!empty($new_cart_data['select_office'])) {
        $cart['select_office'] = $new_cart_data['select_office'];
    }
}

function fn_rus_sdek_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{

    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['select_office'])) {
            $select_office = $cart['select_office'];

        } elseif (!empty($_REQUEST['select_office'])) {
            $select_office = $cart['select_office'] = $_REQUEST['select_office'];
        }

        if (!empty($select_office)) {
            foreach ($product_groups as $group_key => $group) {
                if (!empty($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                        $shipping_id = $shipping['shipping_id'];

                        if($shipping['module'] != 'sdek') {
                            continue;
                        }

                        if (!empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                            $shippings_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                            $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shippings_extra;
                            if (!empty($select_office[$group_key][$shipping_id])) {
                                $office_id = $select_office[$group_key][$shipping_id];
                                $product_groups[$group_key]['chosen_shippings'][$shipping_key]['office_id'] = $office_id;

                                if (!empty($shippings_extra['offices'][$office_id])) {
                                    $office_data = $shippings_extra['offices'][$office_id];
                                    $product_groups[$group_key]['chosen_shippings'][$shipping_key]['office_data'] = $office_data;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($cart['shippings_extra']['data'])) {
            foreach ($cart['shippings_extra']['data'] as $group_key => $shippings) {
                foreach ($shippings as $shipping_id => $shippings_extra) {
                    if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                        $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];

                        if ($module == 'sdek' && !empty($shippings_extra)) {
                            $product_groups[$group_key]['shippings'][$shipping_id]['data'] = $shippings_extra;

                            if (!empty($shippings_extra['delivery_time'])) {
                                $product_groups[$group_key]['shippings'][$shipping_id]['delivery_time'] = $shippings_extra['delivery_time'];
                            }
                        }
                    }
                }
            }
        }

        foreach ($product_groups as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    $shipping_id = $shipping['shipping_id'];
                    $module = $shipping['module'];

                    if ($module == 'sdek' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                    }
                }
            }
        }
    }
}

function fn_sdek_calculate_cost_by_shipment($order_info, $shipping_info, $shipment_info, $rec_city_code)
{

        $total = $weight =  0;
        $goods = array();
        $length = $width = $height = 20;

        foreach ($shipment_info['products'] as $item_id => $amount) {
            $product = $order_info['products'][$item_id];

            $total += $product['subtotal'];

            $product_extra = db_get_row("SELECT shipping_params, weight FROM ?:products WHERE product_id = ?i", $product['product_id']);

            if (!empty($product_extra['weight']) && $product_extra['weight'] != 0) {
                $product_weight = $product_extra['weight'];
            } else {
                $product_weight = 0.01;
            }

            $p_ship_params = unserialize($product_extra['shipping_params']);

            $package_length = empty($p_ship_params['box_length']) ? $length : $p_ship_params['box_length'];
            $package_width = empty($p_ship_params['box_width']) ? $width : $p_ship_params['box_width'];
            $package_height = empty($p_ship_params['box_height']) ? $height : $p_ship_params['box_height'];
            $weight_ar = fn_expand_weight($product_weight);
            $weight = round($weight_ar['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);

            $good['weight'] = $weight;
            $good['length'] = $package_length;
            $good['width'] = $package_width;
            $good['height'] = $package_height;

            for ($x = 1; $x <= $amount; $x++) {
                $goods[] = $good;
            }

        }

        $url = 'http://api.cdek.ru/calculator/calculate_price_by_json.php';
        $post['version'] = '1.0';
        $post['dateExecute'] = date('Y-m-d');

        if (!empty($shipping_info['service_params']['dateexecute'])) {
            $timestamp = TIME + $shipping_info['service_params']['dateexecute'] * SECONDS_IN_DAY;
            $dateexecute = date('Y-m-d', $timestamp);
        } else {
            $dateexecute = date('Y-m-d');
        }

        $post['dateExecute'] = $dateexecute;

        if (!empty($shipping_settings['authlogin'])) {
            $post['authLogin'] = $shipping_info['service_params']['authlogin'];
            $post['secure'] = !empty($shipping_info['service_params']['authpassword']) ? md5($post['dateExecute']."&".$shipping_info['service_params']['authpassword']): '';
        }

        $post['authLogin'] = $shipping_info['service_params']['authlogin'];
        $post['secure'] = md5($post['dateExecute']."&".$shipping_info['service_params']['authpassword']);

        $post['senderCityId'] = $shipping_info['service_params']['from_city_id'];
        $post['receiverCityId'] = $rec_city_code;
        $post['tariffId'] = $shipping_info['service_params']['tariffid'];
        $post['goods'] = $goods;

        $post = json_encode($post);

        $key = md5($post);
        $sdek_data = fn_get_session_data($key);
        $content = json_encode($post);
        if (empty($sdek_data)) {
            $response = Http::post($url, $post, array('Content-Type: application/json',  'Content-Length: '.strlen($content)));
            fn_set_session_data($key, $response);
        } else {
            $response = $sdek_data;
        }

        $result = json_decode($response, true);

        if (!empty($result['result']['price'])) {
            $result = $result['result']['price'];
        } else {
            $result = false;
        }

        return $result;
}
