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

use Tygh\Enum\ProductTracking;
use Tygh\Registry;
use Tygh\BlockManager\SchemesManager;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'm_approve') {
        if (isset($_REQUEST['product_ids'])) {
            db_query("UPDATE ?:products SET approval_status = 'A', status = 'A' WHERE product_id IN (?n)", $_REQUEST['product_ids']);
        }
        return array(CONTROLLER_STATUS_OK, "products.manage?approval_status=P");
    }
    if ($mode == 'm_decline') {
        if (isset($_REQUEST['product_ids'])) {
            db_query("UPDATE ?:products SET approval_status = 'D', status = 'H' WHERE product_id IN (?n)", $_REQUEST['product_ids']);
        }
        return array(CONTROLLER_STATUS_OK, "products.manage?approval_status=P");
    }
}

if ($_SERVER['REQUEST_METHOD'] != 'POST' && $mode == 'update') {

    $tabs = Registry::get('navigation.tabs');

    $players_tab = array (
        'title' => __('players'),
        'js' => true
    );
    $tabs = fn_insert_before_key($tabs, 'seo', 'players', $players_tab);

    if (!empty($tabs['features'])) {
        $features_tab = $tabs['features'];
        unset($tabs['features']);
        $tabs = fn_insert_before_key($tabs, 'shippings', 'features', $features_tab);
    }
    if (!empty($tabs['seo'])) {
        $seo_tab = $tabs['seo'];
        unset($tabs['seo']);
        $tabs = fn_insert_before_key($tabs, 'shippings', 'seo', $seo_tab);
    }
    $technologies_tab = array (
        'title' => __('technologies'),
        'js' => true
    );
    $tabs = fn_insert_before_key($tabs, 'seo', 'technologies', $technologies_tab);
    $variations_tab = array (
        'title' => __('variations'),
        'js' => true
    );
    $tabs = fn_insert_before_key($tabs, 'seo', 'variations', $variations_tab);
    $product_data = Registry::get('view')->gettemplatevars('product_data');
    if (!empty($product_data['price_history'])) {
        $price_history_tab = array (
            'title' => __('price_history'),
            'js' => true
        );
        $tabs = fn_insert_before_key($tabs, 'files', 'price_history', $price_history_tab);
    }
//     $warehouses_tab = array (
//         'title' => __('warehouses'),
//         'js' => true
//     );
//     $tabs = fn_insert_before_key($tabs, 'players', 'warehouses', $warehouses_tab);
//     $options_tab = $tabs['options'];
//     unset($tabs['options']);
//     $tabs['options'] = $options_tab;


    // [/Product tabs]
    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]

    $product_options = Registry::get('view')->gettemplatevars('product_options');
    $product_data['hide_features'] = array(PLAYER_FEATURE_ID);
    if (!empty($product_options)) {
        foreach ($product_options as $i => $opt_data) {
            if (!empty($opt_data['feature_id'])) {
                $product_data['hide_features'][] = $opt_data['feature_id'];
            }
        }
    }
    $product_data = Registry::get('view')->assign('product_data', $product_data);
}
