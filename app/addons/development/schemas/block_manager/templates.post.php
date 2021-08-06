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

$schema['addons/development/blocks/news_feed/news_table.tpl'] = array (
    'settings' => array(
        'number_of_columns' => array (
            'type' => 'input',
            'default_value' => '3'
        ),
    )
);
$schema['addons/development/blocks/products/th_products_block.tpl'] = array (
    'settings' => array(
        'is_capture' => array (
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'format' => array (
            'type' => 'selectbox',
            'values' => array (
                'S' => 'scroller',
                'G' => 'grid_list',
            ),
            'default_value' => 'G'
        ),
        'mode' => array (
            'type' => 'selectbox',
            'values' => array (
                'R' => 'regular',
                'S' => 'small',
                'N' => 'mini',
                'M' => 'micro'
            ),
            'default_value' => 'R'
        ),
        'columns_number' =>  array (
            'type' => 'input',
            'default_value' => 5
        ),
        'not_scroll_automatically' => array (
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'scroll_per_page' =>  array (
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'speed' =>  array (
            'type' => 'input',
            'default_value' => 400
        ),
        'pause_delay' =>  array (
            'type' => 'input',
            'default_value' => 3
        ),
        'item_quantity' =>  array (
            'type' => 'input',
            'default_value' => 5
        ),
        'thumbnail_width' =>  array (
            'type' => 'input',
            'default_value' => 80
        )
    ),
    'bulk_modifier' => array (
        'fn_gather_additional_products_data' => array (
            'products' => '#this',
            'params' => array (
                'get_icon' => true,
                'get_detailed' => true,
                'get_options' => true,
                'get_title_features' => true,
            ),
        ),
    ),
);
$schema['blocks/pages/pages_text_links.tpl']['fillings'][] = 'dynamic_content';

$schema['blocks/products/products_scroller.tpl']['settings']['mode'] = array (
    'type' => 'selectbox',
    'values' => array (
        'R' => 'regular',
        'S' => 'small',
        'N' => 'mini',
        'M' => 'micro'
    )
);
$schema['blocks/products/products_multicolumns.tpl']['settings']['mode'] = array (
    'type' => 'selectbox',
    'values' => array (
        'R' => 'regular',
        'S' => 'small',
        'N' => 'mini',
        'M' => 'micro'
    )
);
$schema['addons/development/blocks/categories/categories_roundabout.tpl']['params'] = array(
    'roundabout' => true
);
$schema['addons/development/blocks/products/products_cross_sales.tpl'] = array (
    'settings' => array(
        'show_price' => array (
            'type' => 'checkbox',
            'default_value' => 'Y'
        ),
        'enable_quick_view' => array (
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'not_scroll_automatically' => array (
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'scroll_per_page' =>  array (
            'type' => 'checkbox',
            'default_value' => 'N'
        ),
        'speed' =>  array (
            'type' => 'input',
            'default_value' => 400
        ),
        'pause_delay' =>  array (
            'type' => 'input',
            'default_value' => 3
        ),
        'item_quantity' =>  array (
            'type' => 'input',
            'default_value' => 5
        ),
        'thumbnail_width' =>  array (
            'type' => 'input',
            'default_value' => 80
        ),
        'mode' => array (
            'type' => 'selectbox',
            'values' => array (
                'R' => 'regular',
                'S' => 'small',
                'N' => 'mini',
                'M' => 'micro'
            )
        )
    ),
    'bulk_modifier' => array (
        'fn_gather_additional_products_data_cs' => array (
            'products' => '#this',
            'params' => array (
                'get_icon' => true,
                'get_detailed' => true,
                'get_options' => true,
            ),
        ),
    ),
    'fillings' => array('product_cross_sales'),
);
$schema['common/breadcrumbs.tpl']['cache'] = array(
    'cache_level' => 'static'
);
$schema['addons/development/blocks/static_templates/ssl_seal.tpl']['cache'] = array(
    'cache_level' => 'static',
    'no_object' => true
);
$schema['blocks/static_templates/copyright.tpl']['cache'] = array(
    'cache_level' => 'static',
    'no_object' => true
);
$schema['addons/development/blocks/static_templates/shipping_and_payment.tpl']['cache'] = array(
    'cache_level' => 'static',
    'no_object' => true
);
$schema['addons/development/blocks/static_templates/catalog.tpl']['cache'] = array(
    'cache_level' => 'static',
    'no_object' => true
);
// $schema['addons/news_and_emails/blocks/static_templates/subscribe.tpl']['cache'] = array(
//     'cache_level' => 'static',
//     'no_object' => true
// );
$schema['blocks/smarty_block.tpl']['cache'] = array(
    'cache_level' => 'static',
    'no_object' => true
);
$schema['blocks/static_templates/my_account_links.tpl']['cache'] = array(
    'cache_level' => 'static',
    'no_object' => true,
    'auth_handlers' => array('user_id')
);
// $schema['addons/development/blocks/static_templates/category_title.tpl']['cache'] = array(
//     'cache_level' => 'static',
//     'request_handlers' => array('%TC_ID%')
// );
$schema['addons/development/blocks/static_templates/image_title.tpl']['cache'] = array(
    'cache_level' => 'static',
);
$schema['addons/development/blocks/static_templates/top_block.tpl']['cache'] = array(
    'cache_level' => 'static',
    'no_object' => true,
    'request_handlers' => array('q'),
    'auth_handlers' => array('user_id'),
    'session_handlers' => array('cart', 'wishlist', 'comparison_list')
);
$schema['addons/development/blocks/static_templates/tennis_bags.tpl']['cache'] = array(
    'update_handlers' => array ('categories', 'category_descriptions'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['addons/development/blocks/static_templates/tennis_strings.tpl']['cache'] = array(
    'update_handlers' => array ('categories', 'category_descriptions'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['addons/development/blocks/static_templates/mens_apparel_and_shoes.tpl']['cache'] = array(
    'update_handlers' => array ('categories', 'category_descriptions'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['addons/development/blocks/static_templates/womens_apparel_and_shoes.tpl']['cache'] = array(
    'update_handlers' => array ('categories', 'category_descriptions'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['addons/development/blocks/static_templates/balls.tpl']['cache'] = array(
    'update_handlers' => array ('categories', 'category_descriptions'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['addons/development/blocks/static_templates/player_profiles.tpl']['cache'] = array(
    'update_handlers' => array ('players'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['addons/development/blocks/static_templates/learning_center.tpl']['cache'] = array(
    'cache_level' => 'static',
    'no_object' => true
);

return $schema;