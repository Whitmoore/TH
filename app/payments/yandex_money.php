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

if (defined('PAYMENT_NOTIFICATION')) {

    // [tennishouse]
    if (isset($_REQUEST['ordernumber'])) {
        $ordernumber = explode('_', $_REQUEST['ordernumber']);
        $order_id = reset($ordernumber);

    } elseif (isset($_REQUEST['orderNumber'])) {
        $orderNumber = explode('_', $_REQUEST['orderNumber']);
        $order_id = reset($orderNumber);

    } elseif (isset($_REQUEST['merchant_order_id'])) {
        $merchant_order_id = explode('_', $_REQUEST['orderNumber']);
        $order_id = reset($merchant_order_id);

    } else {
        $order_id = 0;
    }
    // [tennishouse]

    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
    $processor_data = fn_get_processor_data($payment_id);

    if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
        fn_yandex_money_log_write($mode, 'ym_request.log');
        fn_yandex_money_log_write($_REQUEST, 'ym_request.log');
    }

    if ($mode == 'ok') {

        $order_info = fn_get_order_info($order_id);

        if (fn_check_payment_script('yandex_money.php', $order_id)) {
            fn_order_placement_routines('route', $order_id);
        }

    } elseif ($mode == 'error') {

        $pp_response['order_status'] = 'N';
        $pp_response["reason_text"] = __('text_transaction_cancelled');

        if (fn_check_payment_script('yandex_money.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);

    } elseif ($mode == 'check_order') {

        $order_info = fn_get_order_info($order_id);

        $date_time = date('c');

        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
        $processor_data = fn_get_payment_method_data($payment_id);

        $shop_id = $processor_data['processor_params']['shop_id'];
        $invoiceId = $_REQUEST['invoiceId'];

        $code = 0;

        if ($_REQUEST['orderSumAmount'] != $order_info['total']) {
             $code = 2;
             $total = $order_info['total'];
        }

        $hash = $_REQUEST['action'].';'.$_REQUEST['orderSumAmount'].';'.$_REQUEST['orderSumCurrencyPaycash'].';'.$_REQUEST['orderSumBankPaycash'].';'.$_REQUEST['shopId'].';'.$_REQUEST['invoiceId'].';'.$_REQUEST['customerNumber'].';'.$processor_data['processor_params']['md5_shoppassword'];

        $hash = md5($hash);
        $hash = strtoupper($hash);

        if ($_REQUEST['md5'] != $hash) {
             $code = 1;
        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $item = $dom->createElement('checkOrderResponse');
        $item->setAttribute('performedDatetime', $date_time);
        $item->setAttribute('code', $code);
        $item->setAttribute('shopId', $shop_id);
        $item->setAttribute('invoiceId', $invoiceId);

        if ($code == 2) {
            $item->setAttribute('orderSumAmount', $total);
            $dom->appendChild($item);
            echo($dom->saveXML());
        } else {
            $dom->appendChild($item);
            echo($dom->saveXML());
        }

        if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
            fn_yandex_money_log_write($dom->saveXML(), 'ym_check_order.log');
        }

        exit;

    } elseif ($mode == 'payment_aviso') {

        $order_info = fn_get_order_info($order_id);

        $date_time = date('c');

        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
        $processor_data = fn_get_payment_method_data($payment_id);

        $shop_id = $processor_data['processor_params']['shop_id'];
        $invoiceId = $_REQUEST['invoiceId'];

        $code = 0;

        $hash = $_REQUEST['action'].';'.$_REQUEST['orderSumAmount'].';'.$_REQUEST['orderSumCurrencyPaycash'].';'.$_REQUEST['orderSumBankPaycash'].';'.$_REQUEST['shopId'].';'.$_REQUEST['invoiceId'].';'.$_REQUEST['customerNumber'].';'.$processor_data['processor_params']['md5_shoppassword'];
        $hash = md5($hash);
        $hash = strtoupper($hash);

        if ($_REQUEST['md5'] == $hash) {
             $pp_response = array(
                'order_status' => 'P'
            );
            if (fn_check_payment_script('yandex_money.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
            }

        } else {
            $code = 1;
            $pp_response['order_status'] = 'N';
            $pp_response["reason_text"] = __('error');

            if (fn_check_payment_script('yandex_money.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response, false);
            }

        }

        $dom = new DOMDocument('1.0', 'utf-8');
        $item = $dom->createElement('paymentAvisoResponse');
        $item->setAttribute('performedDatetime', $date_time);
        $item->setAttribute('code', $code);
        $item->setAttribute('shopId', $shop_id);
        $item->setAttribute('invoiceId', $invoiceId);

        $dom->appendChild($item);
        echo($dom->saveXML());

        if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
            fn_yandex_money_log_write($dom->saveXML(), 'ym_payment_aviso.log');
        }

        exit;
    }

} else {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    if ($processor_data['processor_params']['mode'] == 'test') {
        $post_address = "https://demomoney.yandex.ru/eshop.xml";
    } else {
        $post_address = "https://money.yandex.ru/eshop.xml";
    }

    $payment_info['yandex_payment_type'] = mb_strtoupper($payment_info['yandex_payment_type']);

    if (empty($payment_info['yandex_payment_type'])) {
        $payment_type = 'PC';
    } else {
        $payment_type = $payment_info['yandex_payment_type'];
    }

    $phone = '';
    if (!empty($order_info['phone'])) {
        $phone = $order_info['phone'];

    } elseif (!empty($order_info['b_phone'])) {
        $phone = $order_info['b_phone'];

    } elseif (!empty($order_info['s_phone'])) {
        $phone = $order_info['s_phone'];
    }

    $customer_phone = str_replace('+', '', $phone);

    $orderNumber = $order_info['order_id'] . '_' . substr(md5($order_info['order_id'] . TIME), 0, 3);

    $post_data = array(
        'shopId' => $processor_data['processor_params']['shop_id'],
        'Sum' => fn_yandex_money_get_sum($order_info, $processor_data),
        'scid' => $processor_data['processor_params']['scid'],
        'customerNumber' => $order_info['email'],
        'orderNumber' => $orderNumber,
        'shopSuccessURL' => fn_url("payment_notification.ok?payment=yandex_money&ordernumber=$orderNumber", AREA, 'https'),
        'shopFailURL' => fn_url("payment_notification.error?payment=yandex_money&ordernumber=$orderNumber", AREA, 'https'),
        'cps_email' => $order_info['email'],
        'cps_phone' => $customer_phone,
        'paymentAvisoURL' => fn_url("payment_notification.payment_aviso?payment=yandex_money", AREA, 'https'),
        'checkURL' => fn_url("payment_notification.check_order?payment=yandex_money", AREA, 'https'),
        'paymentType' => $payment_type,
        'cms_name' => 'cscart'
    );

    if ($payment_type == 'NV') {
        $post_data['cps_provider'] = 'wm';
    }

    if (!empty($processor_data['processor_params']['logging']) && $processor_data['processor_params']['logging'] == 'Y') {
        fn_yandex_money_log_write($post_data, 'ym_post_data.log');
    }

    fn_create_payment_form($post_address, $post_data, 'Yandex.Money', false);
}

function fn_yandex_money_get_sum($order_info, $processor_data)
{
    $price = $order_info['total'];

    if (CART_PRIMARY_CURRENCY != $processor_data['processor_params']['currency']) {
        $currencies = Registry::get('currencies');
        $currency = $currencies[$processor_data['processor_params']['currency']];
        $price = fn_format_rate_value($price, 'F', $currency['decimals'], $currency['decimals_separator'], $currency['thousands_separator'], $currency['coefficient']);
    }

    return sprintf('%.2f', $price);
}

function fn_yandex_money_log_write($data, $file)
{
    $path = fn_get_files_dir_path();
    fn_mkdir($path);
    $file = fopen($path . $file, 'a');

    if (!empty($file)) {
        fputs($file, 'TIME: ' . date('Y-m-d H:i:s', TIME) . "\n");
        fputs($file, fn_array2code_string($data) . "\n\n");
        fclose($file);
    }
}

exit;
