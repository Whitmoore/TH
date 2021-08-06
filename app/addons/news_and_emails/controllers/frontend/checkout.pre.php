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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if ($mode == 'place_order' || $mode == 'subscribe_customer') {
        if (!empty($_REQUEST['subscribe_to_store_newsletters']) && $_REQUEST['subscribe_to_store_newsletters'] == 'Y') {
            fn_add_subscriber($_SESSION['cart']['user_data']['email']);
        }
    }

    if ($mode == 'subscribe_customer') {
        return array(CONTROLLER_STATUS_REDIRECT, 'checkout.checkout');
    }
}

if (($mode == 'checkout' || $mode == 'customer_info') && !empty($_SESSION['cart']['user_data']['email'])) {

    $subscriber = db_get_row("SELECT * FROM ?:subscribers WHERE email = ?s", $_SESSION['cart']['user_data']['email']);
    $params = array(
        'checkout' => true
    );
    if (!empty($subscriber)) {
        $params['not_subscribed'] = $subscriber['subscriber_id'];
    }
    list($page_mailing_lists) = fn_get_mailing_lists($params);
    Registry::get('view')->assign('page_mailing_lists', $page_mailing_lists);
}
