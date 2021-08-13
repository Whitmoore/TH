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
use Tygh\Shippings\RusSdek;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'details') {
    $params = $_REQUEST;
    if (!empty($params['order_id'])) {
        $data_status = array();
        list($_shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true));
        if (!empty($_shipments)) {
            foreach ($_shipments as $key => $shipment) {
                if ($shipment['carrier'] == 'sdek') {
                    $join = db_quote(" LEFT JOIN ?:rus_cities_sdek as b ON a.city_code = b.city_code ");
                    $join .= db_quote(" LEFT JOIN ?:rus_city_sdek_descriptions as c ON b.city_id = c.city_id ");
                    $d_status = db_get_array("SELECT a.*, IF(a.city_name != '', a.city_name, c.city) AS city FROM ?:rus_sdek_status as a ?p WHERE a.order_id = ?i AND a.shipment_id = ?i ORDER BY a.timestamp ASC", $join, $params['order_id'], $shipment['shipment_id']);

                    if (!empty($d_status)) {
                        $data_status[$key] = $d_status;
                    }
                }
            }

            Registry::get('view')->assign('data_status', $data_status);
            Registry::get('view')->assign('show_shipments', true);
            $navigation_tabs = Registry::get('navigation.tabs');
            $navigation_tabs['sdek_information'] = array(
                'title' => __('shipping_information'),
                'js' => true,
                'href' => 'orders.details?order_id=' . $params['order_id'] . '&selected_section=sdek_information'
            );
            Registry::set('navigation.tabs', $navigation_tabs);
        }
    }

} elseif ($mode == 'sdek_order_status') {
    if (!empty($_REQUEST['order_id'])) {
        $data_status = array();
        list($_shipments) = fn_get_shipments_info(array('order_id' => $_REQUEST['order_id'], 'advanced_info' => true));
        $data_status = array();
        if (!empty($_shipments)) {
            foreach ($_shipments as $key => $shipment) {
                if ($shipment['carrier'] == 'sdek') {
                    $_result = RusSdek::SdekRequest('https://api.cdek.ru/v2/orders?cdek_number=' . $shipment['tracking_number'], array(), 'get');

                    if (empty($_result['error']) && !empty($_result['response']['statuses'])) {
                        RusSdek::SdekAddStatusOrdersV2($_result['response']['statuses'], $_REQUEST['order_id'], $shipment['shipment_id']);
                    }

                    $join = db_quote(" LEFT JOIN ?:rus_cities_sdek as b ON a.city_code = b.city_code ");
                    $join .= db_quote(" LEFT JOIN ?:rus_city_sdek_descriptions as c ON b.city_id = c.city_id ");
                    $d_status = db_get_array("SELECT a.*, IF(a.city_name != '', a.city_name, c.city) AS city FROM ?:rus_sdek_status as a ?p WHERE a.order_id = ?i AND a.shipment_id = ?i ORDER BY a.timestamp ASC", $join, $_REQUEST['order_id'], $shipment['shipment_id']);

                    if (!empty($d_status)) {
                        $data_status[$key] = $d_status;
                    }
                }
            }
            Registry::get('view')->assign('data_status', $data_status);
            Registry::get('view')->display('addons/rus_sdek/components/data_statuses.tpl');
        }
    }
    exit;
}
