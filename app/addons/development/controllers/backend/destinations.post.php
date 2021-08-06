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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_REQUEST['destination_id'] = empty($_REQUEST['destination_id']) ? 0 : $_REQUEST['destination_id'];

// Destiantion data
if ($mode == 'update') {

    $tabs = Registry::get('navigation.tabs');
    $tabs = array(
        'detailed' => array(
            'title' => __('general'),
            'js' => true
        ),
        'shipping_methods' => array(
            'title' => __('shipping_methods'),
            'js' => true
        ),
    );
    Registry::set('navigation.tabs', $tabs);
    $destination = Registry::get('view')->gettemplatevars('destination');
    $shippings = db_get_hash_single_array("SELECT ?:shippings.shipping_id, ?:shipping_descriptions.shipping FROM ?:shippings LEFT JOIN ?:shipping_descriptions ON ?:shippings.shipping_id = ?:shipping_descriptions.shipping_id AND ?:shipping_descriptions.lang_code = ?s ORDER BY ?:shippings.position, ?:shipping_descriptions.shipping", array('shipping_id', 'shipping'), DESCR_SL);

    if (!empty($destination['shipping_ids'])) {
        $destination['shipping_ids'] = unserialize($destination['shipping_ids']);
        $shipping_ids = array();
        foreach ($destination['shipping_ids'] as $i => $id) {
            $shipping_ids[$id] = $shippings[$id];
            unset($shippings[$id]);
        }
        $destination['shipping_ids'] = $shipping_ids;
    }
    Registry::get('view')->assign('destination', $destination);
    Registry::get('view')->assign('shipping_methods', $shippings);
}