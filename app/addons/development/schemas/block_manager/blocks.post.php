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

$schema['products']['cache'] = array(
    'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories')
);
$schema['products']['content']['items']['fillings']['bestsellers']['cache'] = array(
    'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['products']['content']['items']['fillings']['newest']['cache'] = array(
    'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['products']['content']['items']['fillings']['discounted_products']['cache'] = array(
    'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['pages']['content']['items']['fillings']['full_tree_pages']['cache'] = array(
    'update_handlers' => array ('pages', 'page_descriptions'),
    'cache_level' => 'static',
    'no_object' => true
);
$schema['products']['content']['items']['fillings']['player_racket'] = array(
    'params' => array (
        'cid' => RACKETS_CATEGORY_ID,
        'subcats' => 'Y',
        'show_out_of_stock' => true,
        'limit' => 1,
        'request' => array (
            'player_id' => '%PLAYER_ID%',
        ),
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories', 'players_gear'),
        'cache_level' => 'static',
    )
);
$schema['products']['content']['items']['fillings']['player_bags_accessories'] = array(
    'params' => array (
        'cid' => array(BAGS_CATEGORY_ID, RACKETS_CATEGORY_ID, STRINGS_CATEGORY_ID, GRIPS_CATEGORY_ID, DAMPENERS_CATEGORY_ID),
        'subcats' => 'Y',
        'show_out_of_stock' => true,
        'request' => array (
            'player_id' => '%PLAYER_ID%',
        ),
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories', 'players_gear'),
        'cache_level' => 'static',
    )
);
$schema['products']['content']['items']['fillings']['player_apparel_shoes'] = array(
    'params' => array (
        'cid' => array(APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID),
        'subcats' => 'Y',
        'show_out_of_stock' => true,
        'request' => array (
            'player_id' => '%PLAYER_ID%',
        ),
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories', 'players_gear'),
        'cache_level' => 'static',
    )
);
$schema['products']['content']['items']['fillings']['similar_products'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'request' => array (
            'similar_pid' => '%PRODUCT_ID%',
            'exclude_pid' => '%PRODUCT_ID%'
        ),
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'no_items_func' => 'fn_get_similar_category_products',
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
    )
);
$schema['products']['content']['items']['fillings']['also_bought']['cache'] = array(
    'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
    'cache_level' => 'static',
);
$schema['products']['content']['items']['fillings']['same_brand_products'] = array(
    'params' => array (
        'items_function' => 'fn_get_same_brand_products',
        'request' => array (
            'same_brand_pid' => '%PRODUCT_ID%',
            'exclude_pid' => '%PRODUCT_ID%'
        ),
        'limit' => 10,
    ),
    'disable_cache' => true
);
$schema['pages']['content']['items']['fillings']['dynamic_content'] = array (
    'params' => array (
        'status' => 'A',
        'request' => array (
            'parent_id' => '%PAGE_ID%'
        ),
    ),
);
$schema['products']['settings']['all_items_url'] = array (
    'type' => 'input',
    'default_value' => ''
);
$schema['products']['content']['items']['fillings']['allcourt_shoes'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'shoes_surface' => 'allcourt',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['shoes_for_clay'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'shoes_surface' => 'clay',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['shoes_for_grass'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'shoes_surface' => 'grass',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['power_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'power',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['club_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'club',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['pro_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'pro',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['heavy_head_light_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'heavy_head_light',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['light_head_heavy_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'light_head_heavy',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['stiff_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'stiff',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['soft_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'soft',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['regular_head_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'regular_head',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['regular_length_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'regular_length',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['closed_pattern_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'closed_pattern',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['open_pattern_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'open_pattern',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['kids_17_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'kids_17',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['kids_19_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'kids_19',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['kids_21_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'kids_21',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['kids_23_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'kids_23',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['kids_25_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'kids_25',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['kids_26_rackets'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'rackets_type' => 'kids_26',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['natural_gut_strings'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'strings_type' => 'natural_gut',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['nylon_strings'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'strings_type' => 'nylon',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['polyester_strings'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'strings_type' => 'polyester',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['hybrid_strings'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'strings_type' => 'hybrid',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['monofil_strings'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'strings_type' => 'monofil',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['multifil_strings'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'strings_type' => 'multifil',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['textured_strings'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'strings_type' => 'textured',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['synthetic_gut_strings'] = array(
    'params' => array (
        'sort_by' => 'popularity',
        'sort_order' => 'desc',
        'strings_type' => 'synthetic_gut',
        'shuffle' => 'Y',
        'limit' => 10,
    ),
    'cache' => array(
        'update_handlers' => array ('products', 'product_descriptions', 'product_prices', 'products_categories'),
        'cache_level' => 'static',
        'no_object' => true
    )
);
$schema['products']['content']['items']['fillings']['cross_sales'] = array(
    'params' => array (
        'items_function' => 'fn_get_checkout_cross_sales',
        'limit' => 10,
    ),
    'disable_cache' => true,
);
$schema['products']['content']['items']['fillings']['discounted_products'] = array(
    'params' => array (
        'items_function' => 'fn_get_discounted_products',
        'subcats' => 'Y',
        'skip_bulk_modifier' => true
    )
);
$schema['products']['content']['items']['fillings']['product_cross_sales'] = array(
    'params' => array (
        'items_function' => 'fn_get_product_cross_sales',
    ),
    'cache' => false
);
$schema['testimonials']['templates']['addons/discussion/blocks/homepage_testimonials.tpl'] = array();

$schema['news'] = array (
    'templates' => 'blocks/news_feed',
    'wrappers' => 'blocks/wrappers',
    'content' => array (
        'items' => array (
            'type' => 'enum',
            'object' => 'news_feed',
            'items_function' => 'fn_get_news_feed',
            'remove_indent' => true,
//             'hide_label' => true,
            'fillings' => array (
                'news_feed' => array (
                    'settings' => array (
                        'rss_feed_link' => array (
                            'type' => 'input_long',
                            'default_value' => 'http://www.championat.com/xml/rss_tennis-article.xml'
                        ),
                        'number_of_news' => array (
                            'type' => 'input',
                            'default_value' => '15'
                        )
                    )
                ),
                'player_news_feed' => array (
                    'params' => array (
                        'request' => array (
                            'player_id' => '%PLAYER_ID%'
                        )
                    )
                ),
            )
        )
    )
);
$schema['menu']['content']['items']['function'] = array('fn_get_menu_items_th');
$schema['menu']['cache'] = array(
    'update_handlers' => array ('categories', 'players'),
    'cache_level' => 'static',
    'no_object' => true
);

$schema['product_filters']['templates']['addons/development/blocks/product_filters/static.tpl'] = array (
    'fillings' => array ('manually')
);

return $schema;