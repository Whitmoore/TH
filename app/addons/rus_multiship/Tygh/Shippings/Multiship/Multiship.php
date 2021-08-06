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

namespace Tygh\Shippings\Multiship;

use Tygh\Registry;

class Multiship
{
    public static function init($config = null)
    {
        if ($config == null) {

            $config = new MultiShipConfig();

            $config->client_id = Registry::get('addons.rus_multiship.client_id');
            $config->sender_id = array(1324);
            $config->warehouse_id = array(877);
            $config->requisite_id = array(631);
            $config->api_url = "https://multiship.ru/OpenAPI_v3/";
            $config->format = 'json';

            $config->keys = array(
                'getPaymentMethods' => '',
                'getDeliveryMethods' => '',
                'searchDeliveryList' => Registry::get('addons.rus_multiship.key_search_delivery_list'),
                'createOrder' => Registry::get('addons.rus_multiship.key_create_order'),
                'confirmSenderOrders' => 'd8dc10d88b79ca9fedb9f89a17cba7b77b92ca0339c658e534fbaa0e8457aec9',//Registry::get('addons.rus_multiship.confirm_sender_order'),
                'confirmSenderParcel' => '',
                'getSenderOrders' => '',
                'getSenderOrderLabel' => '',
                'getSenderParcelLabel' => '',
                'getSenderOrderStatus' => Registry::get('addons.rus_multiship.key_get_sender_order_status'),
                'getSenderOrderStatuses' => 'd8dc10d88b79ca9fedb9f89a17cba7b7a2e7e7894f42364be338c5e2786eaf9a',
                'getSenderNomenclature' => '',
                'getSenderGoodsBalans' => '',
                'getIndex' => '',
            );
        }

        if (!isset($config) || !isset($config->client_id) || ($config->client_id == '')) {
            die(MULTISHIP_ERROR_CONFIG);
        }

        return new MultishipOpenApi($config);
    }
}
