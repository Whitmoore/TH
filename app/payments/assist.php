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

// rus_build_pack dbazhenov

use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = (int) $_REQUEST['order_id'];

    if (!fn_check_payment_script('assist.php', $order_id, $processor_data)) {
        exit;
    }

    $order_info = fn_get_order_info($order_id);
    $pp_response = array();

    if ($mode == 'return_ok') {
        $request_url = 'https://payments.paysecure.ru/orderstate/orderstate.cfm';
        $post = array();
        $post['Ordernumber'] = $_REQUEST['ordernumber'];
        $post['Merchant_ID'] = $processor_data['processor_params']['merchant_id'];
        $post['Login'] = $processor_data['processor_params']['login'];
        $post['Password'] = $processor_data['processor_params']['password'];
        $post['Format'] = 3;

        $return = Http::post($request_url, $post, array(
            'headers' => array(
                'Accept: text/xml',
                'User-Agent: Mozilla/4.5 [en]',
            )
        ));

        $xml = @simplexml_load_string($return);
        $approved = false;
        $order_amount = 0;
        if (is_array($xml->order)) {
            foreach ($xml->order as $order_info) {
                if ($order_info->orderstate == 'Approved') {
                    $approved = true;
                    $order_amount = $order_info->orderamount;
                }
            }
        } else {
            if ($xml->order->orderstate == 'Approved') {
                $approved = true;
                $order_amount = $xml->order->orderamount;
            }
        }

        if ($approved && $order_amount  == $order_info['total']) {
            $pp_response['order_status'] = 'P';
            $pp_response['reason_text'] = __('transaction_approved');
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('transaction_declined');
        }
    } elseif ($mode == 'return_no') {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = __('transaction_declined');
    }

    fn_finish_payment($order_id, $pp_response);
    fn_order_placement_routines('route', $order_id);

    exit;
} else {
    $url = 'https://payments.paysecure.ru/pay/order.cfm';

    $post = array();

    $post['TestMode'] = $processor_data['processor_params']['mode'] == 'L' ? 0 : 1;
    $post['Merchant_ID'] = $processor_data['processor_params']['merchant_id'];
    $post['OrderNumber'] = $processor_data['processor_params']['order_prefix'] . $order_id . ($order_info['repaid'] ? "_{$order_info['repaid']}" : '');
    $post['OrderAmount'] = $order_info['total'];
    //We can leave this field blank. In this case currency will be taken from payment admin panel.
    $post['OrderCurrency'] = '';
    $post['Language'] = $processor_data['processor_params']['language'];
    $post['Delay'] = 0;
    $post['URL_RETURN'] = fn_url("payment_notification.return?payment=assist&order_id=$order_id", AREA, 'current');
    $post['URL_RETURN_OK'] = fn_url("payment_notification.return_ok?payment=assist&order_id=$order_id", AREA, 'current');
    $post['URL_RETURN_NO'] = fn_url("payment_notification.return_no?payment=assist&order_id=$order_id", AREA, 'current');
    $post['Firstname'] = $order_info['b_firstname'];
    $post['Lastname'] = $order_info['b_lastname'];
    $post['Middlename'] = '';
    $post['Email'] = $order_info['email'];
    $post['HomePhone'] = $order_info['phone'];
    $post['Address'] = $order_info['b_address'];
    $post['Country'] = db_get_field("SELECT code_A3 FROM ?:countries WHERE code = ?s", $order_info['b_country']);
    $post['State'] = $order_info['b_state'];
    $post['City'] = $order_info['b_city'];
    $post['Zip'] = $order_info['b_zipcode'];
    $post['OrderComment'] = $order_info['notes'];
    $post['CardPayment'] = 1;
    $post['YMPayment'] = 0;
    $post['WMPayment'] = 0;
    $post['QIWIPayment'] = 0;
    $post['QIWIMtsPayment'] = 0;
    $post['QIWIMegafonPayment'] = 0;
    $post['QIWIBeelinePayment'] = 0;
    $post['AssistIDPayment'] = 0;
    $post['AssistIDPayment'] = 0;

    fn_create_payment_form($url, $post, 'Assist server', false);
}
