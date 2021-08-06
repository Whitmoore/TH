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

namespace Tygh\Gm;

use Tygh\Registry;

class Gml implements IGml
{

    const ITERATION_ITEMS = 100;
    const IMAGES_LIMIT = 10;

    protected $company_id;
    protected $options = array();
    protected $lang_code = DESCR_SL;
    protected $page = 0;
    protected $debug = false;

    protected $disabled_category_ids = array();

    public function __construct($company_id, $options = array(), $lang_code = DESCR_SL, $page = 0, $debug = false)
    {
        $this->company_id = $company_id;
        $this->options    = $options;
        $this->lang_code  = $lang_code;
        $this->page       = $page;
        $this->debug      = $debug;
    }

    public function get()
    {
        $filename = $this->getFileName();

        if (!file_exists($filename) || $this->debug) {
            $this->generate($filename);
        }

        $this->sendResult($filename);
    }

    public function getFileName()
    {
        $path = sprintf('%sgoogle_merchant/%s_google_merchant.xml',
            fn_get_cache_path(false, 'C', $this->company_id),
            $this->company_id
        );

        return $path;
    }

    public function clearCache()
    {
        return fn_rm($this->getFileName());
    }

    public static function clearCaches($company_ids = null)
    {
        if (is_null($company_ids)) {
            if (Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) {
                $company_ids = Registry::get('runtime.company_data.company_id');
            } else {
                $company_ids = array_keys(fn_get_short_companies());
            }
        }

        foreach ((array) $company_ids as $company_id) {
            $self = new self($company_id);
            $self->clearCache();
        }
    }

    public function generate($filename)
    {
        @ignore_user_abort(1);
        @set_time_limit(0);

        fn_mkdir(dirname($filename));
        $file = fopen($filename, 'wb');

        $this->head($file);
        $this->body($file);
        $this->bottom($file);

        fclose($file);
    }

    protected function head($file)
    {
        $gml_header = array(
            '<?xml version="1.0" encoding="' . $this->options['export_encoding'] . '"?>',
            '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">',
            '<channel>'
        );

        $gml_data = array(
            'title' => Registry::get('settings.Company.company_name'),
            'link' => Registry::get('config.http_location'),
            'description' => 'Tennis-Cart',
        );

        $this->getDisabledCategories();

        fwrite($file, implode(PHP_EOL, $gml_header) . PHP_EOL);
        fwrite($file, fn_array_to_xml($gml_data));
    }

    protected function body($file)
    {
        if ($this->options['hide_disabled_categories'] == "Y") {
            $visible_categories = $this->getVisibleCategories();
        }

        $cat_data = db_get_hash_array("SELECT category_id, id_path, gml_product_category FROM ?:categories", 'category_id');
        $params = array(
            'gml_disable_product' => 'N',
            'extend' => array('description', 'full_description')
        );
        list($products, ) = fn_get_products($params);

        fn_gather_additional_products_data($products, array(
            'get_icon' => false,
            'get_detailed' => true,
            'get_additional' => true,
            'get_options'=> true,
            'get_inventory' => true,
            'get_discounts' => true,
            'get_features' => true,
            'features_display_on' => 'A',
//             'get_title_features' => false,
            'allow_duplication' => false
        ));
//         $brands = fn_get_product_feature_data(BRAND_FEATURE_ID, true, true);

        $offset = 0;
        $option_type = array();
        while ($prods_slice = array_slice($products, $offset, self::ITERATION_ITEMS)) {
            $offset += self::ITERATION_ITEMS;

            $slice_result = '';
            foreach ($prods_slice as $k => &$product) {
                $new_product = array();

//                 $brand = $product['product_features'][BRAND_FEATURE_ID]['variant'];

//              Basic product data
                $new_product['g:id'] = fn_generate_cart_id($product['product_id'], array());
                $new_product['g:title'] = ucfirst(strtolower($this->escape($product['product'])));
                if ($product['full_description'] != '') {
                    $new_product['g:description'] = $this->escape($product['full_description']);
                } elseif ($product['short_description'] != '') {
                    $new_product['g:description'] = $this->escape($product['short_description']);
                } else {
                   $new_product['g:description'] = $new_product['g:title'];
                }
                $new_product['g:link'] = fn_url('products.view?product_id=' . $product['product_id']);
                $new_product['g:image_link'] = $product['main_pair']['detailed']['http_image_path'];
                if (!empty($product['image_pairs'])) {
                    $additional = array_slice($product['image_pairs'], 0, 10);
                    foreach ($additional as $i => $pair)  {
                        if (!empty($pair['detailed']['http_image_path'])) {
                            $new_product['g:additional_image_link'] = $pair['detailed']['http_image_path'];
                        }
                    }
                }
//                 $new_product['g:mobile_link'] = '';

//              Price & availability
                $new_product['g:availability'] = $product['amount'] > 0 ? 'in stock' : 'out of stock';
//                 $new_product['g:availability_date'] = '';
//                 $new_product['g:expiration_date'] = '';
                if (!empty($product['discount'])) {
                    $new_product['g:price'] = $this->formatPrice($product['base_price']);
                    $new_product['g:sale_price'] = $price = $this->formatPrice($product['price']);
                } elseif (!empty($product['list_discount'])) {
                    $new_product['g:price'] = $this->formatPrice($product['list_price']);
                    $new_product['g:sale_price'] = $price = $this->formatPrice($product['price']);
                } else {
                    $new_product['g:price'] = $price = $this->formatPrice($product['price']);
                }

//                 $new_product['g:sale_price_effective_date'] = '';
//                 $new_product['g:unit_pricing_measure'] = '';
//                 $new_product['g:unit_pricing_base_measure'] = '';
//                 $new_product['g:installment'] = '';
//                 $new_product['g:loyalty_points'] = '';

//              Product category
                $global = fn_get_product_global_data($product, array('gml_product_category'), $cat_data);
                $new_product['g:google_product_category'] = !empty($product['gml_product_category']) ? $product['gml_product_category'] : (!empty($global['gml_product_category']) ? $global['gml_product_category'] : false);
//                 $new_product['g:product_type'] = '';

//              Product identifiers
                $new_product['g:brand'] = $product['product_features'][BRAND_FEATURE_ID]['variant'] ?? 'NO BRAND';
                if (!empty($product['ean'])) {
                    $new_product['g:gtin'] = $product['ean'];
                    $new_product['g:identifier_exists'] = 'yes';
                } else {
                    $new_product['g:identifier_exists'] = 'no';
                }
//                 $new_product['g:mpn'] = '';

//              Detailed product description
                $new_product['g:condition'] = 'new';
                $new_product['g:adult'] = 'no';
//                 $new_product['g:multipack'] = '';
//                 $new_product['g:is_bundle'] = '';
//                 $new_product['g:energy_efficiency_class'] = '';
//                 $new_product['g:min_energy_efficiency_class'] = '';
//                 $new_product['g:max_energy_efficiency_class'] = '';

                if (!empty($product['product_features'][CLOTHES_GENDER_FEATURE_ID])) {
                    if (in_array($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variant_id'], array(C_GENDER_M_FV_ID, C_GENDER_W_FV_ID, C_GENDER_U_FV_ID))) {
                        $new_product['g:age_group'] = 'adult';
                    } elseif (in_array($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variant_id'], array(C_GENDER_B_FV_ID, C_GENDER_G_FV_ID))) {
                        $new_product['g:age_group'] = 'kids';
                    }
                    if (in_array($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variant_id'], array(C_GENDER_M_FV_ID, C_GENDER_B_FV_ID))) {
                        $new_product['g:gender'] = 'male';
                    } elseif (in_array($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variant_id'], array(C_GENDER_W_FV_ID, C_GENDER_G_FV_ID))) {
                        $new_product['g:gender'] = 'female';
                    } elseif (in_array($product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variant_id'], array(C_GENDER_U_FV_ID))) {
                        $new_product['g:gender'] = 'unisex';
                    }
                }
                if (!empty($product['product_features'][SHOES_GENDER_FEATURE_ID])) {
                    if (in_array($product['product_features'][SHOES_GENDER_FEATURE_ID]['variant_id'], array(S_GENDER_M_FV_ID, S_GENDER_W_FV_ID, S_GENDER_U_FV_ID))) {
                        $new_product['g:age_group'] = 'adult';
                    } elseif (in_array($product['product_features'][SHOES_GENDER_FEATURE_ID]['variant_id'], array(S_GENDER_K_FV_ID))) {
                        $new_product['g:age_group'] = 'kids';
                    }
                    if (in_array($product['product_features'][SHOES_GENDER_FEATURE_ID]['variant_id'], array(S_GENDER_M_FV_ID))) {
                        $new_product['g:gender'] = 'male';
                    } elseif (in_array($product['product_features'][SHOES_GENDER_FEATURE_ID]['variant_id'], array(S_GENDER_W_FV_ID))) {
                        $new_product['g:gender'] = 'female';
                    } elseif (in_array($product['product_features'][SHOES_GENDER_FEATURE_ID]['variant_id'], array(S_GENDER_U_FV_ID, S_GENDER_K_FV_ID))) {
                        $new_product['g:gender'] = 'unisex';
                    }
                }
                if (!empty($product['product_features'][BG_TYPE_FEATURE_ID])) {
                    $new_product['g:material'] = $product['product_features'][BG_TYPE_FEATURE_ID]['variant'];
                } elseif (!empty($product['product_features'][S_MATERIAL_FEATURE_ID])) {
                    $new_product['g:material'] = $product['product_features'][S_MATERIAL_FEATURE_ID]['variant'];
                } elseif (!empty($product['product_features'][CLOTHES_MATERIAL_FEATURE_ID])) {
                    $new_product['g:material'] = $product['product_features'][CLOTHES_MATERIAL_FEATURE_ID]['variant'];
                } elseif (!empty($product['product_features'][R_MATERIAL_FEATURE_ID])) {
                    $new_product['g:material'] = $product['product_features'][R_MATERIAL_FEATURE_ID]['variant'];
                }
//                 $new_product['g:pattern'] = '';
//                 $new_product['g:size_type'] = '';
                $new_product['g:item_group_id'] = $product['product_code'];
//                 $new_product['g:pattern'] = '';
//                 $new_product['g:pattern'] = '';


//                 Shopping campaigns and other configurations
//                 $new_product['g:adwords_redirect'] = '';
//                 $new_product['g:excluded_​​destination'] = '';
//                 $new_product['g:included_​​destination'] = '';
//                 $new_product['g:custom_​​label_​​0'] = '';
//                 $new_product['g:promotion_​​id'] = '';


//              Shipping
                if ($price <= Registry::get('addons.development.free_shipping_cost')) {
                    $new_product['g:shipping'] = $this->formatPrice(0);
                }
//                 $new_product['g:shipping_​​label'] = '';
                $new_product['g:shipping_weight'] = $product['weight'] . ' kg';
//                 $new_product['g:shipping_​​length'] = '';
//                 $new_product['g:shipping_​​width'] = '';
//                 $new_product['g:shipping_​​height'] = '';
//                 $new_product['g:max_handling_time'] = '';
//                 $new_product['g:min_handling_time'] = '';

//              Tax
//                 $new_product['g:tax'] = '';
//                 $new_product['g:tax_category'] = '';

                if (empty($new_product['g:id']) || empty($new_product['g:title']) || empty($new_product['g:description']) || empty($new_product['g:link']) || empty($new_product['g:image_link']) || empty($new_product['g:availability']) || empty($new_product['g:price']) || empty($new_product['g:google_product_category']) || empty($new_product['g:brand']) || empty($new_product['g:condition']) || empty($new_product['g:adult']) || in_array($product['main_category'], $this->disabled_category_ids) || ($this->options['hide_disabled_categories'] == 'Y' && !in_array($product['main_category'], $visible_categories)) || ($this->options['in_stock_only'] == 'Y' && $product['amount'] <= 0)) {
                    continue;
                }

                if (!empty($product['inventory'])) {
                    $iteration = 0;
                    $item_groups = array();
                    foreach ($product['inventory'] as $combination) {
                        $options = fn_get_product_options_by_combination($combination['combination']);
                        $item_groups[$iteration]['g:id'] = fn_generate_cart_id($product['product_id'], array('product_options' => $options), true);
                        foreach ($options as $opt_id => $vr_id) {
                            if (!empty($product['product_options'][$opt_id]) && !empty($product['product_options'][$opt_id]['variants'][$vr_id]['variant_name'])) {
                                if (!array_key_exists($opt_id, $option_type)) {
                                    if (preg_match('/(размер|толщина|ручка)/iu', $product['product_options'][$opt_id]['option_name'], $match)) {
                                        $option_type[$opt_id] = 'S';
                                    } elseif (preg_match('/(цвет)/iu', $product['product_options'][$opt_id]['option_name'], $match)) {
                                        $option_type[$opt_id] = 'C';
                                    } else {
                                        $option_type[$opt_id] = 'N';
                                    }
                                }
                                if ($option_type[$opt_id] == 'S') {
                                    $item_groups[$iteration]['g:size'] = $product['product_options'][$opt_id]['variants'][$vr_id]['variant_name'];
                                    $item_groups[$iteration]['g:size_type'] = 'regular';
                                } elseif ($option_type[$opt_id] == 'C') {
                                    $item_groups[$iteration]['g:color'] = $product['product_options'][$opt_id]['variants'][$vr_id]['variant_name'];
                                }
                            }
                        }
                        $iteration++;
                    }
                    if (!empty($item_groups)) {
                        foreach ($item_groups as $group) {
                            $slice_result .= fn_array_to_xml(array('item' => array_merge($new_product, $group)));
                        }
                    }
                } else {
//                     $new_product['g:color'] = '';
//                     $new_product['g:size'] = '';
                    $slice_result .= fn_array_to_xml(array('item' => $new_product));
                }
            }
            fwrite($file, $slice_result);
        }
    }

    protected function bottom($file)
    {
        fwrite($file, '</channel>' . PHP_EOL);
        fwrite($file, '</rss>' . PHP_EOL);
    }

    protected function sendResult($filename)
    {
        header("Content-Type: text/xml;charset=" . $this->options['export_encoding']);

        readfile($filename);
        exit;
    }

    protected function formatPrice($price, $currency_code = CART_SECONDARY_CURRENCY)
    {
        $currencies = Registry::get('currencies');
        $currency = $currencies[$currency_code];

        $result = fn_format_rate_value($price, 'F', $currency['decimals'], $currency['decimals_separator'], '', $currency['coefficient']);
        if ($currency['after'] == 'Y') {
            $result .= ' ' . $currency['currency_code'];
        } else {
            $result = $currency['currency_code'] . $result;
        }

        return $result;
    }

    protected function getDisabledCategories()
    {
        if (!isset($this->disabled_category_ids)) {
            $this->disabled_category_ids = array();
            $disable_categories_list = db_get_fields("SELECT id_path FROM ?:categories WHERE gml_disable_cat = ?s", 'Y');
            if (!empty($disable_categories_list)) {
                $like_path = "id_path LIKE '" . implode("%' OR id_path LIKE '", $disable_categories_list) . "%'"; // id_path LIKE '166/196%' OR id_path LIKE '203/204/212%' ...
                $this->disabled_category_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE ?p", $like_path);
            }
        }

        return $this->disabled_category_ids;
    }

    protected function getVisibleCategories()
    {
        $visible_categories = null;

        if (!isset($visible_categories)) {
            $visible_categories = array();

            $params = array(
                'plain' => true,
                'skip_default_condition' => true,
                'status' => array('A', 'H')
            );
            list($categories_tree, ) = fn_get_categories($params);

            if (!empty($categories_tree)) {
                foreach ($categories_tree as $value) {
                    if (isset($value['category_id'])) {
                        $visible_categories[] = $value['category_id'];
                    }
                }
            }
        }

        return $visible_categories;
    }

    protected function escape($data)
    {
        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

}
