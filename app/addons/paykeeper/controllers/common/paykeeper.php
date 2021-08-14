<?php

/* * *************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 * ***************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 * ************************************************************************** */

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'return') {

        $processor_data = fn_get_processor_data($_GET['pid']);

        if ($_POST['key'] != md5($_POST['id'] . sprintf("%.2lf", $_POST['sum']) . $_POST['clientid'] . $_POST['orderid'] . $processor_data['processor_params']['secret'])) {
            echo "Error! Hash mismatch";
            exit;
        }

        if ($_POST['orderid']) {
            fn_change_order_status($_POST['orderid'], $processor_data['processor_params']['order_status']);
        }

        echo "OK " . md5($_POST['id'] . $processor_data['processor_params']['secret']);
        exit;
    }
}
else {
    $cart = & $_SESSION['cart'];
    fn_clear_cart($cart);
    fn_redirect(Registry::get('config.http_location') . "/index.php");
    exit;
}
