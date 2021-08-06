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

if ($mode == 'print_sbrf_receipt') {

    $order_info = fn_get_order_info($_REQUEST['order_id']);
    $currencies = Registry::get('currencies');

    if (isset($currencies['RUB'])) {
        $currency = $currencies['RUB'];

        $for_rub = fn_format_rate_value($order_info['total'], 'F', $currency['decimals'], $currency['decimals_separator'], $currency['thousands_separator'], $currency['coefficient']);

        if ($currency['decimals'] != 0) {
            $length_for_rub = $currency['decimals']+1;
            $rub = substr($for_rub , 0 , -$length_for_rub );

            $length_for_kop = $currency['decimals'];

            $kop = substr($for_rub , -$length_for_kop );

            $total_print = '<strong>' . $rub . '</strong>&nbsp;' . __("sbrf_rub") . '&nbsp;<strong>' . $kop . '</strong>&nbsp;' . __("sbrf_kop");
        } else {
            $total_print = '<strong>' . $for_rub . '</strong>&nbsp;' . __("sbrf_rub");
        }
    } else {
        $total_print = fn_format_price_by_currency($order_info['total']);
    }

    $view = Registry::get('view');

    $view->assign('total_print', $total_print);
    $view->assign('order_info', $order_info);
    $view->assign('fonts_path', fn_get_theme_path('[relative]/[theme]/media/fonts'));

    $view->displayMail('addons/rus_payments/print_sbrf_receipt.tpl', true, 'C');

    exit;

} elseif ($mode == 'details') {

    $order_info = fn_get_order_info($_REQUEST['order_id']);

    if (isset($order_info['payment_method']['processor_params']['sbrf_enabled']) && $order_info['payment_method']['processor_params']['sbrf_enabled'] == "Y" ) {
        $navigation_tabs = Registry::get('navigation.tabs');
        $navigation_tabs['payment_information'] = array(
            'title' => __('payment_information'),
            'js' => true,
            'href' => 'orders.details?order_id=' . $_REQUEST['order_id'] . '&selected_section=payment_information'
        );
        Registry::set('navigation.tabs', $navigation_tabs);
    }

    if (!empty($_REQUEST['payment_id'])) {
        $payment_info = fn_get_payment_method_data($_REQUEST['payment_id']);
        Registry::get('view')->assign('payment_info', $payment_info);
    }

} elseif ($mode == 'get_qr') {

    if (!empty($_REQUEST['order_id'])) {

        $order_info = fn_get_order_info($_REQUEST['order_id']);

        if (!empty($order_info) && $order_info['payment_method']['processor_params']['sbrf_enabled'] == 'Y') {
            fn_qr_generate($order_info);
        } 
    }

    Header("Content-Type: image/png");

    $image = fn_get_contents(DIR_ROOT. '/images/no_image.png');

    fn_echo($image);

    exit;
       
}


