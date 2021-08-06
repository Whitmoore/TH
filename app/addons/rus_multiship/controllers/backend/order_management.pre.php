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

if ($mode == "update_shipping" && isset($_REQUEST['shipping_id'])) {

    if (isset($_REQUEST['price_id']) && isset($_REQUEST['shipping_id'])) {

        if (isset($_REQUEST['price_id'])) {
            $cart['shippings_extra']['price_id'][0][$_REQUEST['shipping_id']] = $_REQUEST['price_id'];
        }

        if (isset($_REQUEST['pickuppoint_id'])) {
            $cart['shippings_extra']['pickuppoint_id'][0][$_REQUEST['shipping_id']] = $_REQUEST['pickuppoint_id'];
        }
    }
}
