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
use Tygh\Customization;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_enable_checkout_mode();

fn_define('ORDERS_TIMEOUT', 60);

// Cart is empty, create it
if (empty($_SESSION['cart'])) {
    fn_clear_cart($_SESSION['cart']);
}

$cart = & $_SESSION['cart'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $_suffix = '';
    $_action = '';

    //
    // Add product to cart
    //
    if ($mode == 'add') {
        $product_data = db_get_row("SELECT a.category_id, b.variant_id, c.value FROM ?:products_categories AS a LEFT JOIN ?:product_features_values AS b ON a.product_id = b.product_id AND b.feature_id = ?i LEFT JOIN ?:product_features_values AS c ON a.product_id = c.product_id AND c.feature_id = ?i WHERE a.product_id = ?i AND a.link_type = 'M'", TYPE_FEATURE_ID, R_STRINGS_FEATURE_ID, $dispatch_extra);
        if ($product_data['category_id'] == RACKETS_CATEGORY_ID && (!empty($product_data['variant_id']) && in_array($product_data['variant_id'], array(PRO_RACKET_FV_ID, CLUB_RACKET_FV_ID, POWER_RACKET_FV_ID, JUNIOR_RACKET_FV_ID))) && !in_array($dispatch_extra, unserialize(EXC_PRODUCT_ITEMS))) {
            $params = $_REQUEST;
            if (!empty($product_data['value']) && $product_data['value'] == 'N') {
                $params['sd_data'] = 'reload=1&option=0';
                Customization::displayCustomization($params);
                exit;
            } else {
                parse_str($params['sd_change'], $change);
                if (isset($change['option'])) {
                    $params['sd_data'] = 'reload=1&option=' . $change['option'];
                    Customization::displayCustomization($params);
                    exit;
                }
            }
        }
    }
}

if (!empty($_REQUEST['order_id']) && strpos($_REQUEST['order_id'], '-') !== false) {
    $_REQUEST['order_id'] = db_get_field("SELECT order_id FROM ?:orders WHERE order_number = ?s", $_REQUEST['order_id']);
}
if ($mode == 'checkout') {
    $total = Registry::get('settings.General.min_order_amount_type') == 'products_with_shippings' ? ($cart['total'] ?? 0) : ($cart['subtotal'] ?? 0);
    fn_set_hook('allow_place_order', $total, $cart);

    $cart['amount_failed'] = (Registry::get('settings.General.min_order_amount') > $total && floatval($total));
    if (!empty($cart['amount_failed'])) {
        fn_set_notification('W', __('attention'), __('text_min_order_amount_required', array('[order_limit]' => fn_format_price_by_currency(Registry::get('settings.General.min_order_amount')))));
//         return array(CONTROLLER_STATUS_REDIRECT, !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "checkout.cart");
    }
}
if ($mode == 'auto_save_user') {

    $user_data = !empty($_REQUEST['user_data']) ? $_REQUEST['user_data'] : array();
    unset($user_data['user_type']);

    fn_save_checkout_user_data($user_data, $auth, $cart, $_REQUEST['ship_to_another']);
    fn_save_checkout_step($cart, $auth['user_id'], $_SESSION['edit_step']);
    exit;
}
