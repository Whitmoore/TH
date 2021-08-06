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

$schema = array(
    'c' => array(
        'tree' => true,
        'path_function' => function($object_id) {

            static $cache = array();

            if (!isset($cache[$object_id])) {
                $path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $object_id);
                $apath = explode('/', $path);
                array_pop($apath);
                $cache[$object_id] = implode('/', $apath);
            }

            return $cache[$object_id];
        },
        'parent_type' => 'c',

        'name' => 'category',
        'picker' => 'pickers/categories/picker.tpl',
        'picker_params' => array(
            'multiple' => false,
            'use_keys' => 'N'
        ),

        'table' => '?:category_descriptions',
        'description' => 'category',
        'dispatch' => 'categories.view',
        'item' => 'category_id',
        'condition' => '',
        'not_shared' => true,

        'tree_options' => array('category', 'category_nohtml'),
        'html_options' => array('file', 'category'),
        'pager' => true,
        'option' => 'seo_category_type',

        'indexed_pages' => array(
            'categories.catalog' => array(),
            'categories.view' => array(
                'index' => array('category_id'),
//                 'noindex' => array('features_hash')
            )
        )

    ), // category (tree)
    'p' => array(
        'tree' => true,
        'path_function' => function($object_id) {

            static $cache = array();
            if (!isset($cache[$object_id])) {  
                $path = db_get_hash_single_array("SELECT c.id_path, p.link_type FROM ?:categories as c LEFT JOIN ?:products_categories as p ON p.category_id = c.category_id WHERE p.product_id = ?i ?p", array('link_type', 'id_path'), $object_id, fn_get_seo_company_condition('c.company_id'));
                $cache[$object_id] = !empty($path['M']) ? $path['M'] : $path['A'];
            }

            return $cache[$object_id];
        },
        'parent_type' => 'c',

        'name' => 'product',
        'picker' => 'pickers/products/picker.tpl',
        'picker_params' => array(
            'type' => 'single',
            'view_mode' => 'button'
        ),


        'table' => '?:product_descriptions',
        'description' => 'product',
        'dispatch' => 'products.view',
        'item' => 'product_id',
        'condition' => '',
        'not_shared' => true,

        'tree_options' => array('product_category_nohtml', 'product_category'),
        'html_options' => array('product_category', 'product_file'),
        'option' => 'seo_product_type',

        'indexed_pages' => array(
            'products.view' => array(
                'index' => array('product_id')
            ),
        ),

    ), // product  (tree)
    'a' => array(
        'tree' => true,
        'path_function' => function($object_id) {

            static $cache = array();
            if (!isset($cache[$object_id])) {
                $path = db_get_field("SELECT id_path FROM ?:pages WHERE page_id = ?i", $object_id);
                $apath = explode('/', $path);
                array_pop($apath);
                $cache[$object_id] = implode('/', $apath);
            }

            return $cache[$object_id];
        },
        'parent_type' => 'a',

        'name' => 'page',
        'picker' => 'pickers/pages/picker.tpl',
        'picker_params' => array(
            'multiple' => false,
            'use_keys' => 'N',
        ),

        'table' => '?:page_descriptions',
        'description' => 'page',
        'dispatch' => 'pages.view',
        'item' => 'page_id',
        'condition' => '',

        'tree_options' => array('page', 'page_nohtml'),
        'html_options' => array('file', 'page'),
        'pager' => true,
        'option' => 'seo_page_type',

        'indexed_pages' => array(
            'pages.view' => array(
                'index' => array('page_id')
            ),
        )

    ), // page     (tree)
    'e' => array(
        'table' => '?:product_feature_variant_descriptions',
        'description' => 'variant',
        'dispatch' => 'product_features.view',
        'item' => 'variant_id',
        'condition' => "AND ?:product_features.seo_variants = 'Y'",
        'join' => 'LEFT JOIN ?:product_feature_variants ON ?:product_feature_variants.variant_id = ?:product_feature_variant_descriptions.variant_id LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_feature_variants.feature_id',
        'is_particle' => true,

        'name' => 'feature',

        'html_options' => array('file'),
        'option' => 'seo_other_type',

        'indexed_pages' => array(
            'product_features.view' => array(
                'index' => array('variant_id'),
                'noindex' => array('features_hash'),
            ),
        ),
        'particle_options' => array(
            'item' => 'features_hash',
            'value_prefix' => 'V',
        )
    ), // feature  (plain)
    's' => array(
        'table' => '?:seo_names',
        'description' => 'name',
        'dispatch' => '',
        'item' => 'object_id',
        'condition' => fn_get_seo_company_condition('?:seo_names.company_id'),
        'not_shared' => true,

        'name' => 'custom',

        'html_options' => array('file'),
        'option' => 'seo_other_type',

        'indexed_pages' => array(
            'index.index' => array(),
            'sitemap.view' => array(),            
        )
    ), // custom    (plain)
    // [tennishouse]
    'l' => array(
        'table' => '?:players',
        'description' => 'player_en',
        'dispatch' => 'players.view',
        'item' => 'player_id',
        'condition' => '',
        'join' => '',
        'skip_lang_condition' => true,

        'name' => 'player',

        'html_options' => array('file', 'players'),
        'option' => 'seo_players_type',

        'tree' => true,
        'tree_options' => array('players_nohtml'),
        'path_function' => function($object_id) {
            return '00';
        },
        'parent_type' => 'h',
        
        'indexed_pages' => array(
            'players.view' => array(
                'index' => array('player_id'),
            ),
            'players.list' => array()
        )
    ), // player  (plain)
    'm' => array(
        'table' => '?:promotion_descriptions',
        'description' => 'name',
        'dispatch' => 'promotions.view',
        'item' => 'promotion_id',
        'condition' => '',
        'skip_lang_condition' => true,

        'name' => 'promotion',

        'html_options' => array('file', 'promotions'),
        'option' => 'seo_promotions_type',

        'tree' => true,
        'tree_options' => array('promotions_nohtml'),
        'path_function' => function($object_id) {
            return '00';
        },
        'parent_type' => 'x',
        
        'indexed_pages' => array(
            'promotions.view' => array(
                'index' => array('promotion_id'),
            ),
            'promotions.list' => array()
        )
    ), // promotion  (plain)
//     'f' => array(
//         'tree' => true,
//         'table' => '?:product_feature_variant_descriptions',
//         'description' => 'variant',
//         'item' => 'features_hash',
//         'condition' => '',
//         'is_particle' => true,
//         'name' => 'filter',
// 
//         'tree_options' => array('filters_nohtml'),
//         'html_options' => array('file'),
//         'option' => 'seo_filters_type',
// 
//         'tree_options' => array(
//             'item' => 'features_hash',
//             'value_prefix' => 'V',
//         )
// //         'indexed_pages' => array(
// //             'product_features.view' => array(
// //                 'index' => array('variant_id'),
// //                 'noindex' => array('features_hash'),
// //             ),
// //         )
//     ), // filter  (plain)
    // [tennishouse]
);

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['m'] = array(
        'table' => '?:companies',
        'description' => 'company',
        'dispatch' => 'companies.view',
        'item' => 'company_id',
        'condition' => '',
        'skip_lang_condition' => true,

        'name' => 'company',
        'html_options' => array('file'),
        'option' => 'seo_other_type',

        'indexed_pages' => array(
            'companies.view' => array(
                'index' => array('company_id')
            ),
        )
    );
}

return $schema;
