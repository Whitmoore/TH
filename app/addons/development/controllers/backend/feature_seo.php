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

$_REQUEST['item_id'] = empty($_REQUEST['item_id']) ? 0 : $_REQUEST['item_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'feature_seo_data'
    );

    //
    // Create/update player
    //
    if ($mode == 'update') {

        fn_update_feature_seo($_REQUEST['feature_seo_data'], $_REQUEST['item_id']);

        $suffix = '.manage?category_id=' . $_REQUEST['feature_seo_data']['category_id'];
    }

    //
    // Processing deleting of multiple player elements
    //
    if ($mode == 'm_delete') {

        if (isset($_REQUEST['item_ids'])) {
            fn_delete_feature_seo($_REQUEST['item_ids']);
        }

        $suffix = ".manage";
    }
    
    return array(CONTROLLER_STATUS_OK, "feature_seo$suffix");
}

if ($mode == 'manage') {

    $params = $_REQUEST;
    $params['get_descriptions'] = true;
    Registry::get('view')->assign('feature_seos', fn_get_feature_seos($params));
    Registry::get('view')->assign('category_id', $_REQUEST['category_id']);

} elseif ($mode == 'update') {

    $params = $_REQUEST;
    $params['get_descriptions'] = true;
    $feature_seo_data = fn_get_feature_seos($params);
    Registry::get('view')->assign('feature_seo', $feature_seo_data['data'][$_REQUEST['item_id']]);
    Registry::get('view')->assign('category_id', $feature_seo_data['data'][$_REQUEST['item_id']]['category_id']);

} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['item_id'])) {
        fn_delete_feature_seo((array) $_REQUEST['item_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "feature_seo.manage&category_id=" . $_REQUEST['category_id']);
    
} elseif ($mode == 'get_feature_variants') {

    list($feature_variants,) = fn_get_product_feature_variants(array(
        'feature_id' => $_REQUEST['feature_id']
    ), 0, CART_LANGUAGE);
    
    Registry::get('view')->assign('feature_variants', $feature_variants);
    Registry::get('view')->assign('feature_id', $_REQUEST['feature_id']);
    Registry::get('view')->assign('key', array_sum(explode('_', str_replace('feature_variants_' . $_REQUEST['id'] . '_', '', $_REQUEST['result_ids']))));
    Registry::get('view')->assign('obj_id', $_REQUEST['result_ids']);
    Registry::get('view')->display('addons/development/views/feature_seo/components/feature_variants.tpl');
    exit;
}
