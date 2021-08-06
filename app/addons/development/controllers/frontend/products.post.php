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
use Tygh\BlockManager\Block;
use Tygh\BlockManager\RenderManager;
use Tygh\BlockManager\SchemesManager;

if (!defined('BOOTSTRAP')) { die('Access denied'); }


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'ajax_search') {
        $params = array(
            'match' => 'all',
            'subcats' => 'Y',
            'pname' => 'Y',
            'pfull' => 'Y',
            'sort_by' => 'popularity',
            'sort_order' => 'desc',
            'q' => $_REQUEST['q']
        );
        list($products,) = fn_get_products($params);
        fn_gather_additional_products_data($products, array(
            'get_icon' => false,
            'get_detailed' => true,
            'get_additional' => false,
            'get_options' => false,
            'get_discounts' => false,
            'get_features' => false,
            'get_title_features' => false,
            'allow_duplication' => false
        ));
        if (!empty($products)) {
            $pieces = fn_explode(' ', trim($_REQUEST['q']));
            foreach ($products as $i => $product) {
                foreach ($pieces as $piece) {
                    if (strlen($piece) == 0) {
                        continue;
                    }
                    $products[$i]['product'] = preg_replace('/' . preg_quote($piece) . '/iu', "<b>$0</b>", $products[$i]['product']);
                }
            }
        }
        Registry::get('view')->assign('results_count', count($products));
        $products = array_slice($products, 0, Registry::get('addons.development.ajax_search_results_number'));
        Registry::get('view')->assign('results', $products);
        Registry::get('view')->display('common/search.tpl');
        exit;
    }
}

if ($mode == 'view') {

    $product = Registry::get('view')->gettemplatevars('product');
    $currencies = Registry::get('currencies');
    $ttl_prc = fn_format_price($product['price']);
    if (__('product_page_title_' . $product['category_type']) != '_product_page_title_' . strtolower($product['category_type']) && __('product_page_title_' . $product['category_type']) != '') {
        Registry::get('view')->assign('page_title', $product['product'] . ' ' . $product['product_code'] . ' – ' . __('product_page_title_' . $product['category_type'], array('[price]' => $ttl_prc . ' ' . strip_tags($currencies[CART_PRIMARY_CURRENCY]['symbol']))));
    } else {
        Registry::get('view')->assign('page_title', $product['product'] . ' ' . $product['product_code'] . ' – ' . __('product_page_title', array('[price]' => $ttl_prc . ' ' . strip_tags($currencies[CART_PRIMARY_CURRENCY]['symbol']))));
    }
    $features = array();
    if (!empty($product['product_features'])) {
        foreach ($product['product_features'] as $i => $f_data) {
            if ($f_data['feature_type'] == 'M' && !empty($f_data['variants'])) {
                foreach ($f_data['variants'] as $j => $var) {
                    if (!empty($var['selected'])) {
                        $features[$f_data['feature_id']]['variants'][] = array(
                            'variant_id' => $var['variant_id'],
                            'variant_name' => $var['variant']
                        );
                    }
                }
            } elseif (!empty($f_data['variant_id'])) {
                $features[$f_data['feature_id']] = array(
                    'variant_id' => $f_data['variant_id'],
                    'variant_name' => $f_data['variants'][$f_data['variant_id']]['variant']
                );
            } else {
                $features[$f_data['feature_id']] = array(
                    'value' => $f_data['value']
                );
            }
        }
        $gender = '';
        $brand_size_chart = false;
        if (!empty($product['product_features'][CLOTHES_GENDER_FEATURE_ID])) {
            $variant_id = $product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variant_id'];
            if ($variant_id == C_GENDER_M_FV_ID) {
                $gender = 'mens';
            } elseif ($variant_id == C_GENDER_W_FV_ID) {
                $gender = 'womens';
            }
            if (!empty($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variants'][$variant_id]['variant_code'])) {
                fn_set_store_gender_mode($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variants'][$variant_id]['variant_code']);
            }
        } elseif (!empty($product['product_features'][SHOES_GENDER_FEATURE_ID])) {
            $variant_id = $product['product_features'][SHOES_GENDER_FEATURE_ID]['variant_id'];
            if ($variant_id == S_GENDER_M_FV_ID) {
                $gender = 'mens';
            } elseif ($variant_id == S_GENDER_W_FV_ID) {
                $gender = 'womens';
            }
            if (!empty($product['product_features'][SHOES_GENDER_FEATURE_ID]['variants'][$variant_id]['variant_code'])) {
                fn_set_store_gender_mode($product['product_features'][SHOES_GENDER_FEATURE_ID]['variants'][$variant_id]['variant_code']);
            }
        } elseif (!empty($product['product_features'][TYPE_FEATURE_ID])) {
            $variant_id = $product['product_features'][TYPE_FEATURE_ID]['variant_id'];
            if (!empty($product['product_features'][TYPE_FEATURE_ID]['variants'][$variant_id]['variant_code'])) {
                fn_set_store_gender_mode($product['product_features'][TYPE_FEATURE_ID]['variants'][$variant_id]['variant_code']);
            }
        }
//         if (!empty($product['header_features'][BRAND_FEATURE_ID])) {
//             $cat_type = '';
//             $_SESSION['product_brand'] = $brand_id = $product['header_features'][BRAND_FEATURE_ID]['variant_id'];
//             if ($product['category_type'] == 'A') {
//                 $cat_type = 'clothes';
//             } elseif ($product['category_type'] == 'S') {
//                 $cat_type = 'shoes';
//             }
//             if (!empty($product['header_features'][BRAND_FEATURE_ID]['variants'][$brand_id][$gender . '_' . $cat_type . '_size_chart'])) {
//                 $brand_size_chart = true;
//                 $title = $product['header_features'][BRAND_FEATURE_ID]['variants'][$brand_id]['variant'];JUNIOR_RACKET_FV_ID
//             }
//         }
    }
//     $_tabs = Registry::get('navigation.tabs');
//     if (!empty($product['size_chart'])) {
//         if ($brand_size_chart) {
//             $_tabs['size_chart']['title'] .= ' ' . $title;
//         }
//     }
//     Registry::set('navigation.tabs', $_tabs);
    if (!empty($product['header_features'])) {
        foreach ($product['header_features'] as $i => $f_data) {
            if (!empty($f_data['variant_id'])) {
                $features[$f_data['feature_id']] = array(
                    'variant_id' => $f_data['variant_id'],
                    'variant_name' => $f_data['variants'][$f_data['variant_id']]['variant']
                );
            } else {
                $features[$f_data['feature_id']] = array(
                    'value' => $f_data['value']
                );
            }
        }
    }
    
    $_SESSION['product_category'] = $product['main_category'];
    $_SESSION['main_product_category'] = $product['category_main_id'];
    $_SESSION['product_features'] = $features;
    $_SESSION['category_type'] = $product['category_type'];
    
    if ($product['category_main_id'] == RACKETS_CATEGORY_ID) {
        Registry::get('view')->assign('show_racket_finder', true);
    }

    $blocks = Block::instance()->getList(
        array('?:bm_blocks.*', '?:bm_blocks_descriptions.*'),
        array(PRODUCT_BLOCK_TABS_GRID_ID),
        array(),
        '',
        " AND ?:bm_snapping.status = 'A' "
    );
    $block_tabs = array();
    if (!empty($blocks[PRODUCT_BLOCK_TABS_GRID_ID])) {
        shuffle($blocks[PRODUCT_BLOCK_TABS_GRID_ID]);
        foreach ($blocks[PRODUCT_BLOCK_TABS_GRID_ID] as $i => $block_data) {
            if ($block_data['properties']['template'] == 'addons/development/blocks/products/th_products_block.tpl') {
                $block_tabs['tabs']['block_tab_' . $block_data['block_id']] = array(
                    'title' => $block_data['name'],
                    'js' => true
                );
            }
        }
    }
    Registry::get('view')->assign('block_tabs', $block_tabs);

    fn_get_product_review_discount($product);
    if ($product['category_type'] == 'R' && in_array($product['product_features'][TYPE_FEATURE_ID]['variant_id'], array(PRO_RACKET_FV_ID, CLUB_RACKET_FV_ID, POWER_RACKET_FV_ID, JUNIOR_RACKET_FV_ID))) {
        if (!empty($product['product_features'][R_STRINGS_FEATURE_ID]['value']) && $product['product_features'][R_STRINGS_FEATURE_ID]['value'] == 'Y') {
            $product['customization_type'] = 'S';
        } else {
            $product['customization_type'] = 'U';
        }
    }
    Registry::get('view')->assign('product', $product);
    
} elseif ($mode == 'sale') {

    $params = $_REQUEST;
    $params['subcats'] = 'Y';
    list($products, $search) = fn_get_discounted_products($params, Registry::get('settings.Appearance.products_per_page'));

    $category_ids = $categories = array();
    if (!empty($products)) {
        foreach ($products as $i => $prod) {
            $category_ids[] = $prod['type_id'];
            if (!empty($search['cd']) && $search['cd'] != $prod['type_id']) {
                unset($products[$i]);
            }
        }
        if (!empty($category_ids)) {
            $categories = db_get_array("SELECT a.category_id, b.category FROM ?:categories AS a LEFT JOIN ?:category_descriptions AS b ON b.category_id = a.category_id AND b.lang_code = ?s WHERE a.category_id IN (?n) ORDER BY a.position ASC", CART_LANGUAGE, array_unique($category_ids));
        }
    }

    $search['items_per_page'] = Registry::get('settings.Appearance.products_per_page');
    $search['total_items'] = count($products);
    $page = intval($search['page']);
    if (empty($page)) {
        $page  = 1;
    }
    $products = array_slice($products, $search['items_per_page'] * ($page - 1), $search['items_per_page']);
    
    $selected_layout = fn_get_products_layout($search);

    fn_add_breadcrumb(__("sale_products"));
    Registry::get('view')->assign('categories', $categories);
    Registry::get('view')->assign('products', $products);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('selected_layout', $selected_layout);
}