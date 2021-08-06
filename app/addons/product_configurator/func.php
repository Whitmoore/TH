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
use Tygh\Enum\ProductTracking;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//
// Returns an array of IDs of compatible products
//
function fn_get_compatible_products_ids($current_product_id, $current_group_id)
{
    $_sets = db_get_hash_array(
        "SELECT ?:products.product_id, ?:conf_class_products.class_id, ?:conf_classes.group_id "
            . "FROM ?:conf_classes "
                . "LEFT JOIN ?:conf_class_products ON ?:conf_classes.class_id = ?:conf_class_products.class_id "
                . "LEFT JOIN ?:products ON ?:products.product_id = ?:conf_class_products.product_id "
        . "WHERE ?:products.status IN ('A', 'H')",
        'product_id'
    );

    $sets = Array();
    foreach ($_sets as $_set) {
        $sets[$_set['class_id']][$_set['product_id']] = $_set;
    }

    $_relations = db_get_array(
        "SELECT slave_class_id, group_id FROM ?:conf_class_products "
            . "INNER JOIN ?:conf_compatible_classes "
                . "ON ?:conf_compatible_classes.master_class_id = ?:conf_class_products.class_id "
            . "INNER JOIN ?:conf_classes "
                . "ON ?:conf_classes.class_id = ?:conf_class_products.class_id "
        . "WHERE product_id = ?i",
        $current_product_id
    );

    $available_products = Array();

    foreach ($_relations as $slave_class) {
        if (isset($sets[$slave_class['slave_class_id']])) {
            foreach ($sets[$slave_class['slave_class_id']] as $product) {
                $available_products[$product['product_id']] = array(
                    'product_id' => $product['product_id'],
                    'group_id' => $product['group_id']
                );
            }
        }
    }

    return $available_products;
}

//
// Delete all links to this product product congiguration module
//
function fn_delete_configurable_product($product_id)
{
    db_query("DELETE FROM ?:conf_class_products WHERE product_id = ?i", $product_id);
    db_query("DELETE FROM ?:conf_group_products WHERE product_id = ?i", $product_id);
    db_query("DELETE FROM ?:conf_product_groups WHERE product_id = ?i", $product_id);

    // If this product was set as default for selection in some group
    $default_ids = db_get_array("SELECT product_id, default_product_ids FROM ?:conf_product_groups WHERE default_product_ids LIKE ?l", "%$product_id%");
    foreach ($default_ids as $key => $value) {
        $def_pr = trim(str_replace("::", ":", str_replace($product_id, "", $value['default_product_ids'])), ":");
        db_query("UPDATE ?:conf_product_groups SET default_product_ids = ?s WHERE product_id = ?i", $def_pr, $value['product_id']);
    }
}

//
// Delete product configuration group
//
function fn_delete_group($group_id)
{
    db_query("DELETE FROM ?:conf_groups WHERE group_id = ?i", $group_id);
    db_query("DELETE FROM ?:conf_group_products WHERE group_id = ?i", $group_id);
    db_query("DELETE FROM ?:conf_product_groups WHERE group_id = ?i", $group_id);
    db_query("DELETE FROM ?:conf_group_descriptions WHERE group_id = ?i", $group_id);

    fn_delete_image_pairs($group_id, 'conf_group');

    // Reset all classes in this group
    db_query("UPDATE ?:conf_classes SET group_id = 0 WHERE group_id = ?i", $group_id);
}

//
// Delete product configuration class
//
function fn_delete_class($class_id)
{
    db_query("DELETE FROM ?:conf_classes WHERE class_id = ?i", $class_id);
    db_query("DELETE FROM ?:conf_class_products WHERE class_id = ?i", $class_id);
    db_query("DELETE FROM ?:conf_compatible_classes WHERE slave_class_id = ?i OR master_class_id = ?i", $class_id, $class_id);
    db_query("DELETE FROM ?:conf_class_descriptions WHERE class_id = ?i", $class_id);
}

function fn_product_configurator_get_group_name($group_id, $lang_code = CART_LANGUAGE)
{
    if (!empty($group_id)) {
        return db_get_field("SELECT configurator_group_name FROM ?:conf_group_descriptions WHERE group_id = ?i AND lang_code = ?s", $group_id, $lang_code);
    }

    return false;
}

function fn_product_configurator_get_class_name($class_id, $lang_code = CART_LANGUAGE)
{
    if (!empty($class_id)) {
        return db_get_field("SELECT class_name FROM ?:conf_class_descriptions WHERE class_id = ?i AND lang_code = ?s", $class_id, $lang_code);
    }

    return false;
}

//
// This function regenerates the cart ID tahing into account the confirable properties of an item
//
function fn_product_configurator_generate_cart_id(&$_cid, $extra, $only_selectable)
{
    // Configurable product
    if (!empty($extra['configuration'])) {
        foreach ($extra['configuration'] as $k => $v) {
            $_cid[] = $k;
            if (!empty($v['product_ids'])) {
                foreach ($v['product_ids'] as $_id) {
                    $_cid[] = $_id;
                    if (!empty($v['options'][$_id]['product_options'])) {
                        foreach ($v['options'][$_id]['product_options'] as $opt => $vrt) {
                            $_cid[] = $vrt;
                        }
                    }
                }
                $_cid[] = !empty($v['amount']) ? $v['amount'] : 1;
            }
        }
    }
    if (!empty($extra['parent']['configuration'])) {
        $_cid[] = $extra['parent']['configuration'];
        $_cid[] = $extra['step'];
    }
}

//
// This function clones product configuration
//
function fn_product_configurator_clone_product($product_id, $pid)
{
    $configuration = db_get_array("SELECT * FROM ?:conf_product_groups WHERE product_id = ?i", $product_id);
    if (empty($configuration)) {
        return false;
    }
    if (is_array($configuration)) {
        foreach ($configuration as $k => $v) {
            $v['product_id'] = $pid;
            db_query("INSERT INTO ?:conf_product_groups ?e", $v);
        }
    }

    return true;
}

function fn_product_configurator_get_products(&$params, &$fields, &$sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    if (AREA == 'C') {
        foreach ($sortings as $type => $field) {
            $sortings[$type] = array('products.product_type', $field);
        }
        $sortings['configurable'] = 'products.product_type';

        if (empty($params['sort_by']) || empty($sortings[$params['sort_by']])) {
            $params = array_merge($params, fn_get_default_products_sorting());
            if (empty($sortings[$params['sort_by']])) {
                $_products_sortings = fn_get_products_sorting();
                $params['sort_by'] = key($_products_sortings);
            }
        }

        $default_sorting = fn_get_products_sorting();

        if (empty($params['sort_order'])) {
            if (!empty($default_sorting[$params['sort_by']]['default_order'])) {
                $params['sort_order'] = $default_sorting[$params['sort_by']]['default_order'];
            } else {
                $params['sort_order'] = 'asc';
            }
        }

        if ($params['sort_order'] == 'asc') {
            $params['sort_order'] = 'descasc';
        }
    }

    if (!empty($params['configurable'])) {
        if ($params['configurable'] == 'Y') {
            $condition .= db_quote(' AND products.product_type = ?s', 'C');
        } elseif ($params['configurable'] != 'Y') {
            $condition .= db_quote(' AND products.product_type != ?s', 'C');
        }
    }
    if (!empty($params['pc_group_id'])) {
        $fields[] = 'GROUP_CONCAT(DISTINCT ?:conf_class_products.class_id) as class_ids';
        $join .= db_quote(" LEFT JOIN ?:conf_group_products ON ?:conf_group_products.product_id = products.product_id LEFT JOIN ?:conf_classes ON ?:conf_classes.group_id = ?:conf_group_products.group_id LEFT JOIN ?:conf_class_products ON ?:conf_class_products.product_id = products.product_id");
        $condition .= db_quote(' AND ?:conf_group_products.group_id = ?i', $params['pc_group_id']);
    }

    return true;
}

function fn_product_configurator_gather_additional_product_data_params(&$products, $params)
{
//     foreach ($products as $k => &$prod) {
//         if (!empty($prod['selected_configuration'])) {
//             foreach ($prod['selected_configuration'] as $group_id => $_data) {
//                 foreach ($_data['product_ids'] as $product_id) {
//                     $data = array(
//                         'product_id' => $product_id,
//                         'original_id' => $group_id,
//                         'virtual_parent_id' => 0,
//                         'get_default_options' => 'A'
//                     );
//                     if (!empty($_data['options'][$product_id]['product_options'])) {
//                         $data['selected_options'] = $_data['options'][$product_id]['product_options'];
//                     }
//                     if (!empty($prod['changed_configuration_option']) && $product_id == array_key_first($prod['changed_configuration_option'])) {
//                         $data['changed_option'] = reset($prod['changed_configuration_option']);
//                     }
//                     $products[] = $data;
//                 }
//             }
//         }
//     }
}

function fn_product_configurator_gather_additional_products_data_pre(&$products, $params)
{
    if (empty($params['get_for_one_product'])) {
        foreach ($products as $k => &$product) {
            if (!empty($product['configuration'])) {
                foreach ($product['configuration'] as $item_id => $_data) {
                    $_data['virtual_parent_id'] = $k;
                    $_data['original_id'] = $item_id;
                    $products[] = $_data;
                }
            }
        }
    }
}

function fn_product_configurator_gather_additional_products_data_post($product_ids, $params, &$products, $auth)
{
// Moved to the dev addon in order to apply variant images
//     if (empty($params['get_for_one_product'])) {
//         foreach ($products as $k => $product) {
//             if (isset($product['virtual_parent_id']) && !empty($product['original_id'])) {
//                 $products[$product['virtual_parent_id']]['configuration'][$product['original_id']] = $product;
//                 unset($products[$k]);
//             }
//         }
//     }
}

function fn_product_configurator_gather_additional_product_data_before_options(&$product, $auth, $params)
{
    if (!empty($params['get_for_one_product'])) {
        if (((!empty($product['product_type']) && $product['product_type'] == 'C') || (/*isset($product['product_features'][R_STRINGS_FEATURE_ID]) && $product['product_features'][R_STRINGS_FEATURE_ID]['value'] == 'N' && */isset($product['product_features'][R_WEIGHT_FEATURE_ID]))) && AREA == 'C' && !empty($params['get_for_one_product'])) {
            if (!empty($product['cart_id'])) {
                $product['edit_configuration'] = $product['cart_id'];
            } elseif (!empty($_REQUEST['cart_id'])) {
                $product['edit_configuration'] = $_REQUEST['cart_id'];
            }
            if (!empty($_REQUEST['cart_id'])) {
                $cart = & $_SESSION['cart'];
                if (isset($cart['products'][$product['edit_configuration']]['extra'])) {
                    $product['extra'] = $cart['products'][$product['edit_configuration']]['extra'];
                    $product['selected_amount'] = $cart['products'][$product['edit_configuration']]['amount'];
                    if (!empty($cart['products'][$product['edit_configuration']]['extra']['product_options'])) {
                        $product['selected_options'] = $cart['products'][$product['edit_configuration']]['extra']['product_options'];
                        $product['get_default_options'] = false;
                    }
                }
            }
        }
    }
}

function fn_product_configurator_gather_additional_product_data_post(&$product, $auth, $params)
{
//     if (AREA == 'C' && !empty($params['get_for_one_product']) && isset($product['product_features'][R_WEIGHT_FEATURE_ID]) && $product['amount'] > 0) {
//         $product['configuration_mode'] = true;
//         $selected_configuration = array();
//         if (!empty($product['cart_id'])) {
//             $product['edit_configuration'] = $product['cart_id'];
//         } elseif (!empty($_REQUEST['cart_id'])) {
//             $product['edit_configuration'] = $_REQUEST['cart_id'];
//         }
//         if (!empty($_REQUEST['cart_id'])) {
//             $cart = & $_SESSION['cart'];
//             if (isset($cart['products'][$product['edit_configuration']]['extra'])) {
//                 $product['extra'] = $cart['products'][$product['edit_configuration']]['extra'];
//                 $product['selected_amount'] = $cart['products'][$product['edit_configuration']]['amount'];
//             }
//
//             if (!empty($cart['products'][$_REQUEST['cart_id']]['extra']['configuration'])) {
//                 $selected_configuration = $cart['products'][$_REQUEST['cart_id']]['extra']['configuration'];
//             }
//         } elseif (!empty($product['selected_configuration'])) {
//             $selected_configuration = $product['selected_configuration'];
//         }
//         if (isset($product['product_features'][R_STRINGS_FEATURE_ID]) && $product['product_features'][R_STRINGS_FEATURE_ID]['value'] == 'N') {
//             list($strining_options, $c_price) = fn_get_stringing_options($product, $selected_configuration, !empty($selected_configuration[STRINGING_GROUP_ID]['product_ids']) && !empty(reset($selected_configuration[STRINGING_GROUP_ID]['product_ids'])) && !in_array('UNSTRUNG', $selected_configuration[STRINGING_GROUP_ID]['product_ids']));
//             if (!empty($c_price)) {
//                 $product['price'] += $c_price;
//                 $product['original_price'] += $c_price;
//                 $product['list_price'] += $c_price;
//             }
//         } else {
//             $product['has_strings'] = true;
//         }
//         list($dampener_options, $c_dp_price) = fn_get_dampener_options($product, $selected_configuration);
//         if (!empty($c_dp_price)) {
//             $product['price'] += $c_dp_price;
//             $product['original_price'] += $c_dp_price;
//             $product['list_price'] += $c_dp_price;
//         }
//         list($overgrip_options, $c_og_price) = fn_get_overgrip_options($product, $selected_configuration);
//         if (!empty($c_og_price)) {
//             $product['price'] += $c_og_price;
//             $product['original_price'] += $c_og_price;
//             $product['list_price'] += $c_og_price;
//         }
//     }
    if (AREA == 'A' && !empty($product['extra']['configuration']) && $product['main_category'] == RACKETS_CATEGORY_ID) {
        $product['configuration_mode'] = true;
        $selected_configuration = $product['extra']['configuration'];
        fn_get_customization_options($product, $selected_configuration);
//         if (!empty($product['extra']['configuration'][STRINGING_GROUP_ID]) || !empty($product['extra']['configuration'][STRINGING_TENSION_GROUP_ID])) {
//             fn_get_stringing_options($product, $selected_configuration);
//         }
//         if (!empty($product['extra']['configuration'][DAMPENER_GROUP_ID])) {
//             fn_get_dampener_options($product, $selected_configuration);
//         }
//         if (!empty($product['extra']['configuration'][OVERGRIP_GROUP_ID])) {
//             fn_get_overgrip_options($product, $selected_configuration);
//         }
        if (!empty($product['product_configurator_groups']) && !empty($product['configuration'])) {
            foreach ($product['product_configurator_groups'] as $k => $pc) {
                foreach ($product['configuration'] as $l => $cnf) {
                    if ($cnf['group_id'] == $pc['group_id']) {
                        $product['product_configurator_groups'][$k]['selected_id'] = $l;
                    }
                }
            }
        }
    }
//     if (AREA == 'C' && in_array($_REQUEST['dispatch'], array('checkout.cart', 'racket_customization.submit')) && !empty($product['configuration']) && ($params['get_icon'] || $params['get_detailed'])) {
//         $prod_ids = array();
//         foreach ($product['configuration'] as $i => $prod) {
//             $prod_ids[] = $prod['product_id'];
//         }
//         $products_images = fn_get_image_pairs($prod_ids, 'product', 'M', $params['get_icon'], $params['get_detailed'], CART_LANGUAGE);
//         foreach ($product['configuration'] as $i => &$prod) {
//             if (empty($prod['main_pair']) && !empty($products_images[$prod['product_id']])) {
//                 $prod['main_pair'] = reset($products_images[$prod['product_id']]);
//             }
//         }
//     }
}

function fn_product_configurator_update_cart_data_post(&$cart, $cart_products)
{
    foreach ($cart_products as $k => $v) {
        if (isset($cart['products'][$k]) && !empty($v['configuration'])) {
            $cart['products'][$k]['pc_original_price'] = $v['original_price'];
            if (!empty($cart['order_id']) && !empty($cart['products'][$k]['extra']['base_price'])) {
                $cart['products'][$k]['base_price'] = $cart['products'][$k]['extra']['base_price'];
            }
            foreach ($v['configuration'] as $_k => $_v) {
                if (!empty($cart['products'][$k]['configuration'][$_k])) {
                    if (!isset($_v['base_price'])) {
                        $cart['products'][$k]['configuration'][$_k]['base_price'] = $_v['base_price'] = $cart['products'][$k]['configuration'][$_k]['stored_price'] != 'Y' ? $_v['price'] : $cart['products'][$k]['configuration'][$_k]['price'];
                    } else {
                        if ($cart['products'][$k]['configuration'][$_k]['stored_price'] == 'Y') {
                            $cart_products[$k]['configuration'][$_k]['base_price'] = $cart['products'][$k]['configuration'][$_k]['price'];
                        }
                    }

                    $cart['products'][$k]['configuration'][$_k]['base_price'] = $cart['products'][$k]['configuration'][$_k]['stored_price'] != 'Y' ? $_v['base_price'] : $cart['products'][$k]['configuration'][$_k]['price'];
                    $cart['products'][$k]['configuration'][$_k]['price'] = $cart['products'][$k]['configuration'][$_k]['stored_price'] != 'Y' ? $_v['price'] : $cart['products'][$k]['configuration'][$_k]['price'];
                    if (isset($_v['discount'])) {
                        $cart['products'][$k]['configuration'][$_k]['discount'] = $_v['discount'];
                    }
                    if (isset($_v['promotions'])) {
                        $cart['products'][$k]['configuration'][$_k]['promotions'] = $_v['promotions'];
                    }
                }
            }
        }
    }
}

function fn_product_configurator_gather_additional_product_data_before_discounts(&$product, $auth, $params)
{
    if (!empty($product['product_type']) && $product['product_type'] == 'C') {
        if (AREA == 'C' && !empty($params['get_for_one_product'])) {
                $product['configuration_mode'] = true;
                $selected_configuration = array();
                if (!empty($product['cart_id'])) {
                    $product['edit_configuration'] = $product['cart_id'];
                } elseif (!empty($_REQUEST['cart_id'])) {
                    $product['edit_configuration'] = $_REQUEST['cart_id'];
                }
                if (!empty($_REQUEST['cart_id'])) {
                    $cart = & $_SESSION['cart'];
                    if (isset($cart['products'][$product['edit_configuration']]['extra'])) {
                        $product['extra'] = $cart['products'][$product['edit_configuration']]['extra'];
                        $product['selected_amount'] = $cart['products'][$product['edit_configuration']]['amount'];
                    }

                    if (!empty($cart['products'][$_REQUEST['cart_id']]['extra']['configuration'])) {
                        $selected_configuration = $cart['products'][$_REQUEST['cart_id']]['extra']['configuration'];
                    }
                } elseif (!empty($product['selected_configuration'])) {
                    $selected_configuration = $product['selected_configuration'];
                } elseif (AREA == 'A' && !empty($product['extra']['configuration'])) {
                    $selected_configuration = $product['extra']['configuration'];
                }
                fn_get_configuration_groups($product, $selected_configuration);
        } elseif (AREA == 'C') {
            $product['price'] = $product['base_price'] = 1;
        }
        if (AREA == 'A' && !empty($product['extra']['configuration'])) {
            $product['configuration_mode'] = true;
            $selected_configuration = $product['extra']['configuration'];
            fn_get_configuration_groups($product, $selected_configuration);
        }
    }

    return true;
}

/**
 * Additional actions for product quick view
 *
 * @param array $params Request parameters
 * @return boolean Always true
 */
function fn_product_configurator_prepare_product_quick_view($params)
{
    $product = Registry::get('view')->getTemplateVars('product');
    fn_pconf_gather_default_configuration_price($product);

    Registry::get('view')->assign('product', $product);

    return true;
}

/**
 * Calculates price of default product configuration
 *
 * @param array $product Product data
 * @return boolean Always true
 */
function fn_pconf_gather_default_configuration_price(&$product)
{
    $price = 0;

    if ($product['product_type'] == 'C') {
        $conf_product_groups = db_get_hash_single_array("SELECT ?:conf_product_groups.group_id, ?:conf_product_groups.default_product_ids FROM ?:conf_product_groups LEFT JOIN ?:conf_groups ON ?:conf_product_groups.group_id = ?:conf_groups.group_id WHERE ?:conf_groups.status = 'A' AND ?:conf_product_groups.product_id = ?i", array('group_id', 'default_product_ids'), $product['product_id']);

        $product_ids = array();
        foreach ($conf_product_groups as $group_id => $group_product_ids) {
            $tmp = is_array($group_product_ids) ? $group_product_ids : explode(':', $group_product_ids);
            foreach ($tmp as $product_id) {
                $product_ids[$product_id] = !empty($product_ids[$product_id]) ? ($product_ids[$product_id] + 1) : 1;
            }
        }

        if (!empty($product_ids)) {
            list($sub_products, $search) = fn_get_products(array('pid' => array_keys($product_ids)));

            fn_gather_additional_products_data($sub_products, array('get_icon' => false, 'get_detailed' => false, 'get_options' => false, 'get_discounts' => true, 'get_features' => false));

            foreach ($sub_products as $sub_product) {
                $price_modifier = $product_ids[$sub_product['product_id']];

                // calculate original price
                $sub_price = !empty($sub_product['original_price']) ? $sub_product['original_price'] : $sub_product['base_price'];

                $product['original_price'] = (!empty($product['original_price']) ? $product['original_price'] : $product['base_price']) + $sub_price * $price_modifier;

                // calculate list price
                $sub_price = ($sub_product['list_price'] > 0) ? $sub_product['list_price'] : $sub_product['base_price'];

                $product['list_price'] = (($product['list_price'] > 0) ? $product['list_price'] : $product['base_price']) + $sub_price * $price_modifier;

                $product['base_price'] += $sub_product['base_price'] * $price_modifier;
                $product['price'] += $sub_product['price'] * $price_modifier;

            }
        }
    }

    return true;
}

/** * Recalculates price and checks if product can be added with the current price
 *
 * @param array $data Adding product data
 * @param float $price Calculated product price
 * @param boolean $allow_add Flag that determines if product can be added to cart
 * @return boolean Always true
 */
function fn_product_configurator_add_product_to_cart_check_price($data, $price, &$allow_add)
{
    if (!$allow_add && empty($price) && !empty($data['configuration'])) {
        $allow_add = true;
    }

    return true;
}

function fn_product_configurator_calculate_cart_items_pre(&$cart, $cart_products, $auth)
{
    foreach ($cart['products'] as $k => &$product) {
        if (!empty($product['configuration'])) {
            if (AREA == 'A') {
                $cart['recalculate_catalog_promotions'] = true;
//                 $product['stored_price'] = 'N';
                $product['price'] = $product['extra']['base_price'];
            }
            foreach ($product['configuration'] as $item_id => $_data) {
                $_data['stored_price'] = !empty($product['extra']['configuration'][$_data['extra']['group_id']]['stored_price']) ?$product['extra']['configuration'][$_data['extra']['group_id']]['stored_price'] : 'N';
                if ($_data['stored_price'] == 'Y') {
                    $_data['price'] = $product['extra']['configuration'][$_data['extra']['group_id']]['price'];
                }
//                 $_data['stored_discount'] = !empty($product['extra']['configuration'][$_data['extra']['group_id']]['stored_discount']) ?$product['extra']['configuration'][$_data['extra']['group_id']]['stored_discount'] : 'N';
//                 if ($_data['stored_discount'] == 'Y') {
//                     $_data['discount'] = $product['extra']['configuration'][$_data['extra']['group_id']]['discount'];
//                 }
                if (!empty($_data['extra']['is_separate'])) {
                    $cart['products'][$item_id] = $_data;
                }
            }
        }
    }
}

function fn_product_configurator_calculate_cart_items($cart, &$cart_products, $auth)
{
    if (AREA == 'A') {
        foreach ($cart['products'] as $k => &$product) {
            if (!empty($product['configuration']) && !empty($product['extra']['promotions'])) {
//                $cart_products[$k]['promotions'] = $product['extra']['promotions'];
            }
        }
    }
}

function fn_product_configurator_calculate_cart_items_after_promotions(&$cart, &$cart_products, $auth)
{
    foreach ($cart['products'] as $k => &$product) {
        if (!empty($product['configuration'])) {
            foreach ($product['configuration'] as $item_id => $_data) {
                if (empty($_data['extra']['is_separate'])) {
                    continue;
                }
                if (empty($cart_products[$item_id])) { // FIXME - for deleted products for OM
                    unset($product['configuration'][$item_id]);
                    continue;
                }
                $cart_products[$item_id]['is_accessible'] = fn_is_accessible_product($cart_products[$item_id]);

                $cart['products'][$k]['price'] += $cart_products[$item_id]['base_price'] * $_data['extra']['step'];
                $cart_products[$k]['price'] += $cart_products[$item_id]['price'] * $_data['extra']['step'];
//                $cart_products[$k]['base_price'] += $cart_products[$item_id]['base_price'] * $_data['extra']['step'];
                $cart_products[$k]['original_price'] += $cart_products[$item_id]['original_price'] * $_data['extra']['step'];
                if (!empty($cart_products[$item_id]['discount'])) {
                    $cart_products[$k]['discount'] = !empty($cart_products[$k]['discount']) ? ($cart_products[$k]['discount'] + $cart_products[$item_id]['discount'] * $_data['extra']['step']) : ($cart_products[$item_id]['discount'] * $_data['extra']['step']);
                }
                $cart_products[$k]['weight'] += $cart_products[$item_id]['weight'] * $_data['extra']['step'];

                $_tax = (!empty($cart_products[$item_id]['tax_summary']) ? ($cart_products[$item_id]['tax_summary']['added'] / $cart_products[$item_id]['amount']) : 0);
                $product['configuration'][$item_id]['original_price'] = $cart_products[$item_id]['original_price'];
                $cart_products[$item_id]['display_price'] = $cart_products[$item_id]['price'] + (Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y' ? $_tax : 0);
                $cart_products[$item_id]['step'] = $_data['extra']['step'];
                $cart_products[$item_id]['group_id'] = $_data['extra']['group_id'];
                $cart_products[$item_id]['subtotal'] = $cart_products[$item_id]['price'] * $_data['extra']['step'];
                $product['configuration'][$item_id]['display_subtotal'] = $cart_products[$item_id]['display_subtotal'] = $cart_products[$item_id]['display_price'] * $_data['extra']['step'];
                $cart_products[$k]['configuration'][$item_id] = $cart_products[$item_id];
                $product['configuration'][$item_id]['product'] = $cart_products[$item_id]['product'];
                unset($cart_products[$item_id]);
                unset($cart['products'][$item_id]);
            }
        }
    }
}

function fn_product_configurator_post_check_amount_in_stock($product_id, &$amount, $product_options, $cart_id, $is_edp, $original_amount, &$cart)
{
    if (!empty($cart['products'][$cart_id]['configuration'])) {
        foreach ($cart['products'][$cart_id]['configuration'] as $_cart_id => $prod) {
            $selectable_cart_id = fn_generate_cart_id($prod['product_id'], array('product_options' => (!empty($prod['product_options']) ? $prod['product_options'] : array())), true);
            $_original_amount = !empty($prod['original_product_data'][$selectable_cart_id]['amount']) ? $prod['original_product_data'][$selectable_cart_id]['amount'] : 0 ;
            $_amount = fn_check_amount_in_stock($prod['product_id'], $prod['amount'], (!empty($prod['product_options']) ? $prod['product_options'] : array()), $_cart_id, (!empty($prod['is_edp']) && $prod['is_edp'] == 'Y' ? 'Y' : 'N'), (!empty($prod['original_product_data'][$selectable_cart_id]['amount']) ? $prod['original_product_data'][$selectable_cart_id]['amount'] : 0), $cart);

            if (empty($_amount)) {
                $amount = false;
                break;
            }
        }
    }
}

function fn_product_configurator_get_cart_product_data($product_id, &$_pdata, &$product, $auth, &$cart, $hash)
{
    if (!empty($product['configuration'])) {
        $cart['products'][$hash]['amount_total'] = 0;
        $tmp_cart = $cart;
        $_pdata['weight'] = 0;
        $total_amount = $product['amount'];
        $is_changed = false;
        foreach ($product['configuration'] as $item_id => $_data) {
            if (!empty($_data['no_product'])) {
                $product['configuration'][$item_id]['product'] = __("string") . ': ' . __("consult_string");
                $_pdata['configuration'][$item_id] = $product['configuration'][$item_id];
                continue;
            }
            $cart['products'][$hash]['amount_total'] += $_data['amount'];
            $amount = floor($product['configuration'][$item_id]['amount'] / $product['configuration'][$item_id]['extra']['step']);
            if ($total_amount > $amount) {
                $total_amount = $amount;
                $is_changed =  true;
            }
            if (!empty($_data['extra']['is_separate'])) {
                continue;
            }

            $_cproduct = fn_get_cart_product_data($item_id, $product['configuration'][$item_id], false, $tmp_cart, $auth);
            $_cproduct['is_accessible'] = fn_is_accessible_product($_cproduct);

            if (empty($_cproduct)) { // FIXME - for deleted products for OM
                unset($product['configuration'][$item_id]);

                continue;
            }

            $cart['products'][$hash]['price'] += $_cproduct['price'] * $_data['extra']['step'];
            $_pdata['price'] += $_cproduct['price'] * $_data['extra']['step'];
            // TennisHouse
            $_pdata['base_price'] += $_cproduct['base_price'] * $_data['extra']['step'];
            $_pdata['original_price'] += $_cproduct['original_price'] * $_data['extra']['step'];
            $_pdata['discount'] += $_cproduct['discount'] * $_data['extra']['step'];
            $_pdata['weight'] += $_cproduct['weight'] * $_data['extra']['step'];


            $_tax = (!empty($_cproduct['tax_summary']) ? ($_cproduct['tax_summary']['added'] / $_cproduct['amount']) : 0);
            $product['configuration'][$item_id]['original_price'] = $_cproduct['original_price'];
            $_cproduct['display_price'] = $_cproduct['price'] + (Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y' ? $_tax : 0);
            $_cproduct['step'] = $_data['extra']['step'];
            $_cproduct['group_id'] = $_data['extra']['group_id'];
            $_cproduct['subtotal'] = $_cproduct['price'] * $_data['extra']['step'];
            $product['configuration'][$item_id]['display_subtotal'] = $_cproduct['display_subtotal'] = $_cproduct['display_price'] * $_data['extra']['step'];
            $_pdata['configuration'][$item_id] = $_cproduct;
            $product['configuration'][$item_id]['product'] = $_cproduct['product'];
        }
        if ($is_changed) {
            $product['amount'] = $total_amount;
            foreach ($product['configuration'] as $item_id => $_data) {
                $_pdata['configuration'][$item_id]['amount'] = $product['configuration'][$item_id]['amount'] = (int) $product['configuration'][$item_id]['extra']['step'] * $total_amount;
            }
        }
    }
}

function fn_product_configurator_form_cart($order_info, &$cart)
{
    foreach ($cart['products'] as $k => &$product) {
        if (!empty($product['extra']['configuration_data'])) {
            $product['configuration'] = $product['extra']['configuration_data'];
            if (defined('ORDER_MANAGEMENT')) {
                foreach ($product['configuration'] as $_id => $conf) {
                    $product['configuration'][$_id]['original_amount'] = $conf['amount'];
                    $selectable_cart_id = fn_generate_cart_id($conf['product_id'], array('product_options' => (!empty($conf['product_options']) ? $conf['product_options'] : array())), true);
                    $product['configuration'][$_id]['original_product_data'][$selectable_cart_id] = $product['configuration'][$_id]['extra']['original_product_data'][$selectable_cart_id] = array (
                        'cart_id' => $selectable_cart_id,
                        'amount' => $conf['amount'],
                        'product_options' => !empty($conf['product_options']) ? $conf['product_options'] : array()
                    );
                    if (in_array($selectable_cart_id, array_keys($cart['original_product_data'][$conf['product_id']]))) {
                        $cart['original_product_data'][$conf['product_id']][$selectable_cart_id]['amount'] += $conf['amount'];
                    } else {
                        $cart['original_product_data'][$conf['product_id']][$selectable_cart_id] = $product['configuration'][$_id]['original_product_data'][$selectable_cart_id];
                    }
                }
            }
        }
    }
}

function fn_product_configurator_pre_add_to_cart(&$product_data, &$cart, $auth, $update)
{
    if (!empty($update)) {
        foreach ($product_data as $key => $value) {
            if (!empty($cart['products'][$key]['extra']['configuration']) && !empty($value['product_id'])) {
                $product_data[$key]['extra']['configuration'] = $cart['products'][$key]['extra']['configuration'];
                if (!empty($value['product_options'])) {
                    $product_data[$key]['extra']['product_options'] = $value['product_options'];
                }
                if (!empty($cart['products'][$key]['configuration'])) {
                    foreach ($cart['products'][$key]['configuration'] as $k => $v) {
                        $product_data[$k] = array(
                            'product_id' => $v['product_id'],
                            'amount' => $v['extra']['step'] * $value['amount'],
                            'extra' => array(
                                'parent' => array(
                                    'configuration' => $cart['products'][$key]['extra']['configuration_id'],
                                    'id' => $key
                                ),
                                'step' => $v['extra']['step'],
                                // TennisHouse
                                'is_separate' => $v['extra']['is_separate'],
                                'group_id' => $v['extra']['group_id'],
                            ),
                        );
                        if (!empty($v['product_options'])) {
                            $product_data[$k]['product_options'] = $v['product_options'];
                        }
                    }
                }

                $product_data[$key]['extra']['configuration_id'] = $cart['products'][$key]['extra']['configuration_id'];
            }
        }

    } else {
        foreach ($product_data as $key => $value) {
            if (!empty($value['configuration']) && !empty($value['product_id'])) {
                if (!empty($value['cart_id'])) {
                    fn_delete_cart_product($cart, $value['cart_id']);
                }

                $product_data[$key]['extra']['configuration'] = $value['configuration'];

                if (!empty($value['product_options'])) {
                    $product_data[$key]['extra']['product_options'] = $value['product_options'];
                }

                $cart_id = fn_generate_cart_id($key, $product_data[$key]['extra'], false);

                if (!empty($cart['products'][$cart_id])) {
                    $product_data[$key]['amount'] += $cart['products'][$cart_id]['amount'];
                }
                foreach ($value['configuration'] as $group_id => $_data) {
                    foreach ($_data['product_ids'] as $_id) {
                        if (!isset($product_data[$_id])) {
                            $product_data[$_id] = array();
                            $product_data[$_id]['product_id'] = $_id;
                            $product_data[$_id]['amount'] = (!empty($_data['amount']) ? $_data['amount'] : 1) * $product_data[$key]['amount'];
                            // TennisHouse
                            $product_data[$_id]['extra']['group_id'] = $group_id;
                            $product_data[$_id]['extra']['is_separate'] = $_data['is_separate'];
                            $product_data[$_id]['extra']['parent']['configuration'] = $cart_id;
                        } elseif (isset($product_data[$_id]['extra']['parent']['configuration']) && $product_data[$_id]['extra']['parent']['configuration'] == $cart_id) {
                            $product_data[$_id]['amount'] += (!empty($_data['amount']) ? $_data['amount'] : 1) * $product_data[$key]['amount'];
                        }
                        $product_data[$_id]['extra']['parent']['id'] = $value['product_id'];
                        if (!empty($_data['options'][$_id]['product_options'])) {
                            $product_data[$_id]['product_options'] = $_data['options'][$_id]['product_options'];
                        }
                    }
                }
                $product_data[$key]['extra']['configuration_id'] = $cart_id;
            }
        }
    }

    // We need to calculate step here because in configuration may be the same products.
    foreach ($product_data as $key => $value) { // We need set 'step' value for all products
        if (isset($value['extra']['parent']['id'])) {
            $parent_id = $value['extra']['parent']['id'];
            if (isset($product_data[$parent_id]) && $value['amount'] >= $product_data[$parent_id]['amount']) {
                $product_data[$key]['extra']['step'] = (int) ($value['amount'] / $product_data[$parent_id]['amount']);
            } else {
                $product_data[$key]['extra']['step'] = $value['amount'];
            }
        } elseif (!empty($value['extra']['configuration'])) {
            $product_data[$key]['extra']['step'] = $value['amount'];
        }
    }
}

function fn_product_configurator_add_to_cart(&$cart, $product_id, $_id)
{
    if (isset($cart['products'][$_id]['extra']['parent']['configuration'])) {
        $is_added = false;
        foreach ($cart['products'] as $key => $product) {
            if (isset($product['extra']['configuration_id']) && $product['extra']['configuration_id'] == $cart['products'][$_id]['extra']['parent']['configuration']) {
                $cart['products'][$_id]['extra']['parent']['configuration'] = $key;
                $is_added = true;
                break;
            }
        }
        if (!$is_added) {
            unset($cart['products'][$_id]);
            foreach ($cart['product_groups'] as $key_group => $group) {
                if (in_array($_id, array_keys($group['products']))) {
                    unset($cart['product_groups'][$key_group]['products'][$_id]);
                }
            }
        }
    }
}

/**
 * Chack main product product amount after adding product to cart
 *
 * @param array $product_data Product data
 * @param array $cart Cart data
 * @param array $auth Auth data
 * @param bool $update Flag the determains if cart data are updated
 * @return bool Always true
 */
function fn_product_configurator_post_add_to_cart($product_data, &$cart, $auth, $update)
{
    foreach ($cart['products'] as $key => $value) {
        if (!empty($value['extra']['configuration'])) {
            foreach ($cart['products'] as $k => $v) {
                if (isset($v['extra']['parent']['configuration']) && $v['extra']['parent']['configuration'] == $value['extra']['configuration_id']) {
                    $v['product_option_data'] = fn_get_selected_product_options_info($v['product_options']);
                    $cart['products'][$key]['configuration'][$k] = $v;
                    unset($cart['products'][$k]);
                }
            }
            foreach ($value['extra']['configuration'] as $i => $c_data) {
                if (in_array('CONSULT_STRINGING', $c_data['product_ids'])) {
                    $cart['products'][$key]['configuration'][CONSULT_STRINGING] = array(
                        'no_product' => true,
                        'amount' => 1
                    );
                }
            }
        }
    }
}

function fn_product_configurator_reorder_item(&$product)
{
    if (!empty($product['extra']['configuration_data'])) {
        $product['configuration'] = $product['extra']['configuration_data'];
        unset($product['extra']['configuration_data']);
    }
}

function fn_product_configurator_reorder(&$order_info, $cart, $auth)
{
    foreach ($order_info['products'] as $k => $v) {
        if (!empty($v['extra']['configuration_data'])) {
            foreach ($v['extra']['configuration_data'] as $i => $_product) {
                unset($order_info['products'][$k]['extra']['configuration_data'][$i]['extra']['warehouses']);
            }
        }
    }
}

function fn_product_configurator_change_order_status(&$status_to, $status_from, &$order_info, $force_notification, $order_statuses, $place_order)
{
    $_updated_ids = array();
    $_error = false;

    foreach ($order_info['products'] as $k => $v) {
        if (!empty($v['extra']['configuration_data'])) {
            foreach ($v['extra']['configuration_data'] as $i => $_product) {
                // Generate ekey if EDP is ordered
                if ((!empty($_product['extra']['is_edp']) && $_product['extra']['is_edp'] == 'Y') || empty($_product['product_id'])) {
                    continue; // don't track inventory
                }

                // Update product amount if inventory tracking is enabled
                if (Registry::get('settings.General.inventory_tracking') == 'Y') {
                    if ($order_statuses[$status_to]['params']['inventory'] == 'D' && $order_statuses[$status_from]['params']['inventory'] == 'I') {
                        // decrease amount
                        $order_warehouses = fn_update_product_amount($_product['product_id'], $_product['amount'], @$_product['extra']['product_options'], '-', @$_product['extra']['warehouses']);
                        if ($order_warehouses == false) {
                            $status_to = 'B'; //backorder
                            $_error = true;
                            fn_set_notification('W', __('warning'), __('low_stock_subj', array(
                                '[product]' => fn_get_product_name($_product['product_id']) . ' #' . $_product['product_id']
                            )));

                            break;
                        } else {
                            $order_info['products'][$k]['extra']['configuration_data'][$i]['extra']['warehouses'] = $order_warehouses;
                            $_updated_ids[$k][] = $i;
                        }
                    } elseif ($order_statuses[$status_to]['params']['inventory'] == 'I' && $order_statuses[$status_from]['params']['inventory'] == 'D') {
                        // increase amount
                        $order_info['products'][$k]['extra']['configuration_data'][$i]['extra']['warehouses'] = fn_update_product_amount($_product['product_id'], $_product['amount'], @$_product['extra']['product_options'], '+', @$_product['extra']['warehouses']);
                    }
                }
            }
        }
    }

    if ($_error) {
        if (!empty($_updated_ids)) {
            foreach ($_updated_ids as $id => $ids) {
                foreach ($ids as $i => $_id) {
                    // increase amount
                    fn_update_product_amount($order_info['products'][$id]['extra']['configuration_data'][$_id]['product_id'], $order_info['products'][$id]['extra']['configuration_data'][$_id]['amount'], @$order_info['products'][$id]['extra']['configuration_data'][$_id]['extra']['product_options'], '+', @$order_info['products'][$id]['extra']['configuration_data'][$_id]['extra']['warehouses']);
                }
            }
        }
    }
}

function fn_product_configurator_get_order_items_info_post(&$order, $v, $k)
{
    if (!empty($v['extra']['pc_original_price'])) {
        $order['products'][$k]['original_price'] = $v['extra']['pc_original_price'];
    }
    if (!empty($v['extra']['configuration_data'])) {
        foreach ($v['extra']['configuration_data'] as $i => $cp) {
            $order['products'][$k]['extra']['configuration_data'][$i]['is_accessible'] = fn_is_accessible_product($cp);
            $order['products'][$k]['extra']['configuration_data'][$i]['discount_prc'] = sprintf('%d', round($order['products'][$k]['extra']['configuration_data'][$i]['discount'] * 100 / $order['products'][$k]['extra']['configuration_data'][$i]['original_price']));;
        }
    }
}

function fn_product_configurator_create_order_details(&$order_details, $item_id, $order_id, $cart)
{
    if (!empty($cart['products'][$item_id]['configuration'])) {
        $extra = unserialize($order_details['extra']);
        $extra['configuration_data'] = $cart['products'][$item_id]['configuration'];
        $order_details['extra'] = serialize($extra);
    }
}

/**
 * Prepare configurable product data to add it to wishlist
 *
 * @param array $product_data product data
 * @param array $wishlist wishlist storage
 * @param array $auth user session data
 * @return boolean always true
 */
function fn_product_configurator_pre_add_to_wishlist(&$product_data, &$wishlist, $auth)
{
    $update = false;
    fn_product_configurator_pre_add_to_cart($product_data, $wishlist, $auth, $update);
}

function fn_product_configurator_post_add_to_wishlist(&$product_data, &$wishlist, $auth)
{
    fn_product_configurator_post_add_to_cart($product_data, $wishlist, $auth);
}

function fn_product_configurator_buy_together_restricted_product($product_id, $auth, &$is_restricted, $show_notification)
{
    if ($is_restricted) {
        return true;
    }

    $product_data = Registry::get('view')->getTemplateVars('product_data');

    if (!empty($product_data)) {
        if ($product_data['product_type'] == 'C') {
            $is_restricted = true;
        }

    } elseif (!empty($product_id)) {
        $product_data = fn_get_product_data($product_id, $auth, CART_LANGUAGE, '', true, true, true, true);

        if ($product_data['product_type'] == 'C') {
            $is_restricted = true;
        }
    }

    if ($is_restricted && $show_notification) {
        fn_set_notification('E', __('error'), __('buy_together_is_not_compatible_with_configurator', array(
            '[product_name]' => $product_data['product']
        )));
    }
}

function fn_product_configurator_calculate_options($cart_products, &$cart, $auth)
{
    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $id => &$product) {
            if (!empty($product['extra']['parent']['configuration']) && !empty($cart['products'][$product['extra']['parent']['configuration']]['object_id'])) {
                $product['extra']['parent']['configuration'] = $cart['products'][$product['extra']['parent']['configuration']]['object_id'];
            }
        }
    }
}

function fn_product_configurator_amazon_products(&$cart_products, $cart)
{
    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $cart_id => $product) {
            if (!empty($product['extra']['configuration'])) {
                foreach ($cart['products'] as $_id => $_product) {
                    if (isset($_product['extra']['parent']['configuration']) && $_product['extra']['parent']['configuration'] == $cart_id) {
                        $cart_products[$cart_id]['price'] -= $cart_products[$_id]['price'];
                    }
                }
            }
        }
    }
}

function fn_product_configurator_update_product_pre(&$product_data, $product_id, $lang_code, $can_update)
{
    if ($can_update == false) {
        return false;
    }
    if (!empty($product_data['product_type']) && $product_data['product_type'] == 'C') {
        $product_data['zero_price_action'] = 'P';
        $product_data['price_mode'] = 'S';
        $product_data['tracking'] = 'D';
        $product_data['discussion_type'] = 'D';

        if (fn_allowed_for('ULTIMATE')) {
            if (!empty($product_id) && !empty($product_data['company_id'])) {
                $product_company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id);

                if ($product_data['company_id'] != $product_company_id) {
                    // check if product is used in product groups
                    $is_in_conf = false;
                    if ($product_data['product_type'] == 'C') {
                        $is_in_conf = true;
                    } elseif (db_get_field("SELECT count(1) FROM ?:conf_group_products WHERE product_id = ?i", $product_id)) {
                        $is_in_conf = true;
                    } elseif (db_get_field("SELECT count(1) FROM ?:conf_class_products WHERE product_id = ?i", $product_id)) {
                        $is_in_conf = true;
                    }

                    if ($is_in_conf) {
                        $product_data['company_id'] = $product_company_id;
                        fn_set_notification('W', __('warning'), __('pconf_company_update_denied'));
                    }
                }
            }
        }
    }

    return true;
}

function fn_get_customization_options(&$product, $selected_configuration, $get_stringing = true)
{
    static $_products, $product_options, $product_exceptions;
    $cids = $show_out_of_stock_ids = $stringing_options = $dampener_options = $overgrip_options = array();

    if (!empty($selected_configuration[STRINGING_GROUP_ID]) && !isset($_products[STRINGING_GROUP_ID])) {
        $cids[] = STRINGS_CATEGORY_ID;
    }
    if (!empty($selected_configuration[DAMPENER_GROUP_ID]) && !isset($_products[DAMPENER_GROUP_ID])) {
        $cids[] = DAMPENERS_CATEGORY_ID;
    }
    if (!empty($selected_configuration[OVERGRIP_GROUP_ID]) && !isset($_products[OVERGRIP_GROUP_ID])) {
        $cids[] = OVERGRIPS_CATEGORY_ID;
    }
    if (!empty($cids)) {
        $params = array(
            'cid' => implode(',', $cids),
            'subcats' => 'Y'
        );

        list($products, $search) = fn_get_products($params);
        if (!empty($products)) {
            foreach ($products as $i => $prod) {
                if ($prod['product_id'] == STRINGING_PRODUCT_ID) {
                    $_products[STRINGING_TENSION_GROUP_ID][$prod['product_id']] = $prod;
                } elseif ($prod['main_category'] == STRINGS_CATEGORY_ID) {
                    $_products[STRINGING_GROUP_ID][$prod['product_id']] = $prod;
                } elseif ($prod['main_category'] == DAMPENERS_CATEGORY_ID) {
                    $_products[DAMPENER_GROUP_ID][$prod['product_id']] = $prod;
                } elseif ($prod['main_category'] == OVERGRIPS_CATEGORY_ID) {
                    $_products[OVERGRIP_GROUP_ID][$prod['product_id']] = $prod;
                }
            }
        }
    }
    $cart_ref = $group_ref = array();
    if (!empty($product['configuration'])) {
        foreach ($product['configuration'] as $group_id => $prod) {
            $cart_ref[$prod['product_id']] = $group_ref[$prod['group_id']] = $group_id;
        }
    }

    $opt_product_ids = $exc_product_ids = $ids_ref = array();
    foreach ($_products as $group => $prods) {
        $fill_opt = $fill_exc = false;
        if (!isset($product_options[$group])) {
            $fill_opt = true;
        }
        if (!isset($product_exceptions[$group])) {
            $fill_exc = true;
        }
        $selected_id = reset($selected_configuration[$group]['product_ids']);
        foreach ($prods as $i => $v) {
            $ids_ref[$v['product_id']] = $group;
            if (!empty($fill_opt) && !in_array($v['product_id'], $opt_product_ids)) {
                $opt_product_ids[] = $v['product_id'];
            }
            if (!empty($fill_exc) && !in_array($v['product_id'], $exc_product_ids)) {
                $exc_product_ids[] = $v['product_id'];
            }
            if ($v['product_id'] == $selected_id && !empty($product['configuration'][$cart_ref[$v['product_id']]]['original_product_data'])) {
                $_products[$group][$i]['original_product_data'] = $product['configuration'][$cart_ref[$v['product_id']]]['original_product_data'];
            }
        }
    }
    if (!empty($opt_product_ids)) {
        $options = fn_get_product_options($opt_product_ids, CART_LANGUAGE, false, false, false, false, false);
        if (!empty($options)) {
            foreach ($options as $prod_id => $opts) {
                $product_options[$ids_ref[$prod_id]][$prod_id] = $opts;
            }
        }
    }
    if (!empty($exc_product_ids)) {
        $exceptions = fn_get_products_exceptions($exc_product_ids, true);
        if (!empty($exceptions)) {
            foreach ($exceptions as $prod_id => $excps) {
                $product_exceptions[$ids_ref[$prod_id]][$prod_id] = $excps;
            }
        }
    }

    $c_price = 0;
    if (!empty($selected_configuration[STRINGING_GROUP_ID])) {
        $stringing_options = array(
            'group_id' => STRINGING_GROUP_ID,
            'configurator_group_name' => __("string"),
            'full_description' => __("select_stringing"),
            'configurator_group_type' => 'S',
            'position' => 0,
            'default_product_ids' => 'CONSULT_STRINGING',
            'required' => 'N',
            'max_amount' => 1,
            'amount' => 1,
            'is_separate' => true,
            'group_item_id' => $group_ref[STRINGING_GROUP_ID]
        );
        $base_stringing_options = array(
            array(
                'product_id' => 'UNSTRUNG',
                'product' => __('unstrung'),
                'no_product' => true
            ),
            array(
                'product_id' => 'CONSULT_STRINGING',
                'product' => __('consult_string'),
                'no_product' => true,
                'no_price' => true
            )
        );
        list($stringing_options, $cprice) = fn_format_customization_group($_products[STRINGING_GROUP_ID], $stringing_options, $base_stringing_options, $selected_configuration[STRINGING_GROUP_ID], $product_options[STRINGING_GROUP_ID], $product_exceptions[STRINGING_GROUP_ID]);
        $c_price += $cprice;
        $product['product_configurator_groups'][STRINGING_GROUP_ID] = $stringing_options;
    }

    if (!empty($selected_configuration[STRINGING_TENSION_GROUP_ID])) {
        $tension_product = reset($_products[STRINGING_TENSION_GROUP_ID]);
        if (!empty($product_options[STRINGING_TENSION_GROUP_ID][STRINGING_PRODUCT_ID])) {
            if (!empty($selected_configuration[STRINGING_TENSION_GROUP_ID]['options'][STRINGING_PRODUCT_ID]['product_options'])) {
                $tension_product['selected_options'] = $selected_configuration[STRINGING_TENSION_GROUP_ID]['options'][STRINGING_PRODUCT_ID]['product_options'];
            }
            fn_apply_selected_options($tension_product, $product_options[STRINGING_TENSION_GROUP_ID][STRINGING_PRODUCT_ID]);
        }
        $tension_options = array(
            'group_id' => STRINGING_TENSION_GROUP_ID,
            'configurator_group_name' => __("stringing_service"),
            'configurator_group_type' => 'T',
            'position' => 0,
            'default_product_ids' => STRINGING_PRODUCT_ID,
            'product' => $tension_product,
            'is_separate' => true,
            'group_item_id' => $group_ref[STRINGING_TENSION_GROUP_ID]
        );
        $c_price += $tension_product['price'];
        $product['product_configurator_groups'][STRINGING_TENSION_GROUP_ID] = $tension_options;
    }

    if (!empty($selected_configuration[DAMPENER_GROUP_ID])) {
        $dampener_options = array(
            'group_id' => DAMPENER_GROUP_ID,
            'configurator_group_name' => __("dampener"),
            'full_description' => __("select_dampener"),
            'configurator_group_type' => 'S',
            'position' => 0,
            'default_product_ids' => 0,
            'required' => 'N',
            'max_amount' => 1,
            'amount' => 1,
            'is_separate' => true,
            'group_item_id' => $group_ref[DAMPENER_GROUP_ID],
            'description' => db_get_field("SELECT description FROM ?:category_descriptions WHERE category_id = ?i and lang_code = ?s", DAMPENERS_CATEGORY_ID, CART_LANGUAGE)
        );
        $base_dampener_options = array(
            array(
                'product_id' => 'NODAMPENER',
                'product' => __('no_dampener'),
                'no_product' => true
            ),
        );
        list($dampener_options, $cprice) = fn_format_customization_group($_products[DAMPENER_GROUP_ID], $dampener_options, $base_dampener_options, $selected_configuration[DAMPENER_GROUP_ID], $product_options[DAMPENER_GROUP_ID], $product_exceptions[DAMPENER_GROUP_ID]);
        $c_price += $cprice;
        $product['product_configurator_groups'][DAMPENER_GROUP_ID] = $dampener_options;
    }

    if (!empty($selected_configuration[OVERGRIP_GROUP_ID])) {
        $overgrip_options = array(
            'group_id' => OVERGRIP_GROUP_ID,
            'configurator_group_name' => __("overgrip"),
            'full_description' => __("select_overgrip"),
            'configurator_group_type' => 'S',
            'position' => 0,
            'default_product_ids' => 0,
            'required' => 'N',
            'max_amount' => 1,
            'amount' => 1,
            'is_separate' => true,
            'group_item_id' => $group_ref[OVERGRIP_GROUP_ID],
            'description' => db_get_field("SELECT description FROM ?:category_descriptions WHERE category_id = ?i and lang_code = ?s", OVERGRIPS_CATEGORY_ID, CART_LANGUAGE)
        );
        $base_overgrip_options = array(
            array(
                'product_id' => 'NOOVERGRIP',
                'product' => __('no_overgrip'),
                'no_product' => true
            ),
        );
        list($overgrip_options, $cprice) = fn_format_customization_group($_products[OVERGRIP_GROUP_ID], $overgrip_options, $base_overgrip_options, $selected_configuration[OVERGRIP_GROUP_ID], $product_options[OVERGRIP_GROUP_ID], $product_exceptions[OVERGRIP_GROUP_ID]);
        $c_price += $cprice;
        $product['product_configurator_groups'][OVERGRIP_GROUP_ID] = $overgrip_options;
    }

    return array($stringing_options, $dampener_options, $overgrip_options, $c_price);
}

function fn_format_customization_group($_products, $group_options, $base_group_options, $selected_configuration = array(), $options = array(), $exceptions = array())
{
    $c_price = 0;
    $original_product_ids = !empty($_SESSION['cart']['original_product_data']) ? array_keys($_SESSION['cart']['original_product_data']) : array();

    $default_ids = explode(':', $group_options['default_product_ids']);
    $selected_ids = empty($selected_configuration['product_ids']) ? $default_ids : (!is_array($selected_configuration['product_ids']) ? array($selected_configuration['product_ids']) : $selected_configuration['product_ids']);
    $selected_options = empty($selected_configuration['options']) ? array() : $selected_configuration['options'];

    foreach ($_products as $_k => $_v) {

        if (in_array($_v['product_id'], $selected_ids)) {
            if (!empty($selected_options[$_v['product_id']]['product_options'])) {
                $_products[$_k]['selected_options'] = $selected_options[$_v['product_id']]['product_options'];
            }
        }
        if (($_v['status'] == 'D' || $_v['amount'] <= 0) && (empty($original_product_ids) || !in_array($_v['product_id'], $original_product_ids))) {
            unset($_products[$_k]);
        }
    }

    foreach ($_products as $i => $prod) {
        if (!empty($options[$prod['product_id']])) {
            fn_apply_selected_options($_products[$i], $options[$prod['product_id']], $exceptions[$prod['product_id']]);
        }
    }

    $_products = array_merge($base_group_options, $_products);

    $class_ids = array();
    $is_selected = false;
    foreach ($_products as $_k => $_v) {

        $_products[$_k] = $_v;

        if (in_array($_v['product_id'], $selected_ids)) {
            $group_options['no_product'] = !empty($_v['no_product']) ? $_v['no_product'] : false;
            $is_selected = true;
            $_products[$_k]['selected'] = 'Y';
            $group_options['selected_product'] = $_v['product_id'];
            if (!empty($_v['price'])) {
                $c_price += $_v['price'] * $group_options['amount'];
            }
        } else {
            $_products[$_k]['selected'] = 'N';
        }

        // Recommended products
        if (in_array($_v['product_id'], $default_ids)) {
            $_products[$_k]['recommended'] = 'Y';
        }

        if (!empty($_v['class_ids'])) {
            $_products[$_k]['class_ids'] = explode(',', $_v['class_ids']);
            $class_ids = array_merge($class_ids, $_products[$_k]['class_ids']);
        }
    }

    $group_options['products_count'] = count($_products);
    $group_options['products'] = $_products;

    return array($group_options, $c_price);
}

function fn_get_stringing_options(&$product, $selected_configuration, $get_stringing = true)
{
    static $_products;

    $stringing_options = array(
        'group_id' => STRINGING_GROUP_ID,
        'configurator_group_name' => __("string"),
        'full_description' => __("select_stringing"),
        'configurator_group_type' => 'S',
        'position' => 0,
        'default_product_ids' => 'CONSULT_STRINGING',
        'required' => 'N',
        'max_amount' => 1,
        'amount' => 1,
        'is_separate' => true
    );
    $base_stringing_options = array(
        array(
            'product_id' => 'UNSTRUNG',
            'product' => __('unstrung'),
            'no_product' => true
        ),
        array(
            'product_id' => 'CONSULT_STRINGING',
            'product' => __('consult_string'),
            'no_product' => true,
            'no_price' => true
        )
    );
    $c_price = 0;

    if (!isset($_products)) {
        $params = array(
            'cid' => STRINGS_CATEGORY_ID,
            'subcats' => 'Y'
        );
        list($_products, $search) = fn_get_products($params);
    }

    $default_ids = explode(':', $stringing_options['default_product_ids']);
    $selected_ids = empty($selected_configuration[$stringing_options['group_id']]['product_ids']) ? $default_ids : (!is_array($selected_configuration[$stringing_options['group_id']]['product_ids']) ? array($selected_configuration[$stringing_options['group_id']]['product_ids']) : $selected_configuration[$stringing_options['group_id']]['product_ids']);
    $selected_options = empty($selected_configuration[$stringing_options['group_id']]['options']) ? array() : $selected_configuration[$stringing_options['group_id']]['options'];
    foreach ($_products as $_k => $_v) {
        if (in_array($_v['product_id'], $selected_ids)) {
            if (!empty($selected_options[$_v['product_id']]['product_options'])) {
                $_products[$_k]['selected_options'] = $selected_options[$_v['product_id']]['product_options'];
            }
        }
        if ($_v['status'] == 'D' || $_v['amount'] <= 0) {
            unset($_products[$_k]);
        }
    }

    fn_gather_additional_products_data($_products, array(
        'get_icon' => false,
        'get_detailed' => false,
        'get_additional' => false,
        'get_options' => true,
        'get_discounts' => false,
        'get_features' => false,
        'get_title_features' => false,
        'allow_duplication' => false
    ));

    $_products = array_merge($base_stringing_options, $_products);

    $class_ids = array();
    $is_selected = false;
    foreach ($_products as $_k => $_v) {

        $_products[$_k] = $_v;

        if (in_array($_v['product_id'], $selected_ids)) {
            $stringing_options['no_product'] = !empty($_v['no_product']) ? $_v['no_product'] : false;
            $is_selected = true;
            $_products[$_k]['selected'] = 'Y';
            $stringing_options['selected_product'] = $_v['product_id'];
            if (!empty($_v['price'])) {
                $c_price += $_v['price'] * $stringing_options['amount'];
            }
        } else {
            $_products[$_k]['selected'] = 'N';
        }

        // Recommended products
        if (in_array($_v['product_id'], $default_ids)) {
            $_products[$_k]['recommended'] = 'Y';
        }

        if (!empty($_v['class_ids'])) {
            $_products[$_k]['class_ids'] = explode(',', $_v['class_ids']);
            $class_ids = array_merge($class_ids, $_products[$_k]['class_ids']);
        }
    }

    $stringing_options['products_count'] = count($_products);
    $stringing_options['products'] = $_products;

    $product['product_configurator_groups'][STRINGING_GROUP_ID] = $stringing_options;
    if ($get_stringing) {
        $params = array(
            'pid' => STRINGING_PRODUCT_ID,
            'view_statuses' => array('A', 'H')
        );
        list($_tension, $search) = fn_get_products($params);
        if (!in_array('CONSULT_STRINGING', $selected_configuration[STRINGING_GROUP_ID]['product_ids'])) {
            $_tension[0]['selected_options'] = empty($selected_configuration[STRINGING_TENSION_GROUP_ID]['options'][STRINGING_PRODUCT_ID]['product_options']) ? array() : $selected_configuration[STRINGING_TENSION_GROUP_ID]['options'][STRINGING_PRODUCT_ID]['product_options'];
            fn_gather_additional_products_data($_tension, array(
                'get_icon' => false,
                'get_detailed' => false,
                'get_additional' => false,
                'get_options' => true,
                'get_discounts' => false,
                'get_features' => false,
                'get_title_features' => false,
                'allow_duplication' => false
            ));
        }
        $tension_product = reset($_tension);
        $tension_options = array(
            'group_id' => STRINGING_TENSION_GROUP_ID,
            'configurator_group_name' => __("stringing_service"),
            'configurator_group_type' => 'T',
            'position' => 0,
            'default_product_ids' => STRINGING_PRODUCT_ID,
            'product' => $tension_product,
            'is_separate' => true
        );
        $c_price += $tension_product['price'];
        $product['product_configurator_groups'][STRINGING_TENSION_GROUP_ID] = $tension_options;
    }

    return array($stringing_options, $c_price);
}

function fn_get_dampener_options(&$product, $selected_configuration)
{
    static $_products;

    $dampener_options = array(
        'group_id' => DAMPENER_GROUP_ID,
        'configurator_group_name' => __("dampener"),
        'full_description' => __("select_dampener"),
        'configurator_group_type' => 'S',
        'position' => 0,
        'default_product_ids' => 0,
        'required' => 'N',
        'max_amount' => 1,
        'amount' => 1,
        'is_separate' => true,
        'description' => db_get_field("SELECT description FROM ?:category_descriptions WHERE category_id = ?i and lang_code = ?s", DAMPENERS_CATEGORY_ID, CART_LANGUAGE)
    );
    $base_dampener_options = array(
        array(
            'product_id' => 'NODAMPENER',
            'product' => __('no_dampener'),
            'no_product' => true
        ),
    );
    $c_price = 0;

    if (!isset($_products)) {
        $params = array(
            'cid' => DAMPENERS_CATEGORY_ID,
            'subcats' => 'Y'
        );
        list($_products, $search) = fn_get_products($params);
    }

    $default_ids = explode(':', $dampener_options['default_product_ids']);
    $selected_ids = empty($selected_configuration[$dampener_options['group_id']]['product_ids']) ? $default_ids : (!is_array($selected_configuration[$dampener_options['group_id']]['product_ids']) ? array($selected_configuration[$dampener_options['group_id']]['product_ids']) : $selected_configuration[$dampener_options['group_id']]['product_ids']);
    $selected_options = empty($selected_configuration[$dampener_options['group_id']]['options']) ? array() : $selected_configuration[$dampener_options['group_id']]['options'];

    foreach ($_products as $_k => $_v) {

        if (in_array($_v['product_id'], $selected_ids)) {
            if (!empty($selected_options[$_v['product_id']]['product_options'])) {
                $_products[$_k]['selected_options'] = $selected_options[$_v['product_id']]['product_options'];
            }
        }
        if ($_v['status'] == 'D' || $_v['amount'] <= 0) {
            unset($_products[$_k]);
        }
    }

    fn_gather_additional_products_data($_products, array(
        'get_icon' => false,
        'get_detailed' => true,
        'get_additional' => false,
        'get_options' => true,
        'get_discounts' => false,
        'get_features' => false,
        'get_title_features' => false,
        'allow_duplication' => false
    ));

    $_products = array_merge($base_dampener_options, $_products);

    $class_ids = array();
    $is_selected = false;
    foreach ($_products as $_k => $_v) {

        $_products[$_k] = $_v;

        if (in_array($_v['product_id'], $selected_ids)) {
            $dampener_options['no_product'] = !empty($_v['no_product']) ? $_v['no_product'] : false;
            $is_selected = true;
            $_products[$_k]['selected'] = 'Y';
            $dampener_options['selected_product'] = $_v['product_id'];
            if (!empty($_v['price'])) {
                $c_price += $_v['price'] * $dampener_options['amount'];
            }
        } else {
            $_products[$_k]['selected'] = 'N';
        }

        // Recommended products
        if (in_array($_v['product_id'], $default_ids)) {
            $_products[$_k]['recommended'] = 'Y';
        }

        if (!empty($_v['class_ids'])) {
            $_products[$_k]['class_ids'] = explode(',', $_v['class_ids']);
            $class_ids = array_merge($class_ids, $_products[$_k]['class_ids']);
        }
    }

    $dampener_options['products_count'] = count($_products);
    $dampener_options['products'] = $_products;

    $product['product_configurator_groups'][DAMPENER_GROUP_ID] = $dampener_options;

    return array($dampener_options, $c_price);
}

function fn_get_overgrip_options(&$product, $selected_configuration)
{
    static $_products;

    $overgrip_options = array(
        'group_id' => OVERGRIP_GROUP_ID,
        'configurator_group_name' => __("overgrip"),
        'full_description' => __("select_overgrip"),
        'configurator_group_type' => 'S',
        'position' => 0,
        'default_product_ids' => 0,
        'required' => 'N',
        'max_amount' => 1,
        'amount' => 1,
        'is_separate' => true,
        'description' => db_get_field("SELECT description FROM ?:category_descriptions WHERE category_id = ?i and lang_code = ?s", OVERGRIPS_CATEGORY_ID, CART_LANGUAGE)
    );
    $base_overgrip_options = array(
        array(
            'product_id' => 'NOOVERGRIP',
            'product' => __('no_overgrip'),
            'no_product' => true
        ),
    );
    $c_price = 0;

    if (!isset($_products)) {
        $params = array(
            'cid' => OVERGRIPS_CATEGORY_ID,
            'subcats' => 'Y'
        );
        list($_products, $search) = fn_get_products($params);
    }

    $default_ids = explode(':', $overgrip_options['default_product_ids']);
    $selected_ids = empty($selected_configuration[$overgrip_options['group_id']]['product_ids']) ? $default_ids : (!is_array($selected_configuration[$overgrip_options['group_id']]['product_ids']) ? array($selected_configuration[$overgrip_options['group_id']]['product_ids']) : $selected_configuration[$overgrip_options['group_id']]['product_ids']);
    $selected_options = empty($selected_configuration[$overgrip_options['group_id']]['options']) ? array() : $selected_configuration[$overgrip_options['group_id']]['options'];

    foreach ($_products as $_k => $_v) {

        if (in_array($_v['product_id'], $selected_ids)) {
            if (!empty($selected_options[$_v['product_id']]['product_options'])) {
                $_products[$_k]['selected_options'] = $selected_options[$_v['product_id']]['product_options'];
            }
        }
        if ($_v['status'] == 'D' || $_v['amount'] <= 0) {
            unset($_products[$_k]);
        }
    }

    fn_gather_additional_products_data($_products, array(
        'get_icon' => false,
        'get_detailed' => true,
        'get_additional' => false,
        'get_options' => true,
        'get_discounts' => false,
        'get_features' => false,
        'get_title_features' => false,
        'allow_duplication' => false
    ));
    $_products = array_merge($base_overgrip_options, $_products);

    $class_ids = array();
    $is_selected = false;
    foreach ($_products as $_k => $_v) {

        $_products[$_k] = $_v;

        if (in_array($_v['product_id'], $selected_ids)) {
            $overgrip_options['no_product'] = !empty($_v['no_product']) ? $_v['no_product'] : false;
            $is_selected = true;
            $_products[$_k]['selected'] = 'Y';
            $overgrip_options['selected_product'] = $_v['product_id'];
            if (!empty($_v['price'])) {
                $c_price += $_v['price'] * $overgrip_options['amount'];
            }
        } else {
            $_products[$_k]['selected'] = 'N';
        }

        // Recommended products
        if (in_array($_v['product_id'], $default_ids)) {
            $_products[$_k]['recommended'] = 'Y';
        }

        if (!empty($_v['class_ids'])) {
            $_products[$_k]['class_ids'] = explode(',', $_v['class_ids']);
            $class_ids = array_merge($class_ids, $_products[$_k]['class_ids']);
        }
    }

    $overgrip_options['products_count'] = count($_products);
    $overgrip_options['products'] = $_products;

    $product['product_configurator_groups'][OVERGRIP_GROUP_ID] = $overgrip_options;

    return array($overgrip_options, $c_price);
}

function fn_get_configuration_groups(&$product, $selected_configuration)
{
    $_tmp = microtime();
    $product_configurator_groups = db_get_array(
        "SELECT ?:conf_groups.group_id, ?:conf_group_descriptions.configurator_group_name, ?:conf_group_descriptions.amount_field, "
            . "?:conf_group_descriptions.full_description, ?:conf_groups.configurator_group_type, "
            . "?:conf_product_groups.position, ?:conf_product_groups.default_product_ids, ?:conf_product_groups.required, ?:conf_product_groups.max_amount "
        . "FROM ?:conf_groups "
            . "LEFT JOIN ?:conf_group_descriptions "
                . "ON ?:conf_group_descriptions.group_id = ?:conf_groups.group_id "
            . "LEFT JOIN ?:conf_product_groups "
                . "ON ?:conf_product_groups.group_id = ?:conf_groups.group_id  "
        . "WHERE ?:conf_groups.status = 'A' AND ?:conf_group_descriptions.lang_code = ?s "
            . "AND ?:conf_product_groups.product_id = ?i "
        . "ORDER BY ?:conf_product_groups.position",
        CART_LANGUAGE, $product['product_id']
    );

    $product['hide_stock_info'] = false;
    if (!empty($product_configurator_groups)) {
        $c_price = 0;

        $params = $inventory = array();
        foreach ($product_configurator_groups as $k => $v) {

            $params['pc_group_id'] = $v['group_id'];
            list($_products, $search) = fn_get_products($params);

            if (empty($_products)) {
                if ($v['required'] == 'Y') {
                    $product['amount'] = 0;
                    $product['tracking'] = 'B';
                    $product['configuration_out_of_stock'] = true;
                    $product['hide_stock_info'] = false;
                    $inventory = array();
                    break;
                }
                continue;
            }

            $default_ids = explode(':', $v['default_product_ids']);
            $selected_ids = empty($selected_configuration[$v['group_id']]['product_ids']) ? $default_ids : (!is_array($selected_configuration[$v['group_id']]['product_ids']) ? array($selected_configuration[$v['group_id']]['product_ids']) : $selected_configuration[$v['group_id']]['product_ids']);
            $selected_options = empty($selected_configuration[$v['group_id']]['options']) ? array() : $selected_configuration[$v['group_id']]['options'];
            $amount = !empty($selected_configuration[$v['group_id']]['amount']) ? $selected_configuration[$v['group_id']]['amount'] : 1;

            $class_ids = array();
            $is_selected = false;
            foreach ($_products as $_k => $_v) {

                $_products[$_k] = $_v;

                if (in_array($_v['product_id'], $selected_ids)) {
                    $is_selected = true;
                    $_products[$_k]['selected'] = 'Y';
                    if (!empty($selected_options[$_v['product_id']]['product_options'])) {
                        $_products[$_k]['selected_options'] = $selected_options[$_v['product_id']]['product_options'];
                    }
                } else {
                    $_products[$_k]['selected'] = 'N';
                }

                // Recommended products
                if (in_array($_v['product_id'], $default_ids)) {
                    $_products[$_k]['recommended'] = 'Y';
                }

                if (!empty($_v['class_ids'])) {
                    $_products[$_k]['class_ids'] = explode(',', $_v['class_ids']);
                    $class_ids = array_merge($class_ids, $_products[$_k]['class_ids']);
                }
            }

            if (!$is_selected) {
                $product['hide_stock_info'] = true;
            }
            fn_gather_additional_products_data($_products, array(
                'get_icon' => false,
                'get_detailed' => true,
                'get_additional' => false,
                'get_options' => true,
                'get_discounts' => false,
                'get_features' => false,
                'get_title_features' => true,
                'allow_duplication' => false
            ));

            $classes = db_get_hash_multi_array("SELECT ?:conf_compatible_classes.slave_class_id, ?:conf_compatible_classes.master_class_id FROM ?:conf_compatible_classes LEFT JOIN ?:conf_classes ON ?:conf_classes.class_id = ?:conf_compatible_classes.slave_class_id WHERE ?:conf_compatible_classes.master_class_id IN (?n) AND ?:conf_classes.status = 'A'", array('master_class_id', 'slave_class_id'), $class_ids);

            foreach ($_products as $_k => $_v) {
                $_products[$_k]['compatible_classes'] = array();
                if ($_v['selected'] == 'Y') {
                    if (!empty($_v['hide_stock_info'])) {
                        $product['hide_stock_info'] = true;
                    } else {
                        if (!empty($_v['tracking']) && $_v['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
                            $max_amount = $_v['inventory_amount'];
                        } else {
                            $max_amount = $_v['amount'];
                        }
                    }
                }
                if (!empty($_v['class_ids'])) {
                    foreach ($_v['class_ids'] as $l => $cl_id) {
                        if (!empty($classes[$cl_id])) {
                            $_products[$_k]['compatible_classes'] += $classes[$cl_id];
                        }
                    }
                }
            }

            if ($product_configurator_groups[$k]['max_amount'] > 1 && isset($max_amount)) {
                $product_configurator_groups[$k]['show_amount'] = true;
            }
            if (isset($max_amount) && $product_configurator_groups[$k]['max_amount'] > $max_amount) {
                $product_configurator_groups[$k]['max_amount'] = $max_amount;
            }
            $product_configurator_groups[$k]['amount'] = ($product_configurator_groups[$k]['max_amount'] < $amount) ? $product_configurator_groups[$k]['max_amount'] : $amount;
            if (isset($max_amount)) {
                $inventory[] = floor($max_amount / $product_configurator_groups[$k]['amount']);
            }
            foreach ($_products as $_k => $_v) {
                if ($_v['selected'] == 'Y') {
                    $product_configurator_groups[$k]['selected_product'] = $_v['product_id'];
                    $c_price += $_v['price'] * $product_configurator_groups[$k]['amount'];
                }
            }

            $product_configurator_groups[$k]['products_count'] = count($_products);
            $product_configurator_groups[$k]['products'] = $_products;
            $product_configurator_groups[$k]['main_pair'] = fn_get_image_pairs($v['group_id'], 'conf_group', 'M');
        }
        if (!empty($inventory)) {
            $product['amount'] = min($inventory);
        }
    }

    // Substitute configuration price instead of product price
    if (!empty($c_price)) {
//         $product['price'] = $product['base_price'] = $product['original_price'] = $c_price;
        $product['price'] += $c_price;
        $product['base_price'] += $c_price;
        if (isset($product['original_price'])) {
            $product['original_price'] += $c_price;
        }
    }

    if (!empty($product_configurator_groups)) {

        // Define list of incompatible products
        $tmp = $product_configurator_groups;
        foreach ($product_configurator_groups as $k => $v) {
            foreach ($v['products'] as $_k => $_v) {
                if ($_v['selected'] == 'Y' && !empty($_v['compatible_classes'])) {
                    foreach ($tmp as $t_key => $t_val) {
                        if ($v['group_id'] !=  $t_val['group_id']) {
                            foreach ($t_val['products'] as $t_kk => $t_vv) {
                                if (!empty($t_vv['class_ids']) && !empty(array_intersect($t_vv['class_ids'], array_keys($_v['compatible_classes'])))) {
                                    $tmp[$t_key]['products'][$t_kk]['disabled'] = false;
                                } else {
                                    $tmp[$t_key]['products'][$t_kk]['disabled'] = true;
                                }
                            }
                        }
                    }
                }
            }
        }

        $product['product_configurator_groups'] = $tmp;
    }

    return $product_configurator_groups;
}

function fn_product_configurator_get_additional_information(&$product, $product_data)
{
    if (!empty($product_data['product_data'][$product['product_id']]['configuration'])) {
        $product['selected_configuration'] = $product_data['product_data'][$product['product_id']]['configuration'];
    }
    if (!empty($product_data['product_data'][$product['product_id']]['cart_id'])) {
        $product['cart_id'] = $product_data['product_data'][$product['product_id']]['cart_id'];
    }
}

/**
 * Calculates price of selected configuration products
 *
 * @param array $conf_product_groups Product groups with selected products identifiers
 * @return float Calculated price
 */
function fn_pconf_get_configuration_price($conf_product_groups)
{
    $price = 0;
    $auth = & $_SESSION['auth'];
    foreach ($conf_product_groups as $k => $v) {
        if (!empty($v)) {
            $_products = db_get_hash_single_array("SELECT ?:product_prices.product_id, IF(?:product_prices.percentage_discount = 0, ?:product_prices.price, ?:product_prices.price - (?:product_prices.price * ?:product_prices.percentage_discount)/100) as price FROM ?:product_prices LEFT JOIN ?:conf_group_products ON ?:conf_group_products.product_id = ?:product_prices.product_id WHERE ?:conf_group_products.group_id = ?i AND ?:product_prices.lower_limit = 1 AND ?:product_prices.usergroup_id IN (?n)", array('product_id', 'price'), $k, (AREA == 'A' ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
            $tmp = is_array($v) ? $v : explode(':', $v);
            foreach ($tmp as $pid) {
                if (!empty($pid) && !empty($_products[$pid]) && AREA != 'A') {
                    $price += $_products[$pid];
                }
            }
        }
    }

    return $price;
}

function fn_check_pconf_access($db_field, $value, $show_notification = true)
{
    if (fn_allowed_for('ULTIMATE') && !empty($value)) {
        list($table, $field) = explode('.', $db_field);
        if (!empty($table) && !empty($field)) {
            $condition = fn_get_company_condition($table . '.company_id');
            if ($condition) {
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                foreach ($value as $v) {
                    $result = db_get_field("SELECT COUNT(1) FROM $table WHERE $db_field = ?s" . $condition, $v);
                    if (!$result) {
                        if ($show_notification) {
                            fn_set_notification('E', __('error'), __('access_denied'));
                        }

                        return false;
                    }
                }
            }
        }
    }

    return true;
}

function fn_product_configurator_update_cart_products_pre(&$cart, $product_data, $auth)
{
    if (is_array($cart['products']) && !empty($product_data)) {
        foreach ($product_data as $k => $v) {
            if (!empty($cart['products'][$k]) && !empty($cart['products'][$k]['configuration'])) {
                if ($v['stored_price'] == 'Y') {
                    $cart['products'][$k]['extra']['base_price'] -= $cart['products'][$k]['price'] - $v['price'];
                } else {
                    $conf_price = 0;
                    foreach ($cart['products'][$k]['configuration'] as $i => $c_data) {
                        $conf_price += $c_data['base_price'];
                    }
                    $cart['products'][$k]['extra']['base_price'] = $cart['products'][$k]['extra']['pc_original_price'] - $conf_price;
                }
            }
        }
    }
}

/**
 * Update product configurator products
 *
 * @param array $cart Array of cart content and user information necessary for purchase
 * @param array $product_data Array of new products data
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @return boolean Always true
 */
function fn_product_configurator_update_cart_products_post(&$cart, $product_data, $auth)
{
    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $_id => $product) {
            if (!empty($product['prev_cart_id']) && !empty($product_data[$product['prev_cart_id']])) {
                $product_data[$product['prev_cart_id']]['cart_id'] = $_id;
                $product_data[$_id] = $product_data[$product['prev_cart_id']];
                unset($product_data[$product['prev_cart_id']]);
            }
            if (!empty($product['extra']['configuration']) && !empty($product['prev_cart_id']) && $product['prev_cart_id'] != $_id) {
                foreach ($cart['products'] as $aux_id => $aux_product) {
                    if (!empty($aux_product['extra']['parent']['configuration']) && $aux_product['extra']['parent']['configuration'] == $product['prev_cart_id']) {
                        $cart['products'][$aux_id]['extra']['parent']['configuration'] = $_id;
                        $cart['products'][$aux_id]['update_c_id'] = true;
                    }
                }
            }
        }

        foreach ($cart['products'] as $upd_id => $upd_product) {
            if (!empty($upd_product['update_c_id'])) {
                $new_id = fn_generate_cart_id($upd_product['product_id'], $upd_product['extra'], false);

                if (!isset($cart['products'][$new_id])) {
                    unset($upd_product['update_c_id']);
                    $cart['products'][$new_id] = $upd_product;
                    unset($cart['products'][$upd_id]);
                    foreach ($cart['product_groups'] as $key_group => $group) {
                        if (in_array($upd_id, array_keys($group['products']))) {
                            unset($cart['product_groups'][$key_group]['products'][$upd_id]);
                            $cart['product_groups'][$key_group]['products'][$new_id] = $upd_product;
                        }
                    }

                    // update taxes
                    fn_update_stored_cart_taxes($cart, $upd_id, $new_id, false);
                }
            }
        }
    }

//     if (AREA == 'A') {
//         foreach ($product_data as $i => $p_data) {
//             if (!empty($p_data['configuration'])) {
//                 $org_cart = $cart;
//                 $_product_data = array();
//                 $count = 0;
//                 $has_stringing = false;
//                 foreach ($p_data['configuration'] as $j => $c_data) {
//                     if ($j == STRINGING_GROUP_ID && (is_numeric(reset($c_data['product_ids'])) || reset($c_data['product_ids']) == 'CONSULT_STRINGING')) {
//                         $has_stringing = true;
//                     }
//                     if (is_numeric(reset($c_data['product_ids']))) {
//                         $count++;
//                     }
//                     if (!empty($c_data['options'])) {
//                         foreach ($c_data['options'] as $p_id => $o_data) {
//                             if (!in_array($p_id, $c_data['product_ids'])) {
//                                 unset($p_data['configuration'][$j]['options'][$p_id]);
//                             }
//                         }
//                     }
//                 }
//                 if (!$has_stringing) {
//                     unset($p_data['configuration'][STRINGING_TENSION_GROUP_ID]);
//                     $count--;
//                 }
//                 $p_data['extra'] = $cart['products'][$i]['extra'];
//                 $_product_data[$p_data['product_id']] = $p_data;
//                 $_auth = !empty($_SESSION['customer_auth']) ? $_SESSION['customer_auth'] : fn_fill_auth(array(), array(), false, 'C');
//                 $ids = fn_add_product_to_cart($_product_data, $cart, $_auth);
//                 $new_key = array_search($p_data['product_id'], $ids);
//                 if ($count > count($cart['products'][$new_key]['configuration'])) {
//                     $cart = $org_cart;
//                 }
//                 unset($_REQUEST['redirect_url']);
//             }
//         }
//     }

    return true;
}
