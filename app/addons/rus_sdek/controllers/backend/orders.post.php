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

                if (empty($sdek_info['RecCityCode']) && isset($shipment['group_key']) && !empty($order_info['product_groups'][$shipment['group_key']]['package_info']['location'])) {
                    $sdek_info['Order']['RecCityCode'] = RusSdek::SdekCityId($order_info['product_groups'][$shipment['group_key']]['package_info']['location']);
                }
                $params_shipping = array(
                    'shipping_id' => $shipment['shipping_id'],
                    'Date' => date("Y-m-d", $shipment['shipment_timestamp'])
                );

                $data_auth = RusSdek::SdekDataAuth($params_shipping);

                if (empty($data_auth)) {
                    continue;
                }

                $order_for_sdek = $sdek_info['Order'];

                $lastname = "";
                if (!empty($order_info['lastname'])) {
                    $lastname = $order_info['lastname'];

                } elseif (!empty($order_info['s_lastname'])) {
                    $lastname = $order_info['s_lastname'];

                } elseif (!empty($order_info['b_lastname'])) {
                    $lastname = $order_info['b_lastname'];
                }
                $firstname = "";
                if (!empty($order_info['firstname'])) {
                    $firstname = $order_info['firstname'];

                } elseif (!empty($order_info['s_firstname'])) {
                    $firstname = $order_info['s_firstname'];

                } elseif (!empty($order_info['b_firstname'])) {
                    $firstname = $order_info['b_firstname'];
                }

                $order_for_sdek['RecipientName'] = $lastname . ' ' . $firstname;

                if (!empty($order_info['phone'])) {
                    $order_for_sdek['Phone'] = $order_info['phone'];

                } elseif (!empty($order_info['s_phone'])) {
                    $order_for_sdek['Phone'] = $order_info['s_phone'];

                } elseif (!empty($order_info['b_phone'])) {
                    $order_for_sdek['Phone'] = $order_info['b_phone'];
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

                $data_auth['Number'] = $params['order_id'] . '_' . $shipment_id;

                $data_auth['OrderCount'] = "1";
                if ($order_info['s_country'] != 'RU') {
                    $data_auth['ForeignDelivery'] = "1";
                    if (!empty($order_info['s_currency'])) {
                        $data_auth['Currency'] = $order_info['s_currency'];
                    }
                }
                $data_auth['wait'] = true;

                $xml = '            ' . RusSdek::arraySimpleXml('DeliveryRequest', $data_auth, 'open');

                $order_for_sdek['SellerName'] = Registry::get('settings.Company.company_name');
                $order_for_sdek['Number'] = $params['order_id'] . '_' . $shipment_id;
                $order_for_sdek['DateInvoice'] = date("Y-m-d", $shipment['shipment_timestamp']);
                $order_for_sdek['RecipientEmail'] = $order_info['email'];

                $partial_condition = $sdek_info['is_partial'] == 'Y' && $order_info['total'] > Registry::get('addons.development.free_shipping_cost') && !empty($order_info['original_shipping_cost']) && $order_info['original_shipping_cost'] > $order_info['display_shipping_cost'];

                if ($order_info['status'] != 'P' && $partial_condition) {
                    if (floatval($order_info['display_shipping_cost']) == 0) {
                        $order_for_sdek['Comment'] .= ' ' . __("try_on_shipping_comment", ["[amount]" => Registry::get('addons.development.free_shipping_cost')]);
                    } elseif (floatval($order_info['display_shipping_cost']) > 0) {
//                         $order_for_sdek['Comment'] .= ' ' . __("try_on_shipping_comment_2", ["[amount]" => Registry::get('addons.development.free_shipping_cost')]);
                    }
                }
                if ($order_info['s_country'] != 'RU') {
                    $order_for_sdek['ShipperName'] = Registry::get('settings.Company.company_name');
                    $order_for_sdek['SellerAddress'] = Registry::get('settings.Company.company_address');
                    $order_for_sdek['ShipperAddress'] = Registry::get('settings.Company.company_address');
                    if (!empty($order_info['s_currency'])) {
                        $order_for_sdek['DeliveryRecipientCost'] = $order_for_sdek['DeliveryRecipientCost'];
//                         $order_for_sdek['RecipientCurrency'] = $order_info['s_currency'];
//                         $order_for_sdek['ItemsCurrency'] = $order_info['s_currency'];
                    }
                } else {
                    $order_for_sdek['DeliveryRecipientCost'] = $order_for_sdek['DeliveryRecipientCost'];
                }

                $xml .= '            ' . RusSdek::arraySimpleXml('Order', $order_for_sdek, 'open');

                if (!empty($sdek_info['Address'])) {
                    $xml .= '            ' . RusSdek::arraySimpleXml('Address', $sdek_info['Address']);
                }

                if (!empty($sdek_info['Order']['Packages'])) {
                    foreach ($sdek_info['Order']['Packages'] as $num => $p_data) {
                        $package_for_xml = array (
                            'Number' => $num,
                            'BarCode' => $num,
                            'Weight' => (!empty($p_data['Weight']) ? $p_data['Weight'] : $weight) * 1000,
                            'SizeA' => $p_data['Size_A'],
                            'SizeB' => $p_data['Size_B'],
                            'SizeC' => $p_data['Size_C'],
                        );
                        $xml .= '            ' . RusSdek::arraySimpleXml('Package', $package_for_xml, 'open');

                        foreach ($p_data['products'] as $item_key => $item_data) {
                            if (empty($item_data['amount'])) {
                                unset($sdek_info['Order']['Packages'][$num]['products'][$item_key]);
                                continue;
                            }
                            $product_for_xml = array (
                                'WareKey' => $sdek_products[$item_key]['ware_key'],
                                'Cost' => $sdek_products[$item_key]['price'],
                                'Payment' => (!empty($item_data['is_paid'])) ? ($sdek_products[$item_key]['total'] < $item_data['is_paid'] ? 0 : $sdek_products[$item_key]['total'] - $item_data['is_paid']) : $sdek_products[$item_key]['total'],
                                'Weight' => $sdek_products[$item_key]['weight'] * 1000 * $item_data['amount'],
                                'Amount' => $item_data['amount'],
                                'Comment' => $sdek_products[$item_key]['product'],
                            );

                            if (!empty($order_info['s_currency'])) {
                                $product_for_xml['CostEx'] = fn_format_price_by_currency($sdek_products[$item_key]['price'], $order_info['s_currency']);
                                $product_for_xml['PaymentEx'] = fn_format_price_by_currency($sdek_products[$item_key]['price'], $order_info['s_currency']);
                                $product_for_xml['WeightBrutto'] = $sdek_products[$item_key]['weight'] * 1000;
                                $product_for_xml['CommentEx'] = preg_replace('/[а-яА-Я]/ui', '', $sdek_products[$item_key]['product']);
                                $product_for_xml['Link'] = $sdek_products[$item_key]['link'];
                            }

                            $xml .= '            ' . RusSdek::arraySimpleXml('Item', $product_for_xml);
                        }

//                         if ($order_info['status'] != 'P' && $sdek_info['is_partial'] == 'Y' && $order_info['total'] > Registry::get('addons.development.free_shipping_cost') && $num == 1) {
//                             if (floatval($order_info['display_shipping_cost']) == 0 && !empty($order_info['original_shipping_cost']) && $order_info['original_shipping_cost'] > $order_info['display_shipping_cost']) {
//                                 $product_for_xml = array (
//                                     'WareKey' => 'SHPNG',
//                                     'Cost' => $order_info['original_shipping_cost'],
//                                     'Payment' => $order_info['original_shipping_cost'],
//                                     'Weight' => 0,
//                                     'Amount' => 1,
//                                     'Comment' => __("shipping_sdek_item"),
//                                 );
//                                 $xml .= '            ' . RusSdek::arraySimpleXml('Item', $product_for_xml);
//                             } elseif (floatval($order_info['display_shipping_cost']) > 0 && !empty($order_info['original_shipping_cost']) && $order_info['original_shipping_cost'] > $order_info['display_shipping_cost']) {
//                                 $product_for_xml = array (
//                                     'WareKey' => 'SHPNG',
//                                     'Cost' => $order_info['original_shipping_cost'],
//                                     'Payment' => $order_info['original_shipping_cost'],
//                                     'Weight' => 0,
//                                     'Amount' => 1,
//                                     'Comment' => __("shipping_sdek_item") . ' 1',
//                                 );
//                                 $xml .= '            ' . RusSdek::arraySimpleXml('Item', $product_for_xml);
//                                 $product_for_xml = array (
//                                     'WareKey' => 'SHPNG2',
//                                     'Cost' => $order_info['display_shipping_cost'],
//                                     'Payment' => $order_info['display_shipping_cost'],
//                                     'Weight' => 0,
//                                     'Amount' => 1,
//                                     'Comment' => __("shipping_sdek_item") . ' 2',
//                                 );
//                                 $xml .= '            ' . RusSdek::arraySimpleXml('Item', $product_for_xml);
//                             }
//                         }
                        $xml .= '            ' . '</Package>';
                    }
                }

                if ($sdek_info['try_on'] == 'Y') {
                    $xml .= '            ' . RusSdek::arraySimpleXml('AddService', array('ServiceCode' => 30));
                    $xml .= '            ' . RusSdek::arraySimpleXml('AddService', array('ServiceCode' => 37));
                }
                if ($sdek_info['is_partial'] == 'Y') {
                    $xml .= '            ' . RusSdek::arraySimpleXml('AddService', array('ServiceCode' => 36));
                }
                $xml .= '            ' . '</Order>';
                $xml .= '            ' . '</DeliveryRequest>';

                $response = RusSdek::SdekXmlRequest('https://integration.cdek.ru/new_orders.php', $xml, $data_auth);

                $result = RusSdek::resultXml($response);

                if (empty($result['error'])) {

                    $register_data = array(
                        'order_id' => $params['order_id'],
                        'shipment_id' => $shipment_id,
                        'dispatch_number' => $result['number'],
                        'data' => date("Y-m-d", $shipment['shipment_timestamp']),
                        'data_xml' => $xml,
                        'timestamp' => TIME,
                        'status' => 'S',
                        'tariff' => $sdek_info['Order']['TariffTypeCode'],
                        'file_sdek' => $shipment_id . '/' . $params['order_id'] . '.pdf',
                        'notes' => $sdek_info['Order']['Comment'],
//                         'dimensions' => serialize(array('size_a' => $sdek_info['Order']['Size_A'], 'size_b' => $sdek_info['Order']['Size_B'], 'size_c' => $sdek_info['Order']['Size_C'])),
//                         'weight' => $sdek_info['Order']['Weight'],
                        'packages' => serialize($sdek_info['Order']['Packages']),
                        'try_on' => $sdek_info['try_on'],
                        'is_partial' => $sdek_info['is_partial']
                    );

                    if (!empty($result['number'])) {
                        db_query('UPDATE ?:shipments SET tracking_number = ?s WHERE shipment_id = ?i', $result['number'], $shipment_id);
                        db_query('UPDATE ?:orders SET tracking_number = ?s WHERE order_id = ?i', $result['number'], $params['order_id']);
                    }

                    $office_services = unserialize(SDEK_OFFICE_SERVICES);
                    if (!in_array($sdek_info['Order']['TariffTypeCode'], $office_services)) {
                        $register_data['address'] = $sdek_info['Address']['Street'];
                    } else {
                        $register_data['address_pvz'] = "{$sdek_info['Address']['PvzCode']}";
                    }

                    $register_id = db_query('INSERT INTO ?:rus_sdek_register ?e', $register_data);

                    foreach ($sdek_products as $sdek_product) {
                        $sdek_product['register_id'] = $register_id;
                        db_query('INSERT INTO ?:rus_sdek_products ?e', $sdek_product);
                    }

                    fn_sdek_get_ticket_order($data_auth, $params['order_id'], $shipment_id);
                }

                $date_status = RusSdek::orderStatusXml($data_auth, $params['order_id'], $shipment_id);

                RusSdek::SdekAddStatusOrders($date_status);
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
                $params_shipping = array(
                    'shipping_id' => $shipment['shipping_id'],
                    'Date' => date("Y-m-d", $shipment['shipment_timestamp']),
                );
                $data_auth = RusSdek::SdekDataAuth($params_shipping);
                if (empty($data_auth)) {
                    continue;
                }
                $date_status = RusSdek::orderStatusXml($data_auth, $params['order_id'], $shipment_id);
                RusSdek::SdekAddStatusOrders($date_status);
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
                                $city_codes[] = $status['city_code'];
                            }
                            $cities = db_get_hash_single_array("SELECT city, city_code FROM ?:rus_city_sdek_descriptions as a LEFT JOIN ?:rus_cities_sdek as b ON a.city_id = b.city_id WHERE b.city_code IN (?n)", array('city_code', 'city'), $city_codes);
                            foreach ($data_status as $k => $status) {
                                $status['city'] = $cities[$status['city_code']];
                                $status['date'] = date("d-m-Y  H:i:s", $status['timestamp']);
                                $data_shipments[$shipment['shipment_id']]['sdek_status'][$status['id']] = array(
                                    'id' => $status['id'],
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
                            'tariff_id' => $data_shipping['service_params']['tariffid'],
                            'send_city_code' => $data_shipping['service_params']['from_city_id'],
                        );

                        $office_services = unserialize(SDEK_OFFICE_SERVICES);
                        if (in_array($data_shipping['service_params']['tariffid'], $office_services)) {
                            $data_shipments[$shipment['shipment_id']]['offices'] = $offices;
                        } else {
                            $data_shipments[$shipment['shipment_id']]['rec_address'] = (!empty($order_info['s_address'])) ? $order_info['s_address'] : $order_info['b_address'];
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
