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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$cart = & $_SESSION['cart'];

if ($mode == 'checkout' || $mode == 'shipping_estimation') {

    if (isset($_REQUEST['price_ids'])) {
        foreach ($_REQUEST['shipping_ids'] as $group_key => $shipping_id) {

            if (isset($_REQUEST['price_ids'][$group_key])) {
                $cart['shippings_extra']['price_id'][$group_key][$shipping_id] = $_REQUEST['price_ids'][$group_key];
            }

            if (isset($_REQUEST['pickuppoint_ids'][$group_key])) {
                $cart['shippings_extra']['pickuppoint_id'][$group_key][$shipping_id] = $_REQUEST['pickuppoint_ids'][$group_key];
            }
        }
    }

}
