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

use Tygh\Memcache;
use Tygh\Registry;
use Tygh\Bootstrap;
use Tygh\FeaturesCache;
use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    if ($mode == 'complete_orders') {

        if ($action == 'clear') {

            unset($_SESSION['complete_orders']);
            $_SESSION['complete_orders']['step'] = 'one';

        } elseif ($action == 'finish') {

            if (!empty($_REQUEST['order_ids'])) {
                foreach ($_REQUEST['order_ids'] as $id) {
                    fn_change_order_status($id, ORDER_STATUS_FINISHED, '', fn_get_notification_rules(array(), true));
                }
            }

            $_SESSION['complete_orders']['step'] = 'two';

        } elseif ($action == 'search') {

            $file = fn_filter_uploaded_data('csv_file');

            $order_statuses = array();
            if (!empty($file) && file_exists($file[0]['path'])) {
                $f = false;
                if ($file[0]['path'] !== false) {
                    $f = fopen($file[0]['path'], 'rb');
                }

                if ($f) {
                    $max_line_size = 65536; // 64 Кб
                    $result = array();

                    $delimiter = ',';
                    $codes = array();
                    $order_id_column = $order_status_column = 0;
                    while (($data = fn_fgetcsv($f, $max_line_size, $delimiter)) !== false) {

                        if (empty($order_id_column)) {
                            foreach ($data as $key => $column) {
                                if ($column == '№ накладной ИМ' || $column == '№ отправления ИМ') {
                                    $order_id_column = $key;
                                }
                                if ($column == 'Статус') {
                                    $order_status_column = $key;
                                }
                            }
                        } elseif (!empty($data[$order_id_column]) && !empty($data[$order_status_column])) {
                            $order_id = explode('_', $data[$order_id_column]);
                            $order_id = $order_id[0];
                            if ($data[$order_status_column] == 'Вручен') {
                                $order_statuses[$order_id] = 'E';
                            } else {
                                $order_statuses[$order_id] = 'N';
                            }
                        }
                    }

                }
            }
            $_SESSION['complete_orders'] = array(
                'step' => 'two',
                'order_d_statuses' => $order_statuses
            );
        }

        fn_prepare_coplete_orders();
        Registry::get('view')->display('addons/development/views/development/complete_orders.tpl');
        exit;
    }

    if ($mode == 'process_rrp') {

    if ($_REQUEST['step'] == 'one') {
            $file = fn_filter_uploaded_data('csv_file');
            if (!empty($file) && file_exists($file[0]['path'])) {

                $f = false;
                if ($file[0]['path'] !== false) {
                    $f = fopen($file[0]['path'], 'rb');
                }

                if ($f) {
                    $max_line_size = 65536; // 64 Кб
                    $result = array();

                    $delimiter = ',';
                    $codes = array();
                    while (($data = fn_fgetcsv($f, $max_line_size, $delimiter)) !== false) {
                        $code = $data[0];
                        if ($_REQUEST['type'] == 'rrp') {
                            $price = $net_cost = 0;
                            if (count($data) == 2) {
                                $price = floatval(str_replace(',', '.', $data[1]));
                            } elseif (count($data) == 3) {
                                $price = floatval(str_replace(',', '.', $data[2]));
                                $net_cost = floatval(str_replace(',', '.', $data[1]));
                            }
                            if (!empty($code) && !empty($price)) {
                                $codes[] = $code;
                                $product_ids = db_get_array("SELECT product_id, import_divider FROM ?:products WHERE product_code = ?s", $code);
                                if (!empty($product_ids)) {
                                    foreach ($product_ids as $i => $p_data) {
                                        if (!empty($net_cost)) {
                                            db_query("UPDATE ?:products SET price_mode = IF(price_mode = 'D', 'S', price_mode), update_with_currencies = 'N', list_price = '0', net_cost = ?d WHERE product_id = ?s", $p_data['import_divider'] * $net_cost, $p_data['product_id']);
                                        } else {
                                            db_query("UPDATE ?:products SET price_mode = IF(price_mode = 'D', 'S', price_mode), update_with_currencies = 'N', list_price = '0' WHERE product_id = ?s", $p_data['product_id']);
                                        }
                                        if ($p_data['import_divider'] == '1') {
                                            $product_data = array(
                                                'price' => $price
                                            );
                                            fn_get_product_prices($p_data['product_id'], $product_data, $auth);
                                            if (!empty($product_data['prices'])) {
                                                foreach ($product_data['prices'] as $j => $pr) {
                                                    if (!empty($pr['percentage_discount'])) {
                                                        $product_data['prices'][$j]['price'] = $pr['percentage_discount'];
                                                        $product_data['prices'][$j]['type'] = 'P';
                                                    } else {
                                                        $product_data['prices'][$j]['type'] = 'A';
                                                    }
                                                }
                                            }
                                            fn_update_product_prices($p_data['product_id'], $product_data);
                                        }
                                    }
                                }
                            }
                        } elseif ($_REQUEST['type'] == 'ean') {
                            $ean = $data[1];
                            if (!empty($code) && !empty($ean)) {
                                $codes[] = $code;
                                $product_ids = db_get_array("SELECT product_id, import_divider FROM ?:products WHERE product_code = ?s", $code);
                                if (!empty($product_ids)) {
                                    foreach ($product_ids as $i => $p_data) {
                                        db_query("UPDATE ?:products SET ean = ?s WHERE product_id = ?i", $ean, $p_data['product_id']);
                                    }
                                }
                            }
                        } elseif ($_REQUEST['type'] == 'net') {
                            $net_cost = $data[1];
                            if (!empty($code) && !empty($net_cost)) {
                                $codes[] = $code;
                                $product_ids = db_get_array("SELECT product_id, import_divider FROM ?:products WHERE product_code = ?s", $code);
                                if (!empty($product_ids)) {
                                    foreach ($product_ids as $i => $p_data) {
                                        db_query("UPDATE ?:products SET net_currency_code = 'RUB', net_cost = ?d WHERE product_id = ?i", $p_data['import_divider'] * $net_cost, $p_data['product_id']);
                                    }
                                }
                            }
                        }
                    }
                    if ($_REQUEST['type'] == 'rrp') {
                        $params = array(
                            'features_hash' => 'V' . implode('.V', $_REQUEST['rrp_data']['brand_ids']),
                            'not_product_codes' => $codes,
                            'sort_by' => 'updated_timestamp',
                            'sort_order' => 'desc'
                        );
                        list($products,) = fn_get_products($params);
                        fn_gather_additional_products_data($products, array(
                            'get_icon' => false,
                            'get_detailed' => false,
                            'get_additional' => false,
                            'get_options' => false,
                            'get_discounts' => true,
                            'get_features' => false,
                            'get_title_features' => false,
                            'allow_duplication' => false
                        ));
                        $cids = $_result = array();
                        foreach ($products as $i => $product) {
                            if ($product['net_cost'] > 0) {
                                $product['net_cost_rub'] = $product['net_cost'] * Registry::get('currencies.' . $product['net_currency_code'] . '.coefficient');
                                $_result[$product['type_id']][] = $product;
                            }
                        }
                        $categories = db_get_array("SELECT a.category_id, b.category FROM ?:categories AS a LEFT JOIN ?:category_descriptions AS b ON b.category_id = a.category_id AND b.lang_code = ?s WHERE a.category_id IN (?n) ORDER BY a.position ASC", CART_LANGUAGE, array_keys($_result));
                        $params = array(
                            'zone' => 'catalog'
                        );
                        list($promotions,) = fn_get_promotions($params);
                        Registry::get('view')->assign('promotions', $promotions);
                        Registry::get('view')->assign('products', $_result);
                        Registry::get('view')->assign('categories', $categories);
                    }
                }
            }
            fn_set_notification('N', __('notice'), __('done'));
            if ($_REQUEST['type'] == 'rrp') {
                $step = 'two';
            } elseif ($_REQUEST['type'] == 'ean') {
                $step = 'one';
            }
        } elseif ($_REQUEST['step'] == 'two') {
            if (!empty($_REQUEST['promotion_id']) && !empty($_REQUEST['product_ids'])) {
                $promotion_data = fn_get_promotion_data($_REQUEST['promotion_id']);
                if (!empty($promotion_data['conditions']['conditions'])) {
                    $found = false;
                    foreach ($promotion_data['conditions']['conditions'] as $i => $promo) {
                        if ($promo['condition'] == 'products' && $promo['operator'] == 'in') {
                            $promotion_data['conditions']['conditions'][$i]['value'] .= ',' . implode(',', $_REQUEST['product_ids']);
                            $found = true;
                            break;
                        }
                    }
                    if (empty($found)) {
                        $new_condition = array(
                            'operator' => 'in',
                            'condition' => 'products',
                            'value' => implode(',', $_REQUEST['product_ids'])
                        );
                        $promotion_data['conditions']['conditions'][] = $new_condition;
                    }
                    fn_update_promotion($promotion_data);
                }
            }
            $step = 'three';
        }
        Registry::get('view')->assign('step', $step);
        Registry::get('view')->assign('brands', fn_development_get_brands());
        Registry::get('view')->display('addons/development/views/development/update_rrp.tpl');
        exit;
    }

    if ($mode == 'update_warehouse_stocks') {
        if (!empty($_REQUEST['calculate'])) {
            $file = fn_filter_uploaded_data('csv_file');
            $missing_products = $updated_products = $broken_options_products = $broken_net_cost = $trash = array();
            if (!empty($file) && !empty($_REQUEST['brand_ids'])) {
                if (in_array(BABOLAT_FV_ID, $_REQUEST['brand_ids'])) {
                    $options = array(
                        'delimiter' => 'C',
                        'lang_code' => 'ru'
                    );
                    if (list($total_data, $trash) = fn_get_babolat_csv($file[0]['path'], $options)) {

                        $params = array(
                            'features_hash' => 'V' . implode('.V', $_REQUEST['brand_ids']),
                            'warehouse_id' => $_REQUEST['warehouse_id']
                            //'force_get_by_ids' => 'Y',
                        );
                        list($products,) = fn_get_products($params);
                        $in_stock = $all_ids = $no_code = $updated_by_combinations = array();
                        if (!empty($products)) {
                            foreach ($products as $i => $p_data) {
                                if (!empty($p_data['product_code'])) {
                                    $all_ids[] = $p_data['product_id'];
                                } else {
                                    $no_code[] = $p_data['product_id'];
                                }
                            }
                        }
                        $_ignore_list = db_get_fields("SELECT ignore_list FROM ?:brand_ignore_list WHERE brand_id IN (?n)", $_REQUEST['brand_ids']);
                        $ignore_list = array();
                        if (!empty($_ignore_list)) {
                            foreach ($_ignore_list as $i => $i_list) {
                                $ignore_list = array_merge($ignore_list, unserialize($i_list));
                            }
                        }

                        $product_codes = array_diff(array_keys($total_data), $ignore_list);
                        $product_data = db_get_hash_array("SELECT product_id, net_cost, product_code, tracking, import_divider, warehouse_ids FROM ?:products WHERE product_id IN (?n)", 'product_id', $all_ids);
                        $product_codes_data = array();
                        foreach ($product_data as $i => $prod) {
                            if (!empty($prod['product_code'])) {
                                $product_codes_data[$prod['product_code']][$prod['product_id']] = $prod;
                            }
                        }


                        $products_options = fn_get_product_options($all_ids, DESCR_SL, true, true);
                        fn_rebuild_product_options_inventory_multi($all_ids, $products_options, $product_data);
                        $warehouse_inventories = db_get_hash_multi_array("SELECT ?:product_warehouses_inventory.*, ?:product_options_inventory.combination FROM ?:product_warehouses_inventory LEFT JOIN ?:product_options_inventory ON ?:product_options_inventory.combination_hash = ?:product_warehouses_inventory.combination_hash WHERE ?:product_warehouses_inventory.combination_hash != '0' AND ?:product_warehouses_inventory.warehouse_id = ?i AND ?:product_warehouses_inventory.product_id IN (?n)", array('product_id', 'combination_hash'), $_REQUEST['warehouse_id'], array_keys($product_data));
                        $other_inventories = db_get_hash_multi_array("SELECT SUM(amount) AS amount, combination_hash, product_id FROM ?:product_warehouses_inventory WHERE combination_hash != '0' AND warehouse_id != ?i AND product_id IN (?n) GROUP BY combination_hash", array('product_id', 'combination_hash'), $_REQUEST['warehouse_id'], array_keys($product_data));
                        $updated_warehouse_inventories = $new_warehouse_inventories = array();


                        foreach ($total_data as $product_code => $data) {
                            if (!empty($ignore_list) && in_array($product_code, $ignore_list)) {
                                continue;
                            }
                            if (empty($data['data'])) {
                                $missing_products[$product_code] = $data;

                            } elseif (empty($product_codes_data[$product_code])) {
                                $combination_hash = db_get_array("SELECT combination_hash, product_id FROM ?:product_options_inventory WHERE product_code = ?s", $product_code);
                                if (count($combination_hash) != 1 || count($data['data']) > 1 || empty($data['data'][0]['amount'])) {
                                    $missing_products[$product_code] = $data;
                                } else {
                                    db_query("UPDATE ?:product_warehouses_inventory SET amount = ?i WHERE combination_hash = ?i AND warehouse_id = ?i", $data['data'][0]['amount'], $combination_hash[0]['combination_hash'], $_REQUEST['warehouse_id']);
                                    $updated_products[$product_code] = array(
                                        'code' => $product_code,
                                        'data' => $data
                                    );
                                    if (!in_array($combination_hash[0]['product_id'], $in_stock)) {
                                        $in_stock[] = $combination_hash[0]['product_id'];
                                    }
                                    $updated_by_combinations[$combination_hash[0]['product_id']] = (empty($updated_by_combinations[$combination_hash[0]['product_id']])) ? array() : $updated_by_combinations[$combination_hash[0]['product_id']];
                                    $updated_by_combinations[$combination_hash[0]['product_id']][] = $combination_hash[0]['combination_hash'];
                                }

                            } else {
                                $combinations_data = array();
                                foreach ($data['data'] as $i => $variant) {
                                    $option_data = $var_id_tmp = $options_count = $missing_variants = $max = array();
                                    $break = false;
                                    foreach ($product_codes_data[$product_code] as $product_id => $_product) {
                                        $option_data[$product_id] = (empty($option_data[$product_id])) ? array() : $option_data[$product_id];
                                        $options_count[$product_id] = array_keys($products_options[$product_id]);
                                        $option_names = array();
                                        if (!empty($products_options[$product_id])) {
                                            foreach ($products_options[$product_id] as $h => $option) {
                                                $option_names[] = $option['option_name'];
                                            }
                                        }
                                        if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
//                                             fn_print_r($variant);
                                        }
                                        if ($variant['name'] != '' && !empty($products_options[$product_id]) && $_product['tracking'] == 'O' && !empty($variant['amount'])) {
                                            $variants = explode(',', fn_normalize_string($variant['name']));
                                            $prev_numeric = false;
                                            $prev_id = '';
                                            foreach ($variants as $j => $variant_name) {
                                                if (is_numeric($variant_name) && $prev_numeric) {
                                                    $variants[$prev_id] = $variants[$prev_id] . '.' . $variant_name;
                                                    unset($variants[$j]);
                                                    continue;
                                                } elseif (is_numeric($variant_name)) {
                                                    $prev_numeric = true;
                                                }
                                                $prev_id = $j;
                                            }
                                            if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
//                                                 fn_print_r($variants);
                                            }
                                            foreach ($variants as $j => $variant_name) {
                                                $max[$j] = (isset($max[$j])) ? $max[$j] : 0;
                                                $variant_name = fn_format_variant_name($variant_name);
                                                if (!empty($option_names)) {
                                                    foreach ($option_names as $h => $o_name) {
                                                        $variant_name = str_ireplace(fn_strtolower($o_name), '', fn_strtolower($variant_name));
                                                    }
                                                }
                                                if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
//                                                     fn_print_r($variant_name);
                                                }
                                                $variant_found = false;
                                                foreach ($products_options[$product_id] as $k => $opt_data) {
                                                    if (!empty($opt_data['variants'])) {
                                                        foreach ($opt_data['variants'] as $kk => $vr_data) {
                                                            $var_name = fn_format_variant_name($vr_data['variant_name']);
                                                            if (strlen($var_name) > 0 && strpos($variant_name, $var_name) !== false) {
                                                                $prc = round(strlen($var_name)/strlen($variant_name), 2) * 100;
                                                                $var_id_tmp[$j][$opt_data['option_id']][$prc] = $vr_data['variant_id'];
                                                                if ($prc > $max[$j]) {
                                                                    $max[$j] = $prc;
                                                                }
                                                            }
                                                            if (strlen($variant_name) > 0 && strpos($var_name, $variant_name) !== false) {
                                                                $prc = round(strlen($variant_name)/strlen($var_name), 2) * 100;
                                                                $var_id_tmp[$j][$opt_data['option_id']][round(strlen($variant_name)/strlen($var_name), 2) * 100] = $vr_data['variant_id'];
                                                                if ($prc > $max[$j]) {
                                                                    $max[$j] = $prc;
                                                                }
                                                            }
                                                            if ($var_name === $variant_name) {
                                                                if (empty($option_data[$product_id][$opt_data['option_id']])) {
                                                                    $option_data[$product_id][$opt_data['option_id']] = $vr_data['variant_id'];
                                                                    $variant_found = true;
                                                                    break 2;
                                                                } else {
                                                                    $broken_options_products[$product_code] = $data;
                                                                    break 5;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                                if (!$variant_found) {
                                                    $missing_variants[$product_id][] = $j;
                                                } else {
                                                    if (!empty($variant['price']) && $_product['net_cost'] != $variant['price']) {
                                                        $broken_net_cost[$product_code] = $data;
                                                    }
                                                }
                                            }
                                        } elseif (count($data['data']) == 1 && $_product['tracking'] == 'B' && !empty($variant['amount']) && empty($products_options[$product_id])) {
                                            $amount = floor($variant['amount'] / $_product['import_divider']);
//                                             $total_amount = db_get_field("SELECT SUM(amount) FROM ?:product_warehouses_inventory WHERE product_id = ?i AND combination_hash = '0'", $product_id);
                                            db_query("UPDATE ?:product_warehouses_inventory SET amount = ?i WHERE product_id = ?i AND combination_hash = '0' AND warehouse_id = ?i", $amount, $product_id, $_REQUEST['warehouse_id']);
//                                             if ($total_amount <= 0 && $amount > 0) {
//                                                 fn_send_product_notifications($product_id);
//                                             }
                                            $updated_products[$product_code] = array(
                                                'code' => $product_code,
                                                'data' => $data
                                            );
                                            if ($amount > 0) {
                                                $in_stock[] = $product_id;
                                            }
                                            $break = true;
                                        } else {
                                            $broken_options_products[$product_code] = $data;
                                            break 2;
                                        }
                                        if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
//                                             fn_print_r($option_data);
                                        }
                                    }
                                    if ($break) {
                                        break;
                                    }
                                    if (!empty($option_data)) {
                                        $combination_hash = false;
                                        if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
//                                             fn_print_die($options_count, $option_data, $var_id_tmp, $missing_variants, $max);
                                        }
                                        foreach ($option_data as $product_id => $opt_data) {
                                            if (count($options_count[$product_id]) != count($option_data[$product_id]) && !empty($missing_variants[$product_id])) {
                                                $diff = array_diff($options_count[$product_id], array_keys($option_data[$product_id]));
                                                if (!empty($diff)) {
                                                    foreach ($diff as $b => $opt_id) {
                                                        foreach ($missing_variants[$product_id] as $r => $var_num) {
                                                            if (!empty($var_id_tmp[$var_num][$opt_id][$max[$var_num]])) {
                                                                $option_data[$product_id][$opt_id] = $var_id_tmp[$var_num][$opt_id][$max[$var_num]];
                                                            }
                                                        }
                                                    }
                                                }
                                                if (count($options_count[$product_id]) != count($option_data[$product_id])) {
                                                    continue;
                                                }
                                            }
                                            $combination_hash = fn_generate_cart_id($product_id, array('product_options' => $option_data[$product_id]));
                                            $combinations_data[$product_id][$combination_hash]['amount'] = $variant['amount'];
                                            if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
//                                                 fn_print_r('generate', $combination_hash, $product_id, array('product_options' => $option_data[$product_id]));
                                            }
                                            $is_combination = true;
//                                             break;
                                        }
                                        if (empty($combination_hash)) {
                                            $broken_options_products[$product_code] = $data;
                                            break;
                                        }
                                    } else {
                                        $broken_options_products[$product_code] = $data;
                                        break;
                                    }
                                }
                                if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
//                                     fn_print_r($combinations_data);
                                }
                                if (!empty($combinations_data)) {
                                    $ttl_updated = 0;
                                    foreach ($combinations_data as $product_id => $comb_data) {
                                        $ttl_updated += count($comb_data);
                                        $total_amount = $new_amount = 0;
                                        foreach ($warehouse_inventories[$product_id] as $k => $v) {
                                            $total_amount += $warehouse_inventories[$product_id][$k]['amount'];
                                            if (!empty($comb_data[$k])) {
                                                $warehouse_inventories[$product_id][$k]['amount'] = $comb_data[$k]['amount'];
                                                unset($comb_data[$k]);
                                            } else {
                                                $warehouse_inventories[$product_id][$k]['amount'] = 0;
                                            }
                                            $new_amount += $warehouse_inventories[$product_id][$k]['amount'];
                                            $tmp = $warehouse_inventories[$product_id][$k];
                                            unset($tmp['combination']);
                                            $new_warehouse_inventories[] = $tmp;
                                        }
//                                         if (($total_amount <= 0) && ($new_amount > 0)) {
//                                             fn_send_product_notifications($product_id);
//                                         }
                                        $updated_warehouse_inventories[$product_id] = $warehouse_inventories[$product_id];
                                        if (!empty($comb_data)) {
                                            $broken_options_products[$product_code] = $data;
                                        } else {
                                            $updated_products[$product_code] = array(
                                                'code' => $product_code,
                                                'data' => $data
                                            );
                                            $in_stock[] = $product_id;
                                        }
                                    }
                                    if ($ttl_updated != count($data['data'])) {
//                                         $broken_options_products[$product_code] = $data;
                                    }
                                }
                            }
                        }

                        if (!empty($new_warehouse_inventories)) {
                            db_query("REPLACE ?:product_warehouses_inventory ?m", $new_warehouse_inventories);
                        }

                        $option_exceptions = array();
                        if (!empty($updated_warehouse_inventories)) {
                            db_query("DELETE FROM ?:product_options_exceptions WHERE product_id IN (?n)", array_keys($updated_warehouse_inventories));
                            $features = db_get_hash_single_array("SELECT a.feature_id, option_id FROM ?:product_options AS a INNER JOIN ?:product_features ON ?:product_features.feature_id = a.feature_id", array('option_id', 'feature_id'));
                            $feature_variants = db_get_hash_single_array("SELECT feature_variant_id, variant_id FROM ?:product_option_variants", array('variant_id', 'feature_variant_id'));
                            foreach ($updated_warehouse_inventories as $pr_id => $combinations) {
                                $option_variants_avail = $option_variants = array();
                                foreach ($combinations as $hash => $wh_combination) {
                                    $options_array = fn_get_product_options_by_combination($wh_combination['combination']);
                                    if ($combinations[$hash]['amount'] < 1 && $other_inventories[$pr_id][$hash]['amount'] < 1) {
                                        $option_exceptions[] = array(
                                            'product_id' => $pr_id,
                                            'combination' => serialize($options_array)
                                        );
                                    } else {
                                        foreach ($options_array as $option_id => $variant_id) {
                                            if (empty($option_variants_avail[$option_id]) || !in_array($variant_id, $option_variants_avail[$option_id])) {
                                                $option_variants_avail[$option_id][] = $option_variants[] = $variant_id;
                                            }
                                        }
                                    }
                                }
                                if (!empty($option_variants_avail)) {
                                    $features_data = array();
                                    foreach ($option_variants_avail as $option_id => $variants) {
                                        if (!empty($features[$option_id])) {
                                            $features_data[$features[$option_id]] = array();
                                            if (!empty($variants)) {
                                                foreach ($variants as $j => $variant_id) {
                                                    if (!empty($feature_variants[$variant_id])) {
                                                        $features_data[$features[$option_id]][] = $feature_variants[$variant_id];
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if (!empty($features_data)) {
                                        $add_new_variant = array();
                                        fn_update_product_features_value($pr_id, $features_data, $add_new_variant, CART_LANGUAGE);
                                    }
                                }
                            }
                        }

                        $out_of_stock = array_diff($all_ids, $in_stock);
                        db_query("UPDATE ?:products SET updated_timestamp = ?i WHERE product_id IN (?n)", time(), $in_stock);
                        if (!empty($out_of_stock)) {
                            db_query("UPDATE ?:product_warehouses_inventory SET amount = '0' WHERE product_id IN (?n) AND warehouse_id = ?i", $out_of_stock, $_REQUEST['warehouse_id']);
                            db_query("DELETE FROM ?:product_options_exceptions WHERE product_id IN (?n)", $out_of_stock);
                            $all_combinations = db_get_hash_multi_array("SELECT combination_hash, combination, product_id FROM ?:product_options_inventory WHERE product_id IN (?n)", array('product_id', 'combination_hash'), $out_of_stock);
                            foreach ($out_of_stock as $os_i => $pr_id) {
                                if (!empty($all_combinations[$pr_id])) {
                                    foreach ($all_combinations[$pr_id] as $t => $comb_dt) {
                                        if (empty($other_inventories[$pr_id]) || $other_inventories[$pr_id][$comb_dt['combination_hash']]['amount'] < 1) {
                                            $options_array = fn_get_product_options_by_combination($comb_dt['combination']);
                                            $option_exceptions[] = array(
                                                'product_id' => $pr_id,
                                                'combination' => serialize($options_array)
                                            );
                                        }
                                    }
                                }
                            }
                        }

                        if (!empty($updated_by_combinations)) {
                            $all_combinations = db_get_hash_multi_array("SELECT combination_hash, combination, product_id FROM ?:product_options_inventory WHERE product_id IN (?n)", array('product_id', 'combination_hash'), array_keys($updated_by_combinations));
                            db_query("DELETE FROM ?:product_options_exceptions WHERE product_id IN (?n)", array_keys($updated_by_combinations));
                            foreach ($updated_by_combinations as $pr_id => $combs) {
                                $out = array_diff(array_keys($all_combinations[$pr_id]), $combs);
                                if (!empty($out)) {
                                    db_query("UPDATE ?:product_warehouses_inventory SET amount = '0' WHERE combination_hash IN (?n) AND warehouse_id = ?i", $out, $_REQUEST['warehouse_id']);
                                    foreach ($out as $t => $comb_hash) {
                                        if ($other_inventories[$pr_id][$comb_hash]['amount'] < 1) {
                                            $options_array = fn_get_product_options_by_combination($all_combinations[$pr_id][$comb_hash]['combination']);
                                            $option_exceptions[] = array(
                                                'product_id' => $pr_id,
                                                'combination' => serialize($options_array)
                                            );
                                        }
                                    }
                                }
                            }
                        }
                        if (!empty($option_exceptions)) {
                            db_query("REPLACE INTO ?:product_options_exceptions ?m", $option_exceptions);
                        }
                    }
                }
            } elseif (empty($_REQUEST['brand_ids'])) {
                fn_set_notification('E', __('error'), __('error_brand_undefined'));
            } elseif (empty($file)) {
                fn_set_notification('E', __('error'), __('error_exim_no_file_uploaded'));
            }
            $ignored_products = array();
            if (!empty($ignore_list)) {
                foreach ($ignore_list as $i => $pcode) {
                    $ignored_products[$pcode] = !empty($total_data[$pcode]) ? $total_data[$pcode] : array('product' => __('missing'));
                }
            }

            FeaturesCache::generate(CART_LANGUAGE);

            Registry::get('view')->assign('warehouse_id', $_REQUEST['warehouse_id']);
            Registry::get('view')->assign('out_of_stock', count($out_of_stock) + count($no_code));
            Registry::get('view')->assign('in_stock', count($in_stock));
            Registry::get('view')->assign('brand_ids', implode(',', $_REQUEST['brand_ids']));
            Registry::get('view')->assign('calculate', true);
            Registry::get('view')->assign('total', count($total_data));
            Registry::get('view')->assign('ignore_list', $ignored_products);
            Registry::get('view')->assign('trash', $trash);
            Registry::get('view')->assign('missing_products', $missing_products);
            Registry::get('view')->assign('updated_products', $updated_products);
            Registry::get('view')->assign('broken_options_products', $broken_options_products);
            Registry::get('view')->assign('broken_net_cost_products', $broken_net_cost);
            fn_set_notification('N', __('notice'), __('stocks_updated_successfully'));
        }

        Registry::get('view')->assign('brands', fn_development_get_brands());
        Registry::get('view')->display('addons/development/components/supplier_stocks.tpl');
        exit;
    }

    if ($mode == 'ignore_products') {
        if (!empty($_REQUEST['brand_ids'])) {
            $_REQUEST['brand_ids'] = explode(',', $_REQUEST['brand_ids']);
            foreach ($_REQUEST['brand_ids'] as $i => $brand_id) {
                $ignore_list = db_get_field("SELECT ignore_list FROM ?:brand_ignore_list WHERE brand_id = ?i", $brand_id);
                $ignore_list = !empty($ignore_list) ? unserialize($ignore_list) : array();
                $ignore_list = array_merge($ignore_list, $_REQUEST['product_codes']);
                $_data = array(
                    'brand_id' => $brand_id,
                    'ignore_list' => serialize($ignore_list)
                );
                db_query("REPLACE INTO ?:brand_ignore_list ?e", $_data);
            }
            fn_set_notification('N', __('notice'), __('added_to_ignore_list'));
        }
        exit;
    }

    if ($mode == 'watch_products') {
        if (!empty($_REQUEST['brand_ids'])) {
            $_REQUEST['brand_ids'] = explode(',', $_REQUEST['brand_ids']);
            foreach ($_REQUEST['brand_ids'] as $i => $brand_id) {
                $ignore_list = db_get_field("SELECT ignore_list FROM ?:brand_ignore_list WHERE brand_id = ?i", $brand_id);
                $ignore_list = !empty($ignore_list) ? unserialize($ignore_list) : array();
                if (!empty($ignore_list)) {
                    foreach ($ignore_list as $i => $pcode) {
                        if (in_array($pcode, $_REQUEST['product_codes'])) {
                            unset($ignore_list[$i]);
                        }
                    }
                    $_data = array(
                        'brand_id' => $brand_id,
                        'ignore_list' => serialize($ignore_list)
                    );
                    db_query("REPLACE INTO ?:brand_ignore_list ?e", $_data);
                    fn_set_notification('N', __('notice'), __('updated_ignore_list'));
                }
            }
        }
        exit;
    }

    if ($mode == 'update_savings_groups') {
        if (!empty($_REQUEST['savings_groups_data'])) {
            $groups = array();
            db_query("DELETE FROM ?:savings_groups");
            foreach ($_REQUEST['savings_groups_data']['groups'] as $k => $group_data) {
                if ($group_data['amount'] != '') {
                    $groups[] = array(
                        'group_id' => $k,
                        'amount' => $group_data['amount'],
                        'usergroup_id' => $group_data['usergroup_id']
                    );
                }
            }
            foreach ($_REQUEST['savings_groups_data']['add_groups'] as $k => $group_data) {
                if ($group_data['amount'] != '') {
                    $groups[] = array(
                        'group_id' => $k,
                        'amount' => $group_data['amount'],
                        'usergroup_id' => $group_data['usergroup_id']
                    );
                }
            }
            if (!empty($groups)) {
                db_query("REPLACE INTO ?:savings_groups ?m", $groups);
            }
        }

        $suffix = "development.saving_system";
    }

    if ($mode == 'check_emails') {
        if (!empty($_REQUEST['subscriber_ids'])) {
            $subscribers = db_get_hash_single_array("SELECT email, subscriber_id FROM ?:subscribers WHERE subscriber_id IN (?n)", array('email', 'subscriber_id'), $_REQUEST['subscriber_ids']);
            foreach ($subscribers as $email => $_id) {
                $result = fn_email_exist(array($email));
                if ($result[$email] == true) {
                    db_query("UPDATE ?:subscribers SET status = 'C' WHERE subscriber_id = ?i", $subscribers[$email]);
                } else {
                    db_query("UPDATE ?:subscribers SET status = 'L' WHERE subscriber_id = ?i", $subscribers[$email]);
                }
            }
        }
        if (!empty($_REQUEST['redirect_url'])) {
            return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url']);
        } else {
            $suffix = "subscribers.manage";
        }
    }

    if ($mode == 'find_state_match') {
        header('Content-Type: application/json');
        $state = fn_find_state_match($_REQUEST['state']);
        fn_echo(json_encode($state['code']));
        exit;
    }

    return array(CONTROLLER_STATUS_OK, $suffix);
}

if ($mode == 'calculate_balance') {

    $params = $_REQUEST;
    if (!empty($params['length']) && !empty($params['relation'])) {
        if (strtoupper($params['relation']) == 'HL') {
            $result = $params['length']/2 - $params['points'] * 1/8;
        } else {
            $result = $params['length']/2 + $params['points'] * 1/8;
        }
        $params['result'] = $result * 2.54;
    }
    Registry::get('view')->assign('params', $params);

} elseif ($mode == 'saving_system') {

    Registry::get('view')->assign('saving_data', db_get_hash_array("SELECT * FROM ?:savings_groups ORDER BY amount ASC", 'group_id'));
    Registry::get('view')->assign('usergroups', fn_get_usergroups('C'));
} elseif ($mode == 'supplier_stocks') {

    Registry::get('view')->assign('brands', fn_development_get_brands());

} elseif ($mode == 'rebuild_combinations') {
    $product_ids = db_get_fields("SELECT DISTINCT(product_id) FROM ?:product_options_inventory");
    if (!empty($product_ids)) {
        foreach ($product_ids as $i => $product_id) {
            fn_rebuild_product_options_inventory($product_id);
        }
    }
    exit;
} elseif ($mode == 'colors') {
    $product_ids = db_get_fields("SELECT DISTINCT(a.product_id) FROM ?:product_options_inventory AS a LEFT JOIN ?:images_links AS b ON a.combination_hash = b.object_id AND b.object_type = 'product_option' WHERE b.pair_id IS NOT NULL");
    fn_print_die($product_ids);
} elseif ($mode == 'optimize_option_images') {
    $ids = db_get_hash_array("SELECT a.*, b.image_path, c.image_path AS detailed_path, p.product_id, po.product_id AS option_product_id FROM ?:images_links AS a LEFT JOIN ?:images AS b ON a.image_id = b.image_id LEFT JOIN ?:images AS c ON a.detailed_id = c.image_id LEFT JOIN ?:products AS p ON p.product_id = a.object_id AND a.object_type = 'product' LEFT JOIN ?:product_option_variants AS v ON v.variant_id = a.object_id AND a.object_type = 'variant_additional' LEFT JOIN ?:product_options AS po ON po.option_id = v.option_id", 'pair_id');
        fn_print_die($ids);

//     $to_delete = array();
//     foreach ($ids as $obj_id => $obj_data) {
//         $image_path = $detailed_path = '';
//         if ($obj_data['object_type'] == 'product') {
//             $image_path = substr($obj_data['image_path'], 0, strrpos($obj_data['image_path'], '.'));
//             $detailed_path = substr($obj_data['detailed_path'], 0, strrpos($obj_data['detailed_path'], '.'));
//             foreach ($ids as $_obj_id => $_obj_data) {
//                 if ($_obj_data['object_type'] == 'variant_additional' && $_obj_data['option_product_id'] == $obj_data['product_id'] && ((!empty($_obj_data['image_path']) && strpos($_obj_data['image_path'], $image_path) !== false) || (!empty($_obj_data['detailed_path']) && strpos($_obj_data['detailed_path'], $detailed_path) !== false))) {
//                     $to_delete[] = $_obj_data;
//                 }
//             }
//         }
//
//     }
//     if (!empty($to_delete)) {
//         foreach ($to_delete as $i => $d_data) {
//             //fn_delete_image_pair($d_data['pair_id'], 'variant_additional');
//         }
//     }
} elseif ($mode == 'check_options') {
    $product_ids = db_get_fields("SELECT DISTINCT(a.product_id) FROM ?:product_options AS a LEFT JOIN ?:products_categories AS b ON a.product_id = b.product_id WHERE b.category_id = '255'");
    if (!empty($product_ids)) {
        db_query("UPDATE ?:products SET options_type = 'S' WHERE product_id IN (?n)", $product_ids);
//         foreach ($product_ids as $i => $product_id) {
//             fn_rebuild_product_options_inventory($product_id);
//         }
    }
    exit;
} elseif ($mode == 'fix_tracking') {
    $ids = db_get_fields("SELECT DISTINCT(product_id) FROM ?:product_options WHERE inventory = 'Y'");
    if (!empty($ids)) {
        db_query("UPDATE ?:products SET tracking = 'O' WHERE product_id IN (?n)", $ids);
    }
    exit;
} elseif ($mode == 'show_colors') {
    $ids = db_get_hash_array("SELECT * FROM ?:product_options AS a LEFT JOIN ?:product_options_descriptions AS b ON a.option_id = b.option_id AND b.lang_code = 'ru' WHERE b.option_name = 'Цвет'", 'option_id');
    if (!empty($ids)) {
        db_query("UPDATE ?:product_options SET show_on_catalog = 'Y' WHERE option_id IN (?n)", array_keys($ids));
    }
    exit;
} elseif ($mode == 'check_exceptions') {
    $products_exceptions = db_get_hash_multi_array("SELECT * FROM ?:product_options_exceptions", array('product_id', 'exception_id'));
    $options_count = db_get_hash_single_array("SELECT COUNT(option_id) as count, product_id FROM ?:product_options GROUP BY product_id", array('product_id', 'count'));
    $to_delete = array();
    if (!empty($products_exceptions)) {
        foreach ($products_exceptions as $product_id => $exps) {
            foreach ($exps as $i => $exp) {
                $combination = unserialize($exp['combination']);
                if (empty($options_count[$exp['product_id']]) && count($combination) != $options_count[$exp['product_id']]) {
                    $to_delete[$i] = $exp;
                }
            }
        }
    }
    if (!empty($to_delete)) {
        db_query("DELETE FROM ?:product_options_exceptions WHERE exception_id IN (?n)", array_keys($to_delete));
    }
    exit;
} elseif ($mode == 'memcached_stats') {
    fn_print_die(Memcache::instance()->call('stats'));
} elseif ($mode == 'get_memcached') {
    fn_print_die(Memcache::instance()->call('getAll', $_REQUEST['key']));
} elseif ($mode == 'fix_po') {
    $options = db_get_array("SELECT * FROM ?:product_options_descriptions WHERE description LIKE '%page_id=58%'");
    if (!empty($options)) {
        foreach ($options as $i => $opt) {
            $desc = str_replace('page_id=58', 'page_id=41', $opt['description']);
//            db_query("UPDATE ?:product_options_descriptions SET description = ?s WHERE option_id = ?i AND lang_code = ?s", $desc, $opt['option_id'], $opt['lang_code']);
        }
    }
    fn_print_die($options);
} elseif ($mode == 'apply_r_go') {
    $cat_ids = array(
        'Ручка' => array(
            '344' => '269',
            '345' => '269',
            '346' => '269',
            '409' => '353',
        ),
        'Размер' => array(
            '373' => '141',
            '374' => '141',
            '443' => '141',
            '377' => '141',
            '376' => '141',
            '375' => '141',
            '378' => '141',
            '379' => '141',
            '347' => '141',
            '345' => '141',
            '382' => '141',
            '383' => '141',
            '444' => '141',
            '448' => '141',
            '449' => '141',
            '446' => '141',
            '381' => '141',
            '390' => '209',
            '391' => '209',
            '456' => '209',
            '457' => '209',
            '455' => '209',
            '392' => '209',
            '393' => '209',
            '459' => '209',
            '453' => '209',
            '460' => '209',
            '452' => '209',
            '450' => '209',
            '461' => '209',
            '451' => '209',
            '454' => '209',
            '355' => '112',
            '441' => '112',
            '440' => '112',
            '413' => '2',
        ),
    );
    $params['cid'] = 0;
    $params['extend'] = array('categories', 'description');
    $params['subcats'] = 'Y';

    list($products, $search) = fn_get_products($params);
    fn_gather_additional_products_data($products, array(
        'get_icon' => false,
        'get_detailed' => false,
        'get_additional' => false,
        'get_options' => true,
        'get_discounts' => false,
        'get_features' => false
    ));
    if (!empty($products)) {
        foreach ($products as $i => $product) {
            if (!empty($product['product_options'])) {
                foreach ($product['product_options'] as $j => $option)  {
                    if (in_array($option['option_name'], array_keys($cat_ids)) && !empty($option['product_id'])) {
                        foreach ($cat_ids[$option['option_name']] as $cid => $goid) {
                            if (in_array($cid, $product['category_ids'])) {
                                fn_delete_product_option($option['option_id'], $product['product_id']);
                                db_query("REPLACE INTO ?:product_global_option_links (option_id, product_id) VALUES(?i, ?i)", $goid, $product['product_id']);
                                // [tennishouse]
                                fn_update_product_tracking($product['product_id']);
                                // [tennishouse]

                                if (fn_allowed_for('ULTIMATE')) {
                                    fn_ult_share_product_option($goid, $product['product_id']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    fn_print_die('done');
} elseif ($mode == 'fix_babolat_variants') {
    $variants = db_get_hash_single_array("SELECT variant_id, code_suffix FROM ?:product_option_variants WHERE code_suffix != ''", array('variant_id', 'code_suffix'));
    foreach ($variants as $variant_id => $code) {
//        db_query("UPDATE ?:product_option_variants SET code_suffix = ?s WHERE variant_id = ?i", (strlen($code) == 3 ? '-' . $code : $code), $variant_id);
    }
    exit;
} elseif ($mode == 'clone_catalog') {
//     $params = array();
//
//     list($products, $search) = fn_get_products(array());
//     foreach ($products as $i => $product) {
//         fn_clone_product($product['product_id']);
//     }
//     fn_print_die('Catalog cloned');
} elseif ($mode == 'test_curl') {
    $extra = array(
//        'request_timeout' => 2
        'test' => true
    );
    // 'https://news.yandex.ru/hardware.rss'
    // 'http://www.championat.com/xml/rss_tennis-article.xml'
    $response = Http::get('https://news.yandex.ru/hardware.rss', array(), $extra);
    fn_print_die($response);
} elseif ($mode == 'add_reward_points') {

//     $reward_points = fn_get_reward_points(0, GLOBAL_REWARD_POINTS, array('0', '1'), 1);
//     $data = db_get_hash_array("SELECT order_id, user_id, subtotal, timestamp FROM ?:orders WHERE user_id != '0' AND status = 'C'", 'order_id');
//     $reason = array(
//         'to' => 'C',
//         'from' => 'O'
//     );
//     if (!empty($data)) {
//         foreach ($data as $order_id => $_dt) {
//             $reward = floor($_dt['subtotal'] / $reward_points['round_to']) * $reward_points['round_to'] * $reward_points['amount'] / 100;
//             if ($reward > 0) {
//                 $reason['order_id'] = $order_id;
//                 fn_save_user_additional_data(POINTS, fn_get_user_additional_data(POINTS, $_dt['user_id']) + $reward, $_dt['user_id']);
//
//                 $change_points = array(
//                     'user_id' => $_dt['user_id'],
//                     'amount' => $reward,
//                     'timestamp' => $_dt['timestamp'],
//                     'action' => CHANGE_DUE_ORDER,
//                     'reason' => serialize($reason)
//                 );
//
//                 if ($reward > 0) {
//                     $now = getdate($_dt['timestamp']);
//                     $change_points['expire'] = mktime(0, 0, 0, $now['mon'], $now['mday'] + 1, $now['year'] + 1);
//                 }
//                 db_query("REPLACE INTO ?:reward_point_changes ?e", $change_points);
//             }
//         }
//     }
    exit;
} elseif ($mode == 'add_usergroups') {

//     $saving_data = db_get_hash_array("SELECT * FROM ?:savings_groups ORDER BY amount ASC", 'group_id');
//     if (!empty($saving_data)) {
//         $data = db_get_fields("SELECT DISTINCT user_id FROM ?:orders WHERE user_id != '0' AND status = 'C'");
//         if (!empty($data)) {
//             foreach ($data as $i => $user_id) {
//                 if (!empty($user_id)) {
//                     $orders_total = db_get_field("SELECT SUM(total) FROM ?:orders WHERE user_id = ?i AND status = 'C'", $user_id);
//                     $usergroup_ids = array();
//                     foreach ($saving_data as $i => $group_data) {
//                         $usergroup_ids[] = $group_data['usergroup_id'];
//                         if ($orders_total > $group_data['amount']) {
//                             $usergroup_id = $group_data['usergroup_id'];
//                         }
//                     }
//                     if (!empty($usergroup_ids)) {
//                         foreach ($usergroup_ids as $i => $ug_id) {
//                             fn_change_usergroup_status('F', $user_id, $ug_id);
//                         }
//                     }
//                     if (!empty($usergroup_id)) {
//                         fn_change_usergroup_status('A', $user_id, $usergroup_id);
//                     }
//                 }
//             }
//         }
//     }
    exit;
} elseif ($mode == 'reset_memcached') {
    Memcache::instance()->call('flush');
    exit;
} elseif ($mode == 'fix_phones') {
    $phones = db_get_array("SELECT profile_id, s_phone FROM ?:user_profiles WHERE s_phone != ''");
    foreach ($phones as $i => $dt) {
        $dt['s_phone'] = preg_replace('/[^0-9]/', '', $dt['s_phone']);
        if ($dt['s_phone'][0] == '8') {
            $dt['s_phone'] = str_replace('8', '7', $dt['s_phone']);
        }
        if ($dt['s_phone'][0] == '9') {
            $dt['s_phone'] = '7' . $dt['s_phone'];
        }
        if ($dt['s_phone'][0] == '7') {
            $dt['s_phone'] = '+' . $dt['s_phone'];
        }
        if (strlen($dt['s_phone']) == 12) {
            $dt['s_phone'] = substr($dt['s_phone'], 0, 2) . '(' . substr($dt['s_phone'], 2, 3) . ')' . substr($dt['s_phone'], 5, 3) . '-' . substr($dt['s_phone'], 8, 2) . '-' . substr($dt['s_phone'], 10);
        }
        db_query("UPDATE ?:user_profiles SET s_phone = ?s WHERE profile_id = ?i", $dt['s_phone'], $dt['profile_id']);
    }
    exit;
} elseif ($mode == 'regenerate_features') {
    $product_ids = db_get_fields("SELECT DISTINCT(product_id) FROM ?:product_options_inventory");
    foreach ($product_ids as $i => $pr_id) {
        fn_update_combinations($pr_id);
    }
    exit;
} elseif ($mode == 'rename_products') {
    $names = array(
//         '344' => array('Теннисная ракетка ' => ''),
//         '345' => array('Теннисная ракетка ' => ''),
//         '346' => array('Теннисная ракетка ' => ''),
//         '409' => array('Детская теннисная ракетка ' => ''),
//         '303' => 'Кроссовки мужские',
//         '304' => 'Кроссовки женские',
//         '439' => 'Детские кроссовки',
//         '265' => array('Струна ' => ''),
//         '398' => 'Сумка',
//         '399' => 'Сумка',
//         '400' => 'Сумка',
//         '401' => 'Сумка',
//         '402' => 'Сумка',
//         '403' => 'Рюкзак',
//         '404' => 'Спортивная сумка',
//         '431' => 'Сумка',
//         '462' => 'Дорожная сумка',
//         '464' => 'Портфель',
//         '266' => array('Мячи ' => ''),
//         '312' => 'Обмотки',
//         '313' => 'Грип',
//         '315' => 'Виброгаситель',
    );
    $params['subcats'] = 'Y';
    foreach ($names as $cid => $name) {
        $params['cid'] = $cid;
        list($products, $search) = fn_get_products($params);
        foreach ($products as $i => $product) {
            $product['product'] = str_replace(array(' женск.', ' жен.', ' муж.', ' мужск.', ' детск.'), array('', '', '', '', ''), $product['product']);
            if (strpos($product['product'], $name) === false) {
                $new_name = $product['product'];
                foreach ($name as $old => $new) {
                    $new_name = str_replace($old, $new, $new_name);
                }
                db_query("UPDATE ?:product_descriptions SET product = ?s WHERE product_id = ?i AND lang_code = ?s", $new_name, $product['product_id'], CART_LANGUAGE);
            }
        }
    }
    exit;
} elseif ($mode == 'add_default_text') {
    db_query("UPDATE ?:product_options_descriptions SET default_text = ?s WHERE option_name = ?s", 'Выберите цвет', 'Цвет');
    exit;
} elseif ($mode == 'get_likes') {
    $result = db_get_hash_array("SELECT product_id, COUNT(product_id) as likes FROM ?:order_details GROUP BY product_id", 'product_id');
    $wishlists = db_get_hash_array("SELECT product_id, COUNT(product_id) as likes FROM ?:user_session_products WHERE type = 'W' GROUP BY product_id", 'product_id');
    if (!empty($wishlists)) {
        foreach ($wishlists as $prod_id => $dt) {
            if (!empty($result[$prod_id])) {
                $result[$prod_id]['likes'] += $dt['likes'];
            } else {
                $result[$prod_id] = $dt;
            }
        }
    }
    foreach ($result as $prod_id => $dt) {
        db_query("UPDATE ?:products SET likes = ?i WHERE product_id = ?i", $dt['likes'], $prod_id);
    }
    exit;
} elseif ($mode == 'cities') {

    $data = array(
        'token' => '5817c1e90a69de97408b4569',
        'contentType' => 'city'
    );
    $response = Http::get('http://kladr-api.ru/api.php', $data);
    fn_print_die($response);

    exit;
} elseif ($mode == 'generate_warehouse_inventory') {

    $products = db_get_array("SELECT product_id, amount FROM ?:products");
    $option_inventory = db_get_hash_multi_array("SELECT * FROM ?:product_options_inventory", array('product_id', 'combination_hash'));

    $data = array();
    foreach ($products as $i => $prod) {
        $brand_warehouse_ids = array(TH_WAREHOUSE_ID);
        $product_features = fn_get_product_features_list($prod, 'C');
        if (!empty($product_features[BRAND_FEATURE_ID]['variant_id'])) {
            $_brand_warehouse_ids = db_get_fields("SELECT warehouse_id FROM ?:warehouse_brands WHERE brand_id = ?i", $product_features[BRAND_FEATURE_ID]['variant_id']);
            if (!empty($_brand_warehouse_ids)) {
                $brand_warehouse_ids = array_merge($brand_warehouse_ids, $_brand_warehouse_ids);
            }
        }
        db_query("UPDATE ?:products SET warehouse_ids = ?s WHERE product_id = ?i", implode(',', $brand_warehouse_ids), $prod['product_id']);
        foreach ($brand_warehouse_ids as $k => $wh_id) {
            $data[] = array(
                'warehouse_hash' => fn_generate_cart_id($prod['product_id'], array('warehouse_id' => $wh_id)),
                'warehouse_id' => $wh_id,
                'product_id' => $prod['product_id'],
                'combination_hash' => 0,
                'amount' => ($wh_id == BABOLAT_WAREHOUSE_ID) ? $prod['amount'] : 0
            );
        }
        if (!empty($option_inventory[$prod['product_id']])) {
            foreach ($option_inventory[$prod['product_id']] as $k => $comb) {
                $options = fn_get_product_options_by_combination($comb['combination']);
                foreach ($brand_warehouse_ids as $kk => $wh_id) {
                    $data[] = array(
                        'warehouse_hash' => fn_generate_cart_id($prod['product_id'], array('product_options' => $options, 'warehouse_id' => $wh_id)),
                        'warehouse_id' => $wh_id,
                        'product_id' => $prod['product_id'],
                        'combination_hash' => $comb['combination_hash'],
                        'amount' => ($wh_id == BABOLAT_WAREHOUSE_ID) ? $comb['amount'] : 0
                    );
                }
            }
        }
    }
    if (!empty($data)) {
        db_query("REPLACE ?:product_warehouses_inventory ?m", $data);
    }

    fn_echo('Done');
    exit;
} elseif ($mode == 'fix_rp') {

    $now = getdate(TIME);
    $expire = mktime(0, 0, 0, $now['mon'] - 2, $now['mday'], $now['year']);
    $res = db_get_array("SELECT order_id, subtotal, user_id FROM ?:orders WHERE subtotal >= 1000 AND user_id != '0' AND timestamp > ?i AND status IN ('C', 'E')", $expire);
    $rp = db_get_hash_multi_array("SELECT * FROM ?:reward_point_changes", array('user_id'));
    $to_add = array();
    foreach ($res as $i => $o_dt) {
        $add = false;
        if (!empty($rp[$o_dt['user_id']])) {
            $added = false;
            foreach ($rp[$o_dt['user_id']] as $k => $rp_data) {
                $reason = unserialize($rp_data['reason']);
                if ($rp_data['action'] == 'O' && $reason['order_id'] == $o_dt['order_id']) {
                    $added = true;
                    break;
                }
            }
            if (!$added) {
                $add = true;
            }
        } else {
            $add = true;
        }

        if ($add) {
            $new = $o_dt;
            $new['rp'] = $rp[$o_dt['user_id']];
            $to_add[] = $new;
        }
    }
    fn_print_die($to_add);
    if (!empty($to_add)) {
        foreach ($to_add as $i => $_data) {
            $reward_points = fn_get_reward_points(0, GLOBAL_REWARD_POINTS, fn_define_usergroups(array('user_id' => $_data['user_id'], 'user_type' => 'C'), 'C'), 1);

            if (isset($reward_points['amount'])) {
                $reward_points['coefficient'] =(Registry::get('addons.reward_points.points_with_discounts') == 'Y' && $reward_points['amount_type'] == 'P' && isset($product['discounted_price'])) ? $product['discounted_price'] / $product['price'] : 1;

                if ($reward_points['amount_type'] == 'P') {
                    $reward_points['amount'] = floor($_data['subtotal'] / $reward_points['round_to'] ) * $reward_points['round_to'] * $reward_points['amount'] / 100;
                }

                $reward_points['raw_amount'] = $reward_points['coefficient'] * $reward_points['amount'];

                $reward_points['amount'] = round($reward_points['raw_amount']);
                $to_add[$i]['to_add'] = $reward_points['amount'];
            }
            if (!empty($to_add[$i]['to_add'])) {
//                 fn_change_user_points($to_add[$i]['to_add'], $_data['user_id'], serialize(array('order_id' => $_data['order_id'],'to' => 'C','from' => 'O')), CHANGE_DUE_ORDER);
            }
        }
    }

    fn_echo('Done');
    exit;
} elseif ($mode == 'enable_reviews') {
    $params['extend'] = array('categories');

    list($products, $search) = fn_get_products($params);
    $result = array();
    foreach ($products as $i => $prod) {
        if ($prod['product_type'] != 'P') {
            $result[$prod['product_id']] = 'D';
        } else {
            $result[$prod['product_id']] = fn_check_category_discussion(explode('/', $prod['id_path']));
        }
    }
    foreach ($result as $prod_id => $dt) {
        $discussion = array(
            'object_type' => 'P',
            'object_id' => $prod_id,
            'type' => $dt,
            'company_id' => 1
        );

//         fn_update_discussion($discussion);
    }
    exit;
} elseif ($mode == 'enable_comparison') {
    $params['extend'] = array('categories');

    list($products, $search) = fn_get_products($params);
    $result = array();
    foreach ($products as $i => $prod) {
        $result[$prod['product_id']] = fn_check_category_comparison(explode('/', $prod['id_path']));
    }
    foreach ($result as $prod_id => $dt) {
        db_query("UPDATE ?:products SET feature_comparison = ?s WHERE product_id = ?i", $dt, $prod_id);
    }
    exit;
} elseif ($mode == 'check_inventory') {
    $result = db_get_hash_multi_array("SELECT prods.product_id, inv.amount, descr.product FROM ?:products AS prods INNER JOIN ?:product_warehouses_inventory AS inv ON inv.product_id = prods.product_id AND inv.warehouse_id = ?i AND inv.amount > 0 LEFT JOIN ?:product_descriptions AS descr ON descr.product_id = prods.product_id AND descr.lang_code = 'ru'", array('product_id'), $_REQUEST['warehouse_id']);
    fn_print_die($result);
    exit;
} elseif ($mode == 'apply_parent_option_id') {
    $opt_ids = db_get_fields("SELECT ?:product_options_descriptions.option_id FROM ?:product_options_descriptions LEFT JOIN ?:product_options ON ?:product_options.option_id = ?:product_options_descriptions.option_id WHERE ?:product_options_descriptions.option_name = 'Цвет' AND ?:product_options.product_id != '0'");
    db_query("UPDATE ?:product_options SET parent_option_id = '139' WHERE option_id IN (?n)", $opt_ids);
    exit;
} elseif ($mode == 'generate_alt_text') {
    $update = array();
    $product_images = db_get_hash_multi_array("SELECT * FROM ?:images_links WHERE object_type = 'product'", array('object_id', 'pair_id'));
    $product_names = db_get_hash_array("SELECT ?:product_descriptions.product, ?:products.product_id, ?:products.product_code FROM ?:products LEFT JOIN ?:product_descriptions ON ?:products.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = 'ru'", 'product_id');
    foreach ($product_images as $prod_id => $pairs) {
        foreach ($pairs as $pair_id => $pair) {
            if (!empty($pair['image_id'])) {
                $update[] = array(
                    'object_id' => $pair['image_id'],
                    'description' => $product_names[$prod_id]['product'] . (!empty($product_names[$prod_id]['product_code']) ? ' ' . $product_names[$prod_id]['product_code'] : ''),
                    'lang_code' => 'ru',
                    'object_holder' => 'images'
                );
            }
            if (!empty($pair['detailed_id'])) {
                $update[] = array(
                    'object_id' => $pair['detailed_id'],
                    'description' => $product_names[$prod_id]['product'] . (!empty($product_names[$prod_id]['product_code']) ? ' ' . $product_names[$prod_id]['product_code'] : ''),
                    'lang_code' => 'ru',
                    'object_holder' => 'images'
                );
            }
        }
    }
    $variant_images = db_get_hash_multi_array("SELECT ?:images_links.*, ?:product_option_variants.option_id, ?:product_options.product_id FROM ?:images_links LEFT JOIN ?:product_option_variants ON ?:product_option_variants.variant_id = ?:images_links.object_id LEFT JOIN ?:product_options ON ?:product_options.option_id = ?:product_option_variants.option_id WHERE ?:images_links.object_type = 'variant_additional'", array('product_id', 'pair_id'));
    $variant_names = db_get_hash_array("SELECT ?:product_option_variants_descriptions.variant_name, ?:product_option_variants_descriptions.variant_id, ?:product_option_variants.code_suffix FROM ?:product_option_variants LEFT JOIN ?:product_option_variants_descriptions ON ?:product_option_variants.variant_id = ?:product_option_variants_descriptions.variant_id AND ?:product_option_variants_descriptions.lang_code = 'ru'", 'variant_id');
    $option_names = db_get_hash_array("SELECT ?:product_options_descriptions.option_name, ?:product_options_descriptions.option_id FROM ?:product_options_descriptions WHERE ?:product_options_descriptions.lang_code = 'ru'", 'option_id');
    $missed = array();
    foreach ($variant_images as $prod_id => $pairs) {
        foreach ($pairs as $pair_id => $pair) {
            if (empty($option_names[$pair['option_id']])) {
                $missed[] = $pair;
                continue;
            }
            if (empty($variant_names[$pair['object_id']])) {
                $missed[] = $pair;
                continue;
            }
            if (!empty($pair['image_id'])) {
                $update[] = array(
                    'object_id' => $pair['image_id'],
                    'description' => $product_names[$prod_id]['product'] .  ' ' . $option_names[$pair['option_id']]['option_name'] . ' ' . $variant_names[$pair['object_id']]['variant_name'] . (!empty($product_names[$prod_id]['product_code']) ? ' ' . $product_names[$prod_id]['product_code']  . (!empty($variant_names[$pair['object_id']]['code_suffix']) ? $variant_names[$pair['object_id']]['code_suffix'] : '') : ''),
                    'lang_code' => 'ru',
                    'object_holder' => 'images'
                );
            }
            if (!empty($pair['detailed_id'])) {
                $update[] = array(
                    'object_id' => $pair['detailed_id'],
                    'description' => $product_names[$prod_id]['product'] .  ' ' . $option_names[$pair['option_id']]['option_name'] . ' ' . $variant_names[$pair['object_id']]['variant_name'] . (!empty($product_names[$prod_id]['product_code']) ? ' ' . $product_names[$prod_id]['product_code']  . (!empty($variant_names[$pair['object_id']]['code_suffix']) ? $variant_names[$pair['object_id']]['code_suffix'] : '') : ''),
                    'lang_code' => 'ru',
                    'object_holder' => 'images'
                );
            }
        }
    }
//     if (!empty($missed)) {
//         foreach ($missed as $i => $pair) {
//             fn_delete_image_pair($pair['pair_id'], $pair['object_type']);
//         }
//     }
//     if (!empty($update)) {
//         db_query("REPLACE ?:common_descriptions ?m", $update);
//     }

    fn_echo('Done');
    exit;
} elseif ($mode == 'add_order_subscribers') {
    $time = 1492905600; // 23 April
    $emails = db_get_hash_array("SELECT DISTINCT LOWER(?:orders.email) AS email, ?:orders.timestamp FROM ?:orders LEFT JOIN ?:subscribers ON ?:subscribers.email = ?:orders.email WHERE ?:orders.timestamp < ?i AND ?:subscribers.email IS NULL", 'email', $time);
    if (!empty($emails)) {
        list($page_mailing_lists) = fn_get_mailing_lists();
        $confirmed = array();
        if (!empty($page_mailing_lists)) {
            foreach ($page_mailing_lists as $i => $m_list) {
                $confirmed[$m_list['list_id']]['confirmed'] = true;
            }
        }
        $iter = 0;
        foreach ($emails as $i => $email_data) {
            $data = array(
                'email' => $email_data['email'],
                'timestamp' => $email_data['timestamp'],
                'status' => 'C'
            );
            $subscriber_id = db_query("REPLACE INTO ?:subscribers ?e", $data);
            if (!empty($page_mailing_lists)) {
                fn_update_subscriptions($subscriber_id, array_keys($page_mailing_lists), $confirmed);
            }
            $iter++;
        }
    }
    fn_echo('Done');
    exit;
} elseif ($mode == 'status_order_subscribers') {
    $ordered = db_get_fields("SELECT ?:subscribers.subscriber_id FROM ?:subscribers INNER JOIN ?:orders ON ?:orders.email = ?:subscribers.email AND ?:orders.email != ''");
    db_query("UPDATE ?:subscribers SET status = 'C' WHERE subscriber_id IN (?n)", $ordered);

    $subs_ids = db_get_fields("SELECT ?:subscribers.subscriber_id FROM ?:subscribers WHERE email IN ('','noemail@tennishouse.ru')");
    db_query("DELETE FROM ?:subscribers WHERE subscriber_id IN (?n)", $subs_ids);
    db_query("DELETE FROM ?:user_mailing_lists WHERE subscriber_id IN (?n)", $subs_ids);

    fn_echo('Done');
    exit;
} elseif ($mode == 'add_user_subscribers') {
    $emails = db_get_hash_array("SELECT DISTINCT LOWER(?:users.email) AS email, ?:users.timestamp, ?:users.firstname, ?:users.lastname FROM ?:users LEFT JOIN ?:orders ON ?:users.email = ?:orders.email WHERE ?:orders.order_id IS NULL", 'email');
    if (!empty($emails)) {
        list($page_mailing_lists) = fn_get_mailing_lists();
        $confirmed = array();
        if (!empty($page_mailing_lists)) {
            foreach ($page_mailing_lists as $i => $m_list) {
                $confirmed[$m_list['list_id']]['confirmed'] = true;
            }
        }
        $iter = 0;
        foreach ($emails as $i => $email_data) {
            $data = array(
                'email' => $email_data['email'],
                'timestamp' => $email_data['timestamp'],
                'status' => (!empty($email_data['firstname']) || !empty($email_data['lastname'])) ? 'C' : 'P'
            );
            $subscriber_id = db_query("REPLACE INTO ?:subscribers ?e", $data);
            if (!empty($page_mailing_lists)) {
                fn_update_subscriptions($subscriber_id, array_keys($page_mailing_lists), $confirmed);
            }
            $iter++;
        }
    }
    fn_echo('Done');
    exit;
} elseif ($mode == 'check_pending') {
    $emails = db_get_hash_single_array("SELECT email, subscriber_id FROM ?:subscribers WHERE status = 'C'", array('subscriber_id', 'email'));
    $result = $result1 = array();
    foreach ($emails as $id => $email) {
        $mailSegments = explode('@', $email);
        $result1[$mailSegments[1]][] = $email;
//         $res = checkdnsrr($mailSegments[1]);
//         if ($res) {
//             $result['C'][] = $id;
//         } else {
//             $result['L'][] = $id;
//         }
    }
    fn_print_die($result1);
    db_query("UPDATE ?:subscribers SET status = 'L' WHERE subscriber_id IN (?a)", $result['L']);
    fn_echo('Done');
    exit;
} elseif ($mode == 'validate_email') {
    $subscribers = db_get_hash_single_array("SELECT email, subscriber_id FROM ?:subscribers WHERE status = 'P'", array('subscriber_id', 'email'));

    foreach ($subscribers as $_id => $email) {
        $result = false;
        $mailSegments = explode('@', $email);
        if (!empty($mailSegments[1])) {
            $domain = $mailSegments[1];
            if (substr($domain, -1) != '.') {
                $domain .= '.';
            }

            $mxRecordsAvailable = getmxrr($domain, $mxRecords, $mxWeight);
            if (!empty($mxRecordsAvailable)) {
                $mxHosts = array_combine($mxRecords,$mxWeight);
                asort($mxHosts, SORT_NUMERIC);
                $mxHost = array_keys($mxHosts)[0];
                if (!empty($mxHost)) {
                //    echo $mxHost . "<br>";
                    $mxSocket = fsockopen($mxHost, 25, $errno, $errstr, 2);
                    if (!empty($mxSocket)) {
                        $response = "";
                        // say HELO to mailserver
                        $response .= fn_send_command($mxSocket, "EHLO mx1.validemail.com");
                        // initialize sending mail
                        $response .= fn_send_command($mxSocket, "MAIL FROM:<info@tennishouse.ru>");
                        // try recipient address, will return 250 when ok..
                        $rcptText = fn_send_command($mxSocket, "RCPT TO:<" . $email . ">");
                        $response .= $rcptText;
                        if (substr($rcptText, 0, 3) == "250") {
                            $result = true;
                        }
                        // quit mail server connection
                        fn_send_command($mxSocket, "QUIT");
                        fclose($mxSocket);
                    }
                }
            }
        }
        if ($result == true) {
            db_query("UPDATE ?:subscribers SET status = 'C' WHERE subscriber_id = ?i", $_id);
        } else {
            db_query("UPDATE ?:subscribers SET status = 'L' WHERE subscriber_id = ?i", $_id);
        }
    }
    fn_echo('Done');
    exit;

} elseif ($mode == 'complete_orders') {

    if (!empty($_SESSION['complete_orders'])) {
        fn_prepare_coplete_orders();
    } else {
        Registry::get('view')->assign('step', 'one');
    }

} elseif ($mode == 'update_rrp') {
    Registry::get('view')->assign('step', 'one');
    Registry::get('view')->assign('brands', fn_development_get_brands());
} elseif ($mode == 'generate_user_lkey' && !empty($_REQUEST['user_id'])) {

    fn_generate_ekey($_REQUEST['user_id'], 'L', SECONDS_IN_DAY * 90);

    return array(CONTROLLER_STATUS_REDIRECT, "profiles.update?user_id=" . $_REQUEST['user_id']);

} elseif ($mode == 'generate_order_link' && !empty($_REQUEST['order_id'])) {

    $user_id = db_get_field("SELECT user_id FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);

    if (empty($user_id)) {
        $user_id = fn_create_order_customer($_REQUEST['order_id'], $auth);
    }

    if (!empty($user_id)) {
        fn_generate_ekey($user_id, 'L', SECONDS_IN_DAY * 90);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "orders.details?order_id=" . $_REQUEST['order_id']);

} elseif ($mode == 'add_user' && !empty($_REQUEST['order_id'])) {

    fn_create_order_customer($_REQUEST['order_id'], $auth);

    return array(CONTROLLER_STATUS_REDIRECT, "orders.details?order_id=" . $_REQUEST['order_id']);

} elseif ($mode == 'get_ppl') {

    $houses = array(14, 22, 27, 32, 73, 44, 111, 67, 94, 205, 123, 207, 155, 259, 380, 474, 285, 680, 510, 1087, 900, 1380, 1330, 1040, 2120, 1150, 1810, 1400, 2800);
    rsort($houses);
    fn_print_die($houses);
    $top = 807;

    fn_subset_sums($houses, $top);

    exit;
} elseif ($mode == 'fix_alt') {
    $params['extend'] = array('categories', 'description');
    list($products, $search) = fn_get_products($params);
    Registry::set('runtime.controller', 'products');
    fn_gather_additional_products_data($products, array(
        'get_icon' => true,
        'get_detailed' => true,
        'get_additional' => true,
        'get_options' => true,
        'get_discounts' => false,
        'get_features' => false
    ));
    Registry::set('runtime.controller', 'development');
    if (!empty($products)) {
        $images_alts = array();
        foreach ($products as $i => $product) {
            $name = $product['product'];
            if (!empty($product['main_pair']) && !empty($product['main_pair']['detailed_id'])) {
                fn_fill_image_common_description($images_alts, $product['main_pair']['detailed_id'], $name . (!empty($product['product_code']) ? ' ' . $product['product_code'] : ''));
            }
            if (!empty($product['image_pairs'])) {
                foreach ($product['image_pairs'] as $l => $i_pair) {
                    if (!empty($i_pair['detailed_id'])) {
                        fn_fill_image_common_description($images_alts, $i_pair['detailed_id'], $name . (!empty($product['product_code']) ? ' ' . $product['product_code'] : ''));
                    }
                }
            }
            if (!empty($product['product_options'])) {
                foreach ($product['product_options'] as $o_id => $o_data) {
                    if (!empty($o_data['variants'])) {
                        $_name = $name . ' ' . $o_data['option_name'];
                        foreach ($o_data['variants'] as $v_id => $v_data) {
                            if (!empty($v_data['images'])) {
                                $__name = $_name . ' ' . $v_data['variant_name'];
                                foreach ($v_data['images'] as $k => $img) {
                                    if (!empty($img['detailed_id'])) {
                                        fn_fill_image_common_description($images_alts, $i_pair['detailed_id'], $__name . (!empty($product['product_code']) ? ' ' . $product['product_code'] : '') . (!empty($v_data['code_suffix']) ? $v_data['code_suffix'] : ''));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!empty($images_alts)) {
            db_query("REPLACE INTO ?:common_descriptions ?m", $images_alts);
        }
    }
    fn_echo('Done');
    exit;
} elseif ($mode == 'fix_obsolete_warehouse_inventory') {
    $options_inventory = db_get_hash_multi_array("SELECT * FROM ?:product_options_inventory", array('product_id', 'combination_hash'));
    $warehouse_inventory = db_get_hash_multi_array("SELECT * FROM ?:product_warehouses_inventory", array('product_id', 'warehouse_hash'));
    $to_delete = array();
    foreach ($options_inventory as $prod_id => $inv_combinations) {
        if (!empty($warehouse_inventory[$prod_id])) {
            $keys = array_keys($inv_combinations);
            foreach ($warehouse_inventory[$prod_id] as $wh_id => $wh_data) {
                if (!in_array($wh_data['combination_hash'], $keys)) {
                    $to_delete[] = $wh_id;
                }
            }
        }
    }
    if (!empty($to_delete)) {
        db_query("DELETE FROM ?:product_warehouses_inventory WHERE warehouse_hash IN (?n)", $to_delete);
    }
    fn_echo('Done');
    exit;
} elseif ($mode == 'fix_missing_warehouse_inventory') {
    $ids = db_get_fields("SELECT DISTINCT product_id FROM ?:product_warehouses_inventory");
    $params['extend'] = array('categories');
    $params['exclude_pid'] = $ids;

    list($products, $search) = fn_get_products($params);
    fn_gather_additional_products_data($products, array(
        'get_icon' => false,
        'get_detailed' => false,
        'get_additional' => false,
        'get_options' => false,
        'get_discounts' => false,
        'get_features' => true
    ));
    if (!empty($products)) {
        foreach ($products as $i => $prod_data) {
            fn_reset_product_warehouses($prod_data['product_id']);
        }
    }
    fn_echo('Done');
    exit;

} elseif ($mode == 'get_feature_variants') {

    list($feature_variants,) = fn_get_product_feature_variants(array(
        'feature_id' => $_REQUEST['feature_id']
    ), 0, CART_LANGUAGE);

    Registry::get('view')->assign('feature_variants', $feature_variants);
    Registry::get('view')->assign('feature_id', $_REQUEST['feature_id']);
    Registry::get('view')->assign('data_name', $_REQUEST['data_name']);
    Registry::get('view')->assign('key', str_replace('feature_variants_' . $_REQUEST['id'] . '_', '', $_REQUEST['result_ids']));
    Registry::get('view')->assign('obj_id', $_REQUEST['result_ids']);
    Registry::get('view')->display('addons/development/components/select_feature_variant_id.tpl');
    exit;
} elseif ($mode == 'move_to_root') {

    $update_ids = $categories = array(RACKETS_CATEGORY_ID, BAGS_CATEGORY_ID, BALLS_CATEGORY_ID, STRINGS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, OVERGRIPS_CATEGORY_ID, BASEGRIPS_CATEGORY_ID, DAMPENERS_CATEGORY_ID, OTHER_CATEGORY_ID, BADMINTON_RACKETS_CATEGORY_ID, SHUTTLECOCKS_CATEGORY_ID, BADMINTON_SHOES_CATEGORY_ID, BADMINTON_BAGS_CATEGORY_ID, BADMINTON_STRINGS_CATEGORY_ID);
    $params = array(
        'subcats' => 'Y',
        'cid' => $categories
    );
    list($products, $search) = fn_get_products($params);
    $update = $prod_ids = array();
    foreach ($products as $prod) {
        if (!empty($prod['all_path'])) {
            $added = false;
            $update_ids = array_merge($update_ids, $prod['category_ids']);
            foreach ($prod['all_path'] as $path) {
                $ids = explode('/', $path);
                $inter = array_intersect($ids, $categories);
                if (!empty($inter)) {
                    $id = reset($inter);
                    $update[] = array(
                        'product_id' => $prod['product_id'],
                        'category_id' => $id,
                        'position' => 0,
                        'link_type' => in_array($prod['main_category'], $ids) ? 'M' : 'A'
                    );
                    db_query("UPDATE ?:seo_names SET path = ?i WHERE object_id = ?i AND type = 'p' ", $id, $prod['product_id']);
                    $added = true;
                }
            }
            if (!empty($added)) {
                $prod_ids[] = $prod['product_id'];
            }
        }
    }
    $update_ids = array_merge($update_ids, $categories);
    if (!empty($prod_ids)) {
        db_query("DELETE FROM ?:products_categories WHERE product_id IN (?n)", $prod_ids);
    }
    if (!empty($update)) {
        db_query("REPLACE ?:products_categories ?m", $update);
    }
    fn_update_product_count($update_ids);

    fn_echo('Done');
    exit;
} elseif ($mode == 'cleanup_category_seo') {
    $cat_ids = db_get_fields("SELECT object_id FROM ?:seo_names LEFT JOIN ?:categories ON ?:categories.category_id = ?:seo_names.object_id WHERE type = 'c' AND category_id IS NULL");
//     db_query("DELETE FROM ?:seo_names WHERE type = 'c' AND object_id IN (?n)", $cat_ids);
    exit;
} elseif ($mode == 'cleanup_feature_seo') {
    $fv_ids = db_get_fields("SELECT object_id FROM ?:seo_names LEFT JOIN ?:product_feature_variants ON ?:product_feature_variants.variant_id = ?:seo_names.object_id LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_feature_variants.feature_id WHERE type = 'e' AND (seo_variants = 'N' OR ?:product_features.feature_id IS NULL)");
//     db_query("DELETE FROM ?:seo_names WHERE type = 'e' AND object_id IN (?n)", $fv_ids);
    exit;
} elseif ($mode == 'fix_old_seo') {
    $objects = db_get_array("SELECT * FROM ?:seo_names1 WHERE type = 'p'");
    $cat_ids = db_get_hash_single_array("SELECT object_id, name FROM ?:seo_names1 WHERE type = 'c'", array('object_id', 'name'));

    foreach ($objects as $i => $obj) {
        $src = '';
        if (!empty($obj['path'])) {
            $path = explode('/', $obj['path']);
            $new_path = array();
            foreach ($path as $pth) {
                $new_path[] = $cat_ids[$pth];
            }
//             fn_seo_update_redirect(array(
//                 'src' => '/' . implode('/', $new_path) . '/' . $obj['name'],
//                 'type' => $obj['type'],
//                 'object_id' => $obj['object_id'],
//                 'company_id' => 1,
//                 'lang_code' => 'ru'
//             ), 0, false);
        }
    }
    exit;

} elseif ($mode == 'tst') {
    fn_check_sms();
    exit;
} elseif ($mode == 'generate_models') {
    $params['extend'] = array('categories', 'description');
    list($products, $search) = fn_get_products($params);
    fn_gather_additional_products_data($products, array(
        'get_icon' => false,
        'get_detailed' => false,
        'get_additional' => false,
        'get_options' => false,
        'get_discounts' => false,
        'get_features' => true,
        'get_title_features' => true
    ));
    if (!empty($products)) {
        foreach ($products as $product) {
            $model = trim(preg_replace(array('/[^a-zA-Z ]/', '/' . $product['product_features'][BRAND_FEATURE_ID]['variant'] . '/i'), '', $product['product']));
            if (!empty($model)) {
                db_query("UPDATE ?:products SET model = ?s WHERE product_id = ?i", $model, $product['product_id']);
            }
        }
    }
    exit;
} elseif ($mode == 'check_codes') {
    $res = db_get_hash_multi_array("SELECT a.product_id, a.product_code, b.product_code AS inventory_code, b.combination_hash FROM ?:products AS a LEFT JOIN ?:product_options_inventory AS b ON a.product_id = b.product_id WHERE b.product_code NOT LIKE CONCAT(a.product_code, '%')", array('product_id', 'combination_hash'));

    if (!empty($res)) {
        foreach ($res as $product_id => $combs) {
            fn_rebuild_inventory_codes($product_id);
        }
    }
    exit;
} elseif ($mode == 'clean_up_exceptions') {

    $exceptions = db_get_hash_multi_array("SELECT * FROM ?:product_options_exceptions", array('product_id', 'exception_id'));
    $product_options = fn_get_product_options(array_keys($exceptions), CART_LANGUAGE, true, false, false, false, false);

    $delete = array();
    foreach ($exceptions as $product_id => $_exceptions) {
        if (count($product_options[$product_id]) > 0) {
            foreach ($_exceptions as $e_id => $e_data) {
                $combination = unserialize($e_data['combination']);
                if (count($combination) != count($product_options[$product_id])) {
                    $delete[$e_id] = $e_data;
                } else {
                    $correct = true;
                    foreach ($combination as $opt_id => $v_id) {
                        if (empty($product_options[$product_id][$opt_id]) || empty($product_options[$product_id][$opt_id]['variants'][$v_id])) {
                            $correct = false;
                            break;
                        }
                    }
                    if (empty($correct)) {
                        $delete[$e_id] = $e_data;
                    }
                }
            }
        } else {
            $delete += $_exceptions;
        }
    }

    if (!empty($delete)) {
        db_query("DELETE FROM ?:product_options_exceptions WHERE exception_id IN (?n)", array_keys($delete));
    }

    exit;

} elseif ($mode == 'rebuild_exceptions') {

    $ids = db_get_fields("SELECT DISTINCT product_id FROM ?:product_warehouses_inventory WHERE amount > 0");
    $product_options = fn_get_product_options($ids, CART_LANGUAGE, true, false, false, false, false);

    foreach ($ids as $product_id) {
        if (count($product_options[$product_id]) > 0) {
            fn_update_product_exceptions($product_id);
        }
    }


    exit;
} elseif ($mode == 'generate_descriptions') {

    $cids = array(RACKETS_CATEGORY_ID);
    $params = array(
        'hide_out_of_stock' => 'Y',
        'cid' => array(RACKETS_CATEGORY_ID)
    );
    list($products, $search) = fn_get_products($params);
    $product_ids = array();
    foreach ($products as $i => $prod) {
        if (!in_array($prod['product_id'], unserialize(EXC_PRODUCT_ITEMS))) {
            $product_ids[] = $prod['product_id'];
        }
    }
    list($descriptions, $features) = fn_generate_product_features_descriptions($product_ids);
    foreach ($descriptions as $prod_id => $descr) {
        if (!empty($features[$prod_id][TYPE_FEATURE_ID]['variant_id']) && $features[$prod_id][TYPE_FEATURE_ID]['variant_id'] != KIDS_RACKET_FV_ID) {
            db_query("UPDATE ?:product_descriptions SET features_description = ?s WHERE product_id = ?i AND lang_code = ?s", '<p>' . $descr . '</p>', $prod_id, CART_LANGUAGE);
        }
    }

    exit;

} elseif ($mode == 'sync') {

    // fn_delete_product_feature_variants(0, db_get_fields("SELECT a.variant_id FROM ?:product_feature_variants AS a LEFT JOIN ?:product_features AS b ON a.feature_id = b.feature_id WHERE b.parent_id = ?i", TM_FEATURE_GROUP_ID));

//     $params = array(
//         'features_hash' => 'V' . DFC_BRAND_ID,
//         'warehouse_id' => DRIADA_WAREHOUSE_ID
//     );
//     list($products,$search) = fn_get_products($params);
//     $_data = array();
//     foreach ($products as $product) {
//         $_data[] = array(
//             'warehouse_hash' => fn_generate_cart_id($product['product_id'], array('warehouse_id' => DRIADA_WAREHOUSE_ID)),
//             'warehouse_id' => DRIADA_WAREHOUSE_ID,
//             'product_id' => $product['product_id'],
//             'amount' => 0
//         );
//         db_query("UPDATE ?:products SET timestamp = '1577883600' WHERE product_id = ?i", $product['product_id']);
//     }
    // db_query("REPLACE ?:product_warehouses_inventory ?m", $_data);
    // fn_print_die($_data);

    exit;
} elseif ($mode == 'clean_cp') {

    db_query("UPDATE ?:products SET price_mode = IF (auto_price = 'Y', 'D', 'S'), yml_pickup = 'Y'");

    $deleted = db_get_fields("SELECT competitive_id FROM ?:competitive_pairs LEFT JOIN ?:competitive_prices ON ?:competitive_pairs.competitive_id = ?:competitive_prices.item_id WHERE item_id IS NULL");

    db_query("DELETE FROM ?:competitive_pairs WHERE competitive_id IN (?n)", $deleted);

    exit;

}

function fn_prepare_coplete_orders()
{
    if (!empty($_SESSION['complete_orders'])) {
        foreach ($_SESSION['complete_orders'] as $k => $v) {
            Registry::get('view')->assign($k, $v);
        }
        if ($_SESSION['complete_orders']['step'] == 'two' && !empty($_SESSION['complete_orders']['order_d_statuses'])) {

            $params = array(
                'order_id' => array_keys($_SESSION['complete_orders']['order_d_statuses'])
            );

            list($orders, $search, $totals) = fn_get_orders($params, 0, true);
            Registry::get('view')->assign('orders', $orders);
        }
    }
}

function fn_fill_image_common_description(&$images_alts, $detailed_id, $name)
{
    $images_alts[] = array(
        'object_id' => $detailed_id,
        'object_type' => '',
        'description' => $name,
        'lang_code' => 'ru',
        'object' => '',
        'object_holder' => 'images'
    );
}

function fn_subset_sums($houses, $top, $sum = 0)
{
    foreach ($houses as $i => $house) {
        if ($house > $top) {
            unset($houses[$i]);
        }

    }

    foreach ($houses as $i => $house) {

    }
    // Print current subset
    if ($l > $r || $sum >= $top)
    {
        fn_print_r($sum , " ");
        return;
    }

    // Subset including arr[l]
    subsetSums($houses, $l + 1, $r, $top, $sum + $houses[$l]);

    // Subset excluding arr[l]
    subsetSums($houses, $l + 1, $r, $top, $sum);
}

function fn_normalize_string($string)
{
    if (preg_match_all("/\d,\d/", $string, $matches)) {
        $rplc = str_replace(',', '.', $matches[0]);
        $string = str_replace($matches[0], $rplc, $string);
    }

    return $string;
}

function fn_format_variant_name($variant_name)
{
    $variant_name = fn_strtolower($variant_name);
    $variant_name = preg_replace('/флуоресцентно/', 'ярко', $variant_name);
    $variant_name = preg_replace('/ярко /', 'ярко-', $variant_name);
    $variant_name = preg_replace('/светло /', 'светло-', $variant_name);
    $variant_name = preg_replace('/темно /', 'темно-', $variant_name);
    $variant_name = preg_replace('/ /', '', $variant_name);
    $variant_name = preg_replace('/:/', '', $variant_name);
    $variant_name = preg_replace('/[\+\-]/', '/', $variant_name);
    $variant_name = preg_replace('/ё/', 'е', $variant_name);
    $variant_name = preg_replace('/голубой/', 'синий', $variant_name);
    $variant_name = preg_replace('/черно/', 'черный', $variant_name);
    $variant_name = preg_replace('/желто/', 'желтый', $variant_name);
    fn_trim_helper($variant_name);
    return $variant_name;
}

function fn_get_babolat_csv($file, $options)
{
    $max_line_size = 65536; // 64 Кб
    $result = array();

    if ($options['delimiter'] == 'C') {
        $delimiter = ',';
    } elseif ($options['delimiter'] == 'T') {
        $delimiter = "\t";
    } else {
        $delimiter = ';';
    }

    if (!empty($file) && file_exists($file)) {

        $encoding = fn_detect_encoding($file, 'F', !empty($options['lang_code']) ? $options['lang_code'] : CART_LANGUAGE);

        if (!empty($encoding)) {
             $file = fn_convert_encoding($encoding, 'UTF-8', $file, 'F');
        } else {
            fn_set_notification('W', __('warning'), __('text_exim_utf8_file_format'));
        }

        $f = false;
        if ($file !== false) {
            $f = fopen($file, 'rb');
        }

        if ($f) {

            // Collect data
            $trash_lines = array();
            $line_it = 1;
            $current_product_code = '';
            $previous_row = array();
            while (($data = fn_fgetcsv($f, $max_line_size, $delimiter)) !== false) {

                $line_it ++;
                if (fn_is_empty($data)) {
                    continue;
                }
                $row = Bootstrap::stripSlashes($data);

                if (empty($row[0])) {
                    $trash_lines[] = array(
                        'line' => $line_it,
                        'data' => $row
                    );
                    continue;
                } elseif (!empty($row[0]) && preg_match("/^[a-zA-Z0-9]{4,8}$/", $row[0], $matches)) {
                    $current_product_code = $matches[0];
                    if (empty($result[$current_product_code])) {
                        $result[$current_product_code] = array(
                            'line' => $line_it,
                            'product' => $row[1],
                            'no_options' => true,
                            'data' => array()
                        );
                    }
                    if (!empty($row[2]) && !empty($result[$current_product_code]['no_options'])) {
                        unset($result[$current_product_code]['data']);
                        $result[$current_product_code]['no_options'] = false;
                    }
                    $item = array(
                        'name' => $row[2],
                        'amount' => $row[count($row) - 1]
                    );

//                     if (count($row) == 4) {
//                         $item['price'] = str_replace(',', '.', $row[2]);
//                     } elseif (count($row) == 5) {
//                         $item['price'] = !empty($row[3]) ? str_replace(',', '.', $row[3]) : str_replace(',', '.', $row[2]);
//                     }
                    $result[$current_product_code]['data'][] = $item;
                } else {
                    $current_product_code = '';
                    $previous_row = '';
                    $trash_lines[] = array(
                        'line' => $line_it,
                        'data' => $row
                    );
                    continue;
                }
                $previous_row = $row;
            }

            return array($result, $trash_lines);
        } else {
            fn_set_notification('E', __('error'), __('error_exim_cant_open_file'));

            return false;
        }
    } else {
        fn_set_notification('E', __('error'), __('error_exim_file_doesnt_exist'));

        return false;
    }
}
