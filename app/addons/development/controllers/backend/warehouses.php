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

$_REQUEST['warehouse_id'] = empty($_REQUEST['warehouse_id']) ? 0 : $_REQUEST['warehouse_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'warehouse_data',
        'warehouses_data'
    );

    //
    // Create/update warehouse
    //
    if ($mode == 'update') {

        $warehouse_id = fn_update_warehouse($_REQUEST['warehouse_data'], $_REQUEST['warehouse_id']);

        if (!empty($warehouse_id)) {
            $suffix = ".update?warehouse_id=$warehouse_id" . (!empty($_REQUEST['warehouse_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['warehouse_data']['block_id'] : "");
        } else {
            $suffix = '.manage';
        }
    }

    //
    // Processing deleting of multiple warehouse elements
    //
    if ($mode == 'm_delete') {

        if (isset($_REQUEST['warehouse_ids'])) {
            foreach ($_REQUEST['warehouse_ids'] as $v) {
                if (fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('ULTIMATE') && fn_check_company_id('warehouses', 'warehouse_id', $v))) {
                    fn_delete_warehouse($v);
                }
            }
        }

        unset($_SESSION['warehouse_ids']);

        fn_set_notification('N', __('notice'), __('text_warehouses_have_been_deleted'));
        $suffix = ".manage";
    }

    if ($mode == 'm_update') {
        if (!empty($_REQUEST['warehouses_data'])) {
            foreach ($_REQUEST['warehouses_data'] as $k => $v) {
                fn_update_warehouse($v, $k);
            }
        }
        $suffix = ".manage";
    }

    return array(CONTROLLER_STATUS_OK, "warehouses$suffix");
}

//
// 'Add new warehouse' page
//
if ($mode == 'add') {

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    ));
    // [/Page sections]

    Registry::get('view')->assign('brands', fn_development_get_brands());
//
// 'warehouse update' page
//
} elseif ($mode == 'update') {

    $warehouse_id = $_REQUEST['warehouse_id'];
    // Get current warehouse data
    $warehouse_data = fn_get_warehouse_data($warehouse_id);

    if (empty($warehouse_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $params = array(
        'warehouse_id' => $warehouse_id,
        'hide_out_of_stock' => 'Y',
    );
    list($products,) = fn_get_products($params);

    $net_ttl = 0;
    $rrp_total = 0;
    foreach ($products as $prod) {
        if (!empty($prod['net_cost']) && $prod['net_cost'] > 0) {
            $wh_inv = explode('|', $prod['wh_inventory']);
            $amount = 0;
            foreach ($wh_inv as $kk => $amnt) {
                $tmp = explode('_', $amnt);
                if (count($tmp) == 3 && $tmp[0] == $warehouse_id) {
                    $amount += $tmp[2];
                }
            }

            if (empty($prod['net_currency_code'])) {
                $global_data = fn_get_product_global_data($prod, array('net_currency_code'));
                $prod['net_currency_code'] = !empty($global_data['net_currency_code']) ? $global_data['net_currency_code'] : CART_PRIMARY_CURRENCY;
            }
            $net_ttl += $prod['net_cost'] * Registry::get('currencies.' . $prod['net_currency_code'] . '.coefficient') * $amount;
            $rrp_total += $prod['price'] * $amount;
        }
    }

    // [Page sections]
    $tabs = array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'import_inventory' => array (
            'title' => __('import_inventory'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    );

    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]
    Registry::get('view')->assign('total', array('net' => $net_ttl, 'rrp' => $rrp_total));
    Registry::get('view')->assign('warehouse_data', $warehouse_data);
    Registry::get('view')->assign('brands', fn_development_get_brands());

}
//
// Delete warehouse
//
elseif ($mode == 'delete') {

    if (!empty($_REQUEST['warehouse_id'])) {
        fn_delete_warehouse($_REQUEST['warehouse_id']);
    }

    fn_set_notification('N', __('notice'), __('text_warehouse_has_been_deleted'));

    return array(CONTROLLER_STATUS_REDIRECT, "warehouses.manage");

//
// 'Management' page
//
} elseif ($mode == 'manage' || $mode == 'picker') {

    $params = $_REQUEST;
    list($warehouses, $search) = fn_get_warehouses($params);

    Registry::get('view')->assign('warehouses', $warehouses);
    Registry::get('view')->assign('search', $search);

}

//
// Categories picker
//
if ($mode == 'picker') {
    Registry::get('view')->display('addons/development/pickers/warehouses/picker_contents.tpl');
    exit;
}
