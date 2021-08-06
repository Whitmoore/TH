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

if ($mode == 'list') {

    fn_add_breadcrumb(__('promotions'));

    $params = array (
        'active' => true,
        'get_hidden' => false,
        'show_on_site' => 'Y',
        'plain' => false
    );

    list($promotions) = fn_get_promotions($params);

    Registry::get('view')->assign('promotions', $promotions);
}
if ($mode == 'view') {
    $promotion_data = fn_get_promotion_data($_REQUEST['promotion_id']);
    
    if (empty($promotion_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    
    fn_add_breadcrumb(__('promotions'), 'promotions.list');
    fn_add_breadcrumb($promotion_data['name']);
    
    if (!empty($promotion_data['tagged_products'])) {
        $_params = array(
            'item_ids' => implode(',', $promotion_data['tagged_products'])
        );
        list($products,) = fn_get_products($_params);
        if (Registry::get('settings.General.catalog_image_afterload') == 'Y') {
            fn_gather_additional_products_data($products, array(
                'get_icon' => false,
                'get_detailed' => false,
                'check_detailed' => true,
                'get_additional' => false,
                'check_additional' => true,
                'get_options' => true,
                'get_discounts' => true,
                'get_features' => false,
                'get_title_features' => true,
                'allow_duplication' => true,
            ));
        } else {
            fn_gather_additional_products_data($products, array(
                'get_icon' => false,
                'get_detailed' => true,
                'get_additional' => false,
                'check_additional' => true,
                'get_options' => true,
                'get_discounts' => true,
                'get_features' => false,
                'get_title_features' => true,
                'allow_duplication' => true,
            ));
        }
        Registry::get('view')->assign('products', $products);
    }
    Registry::get('view')->assign('page_title', $promotion_data['page_title']);
    Registry::get('view')->assign('meta_description', $promotion_data['meta_description']);
    Registry::get('view')->assign('promotion_data', $promotion_data);
}