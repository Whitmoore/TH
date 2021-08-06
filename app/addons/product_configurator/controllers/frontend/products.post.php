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

if ($mode == 'configuration_group') {

    $product_configurator_group = db_get_row(
        "SELECT ?:conf_groups.group_id, ?:conf_group_descriptions.configurator_group_name, "
            . "?:conf_group_descriptions.full_description, ?:conf_groups.configurator_group_type, "
            . "?:conf_product_groups.position, ?:conf_product_groups.default_product_ids, ?:conf_product_groups.required "
        . "FROM ?:conf_groups "
            . "LEFT JOIN ?:conf_group_descriptions "
                . "ON ?:conf_group_descriptions.group_id = ?:conf_groups.group_id "
            . "LEFT JOIN ?:conf_product_groups "
                ."ON ?:conf_product_groups.group_id = ?:conf_groups.group_id "
        ."WHERE ?:conf_groups.status = 'A' AND ?:conf_group_descriptions.lang_code = ?s "
            . "AND ?:conf_groups.group_id = ?i",
        CART_LANGUAGE, $_REQUEST['group_id']
    );

    $product_configurator_group['main_pair'] = fn_get_image_pairs($_REQUEST['group_id'], 'conf_group', 'M');

    Registry::get('view')->assign('product_configurator_group', $product_configurator_group);
    Registry::get('view')->assign('group_id', $_REQUEST['group_id']);
    Registry::get('view')->display('addons/product_configurator/views/products/components/group_info.tpl');
    exit;

} elseif ($mode == 'configuration_product') {

    if (!empty($_REQUEST['product_id'])) {
        $product = fn_get_product_data($_REQUEST['product_id'], $auth, CART_LANGUAGE);
        fn_gather_additional_product_data($product, false, false, true, true, false);

        Registry::get('view')->assign('group_id', $_REQUEST['group_id']);
        Registry::get('view')->assign('product', $product);
        Registry::get('view')->assign('show_info', true);
        Registry::get('view')->display('addons/product_configurator/views/products/components/configuration_product.tpl');
        exit;
    }
}
