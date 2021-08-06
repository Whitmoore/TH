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

use Tygh\Languages\Languages;

use Tygh\Shippings\Multiship\Multiship;
use Tygh\Shippings\Multiship\Objects\MultiShipOrder;
use Tygh\Shippings\Multiship\Objects\MultiShipOrderItem;
use Tygh\Shippings\Multiship\Objects\MultiShipRecipient;
use Tygh\Shippings\Multiship\Objects\MultiShipDelivery;
use Tygh\Shippings\Multiship\Objects\MultiShipDeliveryPoint;

if ( !defined('AREA') ) { die('Access denied'); }

function fn_rus_multiship_install()
{
    $multiship_id = db_query("INSERT INTO ?:settings_objects (`edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`) VALUES ('ROOT', 'multiship_enabled', '7', '0', 'C', 'Y', '90', 'Y')");

    foreach (Languages::getAll() as $lang_code => $lang_data) {
        db_query("INSERT INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES (?i, 'O', ?s, 'Включить MultiShip', '')", $multiship_id, $lang_code);
    }

    $service = array(
        'status' => 'A',
        'module' => 'multiship',
        'code' => 'multiship',
        'sp_file' => '',
        'description' => 'Multiship',
    );

    $service['service_id'] = db_query('INSERT INTO ?:shipping_services ?e', $service);

    foreach (Languages::getAll() as $service['lang_code'] => $lang_data) {
        db_query('INSERT INTO ?:shipping_service_descriptions ?e', $service);
    }
}

function fn_rus_multiship_uninstall()
{
    $multiship_id = db_get_field('SELECT object_id FROM ?:settings_objects WHERE name = ?s', 'multiship_enabled');
    if (!empty($multiship_id)) {
        db_query('DELETE FROM ?:settings_objects WHERE object_id = ?i', $multiship_id);
        db_query('DELETE FROM ?:settings_descriptions WHERE object_id = ?i', $multiship_id);
    }

    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'multiship');
    if (!empty($service_ids)) {
        db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
        db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
    }
}

function fn_rus_multiship_calculate_cart_taxes_pre(&$cart, $cart_products, &$product_groups)
{
    if (!empty($cart['shippings_extra'])) {
        foreach ($product_groups as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    $shipping_id = $shipping['shipping_id'];

                    if (!empty($cart['shippings_extra']['data'][$group_key][$shipping_id])) {
                        $shipping_extra = $cart['shippings_extra']['data'][$group_key][$shipping_id];
                        $product_groups[$group_key]['chosen_shippings'][$shipping_key]['shipping_extra'] = $shipping_extra;
                    }
                }
            }
        }
    }

}

function fn_rus_multiship_get_shipping_ids()
{
    return db_get_fields("SELECT shipping_id FROM ?:shippings WHERE service_id = " .
                            "(SELECT service_id FROM ?:shipping_services WHERE module='multiship');");
}

function fn_rus_multiship_get_carriers()
{
    $carriers = array();
    $carriers[] = 'sdek';
    $carriers[] = 'boxberry';

    return $carriers;
}

function fn_rus_multiship_get_orders($params)
{
    $condition = '';
    if (!empty($params['shipment_id'])) {
        $condition = db_quote('AND shipment_id = ?i', $params['shipment_id']);
    }

    $orders = db_get_array('SELECT multiship_order_id, multiship_order_status, shipment_id, multiship_parcel_id FROM ?:rus_multiship_orders WHERE 1 ?p', $condition);

    return $orders;
}

function fn_rus_multiship_get_order_by_shipment($shipment_id)
{
    $params = array(
        'shipment_id' => $shipment_id
    );

    $orders = fn_rus_multiship_get_orders($params);

    return reset($orders);
}

function fn_rus_multiship_create_multiship_order($shipment)
{
    $order_info = fn_get_order_info($shipment['order_id']);

    $ms_api = MultiShip::init();
    $order = new MultiShipOrder();
    $orderitem = new MultiShipOrderItem();
    $recipient = new MultiShipRecipient();
    $delivery = new MultiShipDelivery();
    $delivery_point = new MultiShipDeliveryPoint();

    $shipment_products = db_get_hash_array('SELECT item_id, product_id, amount FROM ?:shipment_items WHERE shipment_id = ?i', 'item_id', $shipment['shipment_id']);

    foreach ($order_info['products'] as $item_id => $product) {
        if (!empty($shipment_products[$item_id])) {
            $product_data = fn_get_product_data($product['product_id'], $_SESSION['auth']);

            $orderitem->article = $product['product_code'];
            $orderitem->name = $product['product'];
            $orderitem->quantity = $shipment_products[$item_id]['amount'];
            $orderitem->cost = $product['price'];
            $orderitem->width = $product_data['box_width'];
            $orderitem->height = $product_data['box_height'];
            $orderitem->length = $product_data['box_length'];
            $orderitem->weight = $product_data['weight'];

            $order->appendItem($orderitem);
        }
    }

    $order->sender = 1324;
    $order->warehouse = 877;
    $order->requisite = 631;
    $order->num = $order_info['order_id'];
    $order->date = $order_info['timestamp'];
    $order->user_status_id = null;

    $order->weight = 1;
    $order->width = 1;
    $order->height = 1;
    $order->length = 1;

    $order->payment_method = 1;
    $order->delivery_cost = $order_info['shipping_cost'];
    $order->assessed_value = $order_info['subtotal'];
    $order->comment = $order_info['notes'];
    $order->total_cost = $order_info['total'];

    $recipient->first_name = $order_info['s_firstname'];
    $recipient->last_name = $order_info['s_lastname'];
    $recipient->middle_name = '';
    $recipient->phone = $order_info['s_phone'];
    $recipient->email = $order_info['email'];

    $product_group = reset($order_info['product_groups']);
    $shipping = reset($product_group['chosen_shippings']);
    $package_info = $product_group['package_info'];

    $delivery->direction = $shipping['shipping_extra']['direction'];
    $delivery->delivery = $shipping['shipping_extra']['delivery'];
    $delivery->pickuppoint = $shipping['shipping_extra']['pickuppoint'];
    $delivery->price = $shipping['shipping_extra']['price'];
    $delivery->delivery_pickuppoint = $shipping['shipping_extra']['pickuppoint'];
    $delivery->to_ms_warehouse = $shipping['shipping_extra']['to_ms_warehouse'];
    $delivery->city = $package_info['location']['city'];
    $delivery->index = $package_info['location']['zipcode'];
    $delivery->street = $package_info['location']['address'];
    $delivery->house = '';

    $order_new = $ms_api->createOrder($order, $recipient, $delivery, $delivery_point);

    if ($order_new == false && $ms_api->_error) {
        fn_print_die($ms_api->_error, $order_new);
    }

    if ($order_new->status == 'ok') {
        $multiship_order = array(
            'multiship_order_id' => $order_new->data->order_id,
            'multiship_order_status' => MULTISHIP_STATUS_NONE,
            'multiship_parcel_id' => '',
            'shipment_id' => $shipment['shipment_id']
        );

        db_query('INSERT INTO ?:rus_multiship_orders ?e', $multiship_order);
    }
}

function fn_rus_multiship_update_multiship_order()
{

}
