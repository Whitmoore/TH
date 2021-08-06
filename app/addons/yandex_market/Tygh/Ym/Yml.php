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

namespace Tygh\Ym;

use Tygh\Registry;

class Yml implements IYml
{

    const ITERATION_ITEMS = 100;
    const IMAGES_LIMIT = 10;

    protected $company_id;
    protected $options = array();
    protected $lang_code = DESCR_SL;
    protected $page = 0;
    protected $debug = false;

    protected $disabled_category_ids;

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
        $path = sprintf('%syandex_market/%s_yandex_market.yml',
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
        $yml_header = array(
            '<?xml version="1.0" encoding="' . $this->options['export_encoding'] . '"?>',
            '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">',
            '<yml_catalog date="' . date('Y-m-d G:i') . '">',
            '<shop>'
        );

        $yml_data = array(
            'name' => $this->getShopName(),
            'company' => Registry::get('settings.Company.company_name'),
            'url' => Registry::get('config.http_location'),
            'enable_auto_discounts' => 1,
            'platform' => 'Tennis-Cart',
            'version' => '1.0',
            'agency' => 'Agency',
            'email' => Registry::get('settings.Company.company_orders_department'),
        );

        $this->buildCurrencies($yml_data);

        $this->buildCategories($yml_data);

        if ($this->options['local_delivery_cost'] == "Y") {
            $yml_data['delivery-options'] = array(
                'option@cost=500@days=0-1@order-before=24' => ''
            );
        }

        fwrite($file, implode(PHP_EOL, $yml_header) . PHP_EOL);
        fwrite($file, fn_yandex_market_array_to_yml($yml_data));
    }

    protected function body($file)
    {
        fwrite($file, '<offers>' . PHP_EOL);
        $offered = array();

        if ($this->options['disable_cat_d'] == "Y") {
            $visible_categories = $this->getVisibleCategories();
        }

        $free_ship = fn_get_promotion_data(FREE_SHIPPING_PROMO_ID);
        $free_ship_cat = fn_get_promotion_condition($free_ship, 'categories');
        
        $params = array(
            'yml_export_yes' => 'Y',
            'extend' => array('description', 'full_description'),
//             'pid' => 1655
        );
        list($products, ) = fn_get_products($params);

        $pay_on_delivery_limits = db_get_row("SELECT min_limit, max_limit FROM ?:payments WHERE payment_id = ?i", PAY_ON_DELIVERY_P_ID);
        
        fn_gather_additional_products_data($products, array(
            'get_icon' => false,
            'get_detailed' => true,
            'get_additional' => true,
            'get_options'=> true,
            'get_inventory' => true,
            'get_discounts' => true,
            'get_features' => true,
            'get_title_features' => true,
            'features_display_on' => 'A',
            'allow_duplication' => true,
            'display_variant_additional_pairs' => true
        ));

        $offset = 0;
        $global_data = array();
        $exceptions = unserialize(EXC_PRODUCT_ITEMS);
        $free_strings = array();
        while ($prods_slice = array_slice($products, $offset, self::ITERATION_ITEMS)) {
            $offset += self::ITERATION_ITEMS;
            $ids = array();
            foreach ($prods_slice as $k => &$product) {
                $ids[] = $product['product_id'];
            }
//             $products_images_main = fn_get_image_pairs($ids, 'product', 'M', false, true, $this->lang_code);
//             $products_images_additional = fn_get_image_pairs($ids, 'product', 'A', false, true, $this->lang_code);

            foreach ($prods_slice as $k => &$product) {
                $is_broken = false;

                $product['price'] = fn_parse_price($product['price']);

                if (empty($product['price'])) {
                    $is_broken = true;
                }

                if (in_array($product['main_category'], $this->disabled_category_ids)) {
                    $is_broken = true;
                }

                if ($this->options['disable_cat_d'] == 'Y' && !in_array($product['main_category'], $visible_categories)) {
                    $is_broken = true;
                }

                if (!isset($global_data[$product['main_category']])) {
                    $global_data[$product['main_category']] = fn_get_product_global_data($product, array('product_pretitle'));
                }
                // $product['product'] = $this->escape($product['product']);
                if (!empty($global_data[$product['main_category']]['product_pretitle']) && !in_array($product['product_id'], $exceptions)) {
                    $product['product'] = $global_data[$product['main_category']]['product_pretitle'] . ' ' . $product['product'];
                }
                $product['product'] .= ' ' . $product['product_code'];
                $product['full_description'] = !empty($product['full_description']) ? $this->escape($product['full_description']) : '';
                $product['product_features'] = $this->getProductFeatures($product);
                if (!empty($product['product_features'])) {
                    foreach ($product['product_features'] as $i => $f_data) {
                        $product['full_description'] .= (empty($product['full_description']) ? '' : '; ') . $f_data['name'] . ':' . $f_data['value'];
                    }
                }
                $product['brand'] = $this->getBrand($product);

                if ($this->options['export_type'] == 'vendor_model') {
                    if (empty($product['brand']) || empty($product['yml_model'])) {
                        $is_broken = true;
                    }
                }
                if (empty($product['full_description'])) {
                    $product['full_description'] = $product['product'];
                }

//                 if ($product['tracking'] == 'O') {
//                     $product['amount'] = db_get_field(
//                         "SELECT SUM(amount) FROM ?:product_warehouses_inventory WHERE product_id = ?i AND combination_hash != '0'",
//                         $product['product_id']
//                     );
//                 }

                if ($this->options['export_stock'] == 'Y' && $product['amount'] <= 0) {
                    $is_broken = true;
                }

                if ($is_broken) {
                    unset($prods_slice[$k]);
                    continue;
                }
                $product['item_id'] = fn_generate_cart_id($product['product_id'], array());
                $product['product_url'] = fn_url('products.view?product_id=' . $product['product_id']);

                // Images
                $images = array_merge(
                    array($product['main_pair']),
                    $product['image_pairs'] ?? array()
                );
                $product['images'] = array_slice($images, 0, self::IMAGES_LIMIT);

                if ($this->options['market_category'] == "Y" && empty($product['yml_market_category']) && !empty($product['all_path'])) {
                    $id_path = $cats = array();
                    foreach ($product['all_path'] as $i => $path) {
                        $cats[$i] = explode('/', $path);
                        $id_path = array_merge($id_path, explode('/', $path));
                    }
                    $main = $cats[$product['main_category']];
                    unset($cats[$product['main_category']]);
                    $invert = array();
                    foreach (array_reverse($main) as $i => $cid) {
                        $invert[$i][] = $cid;
                    }
                    if (!empty($cats)) {
                        foreach ($cats as $_i => $cids) {
                            foreach (array_reverse($cids) as $i => $cid) {
                                $invert[$i][] = $cid;
                            }
                        }
                    }
                    $_data = db_get_hash_array("SELECT category_id, yml_market_category FROM ?:categories WHERE category_id IN (?n)", 'category_id', array_unique($id_path));
                    foreach ($invert as $i => $cat_ids) {
                        foreach ($cat_ids as $n => $cat_id) {
                            if (!empty($_data[$cat_id]['yml_market_category'])) {
                                $product['yml_market_category'] = $_data[$cat_id]['yml_market_category'];
                                break 2;
                            }
                        }
                    }
                }

                $product['delivery-options'] = array();
                if ($product['yml_cost'] != 0) {
                    $product['delivery-options']['option@cost=' . $product['yml_cost'] . '@days=2-3@order-before=24'] = '';
                } else {
                    if ($product['price'] < Registry::get('addons.development.free_shipping_cost') || (!empty($free_ship_cat) && !in_array($product['main_category'], $free_ship_cat))) {
                        $product['delivery-options']['option@cost=' . $this->options['global_local_delivery_cost'] . '@days=2-3@order-before=24'] = '';
                    } else {
                        $product['delivery-options']['option@cost=300@days=2-3@order-before=24'] = '';
                    }
                    $product['delivery-options']['option@cost=500@days=0-1@order-before=24'] = '';
                }
                $product['pickup-options'] = array(
                    'option@cost=0@days=0-1' => ''
                );
                
                $product['yml_sales_notes'] .= ($product['yml_sales_notes'] != '' ? ' ' : '') . __('found_cheaper');
                
                if ((empty($pay_on_delivery_limits['min_limit']) || (!empty($pay_on_delivery_limits['min_limit']) && $pay_on_delivery_limits['min_limit'] <= $product['price'])) && (empty($pay_on_delivery_limits['max_limit']) || (!empty($pay_on_delivery_limits['max_limit']) && $pay_on_delivery_limits['max_limit'] >= $product['price']))) {
                    $product['yml_sales_notes'] .= ($product['yml_sales_notes'] != '' ? ' ' : '') . __('payment_on_delivery');
                }
                
                $use_group_id = false;
                if (in_array($product['main_category'], array(APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, BADMINTON_SHOES_CATEGORY_ID))) {
                    $use_group_id = true;
                }
                
                if (!empty($product['inventory'])) {
                    $iteration = 0;
                    $item_groups = array();
                    foreach ($product['inventory'] as $combination) {
                        $options = fn_get_product_options_by_combination($combination['combination']);
                        if (!empty($product['duplicated_data']) && !empty($options[$product['duplicated_data']['option_id']]) && $options[$product['duplicated_data']['option_id']] != $product['duplicated_data']['variant_id']) {
                            continue;
                        }
                        $item_groups[$iteration] = $product;
                        $item_groups[$iteration]['item_id'] = fn_generate_cart_id($product['product_id'], array('product_options' => $options), true);
                        if (!empty($product['free_strings'])) {
                            $free_strings[] = $item_groups[$iteration]['item_id'];
                        }
                        if (!empty($use_group_id)) {
//                             $item_groups[$iteration]['group_id'] = $product['product_id'];
                        }
                        $name_suffix = array();
                        foreach ($options as $opt_id => $vr_id) {
                            if (!empty($product['product_options'][$opt_id]) && $product['product_options'][$opt_id]['variants'][$vr_id]['variant_name'] !== false) {
                                if (empty($product['duplicated_data']) || $product['duplicated_data']['option_id'] != $opt_id) {
                                    $name_suffix[] = $product['product_options'][$opt_id]['option_name'] . ': ' . $product['product_options'][$opt_id]['variants'][$vr_id]['variant_name'];
                                }
                                if (empty($option_type) || !array_key_exists($opt_id, $option_type)) {
                                    if (preg_match('/(размер)/iu', $product['product_options'][$opt_id]['option_name'], $match)) {
                                        $option_type[$opt_id] = 'S';
                                    } elseif (preg_match('/(толщина|ручка)/iu', $product['product_options'][$opt_id]['option_name'], $match)) {
                                        $option_type[$opt_id] = 'G';
                                    } elseif (preg_match('/(цвет)/iu', $product['product_options'][$opt_id]['option_name'], $match)) {
                                        $option_type[$opt_id] = 'C';
                                    } else {
                                        $option_type[$opt_id] = 'N';
                                    }
                                }
                                if ($option_type[$opt_id] == 'S') {
                                    if (in_array($product['main_category'], array(SHOES_CATEGORY_ID, BADMINTON_SHOES_CATEGORY_ID))) {
                                        $unit = 'EU';
                                    } elseif ($opt_id == APPAREL_KIDS_SIZE_OPT_ID) {
                                        $unit = 'Height';
                                    } else {
                                        $unit = 'INT';
                                    }
                                    if ($opt_id == APPAREL_KIDS_SIZE_OPT_ID) {
                                        $item_groups[$iteration]['product_features'][] = array(
                                            'name' => 'Размер',
                                            'value' => YM_APPAREL_KIDS_SIZE_VARIANTS[$vr_id],
                                            'unit' => $unit
                                        );
                                    } else {
                                        $variant = $product['product_options'][$opt_id]['variants'][$vr_id]['variant_name'];
                                        if (preg_match('/\//iu', $product['product_options'][$opt_id]['variants'][$vr_id]['variant_name'], $match)) {
                                            $variants = explode('/', $variant);
                                            $iter = $variants[0];
                                            $new_vars = array();
                                            while ($iter <= $variants[1]) {
                                                $new_vars[] = $iter;
                                                $iter++;
                                            }
                                            $variant = implode(',', $new_vars);
                                            $unit = 'EU';
                                        }
                                        $item_groups[$iteration]['product_features'][] = array(
                                            'name' => 'Размер',
                                            'value' => $variant,
                                            'unit' => $unit
                                        );
                                    }
                                } elseif ($option_type[$opt_id] == 'G') {
                                        $item_groups[$iteration]['product_features'][] = array(
                                            'name' => 'Размер',
                                            'value' => $product['product_options'][$opt_id]['variants'][$vr_id]['variant_name']
                                        );
                                } elseif ($option_type[$opt_id] == 'C') {
                                    $item_groups[$iteration]['product_features'][] = array(
                                        'name' => 'Цвет',
                                        'value' => $product['product_options'][$opt_id]['variants'][$vr_id]['variant_name']
                                    );
                                }
                            }
                        }
                        if (!empty($name_suffix)) {
                            $item_groups[$iteration]['product'] .= ' (' . implode(', ', $name_suffix) . ')';
                        }
                        $iteration++;
                    }
                    if (!empty($item_groups)) {
                        foreach ($item_groups as $group) {
                            list($key, $value) = $this->offer($group);
                            $offered[$key] = $value;
                        }
                    }
                } else {
                    if (!empty($product['free_strings'])) {
                        $free_strings[] = $product['item_id'];
                    }
                    list($key, $value) = $this->offer($product);
                    $offered[$key] = $value;
                }
            }

            if (!empty($offered)) {
                fwrite($file, fn_yandex_market_array_to_yml($offered));
                unset($offered);
            }

        }
        fwrite($file, '</offers>' . PHP_EOL);
        
        if (!empty($free_strings)) {
            $promo = array(
                'gifts' => array(
                    'gift@id=1' => array(
                        'name' => __('yml_gift_free_stringing'),
                        'picture' => 'https://www.tennishouse.ru/images/watermarked/1/detailed/46/SONIC16-BK-12.jpg'
                    )
                ),
                'promos' => array(
                    'promo@id=FreeStrings@type=gift with purchase' => array(
                        'purchase' => array(
                            'required-quantity' => 1,
                        ),
                        'promo-gifts' => array(
                            'promo-gift@gift-id=1' => ''
                        )
                    )
                )
            );
            foreach ($free_strings as $offer_id) {
                $promo['promos']['promo@id=FreeStrings@type=gift with purchase']['purchase']['product@offer-id=' . $offer_id] = '';
            }
            fwrite($file, fn_yandex_market_array_to_yml($promo));
        }
    }

    protected function bottom($file)
    {
        fwrite($file, '</shop>' . PHP_EOL);
        fwrite($file, '</yml_catalog>' . PHP_EOL);
    }

    protected function sendResult($filename)
    {
        header("Content-Type: text/xml;charset=" . $this->options['export_encoding']);

        readfile($filename);
        exit;
    }

    protected function getShopName()
    {
        $shop_name = $this->options['shop_name'];

        if (empty($shop_name)) {
            if (fn_allowed_for('ULTIMATE')) {
                $shop_name = fn_get_company_name($this->company_id);
            } else {
                $shop_name = Registry::get('settings.Company.company_name');
            }
        }

        return strip_tags($shop_name);
    }

    protected function buildCurrencies(&$yml_data)
    {
        $currencies = Registry::get('currencies');

        if (CART_PRIMARY_CURRENCY != "RUB") {

            $rub_coefficient = !empty($currencies['RUB']) ? $currencies['RUB']['coefficient'] : 1;
            $primary_coefficient = $currencies[CART_PRIMARY_CURRENCY]['coefficient'];

            foreach ($currencies as $cur) {
                if ($this->currencyIsValid($cur['currency_code']) && $cur['status'] == 'A') {
                    if ($cur['currency_code'] == 'RUB') {
                        $coefficient = '1.0000';
                        $yml_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';

                    } else {
                        $coefficient = $cur['coefficient'] * $primary_coefficient / $rub_coefficient;
                        $yml_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';
                    }
                }
            }

        } else {
            foreach ($currencies as $cur) {
                if ($this->currencyIsValid($cur['currency_code']) && $cur['status'] == 'A') {
                    $yml_data['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $cur['coefficient']] = '';
                }
            }
        }
    }

    protected function currencyIsValid($currency)
    {
        $currencies = array(
            'RUR',
            'RUB',
            'UAH',
            'BYR',
            'KZT',
            'USD',
            'EUR'
        );

        return in_array($currency, $currencies);
    }

    protected function buildCategories(&$yml_data)
    {
        $params = array (
            'simple' => false,
            'plain' => true,
            'skip_filter' => true,
            'skip_default_condition' => true
        );

        if ($this->options['disable_cat_d'] == "Y") {
            $params['status'] = array('A', 'H');
        }

        $disable_cat_full_list = $this->getDisabledCategories();

        list($categories_tree, ) = fn_get_categories($params, $this->lang_code);

        foreach ($categories_tree as $cat) {
            if (isset($cat['category_id']) && !in_array($cat['category_id'], $disable_cat_full_list)) {

                if ($cat['parent_id'] == 0) {
                    $yml_data['categories']['category@id=' . $cat['category_id']] = $cat['category'];

                } else {
                    $yml_data['categories']['category@id=' . $cat['category_id'] . '@parentId=' . $cat['parent_id']] = $cat['category'];
                }
            }
        }
    }

    protected function getDisabledCategories()
    {
        if (!isset($this->disabled_category_ids)) {
            $this->disabled_category_ids = array();
            $disable_categories_list = db_get_fields("SELECT id_path FROM ?:categories WHERE yml_disable_cat = ?s", 'Y');
            if (!empty($disable_categories_list)) {
                $like_path = "id_path LIKE '" . implode("%' OR id_path LIKE '", $disable_categories_list) . "%'"; // id_path LIKE '166/196%' OR id_path LIKE '203/204/212%' ...
                $this->disabled_category_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE ?p", $like_path);
            }
        }

        return $this->disabled_category_ids;
    }

    /**
     * Export product features
     */
    protected function getProductFeatures($product)
    {
        $lang_code = $this->lang_code;

        $result = $skip = array();
        if (!empty($product['product_options'])) {
            foreach ($product['product_options'] as $option) {
                if (!empty($option['feature_id'])) {
                    $skip[] = $option['feature_id'];
                }
            }
        }

        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $f) {
                if (in_array($f['feature_id'], $skip)) {
                    continue;
                }
                if ($f['feature_type'] == "C") {
                    $result[] = array(
                        'name' => $f['description'],
                        'feature_id' => $f['feature_id'],
                        'value' => ($f['value'] == "Y") ? __("yes") : __("no")
                    );
                } elseif ($f['feature_type'] == "S" && !empty($f['variant'])) {
                    $result[] = array(
                        'name' => $f['description'],
                        'feature_id' => $f['feature_id'],
                        'value' => $f['variant']
                    );
                } elseif ($f['feature_type'] == "T" && !empty($f['value'])) {
                    $result[] = array(
                        'name' => $f['description'],
                        'feature_id' => $f['feature_id'],
                        'value' => $f['value']
                    );
                } elseif ($f['feature_type'] == "M") {
                    if (!empty($f['variants'])) {
                        $_value = '';
                        $counter = count($f['variants']);
                        foreach ($f['variants'] as $_variant) {
                            if ($counter > 1) {
                                $_value .= $_variant['variant'] . ', ';
                            } else {
                                $_value = $_variant['variant'];
                            }
                        }
                        $_value = ($counter > 1) ? substr($_value, 0, -2) : $_value;
                        $result[] = array(
                            'name' => $f['description'],
                            'feature_id' => $f['feature_id'],
                            'value' => $_value
                        );
                    }
                } elseif ($f['feature_type'] == "N") {
                    $result[] = array(
                        'name' => $f['description'],
                        'feature_id' => $f['feature_id'],
                        'value' => $f['variant']
                    );
                } elseif ($f['feature_type'] == "O") {
                    $result[] = array(
                        'name' => $f['description'],
                        'feature_id' => $f['feature_id'],
                        'value' => $f['value_int']
                    );
                } elseif ($f['feature_type'] == "E") {
                    $result[] = array(
                        'name' => $f['description'],
                        'feature_id' => $f['feature_id'],
                        'value' => $f['variant']
                    );
                }
            }
        }

        return $result;
    }

    protected function getImageUrl($image_pair)
    {
        $url = '';

        if ($this->options['image_type'] == 'detailed') {
            $url = $image_pair['detailed']['image_path'];
        } else {
            $image_data = fn_image_to_display(
                $image_pair,
                $this->options['thumbnail_width'],
                $this->options['thumbnail_height']
            );

            if (!empty($image_data) && strpos($image_data['image_path'], '.php')) {
                $image_data['image_path'] = fn_generate_thumbnail(
                    $image_data['detailed_image_path'],
                    $image_data['width'],
                    $image_data['height']
                );
            }

            if (!empty($image_data['image_path'])) {
                $url = $image_data['image_path'];
            }
        }

        $url = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

        $url = fn_yandex_market_c_encode($url);

        return str_replace('–', urlencode('–'), $url);
    }

    protected function getVisibleCategories()
    {
        $visible_categories = null;

        if (!isset($visible_categories)) {
            $visible_categories = array();

            if ($this->options['disable_cat_d'] == "Y") {
                $params['plain'] = true;
                $params['skip_default_condition'] = true;
                $params['status'] = array('A', 'H');
                list($categories_tree, ) = fn_get_categories($params);

                if (!empty($categories_tree)) {
                    foreach ($categories_tree as $value) {
                        if (isset($value['category_id'])) {
                            $visible_categories[] = $value['category_id'];
                        }
                    }
                }
            }
        }

        return $visible_categories;
    }

    protected function getMarketCategories()
    {
        static $market_categories = null;

        if (!isset($market_categories)) {
            $market_categories = array();

            if ($this->options['market_category'] == "Y" && $this->options['market_category_object'] == "category") {
                $market_categories = db_get_hash_single_array(
                    "SELECT category_id, yml_market_category FROM ?:categories WHERE yml_market_category != ?s",
                    array('category_id', 'yml_market_category'), ''
                );
            }
        }

        return $market_categories;
    }

    protected function getBrand($product)
    {
        $brand = '';

        if (!empty($product['yml_brand'])) {
            $brand = $product['yml_brand'];

        } elseif (!empty($product['product_features'])) {

            $feature_for_brand = $this->options['feature_for_brand'];
            $brands = array();

            if (!empty($feature_for_brand)) {

                foreach ($feature_for_brand as $brand_name => $check) {
                    if ($check == 'Y') {
                        $brands[] = $brand_name;
                    }
                }
                $brands = array_unique($brands);
            }

            foreach ($product['product_features'] as $feature) {
                if (in_array($feature['feature_id'], $brands)) {
                    $brand = $feature['value'];
                    break;
                }
            }
        }

        return $brand;
    }

    protected function offer($product)
    {
//     fn_print_r($product['images']);
        $yml_data = array();
        $offer_attrs = '';

        if (!empty($product['yml_bid'])) {
            $offer_attrs .= '@bid=' . $product['yml_bid'];
        }

        if (!empty($product['yml_cbid'])) {
            $offer_attrs .= '@cbid=' . $product['yml_cbid'];
        }

        $price_fields = array('price', 'yml_cost', 'list_price');

        if (CART_PRIMARY_CURRENCY != "RUB") {
            $currencies = Registry::get('currencies');
            if (isset($currencies['RUB'])) {
                $currency = $currencies['RUB'];
                foreach ($price_fields as $field) {
                    $product[$field] = fn_format_rate_value(
                        $product[$field],
                        'F',
                        $currency['decimals'],
                        $currency['decimals_separator'],
                        $currency['thousands_separator'],
                        $currency['coefficient']
                    );
                }
            }
        }

        foreach ($price_fields as $field) {
            $product[$field] = floatval($product[$field]) ? $product[$field] : fn_parse_price($product[$field]);
        }

        $yml_data['url'] = $product['product_url'];

        $yml_data['price'] = $product['price'];
        if (!empty($product['list_price']) && $product['price'] < $product['list_price']) {
            $yml_data['oldprice'] = $product['list_price'];
        }
        $yml_data['currencyId'] = "RUB";
        $yml_data['categoryId@type=Own'] = $product['main_category'];

        if ($this->options['market_category'] == "Y" && !empty($product['yml_market_category'])) {
            $yml_data['market_category'] = $product['yml_market_category'];
        }

        // Images
        $picture_index = 0;
        while ($image = array_shift($product['images'])) {
            $key = 'picture';
            if ($picture_index) {
                $key .= '+' . $picture_index;
            }
            $yml_data[$key] = $this->getImageUrl($image);

            $picture_index ++;
        }

        $yml_data['store'] = ($product['yml_store'] == 'Y' ? 'true' : 'false');
        $yml_data['pickup'] = ($product['yml_pickup'] == 'Y' ? 'true' : 'false');
//         if ($product['yml_pickup'] == 'Y') {
//             $yml_data['pickup-options'] = $product['pickup-options'];
//         }
        $yml_data['delivery'] = ($product['yml_delivery'] == 'Y' ? 'true' : 'false');
        if ($this->options['local_delivery_cost'] == "Y" && !empty($yml_data['delivery'])) {
            $yml_data['delivery-options'] = $product['delivery-options'];
        }

        $type = '';
        if ($this->options['export_type'] == 'vendor_model') {

            $type = '@type=vendor.model';

            if ($this->options['type_prefix'] == "Y") {
                if (!empty($product['yml_type_prefix'])) {
                    $yml_data['typePrefix'] = $product['yml_type_prefix'];

                } else {
                    $yml_data['typePrefix'] = $product['category'];
                }
            }

            $yml_data['vendor'] = $product['brand'];
            if ($this->options['export_vendor_code'] == 'Y' && !empty($product['product_code'])) {
                $yml_data['vendorCode'] = $product['product_code'];
            }
            $yml_data['model'] = !empty($product['yml_model']) ? $product['yml_model'] : '';

        } elseif ($this->options['export_type'] == 'simple') {
            $yml_data['name'] = $product['product'];

            if (!empty($product['brand'])) {
                $yml_data['vendor'] = $product['brand'];
            }

            if ($this->options['export_vendor_code'] == 'Y' && !empty($product['product_code'])) {
                $yml_data['vendorCode'] = $product['product_code'];
            }
        }

        if (!empty($product['full_description'])) {
            $yml_data['description'] = $product['full_description'];
        }

        if (!empty($product['yml_sales_notes'])) {
            $yml_data['sales_notes'] = $product['yml_sales_notes'];
        }

        if (!empty($product['yml_origin_country']) && fn_yandex_market_check_country($product['yml_origin_country'])) {
            $yml_data['country_of_origin'] = $product['yml_origin_country'];
        }

        if (!empty($product['yml_manufacturer_warranty'])) {
            $yml_data['manufacturer_warranty'] = $product['yml_manufacturer_warranty'];
        }

        if (!empty($product['yml_seller_warranty'])) {
            $yml_data['seller_warranty'] = $product['yml_seller_warranty'];
        }

        if (!empty($product['ean'])) {
            $yml_data['barcode'] = $product['ean'];
        }

        if (!empty($product['model'])) {
            $yml_data['model'] = $product['model'];
        }

        if (!empty($product['promo-gifts'])) {
            $yml_data['promo-gifts'] = $product['promo-gifts'];
        }

        if (!empty($product['group_id'])) {
            $yml_data['group_id'] = $product['group_id'];
        }

        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $feature) {
                $attr = '';
                foreach ($feature as $f_p => $f_v) {
                    if (!in_array($f_p, array('feature_id', 'value')) && $f_v !== false) {
                        $attr .= '@' . $f_p . '=' . $this->escape($f_v);
                    }
                }
                $yml_data['param' . $attr] = $feature['value'];
            }
        }

        $avail = 'true';

        return array(
            'offer@id=' . $product['item_id'] . $type . '@available=' . $avail . $offer_attrs,
            $yml_data
        );
    }

    protected function getVendorCode($product)
    {
        if (!empty($product['product_features'])) {
            foreach ($product['product_features'] as $feature) {
                if ($feature['description'] == $this->options['feature_for_vendor_code']) {
                    return $feature['value'];
                }
            }
        }

        return '';
    }

    protected function escape($data)
    {
        return htmlspecialchars(strip_tags($data), ENT_QUOTES, 'UTF-8');
    }

}
