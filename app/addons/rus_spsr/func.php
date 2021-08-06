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

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_spsr_cache_cities()
{
    return __('regenerate_cities_cache', array(
        '[generate_url]' =>  fn_url('rus_spsr.regenerate_cities_cache')
    ));
}

function fn_rus_spsr_install()
{
    $spsr_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`) VALUES ('ROOT', 'spsr_enabled', '7', '0', 'C', 'Y', '140', 'Y')");

    foreach (fn_get_translation_languages() as $lang_code => $v) {
        db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES (?i, 'O', ?s, 'Включить СПСР-Экспресс', '')", $spsr_id, $lang_code);
    }

    $service = array(
        'status' => 'A',
        'module' => 'spsr',
        'code' => '1',
        'sp_file' => '',
        'description' => 'СПСР-Экспресс',
    );
    
    $service_id = db_query('INSERT INTO ?:shipping_services ?e', $service);
    $service['service_id'] = $service_id;

    foreach (fn_get_translation_languages() as $lang_code => $v) {
        $service['lang_code'] = $lang_code;
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }
}

function fn_rus_spsr_uninstall()
{
    $spsr_id = db_get_field('SELECT object_id FROM ?:settings_objects WHERE name = ?s', 'spsr_enabled');

    db_query('DELETE FROM ?:settings_objects WHERE object_id = ?i', $spsr_id);
    db_query('DELETE FROM ?:settings_descriptions WHERE object_id = ?i', $spsr_id);

    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'spsr');
    db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
    db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    
    db_query('DROP TABLE IF EXISTS ?:rus_spsr_cities');
    db_query('DROP TABLE IF EXISTS ?:rus_spsr_city_descriptions');
//     db_query('DROP TABLE IF EXISTS ?:rus_spsr_products');
//     db_query('DROP TABLE IF EXISTS ?:rus_spsr_register');
//     db_query('DROP TABLE IF EXISTS ?:rus_spsr_status');
}

function fn_rus_spsr_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{

    if (!empty($cart['shippings_extra']['data'])) {
        if (!empty($cart['shippings_extra']['data'])) {
            foreach ($cart['shippings_extra']['data'] as $group_key => $shippings) {
                foreach ($shippings as $shipping_id => $shippings_extra) {
                    if (!empty($product_groups[$group_key]['shippings'][$shipping_id]['module'])) {
                        $module = $product_groups[$group_key]['shippings'][$shipping_id]['module'];

                        if ($module == 'spsr' && !empty($shippings_extra)) {
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

                    if ($module == 'spsr' && !empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['data'] = $shipping_extra;
                    }
                }
            }
        }
    }
}

// function fn_spsr_calculate_cost_by_shipment($order_info, $shipping_info, $shipment_info, $rec_city_code) 
// {
// 
//         $total = $weight =  0;
//         $goods = array();
//         $length = $width = $height = 20;
// 
//         foreach ($shipment_info['products'] as $item_id => $amount) {
//             $product = $order_info['products'][$item_id];
// 
//             $total += $product['subtotal'];
// 
//             $product_extra = db_get_row("SELECT shipping_params, weight FROM ?:products WHERE product_id = ?i", $product['product_id']);
// 
//             if (!empty($product_extra['weight']) && $product_extra['weight'] != 0) {
//                 $product_weight = $product_extra['weight'];
//             } else {
//                 $product_weight = 0.01;
//             }
// 
//             $p_ship_params = unserialize($product_extra['shipping_params']);
// 
//             $package_length = empty($p_ship_params['box_length']) ? $length : $p_ship_params['box_length'];
//             $package_width = empty($p_ship_params['box_width']) ? $width : $p_ship_params['box_width'];
//             $package_height = empty($p_ship_params['box_height']) ? $height : $p_ship_params['box_height'];
//             $weight_ar = fn_expand_weight($product_weight);
//             $weight = round($weight_ar['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000, 3);
// 
//             $good['weight'] = $weight;
//             $good['length'] = $package_length;
//             $good['width'] = $package_width;
//             $good['height'] = $package_height;
// 
//             for ($x = 1; $x <= $amount; $x++) {
//                 $goods[] = $good;
//             }
//             
//         }
// 
//         $url = 'http://api.edostavka.ru/calculator/calculate_price_by_json.php';
//         $post['version'] = '1.0';       
//         $post['dateExecute'] = date('Y-m-d');
// 
//         if (!empty($shipping_info['service_params']['dateexecute'])) {
//             $timestamp = TIME + $shipping_info['service_params']['dateexecute'] * SECONDS_IN_DAY;
//             $dateexecute = date('Y-m-d', $timestamp);
//         } else {
//             $dateexecute = date('Y-m-d');
//         }
// 
//         $post['dateExecute'] = $dateexecute;
// 
//         if (!empty($shipping_settings['authlogin'])) {
//             $post['authLogin'] = $shipping_info['service_params']['authlogin'];
//             $post['secure'] = !empty($shipping_info['service_params']['authpassword']) ? md5($post['dateExecute']."&".$shipping_info['service_params']['authpassword']): '';
//         }
// 
//         $post['authLogin'] = $shipping_info['service_params']['authlogin'];
//         $post['secure'] = md5($post['dateExecute']."&".$shipping_info['service_params']['authpassword']);
// 
//         $post['senderCityId'] = $shipping_info['service_params']['from_city_id'];
//         $post['receiverCityId'] = $rec_city_code;
//         $post['tariffId'] = $shipping_info['service_params']['tariffid'];
//         $post['goods'] = $goods;
// 
//         $post = json_encode($post);
// 
//         $key = md5($post);
//         $spsr_data = fn_get_session_data($key);
//         $content = json_encode($post);
//         if (empty($spsr_data)) {
//             $response = Http::post($url, $post, array('Content-Type: application/json',  'Content-Length: '.strlen($content)));
//             fn_set_session_data($key, $response);
//         } else {
//             $response = $spsr_data;
//         }
// 
//         $result = json_decode($response, true);
// 
//         if (!empty($result['result']['price'])) {
//             $result = $result['result']['price'];
//         } else {
//             $result = false;
//         }
// 
//         return $result;
// }
