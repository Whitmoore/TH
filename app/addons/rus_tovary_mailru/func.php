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

// rus_build_mailru dbazhenov

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_rus_tovary_mailru_url_auto()
{
    $key = Registry::get('addons.rus_tovary_mailru.cron_key');
    $company_id = Registry::get('runtime.simple_ultimate') ? Registry::get('runtime.forced_company_id') : Registry::get('runtime.company_id');
    $url = fn_url('exim.mailru_cron_export?cron_key=' . urlencode($key) . '&switch_company_id=' . $company_id , 'A');
    $text = __('mailru_export_auto_info') . '<br/ >' . $url ;

    $text =  '<br/ ><br/ >' . __('mailru_export_auto_info_header') . '<br/ ><br/ >' . $text . '<br /><br/ >' . __('mailru_export_auto_info_end') . '<br /><br/ >' . __('mailru_export_auto_info_bottom') ;

    $file = fn_get_files_dir_path() . Registry::get('addons.rus_tovary_mailru.cron_filename');
    $url_to_file = Registry::get('config.current_location') . '/' . fn_get_rel_dir($file);
    
    $text .= '<br/ ><br/ >' . __('mailru_export_auto_info_file') . '<br/ >' . $file . '<br/ ><a href =' . $url_to_file . '>' . $url_to_file . '</a>';

    return $text;
}

function fn_settings_variants_addons_rus_tovary_mailru_feature_for_brand()
{
    $join = "LEFT JOIN ?:product_features_descriptions as b ON a.feature_id = b.feature_id";
    $brands = db_get_hash_single_array("SELECT a.feature_id, b.description FROM ?:product_features as a $join WHERE a.feature_type = ?s AND b.lang_code = ?s", array('description', 'description'), "E", DESCR_SL);

    return $brands;
}

function fn_mailru_prepare_offer($_result, $options, $enclosure)
{
    //array error
    $error_products = array(
        'products_zero_price' => '',
        'disabled_products' => '',
        'out_of_stock' => '',
        'empty_brand' => '',
        'empty_model' => '',
        'disable_cat_list' => '',
        'disable_cat_list_d' => ''
    );

    //data addons mail
    $addon_settings = Registry::get('addons.rus_tovary_mailru');
    $delivery_type = $addon_settings['delivery_type'];

    list($_result, $product_ids) = fn_mailru_filter_products($_result, $addon_settings, $error_products);

    if (empty($_result)) {
        return true;
    }

    //category
    $visible_categories = fn_mailru_get_visible_categories($addon_settings);

    $yml_mailru_data = db_get_hash_array("SELECT product_id, mailru_brand, mailru_model, mailru_delivery, mailru_pickup, mailru_cost, mailru_type_prefix, mailru_mcp FROM ?:products WHERE product_id IN (?n)", 'product_id', $product_ids);
    $products_zero_price = '';

    foreach ($_result as $data) {
        $yml = array();
        if ($data['status'] != 'A') {
            continue;
        }

        if ($addon_settings['disable_cat_d'] == "Y") {
            if (!in_array($data['category'], $visible_categories)) {
                $error_products['disable_cat_list_d'] .= $data['product_name'] . ', ';
                continue;
            }
        }

        $avail = fn_is_accessible_product(array('product_id' => $data['product_id'])) ? 'true' : 'false';

        if (CART_PRIMARY_CURRENCY != "RUB") {

            $currencies = Registry::get('currencies');

            if (isset($currencies['RUB'])) {
                $currency = $currencies['RUB'];
                $price = fn_format_rate_value($data['price'], 'F', $currency['decimals'], $currency['decimals_separator'], $currency['thousands_separator'], $currency['coefficient']);
            } else {
                $price = $data['price'];
            }

            $price = !floatval($price) ? fn_parse_price($price) : $price;

            $delivery_cost = fn_mailru_format_price($yml_mailru_data[$data['product_id']]['mailru_cost'], "RUB");
            $delivery_cost = !floatval($delivery_cost) ? fn_parse_price($delivery_cost) : $delivery_cost;
        } else {
            $price = !floatval($data['price']) ? fn_parse_price($data['price']) : $data['price'];

            $delivery_cost = !floatval($yml_mailru_data[$data['product_id']]['mailru_cost']) ? fn_parse_price($yml_mailru_data[$data['product_id']]['mailru_cost']) : $yml_mailru_data[$data['product_id']]['mailru_cost'];
        }

        if (empty($price)) {
            $products_zero_price .= $data['product'] . ', ';
            continue;
        }

        $brand = fn_mailru_get_brand($data, $yml_mailru_data[$data['product_id']], $addon_settings);

        $url = fn_url($data['product_url']);
        $url = htmlentities($url);

        $offer_attrs = '';
        if (!empty($yml_mailru_data[$data['product_id']]['mailru_mcp'])) {
            $offer_attrs .= '@cbid=' . $yml_mailru_data[$data['product_id']]['mailru_mcp'];
        }

        if (CART_PRIMARY_CURRENCY == "RUB") {
            $currency_id = "RUR";
        } elseif (CART_PRIMARY_CURRENCY == "EUR") {
            $currency_id = "EURO";
        } else {
            $currency_id = CART_PRIMARY_CURRENCY ;
        }

        $image_url = fn_tovary_mailru_c_encode($data['image_url']);
        $s = urlencode("???");
        $image_url = str_replace("???", $s, $image_url);

        $yml['url'] = $url;
        $yml['price'] = $price;
        $yml['currencyId'] = $currency_id;
        $yml['categoryId'] = $data['category'];
        if (!empty($image_url)) {
            $yml['picture'] = $image_url;
        }

        if ($addon_settings['mail_settings'] == "type_name") {
            $yml['name'] = htmlspecialchars($data['product_name']);
        } elseif (($addon_settings['mail_settings'] == "type_detailed") && !empty($yml_mailru_data[$data['product_id']]['mailru_model'])) {
            if ($addon_settings['type_prefix'] == "Y") {
                if (!empty($yml_mailru_data[$data['product_id']]['mailru_type_prefix'])) {
                    $yml['typePrefix'] = $yml_mailru_data[$data['product_id']]['mailru_type_prefix'];
                } else {
                    $yml['typePrefix'] = $data['category_descriptions'];
                }
            }

            if (empty($brand)) {
                $error_products['empty_brand'] .= $data['product_name'] . ', ';
            } else {
                $yml['vendor'] = $brand;
            }

            $yml['model'] = $yml_mailru_data[$data['product_id']]['mailru_model'];
        } else {
            continue;
		}

        if (!empty($data['full_description'])) {
            $yml['description'] = $data['full_description'];
        }

        if (!empty($data['product_features'])) {
            foreach ($data['product_features'] as $feature) {
                $yml['param@name=' . fn_exim_mailru_get_product_info($feature['description'])] = $feature['value'];
            }
        }

        $yml['delivery'] = ($yml_mailru_data[$data['product_id']]['mailru_delivery'] == 'Y' ? 'true' : 'false');
        $yml['pickup'] = ($yml_mailru_data[$data['product_id']]['mailru_pickup'] == 'Y' ? 'true' : 'false');
        if ($addon_settings['local_delivery_cost'] == 'Y') {
            if ($delivery_cost == 0) {
                if ($delivery_type == 'value') {
                    $delivery_cost="0";
                    $yml['local_delivery_cost'] = $delivery_cost;
                } elseif ($delivery_type == 'free') {
                      $delivery_cost="???????????????????? ????????????????";
                    $yml['local_delivery_cost'] = $delivery_cost;
                }
            } else {
                $yml['local_delivery_cost'] = $delivery_cost;
            }
        }
        $yml_offers['offer@id=' . $data['product_id']  . '@available=' . $avail . $offer_attrs] = $yml;
        $_SESSION['mailru_export_count']++;
    }

    if ($products_zero_price) {
        fn_set_notification('W', __('error'), __('mailru_export_unsuccessfull') . $products_zero_price);
    }

    if (!empty($error_products) && $addon_settings['notify_disable_products'] == "Y") {
        foreach ($error_products as $key => $value) {
            if (!empty($value)) {
                fn_set_notification('W', __('error'), __('mailru_export_unsuccessfull_' . $key) . $value);
            }
        }
    }

    fn_mailru_write_yml($options['filename'], 'a+', fn_mailru_array_to_yml($yml_offers));

    return true;
}

function fn_mailru_check_currencies($currency)
{
    //only these currencies available
    $currencies = array(
        'RUB',
        'UAH',
        'BYR',
        'KZT',
        'USD',
        'EURO'
    );

    return in_array($currency, $currencies);
}

function fn_mailru_put_bottom($filename)
{
    $yml = array(
        '</offers>',
        '</shop>',
        '</torg_price>'
    );

    fn_mailru_write_yml($filename, 'a+', $yml);

    if (!defined('AJAX_REQUEST')) {
        $msg = str_replace(
            '[count]',
            $_SESSION['mailru_export_count'],
            __('mailru_export_export_true')
        );
        //fn_set_notification('N', __('notice'), $msg, 'S');
        fn_echo("<br/>" . $msg);
    }
    unset($_SESSION['mailru_export_count']);

    return true;
}

function fn_mailru_put_header($filename)
{
    $_SESSION['mailru_export_count'] = 0;

    $shop_name = Registry::get('addons.rus_tovary_mailru.shop_name');
    if (empty($shop_name)) {
        if (fn_allowed_for('ULTIMATE')) {
            $company_id = Registry::ifGet('runtime.company_id', fn_get_default_company_id());
            $shop_name = fn_get_company_name($company_id);
        } else {
            $shop_name = Registry::get('settings.Company.company_name');
        }
    }

    $shop_name = strip_tags($shop_name);

    $yml_header = array(
        '<?xml version="1.0" encoding="' . Registry::get('addons.rus_tovary_mailru.export_encoding') . '"?>',
        '<torg_price date="' . date('Y-m-d G:i') . '">',
        '<shop>'
    );

    $yml = array(
        'shopname' => $shop_name,
        'company' => Registry::get('settings.Company.company_name'),
        'url' => Registry::get('config.http_location'),
    );
    $currencies = Registry::get('currencies');

    if (CART_PRIMARY_CURRENCY != "RUB") {

        $rub_coefficient = !empty($currencies['RUB']) ? $currencies['RUB']['coefficient'] : 1;
        $primary_coefficient = $currencies[CART_PRIMARY_CURRENCY]['coefficient'];

        foreach ($currencies as $cur) {
            if (fn_mailru_check_currencies($cur['currency_code']) && $cur['status'] == 'A') {

                if ($cur['currency_code'] == "RUB") {
                    $coefficient = '1.0000';
                    $yml['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';

                } else {
                    $coefficient = $cur['coefficient'] * $primary_coefficient / $rub_coefficient;
                    $yml['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $coefficient] = '';
                }
            }
        }

    } else {
        foreach ($currencies as $cur) {
            if (fn_mailru_check_currencies($cur['currency_code']) && $cur['status'] == 'A') {
                $yml['currencies']['currency@id=' . $cur['currency_code'] . '@rate=' . $cur['coefficient']] = '';
            }
        }
    }

    $params = array (
        'simple' => false,
        'plain' => true,
    );

    if (fn_allowed_for('ULTIMATE') && is_numeric($shop_name)) {
        $params['company_ids'] = $shop_name;
    }

    list($categories_tree, ) = fn_get_categories($params);

    foreach ($categories_tree as $cat) {
        if (isset($cat['category_id'])) {
                $yml['categories']['category@id=' . $cat['category_id'] . '@parentId=' . $cat['parent_id']] = htmlspecialchars($cat['category']);
        }
    }

    $yml_data = implode("\n", $yml_header) . "\n" . fn_mailru_array_to_yml($yml) . "<offers>\n";
    fn_mailru_write_yml($filename, 'w+',$yml_data);
}

function fn_mailru_write_yml($filename, $mode, &$yml)
{
    $path = fn_get_files_dir_path();

    if (!is_dir($path)) {
        fn_mkdir($path);
    }

    $fd = fopen($path . $filename, $mode);

    if ($fd) {
        if (!is_array($yml)) {
            if (Registry::get('addons.rus_tovary_mailru.export_encoding') == 'windows-1251') {
                $yml = fn_convert_encoding('UTF-8', 'windows-1251', $yml, 'S');
            }

            fwrite($fd, $yml);

        } else {
            foreach ($yml as $key => $content) {
                $content = $content . "\n";
                if (Registry::get('addons.rus_tovary_mailru.export_encoding') == 'windows-1251') {
                    $content = fn_convert_encoding('UTF-8', 'windows-1251', $content, 'S');
                }
                fwrite($fd, $content);
                unset($yml[$key]);
            }
        }
        fclose($fd);
        @chmod($path . $filename, DEFAULT_FILE_PERMISSIONS);
    }
}

function fn_mailru_check_currency()
{
    $currencies = Registry::get('currencies');

    if (empty($currencies[CURRENCY_RUB]) || $currencies[CURRENCY_RUB]['is_primary'] != 'Y') {
        fn_set_notification('W', __('warning'), __('core.currency_is_absent', array(
            '[code]' => CURRENCY_RUB
        )));
    }
}

function fn_mailru_get_brand($data, $yml_mailru_data, $addon_settings)
{
    $brand = '';

    if (!empty($yml_mailru_data['mailru_brand'])) {
        $brand = $yml_mailru_data['mailru_brand'];

    } elseif (!empty($data['product_features'])) {
        $feature_for_brand = $addon_settings['feature_for_brand'];
        $brands = array();

        if (!empty($feature_for_brand)) {
            foreach ($feature_for_brand as $brand_name => $check) {
                if ($check == "Y") {
                    $brands[] = $brand_name;
                }
            }
            $brands = array_unique($brands);
        }

        foreach ($data['product_features'] as $feature) {
            if (in_array($feature['description'], $brands)) {
                $brand = $feature['value'];
                break;
            }
        }
    }

    return $brand;
}

//tovary null category
function fn_mailru_get_visible_categories($addon_settings)
{
    $visible_categories = null;

    if (!isset($visible_categories)) {
        $visible_categories = array();

        if ($addon_settings['disable_cat_d'] == "Y") {
            $params['plain'] = true;
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

//count 0 products
function fn_mailru_filter_products($_result, $addon_settings, $error_products)
{
    $product_ids = array();
    $products_filter = array();

    foreach ($_result as $product) {
        $price = !floatval($product['price']) ? fn_parse_price($product['price']) : $product['price'];

        if (empty($price) || $price == '0') {
            $error_products['products_zero_price'] .= $product['product_name'] . ', ';
            continue;
        }

        $tracking = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $product['product_id']);

        if (empty($tracking) || $tracking == 'O') {
            $product['amount'] = db_get_field("SELECT SUM(amount) FROM ?:product_warehouses_inventory WHERE product_id = ?i", $product['product_id']);
        }

        if ($addon_settings['export_stock'] == "Y" && $product['amount'] <= 0) {
            $error_products['out_of_stock'] .= $product['product_name'] . ', ';
            continue;
        }

        $product_ids[] = $product['product_id'];
        $products_filter[] = $product;
    }

    return array($products_filter, $product_ids);
}

function fn_tovary_mailru_c_encode($s)
{
    $rep = array(
        " "=> "%20",
        "??"=>"%D0%B0", "??"=>"%D0%90",
        "??"=>"%D0%B1", "??"=>"%D0%91",
        "??"=>"%D0%B2", "??"=>"%D0%92",
        "??"=>"%D0%B3", "??"=>"%D0%93",
        "??"=>"%D0%B4", "??"=>"%D0%94",
        "??"=>"%D0%B5", "??"=>"%D0%95",
        "??"=>"%D1%91", "??"=>"%D0%81",
        "??"=>"%D0%B6", "??"=>"%D0%96",
        "??"=>"%D0%B7", "??"=>"%D0%97",
        "??"=>"%D0%B8", "??"=>"%D0%98",
        "??"=>"%D0%B9", "??"=>"%D0%99",
        "??"=>"%D0%BA", "??"=>"%D0%9A",
        "??"=>"%D0%BB", "??"=>"%D0%9B",
        "??"=>"%D0%BC", "??"=>"%D0%9C",
        "??"=>"%D0%BD", "??"=>"%D0%9D",
        "??"=>"%D0%BE", "??"=>"%D0%9E",
        "??"=>"%D0%BF", "??"=>"%D0%9F",
        "??"=>"%D1%80", "??"=>"%D0%A0",
        "??"=>"%D1%81", "??"=>"%D0%A1",
        "??"=>"%D1%82", "??"=>"%D0%A2",
        "??"=>"%D1%83", "??"=>"%D0%A3",
        "??"=>"%D1%84", "??"=>"%D0%A4",
        "??"=>"%D1%85", "??"=>"%D0%A5",
        "??"=>"%D1%86", "??"=>"%D0%A6",
        "??"=>"%D1%87", "??"=>"%D0%A7",
        "??"=>"%D1%88", "??"=>"%D0%A8",
        "??"=>"%D1%89", "??"=>"%D0%A9",
        "??"=>"%D1%8A", "??"=>"%D0%AA",
        "??"=>"%D1%8B", "??"=>"%D0%AB",
        "??"=>"%D1%8C", "??"=>"%D0%AC",
        "??"=>"%D1%8D", "??"=>"%D0%AD",
        "??"=>"%D1%8E", "??"=>"%D0%AE",
        "??"=>"%D1%8F", "??"=>"%D0%AF"
    );

    $s = strtr($s, $rep);

    return $s;
}

function fn_mailru_array_to_yml($data, $level = 0)
{
    if (!is_array($data)) {
        return fn_html_escape($data);
    }

    $return = '';
    foreach ($data as $key => $value) {
        $attr = '';
        if (is_array($value) && is_numeric(key($value))) {
            foreach ($value as $k => $v) {
                $arr = array(
                    $key => $v
                );
                $return .= fn_array_to_xml($arr);
                unset($value[$k]);
            }
            unset($data[$key]);
            continue;
        }

        if (strpos($key, '@') !== false) {
            $data = explode('@', $key);
            $key = $data[0];
            unset($data[0]);

            if (count($data) > 0) {
                foreach ($data as $prop) {
                    if (strpos($prop, '=') !== false) {
                        $prop = explode('=', $prop);
                        $attr .= ' ' . $prop[0] . '="' . $prop[1] . '"';
                    } else {
                        $attr .= ' ' . $prop . '=""';
                    }
                }
            }
        }

        $tab = str_repeat("    ", $level);

        if (empty($value)) {
            if ($key == 'local_delivery_cost') {
                $return .= $tab . "<" . $key . $attr . ">" . fn_mailru_array_to_yml($value, $level + 1) . '</' . $key . ">\n";
            } else {
                $return .= $tab . "<" . $key . $attr . "/>\n";
            }

        } elseif (is_array($value)) {
            $return .= $tab . "<" . $key . $attr . ">\n" . fn_mailru_array_to_yml($value, $level + 1) . '</' . $key . ">\n";

        } else {
            $return .= $tab . "<" . $key . $attr . '>' . fn_mailru_array_to_yml($value, $level + 1) . '</' . $key . ">\n";
        }

    }

    return $return;
}

function fn_mailru_format_price($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
        if ($currencies[$payment_currency]['is_primary'] != 'Y') {
            $price = fn_format_price($price / $currencies[$payment_currency]['coefficient']);
        }
    } else {
        return false;
    }

    return $price;
}

function fn_mailru_format_price_down($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
          $price = fn_format_price($price * $currencies[$payment_currency]['coefficient']);
    } else {
        return false;
    }

    return $price;
}
