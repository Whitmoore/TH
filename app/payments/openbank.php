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

if (defined('PAYMENT_NOTIFICATION')) {

    list($order_id) = explode('_', $_REQUEST['order_id']);
    $order_info = fn_get_order_info($order_id);
    $route = $order_info['repaid'] ? 'repay' : 'route';

    if ($mode == 'return') {

        $pp_response = array(
            'order_status' => 'P',
            'transaction_id' => $_REQUEST['orderId']
        );
        if (fn_check_payment_script('openbank.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response);
        }
        fn_order_placement_routines($route, $order_id);

    } elseif ($mode == 'error') {
    
        $processor_data = fn_get_processor_data($order_info['payment_id']);
        $post_address = "https://secure.openbank.ru/payment/rest/getOrderStatusExtended.do";
        $post_data = array(
            'userName' => $processor_data['processor_params']['login'],
            'password' => $processor_data['processor_params']['password'],
            'orderId' => $_REQUEST['orderId'],
            'language' => CART_LANGUAGE,
        );
        $result = json_decode(Http::post($post_address, $post_data));

        $pp_response = array(
            'order_status' => 'N',
            'reason_text' => $result->actionCodeDescription,
            'transaction_id' => $_REQUEST['orderId']
        );

        if (fn_check_payment_script('openbank.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines($route, $order_id);
    }

} else {

    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    $post_address = "https://secure.openbank.ru/payment/rest/register.do";

    $payment_desc = '';
    if (is_array($order_info['products'])) {
        foreach ($order_info['products'] as $k => $v) {
            $payment_desc .= ($payment_desc != '') ? ' / ' : $order_info['products'][$k]['product'];
        }
    }
    $_order_id = $order_id . '_' . TIME;

    $post_data = array(
        'userName' => $processor_data['processor_params']['login'],
        'password' => $processor_data['processor_params']['password'],
        'orderNumber' => $order_info['order_number'] . '_' . TIME,
        'amount' => $order_info['total'] * 100,
        'currency' => '810',
        'returnUrl' => fn_url("payment_notification.return?payment=openbank&order_id=$_order_id", AREA),
        'failUrl' => fn_url("payment_notification.error?payment=openbank&order_id=$_order_id", AREA),
        'description' => $payment_desc,
        'language' => CART_LANGUAGE,
        
//        'pageView' => '',
//        'clientId' => '',
//        'jsonParams' => '',
//        'sessionTimeoutSecs' => '',
//        'expirationDate' => '',
    );
    $result = json_decode(Http::post($post_address, $post_data));
    if (!empty($result->errorCode)) {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = $result->errorMessage;
        fn_finish_payment($_order_id, $pp_response);
        fn_order_placement_routines('route', $_order_id);
    } elseif (!empty($result->formUrl)) {
        fn_redirect($result->formUrl, true);
    }
}

exit;