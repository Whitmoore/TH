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

// rus_build_exim_1C dbazhenov

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_settings_variants_addons_rus_exim_1c_exim_1c_export_default_category()
{
  
	   $categories_ids = fn_get_plain_categories_tree(0, false);
	   
       foreach ($categories_ids as $key => $cat) {
			$indent = '';
			for($i = 0; $i < $cat['level']; $i++) {
					$indent = $indent."¦__";
			}
			$categories[$cat['category_id']] = $indent.$cat['category'];
       }
        
       return $categories;
}

function fn_exim_1c_import_categories($xml, $parent_id = 0, $user_type, $company_id)
{
    $categories_1c = array();
    if (isset($xml->Группы->Группа)) {
        foreach ($xml->Группы->Группа as $_group) {
            $category_data = array();
            $category_id = '';
            $id = db_get_field("SELECT category_id FROM ?:categories WHERE external_id = ?s", $_group->Ид);

            if (!empty($id)) {
                $category_id = $id;
            }

            $category_data['category'] = strval($_group->Наименование);
            $category_data['status'] = 'A';
            $category_data['parent_id'] = (int) $parent_id;
            $category_data['timestamp'] = time();
            $category_data['external_id'] = strval($_group->Ид);
            $category_data['company_id'] = $company_id;
            if ($user_type != 'V') {
                $category_id = fn_update_category($category_data, $category_id);
            } else {
                $category_id = Registry::get('addons.rus_exim_1c.exim_1c_export_default_category');
                $id = db_get_field("SELECT category_id FROM ?:category_descriptions WHERE lang_code = 'ru' AND category = ?s", strval($_group->Наименование));	
                if (!empty($id)) {
                    $category_id = $id;
                }
            }
            $categories_1c[strval($_group->Ид)] = $category_id;
            if (isset($_group->Группы->Группа)) {
                fn_exim_1c_import_categories($_group, $category_id, $user_type, $company_id);
            }
        }
        if (Registry::isExist('rus_exim_1c.categories_1c')) {
            $_categories_1c = Registry::get('rus_exim_1c.categories_1c');
            $categories_1c = fn_array_merge ($_categories_1c, $categories_1c);
            Registry::set('rus_exim_1c.categories_1c', $categories_1c);    
        } else {
            Registry::set('rus_exim_1c.categories_1c', $categories_1c);    
        }
    }
}

function fn_exim_1c_collect_features($xml, $company_id)
{
    if (isset($xml->Свойства->Свойство)) {
        $features_1c = array();
        $features_list = fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_features_list'));       
        foreach ($xml->Свойства->Свойство as $_feature) {
            if (Registry::get('addons.rus_exim_1c.exim_1c_import_restraction_features') == 'Y') {
                if (Registry::get('addons.rus_exim_1c.exim_1c_deny_or_allow') == 'do_not_import') {
                    if (in_array(strval($_feature->Наименование), $features_list)) {
                        continue;
                    }
                } else {
                    if (!in_array(strval($_feature->Наименование), $features_list)) {
                        continue;
                    }
                }
            }
            $feature_data = array();
            $id = db_get_field("SELECT feature_id FROM ?:product_features WHERE external_id = ?s", $_feature->Ид);
            $feature_id = (!empty($id)) ? $id : 0;   
          
            $feature_data = fn_exim_1c_feature_skeleton(strval($_feature->Наименование), $feature_id, strval($_feature->Ид), $company_id);
             
            $flag = true;
            $forbidden_features = fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_shipping_cost'));
            $forbidden_features = fn_array_merge($forbidden_features, fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_number_of_items')));
            $forbidden_features = fn_array_merge($forbidden_features, fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_box_length')));
            $forbidden_features = fn_array_merge($forbidden_features, fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_box_width')));
            $forbidden_features = fn_array_merge($forbidden_features, fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_box_height')));  
            foreach ($forbidden_features as $key => $value) {
                if ($feature_data['description'] == $value) {
                    $flag = false;
                }
            }
            if ($flag) {
                $_feature_id = fn_update_product_feature($feature_data, $feature_id);
            } else {
                $feature_id = 'no_feature';
            }
            
            $count = 0;
            
            if (!empty($_feature->ВариантыЗначений)) {
                foreach ($_feature->ВариантыЗначений->Справочник as $_variant) {
                    $_variants[$count]['id'] = strval($_variant->ИдЗначения);
                    $_variants[$count]['value'] = strval($_variant->Значение);
                    $count++;
                }
            } else {
                $_variants = '';
            }
            
            if ($feature_id === 0) {
                db_query("INSERT INTO ?:ult_objects_sharing VALUES ($company_id, $_feature_id, 'product_features')");
            }
            $features_1c[strval($_feature->Ид)]['id'] = (!empty($feature_id)) ? $feature_id : $_feature_id;
            $features_1c[strval($_feature->Ид)]['name'] = strval($_feature->Наименование);
            if (!empty($_variants)) {
                $features_1c[strval($_feature->Ид)]['variants'] = $_variants;
            }
        }

        if (Registry::get('addons.rus_exim_1c.exim_1c_export_brand') == 'use_manufacturer') {
            $feature_data = array();
            $id = db_get_field("SELECT feature_id FROM ?:product_features WHERE external_id = ?s AND company_id = ?i", "brand1c", $company_id);
            $feature_id = (!empty($id)) ? $id : 0;
            $feature_data = fn_exim_1c_feature_skeleton('Изготовитель', $feature_id, "brand1c", $company_id);
            $feature_data['feature_type'] = 'E';
            $_feature_id = fn_update_product_feature($feature_data, $feature_id);
            $_variants = '';
            if ($feature_id == 0) {
                db_query("INSERT INTO ?:ult_objects_sharing VALUES ($company_id, $_feature_id, 'product_features')");
            }
            $features_1c['brand1c']['id'] = (!empty($feature_id)) ? $feature_id : $_feature_id;
            $features_1c['brand1c']['name'] = strval('Изготовитель');
        } 
        if (Registry::isExist('rus_exim_1c.features_1c')) {
            $_features_1c = Registry::get('rus_exim_1c.features_1c');
            $features_1c = fn_array_merge ($_features_1c, $features_1c);
            Registry::set('rus_exim_1c.features_1c', $features_1c);    
        } else {
            Registry::set('rus_exim_1c.features_1c', $features_1c);    
        }
    }
}

function fn_exim_1c_import_products($xml, $user_data, $company_id)
{
    if (isset($xml->Товары->Товар)) {
        if (Registry::isExist('rus_exim_1c.options_1c')) {
            Registry::set('rus_exim_1c.options_1c', array());
        }
        $_cat = Registry::get('rus_exim_1c.categories_1c');
        $products_1C = array();
        $lang_code = CART_LANGUAGE;
        foreach ($xml->Товары->Товар as $_product) {
            if (empty($_product->Наименование) || empty($_product->Группы->Ид)) {
                continue;
            }
            $product = array();
            $ids = fn_explode('#', $_product->Ид);
            $guid_product = array_shift($ids);
            $guid_variant = (!empty($ids)) ? array_shift($ids) : '';

            if (empty($guid_variant)) {
                $guid_variant = fn_exim_1c_get_guid();
            }
            if (Registry::get('addons.rus_exim_1c.exim_1c_export_product_code') == 'art') {
                $product['product_code'] = !empty($_product->Артикул) ? strval($_product->Артикул) : '';
            } elseif (Registry::get('addons.rus_exim_1c.exim_1c_export_product_code') == 'code') {
                foreach ($_product->ЗначенияРеквизитов->ЗначениеРеквизита as $reckvizit) {
                    if (strval($reckvizit->Наименование) == 'Код') {
                        $product['product_code'] = strval($reckvizit->Значение);
                    }
                }
            }
            if (Registry::get('addons.rus_exim_1c.exim_1c_export_product_name') == 'name') {
                $product['product'] = $_product->Наименование;
            } elseif (Registry::get('addons.rus_exim_1c.exim_1c_export_product_name') == 'full_name') {
                $product['product'] = $_product->Наименование;
                foreach ($_product->ЗначенияРеквизитов->ЗначениеРеквизита as $reckvizit) {
                    if (strval($reckvizit->Наименование) == 'Полное наименование') {
                        $product['product'] = strval($reckvizit->Значение);
                    }
                }
            }
			
			//[uzaharova Add promo text]
			if (Registry::get('addons.rus_exim_1c.exim_1c_page_title') == 'name') {
			    $product['page_title'] = trim($_product->Наименование, " -");
			}
            if (Registry::get('addons.rus_exim_1c.exim_1c_page_title') == 'full_name') {
			    $product['page_title'] = trim($_product->Наименование, " -");
                foreach ($_product->ЗначенияРеквизитов->ЗначениеРеквизита as $reckvizit) {
                    if (strval($reckvizit->Наименование) == 'Полное наименование') {
                        $product['page_title'] = trim(strval($reckvizit->Значение), " -");
                    }
                }
			}
			
            $product['status'] = 'A';
            $product['timestamp'] = time();
            $product['is_edp'] = 'N';
            $product['price'] = '0.00';
            $product['list_price'] = '0.00';
            $product['category_ids'] = !empty($_cat[strval($_product->Группы->Ид)]) ? array($_cat[strval($_product->Группы->Ид)]) : array('0');
            if ($user_data['user_type'] == 'V') {
                $product['company_id'] = $user_data['company_id'];
            } else {
                $product['company_id'] = $company_id;
			}
            $product['external_id'] = $guid_product;
        
            if (Registry::get('addons.rus_exim_1c.exim_1c_export_full_description') == 'description') {
                $product['full_description'] = strval($_product->Описание);
            } elseif (Registry::get('addons.rus_exim_1c.exim_1c_export_full_description') == 'HTMLdescription') {
                foreach ($_product->ЗначенияРеквизитов->ЗначениеРеквизита as $reckvizit) {
                    if (strval($reckvizit->Наименование) == 'ОписаниеВФорматеHTML') {
                        $no_html_description = false;
                        $product['full_description'] = strval($reckvizit->Значение);
                    }
                }
            } elseif (Registry::get('addons.rus_exim_1c.exim_1c_export_full_description') == 'full_name') {
                foreach ($_product->ЗначенияРеквизитов->ЗначениеРеквизита as $reckvizit) {
                    if (strval($reckvizit->Наименование) == 'Полное наименование') {
                        $product['full_description'] = strval($reckvizit->Значение);
                    }
                }
            }
            
            if (Registry::get('addons.rus_exim_1c.exim_1c_export_short_description') == 'description') {
                $product['short_description'] = strval($_product->Описание);
            } elseif (Registry::get('addons.rus_exim_1c.exim_1c_export_short_description') == 'HTMLdescription') {
                foreach ($_product->ЗначенияРеквизитов->ЗначениеРеквизита as $reckvizit) {
                    if (strval($reckvizit->Наименование) == 'ОписаниеВФорматеHTML') {
                        $no_html_description = false;
                        $product['short_description'] = strval($reckvizit->Значение);
                    }
                }
            } elseif (Registry::get('addons.rus_exim_1c.exim_1c_export_short_description') == 'full_name') {
                foreach ($_product->ЗначенияРеквизитов->ЗначениеРеквизита as $reckvizit) {
                    if (strval($reckvizit->Наименование) == 'Полное наименование') {
                        $product['short_description'] = strval($reckvizit->Значение);
                    }
                }
            }
			
			//[Add promo_text]
            if (Registry::get('addons.rus_exim_1c.exim_1c_promo_text') == 'name') {
                $product['promo_text'] = trim($_product->Наименование, " -");
            }
			if (Registry::get('addons.rus_exim_1c.exim_1c_promo_text') == 'full_name') {
			    foreach ($_product->ЗначенияРеквизитов->ЗначениеРеквизита as $reckvizit) {
                    if (strval($reckvizit->Наименование) == 'Полное наименование') {
                        $product['promo_text'] = strval($reckvizit->Значение);
                    }
                }
			}
            if (Registry::get('addons.rus_exim_1c.exim_1c_promo_text') == 'description_product') {
			    if (!empty($_product->Описание)) {
				    $product['promo_text'] = strval($_product->Описание);
				}
			}
            if (Registry::get('addons.rus_exim_1c.exim_1c_promo_text') == 'property_product') {
			    $_property_name = Registry::get('addons.rus_exim_1c.exim_1c_property_product');
				if (!empty($_property_name)) {
				    $product['promo_text'] = fn_exim_1c_product_features_promo_text($_product, $_property_name);
				}
			}
            
            $_pid = db_get_field("SELECT product_id FROM ?:products WHERE external_id = ?s", $guid_product);
            $_pid = (empty($_pid)) ? 0 : $_pid;
            if ($_pid == 0) {
                $_product_id = fn_update_product($product, $_pid, $lang_code);
            } else {
                $product['main_category'] = $product['category_ids'][0];
                $_product_id = $_pid;
                
                $_main_category = db_get_row("SELECT category_id, position FROM ?:products_categories WHERE product_id = ?i AND link_type = ?s", $_product_id, 'M');
                $_add_categories = db_get_array("SELECT category_id, position FROM ?:products_categories WHERE product_id = ?i ORDER BY category_id", $_product_id);
    
                $add_categories = array();
                foreach ($_add_categories as $_category) {
                    $add_categories[] = $_category['category_id'];
                    $add_categories_positions[$_category['category_id']] = $_category['position'];
                }
                $prev_cat = $add_categories;

                if (!empty($product['category_ids'])) {
                    $main_category = (!empty($product['main_category'])) ? $product['main_category'] : $add_categories[0];
                } else {
                    $main_category = $_main_category['category_id'];
                }
                $add_categories[] = $main_category;
                $add_categories = array_unique($add_categories);
                db_query("DELETE FROM ?:products_categories WHERE product_id = ?i", $_product_id);
                fn_update_product_count($prev_cat);
            
                $new_ids = $add_categories;
                $_data = array (
                    'product_id' => $_product_id,
                    'link_type' => 'A',
                );
               
                foreach ($add_categories as $c_id) {
                    $_data['category_id'] = $c_id;
                    if (isset($add_categories_positions[$c_id])) {
                        $_data['position'] = $add_categories_positions[$c_id];
                    } else {
                        $_data['position'] = 0;
                    }
                    db_query("INSERT INTO ?:products_categories ?e", $_data);
                }

                fn_update_product_count($new_ids);
                db_query("UPDATE ?:products_categories SET link_type = ?s WHERE product_id = ?i AND category_id = ?i", 'M', $_product_id, $main_category);
                
                $product_update = array();
                $product_update['product'] = $product['product'];
                if (isset($product['full_description'])) {
                    $product_update['full_description'] = $product['full_description'];
                }
                if (isset($product['short_description'])) {
                    $product_update['short_description'] = $product['short_description'];
                }
                
                if (isset($product['page_title'])) {
                    $product_update['page_title'] = $product['page_title'];
                }
                if (isset($product['promo_text'])) {
                    $product_update['promo_text'] = $product['promo_text'];
                }
             
                db_query("UPDATE ?:product_descriptions SET ?u WHERE product_id = ?i AND lang_code = ?s", $product_update, $_product_id, $lang_code);
                
                $product_update['product_code'] = $product['product_code'];    
                $product_update['status'] = 'A';
                db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $product_update, $_product_id);
    
            }
            
            if (!isset($products_1c[$guid_product])) {
                $products_1c[$guid_product] = $_product_id;
            }

            if (!empty($_product->Картинка)) {
                fn_exim_1c_import_product_image($_product, $_product_id, $company_id);
            }

            if (isset($_product->ЗначенияСвойств->ЗначенияСвойства)) {
                fn_exim_1c_import_product_features($_product, $_product_id);
            }

            if (isset($_product->ХарактеристикиТовара->ХарактеристикаТовара)) {
                fn_exim_1c_collect_options($_product, $_product_id, $guid_variant);
            }
            
            if (isset($_product->СтавкиНалогов)) {
                fn_exim_1c_add_tax($_product->СтавкиНалогов, $_product_id);
            }
        }
        $options = array();
        if (Registry::isExist('rus_exim_1c.options_1c')) {
            $options = Registry::get('rus_exim_1c.options_1c');
        }

        $option_name = __('1c_additional_options');

        if (is_array($options) && !empty($options)) {
            fn_exim_1c_import_product_options($options, $option_name, $company_id);
        }
        //write products id's from SESSION into temporary table. The SESSION is rewrite on each mode (Registry and REQUEST too).
         if (!empty($products_1c)) {
            foreach ($products_1c as $ext_key => $prod_key) {
                $_products_c = array(
                    'id' => '',
                    'object_type' => 'products_1c',
                    'primary_object' => $ext_key,
                    'secondary_object' => $prod_key,
                );

                db_query("INSERT INTO ?:rus_exim_1c ?e", $_products_c);
            }
        }
    }
}

function fn_exim_1c_add_tax($tax, $product_id)
{
    if (Registry::get('addons.rus_exim_1c.exim_1c_add_tax') == 'Y') {
        $product = array();
        $tax_id = db_get_field("SELECT tax_id FROM ?:rus_exim_1c_taxes WHERE tax_1c = ?s", strval($tax->СтавкаНалога->Ставка));
        $_tax_ids = db_get_field("SELECT tax_ids FROM ?:products WHERE product_id = ?i", $product_id);
        $tax_ids = fn_explode(',', $_tax_ids);
        if (!in_array($tax_id, $tax_ids)) {
            $tax_ids[] = $tax_id; 
        }
        foreach ($tax_ids as $key => &$value) {
            if (empty($value)) {
                unset($tax_ids[$key]);
            }
        }
        $product['tax_ids'] = $tax_ids;
        fn_update_product($product, $product_id, CART_LANGUAGE);
    }
}

function fn_exim_1c_import_product_image(&$_product, $_product_id, $company_id)
{
    list($dir_c, $dir_c_url) = fn_get_dir_c($company_id);
    $lang_code = CART_LANGUAGE;
    $_REQUEST["server_import_1c_image_icon"] = '';
    $_REQUEST["type_import_1c_image_icon"] = array('server');
    if (PRODUCT_VERSION >= '4.1.1') {
        $_REQUEST["type_import_1c_image_detailed"] = array('url');
    } else {
        $_REQUEST["type_import_1c_image_detailed"] = array('server');
    }
    $image_pairs = false;
    foreach ($_product->Картинка as $key => $images) {
    
        if (PRODUCT_VERSION >= '4.1.1') {
            $images = strval($images);
            if ((fn_exim_1c_check_file($dir_c . fn_basename($images))) !== false) {
                $detailed_file = $dir_c_url . fn_basename($images);
                $_REQUEST["file_import_1c_image_detailed"] = array($detailed_file);
            } else {
                $_REQUEST["file_import_1c_image_detailed"] = array();
            }
        } else {
            if (($detailed_file = fn_exim_1c_check_file($dir_c . fn_basename($images))) !== false) {
                $_REQUEST["file_import_1c_image_detailed"] = array(str_ireplace(DIR_ROOT, '', $detailed_file));
            } else {
                $_REQUEST["file_import_1c_image_detailed"] = array();
            }
        }
        
        $name = fn_explode('.', fn_basename($images));
        $is_img = db_get_fields("SELECT b.image_path FROM ?:images_links as a LEFT JOIN ?:images as b ON a.detailed_id = b.image_id WHERE a.object_type = 'product' AND a.object_id = ?i AND b.image_path LIKE '%".$name[0]."%'", $_product_id);
        
        if (!empty($is_img)) {
            if (strpos($is_img[0], $name[0]) !== false) {
                continue;
            }
        }
        if ($image_pairs) {
            $_REQUEST['import_1c_image_data'] = array(
                array(
                    'type' => 'A',
                    'image_alt' => '',
                    'detailed_alt' => '',
                    'position' => 0,
                )
            );
            fn_attach_image_pairs('import_1c', 'product', $_product_id);
        } else {
            $_REQUEST['import_1c_image_data'] = array(
                array(
                    'type' => 'M',
                    'image_alt' => '',
                    'detailed_alt' => '',
                    'position' => 0,
                )
            );
            fn_attach_image_pairs('import_1c', 'product', $_product_id);        
        }
        $image_pairs = true;
    }
}

function fn_exim_1c_import_product_image_from_ftp($xml, $company_id)
{
    list($dir_c, $dir_c_url) = fn_get_dir_c($company_id);
    $lang_code = CART_LANGUAGE;
    $_REQUEST["server_import_1c_image_icon"] = '';
    $_REQUEST["type_import_1c_image_icon"] = array('server');
    $_REQUEST["type_import_1c_image_detailed"] = array('url');
     
    for ($i = 0; $i < count($xml); $i++) {
        $image = $xml->Image[$i];
        $external_id = strval($image->GUIDProduct);
        $product_id = db_get_field("SELECT product_id FROM ?:products WHERE external_id = ?s", $external_id);
        if (empty($product_id)) {
            continue;
        }
        $img_file = strval($image->File);
        $description = '';
        if (isset($image->Description)) {
            $description = strval($image->Description);
        }
        $_REQUEST["file_import_1c_image_detailed"] = array();

        if ((fn_exim_1c_check_file($dir_c . $img_file)) !== false) {
            $detailed_file = $dir_c_url . $img_file;
            $_REQUEST["file_import_1c_image_detailed"] = array($detailed_file);
        }

        $name = fn_explode('.', $img_file);
        $is_imgs = db_get_array("SELECT b.image_path, a.detailed_id, b.image_id, a.object_id, a.pair_id, a.object_type FROM ?:images_links as a LEFT JOIN ?:images as b ON a.detailed_id = b.image_id WHERE a.object_type = 'product' AND a.object_id = ?i AND b.image_path LIKE '%".$name[0]."%'", $product_id);
       
        if (!empty($is_imgs)) {
            foreach ($is_imgs as $img_key => $img_val) {
                if (strpos($img_val['image_path'], $name[0]) !== false) {
                    fn_delete_image($img_val['image_id'], $img_val['pair_id'], 'detailed');
                }
            }
        }
        $_REQUEST['import_1c_image_data'] = array(
                array(
                    'type' => 'A',
                    'image_alt' => '',
                    'detailed_alt' => $description,
                    'position' => 0,
                )
            );
        if (strval($image->Main)) {
            $_REQUEST['import_1c_image_data'][0]['type'] = 'M';
        }
        fn_attach_image_pairs('import_1c', 'product', $product_id); 
    }
}

function fn_exim_1c_import_product_features(&$_product, $_product_id)
{
    $variants_data['product_id'] = $_product_id;
    $variants_data['lang_code'] = CART_LANGUAGE;
    $features_1c = array();
    if (Registry::isExist('rus_exim_1c.features_1c')) {
        $features_1c = Registry::get('rus_exim_1c.features_1c');
    }
    
    $_cat = Registry::get('rus_exim_1c.categories_1c');
    $category_id = !empty($_cat[strval($_product->Группы->Ид)]) ? $_cat[strval($_product->Группы->Ид)] : '0';
    
    foreach ($_product->ЗначенияСвойств->ЗначенияСвойства as $_feature) {
        foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_number_of_items')) as $key_feature => $val_feature) {
            if ((isset($features_1c[strval($_feature->Ид)]['name'])) && ($features_1c[strval($_feature->Ид)]['name'] == $val_feature)) {
                $val_var_value = str_replace(',', '.', strval($_feature->Значение));
                $val_var_value = (int) preg_replace("/[^x\d|*\.]/", "", $val_var_value);
                if ($val_var_value > 0) {
                    $shipping_params = db_get_field("SELECT shipping_params FROM ?:products WHERE product_id = ?i", $_product_id);
                    $shipping_params_array = unserialize($shipping_params);  
                    $shipping_params_array['min_items_in_box'] = $val_var_value; 
                    $shipping_params_array['max_items_in_box'] = $val_var_value;
                    $shipping_params = serialize($shipping_params_array); 
                    $product['shipping_params'] = $shipping_params;
                    db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $product, $_product_id);
                }            
            }
        }
    }
    
    foreach ($_product->ЗначенияСвойств->ЗначенияСвойства as $_feature) {  
        if (!isset($features_1c[strval($_feature->Ид)])) {
            continue;
        }
        $is_id = true;
        $is_type_feature = 'selectbox';
        $variants_data['feature_id'] = $features_1c[strval($_feature->Ид)]['id']; 
        if ($features_1c[strval($_feature->Ид)]['id'] != 'no_feature') {
            $_categories_path = db_get_field("SELECT categories_path FROM ?:product_features WHERE feature_id = ?i", $features_1c[strval($_feature->Ид)]['id']);
            $categories_path = fn_explode(',', $_categories_path);
            if (!in_array($category_id, $categories_path)) {
                $categories_path[] = $category_id; 
            }
            foreach ($categories_path as $key => $value) {
                if (empty($value)) {
                    unset($categories_path[$key]);
                }
            }
            $feature_data['categories_path'] = implode(',', $categories_path);
            db_query("UPDATE ?:product_features SET ?u WHERE feature_id = ?i", $feature_data, $features_1c[strval($_feature->Ид)]['id']);
        }
        
        if (!empty($features_1c[strval($_feature->Ид)]['variants'])) {
            $_variants = $features_1c[strval($_feature->Ид)]['variants'];
        } else {
            $_variants = '';
        }
        
        foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_export_weight_property')) as $key_feature => $val_feature) {
            if ($features_1c[strval($_feature->Ид)]['name'] == $val_feature) {
                $_value = strval($_feature->Значение);
                $product['weight'] = (float) str_replace(',', '.', $_value);
                fn_update_product($product, $_product_id, CART_LANGUAGE);
                $is_type_feature = 'number';
            }
        }
        
        foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_free_shipping')) as $key_feature => $val_feature) {
            if ($features_1c[strval($_feature->Ид)]['name'] == $val_feature) {
                if (strval($_feature->Значение) == 'Да') {
                    $product['free_shipping'] = 'Y';
                    fn_update_product($product, $_product_id, CART_LANGUAGE);
                }
                $is_type_feature = 'checkbox';
            }
        }
        
        foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_shipping_cost')) as $key_feature => $val_feature) {
            if ($features_1c[strval($_feature->Ид)]['name'] == $val_feature) {
                $_value = strval($_feature->Значение);
                $product['shipping_freight'] = (float) str_replace(',', '.', $_value);
                fn_update_product($product, $_product_id, CART_LANGUAGE);
            }
        }
        
        foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_box_length')) as $k_feature => $v_feature) {
            $shipping_params = db_get_field("SELECT shipping_params FROM ?:products WHERE product_id = ?i", $_product_id);
            $shipping_params_array = unserialize($shipping_params);  
            if ($shipping_params_array['min_items_in_box'] > 0) {
                if ($features_1c[strval($_feature->Ид)]['name'] == $v_feature) {      
                    $val_var_value = str_replace(',', '.', strval($_feature->Значение));
                    $val_var_value = (float) preg_replace("/[^x\d|*\.]/", "", $val_var_value);         
                    $shipping_params_array['box_length'] = $val_var_value; 
                    $shipping_params = serialize($shipping_params_array); 
                    $product['shipping_params'] = $shipping_params;
                    db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $product, $_product_id);
                }
            }
        }
        
        foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_box_width')) as $k_feature => $v_feature) {
            $shipping_params = db_get_field("SELECT shipping_params FROM ?:products WHERE product_id = ?i", $_product_id);
            $shipping_params_array = unserialize($shipping_params);  
            if ($shipping_params_array['min_items_in_box'] > 0) {
                if ($features_1c[strval($_feature->Ид)]['name'] == $v_feature) {      
                    $val_var_value = str_replace(',', '.', strval($_feature->Значение));
                    $val_var_value = (float) preg_replace("/[^x\d|*\.]/", "", $val_var_value); 
                    $shipping_params_array['box_width'] = $val_var_value; 
                    $shipping_params = serialize($shipping_params_array); 
                    $product['shipping_params'] = $shipping_params;
                    db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $product, $_product_id);
                }
            }
        }
        
        foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_box_height')) as $k_feature => $v_feature) {
            $shipping_params = db_get_field("SELECT shipping_params FROM ?:products WHERE product_id = ?i", $_product_id);
            $shipping_params_array = unserialize($shipping_params);  
            if ($shipping_params_array['min_items_in_box'] > 0) {
                if ($features_1c[strval($_feature->Ид)]['name'] == $v_feature) {      
                    $val_var_value = str_replace(',', '.', strval($_feature->Значение));
                    $val_var_value = (float) preg_replace("/[^x\d|*\.]/", "", $val_var_value); 
                    $shipping_params_array['box_height'] = $val_var_value; 
                    $shipping_params = serialize($shipping_params_array); 
                    $product['shipping_params'] = $shipping_params;
                    db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $product, $_product_id);
                }
            }
        }
        
        $value_data['feature_id'] = $features_1c[strval($_feature->Ид)]['id'];
        $value_data['product_id'] = $_product_id;
        $value_data['lang_code'] = CART_LANGUAGE;
        if (empty($_variants)) {   
            if ($is_type_feature == 'number') {
                $_value = strval($_feature->Значение);
                $value_data['value_int'] = (float) str_replace(',', '.', $_value);        
            } elseif ($is_type_feature == 'checkbox') {
                $value_data['value'] = '';
                if (strval($_feature->Значение) == 'Да') {
                    $value_data['value'] = 'Y';
                }
            } elseif ($is_type_feature == 'selectbox') {      
                $variant['variant'] = strval($_feature->Значение);
            }
        } else {
            $value = strval($_feature->Значение);
            
            foreach ($_variants as $key => $val) {
                if ($value == $val['id']) {
                    $variant['variant'] = $val['value'];
                    $is_id = false;
                    break;
                }
            }
            if ($is_id) {
                $variant['variant'] = strval($_feature->Значение);
            }
        }
        if ((isset($features_1c[strval($_feature->Ид)])) && ($features_1c[strval($_feature->Ид)]['id'] !== 'no_feature')) {
            if (($is_type_feature == 'number') || ($is_type_feature == 'checkbox')) {
                db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id = ?i", $features_1c[strval($_feature->Ид)]['id'], $_product_id);
                db_query("REPLACE INTO ?:product_features_values ?e", $value_data); 
            } elseif ($is_type_feature == 'selectbox') {
                //check if current variant already exist
                list($check, $variant_id) = fn_exim_1c_check_feature_variant($variants_data['feature_id'], $variant['variant']);
                if ($check) {
                    $variants_data['variant_id'] = fn_add_feature_variant($variants_data['feature_id'], $variant);
                } else {
                    $variants_data['variant_id'] = $variant_id;
                }
                foreach (fn_get_translation_languages() as $variants_data['lang_code'] => $_d) {
                    db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id = ?i", $variants_data['feature_id'], $variants_data['product_id']);
                    db_query("REPLACE INTO ?:product_features_values ?e", $variants_data);  
                }
            }
        }
    }
    if (Registry::get('addons.rus_exim_1c.exim_1c_export_brand') == 'use_manufacturer') {
        if (isset($_product->Изготовитель)) {
            $variants_data['feature_id'] = $features_1c['brand1c']['id'];
            $variant_id = db_get_field("SELECT variant_id FROM ?:product_feature_variants WHERE feature_id = ?i AND external_id = ?s", $variants_data['feature_id'], strval($_product->Изготовитель->Ид));
            $variant['variant'] = strval($_product->Изготовитель->ОфициальноеНаименование);
            if (empty($variant_id)) {
                $variants_data['variant_id'] = fn_add_feature_variant($variants_data['feature_id'], $variant);
                db_query("UPDATE ?:product_feature_variants SET external_id = ?s WHERE variant_id = ?i", strval($_product->Изготовитель->Ид), $variants_data['variant_id']);
            } else {
                $variants_data['variant_id'] = $variant_id;
            }
            db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id = ?i", $variants_data['feature_id'], $variants_data['product_id']);
            db_query("REPLACE INTO ?:product_features_values ?e", $variants_data);
        }
    }
}

function fn_exim_1c_import_product_options($options, $option_name, $company_id)
{
    foreach ($options as $_pid => $_opts) {
        $option_data = $comment_data = array();
        $option_comment = '';

        foreach ($_opts as $variant_external_id => $option_data) {
            $variant_name = '';
            foreach ($option_data as $option => $value) {
                $variant_name .= $value . '/';
                $comment_data[$option] = '';
            }

            $variant_name = trim($variant_name, '/');
            $variants[$_pid][] = fn_exim_1c_option_variant_skeleton($variant_name, $variant_external_id, $company_id);
        }

        foreach ($comment_data as $comment => $zero) {
            $option_comment .= $comment.'/';
        }
        $option_comment = trim($option_comment, '/');

        $option_data = fn_exim_1c_option_skeleton($option_name, $_pid, $option_comment, $variants[$_pid]);

        $option_id = db_get_field("SELECT option_id FROM ?:product_options WHERE product_id = ?i AND option_type = ?s", $_pid, 'S');
        if (empty($option_id)) {
            $option_id = 0;
        }
        $option_id = fn_update_product_option($option_data, $option_id, CART_LANGUAGE);

        $option_data = fn_get_product_option_data($option_id, $_pid);
        db_query("DELETE FROM ?:product_options_inventory WHERE product_id = ?i", $_pid);
        foreach ($option_data['variants'] as $_k => $_v) {
            $c_hash = fn_generate_cart_id($_pid, array('product_options' => array($option_id => $_v['variant_id'])));
            $c = fn_get_options_combination(array($option_id => $_v['variant_id']));

            $_data = array(
                'amount' => '',
                'product_id' => $_pid,
                'combination_hash' => $c_hash,
                'combination' => $c,
                'product_code' => '',
                'external_id' => db_get_field("SELECT external_id FROM ?:product_option_variants WHERE option_id = ?i AND variant_id = ?i", $option_id, $_v['variant_id'])
            );
            db_query("REPLACE INTO ?:product_options_inventory ?e", $_data);
        }
    }
}

function fn_exim_1c_import_offers($xml, $changes, $company_id)
{
    if (isset($xml->Предложения->Предложение)) {
        $lang_code = CART_LANGUAGE;
        $_products = db_get_hash_array("SELECT primary_object, secondary_object FROM ?:rus_exim_1c WHERE object_type = ?s", 'primary_object', 'products_1c');
        foreach ($_products as $k => $v) {
            $_products[$k] = $v['secondary_object'];
        }
        db_query("DELETE FROM ?:rus_exim_1c WHERE object_type = ?s", 'products_1c');
        $options_data = array();
        $count_var = 0;
        $t_prices = array();
        
        $_global_options = $_combination_options = $_products_options = $_combination_prices = array();
        if (Registry::get('addons.rus_exim_1c.exim_1c_export_add_prices') == 'Y') {
            if (isset($xml->ТипыЦен->ТипЦены)) {
                foreach ($xml->ТипыЦен->ТипЦены as $_prices) { 
                    $t_price = array();
                    $t_price[0]['external_id'] = strval($_prices->Ид);
                    $t_price[0]['name'] = trim(strval($_prices->Наименование));    
                    
                    $t_price_data = db_get_row("SELECT usergroup_id, type FROM ?:rus_exim_1c_prices WHERE price_1c = ?s", $t_price[0]['name']);
                    if (!empty($t_price_data['usergroup_id']) && ($t_price_data['usergroup_id'] > 0)) {
                        $t_price[0]['usergroup_id'] = $t_price_data['usergroup_id'];
                        $t_price[0]['type'] = '';
                        $t_prices = fn_array_merge($t_prices, $t_price, false);
                    } elseif ($t_price_data['type'] == 'base') {                        
                        $t_price[0]['usergroup_id'] = $t_price_data['usergroup_id'];
                        $t_price[0]['type'] = 'base';
                        $t_prices = fn_array_merge($t_prices, $t_price, false);
                    } elseif ($t_price_data['type'] == 'list') {                        
                        $t_price[0]['usergroup_id'] = $t_price_data['usergroup_id'];
                        $t_price[0]['type'] = 'list';
                        $t_prices = fn_array_merge($t_prices, $t_price, false);
                    }
                }
            }
        }
        foreach ($xml->Предложения->Предложение as $_offer) {            
            $offer = array();
            $ids = fn_explode('#', $_offer->Ид);
            $guid_product = array_shift($ids);
            $guid_variant = (!empty($ids)) ? array_shift($ids) : '';
            
            if (isset($_offer->Количество)) {
                $offer['amount'] = strval($_offer->Количество);
            } else {
                $offer['amount'] = 0;
            }
            if (Registry::get('addons.rus_exim_1c.exim_1c_export_stock_empty') == 'Y') {
                if ($offer['amount'] == 0) {
                    $offer['status'] = 'H';
                }
            }
            $prices_1c = array();
            $i = 0;
            if (Registry::get('addons.rus_exim_1c.exim_1c_export_add_prices') == 'Y') {
                if (!empty($t_prices)) {
                    foreach ($_offer->Цены->Цена as $_price) {
                        $id_type_price = strval($_price->ИдТипаЦены);
                        $price = strval($_price->ЦенаЗаЕдиницу);
                        foreach ($t_prices as $key => $t_price) {
                            if ($id_type_price == $t_price['external_id']) {
                                if ($t_price['type'] == 'base') {
                                    $offer['price'] = $price;
                                } elseif ($t_price['type'] == 'list') {
                                    $offer['list_price'] = $price;
                                } else {
                                    $i ++;
                                    $prices_1c[$i]['lower_limit'] = 1;
                                    $prices_1c[$i]['price'] = $price;
                                    $prices_1c[$i]['type'] = $price;
                                    $prices_1c[$i]['usergroup_id'] = $t_price['usergroup_id'];
                                }
                            }
                        }
                    }
                }
            } else {
                $offer['price'] = strval($_offer->Цены->Цена->ЦенаЗаЕдиницу);
            }    
            if (!empty($prices_1c)) {
                $offer['prices'] = $prices_1c;
            }
            if (empty($guid_variant)) {        
                if (!isset($offer['list_price'])) {
                    $offer['list_price'] = '0.00';
                }
                fn_update_product($offer, $_products[$guid_product]);
            } else {
                $product_id = db_get_field("SELECT product_id FROM ?:products WHERE external_id = ?s", $guid_product);
                if (Registry::get('addons.rus_exim_1c.exim_1c_export_add_inventory') == 'standart') {                
                    $options = $options_no = array();
                    if (isset($_offer->ХарактеристикиТовара->ХарактеристикаТовара)) {
                        foreach ($_offer->ХарактеристикиТовара->ХарактеристикаТовара as $_options) {
                            $options[] = strval($_options->Наименование).': '.strval($_options->Значение);
                            $options_no[strval($_options->Наименование)] = strval($_options->Значение);
                        }
                    }
                    if (empty($options_no)) {
                        if (!isset($offer['list_price'])) {
                            $offer['list_price'] = '0.00';
                        }
                        fn_update_product($offer, $_products[$guid_product]);
                    } else {
                        $var = implode('; ', $options);
                
                        $var_data['var'] = $var;
                        $var_data['product_id'] = $product_id;
                        $var_data['amount'] = $offer['amount'];
                        $var_data['price'] = $offer['price'];
                        $var_data['ext_id'] = strval($_offer->Ид);
                    
                        $options_data[$product_id]['product_id'] = $product_id;
                        $options_data[$product_id]['var_data'][$count_var] = $var_data;
                        $count_var++;
                    }
                } elseif (Registry::get('addons.rus_exim_1c.exim_1c_export_add_inventory') == 'no_price') {
                    if (isset($_offer->ХарактеристикиТовара->ХарактеристикаТовара)) {
                        if (!isset($offer['list_price'])) {
                            $offer['list_price'] = '0.00';
                        }
                        $offer['tracking'] = 'O';
                        $_combination_prices[$product_id] = $offer;
                        $_combination_options_data = array(
                            'product_id' => $product_id,
                            'guid_priduct' => $guid_product,
                            'amount' => $offer['amount'],
                            'ext_id' => strval($_offer->Ид)
                        );
                        foreach ($_offer->ХарактеристикиТовара->ХарактеристикаТовара as $_options) {
                            $_global_options[strval($_options->Наименование)][strval($_options->Значение)] = strval($_options->Значение);
                            $_combination_options_data['variants'][] = array(
                                'option_name' => strval($_options->Наименование),
                                'variant_name' => strval($_options->Значение)
                            );
                            $_products_options[$product_id][strval($_options->Наименование)] = strval($_options->Наименование);
                        }
                        $_combination_options[] = $_combination_options_data;
                    }
                }
            }
        }
        
        if (Registry::get('addons.rus_exim_1c.exim_1c_export_add_inventory') == 'standart') {
            foreach($options_data as $k_prod_id => &$val_opt_data) {
                $product_id = $val_opt_data['product_id'];
                if (!empty($product_id)) {
                    db_query("UPDATE ?:products SET tracking = 'O' WHERE product_id = ?i", $product_id);
                    $option_name = Registry::get('addons.rus_exim_1c.exim_1c_export_option_name');
                    $option_data = array(
                        'product_id' => $product_id,
                        'option_name' => $option_name,
                        'company_id' => $company_id,
                        'option_type' => 'S',
                        'required' => 'N',
                        'inventory' => 'Y',
                        'multiupload' => 'M'
                    );
                    if (Registry::get('addons.rus_exim_1c.exim_1c_type_option') == 'radiogroup') {
                        $option_data['option_type'] = 'R';
                    }
                    foreach ($val_opt_data['var_data'] as $vkey => $vval) {
                        if (!empty($vval['amount']) || (Registry::get('addons.rus_exim_1c.exim_1c_export_inventory_zero') == 'Y')){
                            if (isset($vval['price'])) {
                                $vval_price = $vval['price'];
                            } else {
                                $vval_price = 0;
                            }
                            $option_data['variants'][] = array(
                                'variant_name' => $vval['var'],
                                'modifier_type' => 'A',
                                'modifier' => $vval_price,
                                'weight_modifier' => 0,
                                'weight_modifier_type' => 'A'
                            );
                        }
                    }
                    $_option_id = db_get_field("SELECT a.option_id FROM ?:product_options as a WHERE a.product_id = ?i", $product_id);
                    $option_id = fn_update_product_option($option_data, $_option_id, $lang_code);
                    $val_opt_data['option_id'] = $option_id;        
                }
            }
            foreach ($options_data as $k_comb_prod_id => $val_comd_opt_data) {
                $product_id = $val_comd_opt_data['product_id'];
                if (!empty($product_id)) {
                    db_query("UPDATE ?:products SET tracking = 'O' WHERE product_id = ?i", $product_id);
                    db_query("UPDATE ?:product_prices SET price = 0.00 WHERE lower_limit = 1 AND product_id = ?i", $product_id);
                    db_query("UPDATE ?:product_prices SET price = 0.00 WHERE lower_limit = 1 AND product_id = ?i", $product_id);
                    $option_id = $val_comd_opt_data['option_id'];
                    foreach ($val_comd_opt_data['var_data'] as $vkey_comb => $vval_comb) {
                        $db_variant_id = db_get_field("SELECT a.variant_id FROM ?:product_option_variants as a LEFT JOIN ?:product_option_variants_descriptions as b ON a.variant_id = b.variant_id WHERE a.option_id = ?i AND b.variant_name = ?s AND b.lang_code = ?s", $option_id, $vval_comb['var'], $lang_code);
                        $add_options_combination = array(
                            $option_id => $db_variant_id
                        );
                        $combination_hash = fn_generate_cart_id($product_id, array('product_options' => $add_options_combination));
                        $combination = fn_get_options_combination($add_options_combination);
                        $product_code = fn_get_product_code($product_id, $add_options_combination);
                        
                        $old_combination_hash = db_get_field("SELECT combination_hash FROM ?:product_options_inventory WHERE external_id = ?s", $vval_comb['ext_id']);
                        $image_pair_id = db_get_field("SELECT pair_id FROM ?:images_links WHERE object_id = ?i", $old_combination_hash);
                        
                        db_query("DELETE FROM ?:product_options_inventory WHERE external_id = ?s", $vval_comb['ext_id']);
                    
                        if (!empty($vval_comb['amount']) || (Registry::get('addons.rus_exim_1c.exim_1c_export_inventory_zero') == 'Y')){
                            $_data = array(
                                'product_id' => $product_id,
                                'combination_hash' => $combination_hash,
                                'combination' => $combination,
                                'product_code' => !empty($product_code) ? $product_code : '',
                                'amount' => $vval_comb['amount'],
                                'external_id' => $vval_comb['ext_id']
                            );
                            
                            if (!empty($image_pair_id)) {
                                db_query("UPDATE ?:images_links SET object_id = ?i WHERE pair_id = ?i", $_data['combination_hash'], $image_pair_id);
                            }
                            
                            db_query("REPLACE INTO ?:product_options_inventory ?e", $_data);
                        }
                    }                    
                }
            }
        } elseif (Registry::get('addons.rus_exim_1c.exim_1c_export_add_inventory') == 'no_price') {
            foreach ($_global_options as $option => &$vars) {
                $option_data = array(
                        'option_name' => $option,
                        'company_id' => $company_id,
                        'option_type' => 'S',
                        'required' => 'N',
                        'inventory' => 'Y',
                        'multiupload' => 'M'
                );
                if (Registry::get('addons.rus_exim_1c.exim_1c_type_option') == 'radiogroup') {
                    $option_data['option_type'] = 'R';
                }
                foreach ($vars as $vkey => $vval) {
                    $option_data['variants'][] = array(
                        'variant_name' => $vval,
                        'modifier_type' => 'A',
                        'modifier' => 0,
                        'weight_modifier' => 0,
                        'weight_modifier_type' => 'A'
                    );
                }
                $option_id = db_get_field("SELECT option_id FROM ?:product_options_descriptions WHERE option_name = ?s", $option);
                if (empty($option_id)) {
                    $option_id = fn_update_product_option($option_data, $option_id, $lang_code);
                } else {
                    $_variants = db_get_array("SELECT a.variant_id, b.variant_name FROM ?:product_option_variants as a LEFT JOIN ?:product_option_variants_descriptions as b ON a.variant_id = b.variant_id WHERE a.option_id = ?i AND b.lang_code = ?s", $option_id, $lang_code);
                    if (!empty($_variants)) {
                        foreach ($_variants as $_key => $_val) {
                            $find_variant = false;
                            foreach ($option_data['variants'] as $v_key => &$vard) {
                                if ($vard['variant_name'] == $_val['variant_name']) {
                                    $vard['variant_id'] = $_val['variant_id'];
                                    $find_variant = true;
                                    continue;
                                }
                            } 
                            if (!$find_variant) {
                                $option_data['variants'][] = $_val;
                            }
                        }
                    }
                    
                    $option_id = fn_update_product_option($option_data, $option_id, $lang_code);
                }
                $_variants = db_get_array("SELECT a.variant_id, b.variant_name FROM ?:product_option_variants as a LEFT JOIN ?:product_option_variants_descriptions as b ON a.variant_id = b.variant_id WHERE a.option_id = ?i AND b.lang_code = ?s", $option_id, $lang_code);
                foreach ($vars as $_vkey => &$_vval) {
                    foreach ($_variants as $_keyvar => $_valvar) {
                        if ($_vkey == $_valvar['variant_name']) {
                            $_vval = $_valvar['variant_id'];
                        }
                    }
                }
            }
            foreach ($_products_options as $_pid => &$_options_names) {
                foreach ($_options_names as $_option_name => &$_option_val) {
                    $global_option_id = db_get_field("SELECT a.option_id FROM ?:product_options as a LEFT JOIN ?:product_options_descriptions as b ON a.option_id = b.option_id WHERE b.option_name = ?s AND b.lang_code = ?s", $_option_name, $lang_code);
                    $product_option_id = db_get_field("SELECT a.option_id FROM ?:product_options as a LEFT JOIN ?:product_options_descriptions as b ON a.option_id = b.option_id WHERE b.option_name = ?s AND b.lang_code = ?s AND a.product_id", $option_id, $lang_code, $_pid);
                    if (empty($product_option_id)) {
                        db_query("REPLACE INTO ?:product_global_option_links (option_id, product_id) VALUES(?i, ?i)", $global_option_id, $_pid);
                        if (fn_allowed_for('ULTIMATE')) {
                            fn_ult_share_product_option($global_option_id, $_pid);
                        }
                    }
                    $_option_val = $global_option_id;
                    foreach ($_combination_options as $_key_comb => &$_pr_combination_data) {
                        if ($_pid == $_pr_combination_data['product_id']) {
                            if (count($_options_names) != count($_pr_combination_data['variants'])) {
                                $_no_option = true;
                                foreach ($_pr_combination_data['variants'] as $_key_var => &$_pr_comb_var_data) {
                                    if ($_option_name == $_pr_comb_var_data['option_name']) {
                                        $_no_option = false;
                                    }
                                }    
                                if ($_no_option) {
                                    fn_exim_1c_create_empty_variant($global_option_id, $_pr_combination_data, $_option_name, $_global_options);
                                }
                            }
                        }
                    }
                }
            }

            foreach ($_combination_options as $_key => &$_pr_combination) {
                db_query("UPDATE ?:products SET tracking = 'O' WHERE product_id = ?i", $_pr_combination['product_id']);
                db_query("UPDATE ?:product_prices SET price = 0.00 WHERE lower_limit = 1 AND product_id = ?i", $_pr_combination['product_id']);
                $add_options_combination = array();
                foreach ($_pr_combination['variants'] as $_key_comb => &$_combination) {
                    $_combination['option_id'] = $_products_options[$_pr_combination['product_id']][$_combination['option_name']]; 
                    $_combination['variant_id'] = $_global_options[$_combination['option_name']][$_combination['variant_name']]; 
                    $add_options_combination[$_combination['option_id']] = $_combination['variant_id'];                
                }
                $combination_hash = fn_generate_cart_id($_pr_combination['product_id'], array('product_options' => $add_options_combination));
                $combination = fn_get_options_combination($add_options_combination);
                $product_code = fn_get_product_code($_pr_combination['product_id'], $add_options_combination);
                
                $old_combination_hash = db_get_field("SELECT combination_hash FROM ?:product_options_inventory WHERE external_id = ?s", $_pr_combination['ext_id']);
                $image_pair_id = db_get_field("SELECT pair_id FROM ?:images_links WHERE object_id = ?i", $old_combination_hash);
                
                db_query("DELETE FROM ?:product_options_inventory WHERE external_id = ?s", $_pr_combination['ext_id']);
                
                if (!empty($_pr_combination['amount']) || (Registry::get('addons.rus_exim_1c.exim_1c_export_inventory_zero') == 'Y')){    
                    $_data = array(
                        'product_id' => $_pr_combination['product_id'],
                        'combination_hash' => $combination_hash,
                        'combination' => $combination,
                        'amount' => $_pr_combination['amount'],
                        'external_id' => $_pr_combination['ext_id']
                    );
                }
                
                db_query("REPLACE INTO ?:product_options_inventory ?e", $_data);
                
                if (!empty($image_pair_id)) {
                    db_query("UPDATE ?:images_links SET object_id = ?i WHERE pair_id = ?i", $_data['combination_hash'], $image_pair_id);
                }
            }
            foreach ($_combination_prices as $_pid => $_offer_product) {        
                if (Registry::get('addons.rus_exim_1c.exim_1c_export_stock_empty') == 'Y') {
                    if (db_get_field("SELECT COUNT(combination_hash) FROM ?:product_options_inventory WHERE amount > 0 AND product_id = ?i", $_pid) > 0) {
                        $_offer_product['status'] = 'A';
                    } else {
                        $_offer_product['status'] = 'H';
                    }
                }
                fn_update_product($_offer_product, $_pid);    
            }
        }
    }
}

function fn_exim_1c_create_empty_variant($global_option_id, &$_product_combination_data, $_option_name, &$_global_options)
{
    if (isset($_global_options[$_option_name]['empty'])) {
        $variant_id = $_global_options[$_option_name]['empty'];
    }
    if (empty($variant_id)) {
        $variant = array(
            'option_id' => $global_option_id,
            'modifier_type' => 'A',
            'modifier' => 0,
            'weight_modifier' => 0,
            'weight_modifier_type' => 'A'
        );
        $variant_id = db_query("INSERT INTO ?:product_option_variants ?e", $variant);
        $variant = array(
            'variant_id' => $variant_id,
            'variant_name' => 'empty',
            'lang_code' => CART_LANGUAGE
        );
        db_query("INSERT INTO ?:product_option_variants_descriptions ?e", $variant);    
        $_global_options[$_option_name]['empty'] = $variant_id;
    }
    
    $_product_combination_data['variants'][] = array(
        'option_name' => $_option_name,
        'variant_name' => 'empty',
        'option_id' => $global_option_id,
        'variant_id' => $variant_id
    );
}

function fn_exim_1c_import_orders($xml, $company_id)
{
    foreach ($xml->Документ as $_order) {
        $order_id = intval($_order->Номер);
        $is_order_id = db_get_field("SELECT order_id FROM ?:orders WHERE order_id = ?i", $order_id);
        if (empty($is_order_id)) {
            continue;
        }
        $delete_order = false;
        $order_info = fn_get_order_info($order_id);

        $currencies = Registry::get('currencies');
        if (isset($_order->Валюта) && in_array(strtoupper($_order->Валюта), array_keys($currencies))) {
            $order_currency = strtoupper($_order->Валюта);
        } else {
            $order_currency = CART_PRIMARY_CURRENCY;
        }

        if (empty($order_info)) {
            $_order_id = db_get_field("SELECT order_id FROM ?:orders WHERE parent_order_id = ?i AND company_id = ?i", $order_id, $company_id);
            $order_info = fn_get_order_info($_order_id);
            if (!empty($order_info)) {
                $order_id = $_order_id;
            }
        }

        $order_exists = (!empty($order_info)) ? 1 : 0;

        if (isset($_order->Контрагенты->Контрагент)) {
            foreach ($_order->Контрагенты->Контрагент as $_agent) {
                if ($_agent->Роль == 'Покупатель') {
                    $_user_id = (isset($_agent->Ид)) ? intval($_agent->Ид) : 0;
                    if (!empty($_user_id)) {
                        $user_id = db_get_field("SELECT user_id FROM ?:users WHERE external_id = ?i", $_user_id);
                    }

                    $cust_data = fn_explode(" ", strval($_agent->Наименование));
                    $firstname = isset($_agent->Имя) ? strval($_agent->Имя) : '';
                    $lastname = isset($_agent->Фамилия) ? strval($_agent->Фамилия) : '';
                    if (empty($firstname)) {
                        $firstname = !empty($cust_data[1]) ? $cust_data[1] : '';
                    }
                    if (empty($lastname)) {
                        $lastname = !empty($cust_data[0]) ? $cust_data[0] : '';
                    }

                    //user_info
                    $zip = $address = $city = $country = '';
                    if (isset($_agent->Адрес->АдресноеПоле)) {
                        foreach ($_agent->Адрес->АдресноеПоле as $_order_user) {
                            $type = strval($_order_user->Тип);
                            $value = strval($_order_user->Значение);
                            if (stristr($type, 'Почтовый индекс')) {
                                $zip = $value;
                            } elseif (stristr($type, 'Улица')) {
                                $address = $value;
                            } elseif (stristr($type, 'Страна')) {
                                $country = $value;
                            } elseif (stristr($type, 'Город')) {
                                $city = $value;
                            }
                        }
                    }
                }
            }
        }

        //order time
        list($year, $month, $day) = fn_explode("-", strval($_order->Дата));
        list($hour, $minute, $second) = fn_explode(":", strval($_order->Время));
        $order_time = mktime($hour, $minute, $second, $month, $day, $year);

        //order status
        $notes = $delete_action = '';
        foreach ($_order->ЗначенияРеквизитов->ЗначениеРеквизита as $_order_rec) {
            $name = strval($_order_rec->Наименование);
            $value = strval($_order_rec->Значение);
            if (stristr($name, 'Отменен') && $value == 'true') {
                $order_status = 'I';
                break;
            }
            if (stristr($name, 'Финальный статус')) {
                $order_status = ($value != 'false') ? 'C' : 'P';
                break;
            }
            if (stristr($name, 'Проведен')) {
                $order_status = ($value != 'false') ? 'P' : 'O';
                break;
            }
            if (stristr($name, 'Номер платежного документа') || stristr($name, 'Номер по 1С') || stristr($name, 'Метод оплаты')) {
                $notes .= "$name: $value\n";
            }
            if (stristr($name, 'ПометкаУдаления') && $value != 'false') {
                $delete_action = true;
                if (!empty($order_exists)) {
                    db_query("DELETE FROM ?:orders WHERE order_id = ?i", $order_id);
                    db_query("DELETE FROM ?:order_details WHERE order_id = ?i", $order_id);
                    db_query("DELETE FROM ?:profile_fields_data WHERE object_id = ?i AND object_type = 'O'", $order_id);
                    db_query("DELETE FROM ?:order_data WHERE order_id = ?i AND type IN ('T', 'C', 'P')", $order_id);
                    $delete_order = true;
                    break;
                }
            }
        }

        if ($delete_order === true || $delete_action === true) {
            continue;
        }

        if (isset($_order->Комментарий)) {
            $notes .= strval($_order->Комментарий);
        }

        $shipping_cost = $subtotal = 0;
        $order_details = array();
        foreach ($_order->Товары->Товар as $_order_product) {
            $cost = strval($_order_product->ЦенаЗаЕдиницу);
            if (strval($_order_product->Ид) == 'ORDER_DELIVERY' || strval($_order_product->Наименование) == 'Доставка заказа') {
                $shipping_cost = $cost;
            } else {
                $subtotal += $cost;
                $product = db_get_row("SELECT product_id, product_code FROM ?:products WHERE external_id = ?s", $_order_product->Ид);
                if (empty($product)) {
                    $product_id = 0;
                    $product_code = '';
                } else {
                    $product_id = $product['product_id'];
                    $product_code = $product['product_code'];
                }
                $order_details[] = array(
                    'item_id' => fn_generate_cart_id($product_id, array()),
                    'product_id' => $product_id,
                    'product_code' => $product_code,
                    'price' => $cost,
                    'amount' => intval($_order_product->Количество),
                    'extra' => serialize(array('external_id' => strval($_order_product->Ид), 'is_edp' => 'N')),
                );
            }
        }

        $order_data = array(
            'is_parent_order' => 'N',
            'parent_order_id' => (!empty($order_info['parent_order_id'])) ? $order_info['parent_order_id'] : $order_id,
            'user_id' => !empty($user_id) ? $user_id : 0,
            'total' => strval($_order->Сумма),
            'company_id' => $company_id,
            'subtotal' => $subtotal,
            'discount' => '0.00',
            'subtotal_discount' => '0.00',
            'payment_surcharge' => '0.00',
            'shipping_ids' => '',
            'shipping_cost' => $shipping_cost,
            'timestamp' => $order_time,
            'status' => $order_status,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'b_firstname' => $firstname,
            'b_lastname' => $lastname,
            'b_address' => $address,
            'b_city' => $city,
            'b_country' => $country,
            'b_zipcode' => $zip,
            's_firstname' => $firstname,
            's_lastname' => $lastname,
            's_address' => $address,
            's_city' => $city,
            's_country' => $country,
            's_zipcode' => $zip,
            'tax_exempt' => 'N',
            'details' => $notes
        );

//        fn_set_company_id($order_data);

        //order action
        if (!empty($order_exists)) {
            db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $order_data, $order_id);
            db_query("DELETE FROM ?:order_details WHERE order_id = ?i", $order_id);
        } else {
            $order_id = db_query("INSERT INTO ?:orders ?e", $order_data);
            $new_order_data = array(
                'order_id' => $order_id,
                'user_id' => !empty($user_id) ? $user_id : 0,
            );
            db_query("INSERT INTO ?:new_orders ?e", $new_order_data);
        }

        foreach ($order_details as $k => $v) {
            $v['order_id'] = $order_id;

            db_query("REPLACE INTO ?:order_details ?e", $v);
        }
    }

    return true;
}

function fn_exim_1c_export_orders($company_id)
{
    $params['company_name'] = true;
    $params['place'] = 'exim_1c';
    $params['company_id'] = $company_id;
    if (Registry::get('addons.rus_exim_1c.exim_1c_export_add_true_orders') == 'Y') {
        $statuses = Registry::get('addons.rus_exim_1c.exim_1c_export_order_statuses');
        if (!empty($statuses)) {
            foreach($statuses as $key => $status) {
                if (!empty($status)) {
                    $params['status'][] = $key;
                }
            }
        }
    }
    list($orders, $search) = fn_get_orders($params);
    header("Content-type: text/xml; charset=utf-8");
    fn_echo("\xEF\xBB\xBF");
    fn_echo("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n");
    fn_echo("   <КоммерческаяИнформация ВерсияСхемы=\"2.05\" ДатаФормирования=\"".date("Y-m-d")."\">\n");
    foreach ($orders as $k => $data) {
        $order_data = fn_get_order_info($data['order_id']);
        $order_data = fn_exim_1c_data_encode($order_data);
        if (empty($order_data['secondary_currency']) || $order_data['secondary_currency'] == 'RUR' || $order_data['secondary_currency'] == 'RUB') {
            $currency = __('1c_export_currency');
        } else {
            $currency = $order_data['secondary_currency'];
        }
        fn_echo("        <Документ>\n");
        fn_echo("            <Ид>$data[order_id]</Ид>\n");
        fn_echo("            <Номер>$data[order_id]</Номер>\n");
        fn_echo("            <Дата>".date('Y-m-d', $data['timestamp'])."</Дата>\n");
        fn_echo("            <ХозОперация>Заказ товара</ХозОперация>\n");
        fn_echo("            <Роль>Продавец</Роль>\n");
        fn_echo("            <Валюта>$currency</Валюта>\n");
        fn_echo("            <Курс>1</Курс>\n");
        fn_echo("            <Сумма>$data[total]</Сумма>\n");

        //customer info
        fn_echo("            <Контрагенты>\n");
        $o_cust = fn_exim_1c_build_customer_info($order_data);
        foreach ($o_cust as $_o_cust) {
            fn_echo($_o_cust);
        }
        fn_echo("            </Контрагенты>\n");
        fn_echo("            <Время>".date('H:i:s', $order_data['timestamp'])."</Время>\n");

        //order_comment
        if (!empty($order_data['notes'])) {
            $notes = htmlspecialchars(db_get_field("SELECT notes FROM ?:orders WHERE order_id = ?i", $data['order_id']), ENT_QUOTES);
            fn_echo("            <Комментарий>$notes</Комментарий>\n");
        } else {
            fn_echo("            <Комментарий/>\n");
        }

        //order items
        $o_products = fn_exim_1c_build_order_products($order_data);
        fn_echo("            <Товары>\n");
        foreach ($o_products as $_o_products) {
            fn_echo($_o_products);
        }
        fn_echo("            </Товары>\n");

        //order status information
        fn_echo("<ЗначенияРеквизитов>\n");
        fn_echo("    <ЗначениеРеквизита>\n");
        if ($order_data['status'] == 'O') {
            fn_echo("        <Наименование>Статус заказа</Наименование>\n");
            fn_echo("        <Значение>[$order_data[status]] Принят</Значение>\n");
        } elseif ($order_data['status'] == 'B') {
            fn_echo("        <Наименование>Статус заказа</Наименование>\n");
            fn_echo("        <Значение>[$order_data[status]] Отложен</Значение>\n");
        } elseif ($order_data['status'] == 'P') {
            fn_echo("        <Наименование>Статус заказа</Наименование>\n");
            fn_echo("        <Значение>[$order_data[status]] Обработан</Значение>\n");
        } elseif ($order_data['status'] == 'C') {
            fn_echo("        <Наименование>Статус заказа</Наименование>\n");
            fn_echo("        <Значение>[$order_data[status]] Выполнен</Значение>\n");
        } elseif (in_array($order_data['status'], array('D', 'F', 'I'))) {
            fn_echo("        <Наименование>Отменен</Наименование>\n");
            fn_echo("        <Значение>true</Значение>\n");
        } else {
            $status_description = db_get_field("SELECT description FROM ?:status_descriptions WHERE status = ?s AND type='O' AND lang_code = ?s", $order_data['status'], CART_LANGUAGE);
            fn_echo("        <Наименование>Статус заказа</Наименование>\n");
            fn_echo("        <Значение>[$order_data[status]] $status_description</Значение>\n");
        }
        fn_echo("        </ЗначениеРеквизита>\n");
        $payment_name = db_get_field("SELECT payment FROM ?:payment_descriptions WHERE payment_id = ?i AND lang_code = ?s", $order_data['payment_method']['payment_id'], CART_LANGUAGE);
        if (!empty($payment_name)) {
            fn_echo("        <ЗначениеРеквизита>\n");
            fn_echo("            <Наименование>Метод оплаты</Наименование>\n");
            fn_echo("            <Значение>$payment_name</Значение>\n");
            fn_echo("        </ЗначениеРеквизита>\n");
        }
        fn_echo("        </ЗначенияРеквизитов>\n");
        fn_echo("    </Документ>\n");
    }
    fn_echo("</КоммерческаяИнформация>\n");
}

function fn_exim_1c_build_order_products($order_data)
{
    $o_data = array();

    if (Registry::get('addons.rus_exim_1c.exim_1c_export_order_shipping') == 'Y' && $order_data['shipping_cost'] > 0) {
        $o_data[] = "    <Товар>\n";
        $o_data[] = "        <Ид>ORDER_DELIVERY</Ид>\n";
        $o_data[] = "        <Наименование>Доставка заказа</Наименование>\n";
        $o_data[] = "        <ЦенаЗаЕдиницу>$order_data[shipping_cost]</ЦенаЗаЕдиницу>\n";
        $o_data[] = "        <Количество>1</Количество>\n";
        $o_data[] = "        <Сумма>$order_data[shipping_cost]</Сумма>\n";
        $o_data[] = "        <ЗначенияРеквизитов>\n";
        $o_data[] = "            <ЗначениеРеквизита>\n";
        $o_data[] = "                <Наименование>ВидНоменклатуры</Наименование>\n";
        $o_data[] = "                <Значение>Услуга</Значение>\n";
        $o_data[] = "            </ЗначениеРеквизита>\n";
        $o_data[] = "            <ЗначениеРеквизита>\n";
        $o_data[] = "                <Наименование>ТипНоменклатуры</Наименование>\n";
        $o_data[] = "                <Значение>Услуга</Значение>\n";
        $o_data[] = "            </ЗначениеРеквизита>\n";
        $o_data[] = "        </ЗначенияРеквизитов>\n";
        $o_data[] = "    </Товар>\n";
    }
    
    foreach ($order_data['products'] as $k => $v) {
        $product_ext_id = db_get_field("SELECT external_id FROM ?:products WHERE product_id = ?i", $v['product_id']);
        $product_code = htmlspecialchars(db_get_field("SELECT product_code FROM ?:products WHERE product_id = ?i", $v['product_id']), ENT_QUOTES);
        $product_name = htmlspecialchars(fn_get_product_name($v['product_id'], CART_LANGUAGE), ENT_QUOTES);

        if (empty($product_ext_id)) {
            $product_ext_id = fn_exim_1c_get_guid();
            db_query("UPDATE ?:products SET external_id = ?s WHERE product_id = ?i", $product_ext_id, $v['product_id']);
        }
        

        $o_data[] = "    <Товар>\n";
        $o_data[] = "        <Ид>$product_ext_id</Ид>\n";
        $o_data[] = "        <Артикул>$product_code</Артикул>\n";
        $o_data[] = "        <Наименование>$product_name</Наименование>\n";

        $o_data[] = "        <БазоваяЕдиница Код=\"796\" НаименованиеПолное=\"Штука\" МеждународноеСокращение=\"PCE\">шт</БазоваяЕдиница>\n";
        if (isset($v['discount'])) {
            $o_data[] = "    <Скидки>\n";
            $o_data[] = "        <Скидка>\n";
            $o_data[] = "            <Наименование>Скидка на товар</Наименование>\n";
            $o_data[] = "            <Сумма>$v[discount]</Сумма>\n";
            $o_data[] = "            <УчтеноВСумме>true</УчтеноВСумме>\n";
            $o_data[] = "        </Скидка>\n";
            $o_data[] = "    </Скидки>\n";
        }
        $o_data[] = "        <ЦенаЗаЕдиницу>$v[base_price]</ЦенаЗаЕдиницу>\n";
        $o_data[] = "        <Количество>$v[amount]</Количество>\n";
        $o_data[] = "        <Сумма>$v[subtotal]</Сумма>\n";
        $o_data[] = "        <ЗначенияРеквизитов>\n";
        $o_data[] = "            <ЗначениеРеквизита>\n";
        $o_data[] = "                <Наименование>ВидНоменклатуры</Наименование>\n";
        $o_data[] = "                <Значение>Товар</Значение>\n";
        $o_data[] = "            </ЗначениеРеквизита>\n";
        $o_data[] = "            <ЗначениеРеквизита>\n";
        $o_data[] = "                <Наименование>ТипНоменклатуры</Наименование>\n";
        $o_data[] = "                <Значение>Товар</Значение>\n";
        $o_data[] = "            </ЗначениеРеквизита>\n";
        $o_data[] = "        </ЗначенияРеквизитов>\n";
        $o_data[] = "    </Товар>\n";
    }

    return $o_data;
}

function fn_exim_1c_build_customer_info($order_data)
{
    $o_data = array();
    $lastname = db_get_field("SELECT lastname FROM ?:orders WHERE order_id = ?i", $order_data['order_id']);
    $firstname = db_get_field("SELECT firstname FROM ?:orders WHERE order_id = ?i", $order_data['order_id']);
    $full_user_name = $lastname. ' ' . $firstname;
    $name = (!empty($order_data['company'])) ? $order_data['company'] : $full_user_name;
    $full_user_address = $order_data['b_zipcode'] . ', ' . $order_data['b_country_descr'] . ', ' . $order_data['b_city'] . ', ' . $order_data['b_address'];
    $user_id = $order_data['user_id'];

    $user_info = db_get_row("SELECT user_type, external_id FROM ?:users WHERE user_id = ?i", $user_id);
    if (empty($user_info['external_id'])) {
        $user_external_id = fn_exim_1c_get_guid();
        //we will create an external id for registered user only
        if (!empty($user_id)) {
            db_query("UPDATE ?:users SET external_id = ?s WHERE user_id = ?i", $user_external_id, $user_id);
        }
    } else {
        $user_external_id = $user_info['external_id'];
    }
    
    $country_descr = db_get_field("SELECT country FROM ?:country_descriptions WHERE code = ?s AND lang_code = ?s", $order_data['b_country'], CART_LANGUAGE);
    $city = db_get_field("SELECT b_city FROM ?:orders WHERE order_id = ?i", $order_data['order_id']);
    $address = db_get_field("SELECT b_address FROM ?:orders WHERE order_id = ?i", $order_data['order_id']);
    $address .= ' ' . db_get_field("SELECT b_address_2 FROM ?:orders WHERE order_id = ?i", $order_data['order_id']);
    $o_data[] = "<Контрагент>\n";
    $o_data[] = "    <Ид>$user_external_id</Ид>\n";
    $o_data[] = "    <Наименование>$name</Наименование>\n";
    $o_data[] = "    <Роль>Покупатель</Роль>\n";
    $o_data[] = "    <ПолноеНаименование>$full_user_name</ПолноеНаименование>\n";
    $o_data[] = "    <Фамилия>$lastname</Фамилия>\n";
    $o_data[] = "    <Имя>$firstname</Имя>\n";
    //customer address info
    $o_data[] = "    <Адрес>\n";
    $o_data[] = "        <Представление>$order_data[b_zipcode], $country_descr, $city, $address</Представление>\n";
    $o_data[] = "        <АдресноеПоле>\n";
    $o_data[] = "            <Тип>Почтовый индекс</Тип>\n";
    $o_data[] = "            <Значение>$order_data[b_zipcode]</Значение>\n";
    $o_data[] = "        </АдресноеПоле>\n";
    $o_data[] = "        <АдресноеПоле>\n";
    $o_data[] = "            <Тип>Страна</Тип>\n"; 
    $o_data[] = "            <Значение>$country_descr</Значение>\n";
    $o_data[] = "        </АдресноеПоле>\n";
    $o_data[] = "        <АдресноеПоле>\n";
    $o_data[] = "            <Тип>Город</Тип>\n";
    $o_data[] = "            <Значение></Значение>\n";
    $o_data[] = "        </АдресноеПоле>\n";
    $o_data[] = "        <АдресноеПоле>\n";
    $o_data[] = "            <Тип>Улица</Тип>\n";
    $o_data[] = "            <Значение>$address</Значение>\n";
    $o_data[] = "        </АдресноеПоле>\n";
    $o_data[] = "    </Адрес>\n";
    //customer contacts
    $o_data[] = "    <Контакты>\n";
    $o_data[] = "        <Контакт>\n";
    $o_data[] = "             <Тип>Почта</Тип>\n";
    $o_data[] = "             <Значение>$order_data[email]</Значение>\n";
    $o_data[] = "        </Контакт>\n";
    if (!empty($order_data['phone'])) {
        $o_data[] = "       <Контакт>\n";
        $o_data[] = "           <Тип>ТелефонРабочий</Тип>\n";
        $o_data[] = "           <Значение>$order_data[phone]</Значение>\n";
        $o_data[] = "       </Контакт>\n";
    }
    $o_data[] = "    </Контакты>\n";
    
    $o_data[] = "    <Представители>\n";
    $o_data[] = "        <Представитель>\n";
    $o_data[] = "            <Контрагент>\n";
    $o_data[] = "                <Отношение>Контактное лицо</Отношение>\n";
    $o_data[] = "                <Ид>$user_external_id</Ид>\n";
    if (isset($user_info['user_type']) && $user_info['user_type'] == 'A') {
        $o_data[] = "                <Наименование>Администратор</Наименование>\n";
    } else {
        $o_data[] = "                <Наименование>Покупатель</Наименование>\n";
    }
    $o_data[] = "            </Контрагент>\n";
    $o_data[] = "        </Представитель>\n";
    $o_data[] = "    </Представители>\n";
    $o_data[] = "</Контрагент>\n";

    return $o_data;
}

function fn_exim_1c_feature_skeleton($feature_name, $feature_id, $external_id, $company_id)
{
    $data = array(
        'variants' => array(),
        'description' => $feature_name,
        'position' => 0,
        'feature_type' => 'S',
        'parent_id' => 0,
        'display_on_catalog' => "Y",
        'display_on_product' => "Y",
        'prefix' => '',
        'suffix' => '',
        'company_id' => $company_id,
        'external_id' => $external_id
    );
    
    foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_export_weight_property')) as $key => $val_feature) {
        if ($feature_name == $val_feature) {
            $data['feature_type'] = 'O';
            if (Registry::get('addons.rus_exim_1c.exim_1c_display_weight') == 'Y') {
                $data['status'] = 'A';
            } else {
                $data['status'] = 'D';
            }
        }
    }
    
    foreach (fn_explode("\n", Registry::get('addons.rus_exim_1c.exim_1c_free_shipping')) as $key => $val_feature) {
        if ($feature_name == $val_feature) {
            $data['feature_type'] = 'C';
            if (Registry::get('addons.rus_exim_1c.exim_1c_display_free_shipping') == 'Y') {
                $data['status'] = 'A';
            } else {
                $data['status'] = 'D';
            }
        }
    }
    
    if ((Registry::get('addons.rus_exim_1c.exim_1c_export_brand') == 'property_product') && (Registry::get('addons.rus_exim_1c.exim_1c_property_for_manufacturer') === $feature_name)) {
        $data['feature_type'] = 'E';
    }
    
    return $data;
}

function fn_exim_1c_option_skeleton($option_name, $product_id, $comment, $variants = array())
{
    $data = array(
        'product_id' => $product_id,
        'option_name' => $option_name,
        'position' => '',
        'inventory' => 'Y',
        'option_type' => 'S',
        'description' => '',
        'comment' => $comment,
        'required' => 'Y',
        'multiupload' => 'N',
        'variants' => $variants,
        'object' => 'product'
    );

    return $data;
}

function fn_exim_1c_option_variant_skeleton($variant_name, $external_id, $company_id)
{
    $data = array(
        'position' => '',
        'variant_name' => $variant_name,
        'modifier' => '',
        'modifier_type' => 'A',
        'weight_modifier' => '',
        'weight_modifier_type' => 'A',
        'status' => 'A',
        'company_id' => $company_id,
        'external_id' => $external_id
    );

    return $data;
}

function fn_exim_1c_collect_options(&$_product, $product_id, $guid_variant)
{
    if (Registry::isExist('rus_exim_1c.options_1c')) {
        $options_1c = Registry::get('rus_exim_1c.options_1c');
	} else {
	    $options_1c = array();
	}
    foreach ($_product->ХарактеристикиТовара->ХарактеристикаТовара as $_options) {
        $options_1c[$product_id][$guid_variant][strval($_options->Наименование)] = strval($_options->Значение);
    }
    Registry::set('rus_exim_1c.options_1c', $options_1c);
}

function fn_exim_1c_check_file($file)
{
    return (is_file($file)) ? realpath($file) : false;
}

function fn_exim_1c_check_feature_variant($feature_id, $variant)
{
    $variant_exists = db_get_field("SELECT ?:product_feature_variant_descriptions.variant_id FROM ?:product_feature_variant_descriptions LEFT JOIN ?:product_feature_variants ON ?:product_feature_variant_descriptions.variant_id = ?:product_feature_variants.variant_id WHERE ?:product_feature_variants.feature_id = ?i AND ?:product_feature_variant_descriptions.variant = ?s AND ?:product_feature_variant_descriptions.lang_code = ?s", $feature_id, $variant, DESCR_SL);

    $result = (!empty($variant_exists)) ? false : true;

    return array($result, $variant_exists);
}

function fn_exim_1c_get_external_file($dir, $filename)
{
    if (!is_dir($dir)) {
        fn_mkdir($dir);
    }
    $file = $dir . $filename;
    if (file_exists($file)) {
//        fn_rm($file);
    }
    $f = fopen($file, 'a');
    if (!$f) {
        return false;
    }
    fwrite($f, file_get_contents('php://input'));
    fclose($f);
}

function fn_exim_1c_get_guid()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

function fn_exim_1c_generate_filename($filename, $suffix)
{
    $ext = pathinfo($filename);
    if (!empty($ext['extension'])) {
        $filename = fn_basename($filename, '.'.$ext['extension']);
    }

    return $filename.$suffix;
}

function fn_exim_1c_checkauth($cookie, $cookie_id)
{
    fn_echo("success\n");
    fn_echo("$cookie\n");
    fn_echo($cookie_id);
}

function fn_exim_1c_auth_error($msg)
{
    header('WWW-Authenticate: Basic realm="Authorization required"');
    header('HTTP/1.0 401 Unauthorized');
    fn_echo($msg);
}

function fn_exim_1c_check_prices($xml)
{
    $t_prices = db_get_array("SELECT price_1c, usergroup_id, type FROM ?:rus_exim_1c_prices");
    $t_prices_1c = array();
    $i = 0;
    if (isset($xml->Предложения->Предложение)) {
        if (isset($xml->ТипыЦен->ТипЦены)) {
            foreach ($xml->ТипыЦен->ТипЦены as $_prices) { 
                $t_prices_1c[$i]['price_1c'] = trim(strval($_prices->Наименование));    
                $t_prices_1c[$i]['external_id'] = trim(strval($_prices->Ид));    
                $i ++;
            }
        }
    }    
    if (!empty($t_prices_1c)) {
        foreach ($t_prices as $k => &$t_price) {
            $valid = false;
            foreach ($t_prices_1c as $kk => $t_price_1c) {
                if ($t_price['price_1c'] == $t_price_1c['price_1c']) {
                    $valid = true;
                    break;
                }
            }
            $t_price['valid'] = $valid;
        }
    }
    return $t_prices;
}

function fn_settings_variants_addons_rus_exim_1c_exim_1c_export_order_statuses()
{
    $order_statuses = array();
    $statuses = db_get_array("SELECT status, description FROM ?:status_descriptions WHERE type = 'O' AND lang_code = ?s", CART_LANGUAGE);
    foreach ($statuses as $key => $val) {
            $order_statuses[$val['status']] = $val['description'];
    }
    
    return $order_statuses;
}

function fn_exim_1c_clear_1c_dir($path)
{
    if(is_dir($path)) {
        $dirHandle = opendir($path);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file!='.' && $file!='..') {
                $tmpPath=$path.'/'.$file;
                chmod($tmpPath, 0777);
                if (is_dir($tmpPath)) {
                    fn_exim_1c_clear_1c_dir($tmpPath);
                } else { 
                    if(file_exists($tmpPath)) {
                        unlink($tmpPath);
                    }
                }
            }
        }
        closedir($dirHandle);
    }
}

function fn_exim_1c_product_features_promo_text($_product, $property_name)
{
    //[Add promo text]
    $features_1c = array();
    if (Registry::isExist('rus_exim_1c.features_1c')) {
        $features_1c = Registry::get('rus_exim_1c.features_1c');
    } else {
        return '';
    }
    if (isset($_product->ЗначенияСвойств->ЗначенияСвойства)) {
        foreach ($_product->ЗначенияСвойств->ЗначенияСвойства as $_feature) {	
            $feature_name = trim($features_1c[strval($_feature->Ид)]['name'], " ");
            if ($property_name == $feature_name) {
                return strval($_feature->Значение);
            }
        }
    }
	
	return '';
}

function fn_rus_exim_1c_allowed_access($user_data)
{
    if (empty($user_data['usergroups'])) {
        return true;
    }
    foreach ($user_data['usergroups'] as $usergroup) {
        $privilege = db_get_field("SELECT privilege FROM ?:usergroup_privileges WHERE usergroup_id=?i AND privilege='exim_1c'", $usergroup['usergroup_id']);
        if ((!empty($privilege)) && ($usergroup['status'] == 'A')) {
            return true;
        }
    }
    
    return false;
}

function fn_get_dir_c($company_id)
{
    $dir_c = fn_get_files_dir_path() . 'exim/1C_' . date('dmY') . '/';
    $dir_c_url = Registry::get('config.http_location') . '/' . fn_get_rel_dir($dir_c);
    
    return array($dir_c, $dir_c_url);
}
//
// hooks
//

function fn_rus_exim_1c_update_profile(&$action, &$user_data, &$current_user_data)
{
    if ($action == 'add') {
        $user_external_id = fn_exim_1c_get_guid();
        db_query("UPDATE ?:users SET external_id = ?s WHERE user_id = ?i", $user_external_id, $user_data['user_id']);
    }
}

function fn_rus_exim_1c_get_orders($params, $fields, $sortings, &$condition, $join, $group)
{
    if (isset($params['place'])) {
        if ((Registry::get('addons.rus_exim_1c.exim_1c_export_add_true_orders') == 'Y') && ($params['place'] == 'exim_1c')) {
            $order_id = Registry::get('addons.rus_exim_1c.exim_1c_from_order_id');
            if (!empty($order_id)) {
                $condition .= db_quote(" AND ?:orders.order_id >= ?i", $order_id);
            }
        }
    }
}

function fn_exim_1c_data_encode($data)
{
    if (empty($data)) {
        return '';
    }

    if (!is_array($data)) {
        if (is_string($data)) {
            $data = htmlentities($data);
        }
    } else {
        foreach ($data as $k => $v) {
            $data[$k] = fn_exim_1c_data_encode($v);
        }
    }

    return $data;
}

