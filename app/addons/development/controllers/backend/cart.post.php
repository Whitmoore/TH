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
use Tygh\Session;
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_SESSION['cart'] = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$cart = & $_SESSION['cart'];

$_SESSION['customer_auth'] = isset($_SESSION['customer_auth']) ? $_SESSION['customer_auth'] : array();
$customer_auth = & $_SESSION['customer_auth'];


if ($mode == 'create_order' && !empty($_REQUEST['user_id'])) {

    $item_types = fn_get_cart_content_item_types();
    fn_clear_cart($cart, true);
    $cart_info['products'] = db_get_array(
        "SELECT ?:user_session_products.item_id, ?:user_session_products.item_type, ?:user_session_products.product_id, ?:user_session_products.amount, ?:user_session_products.price, ?:user_session_products.extra, ?:user_session_products.user_data"
        . " FROM ?:user_session_products"
        . " WHERE ?:user_session_products.user_id = ?i AND ?:user_session_products.type = 'C' AND ?:user_session_products.item_type IN (?a)",
        $_REQUEST['user_id'], $item_types
    );
    $user_data = array();
    if (!empty($cart_info['products'])) {
        foreach ($cart_info['products'] as $key => $product) {
            $cart_info['products'][$key] = unserialize($product['extra']);
            if (!empty($product['user_data']) && empty($user_data)) {
                $ud = unserialize($product['user_data']);
                if (!empty($ud)) {
                    $user_data = $ud;
                }
            }
        }
    }
    $cart_info = array_merge($cart_info, $user_data);
    
    $is_exist = fn_get_user_info($cart_data['user_id']);
    if (!empty($is_exist)) {
        $cart_info['user_id'] = $_REQUEST['user_id'];
    }
    $customer_auth = fn_fill_auth(array(), array(), false, 'C');
    
    // Fill the cart
    foreach ($cart_info['products'] as $_id => $item) {
        $_item = array (
            $item['product_id'] => array (
                'amount' => $item['amount'],
                'product_id' => $item['product_id'],
                'product_options' => (!empty($item['extra']['product_options']) ? $item['extra']['product_options'] : array()),
            ),
        );
        if (isset($item['extra'])) {
            $_item[$item['product_id']] = array_merge($_item[$item['product_id']], $item['extra']);
        }
        fn_add_product_to_cart($_item, $cart, $customer_auth);
    }


    // Fill customer info
    $cart['user_data'] = fn_check_table_fields($cart_info, 'user_profiles');
    $cart['user_data'] = fn_array_merge(fn_check_table_fields($cart_info, 'users'), $cart['user_data']);
    if (!empty($cart_info['fields'])) {
        $cart['user_data']['fields'] = $cart_info['fields'];
    }
    
    return array(CONTROLLER_STATUS_REDIRECT, "order_management.add");
}

