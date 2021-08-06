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

if ($mode == 'update' || $mode == 'add') {

    Registry::get('view')->assign('filter_items', $filters);

    $params = array(
        'variants' => false,
        'plain' => true,
        'exclude_group' => true
    );

    list($filter_features) = fn_get_product_features($params, 0, DESCR_SL);
    $section_features = array();
    foreach ($filter_features as $i => $feature) {
        $section_features[$feature['feature_id']] = (!empty($feature['group_description']) ? $feature['group_description'] . ': ' : '') . $feature['description'];
    }
    $category_data = Registry::get('view')->gettemplatevars('category_data');
    $filter_params = array(
        'get_variants' => false,
        'short' => true,
        'category_ids' => $category_data['category_id'],
        'feature_type' => array('S', 'M', 'E')
    );
    list($filters) = fn_get_product_filters($filter_params);
    Registry::get('view')->assign('filter_features', $filters);
    if (!empty($category_data['sections_categorization'])) {
        $f_ids = array();
        foreach ($category_data['sections_categorization'] as $j => $f_id) {
            $f_ids[$f_id] = $section_features[$f_id];
            unset($section_features[$f_id]);
        }
        $category_data['sections_categorization'] = $f_ids;
    }

    fn_get_schema('settings', 'variants.functions', 'php', true);
    $category_data['sortings'] = fn_settings_variants_appearance_available_product_list_sortings();

    $params = array(
        'category_id' => $category_data['category_id'],
        'get_descriptions' => true
    );
    $category_data['feature_seos'] = fn_get_feature_seos($params);

    Registry::get('view')->assign('category_data', $category_data);
    Registry::get('view')->assign('section_features', $section_features);

    $tabs = Registry::get('navigation.tabs');
    $update_products_tab = array (
        'title' => __('update_products'),
        'js' => true
    );
    $tabs = fn_insert_before_key($tabs, 'addons', 'update_products', $update_products_tab);
    $tabs['cross_categories'] = array (
        'title' => __('cross_categories'),
        'js' => true
    );
    $tabs['qty_discounts'] = array (
        'title' => __('qty_discounts'),
        'js' => true
    );
    $tabs['feature_seo'] = array (
        'title' => __('feature_seo'),
        'js' => true
    );
    Registry::set('navigation.tabs', $tabs);
}
