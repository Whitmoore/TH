<?php

/**
 * @var array $processor_data
 * @var array $order_info
 * @var string $mode
 */
if (!defined('BOOTSTRAP')) { die('Access denied'); }

require_once('paykeeper.class.php');

$pk_obj = new PaykeeperPayment();

$pk_obj->setOrderParams($order_info["total"],                         //sum
                        $order_info["firstname"]
                        . $order_info["lastname"],                    //clientid
                        $order_info["order_id"],                      //orderid
                        $order_info["email"],                         //client_email
                        $order_info["phone"],                         //client_phone
                        "",                                           //service_name
                        $processor_data['processor_params']['url'],   //payment form url
                        $processor_data['processor_params']['secret'] //secret key
);

//GENERATE FZ54 CART
$product_cart_sum = 0;

foreach ($order_info['products'] as $item) {
    $tax_rate = NULL;
    $sum = (float) number_format($item['price'] * $item['amount'], 2, ".", "");
    $product_cart_sum += $sum;

    $tax_rate = getTaxRate($order_info, $item["item_id"], "cart");

    if ($tax_rate !== NULL) {
        $taxes = $pk_obj->setTaxes($sum, (int) $tax_rate);
    } else {
        $taxes = array('tax' => 'none', 'tax_sum' => 0);
    }
    $pk_obj->updateFiscalCart($pk_obj->getPaymentFormType(), $item["extra"]["product"], $item['price'], $item['amount'], $sum, $taxes["tax"], $taxes["tax_sum"]);
}

//add shipping parameters to cart
$shipping_tax_rate = NULL;
$shipping_taxes = array("tax" => "none", "tax_sum" => 0);

$shipping_name = $order_info["shipping"][0]["shipping"];
$pk_obj->setShippingPrice($order_info["shipping_cost"]);
$shipping_tax_rate = getTaxRate($order_info, $order_info["shipping_ids"], "shipping");
if ($shipping_tax_rate != NULL)
    $shipping_taxes = $pk_obj->setTaxes($pk_obj->getShippingPrice(), $shipping_tax_rate);
if ($pk_obj->getShippingPrice() > 0) {
    $pk_obj->setUseDelivery(); //for discount set and precision correct check
    $pk_obj->updateFiscalCart($pk_obj->getPaymentFormType(),
                            $shipping_name,
                            $pk_obj->getShippingPrice(), 1,
                            $pk_obj->getShippingPrice(),
                            $shipping_taxes["tax"],
                            $shipping_taxes["tax_sum"]);
}

//set discounts
$pk_obj->setDiscounts($product_cart_sum, (float) $order_info["subtotal_discount"]);


//handle possible precision problem
$pk_obj->correctPrecision($product_cart_sum);

//$pk_obj->showDebugInfo($pk_obj->getFiscalCart());


$fiscal_cart_encoded = json_encode($pk_obj->getFiscalCart());

//generate payment form
$form = "";

if ($pk_obj->getPaymentFormType() == "create") { //create form
    $to_hash = $pk_obj->getOrderTotal(True)                .
               $pk_obj->getOrderParams("clientid")     .
               $pk_obj->getOrderParams("orderid")      .
               $pk_obj->getOrderParams("service_name") .
               $pk_obj->getOrderParams("client_email") .
               $pk_obj->getOrderParams("client_phone") .
               $pk_obj->getOrderParams("secret_key");
    $sign = hash ('sha256' , $to_hash);
    $pk_obj->setSubmitButtonCSSClass("");
    $form = '
        <h3>Сейчас Вы будете перенаправлены на страницу банка.</h3>
        <form name="payment" id="pay_form" action="'.$pk_obj->getOrderParams("form_url").'" accept-charset="utf-8" method="post">
        <input type="hidden" name="sum" value = "'.$pk_obj->getOrderTotal(True).'"/>
        <input type="hidden" name="orderid" value = "'.$pk_obj->getOrderParams("orderid").'"/>
        <input type="hidden" name="clientid" value = "'.$pk_obj->getOrderParams("clientid").'"/>
        <input type="hidden" name="client_email" value = "'.$pk_obj->getOrderParams("client_email").'"/>
        <input type="hidden" name="client_phone" value = "'.$pk_obj->getOrderParams("client_phone").'"/>
        <input type="hidden" name="service_name" value = "'.$pk_obj->getOrderParams("service_name").'"/>
        <input type="hidden" name="cart" value = \''.$fiscal_cart_encoded.'\' />
        <input type="hidden" name="sign" value = "'.$sign.'"/>
        <input type="submit" class="'.$pk_obj->getSubmitButtonCSSClass().'" value="Оплатить"/>
        </form>
        <script type="text/javascript">
        window.onload=function(){
            setTimeout(fSubmit, 2000);
        }
        function fSubmit() {
            document.forms["pay_form"].submit();
        }
        </script>';
}
else { //order form
    $payment_parameters = array(
        "clientid"=>$pk_obj->getOrderParams("clientid"),
        "orderid"=>$pk_obj->getOrderParams('orderid'),
        "sum"=>$pk_obj->getOrderTotal(),
        "phone"=>$pk_obj->getOrderParams("phone"),
        "client_email"=>$pk_obj->getOrderParams("client_email"),
        "cart"=>$fiscal_cart_encoded);
    $query = http_build_query($payment_parameters);
    $err_num = $err_text = NULL;
    if( function_exists( "curl_init" )) { //using curl
        $CR = curl_init();
        curl_setopt($CR, CURLOPT_URL, $pk_obj->getOrderParams("form_url"));
        curl_setopt($CR, CURLOPT_POST, 1);
        curl_setopt($CR, CURLOPT_FAILONERROR, true);
        curl_setopt($CR, CURLOPT_POSTFIELDS, $query);
        curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($CR, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec( $CR );
        $error = curl_error( $CR );
        if( !empty( $error )) {
            $form = "<br/><span class=message>"."INTERNAL ERROR:".$error."</span>";
            return false;
        }
        else {
            $form = $result;
        }
        curl_close($CR);
    }
    else { //using file_get_contents
        if (!ini_get('allow_url_fopen')) {
            $form_html = "<br/><span class=message>"."INTERNAL ERROR: Option allow_url_fopen is not set in php.ini"."</span>";
        }
        else {
            $form = file_get_contents($server, false, $context);
        }
    }
}
if ($form  == "") {
    $form = '<h3>Произошла ошибка при инциализации платежа</h3><p>$err_num: '.htmlspecialchars($err_text).'</p>';
}

//render form
echo $form;

fn_change_order_status($order_info['order_id'], 'O', '', false);

$_SESSION['cart'] = array(
    'user_data' => !empty($_SESSION['cart']['user_data']) ? $_SESSION['cart']['user_data'] : array(),
    'profile_id' => !empty($_SESSION['cart']['profile_id']) ? $_SESSION['cart']['profile_id'] : 0,
    'user_id' => !empty($_SESSION['cart']['user_id']) ? $_SESSION['cart']['user_id'] : 0,
);
$_SESSION['shipping_rates'] = array();
unset($_SESSION['shipping_hash']);

$condition = fn_user_session_products_condition();
db_query('DELETE FROM ?:user_session_products WHERE ' . $condition);

exit;

function getTaxRate($order_info, $tax_item_id, $type) {

    $order_taxes_info = $order_info["taxes"];
    foreach ($order_taxes_info as $current_tax) {
        switch ($type) {
            case "cart":
                $current_tax_items = $current_tax["applies"]["items"]["P"];
                break;
            case "shipping":
                $current_tax_items = $current_tax["applies"]["items"]["S"][0];
                break;
        }
        foreach ($current_tax_items as $key => $value)
            if ($tax_item_id == $key && $value == TRUE) {
                return (int) $current_tax["rate_value"];
            }
    }
    return NULL;
}
