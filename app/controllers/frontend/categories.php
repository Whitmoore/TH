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
use Tygh\BlockManager\SchemesManager;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_REQUEST['category_id'] = empty($_REQUEST['category_id']) ? 0 : $_REQUEST['category_id'];

if ($mode == 'catalog') {
    fn_add_breadcrumb(__('catalog'));

    $root_categories = fn_get_subcategories(0);

    foreach ($root_categories as $k => $v) {
        $root_categories[$k]['main_pair'] = fn_get_image_pairs($v['category_id'], 'category', 'M');
    }

    Registry::get('view')->assign('root_categories', $root_categories);

} elseif ($mode == 'view') {

    if (!empty($_REQUEST['item_ids']) && $_REQUEST['category_id'] == '254' && $_REQUEST['item_ids'] == '1312') {
        return array(CONTROLLER_STATUS_REDIRECT, "categories.view?category_id=254&item_ids=1312,1313,1314,1315,1316,1317,1318,1319");
    }
    $_statuses = array('A', 'H');
    $_condition = fn_get_localizations_condition('localization', true);
    $preview = fn_is_preview_action($auth, $_REQUEST);

    if (!$preview) {
        $_condition .= ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'usergroup_ids', true) . ')';
        $_condition .= db_quote(' AND status IN (?a)', $_statuses);
    }

    if (fn_allowed_for('ULTIMATE')) {
        $_condition .= fn_get_company_condition('?:categories.company_id');
    }

    $category_exists = db_get_field(
        "SELECT category_id FROM ?:categories WHERE category_id = ?i ?p",
        $_REQUEST['category_id'],
        $_condition
    );

    if (!empty($category_exists)) {
        // [tennishouse]

        if (!empty($_REQUEST['features_hash'])) {
            $_REQUEST['features_hash'] = fn_correct_features_hash($_REQUEST['features_hash']);
        }

        // Save current url to session for 'Continue shopping' button
        $_SESSION['continue_url'] = "categories.view?category_id=$_REQUEST[category_id]";

        // Save current category id to session
        $_SESSION['current_category_id'] = $_SESSION['breadcrumb_category_id'] = $_REQUEST['category_id'];

        // Get subcategories list for current category
        $subcategories = fn_get_subcategories($_REQUEST['category_id']);
        Registry::get('view')->assign('subcategories', $subcategories);
        
        // Get full data for current category
        $category_data = fn_get_category_data($_REQUEST['category_id'], CART_LANGUAGE, '*', true, false, $preview);
        
        $category_parent_ids = fn_explode('/', $category_data['id_path']);
        $main_parent_id = reset($category_parent_ids);
        array_pop($category_parent_ids);

        $subtabs_string = $tab_string = '';
//         if (empty($subcategories) || !empty($category_data['parent_id'])) {
            $params = $_REQUEST;

            if (!empty($_REQUEST['items_per_page'])) {
                $_SESSION['items_per_page'] = $_REQUEST['items_per_page'];
            } elseif (!empty($_SESSION['items_per_page'])) {
                $params['items_per_page'] = $_SESSION['items_per_page'];
            } elseif (!empty($category_data['products_per_page'])) {
                $params['items_per_page'] = $category_data['products_per_page'];
            }
            
            $params['cid'] = $_REQUEST['category_id'];
            $params['extend'] = array('categories', 'description');
            $params['subcats'] = '';
            if (!empty($category_data['tabs_categorization'])) {
                $params['tabs_categorization'] = $category_data['tabs_categorization'];
            }
            if (!empty($category_data['subtabs_categorization'])) {
                $params['subtabs_categorization'] = $category_data['subtabs_categorization'];
            }
            if (!empty($category_data['sections_categorization'])) {
                $params['sections_categorization'] = $category_data['sections_categorization'];
            }
            if (Registry::get('settings.General.show_products_from_subcategories') == 'Y') {
                $params['subcats'] = 'Y';
            }
            if (!empty($category_data['products_sorting'])) {
                $sorting = explode('-', $category_data['products_sorting']);
                if (is_array($sorting) && count($sorting) == 2) {
                    $params['sort_by'] = array_shift($sorting);
                    $params['sort_order'] = array_shift($sorting);
                }
            }
            if (!empty($category_data['code'])) {
                fn_set_store_gender_mode($category_data['code']);
            }
            list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);
            if (isset($search['page']) && ($search['page'] > 1) && empty($products)) {
                return array(CONTROLLER_STATUS_NO_PAGE);
            }
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
                    'av_ids' => (!empty($search['av_ids'])) ? $search['av_ids'] : array()
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
                    'av_ids' => (!empty($search['av_ids'])) ? $search['av_ids'] : array()
                ));
            }
            $_params = array(
                'category_id' => $category_data['category_id']
            );
            $category_data['feature_seos'] = fn_get_feature_seos($_params);
            
            if (!empty($category_data['feature_seos']['data']) && !empty($search['av_ids'])) {
                $match = array();
                foreach ($category_data['feature_seos']['data'] as $item_id => $f_combination) {
                    $_match = true;
                    if (!empty($f_combination['features'])) {
                        foreach ($f_combination['features'] as $f_id => $v_id) {
                            if (empty($search['av_ids'][$f_id][$v_id])) {
                                $_match = false;
                                break;
                            }
                        }
                    }
                    if (!empty($_match)) {
                        $match[$item_id] = count($f_combination['features']);
                    }
                }
                if (!empty($match)) {
                    arsort($match);
                    $comb_id = key($match);
                    $category_data['page_title'] = $category_data['feature_title'] = $category_data['feature_seos']['data'][$comb_id]['page_title'];
                    $category_data['full_description'] = $category_data['feature_seos']['data'][$comb_id]['full_description'];
                    $category_data['meta_description'] = $category_data['feature_seos']['data'][$comb_id]['meta_description'];
                    $category_data['meta_keywords'] = $category_data['feature_seos']['data'][$comb_id]['meta_keywords'];
                    $parsed_ranges = fn_parse_features_hash($_REQUEST['features_hash'], false);
                    $features_hash = '';
                    foreach ($parsed_ranges[2] as $i => $range) {
                        if (in_array($range, $category_data['feature_seos']['data'][$comb_id]['features'])) {
                            $features_hash .= (!empty($features_hash) ? '.' : '') . $parsed_ranges[0][$i];
                        }
                    }
                    $category_data['canonical'] = $category_data['canonical'] . '&features_hash=' . $features_hash;
                }
            }
            if (!empty($products)) {
            
                $tb_feature = $stb_feature = array();
                $tb_feature_selected = false;
                if (!empty($category_data['tabs_categorization']) || !empty($category_data['subtabs_categorization'])) {
                    $dynamic_object = array(
                        'object_id' => $category_data['category_id'],
                        'object_type' => 'categories',
                    );
                    $block_data = Block::instance()->getList(
                        array('?:bm_snapping.*','?:bm_blocks.*', '?:bm_blocks_descriptions.*'),
                        FILTER_SNAPPING_ID,
                        $dynamic_object
                    );
                    if (!empty($block_data[FILTER_SNAPPING_ID][FILTER_BLOCK_ID])) {
                        $block_scheme = SchemesManager::getBlockScheme($block_data[FILTER_SNAPPING_ID][FILTER_BLOCK_ID]['type'], array());
                        $items = Block::instance()->getItems('items', $block_data[FILTER_SNAPPING_ID][FILTER_BLOCK_ID], $block_scheme);
                        
                        if (!empty($items)) {
                            foreach ($items as $ft_id => $f_data) {
                                if (!empty($category_data['tabs_categorization']) && $f_data['feature_id'] == $category_data['tabs_categorization']) {
                                    $tb_feature = $f_data;
                                    if (!empty($f_data['selected_ranges'])) {
                                        foreach ($f_data['selected_ranges'] as $fl_id => $sf_data) {
                                            $tb_feature['ranges'][$fl_id]['is_selected'] = true;
                                            $tb_feature_selected = $fl_id;
                                        }
                                    }
                                }
                                if (!empty($category_data['subtabs_categorization']) && $f_data['feature_id'] == $category_data['subtabs_categorization']) {
                                    $stb_feature = $f_data;
                                    if (!empty($f_data['selected_ranges'])) {
                                        foreach ($f_data['selected_ranges'] as $fl_id => $sf_data) {
                                            $stb_feature['ranges'][$fl_id]['is_selected'] = true;
                                        }
                                    }
                                }
                            }
                            if (!empty($category_data['subtabs_categorization']) && !empty($stb_feature['ranges'])) {
                                $variant_ids = array();
                                foreach ($stb_feature['ranges'] as $variant) {
                                    $variant_ids[] = $variant['range_id'];
                                }
                                $image_pairs = fn_get_image_pairs($variant_ids, 'feature_variant', 'V', true, true);
                                foreach ($stb_feature['ranges'] as &$variant) {
                                    $variant['image_pair'] = array_pop($image_pairs[$variant['range_id']]);
                                }
                            }
                        }
                    }
                }
                Registry::get('view')->assign('tb_feature_selected', $tb_feature_selected);
                Registry::get('view')->assign('stb_feature', $stb_feature);
                Registry::get('view')->assign('tb_feature', $tb_feature);
//                 Registry::get('view')->assign('active_tab', !empty($params['tc_id']) ? $params['tc_id'] : false);

//                 if (empty($category_data['brand']) || $category_data['brand']['feature_id'] != $category_data['tabs_categorization']) {
//                 
//                     $hide_tabs = array(KIDS_RACKET_FV_ID);
//                     $tb_feature = array(
//                         'variants' => array(),
//                     );
//                     $tmp_tabs = $all_tab = array();
//                     if (!empty($category_data['tabs_categorization'])) {
//                         $tb_feature = fn_get_product_feature_data($category_data['tabs_categorization'], true);
//                     }
//                     if (empty($tb_feature['variants']) || !empty($category_data['all_items_tab'])) {
//                         $lang_var = __("all_items_tab_" . $category_data['type']);
//                         if (empty($lang_var) || $lang_var[0] == '_') {
//                             $lang_var = __("all");
//                         }
//                         $all_tab['ALL'] = array(
//                             'variant' => $lang_var,
//                             'variant_code' => 'ALL',
//                             'variant_id' => 'ALL'
//                         );
//                     }
//                     if (!empty($tb_feature['variants'])) {
//                         $tmp_tabs = $tab_ids = array();
//                         foreach ($tb_feature['variants'] as $key => $vr_data) {
//                             if (!empty($vr_data['variant_code'])) {
//                                 $tab_ids[$key][] = $vr_data['variant_code'];
//                                 if (!in_array($vr_data['variant_code'], array_keys($tmp_tabs))) {
//                                     $lang_var = __("tab_groups_" . $tb_feature['feature_code'] . '_' . $vr_data['variant_code']);
//                                     if (empty($lang_var) || $lang_var[0] == '_') {
//                                         $lang_var = $vr_data['variant'];
//                                     }
//                                     $new_var = array(
//                                         'variant' => $lang_var,
//                                         'variant_code' => $vr_data['variant_code'],
//                                         'variant_id' => $vr_data['variant_code']
//                                     );
//                                     $tmp_tabs[$vr_data['variant_code']] = $new_var;
//                                 }
//                             }
//                             if (!in_array($key, $hide_tabs) && !empty($category_data['extended_tabs_categorization'])) {
//                                 $tab_ids[$key][] = $key;
//                             }
//                         }
//                         $_tb_feature_vars = $tb_feature['variants'];
//                         $tb_feature['variants'] = $all_tab + $tmp_tabs + $_tb_feature_vars;
//                         $tb_feature['variants']['DSC'] = array(
//                             'variant' => __("discounts"),
// //                             'variant_code' => 'DSC',
// //                             'variant_id' => 'DSC'
//                         );
//                         $tabs_categorization = $discounts = array();
//                         foreach ($products as $i => $product) {
//                             if (!empty($tb_feature['variants']['ALL'])) {
//                                 $tabs_categorization['ALL'][] = $product;
//                             }
//                             if (!empty($product['tabs_categorization']) && !empty($tab_ids[$product['tabs_categorization']])) {
//                                 foreach ($tab_ids[$product['tabs_categorization']] as $i => $tab_id) {
//                                     $tabs_categorization[$tab_id][] = $product;
//                                 }
//                             } else {
//                                 if (!empty($category_data['tabs_categorization'])) {
//                                     $tabs_categorization['other'][] = $product;
//                                 }
//                             }
//                             if ($product['base_price'] > $product['price'] || $product['list_price'] > $product['price']) {
//                                 $discounts[] = $product;
//                             }
//                         }
//                         if (!empty($discounts)) {
//                             $tabs_categorization['DSC'] = $discounts;
//                         }
//                         foreach ($tb_feature['variants'] as $key => $vr_data) {
//                             if (empty($tabs_categorization[$key])) {
//                                 unset($tb_feature['variants'][$key]);
//                             }
//                         }
//                         if (!empty($tabs_categorization['other'])) {
//                             $tb_feature['variants']['other'] = array('variant' => __("other"));
//                         }
// 
//                         if (count($tb_feature['variants']) == 1) {
//                             $keys = array_keys($tb_feature['variants']);
//                             if (reset($keys) == 'all') {
//                                 unset($tb_feature['variants']['all']);
//                             }
//                         }
//                         if (!empty($_REQUEST['tc_id'])) {
//                             $_SESSION['tc_id'][$main_parent_id] = $params['tc_id'] = $_REQUEST['tc_id'];
//                             // Store gender mode
//                             if (!empty($tb_feature['variants'][$_REQUEST['tc_id']]['variant_code'])) {
//                                 fn_set_store_gender_mode($tb_feature['variants'][$_REQUEST['tc_id']]['variant_code']);
//                             }
//                         }
//                         if (empty($params['tc_id']) && empty($category_data['all_items_tab'])) {
//                             $fit_gender = array();
//                             foreach ($tb_feature['variants'] as $j => $vt_data) {
//                                 if (!empty($vt_data['variant_code']) && fn_gender_match($vt_data['variant_code']) && !empty($tabs_categorization[$vt_data['variant_id']])) {
//                                     $fit_gender[] = $vt_data['variant_id'];
//                                 }
//                             }
//                             if (!empty($fit_gender)) {
//                                 if (count($fit_gender) > 1 && !empty($_SESSION['tc_id'][$main_parent_id]) && in_array($_SESSION['tc_id'][$main_parent_id], $fit_gender)) {
//                                     $selected_tab = $_SESSION['tc_id'][$main_parent_id];
//                                 } else {
//                                     $selected_tab = reset($fit_gender);
//                                 }
//                                 $_REQUEST['tc_id'] = $params['tc_id'] = $_SESSION['tc_id'][$main_parent_id] = $selected_tab;
//                                 fn_set_store_gender_mode($selected_tab);
//                             }
//                             if (empty($params['tc_id'])) {
//                                 if (!empty($_SESSION['tc_id'][$main_parent_id]) && !empty($tabs_categorization[$_SESSION['tc_id'][$main_parent_id]])) {
//                                     $_REQUEST['tc_id'] = $params['tc_id'] = $_SESSION['tc_id'][$main_parent_id];
//                                 } elseif (!empty($tb_feature['variants'])) {
//                                     $keys = array_keys($tb_feature['variants']);
//                                     $_REQUEST['tc_id'] = $params['tc_id'] = $_SESSION['tc_id'][$main_parent_id] = reset($keys);
//                                 }
//                                 if (!empty($tb_feature['variants'][$params['tc_id']]['variant_code'])) {
//                                     fn_set_store_gender_mode($tb_feature['variants'][$params['tc_id']]['variant_code']);
//                                 }
//                             }
//                         }
//                         if (!empty($params['tc_id']) && !empty($tabs_categorization[$params['tc_id']])) {
//                             $products = $tabs_categorization[$params['tc_id']];
//                             $tab_string = $tb_feature['variants'][$params['tc_id']]['variant'];
//                         }
//                         Registry::get('view')->assign('tb_feature', $tb_feature);
//                         Registry::get('view')->assign('active_tab', !empty($params['tc_id']) ? $params['tc_id'] : false);
//                         //Registry::get('view')->assign('tab_ids', $tab_ids);
//                     }
//                 }

//                 if (!empty($category_data['subtabs_categorization']) && (empty($category_data['brand']) || $category_data['brand']['feature_id'] != $category_data['subtabs_categorization'])) {
//                     $stb_feature = fn_get_product_feature_data($category_data['subtabs_categorization'], true, true);
//                     if (!empty($stb_feature['variants'])) {
//                         $subtabs_categorization = array();
//                         foreach ($products as $i => $product) {
//                             if (!empty($product['subtabs_categorization'])) {
//                                 $subtabs_categorization[$product['subtabs_categorization']][] = $product;
//                             } else {
//                                 $subtabs_categorization['other'][] = $product;
//                             }
//                         }
//                         $sb_array = array();
//                         foreach ($stb_feature['variants'] as $key => $vr_data) {
//                             if (empty($subtabs_categorization[$key])) {
//                                 unset($stb_feature['variants'][$key]);
//                             } else {
//                                 $sb_array[] = $stb_feature['variants'][$key]['variant'];
//                             }
//                         }
//                         if (!empty($sb_array)) {
//                             $subtabs_string = implode(', ', $sb_array);
//                         }
//                         if (!empty($subtabs_categorization['other'])) {
//                             $stb_feature['variants']['other'] = array('variant' => __("other"));
//                         }
//                         if (!empty($_REQUEST['stc_id'])) {
//                             if ($_REQUEST['stc_id'] == 'all') {
//                                 unset($_SESSION['stc_id'][$main_parent_id]);
//                             } else {
//                                 $_SESSION['stc_id'][$main_parent_id] = $_REQUEST['stc_id'];
//                             }
//                         }
//                         if (empty($params['stc_id'])) {
//                             if (!empty($_SESSION['stc_id'][$main_parent_id]) && !empty($subtabs_categorization[$_SESSION['stc_id'][$main_parent_id]])) {
//                                 $_REQUEST['stc_id'] = $params['stc_id'] = $_SESSION['stc_id'][$main_parent_id];
// //                             } elseif (!empty($stb_feature['variants'])) {
// //                                 $keys = array_keys($stb_feature['variants']);
// //                                 $_REQUEST['stc_id'] = $params['stc_id'] = $_SESSION['stc_id'][$main_parent_id] = reset($keys);
//                             } else {
//                                 $params['stc_id'] = 'all';
//                             }
//                         }
//                         if (!empty($params['stc_id']) && !empty($subtabs_categorization[$params['stc_id']])) {
//                             $products = $subtabs_categorization[$params['stc_id']];
//                         }
//             
//                         Registry::get('view')->assign('stb_feature', $stb_feature);
//                         Registry::get('view')->assign('active_subtab', $params['stc_id']);
//                     }
//                 }
            }
            if ($category_data['pagination_type'] == 'R' && !empty($search['post_items_per_page'])) {
                $search['items_per_page'] = $search['post_items_per_page'];
                $search['total_items'] = count($products);
                $page = intval($search['page']);
                if (empty($page)) {
                    $page  = 1;
                }
                $items_per_page = intval($search['post_items_per_page']);
                $products = array_slice($products, $items_per_page * ($page - 1), $items_per_page);
            }
            if (!empty($products)) {
                if (!empty($category_data['sections_categorization'])) {
                    $sections_categorization = $other = array();
                    foreach ($products as $i => $product) {
                        if (!empty($product['sections_categorization'])) {
                            $sections = array_unique(explode(',', $product['sections_categorization']));
                            foreach ($sections as $k => $section_id) {
                                if (!empty($section_id)) {
                                    $product['obj_prefix'] = (empty($product['obj_prefix'])) ? $section_id : $product['obj_prefix'] . '_' . $section_id;
                                    $sections_categorization[$section_id][] = $product;
                                }
                            }
                        } else {
                            $other[] = $product;
                        }
                    }
                    foreach ($sections_categorization as $s_id => $prods) {
                        $is_conf = $is_reg = false;
                        foreach ($prods as $prod) {
                            if (!$is_reg && $prod['product_type'] == 'P') {
                                $is_reg = true;
                            }
                            if (!$is_conf && $prod['product_type'] == 'C') {
                                $is_conf = true;
                            }
                            if ($is_conf && $is_reg) {
                                break;
                            }
                        }
                        if ($is_conf && !$is_reg) {
                            foreach ($prods as $p_id => $prod) {
                                if ($prod['product_type'] == 'C') {
                                    unset($sections_categorization[$s_id][$p_id]);
                                }
                            }
                        }
                    }
                    $sc_feature = db_get_hash_array("SELECT ?:product_features.feature_id, ?:product_features_descriptions.description, ?:product_feature_variants.variant_id, ?:product_feature_variant_descriptions.variant, ?:product_feature_variant_descriptions.description AS variant_description FROM ?:product_features LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = ?:product_features.feature_id AND ?:product_features_descriptions.lang_code = ?s INNER JOIN ?:product_feature_variants ON ?:product_feature_variants.feature_id = ?:product_features.feature_id LEFT JOIN ?:product_feature_variant_descriptions ON ?:product_feature_variant_descriptions.variant_id = ?:product_feature_variants.variant_id AND ?:product_feature_variant_descriptions.lang_code = ?s WHERE ?:product_features.feature_id IN (?n) AND ?:product_feature_variants.variant_id IN (?n) ORDER BY ?:product_feature_variants.position", 'variant_id', CART_LANGUAGE, CART_LANGUAGE, $category_data['sections_categorization'], array_keys($sections_categorization));
                    $sections_categorization['other'] = $other;
                    Registry::get('view')->assign('sections_categorization', $sections_categorization);
                    Registry::get('view')->assign('sc_feature', $sc_feature);
                }
            }
            // [tennishouse]
            Registry::get('view')->assign('products', $products);
            Registry::get('view')->assign('search', $search);
//         }
    
        // If a page title for this category exists then assign it to the template
        if (!empty($category_data['page_title'])) {
             Registry::get('view')->assign('page_title', $category_data['page_title']);
             $category_title = $category_data['page_title'];
        } else {
             $category_title = $category_data['category'];
        }
        Registry::get('view')->assign('meta_description', !empty($category_data['meta_description']) ? $category_data['meta_description'] : __("category_meta_description", array('[category]' => fn_strtolower($category_title), '[tabs]' => '', '[subtabs]' => $subtabs_string)));
        if (!empty($category_data['meta_keywords'])) {
            Registry::get('view')->assign('meta_keywords', $category_data['meta_keywords']);
        }

        $show_no_products_block = ((!empty($params['features_hash']) || !empty($params['features_condition'])) && !$products);
        Registry::get('view')->assign('show_no_products_block', $show_no_products_block);

        $_request_data = $_REQUEST;
        $_request_data['default_layout'] = $category_data['default_layout'];
        $_request_data['selected_layouts'] = $category_data['selected_layouts'];
        $selected_layout = fn_get_products_layout($_request_data);
        Registry::get('view')->assign('show_qty', true);
        Registry::get('view')->assign('selected_layout', $selected_layout);

        Registry::get('view')->assign('category_data', $category_data);

        fn_define('FILTER_CUSTOM_ADVANCED', true); // this constant means that extended filtering should be stayed on the same page

        // [Breadcrumbs]
        if (!empty($category_parent_ids)) {
            $cat_data = db_get_hash_array("SELECT a.category_id, a.is_virtual, b.category FROM ?:categories AS a LEFT JOIN ?:category_descriptions AS b ON a.category_id = b.category_id AND b.lang_code = ?s WHERE a.category_id IN (?n)", 'category_id', CART_LANGUAGE, $category_parent_ids);
            Registry::set('runtime.active_category_ids', $category_parent_ids);
            foreach ($category_parent_ids as $i => $c_id) {
//                 if ($cat_data[$c_id]['is_virtual'] != 'Y') {
                    fn_add_breadcrumb($cat_data[$c_id]['category'], "categories.view?category_id=$c_id");
//                 }
            }
        }

        fn_add_breadcrumb($category_data['category'], (empty($_REQUEST['features_hash']) && empty($_REQUEST['advanced_filter'])) ? '' : "categories.view?category_id=$_REQUEST[category_id]");

//         if (!empty($params['features_hash'])) {
//             fn_add_filter_ranges_breadcrumbs($params, "categories.view?category_id=$_REQUEST[category_id]");
//         } elseif (!empty($_REQUEST['advanced_filter'])) {
//             fn_add_breadcrumb(__('advanced_filter'));
//         }
        // [/Breadcrumbs]
    } else {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

} elseif ($mode == 'picker') {

    $category_count = db_get_field("SELECT COUNT(*) FROM ?:categories");
    if ($category_count < CATEGORY_THRESHOLD) {
        $params = array (
            'simple' => false
        );
         list($categories_tree, ) = fn_get_categories($params);
         Registry::get('view')->assign('show_all', true);
    } else {
        $params = array (
            'category_id' => $_REQUEST['category_id'],
            'current_category_id' => $_REQUEST['category_id'],
            'visible' => true,
            'simple' => false
        );
        list($categories_tree, ) = fn_get_categories($params);
    }

    if (!empty($_REQUEST['root'])) {
        array_unshift($categories_tree, array('category_id' => 0, 'category' => $_REQUEST['root']));
    }
    Registry::get('view')->assign('categories_tree', $categories_tree);
    if ($category_count < CATEGORY_SHOW_ALL) {
        Registry::get('view')->assign('expand_all', true);
    }
    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->assign('category_id', $_REQUEST['category_id']);
    }
    Registry::get('view')->display('pickers/categories/picker_contents.tpl');
    exit;
}
