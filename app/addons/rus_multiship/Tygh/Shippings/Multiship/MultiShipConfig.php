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

class MultiShipConfig
{
    public $client_id = '';
    public $sender_id = array();
    public $warehouse_id = array();
    public $requisite_id = array();
    public $api_url = '';
    public $format = '';

    public $keys = array(
        'getPaymentMethods' => '',
        'getDeliveryMethods' => '',
        'searchDeliveryList' => '',
        'createOrder' => '',
        'confirmSenderOrder' => '',
        'confirmSenderParcel' => '',
        'confirmSenderOrders' => '',
        'getSenderOrders' => '',
        'getSenderOrderLabel' => '',
        'getSenderParcelLabel' => '',
        'getSenderOrderStatus' => '',
        'getSenderOrderStatuses' => '',
        'getSenderNomenclature' => '',
        'getSenderGoodsBalans' => '',
        'getCities' => '',
        'getIndex' => '',
    );
}
