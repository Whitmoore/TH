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

// TennisHouse

use Tygh\Registry;
use Tygh\Http;

if (defined('PAYMENT_NOTIFICATION')) {

    if (isset($_REQUEST['order_id'])) {
        $ordernumber = explode('_', $_REQUEST['order_id']);
        $order_id = reset($ordernumber);

    } elseif (isset($_REQUEST['order'])) {
        $orderNumber = explode('_', $_REQUEST['order']);
        $order_id = reset($orderNumber);
    } else {
        $order_id = 0;
    }

    if ($mode == 'notify') {

        $pp_response['order_status'] = 'P';
        if (fn_check_payment_script('qiwi.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
        
    } elseif ($mode == 'error') {

        $pp_response['order_status'] = 'F';
        $pp_response["reason_text"] = __('text_transaction_cancelled');

        if (fn_check_payment_script('qiwi.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
    }
    exit;
}

if (!defined('BOOTSTRAP')) {
    if ((!empty($_REQUEST['parameter']) && $_REQUEST['parameter'] == 'update') && !empty($_REQUEST['status'])) {
        require './init_payment.php';

        if (!empty($_REQUEST['command']) && $_REQUEST['command'] == 'bill') {
            if (isset($_REQUEST['bill_id'])) {
                $ordernumber = explode('_', $_REQUEST['bill_id']);
                $order_id = reset($ordernumber);
            } else {
                $order_id = 0;
            }

            if ($_REQUEST['status'] == 'paid') {

                $pp_response['order_status'] = 'P';
                if (fn_check_payment_script('qiwi.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response, false);
                }

            } elseif (in_array($_REQUEST['status'], array('rejected', 'unpaid', 'expired'))) {

                $pp_response['order_status'] = 'F';
                $pp_response["reason_text"] = __('text_transaction_cancelled');

                if (fn_check_payment_script('qiwi.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response, false);
                }
            }
            db_query("INSERT INTO ?:tmp ?e", array('data' => serialize($_REQUEST)));
            header('Content-Type: text/xml; charset=utf-8');
            fn_echo('<?xml version="1.0"?><result><result_code>0</result_code></result>');
        }
        exit;
    } else {
        die('Access denied');
    }
} else {
    $dame_format = 'Y-m-d\TH:i:s';
    
    $_order_id = $order_info['repaid'] ? ($order_info['order_id'] . '_' . $order_info['repaid']) : $order_info['order_id'];
    $_order_total = fn_format_rate_value($order_info['total'], 'F', 2, '.', '', '');
    $_lifetime = date($dame_format, time() + ($processor_data['processor_params']['lifetime'] * 60));
    $url = "https://qiwi.com/api/v2/prv/" . $processor_data['processor_params']['shop_id'] . "/bills/" . $_order_id;
    
    $data = array(
        "user" => "tel:+" . fn_qiwi_convert_phone($order_info['payment_info']['phone']),
        "amount" => $_order_total,
        "ccy" => $order_info['secondary_currency'],
        "comment" => (!empty($order_info['notice']) ? $order_info['notice'] : ''),
        "lifetime" => $_lifetime,
        "pay_source" => "qw",
        "prv_name" => "TennisHouse"
    );
    $extra = array(
        'basic_auth' => array($processor_data['processor_params']['login'] . ':' . $processor_data['processor_params']['passwd']),
        'headers' => array('Accept: application/json')
    );
    $result = json_decode(Http::put($url, $data, $extra));
    $pp_response['reason_text'] = __('qiwi_result_status_' . $result->response->result_code);

    if ($result->response->result_code == 0) {
        $pp_response['order_status'] = 'O';
    } else {
        $pp_response['order_status'] = 'F';
    }

    fn_finish_payment($order_id, $pp_response);
    fn_clear_cart($_SESSION['cart']);
    $idata = array (
        'order_id' => $_order_id,
        'type' => 'S',
        'data' => TIME
    );
    db_query("REPLACE INTO ?:order_data ?e", $idata);
    
    if ($result->response->result_code == 0) {
        $redirect_url = "https://qiwi.com/order/external/main.action?shop=" . $processor_data['processor_params']['shop_id'] . '&transaction=' . $_order_id . '&successUrl=' . urlencode(fn_url("payment_notification.notify?payment=qiwi&order_id=$_order_id", AREA)) . '&failUrl=' . urlencode(fn_url("payment_notification.error?payment=qiwi&order_id=$_order_id", AREA));
        fn_redirect($redirect_url, true);
    } else {
        fn_order_placement_routines('route', $order_id, false);
    }
    
}

function fn_qiwi_convert_phone($phone)
{
    $phone = str_replace(array('+', ' ', '(', ')', '-'), '', $phone);

    if (strlen($phone) > 11) {
        $phone = substr($phone, -11);
    }

    return $phone;
}
