<?php

if (defined('PAYMENT_NOTIFICATION')) {

    if ($mode == 'return') {

        $order_info = fn_get_order_info($order_id);
        $route = $order_info['repaid'] ? 'repay' : 'route';
        
        if ($_REQUEST['result'] == 'success') {

            $pp_response = array(
                'order_status' => 'P',
                'transaction_id' => $_REQUEST['payment_id']
            );
            if (fn_check_payment_script('paykeeper.php', $_REQUEST['order_id'])) {
                fn_finish_payment($_REQUEST['order_id'], $pp_response);
            }
            fn_order_placement_routines($route, $_REQUEST['order_id']);

        } else {
        
            $pp_response = array(
                'order_status' => 'N',
                'transaction_id' => $_REQUEST['payment_id']
            );

            if (fn_check_payment_script('paykeeper.php', $_REQUEST['order_id'])) {
                fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
            }

            fn_order_placement_routines($route, $_REQUEST['order_id']);
        }
    }
    
    if ($mode == 'notify') {
        $order_id = db_get_field("SELECT order_id FROM ?:orders WHERE order_number = ?s", $_REQUEST['orderid']);
        $order_info = fn_get_order_info($order_id);
        $processor_data = fn_get_processor_data($order_info['payment_id']);

        if ($_REQUEST['key'] != md5($_REQUEST['id'] . sprintf("%.2lf", $_REQUEST['sum']) . $_REQUEST['clientid'] . $_REQUEST['orderid'] . $processor_data['processor_params']['secret'])) {
            fn_echo("Error! Hash mismatch");
            exit;
        } else {
            fn_echo("OK " . md5($_REQUEST['id'] . $processor_data['processor_params']['secret']));
        }
    }
    
} else {

    if (!defined('BOOTSTRAP')) { die('Access denied'); }
    
    $cart = array();
    foreach ($order_info['products'] as $k => $v) {
        $price = fn_format_price($v['price'] - $order_info['subtotal_discount'] * $v['price'] / $order_info['subtotal']);
        $cart[] = array(
            'name' => $v['product'],
            'price' => $price,
            'quantity' => $v['amount'],
            'sum' => $v['amount'] * $price,
            'tax' => 'none',
        );
    }
    
    $data = array (
        'sum' => $order_info['total'],
        'clientid' => $order_info['firstname'] . ' ' . $order_info['lastname'],
        'orderid' => $order_info['order_number']/* . '_' . TIME*/,
        'service_name' => $payment_desc,
        'client_email' => $order_info['email'],
        'client_phone' => $order_info['phone'],
    );
        
    $data['sign'] = hash('sha256' , implode('', $data) . $processor_data['processor_params']['secret']);
    $data['user_result_callback'] = fn_url("payment_notification.return?payment=paykeeper&order_id=$order_id", AREA);
    $data['cart'] = json_encode($cart);
    
    fn_create_payment_form($processor_data['processor_params']['url'] . '/create/', $data, 'PayKeeper', false);
    
}

exit;