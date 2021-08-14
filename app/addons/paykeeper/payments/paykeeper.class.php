<?php
class PaykeeperPayment {

    private $fiscal_cart = array(); //fz54 cart
    private $order_total = 0; //order total sum
    private $shipping_price = 0; //shipping price
    private $use_taxes = false;
    private $use_delivery = false;
    private $order_params = NULL;
    private $discounts = array();
    private $submit_button_class = "";
	
    public function setOrderParams($order_total = 0, $clientid="", $orderid="", $client_email="",
                                    $client_phone="", $service_name="", $form_url="", $secret_key="")
    {
       $this->setOrderTotal($order_total);
       $this->order_params = array(
           "sum" => $order_total,
           "clientid" => $clientid,
           "orderid" => $orderid,
           "client_email" => $client_email,
           "client_phone" => $client_phone,
           "phone" => $client_phone,
           "service_name" => $service_name,
           "form_url" => $form_url,
           "secret_key" => $secret_key,
       );
    }

    public function getOrderParams($value)
    {
        return array_key_exists($value, $this->order_params) ? $this->order_params["$value"] : False;
    }

    public function updateFiscalCart($ftype, $name="", $price=0, $quantity=0, $sum=0, $tax="none", $tax_sum=0)
    {
        //update fz54 cart
        if ($ftype == "create") {
            $name = str_replace("\n ", "", $name);
            $name = str_replace("\r ", "", $name);
            //$name = preg_replace('/"{1,}/','\'',$name);
        }
        $this->fiscal_cart[] = array(
            "name" => $name,
            "price" => $price,
            "quantity" => $quantity,
            "sum" => $sum,
            "tax" => $tax,
            "tax_sum" => number_format($tax_sum, 2, ".", "")
        );
    }

    public function getFiscalCart()
    {
        return $this->fiscal_cart;
    }

    public function setDiscounts($cart_sum, $discount_value)
    {
        //set discounts
        if ($discount_value > 0) {

            $fiscal_cart_count = ($this->getUseDelivery()) ? count($this->getFiscalCart())-1 : count($this->getFiscalCart());
            $discount_modifier_value = ($this->getOrderTotal() - $this->getShippingPrice())/$cart_sum;
            //iterate fiscal cart without shipping
            for ($pos=0; $pos<$fiscal_cart_count; $pos++) {
                $this->fiscal_cart[$pos]["sum"] *= $discount_modifier_value;
                $this->fiscal_cart[$pos]["price"] = $this->fiscal_cart[$pos]["sum"]/$this->fiscal_cart[$pos]["quantity"];
                //formatting
                $this->fiscal_cart[$pos]["price"] = number_format($this->fiscal_cart[$pos]["price"], 3, ".", "");
                $this->fiscal_cart[$pos]["sum"] = number_format($this->fiscal_cart[$pos]["sum"], 2, ".", "");
                //recalculate taxes
                $this->recalculateTaxes($pos);
            }
        }
    }

    public function correctPrecision($cart_sum)
    {
        //handle possible precision problem
        $fiscal_cart_sum = $cart_sum;
        $total_sum = $this->getOrderTotal(True);
        //add shipping sum to cart sum
        if ($this->getShippingPrice() > 0)
            $fiscal_cart_sum += $this->fiscal_cart[count($this->fiscal_cart)-1]['sum'];
        //debug_info
        //echo "total: " . $total_sum . " - cart: " . $cart_sum;
        $diff_sum = $fiscal_cart_sum - $total_sum;
        if (abs($diff_sum) <= 0.01) {
            $this->setOrderTotal(number_format($total_sum+$diff_sum, 2, ".", ""));
        }
        else {
            if ($this->getUseDelivery() && ($fiscal_cart_sum < $total_sum)) {
                $this->setOrderTotal(number_format($total_sum+$diff_sum, 2, ".", ""));
                $delivery_pos = count($this->getFiscalCart())-1;
                $this->fiscal_cart[$delivery_pos]["price"] = number_format(
                                   $this->fiscal_cart[$delivery_pos]["price"]+$diff_sum, 2, ".", "");
                $this->fiscal_cart[$delivery_pos]["sum"] = number_format(
                                   $this->fiscal_cart[$delivery_pos]["sum"]+$diff_sum, 2, ".", "");
                $this->recalculateTaxes($delivery_pos);
            }
        }
    }

    public function setOrderTotal($value)
    {
        $this->order_total = $value;
    }

    public function getOrderTotal($format=False)
    {
        return ($format) ? number_format($this->order_total, 2, ".", "") : 
                                         $this->order_total;
    }

    public function setShippingPrice($value)
    {
        $this->shipping_price = $value;
    }

    public function getShippingPrice()
    {
        return $this->shipping_price;
    }

    public function getPaymentFormType()
    {
        if (strpos($this->order_params["form_url"], "/order/inline") == True)
            return "order";
        else
            return "create";
    }

    public function setUseTaxes()
    {
        $this->use_taxes = TRUE;
    }

    public function getUseTaxes()
    {
        return $this->use_taxes;
    }

    public function setUseDelivery()
    {
        $this->use_delivery = True;
    }

    public function getUseDelivery()
    {
        return $this->use_delivery;
    }

    public function recalculateTaxes($item_pos)
    {
        //recalculate taxes
        switch($this->fiscal_cart[$item_pos]['tax']) {
            case "vat10":
                $this->fiscal_cart[$item_pos]['tax_sum'] = round((float)
                    (($this->fiscal_cart[$item_pos]['sum']/110)*10), 2);
                break;
            case "vat18":
                $this->fiscal_cart[$item_pos]['tax_sum'] = round((float)
                    (($this->fiscal_cart[$item_pos]['sum']/118)*18), 2);
                break;
        }
    }

    public function setTaxes($sum, $tax_rate)
    {
        $taxes = array("tax" => "none", "tax_sum" => 0);
        if ($tax_rate !== NULL) {
            switch(number_format($tax_rate, 0, ".", "")) {
                case 0:
                    $taxes["tax"] = "vat0";
                    $taxes["tax_sum"] = number_format(0, 2, ".", "");
                    break;
                case 10:
                    $taxes["tax"] = "vat10";
                    $taxes["tax_sum"] = round((float)(($sum/110)*10), 2);
                    if (!$this->getUseTaxes())
                        $this->setUseTaxes();
                    break;
                case 18:
                    $taxes["tax"] = "vat18";
                    $taxes["tax_sum"] = round((float)(($sum/118)*18), 2);
                    if (!$this->getUseTaxes())
                        $this->setUseTaxes();
                    break;
            }
        }
        return $taxes;
    }

    public function showDebugInfo($obj_to_debug)
    {
        echo "<pre>";
        var_dump($obj_to_debug);
        echo "</pre>";
    }

    public function setSubmitButtonCSSClass($class_string)
    {
        $this->submit_button_class = $class_string;
    }

    public function getSubmitButtonCSSClass()
    {
        return $this->submit_button_class;
    }

    public function parseOrderId($orderid)
    {
        return explode("-", $orderid);
    }

}
