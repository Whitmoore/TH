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

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_rus_payments_install()
{
    $payments = fn_get_schema('rus_payments', 'processors', 'php', true);

    if (!empty($payments)) {
        foreach ($payments as $payment) {

            $processor_id = db_get_field("SELECT processor_id FROM ?:payment_processors WHERE admin_template = ?s", $payment['admin_template']);

            if (empty($processor_id)) {
                db_query('INSERT INTO ?:payment_processors ?e', $payment);
            } else {
                db_query("UPDATE ?:payment_processors SET ?u WHERE processor_id = ?i", $payment ,$processor_id);
            }
        }
    }
}

function fn_rus_payments_uninstall()
{
    $payments = fn_get_schema('rus_payments', 'processors');
    fn_rus_payments_disable_payments($payments, true);

    foreach ($payments as $payment) {
        db_query("DELETE FROM ?:payment_processors WHERE processor_script = ?s", $payment['admin_template']);
    }
}

function fn_rus_payments_disable_payments($payments, $drop_processor_id = false)
{
    $fields = '';
    if ($drop_processor_id) {
        $fields = 'processor_id = 0,';
    }

    foreach ($payments as $payment) {
        $processor_id = db_get_field("SELECT processor_id FROM ?:payment_processors WHERE processor_script = ?s", $payment['admin_template']);

        if (!empty($processor_id)) {
            db_query("UPDATE ?:payments SET $fields status = 'D' WHERE processor_id = ?i", $processor_id);
        }
    }
}

function fn_rus_pay_format_price($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
        if ($currencies[$payment_currency]['is_primary'] != 'Y') {
            $price = fn_format_price($price / $currencies[$payment_currency]['coefficient']);
        }
    } else {
        return false;
    }

    return $price;
}

function fn_rus_pay_format_price_down($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
          $price = fn_format_price($price * $currencies[$payment_currency]['coefficient']);
    } else {
        return false;
    }

    return $price;
}

function fn_rus_payments_normalize_phone($phone)
{
    $phone_normalize = '';

    if (!empty($phone)) {
        if (strpos('+', $phone) === false && $phone[0] == '8') {
            $phone[0] = '7';
        }

        $phone_normalize = str_replace(array(' ', '(', ')', '-'), '', $phone);
    }

    return $phone_normalize;
}

function fn_qr_generate($order_info, $delimenter = '|')
{
    $processor_params = $order_info['payment_method']['processor_params'];

    $format_block = 'ST' . '0001' . '2' . $delimenter;

    $required_block = array(
        'Name' => $processor_params['sbrf_recepient_name'],
        'PersonalAcc' => $processor_params['sbrf_settlement_account'],
        'BankName' => $processor_params['sbrf_bank'],
        'BIC' => $processor_params['sbrf_bik'],
        'CorrespAcc' => $processor_params['sbrf_cor_account'],
    );

    $required_block = fn_qr_array2string($required_block, $delimenter);

    $additional_block = array(
        'PayeeINN' => $processor_params['sbrf_inn'],
        'Sum' => $order_info['total'] * 100,
        'Purpose' => __('sbrf_order_payment') . ' №' . $order_info['order_id'],
        'LastName' => $order_info['b_lastname'],
        'FirstName' => $order_info['b_firstname'],
        'PayerAddress' => $order_info['b_city'],
        'Phone' => $order_info['b_phone'],
    );

    $additional_block = fn_qr_array2string($additional_block, $delimenter);

    $string = $format_block . $required_block . $additional_block;

    $string = substr($string, 0, -1);

    $resolution = $processor_params['sbrf_qr_resolution'];

    $data = array(
        'cht' => 'qr',
        'choe' => 'UTF-8',
        'chl' => $string,
        'chs' => $resolution . 'x' . $resolution,
        'chld' => 'M|4'
    );

    $url = 'https://chart.googleapis.com/chart';

    $response = Http::get($url, $data);

    Header("Content-Type: image/png");

    if (!strpos($response, 'Error')) {
        fn_echo($response);
    } else {
        $image = fn_get_contents(DIR_ROOT. '/images/no_image.png');

        fn_echo($image);
    }

    exit;
}

function fn_qr_array2string($array, $del = '|', $eq = '=')
{
    if (is_array($array)) {

        $string = '';

        foreach ($array as $key => $value) {
            if (!empty($value)) {
                $string .= $key . $eq . $value . $del ;
            }
        }
    }

    return $string;
}
