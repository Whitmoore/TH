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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
}

$params = $_REQUEST;

if ($mode == 'manage') {

    $od_orders = db_get_field("SELECT COUNT(*) FROM ?:orders WHERE " . fn_get_overdue_delivery_condition('?:orders'));

    if (!empty($od_orders)) {
        fn_set_notification('W', __('important'), __('text_overdue_delivery_orders', array(
            '[number]' => $od_orders,
            '[link]' => fn_url('orders.manage?overdue_delivery=Y')
        )));
    }
}
