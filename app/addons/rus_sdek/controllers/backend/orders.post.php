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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $params = $_REQUEST;
    if (!empty($params['order_id'])) {
        $order_info = fn_get_order_info($params['order_id'], false, true, true, true);

        if ($mode == 'sdek_order_delivery') {

            if (empty($params['add_sdek_info'])) {
                return false;
            }

            foreach ($params['add_sdek_info'] as $shipment_id => $sdek_info) {

                list($_shipments, $search) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true, 'shipment_id' => $shipment_id));

                $shipment = reset($_shipments);

                // if (empty($sdek_info['RecCityCode']) && isset($shipment['group_key']) && !empty($order_info['product_groups'][$shipment['group_key']]['package_info']['location'])) {
                //     $sdek_info['Order']['RecCityCode'] = RusSdek::SdekCityId($order_info['product_groups'][$shipment['group_key']]['package_info']['location']);
                // }

                $order_for_sdek = $sdek_info['order'];
                $order_for_sdek['type'] = '1';
                $order_for_sdek['number'] = $params['order_id'] . '_' . $shipment_id;
                $order_for_sdek['date_invoice'] = date("Y-m-d", $shipment['shipment_timestamp']);
                $order_for_sdek['shipper_name'] = Registry::get('settings.Company.company_name');
                $order_for_sdek['shipper_address'] = Registry::get('settings.Company.company_address');

                if ($order_info['status'] != 'P' && !empty($order_info['original_shipping_cost']) && $order_info['original_shipping_cost'] > $order_for_sdek['delivery_recipient_cost']['value'] && !empty(Registry::get('addons.development.free_shipping_cost'))) {
                    $order_for_sdek['delivery_recipient_cost_adv'][] = array(
                        'threshold' => Registry::get('addons.development.free_shipping_cost'),
                        'sum' => $order_info['original_shipping_cost']
                    );
                }

                $order_for_sdek['sender'] = array(
                    'company' => Registry::get('settings.Company.company_name'),
                    'name' => 'Репин Александр Вячеславович',
                    'email' => Registry::get('settings.Company.company_orders_department'),
                    'phones' => array(
                        array('number' => str_replace(' ', '', Registry::get('settings.Company.company_phone')))
                    )
                );

                $order_for_sdek['seller'] = array(
                    'name' => 'ИП Репин Александр Вячеславович',
                    'inn' => '732894430462',
                    'phone' => str_replace(' ', '', Registry::get('settings.Company.company_phone')),
                    'ownership_form' => '63',
                    'address' => Registry::get('settings.Company.company_address')
                );

                $order_for_sdek['recipient'] = array(
                    'name' => ($order_info['lastname'] ?? $order_info['s_lastname'] ?? $order_info['b_lastname']) . ' ' . ($order_info['firstname'] ?? $order_info['s_firstname'] ?? $order_info['b_firstname']),
                    'email' => $order_info['email'],
                    'phones' => array(
                        array('number' => $order_info['phone'] ?? $order_info['s_phone'] ?? $order_info['b_phone'])
                    )
                );

                $order_for_sdek['services'] = array(
                    array('code' => 'INSPECTION_CARGO')
                );
                if (!empty($sdek_info['try_on']) && $sdek_info['try_on'] == 'Y') {
                    $order_for_sdek['services'][] = array(
                        'code' => 'TRYING_ON'
                    );
                }
                if (!empty($sdek_info['is_partial']) && $sdek_info['is_partial'] == 'Y') {
                    $order_for_sdek['services'][] = array(
                        'code' => 'PART_DELIV'
                    );
                }

                $sdek_products = array();
                $weight = 0;

                foreach ($shipment['products'] as $item_id => $amount) {
                    $data_product = $order_info['products'][$item_id];

                    $product_weight = db_get_field("SELECT weight FROM ?:products WHERE product_id = ?i", $data_product['product_id']);

                    if (!empty($product_weight) && $product_weight != 0) {
                        $product_weight = $product_weight;
                    } else {
                        $product_weight = 0.01;
                    }

                    $price = $data_product['price'] - $order_info['subtotal_discount'] * $data_product['price'] / $order_info['subtotal'];
                    $sdek_product = array(
                        'ware_key' => $data_product['item_id'],
                        'product' => $data_product['product'],
                        'price' => $price,
                        'amount' => $amount,
                        'total' => /*($order_info['status'] == 'P') ? 0 : */$price,
                        'weight' => /*$amount * */$product_weight,
                        'order_id' => $params['order_id'],
                        'shipment_id' => $shipment_id,
                        'link' => fn_url("products.view&product_id=" . $data_product['product_id'], 'C')
                    );
                    $weight = $weight + ($amount * $product_weight);

                    if (!empty($data_product['extra']['configuration_data'])) {
                        $sdek_product['product'] .= ' (';
                        $iter = 0;
                        foreach ($data_product['extra']['configuration_data'] as $pc_id => $pdata) {
                            $sdek_product['product'] .= ($iter > 0 ? '; ' : '') . $pdata['product'] . ' - ' . $pdata['extra']['step'] . __("items");
                            $iter++;
                        }
                        $sdek_product['product'] .= ')';
                    }

                    $sdek_products[$data_product['item_id']] = $sdek_product;
                }
                if (!empty($sdek_info['packages'])) {
                    foreach ($sdek_info['packages'] as $num => $p_data) {
                        $package = array (
                            'number' => $num,
                            'weight' => (!empty($p_data['weight']) ? $p_data['weight'] : $weight) * 1000,
                            'length' => $p_data['length'],
                            'width' => $p_data['width'],
                            'height' => $p_data['height'],
                        );

                        foreach ($p_data['products'] as $item_key => $item_data) {
                            if (empty($item_data['amount'])) {
                                unset($sdek_info['packages'][$num]['products'][$item_key]);
                                continue;
                            }
                            $item = array (
                                'name' => $sdek_products[$item_key]['product'],
                                'ware_key' => $sdek_products[$item_key]['ware_key'],
                                'payment' => array(
                                    'value' => (!empty($item_data['is_paid'])) ? ($sdek_products[$item_key]['total'] < $item_data['is_paid'] ? 0 : $sdek_products[$item_key]['total'] - $item_data['is_paid']) : $sdek_products[$item_key]['total']
                                ),
                                'cost' => $sdek_products[$item_key]['price'],
                                'weight' => $sdek_products[$item_key]['weight'] * 1000,
                                'amount' => $item_data['amount'],
                                'name_i18n' => preg_replace('/[а-яА-Я]/ui', '', $sdek_products[$item_key]['product']),
                                'url' => $sdek_products[$item_key]['link']
                            );

                            $package['items'][] = $item;
                        }

                        $order_for_sdek['packages'][] = $package;
                    }
                }

                $extra = array(
                    'headers' => array('Content-Type: application/json')
                );

                $result = RusSdek::SdekRequest('https://api.cdek.ru/v2/orders', json_encode($order_for_sdek), 'post', $extra);

                if (empty($result['error']) && !empty($result['response']['uuid'])) {

                    $_result = RusSdek::SdekRequest('https://api.cdek.ru/v2/orders/' . $result['response']['uuid'], array(), 'get');
                    if (empty($_result['error'])) {

                        $requests = 1;
                        while (empty($_result['response']['cdek_number']) && $requests < 5) {
                            sleep(1);
                            $_result = RusSdek::SdekRequest('https://api.cdek.ru/v2/orders/' . $result['response']['uuid'], array(), 'get');
                            $requests++;
                        }

                        $register_data = array(
                            'order_id' => $params['order_id'],
                            'shipment_id' => $shipment_id,
                            'uuid' => $_result['response']['uuid'],
                            'dispatch_number' => $_result['response']['cdek_number'],
                            'data' => date("Y-m-d", $shipment['shipment_timestamp']),
                            'data_xml' => serialize($order_for_sdek),
                            'timestamp' => TIME,
                            'status' => 'S',
                            'tariff' => $sdek_info['order']['tariff_code'],
                            'file_sdek' => $shipment_id . '/' . $params['order_id'] . '.pdf',
                            'notes' => $sdek_info['order']['comment'],
                            'packages' => serialize($sdek_info['packages']),
                            'try_on' => $sdek_info['try_on'],
                            'is_partial' => $sdek_info['is_partial'],
                            'net_shipping' => $_result['response']['delivery_detail']['delivery_sum'] ?? 0,
                            'net_payment' => ($_result['response']['delivery_detail']['total_sum'] ?? 0) - ($_result['response']['delivery_detail']['delivery_sum'] ?? 0)
                        );

                        db_query('UPDATE ?:shipments SET tracking_number = ?s WHERE shipment_id = ?i', $_result['response']['cdek_number'], $shipment_id);
                        db_query('UPDATE ?:orders SET tracking_number = ?s WHERE order_id = ?i', $_result['response']['cdek_number'], $params['order_id']);

                        if (!empty($_result['response']['delivery_point'])) {
                            $register_data['address_pvz'] = $_result['response']['delivery_point'];
                        } else {
                            $register_data['address'] = $_result['response']['to_location']['address'];
                        }

                        $register_id = db_query('INSERT INTO ?:rus_sdek_register ?e', $register_data);

                        foreach ($sdek_products as $sdek_product) {
                            $sdek_product['register_id'] = $register_id;
                            db_query('INSERT INTO ?:rus_sdek_products ?e', $sdek_product);
                        }

                        if (!empty($_result['response']['statuses'])) {
                            RusSdek::SdekAddStatusOrdersV2($_result['response']['statuses'], $params['order_id'], $shipment_id);
                        }

                        // $params_shipping = array(
                        //     'shipping_id' => $shipment['shipping_id'],
                        //     'Date' => date("Y-m-d", $shipment['shipment_timestamp'])
                        // );
                        //
                        // $data_auth = RusSdek::SdekDataAuth($params_shipping);
                        // fn_sdek_get_ticket_order($data_auth, $params['order_id'], $shipment_id);
                    }
                }
            }

        } elseif ($mode == 'sdek_order_delete') {
            foreach ($params['add_sdek_info'] as $shipment_id => $sdek_info) {
                list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true, 'shipment_id' => $shipment_id));
                $shipment = reset($_shipments);
                $params_shipping = array(
                    'shipping_id' => $shipment['shipping_id'],
                    'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
                );
                $data_auth = RusSdek::SdekDataAuth($params_shipping);
                if (empty($data_auth)) {
                    continue;
                }

                $data_auth['Number'] = $params['order_id'] . '_' . $shipment_id;
                $data_auth['OrderCount'] = "1";
                $xml = '            ' . RusSdek::arraySimpleXml('DeleteRequest', $data_auth, 'open');
                $order_sdek = array (
                    'Number' => $params['order_id'] . '_' . $shipment_id
                );
                $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_sdek);
                $xml .= '            ' . '</DeleteRequest>';

                $response = RusSdek::SdekXmlRequest('https://integration.cdek.ru/delete_orders.php', $xml, $data_auth);
                $result = RusSdek::resultXml($response);
                if (empty($result['error'])) {
                    db_query('DELETE FROM ?:rus_sdek_products WHERE order_id = ?i and shipment_id = ?i ', $params['order_id'], $shipment_id);
                    db_query('DELETE FROM ?:rus_sdek_register WHERE order_id = ?i and shipment_id = ?i ', $params['order_id'], $shipment_id);
                    db_query('DELETE FROM ?:rus_sdek_status WHERE order_id = ?i and shipment_id = ?i ', $params['order_id'], $shipment_id);
                }
            }

        } elseif ($mode == 'sdek_order_status') {
            foreach ($params['add_sdek_info'] as $shipment_id => $sdek_info) {
                list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true, 'shipment_id' => $shipment_id));
                $shipment = reset($_shipments);

                $_result = RusSdek::SdekRequest('https://api.cdek.ru/v2/orders?cdek_number=' . $shipment['tracking_number'], array(), 'get');

                if (empty($_result['error']) && !empty($_result['response']['statuses'])) {
                    RusSdek::SdekAddStatusOrdersV2($_result['response']['statuses'], $params['order_id'], $shipment_id);
                }
            }
        }

        $url = fn_url("orders.details&order_id=" . $params['order_id'] . '&selected_section=sdek_orders', 'A', 'current');
        if (defined('AJAX_REQUEST') && !empty($url)) {
            Registry::get('ajax')->assign('force_redirection', $url);
            exit;
        }

        return array(CONTROLLER_STATUS_OK, $url);
    }
}

if ($mode == 'details') {
    $params = $_REQUEST;
    $order_info = Registry::get('view')->getTemplateVars('order_info');

    $sdek_info = $sdek_pvz = false;
    if (!empty($order_info['shipping'])) {
        foreach ($order_info['shipping'] as $shipping) {
            if ($shipping['module'] == 'sdek') {
                $sdek_pvz = !empty($shipping['office_id']) ? $shipping['office_id'] : '';
            }
        }
    }

    list($all_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true));

    if (!empty($all_shipments)) {

        $sdek_shipments = $data_shipments = array();

        foreach ($all_shipments as $key => $_shipment) {
            if ($_shipment['carrier'] == 'sdek') {
                $sdek_shipments[$_shipment['shipment_id']] = $_shipment;
            }
        }

        if (!empty($sdek_shipments)) {

            $offices = array();
            $location = array(
                'country' => (!empty($order_info['s_country'])) ? $order_info['s_country'] : $order_info['b_country'],
                'state' => (!empty($order_info['s_state'])) ? $order_info['s_state'] : $order_info['b_state'],
                'city' => (!empty($order_info['s_city'])) ? $order_info['s_city'] : $order_info['b_city']
            );
            $rec_city_code = RusSdek::SdekCityId($location);

            if (!empty($rec_city_code)) {
                $offices = RusSdek::SdekPvzOffices(array('cityid' => $rec_city_code));
            }

            foreach ($sdek_shipments as $key => $shipment) {

                    $data_sdek = db_get_row("SELECT register_id, order_id, timestamp, status, tariff, address_pvz, address, file_sdek, notes, dimensions, weight, packages, try_on, is_partial FROM ?:rus_sdek_register WHERE order_id = ?i and shipment_id = ?i", $shipment['order_id'], $shipment['shipment_id']);

                    if (!empty($data_sdek)) {
                        $data_shipments[$shipment['shipment_id']] = $data_sdek;
                        $data_shipments[$shipment['shipment_id']]['dimensions'] = !empty($data_sdek['dimensions']) ? unserialize($data_sdek['dimensions']) : array();
                        $data_shipments[$shipment['shipment_id']]['packages'] = !empty($data_sdek['packages']) ? unserialize($data_sdek['packages']) : array();
                        $data_shipments[$shipment['shipment_id']]['shipping'] = $shipment['shipping'];
                        $office_services = unserialize(SDEK_OFFICE_SERVICES);
                        if (in_array($data_shipments[$shipment['shipment_id']]['tariff'], $office_services)) {
                            $data_shipments[$shipment['shipment_id']]['address'] = $offices[$data_sdek['address_pvz']]['Address'];
                        }

                        $data_status = db_get_array("SELECT * FROM ?:rus_sdek_status WHERE order_id = ?i AND shipment_id = ?i ORDER BY timestamp ASC", $params['order_id'], $shipment['shipment_id']);
                        if (!empty($data_status)) {
                            $city_codes = array();
                            foreach ($data_status as $k => $status) {
                                if (empty($status['city_name'])) {
                                    $city_codes[] = $status['city_code'];
                                }
                            }
                            if (!empty($city_codes)) {
                                $cities = db_get_hash_single_array("SELECT city, city_code FROM ?:rus_city_sdek_descriptions as a LEFT JOIN ?:rus_cities_sdek as b ON a.city_id = b.city_id WHERE b.city_code IN (?n)", array('city_code', 'city'), $city_codes);
                            }
                            foreach ($data_status as $k => $status) {
                                $status['city'] = ($status['city_name'] != '') ? $status['city_name'] : $cities[$status['city_code']];
                                $status['date'] = date("d-m-Y  H:i:s", $status['timestamp']);
                                $data_shipments[$shipment['shipment_id']]['sdek_status'][] = array(
                                    'id' => (!empty($status['code']) ? $status['code'] : $status['id']),
                                    'date' => $status['date'],
                                    'status' => $status['status'],
                                    'city' => $status['city'],
                                );
                            }
                        }

                    } else {

                        $data_shipping = fn_get_shipping_info($shipment['shipping_id'], DESCR_SL);

                        $cost = fn_sdek_calculate_cost_by_shipment($order_info, $data_shipping, $shipment, $rec_city_code);

                        $prod_ids = array();
                        foreach ($shipment['products'] as $item_id => $amount) {
                            $prod_ids[$item_id] = $order_info['products'][$item_id]['product_id'];
                        }
                        $weights = db_get_hash_single_array("SELECT product_id, weight FROM ?:products WHERE product_id IN (?n)", array('product_id', 'weight'), array_unique($prod_ids));
                        $package_weight = 0;
                        foreach ($shipment['products'] as $item_id => $amount) {
                            $package_weight += $amount * $weights[$prod_ids[$item_id]];
                        }

                        $data_shipments[$shipment['shipment_id']] = array(
                            'order_id' => $shipment['order_id'],
                            'shipping' => $shipment['shipping'],
                            'comments' => $shipment['comments'],
                            'delivery_cost' => $cost,
                            'weight' => $package_weight,
                            'tariff_id' => $data_shipping['service_params']['tariffid']
                        );

                        $office_services = unserialize(SDEK_OFFICE_SERVICES);
                        if (in_array($data_shipping['service_params']['tariffid'], $office_services)) {
                            $data_shipments[$shipment['shipment_id']]['offices'] = $offices;
                        } else {
                            $data_shipments[$shipment['shipment_id']]['rec_address'] = (!empty($order_info['s_address'])) ? $order_info['s_address'] : $order_info['b_address'];
                        }

                        $from_office_ids = unserialize(SDEK_FROM_OFFICE_SERVICES);
                        $data_shipments[$shipment['shipment_id']]['from_location'] = array(
                            'code' => $data_shipping['service_params']['from_city_id'],
                            'address' => $data_shipping['service_params']['from_location']
                        );
                        if (in_array($data_shipping['service_params']['tariffid'], $from_office_ids)) {
                            $data_shipments[$shipment['shipment_id']]['shipment_point'] = $data_shipping['service_params']['shipment_point'];
                        }
                    }

                    if ($order_info['status'] != 'P') {
                        foreach ($shipment['products'] as $item_id => $k) {
                            $category_ids = db_get_fields("SELECT category_id FROM ?:products_categories WHERE product_id = ?i ORDER BY link_type DESC", $order_info['products'][$item_id]['product_id']);
                            if (in_array(APPAREL_CATEGORY_ID, $category_ids) || in_array(SHOES_CATEGORY_ID, $category_ids) ||  in_array(BADMINTON_SHOES_CATEGORY_ID, $category_ids)) {
                                $sdek_shipments[$shipment['shipment_id']]['try_on'] = true;
                                $sdek_shipments[$shipment['shipment_id']]['is_partial'] = true;
                                break;
                            }
                        }
                    }
            }

            if (!empty($data_shipments)) {

                Registry::set('navigation.tabs.sdek_orders', array (
                    'title' => __('shippings.sdek.sdek_orders'),
                    'js' => true
                ));

                Registry::get('view')->assign('data_shipments', $data_shipments);
                Registry::get('view')->assign('sdek_pvz', $sdek_pvz);
                Registry::get('view')->assign('rec_city_code', $rec_city_code);
                Registry::get('view')->assign('order_id', $params['order_id']);

            }
            Registry::get('view')->assign('sdek_shipments', $sdek_shipments);
        }
    }

} elseif ($mode == 'sdek_get_ticket') {

    $params = $_REQUEST;

    $file = $params['order_id'] . '.pdf';

    $path = fn_get_files_dir_path() . 'sdek/' . $params['shipment_id'] . '/';

    fn_get_file($path . $file);

    if (defined('AJAX_REQUEST') && !empty($url)) {
        Registry::get('ajax')->assign('force_redirection', $url);
        exit;
    }

    return array(CONTROLLER_STATUS_OK);
}

function fn_sdek_get_ticket_order($data_auth, $order_id, $chek_id)
{
    unset($data_auth['Number']);
    $xml = '            ' . RusSdek::arraySimpleXml('OrdersPrint', $data_auth, 'open');
    $order_sdek = array (
        'Number' => $order_id . '_' . $chek_id,
        'Date' => $data_auth['Date']
    );
    $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_sdek);
    $xml .= '            ' . '</OrdersPrint>';

    $response = RusSdek::SdekXmlRequest('https://integration.cdek.ru/orders_print.php', $xml, $data_auth);

    $download_file_dir = fn_get_files_dir_path() . '/sdek' . '/' . $chek_id . '/';

    fn_rm($download_file_dir);
    fn_mkdir($download_file_dir);

    $name = $order_id . '.pdf';

    $download_file_path = $download_file_dir . $name;
    if (!fn_is_empty($response)) {
        fn_put_contents($download_file_path, $response);
    }
}
