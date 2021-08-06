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

use Tygh\Memcache;
use Tygh\Registry;
use Tygh\LogFacade;
use Tygh\Http;
use Tygh\FeaturesCache;
use Tygh\Menu;
use Tygh\Shippings\Shippings;
use Tygh\Settings;
use Tygh\Enum\ProductTracking;
use Tygh\Shippings\RusSdek;
use Tygh\Sync\Sync;
use Tygh\Navigation\LastView;
use Tygh\Ym\Yml;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_create_order_customer($order_id, $auth)
{
    $user_data = fn_get_order_info($order_id, false, true, true, true);
    $user_data['status'] = 'A';
    $res = fn_update_user(0, $user_data, $auth, false, false, false, true);
    if (!empty($res)) {
        list($user_id, $profile_id) = $res;
        db_query("UPDATE ?:orders SET user_id = ?i WHERE order_id = ?i", $user_id, $order_id);

        return $user_id;
    }

    return false;
}

function fn_save_checkout_step($cart, $user_id = 0, &$edit_step)
{
    $_edit_step = 'step_one';

    $profile_fields = fn_get_profile_fields('O');
    $billing_population = fn_check_profile_fields_population($cart['user_data'], 'B', $profile_fields);
    if ($billing_population == true || empty($profile_fields['B'])) {
        $shipping_population = fn_check_profile_fields_population($cart['user_data'], 'S', $profile_fields);
        if ($shipping_population == true || empty($profile_fields['S'])) {
            $_edit_step = 'step_two';
        }
    }

    if ($_edit_step == 'step_two' && !empty($cart['chosen_shipping'])) {
        $_edit_step = 'step_three';
    }
    if ($_edit_step == 'step_three' && !empty($cart['payment_id'])) {
        $_edit_step = 'step_four';
    }
    $edit_step = $_edit_step;

    $step = 0;
    if ($edit_step == 'step_one' || $edit_step == 'step_two') {
        $step = 1;
    } elseif ($edit_step == 'step_three') {
        $step = 2;
    } elseif ($edit_step == 'step_four') {
        $step = 3;
    }

    $user_type = 'R';
    if (empty($user_id)) {
        if (fn_get_session_data('cu_id')) {
            $user_id = fn_get_session_data('cu_id');
        } else {
            $user_id = fn_crc32(uniqid(TIME));
            fn_set_session_data('cu_id', $user_id, COOKIE_ALIVE_TIME);
        }
        $user_type = 'U';
    }

    if (!empty($user_id)) {

        $condition = db_quote(" AND user_id = ?i AND type = 'C' AND user_type = ?s", $user_id, $user_type);
        if (fn_allowed_for('ULTIMATE')) {
            $condition .= fn_get_company_condition('?:user_session_products.company_id');
        }

        if (!empty($cart['products']) && is_array($cart['products'])) {
            db_query("UPDATE ?:user_session_products SET step = ?s WHERE item_id IN (?n) AND user_id = ?i AND type = 'C' AND user_type = ?s", $step, array_keys($cart['products']), $user_id, $user_type);
        }
    }

}

function fn_approximate_shipping(&$approx_shipping)
{
    if (empty($approx_shipping['time']) && (!empty($approx_shipping['city_id']) || (!empty($approx_shipping['country']) && !empty($approx_shipping['city'])))) {
        $approx_shipping['time'] = fn_get_approximate_shipping($approx_shipping);
    }
    if ($approx_shipping['city'] == 'Москва') {
        $approx_shipping['time'] = '0-2';
    }
    $approx_shipping['is_complete'] = true;
}

function fn_request_kladr($query)
{
    $result = array();
    $data = array(
        'token' => Registry::get('addons.development.kladr_token'),
        'query' => $query,
        'contentType' => 'city',
        'withParent' => '1',
        'limit' => '10'
    );
    $extra = array(
        'request_timeout' => 1,
        'timeout' => 1,
    );

    $response = json_decode(Http::get('https://kladr-api.ru/api.php', $data, $extra), true);

    if (empty($response)) {
        return false;
    }

    if (!empty($response['result'])) {
        foreach ($response['result'] as $i => $city) {
            if ($city['id'] == 'Free') {
                continue;
            }
            $variant = array(
                'city_id' => $city['id'],
                'city_id_type' => 'kladr',
                'city' => $city['name'],
                'value' => $city['name'],
                'zip' => $city['zip'],
                'label' => (!empty($city['typeShort']) ? $city['typeShort'] . '. ' : '') . $city['name'],
                'country_code' => 'RU',
                'state' => '',
                'country' => 'Россия'
            );
            if (!empty($city['parents'])) {
                foreach (array_reverse($city['parents']) as $pnt) {
                    $variant['label'] .= ', ' . $pnt['name'] . (!empty($pnt['typeShort']) ? ' ' . $pnt['typeShort'] : '');

                    if ($pnt['contentType'] == 'region') {
                        $variant['state_raw'] = $pnt['name'];
                    }
                }
            } else {
                $variant['state_raw'] = $city['name'];
            }
            $variant['label'] .= ', Россия';
            $result[] = $variant;
        }
    }

    return $result;
}

function fn_update_sdek_cities()
{
    $details = RusSdek::PullAllCities();

    return array(true, $details);
}

function fn_reset_shipping(&$product_groups)
{
    foreach ($product_groups as &$group) {
        unset($group['shippings']);
    }
}

function fn_check_user_session_data(&$user_data)
{
    if (!empty($_SESSION['approx_shipping'])) {
        if (!empty($_SESSION['approx_shipping']['country'])) {
            if (empty($user_data['b_country'])) {
                $user_data['b_country'] = $_SESSION['approx_shipping']['country'];
            }
            if (empty($user_data['s_country'])) {
                $user_data['s_country'] = $_SESSION['approx_shipping']['country'];
            }
        }
        if (!empty($_SESSION['approx_shipping']['state'])) {
            if (empty($user_data['b_state']) && $user_data['b_country'] == $_SESSION['approx_shipping']['country']) {
                $user_data['b_state'] = $_SESSION['approx_shipping']['state'];
            }
            if (empty($user_data['s_state']) && $user_data['s_country'] == $_SESSION['approx_shipping']['country']) {
                $user_data['s_state'] = $_SESSION['approx_shipping']['state'];
            }
        }
        if (!empty($_SESSION['approx_shipping']['city'])) {
            if (empty($user_data['b_city']) && empty($user_data['b_city_id']) && $user_data['b_country'] == $_SESSION['approx_shipping']['country'] && $user_data['b_state'] == $_SESSION['approx_shipping']['state']) {
                $user_data['b_city'] = $_SESSION['approx_shipping']['city'];
                if (!empty($_SESSION['approx_shipping']['city_id']) && !empty($_SESSION['approx_shipping']['city_id_type'])) {
                    $user_data['b_city_id'] = $_SESSION['approx_shipping']['city_id'];
                    $user_data['b_city_id_type'] = $_SESSION['approx_shipping']['city_id_type'];
                }
            }
            if (empty($user_data['s_city']) && empty($user_data['s_city_id']) && $user_data['s_country'] == $_SESSION['approx_shipping']['country'] && $user_data['s_state'] == $_SESSION['approx_shipping']['state']) {
                $user_data['s_city'] = $_SESSION['approx_shipping']['city'];
                if (!empty($_SESSION['approx_shipping']['city_id']) && !empty($_SESSION['approx_shipping']['city_id_type'])) {
                    $user_data['s_city_id'] = $_SESSION['approx_shipping']['city_id'];
                    $user_data['s_city_id_type'] = $_SESSION['approx_shipping']['city_id_type'];
                }
            }
        }
    }
}

function fn_archieve_order_data()
{
    $details = array();

    ini_set('memory_limit', '1024m');
    $orders_data = db_get_hash_multi_array("SELECT od.*, o.timestamp FROM ?:order_data AS od INNER JOIN ?:orders AS o ON o.order_id = od.order_id WHERE o.is_archieved = 'N' AND o.timestamp < ?i AND o.status IN (?a)", array('order_id', 'type'), TIME - SECONDS_IN_DAY * Registry::get('addons.development.archive_orders_time'), ORDER_BACKUP_STATUSES);

    foreach ($orders_data as $o_id => $types) {
        $content = array();
        foreach ($types as $type => $o_data) {
            $content[$type] = $o_data['data'];
            $year = date('Y', $o_data['timestamp']);
        }
        fn_put_contents(DIR_ROOT . '/var/order_data/' . $year . '/' . $o_id . '.txt', serialize($content), '', 0777);
        $details[] = $o_id;
    }

    db_query("DELETE FROM ?:order_data WHERE order_id IN (?a)", array_keys($orders_data));
    db_query("UPDATE ?:orders SET is_archieved = 'Y' WHERE order_id IN (?a)", array_keys($orders_data));

    return array(true, $details);
}

function fn_get_overdue_delivery_condition($table)
{
    return db_quote("$table.est_delivery_date != '0' AND $table.delivery_date = '0' AND $table.est_delivery_date < ?i AND $table.status IN (?a)", TIME, ORDER_DELIVERY_STATUSES);
}

function fn_get_working_date($number, $tmstp = TIME)
{
    $tfh = 60 * 60 * 24;

    $_calendar = db_get_hash_single_array("SELECT * FROM ?:calendar", array('year', 'calendar'));
    $calendar = array();
    foreach ($_calendar as $i => $y_data) {
        $year = unserialize($y_data);
        foreach ($year as $j => $m_data) {
            $days = explode(',', $m_data['days']);
            foreach ($days as $day) {
                if (strpos($day, '*') === false) {
                    $calendar[$i][$m_data['month']][$day] = 'H';
                } else {
                    $calendar[$i][$m_data['month']][(int)$day] = 'W';
                }
            }

        }
    }

    $date = getdate($tmstp);
    while ($number > 0) {
        $tmstp += $tfh;
        $date = getdate($tmstp);
        if (empty($calendar[$date['year']][$date['mon']])) {
            return false;
        }
        $is_workday = true;
        if ((in_array($date['wday'], array('6', '0')) &&
        (empty($calendar[$date['year']][$date['mon']][$date['mday']]) || $calendar[$date['year']][$date['mon']][$date['mday']] != 'W')) ||
        (!in_array($date['wday'], array('6', '0')) &&
        !empty($calendar[$date['year']][$date['mon']][$date['mday']]) && $calendar[$date['year']][$date['mon']][$date['mday']] == 'H')) {
            $is_workday = false;
        }

        if (!empty($is_workday)) {
            $number--;
        }
    }

    return mktime(23, 59, 59, $date['mon'], $date['mday'], $date['year']);
}

function fn_download_calendar()
{
    $extra = array(
        'request_timeout' => 2,
        'timeout' => 1,
    );
    $year = date("Y");
    $year++;
    $response = Http::get('http://xmlcalendar.ru/data/ru/' . $year . '/calendar.json', array(), $extra);

    if (Http::getStatus() == Http::STATUS_OK && !empty($response)) {
        $json = json_decode($response, true);
        if (!empty($json['months'])) {
            $data = array(
                'year' => $year,
                'calendar' => serialize($json['months'])
            );
            db_query("REPLACE INTO ?:calendar ?e", $data);
        }
    }

    return array(true, array());
}

function fn_install_cron_settings()
{

    $schema = fn_get_schema('cron', 'schema', 'php', true);
    $section_id = db_get_field("SELECT section_id FROM ?:settings_sections WHERE name = 'Cron'");
    if (empty($section_id)) {
        $s_data = array(
            'parent_id' => 0,
            'edition_type' => 'ROOT',
            'name' => 'Cron',
            'position' => '100',
            'type' => 'CORE'
        );
        $section_id = db_query("REPLACE INTO ?:settings_sections ?e", $s_data);
        foreach (fn_get_translation_languages() as $lang_code => $_v) {
            $s__data = array(
                'object_id' => $section_id,
                'object_type' => 'S',
                'lang_code' => $lang_code,
                'value' => 'Планировщик'
            );
            db_query("REPLACE INTO ?:settings_descriptions ?e", $s__data);
        }
    }
    if (!empty($section_id)) {

        $objects = db_get_hash_single_array("SELECT object_id, name FROM ?:settings_objects WHERE section_id = ?i", array('name', 'object_id'), $section_id);

        $position = 0;
        $installed = array();
        foreach ($schema as $type => $data) {

            $installed[] = 'cron_script_' . $type;
            if (empty($objects['cron_script_' . $type])) {
                $_data = array(
                    'name' => 'cron_script_' . $type,
                    'section_id' => $section_id,
                    'section_tab_id' => '0',
                    'type' => 'C',
                    'value' => 'Y',
                    'position' => $position,
                    'is_global' => 'N'
                );
                $id = db_query("REPLACE INTO ?:settings_objects ?e", $_data);
                foreach (fn_get_translation_languages() as $lang_code => $_v) {
                    $__data = array(
                        'object_id' => $id,
                        'object_type' => 'O',
                        'lang_code' => $lang_code,
                        'value' => __($data['name'])
                    );
                    db_query("REPLACE INTO ?:settings_descriptions ?e", $__data);
                }
            }
            $position++;
        }

        $to_delete = array_diff(array_keys($objects), $installed);
        if (!empty($to_delete)) {
            foreach ($to_delete as $name) {
                db_query("DELETE FROM ?:settings_objects WHERE object_id = ?i", $objects[$name]);
                db_query("DELETE FROM ?:settings_descriptions WHERE object_id = ?i", $objects[$name]);
            }
        }
    }
}

function fn_get_promotion_condition($conditions, $condition)
{
    if (!empty($conditions['condition'])) {
        if ($conditions['condition'] == $condition) {
            return explode(',', $conditions['value']);
        }
    } elseif (is_array($conditions)) {
        foreach ($conditions as $conds) {
            $val = fn_get_promotion_condition($conds, $condition);
            if (!empty($val)) {
                return $val;
            }
        }
    } elseif (!empty($conditions['conditions'])) {
        return fn_get_promotion_condition($conditions['conditions'], $condition);
    }

    return false;
}

function fn_update_xml_feed()
{
    $errors = array();
    Yml::clearCaches();

    $company_id = 1;
    $options = Registry::get('addons.yandex_market');

    $yml = new Yml($company_id, $options);
    $filename = $yml->getFileName();
    if (!file_exists($filename)) {
        $yml->generate($filename);
    }

    return array(true, $errors);
}

function fn_print_tpl()
{
    $args = func_get_args();

    $prefix = '<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">';
    $suffix = '</ol><div style="clear:left;"></div>';

    if (!empty($args)) {
        echo($prefix);
        foreach ($args as $k => $v) {
            echo('<li><pre>' . print_r($v, true) . "\n" . '</pre></li>');
        }
        echo($suffix);
    }
}

function fn_get_cron_log_types()
{
    $scheme = fn_get_schema('cron', 'schema');

    $types = array();
    if (!empty($scheme)) {
        foreach ($scheme as $type => $data) {
            $types[$type] = __($data['name']);
        }
    }

    return $types;
}

function fn_run_cron_script($type, $data)
{
    if (function_exists($data['function'])) {
        $action = array(
            'type' => $type,
            'status' => 'P',
            'timestamp' => time()
        );
        $log_id = db_query("INSERT INTO ?:cron_logs ?e", $action);
        list($result, $results) = call_user_func($data['function']);
        db_query("UPDATE ?:cron_logs SET status = ?s, results = ?s, timestamp_finish = ?i WHERE log_id = ?i", ($result ? 'S' : 'F'), serialize($results), time(), $log_id);
    }

    return true;
}

function fn_get_cron_logs($params, $items_per_page = 0)
{
    // Init filter
    $params = LastView::instance()->update('cron_logs', $params);

    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $sortings = array (
        'timestamp' => array ('?:cron_logs.timestamp', '?:cron_logs.log_id'),
        'type' => array ('?:cron_logs.type', '?:cron_logs.log_id'),
    );

    $fields = array (
        '?:cron_logs.*',
    );

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $join = "";

    $condition = '';

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($time_from, $time_to) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:cron_logs.timestamp >= ?i AND ?:cron_logs.timestamp <= ?i)", $time_from, $time_to);
    }

    if (!empty($params['type'])) {
        $condition .= db_quote(" AND ?:cron_logs.type = ?s", $params['type']);
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:cron_logs.log_id)) FROM ?:cron_logs ?p WHERE 1 ?p", $join, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $data = db_get_array("SELECT " . join(', ', $fields) . " FROM ?:cron_logs ?p WHERE 1 ?p $sorting $limit", $join, $condition);

    foreach ($data as $k => $v) {
        $data[$k]['results'] = !empty($v['results']) ? unserialize($v['results']) : array();
    }

    return array($data, $params);
}

function fn_synchronize_agents()
{
    $results = $status = array();
    list($status[DRIADA_WAREHOUSE_ID], $results[DRIADA_WAREHOUSE_ID]) = Sync::Synchronize(DRIADA_WAREHOUSE_ID);

    return array($status, $results);
}

function fn_generate_product_features_descriptions($product_ids)
{
    $result = array();
    $data = db_get_hash_multi_array("SELECT a.variant_id, a.product_id, a.feature_id, c.description_templates FROM ?:product_features_values AS a LEFT JOIN ?:product_features AS b ON a.feature_id = b.feature_id LEFT JOIN ?:product_feature_variant_descriptions AS c ON c.variant_id = a.variant_id AND c.lang_code = ?s WHERE a.product_id IN (?n) AND b.feature_type IN (?a) ORDER BY b.position ASC", array('product_id', 'feature_id'), CART_LANGUAGE, $product_ids, unserialize(DESCRIPTION_FEATURE_TYPES));

    if (!empty($data)) {
        foreach ($data as $product_id => $features) {
            $description = '';
            foreach ($features as $feature_id => $f_data) {
                if (!empty($f_data['description_templates'])) {
                    $vars = explode(PHP_EOL, trim($f_data['description_templates']));
                    $description .= ($description != '' && substr($description, -1) != '.' ? '.' : '') . ' ' . trim($vars[array_rand($vars)]);
                }
            }
            $result[$product_id] = $description;
        }
    }

    return array($result, $data);
}

function fn_apply_selected_options(&$product, $options, $exceptions = array())
{
    $product['selected_options'] = empty($product['selected_options']) ? array() : $product['selected_options'];

    if (!isset($product['options_type']) || !isset($product['exceptions_type'])) {
        $types = db_get_row('SELECT options_type, exceptions_type FROM ?:products WHERE product_id = ?i', $product['product_id']);
        $product['options_type'] = $types['options_type'];
        $product['exceptions_type'] = $types['exceptions_type'];
    }

    if (empty($product['product_options'])) {
        if (!empty($product['combination'])) {
            $selected_options = fn_get_product_options_by_combination($product['combination']);
        }

        $product['product_options'] = (!empty($selected_options)) ? fn_get_selected_product_options($product['product_id'], $selected_options, CART_LANGUAGE) : $options;
    }

    if (!empty($exceptions)) {
        $product['exceptions'] = $exceptions;
    }
    if (!empty($products_inventory)) {
        $product['inventory_combinations'] = $products_inventory[$product_id];
    }
    $product = fn_apply_options_rules($product);

    $product['has_options'] = !empty($product['product_options']);

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $product = fn_apply_exceptions_rules($product);
    }
    unset($product['exceptions']);
    unset($product['inventory_combinations']);

    // Change price
    $selected_options = isset($product['selected_options']) ? $product['selected_options'] : array();
    foreach ($product['product_options'] as $option) {
        if (!empty($option['disabled'])) {
            unset($selected_options[$option['option_id']]);
        }
    }

    $product['selected_options'] = $selected_options;
    if (empty($product['modifiers_price'])) {
        if (!isset($product['base_price'])) {
            $product['base_price'] = $product['price'];
        }
        $product['base_modifier'] = fn_apply_options_modifiers($selected_options, $product['base_price'], 'P', array(), array('product_data' => $product));
        $old_price = $product['price'];
        $product['price'] = fn_apply_options_modifiers($selected_options, $product['price'], 'P', array(), array('product_data' => $product));

        if (empty($product['original_price'])) {
            $product['original_price'] = $old_price;
        }

        $product['original_price'] = fn_apply_options_modifiers($selected_options, $product['original_price'], 'P', array(), array('product_data' => $product));
        $product['modifiers_price'] = $product['price'] - $old_price;
    }

    if (!empty($product['list_price'])) {
        $product['list_price'] = fn_apply_options_modifiers($selected_options, $product['list_price'], 'P', array(), array('product_data' => $product));
    }

    if (!empty($product['prices']) && is_array($product['prices'])) {
        foreach ($product['prices'] as $pr_k => $pr_v) {
            $product['prices'][$pr_k]['price'] = fn_apply_options_modifiers($selected_options, $pr_v['price'], 'P', array(), array('product_data' => $product));
        }
    }
}

function fn_rebuild_inventory_codes($product_id)
{
    $options = db_get_fields("SELECT a.option_id FROM ?:product_options as a LEFT JOIN ?:product_global_option_links as b ON a.option_id = b.option_id WHERE (a.product_id = ?i OR b.product_id = ?i) AND a.option_type IN ('S','R','C') AND a.inventory = 'Y' ORDER BY position", $product_id, $product_id);

    if (empty($options)) {
        db_query("DELETE FROM ?:product_options_inventory WHERE product_id = ?i", $product_id);
        return false;
    }

    foreach ($options as $k => $option_id) {
        $variants[$k] = db_get_fields("SELECT variant_id FROM ?:product_option_variants WHERE option_id = ?i ORDER BY position", $option_id);
        $variant_codes[$option_id] = db_get_hash_single_array("SELECT variant_id, code_suffix FROM ?:product_option_variants WHERE variant_id IN (?a)", array('variant_id', 'code_suffix'), $variants[$k]);
    }
    $product_code = db_get_field("SELECT product_code FROM ?:products WHERE product_id = ?i", $product_id);

    $combinations = fn_get_options_combinations($options, $variants);

    if (!empty($combinations)) {
        foreach ($combinations as $combination) {

            $combination_hash = fn_generate_cart_id($product_id, array('product_options' => $combination));
            $_product_code = (!empty($product_code)) ? $product_code : '';
            foreach ($combination as $option_id => $variant_id) {
                if (isset($variant_codes[$option_id][$variant_id])) {
                    $_product_code .= $variant_codes[$option_id][$variant_id];
                }
            }
            db_query("UPDATE ?:product_options_inventory SET product_code = ?s WHERE combination_hash = ?i", $_product_code, $combination_hash);
        }
    }

    return true;
}

function fn_delete_coupon($code)
{
    unset($_SESSION['coupons'][$code], $_SESSION['cart']['coupons'][$code]);
}

function fn_parse_catalog_promo(&$text, $prefix)
{
    if (preg_match_all('/\[apply_code\|(.*)\]/u', $text, $matches)) {
        foreach ($matches[1] as $i => $vl) {
            $values = explode('|', $vl);
            $params = array (
                'active' => true,
                'coupon_code' => $values[0]
            );
            list($coupon) = fn_get_promotions($params);

            if (in_array(strtolower(trim($values[0])), array_keys($_SESSION['coupons']))) {

                if (!empty($coupon)) {
                    $text = str_replace($matches[0][$i], '<div class="ty-catalog-promocode-block" id="catalog_promocode_' . $prefix . '">' . (!empty($values[2]) ? $values[2] : __('promo_applied')) .
                        ' <form action="' . fn_url() . '" method="post" name="form_link" class="cm-ajax cm-ajax-force cm-ajax-full-render">
                                <input type="hidden" name="catalog_coupon" value="' . $values[0] . '">
                                <input type="hidden" name="result_ids" value="product_details,pagination_contents,checkout*,cart_status*,cart_items,payment-methods,catalog_promocode*">
                                <input type="hidden" name="redirect_url" value="' . Registry::get('config.current_url') . '">
                                <button class="ty-button-link ty-btn" name="dispatch[development.delete_coupon]" type="submit">' . __('cancel') . '</button>
                            </form>
                        <!--catalog_promocode_' . $prefix . '--></div>',
                    $text);
                } else {
                    $text = str_replace($matches[0][$i], '', $text);
                }
            } else {
                if (!empty($coupon)) {
                    $text = str_replace($matches[0][$i],
                        '<div class="ty-catalog-promocode-block" id="catalog_promocode_' . $prefix . '">
                            <form action="' . fn_url() . '" method="post" name="form_link" class="cm-ajax cm-ajax-force cm-ajax-full-render">
                                <input type="hidden" name="catalog_coupon" value="' . $values[0] . '">
                                <input type="hidden" name="result_ids" value="product_details,pagination_contents,checkout*,cart_status*,cart_items,payment-methods,catalog_promocode*">
                                <input type="hidden" name="redirect_url" value="' . Registry::get('config.current_url') . '">
                                <button class="ty-button-link ty-btn" name="dispatch[development.apply_coupon]" type="submit">' . (!empty($values[1]) ? $values[1] : __('apply')) . '</button>
                            </form>
                        <!--catalog_promocode_' . $prefix . '--></div>',
                    $text);
                } else {
                    $text = str_replace($matches[0][$i], '', $text);
                }
            }
        }
    }
}

function fn_is_free_shipping($product)
{
    if ($product['price'] < Registry::get('addons.development.free_shipping_cost')) {
        return false;
    }

    $params = array(
        'active' => true,
        'expand' => true,
        'promotion_id' => FREE_SHIPPING_PROMO_ID,
    );

    list($promotions,) = fn_get_promotions($params);

    if (!empty($promotions[FREE_SHIPPING_PROMO_ID]['conditions'])) {
        $params = array(
            'ex_condition_name' => 'categories'
        );
        fn_remove_condition($promotions[FREE_SHIPPING_PROMO_ID], $params);
        $cart_products = array();
        if (!fn_promotion_check(FREE_SHIPPING_PROMO_ID, $promotions[FREE_SHIPPING_PROMO_ID]['conditions'], $product, $_SESSION['auth'], $cart_products)) {
            return false;
        }

        return true;
    }

    return false;
}

function fn_check_state(&$user_data)
{
    if (!empty($user_data['s_state_raw'])) {
        $state = fn_find_state_match($user_data['s_state_raw'], $user_data['s_country']);
        if (!empty($state['code'])) {
            $user_data['s_state'] = $state['code'];
        }
    }
    if (!empty($user_data['b_state_raw'])) {
        $state = fn_find_state_match($user_data['b_state_raw'], $user_data['b_country']);
        if (!empty($state['code'])) {
            $user_data['b_state'] = $state['code'];
        }
    }
}

function fn_save_cart_step($cart, $user_id, $edit_step)
{
    $step = 0;
    if ($edit_step == 'step_two') {
        $step = 1;
    } elseif ($edit_step == 'step_three') {
        $step = 2;
    } elseif ($edit_step == 'step_four') {
        $step = 3;
    }

    $user_type = 'R';
    if (empty($user_id)) {
        if (fn_get_session_data('cu_id')) {
            $user_id = fn_get_session_data('cu_id');
        } else {
            $user_id = fn_crc32(uniqid(TIME));
            fn_set_session_data('cu_id', $user_id, COOKIE_ALIVE_TIME);
        }
        $user_type = 'U';
    }

    if (!empty($user_id)) {

        $condition = db_quote(" AND user_id = ?i AND type = 'C' AND user_type = ?s", $user_id, $user_type);
        if (fn_allowed_for('ULTIMATE')) {
            $condition .= fn_get_company_condition('?:user_session_products.company_id');
        }

        if (!empty($cart['products']) && is_array($cart['products'])) {
            db_query("UPDATE ?:user_session_products SET step = ?s WHERE item_id IN (?n) AND user_id = ?i AND type = 'C' AND user_type = ?s", $step, array_keys($cart['products']), $user_id, $user_type);
        }
    }
}

function fn_check_delivery_statuses()
{
    $errors = $delivery_dates = array();
    $data = db_get_hash_array("SELECT ?:orders.order_id, ?:orders.status, GROUP_CONCAT(DISTINCT ?:shipment_items.shipment_id SEPARATOR ',') AS shipment_ids, delivery_date FROM ?:orders LEFT JOIN ?:shipment_items ON ?:shipment_items.order_id = ?:orders.order_id WHERE ?:orders.status IN (?a) GROUP BY order_id", 'order_id', unserialize(ORDER_CHECK_STATUSES));

    if (!empty($data)) {
        $_shipment_ids = array();
        foreach ($data as $order_id => $order_data) {
            $data[$order_id]['shipment_ids'] = explode(',', $order_data['shipment_ids']);
            $_shipment_ids = array_merge($_shipment_ids, $data[$order_id]['shipment_ids']);
        }
        $shipment_data = db_get_hash_array("SELECT shipment_id, shipping_id, timestamp FROM ?:shipments WHERE shipment_id IN (?n)", 'shipment_id', $_shipment_ids);
        foreach ($data as $order_id => $order_data) {
            $change_status = false;
            foreach ($order_data['shipment_ids'] as $i => $shipment_id) {
                if (!empty($shipment_data[$shipment_id])) {
                    $params_shipping = array(
                        'shipping_id' => $shipment_data[$shipment_id]['shipping_id'],
                        'Date' => date("Y-m-d", $shipment_data[$shipment_id]['timestamp']),
                    );
                    $data_auth = RusSdek::SdekDataAuth($params_shipping);
                    if (empty($data_auth)) {
                        continue;
                    }
                    $date_status = RusSdek::orderStatusXml($data_auth, $order_id, $shipment_id);
                    if (!empty($date_status)) {
                        RusSdek::SdekAddStatusOrders($date_status);
                        if (empty($order_data['delivery_date'])) {
                            foreach ($date_status as $s_id => $s_data) {
                                if (in_array($s_data['status'], array('Вручен', 'Не вручен', 'Принят на склад до востребования'))) {
                                    $delivery_dates[$order_id] = $s_data['timestamp'];
                                    break;
                                }
                            }
                        }
                        $last = array_pop($date_status);
                        if ($last['status'] == 'Вручен') {
                            $change_status = ORDER_STATUS_COMPLETED;
                        } elseif ($last['status'] == 'Не вручен') {
                            $change_status = ORDER_STATUS_NOT_DELIVERED;
                        } elseif ($last['status'] == 'Принят на склад до востребования') {
                            $change_status = ORDER_STATUS_WAITING_FOR_PICKUP;
                        }
                    }
                }
            }
            if (!empty($change_status) && $change_status != $order_data['status']) {
                fn_change_order_status($order_id, $change_status);
            }
        }

        if (!empty($delivery_dates)) {
            foreach ($delivery_dates as $o_id => $d_timestamp) {
                db_query("UPDATE ?:orders SET delivery_date = ?i WHERE order_id = ?i", $d_timestamp, $o_id);
            }
        }
    }

    return array(true, $errors);
}

function fn_seo_variants_allowed($type)
{
    return in_array($type, unserialize(SEO_VARIANTS_ALLOWED));
}

function fn_get_categories_subitems($cats)
{
    $subitems = array();
    if (!empty($cats)) {
        foreach ($cats as $c_id => $c_data) {
            $subitems[$c_id] = !empty($c_data['menu_subitems']) ? unserialize($c_data['menu_subitems']) : array();
        }
    }

    return $subitems;
}

function fn_get_generate_categories_menu_subitems()
{
    $errors = array();
    $cats = fn_get_categories_tree();
    $subitems = fn_top_menu_standardize($cats, 'category_id', 'category', 'subcategories', 'categories.view?category_id=');

    $join = db_quote(" LEFT JOIN ?:products ON ?:products.product_id = pfvl.product_id LEFT JOIN ?:product_feature_variants AS pfv ON pfvl.variant_id = pfv.variant_id LEFT JOIN ?:product_feature_variant_descriptions AS pfvd ON pfvd.variant_id = pfv.variant_id AND pfvd.lang_code = ?s INNER JOIN ?:products_categories AS pc ON pc.product_id = ?:products.product_id LEFT JOIN ?:categories ON ?:categories.category_id = pc.category_id " .
    "LEFT JOIN ?:product_warehouses_inventory as warehouse_inventory ON warehouse_inventory.product_id = ?:products.product_id
        AND warehouse_inventory.amount > 0 AND (CASE ?:products.tracking
            WHEN ?s THEN warehouse_inventory.combination_hash != '0'
            WHEN ?s THEN warehouse_inventory.combination_hash = '0'
            WHEN ?s THEN 1
        END)", CART_LANGUAGE,
        ProductTracking::TRACK_WITH_OPTIONS,
        ProductTracking::TRACK_WITHOUT_OPTIONS,
        ProductTracking::DO_NOT_TRACK
    );

    $condition = db_quote(
        " AND (CASE ?:products.tracking
            WHEN ?s THEN warehouse_inventory.amount > 0
            WHEN ?s THEN warehouse_inventory.amount > 0
            WHEN ?s THEN 1
        END)",
        ProductTracking::TRACK_WITH_OPTIONS,
        ProductTracking::TRACK_WITHOUT_OPTIONS,
        ProductTracking::DO_NOT_TRACK
    );

    $values_fields = array (
        'pfvl.feature_id',
        'COUNT(DISTINCT ?:products.product_id) as products',
        'pfvl.variant_id',
        'pfvd.variant'
    );

    foreach ($cats as $c_id => $c_data) {

        $cats_subitems = $features = array();
        $_condition = $condition;
        $category_ids = db_get_fields("SELECT ct1.category_id FROM ?:categories AS ct LEFT JOIN ?:categories AS ct1 ON ct1.id_path LIKE CONCAT(ct.id_path, '/%') WHERE ct.category_id = ?i", $c_id);
        $category_ids[] = $c_id;
        $limit = 2 - (!empty($subitems[$c_id]['subitems']) ? 1 : 0);
        $_condition .= db_quote(" AND pc.category_id IN (?n)", $category_ids);
        $features = db_get_hash_single_array("SELECT pf.feature_id, pfd.description FROM ?:product_features AS pf LEFT JOIN ?:product_features_descriptions AS pfd ON pf.feature_id = pfd.feature_id AND pfd.lang_code = ?s WHERE pf.feature_type IN (?a) AND pf.seo_variants = 'Y' AND (pf.categories_path = '' OR FIND_IN_SET(?i, pf.categories_path)) AND pf.parent_variant_id = '0' ORDER BY pf.position ASC LIMIT ?i", array('feature_id', 'description'), CART_LANGUAGE, unserialize(SEO_VARIANTS_ALLOWED), $c_id, $limit);

        if (!empty($features)) {
            foreach ($features as $f_id => $f_name) {
                $cats_subitems[$f_id] = db_get_hash_array("SELECT  " . implode(', ', $values_fields) . " FROM ?:product_features_values AS pfvl ?p WHERE pfvl.feature_id = ?i AND pfvl.lang_code = ?s AND ?:products.status IN ('A') ?p GROUP BY pfvl.variant_id ORDER BY pfv.position", 'variant_id', $join, $f_id, CART_LANGUAGE, $_condition);
            }
        }
        $_subitems = !empty($subitems[$c_id]['subitems']) ? $subitems[$c_id]['subitems'] : array();
        $subitems[$c_id]['subitems'] = array();
        if (count($_subitems) > 0) {
            $subitems[$c_id]['subitems']['subcategories'] = array(
                'object_id' => $c_id,
                'is_virtual' => 'Y',
                'descr' => $subitems[$c_id]['descr'],
                'subitems' => $_subitems
            );
        }

        foreach ($cats_subitems as $f_id => $f_data) {
            $subitems[$c_id]['subitems'][$f_id] = array(
                'object_id' => $f_id,
                'is_virtual' => 'Y',
                'is_feature' => 'Y',
                'descr' => $features[$f_id],
            );
            foreach ($f_data as $v_id => $v_data) {
                $subitems[$c_id]['subitems'][$f_id]['subitems'][$v_id] = array(
                    'object_id' => $v_id,
                    'is_virtual' => 'N',
                    'is_feature' => 'Y',
                    'descr' => $v_data['variant'],
                    'new_window' => false,
                    'param' => 'categories.view?category_id=' . $c_id . '&features_hash=V' . $v_id
                );
            }
        }
        db_query("UPDATE ?:categories SET menu_subitems = ?s WHERE category_id = ?i", serialize($subitems[$c_id]), $c_id);
    }

    return array(true, $errors);
}

function fn_get_features_by_variant($variant_id = 0, $params = array())
{
    $default_params = array(
        'variants' => true,
        'plain' => true,
        'exclude_group' => true
    );
    $params = array_merge($default_params, $params);
    list($features) = fn_get_product_features($params, 0, DESCR_SL);

    if (!empty($variant_id)) {
        $feature_id = db_get_field("SELECT feature_id FROM ?:product_feature_variants WHERE variant_id = ?i", $variant_id);
        if (!empty($feature_id)) {
            $features[$feature_id]['selected'] = true;
        }
    }

    return $features;
}

function fn_update_feature_seo($data, $item_id)
{
    if (!empty($data['features'])) {
        $data['combination'] = '';
        $v_ids = array();
        foreach ($data['features'] as $key => $v_id) {
            if (!empty($v_id) && !in_array($v_id, $v_ids)) {
                $data['combination'] .= (($data['combination'] != '') ? ',' : '') . $v_id;
                $v_ids[] = $v_id;
            } else {
                unset($data['features'][$key]);
            }
        }
        $data['item_id'] = fn_generate_cart_id($data['category_id'], array('combination_hash' => $data['combination']));
    }

    if (!empty($item_id)) {
        db_query("DELETE FROM ?:category_feature_seo WHERE item_id = ?i", $item_id);
    }
    db_query("REPLACE INTO ?:category_feature_seo ?e", $data);
}

function fn_delete_feature_seo($item_ids)
{
    db_query("DELETE FROM ?:category_feature_seo WHERE item_id IN (?n)", $item_ids);
}

function fn_get_feature_seo_data($fs_id)
{
    $feature_seo_data = db_get_array("SELECT a.* FROM ?:category_feature_seo as a WHERE item_id = ?i ORDER BY a.position ASC", $fs_id);

    return $feature_seo_data;
}

function fn_get_feature_seos($params)
{
    $features = $variants = $result = array();
    $where = '';
    if (!empty($params['category_id'])) {
        $where .= db_quote(" AND a.category_id = ?i", $params['category_id']);
    }
    if (!empty($params['item_id'])) {
        $where .= db_quote(" AND a.item_id = ?i", $params['item_id']);
    }
    if (AREA == 'C') {
        $where .= db_quote(" AND a.status = 'A'");
    }
    $feature_seos = db_get_hash_array("SELECT a.* FROM ?:category_feature_seo as a WHERE 1 $where ORDER BY a.position ASC", 'item_id');

    if (!empty($feature_seos)) {
        $feature_ids = $variant_ids = array();
        foreach ($feature_seos as $i => $fs) {
            $feature_seos[$i]['name'] = '';
            $tmp = explode(',', $fs['combination']);
            $feature_seos[$i]['features'] = db_get_hash_single_array("SELECT feature_id, variant_id FROM ?:product_feature_variants WHERE variant_id IN (?n)", array('feature_id', 'variant_id'), $tmp);
            $variant_ids = array_merge($variant_ids, $tmp);
            $feature_ids = array_merge($feature_ids, array_keys($feature_seos[$i]['features']));
        }
        if (!empty($params['get_descriptions']) && !empty($feature_seos[$i]['features'])) {
            $result['description']['features'] = db_get_hash_single_array("SELECT feature_id, description FROM ?:product_features_descriptions WHERE feature_id IN (?n) AND lang_code = ?s", array('feature_id', 'description'), $feature_ids, DESCR_SL);
            $result['description']['variants'] = db_get_hash_single_array("SELECT variant_id, variant FROM ?:product_feature_variant_descriptions WHERE variant_id IN (?n) AND lang_code = ?s", array('variant_id', 'variant'), $variant_ids, DESCR_SL);
        }
    }
    $result['data'] = $feature_seos;

    return $result;
}

function fn_clean_ranges_from_feature_hash($feature_hash, $ranges, $field_type = '')
{
    $hash = explode('.', $feature_hash);

    foreach ($ranges as $range) {
        $prefix = empty($field_type) ? (in_array($range['feature_type'], array('N', 'O', 'D')) ? 'R' : 'V') : $field_type;
        $key = array_search($prefix . $range['range_id'], $hash);
        if ($key !== false) {
            unset($hash[$key]);
        }
    }

    return implode('.', $hash);
}

function fn_get_location_by_ip()
{
    $data = array();
    $data['ip'] = $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? '178.45.22.0' : $_SERVER['REMOTE_ADDR'];

    // $extra = array(
    //     'request_timeout' => 2,
    //     'timeout' => 1,
    //     'headers' => array('Accept: application/json',  'Authorization: Token 103a9bfee0f97140574ab8adbcbbf75e9e98a28c')
    // );
    // $response = Http::get('https://suggestions.dadata.ru/suggestions/api/4_1/rs/detectAddressByIp',
    //     array('ip' => $data['ip']),
    //     $extra
    // );
    // $json = json_decode($response);
    // if (!empty($json->location->data)) {
    //     $data['city'] = strval($json->location->data->city);
    //     $data['city_id'] = strval($json->location->data->city_kladr_id);
    //     $data['city_id_type'] = 'kladr';
    //     $data['country'] = strval($json->location->data->country_iso_code);
    //     if (!empty($json->location->data->region) && $state = fn_find_state_match($json->location->data->region)) {
    //         $data['state'] = $state['code'];
    //         $data['state_id'] = $state['state_id'];
    //     }
    // }
    $extra = array(
        'request_timeout' => 2,
        'timeout' => 1,
    );
    $response = Http::get('http://ip-api.com/json/' . $data['ip'],
        array('lang' => 'ru'),
        $extra
    );
    $json = json_decode($response);
    if (!empty($json->status) && $json->status == 'success') {
        $data['city'] = strval($json->city);
        $data['country'] = strval($json->countryCode);
        if (!empty($json->region)) {
            $data['state'] = strval($json->region);
        } elseif (!empty($json->regionName) && $state = fn_find_state_match(strval($json->regionName))) {
            $data['state'] = $state['code'];
        }
    }

    return $data;
}

function fn_remove_condition(&$condition, $params)
{
    if (!empty($condition['conditions'])) {
        if (!empty($condition['conditions']['conditions'])) {
            fn_remove_condition($condition['conditions'], $params);
        } elseif (is_array($condition['conditions'])) {
            foreach ($condition['conditions'] as $key => $_condition) {
                if (!empty($_condition['condition']) && ((!empty($params['condition_name']) && $_condition['condition'] == $params['condition_name']) || (!empty($params['ex_condition_name']) && $_condition['condition'] != $params['ex_condition_name']) )) {
                    unset($condition['conditions'][$key]);
                }
            }
        }
    }

    return false;
}


function fn_find_cart_id($product_id, $list)
{
    if (!empty($list['products'])) {
        foreach ($list['products'] as $id => $data) {
            if ($data['product_id'] == $product_id) {
                return $id;
            }
        }
    }

    return false;
}

function fn_short_order_number($order_number)
{
    return substr($order_number, strlen($order_number) - 6, 6);
}

function fn_generate_rand($length = 6)
{
    $result = '';
    $chars = implode(range('0', '9'));

    for ($i = 0; $i < $length; $i++) {
        $result .= $chars[rand(0, strlen($chars) - 1)];
    }

    return $result;
}

function fn_recaptcha_image_verification_settings_proxy()
{
    $view = Registry::get('view');
    $settings = Settings::instance();
    $proxied_section = $settings->getSectionByName('Image_verification');
    $proxied_setting_objects = $settings->getList($proxied_section['section_id'], 0);

    $output = '';
    foreach ($proxied_setting_objects as $subsection_name => $setting_objects) {
        foreach ($setting_objects as $setting_object) {
            $view->assign('item', $setting_object);
            $view->assign('section', $proxied_section['section_id']);
            $view->assign('html_name', "addon_data[options][{$setting_object['object_id']}]");
            $view->assign('class', 'setting-wide');
            $view->assign('html_id', "addon_option_recaptcha_{$setting_object['name']}");

            $output .= $view->fetch('common/settings_fields.tpl');
        }
    }

    return $output;
}

function fn_promotion_validate_promo_code(&$promotion, &$cart, $promotion_id = 0)
{
    // Check already applied coupons
    if (!empty($cart['coupons'])) {
        db_query("DELETE FROM ?:promo_codes WHERE expire < ?i", TIME);
        $avail_codes = db_get_fields("SELECT promo_code FROM ?:promo_codes");
        $codes = array();
        if (!empty($avail_codes)) {
            $coupons = array_keys($cart['coupons']);
            foreach ($avail_codes as $expected_coupon_code) {
                if (in_array(strtolower($expected_coupon_code), $coupons)) {
                    $codes[] = $expected_coupon_code;
                }
            }
        }

        if (!empty($codes) && !empty($promotion_id)) {
            $promotion['value'] .= (empty($promotion['value']) ? '' : ',') . implode(',', $codes);
            foreach ($codes as $_code) {
                $_code = strtolower($_code);
                if (is_array($cart['coupons'][$_code]) && !in_array($promotion_id, $cart['coupons'][$_code])) {
                    $cart['coupons'][$_code][] = $promotion_id;
                }
            }
        }

        return $codes;
    }

    return false;
}

function fn_assign_user_posts($user_id)
{
    if (!empty($_SESSION['post_ids'])) {
        $settings = Registry::get('addons.development');

        list($post_ids, $review_ids) = fn_get_posts_object_ids($_SESSION['post_ids'], false);
        db_query("UPDATE ?:discussion_posts SET user_id = ?i WHERE post_id IN (?n)", $user_id, array_unique($post_ids));
        unset($_SESSION['post_ids']);

        if (!empty($review_ids)) {
            $exists = db_get_array("SELECT ?:discussion_posts.post_id, ?:discussion.object_id, ?:discussion.object_type FROM ?:discussion_posts LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id WHERE ?:discussion_posts.post_id IN (?n) AND ?:discussion_posts.is_rewarded = 'Y'", array_unique($review_ids));

            if (!empty($exists)) {
                foreach ($exists as $i => $post_data) {
                    fn_change_user_points($settings['review_reward_' . $post_data['object_type']], $user_id, serialize(array('post_id' => $post_data['post_id'], 'object_id' => $post_data['object_id'], 'type' => $post_data['object_type'])), CHANGE_DUE_REVIEW);
                }
            }
        }
    }
}

function fn_count_new_reviews($user_id, $object_type)
{
    $reviews = 0;
    if (!empty($user_id)) {
        $condition = '';
        $last_order = db_get_field("SELECT ?:orders.timestamp FROM ?:orders LEFT JOIN ?:status_data ON ?:status_data.status = ?:orders.status AND ?:status_data.type = 'O' WHERE ?:orders.user_id = ?i AND ?:status_data.param = 'inventory' AND ?:status_data.value = 'D' ORDER BY ?:orders.timestamp DESC", $user_id);
        if (!empty($last_order)) {
            $condition .= db_quote("AND ?:discussion_posts.timestamp > ?i", $last_order);
        }

        $reviews = db_get_field("SELECT COUNT(?:discussion_posts.post_id) FROM ?:discussion_posts LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id LEFT JOIN ?:discussion_messages ON ?:discussion_messages.post_id = ?:discussion_posts.post_id WHERE ?:discussion_posts.user_id = ?i AND ?:discussion.object_type = ?s AND ?:discussion_messages.message != '' $condition ", $user_id, $object_type);

    } elseif (!empty($_SESSION['post_ids'][$object_type])) {
        $settings = Registry::get('addons.development');

        if (!empty($_SESSION['auth']['order_ids'])) {
            $lst_order = db_get_field("SELECT ?:orders.timestamp FROM ?:orders LEFT JOIN ?:status_data ON ?:status_data.status = ?:orders.status AND ?:status_data.type = 'O' WHERE ?:orders.order_id IN (?n) AND ?:status_data.param = 'inventory' AND ?:status_data.value = 'D' ORDER BY ?:orders.timestamp DESC", $_SESSION['auth']['order_ids']);
        }
        $reviews = 0;
        foreach ($_SESSION['post_ids'][$object_type] as $i => $post) {
            if (!empty($post['message']) && (empty($lst_order) || $lst_order < $post['timestamp'])) {
                $reviews++;
            }
        }
    }

    return $reviews;
}

function fn_promotion_apply_cart_mod_rule($bonus, &$cart, &$auth, &$cart_products)
{
    if ($bonus['bonus'] == 'cart_product_discount') {
        if (!isset($cart['products'][$cart['promotion_item_id']]['promotions'][$bonus['promotion_id']])) {
            if (fn_promotion_apply_discount($bonus['promotion_id'], $bonus, $cart_products[$cart['promotion_item_id']])) {
                $cart['use_discount'] = true;
            }
        }
    } elseif ($bonus['bonus'] == 'discount_on_products') {
        if (fn_promotion_validate_attribute($cart_products[$cart['promotion_item_id']]['product_id'], $bonus['value'], 'in') && !isset($cart['products'][$cart['promotion_item_id']]['promotions'][$bonus['promotion_id']])) {
            if (fn_promotion_apply_discount($bonus['promotion_id'], $bonus, $cart_products[$cart['promotion_item_id']])) {
                $cart['use_discount'] = true;
            }
        }

    } elseif ($bonus['bonus'] == 'discount_on_categories') {
        if (fn_promotion_validate_attribute($cart_products[$cart['promotion_item_id']]['category_ids'], $bonus['value'], 'in') && !isset($cart['products'][$cart['promotion_item_id']]['promotions'][$bonus['promotion_id']])) {
            if (fn_promotion_apply_discount($bonus['promotion_id'], $bonus, $cart_products[$cart['promotion_item_id']])) {
                $cart['use_discount'] = true;
            }
        }

    }

    return true;
}

function fn_promotion_validate_catalog_coupon(&$promotion, $product, $promotion_id = 0)
{
    $values = fn_explode(',', $promotion['value']);

    // Check already applied coupons
    if (!empty($_SESSION['coupons'])) {
        $coupons = array_keys($_SESSION['coupons']);

        if ($promotion['operator'] == 'cont') {
            $codes = array();
            foreach ($coupons as $coupon_val) {
                foreach ($values as $cond_val) {
                    $cond_val = strtolower($cond_val);
                }
            }
        } else {
            $codes = array();
            foreach ($values as $expected_coupon_code) {
                if (in_array(strtolower($expected_coupon_code), $coupons)) {
                    $codes[] = $expected_coupon_code;
                }
            }
        }

        if (!empty($codes) && !empty($promotion_id)) {
            foreach ($codes as $_code) {
                $_code = strtolower($_code);
                if (is_array($_SESSION['coupons'][$_code]) && !in_array($promotion_id, $_SESSION['coupons'][$_code])) {
                    $_SESSION['coupons'][$_code][] = $promotion_id;
                }
            }
        }

        return $codes;
    }

    return false;
}

function fn_promotion_validate_no_list_discount(&$promotion, $product, $promotion_id = 0)
{
    if (!empty($product['list_price']) && $product['list_price'] > $product['price']) {
        return 'N';
    } else {
        return 'Y';
    }
}

function fn_promotion_validate_no_catalog_discount(&$promotion, $cart, $cart_products, $promotion_id = 0)
{
    if (!empty($cart['promotion_item_id']) && ((!empty($cart_products[$cart['promotion_item_id']]['list_price']) && $cart_products[$cart['promotion_item_id']]['list_price'] > $cart_products[$cart['promotion_item_id']]['price']) || !empty($cart_products[$cart['promotion_item_id']]['discount']) || !empty($cart_products[$cart['promotion_item_id']]['hidden_discount']))) {
        return 'N';
    } else {
        return 'Y';
    }
}

function fn_is_free_strings($product, $features)
{
    if ($product['main_category'] == RACKETS_CATEGORY_ID && !empty($features[R_STRINGS_FEATURE_ID]['value']) && $features[R_STRINGS_FEATURE_ID]['value'] == 'N' && in_array($features[TYPE_FEATURE_ID]['variant_id'], array(PRO_RACKET_FV_ID, CLUB_RACKET_FV_ID, POWER_RACKET_FV_ID, JUNIOR_RACKET_FV_ID))
        && (empty($product['discount_prc']) || $product['discount_prc'] < Registry::get('addons.development.free_strings_cond'))) {
        return true;
    } else {
        return false;
    }

}

function fn_promotion_validate_free_strings(&$promotion, $cart, $cart_products, $promotion_id = 0)
{
    static $features = array();

    if ($cart['products'][$cart['promotion_item_id']]['product_id'] == STRINGING_PRODUCT_ID && !empty($cart['products'][$cart['promotion_item_id']]['extra']['parent']['configuration']) && !empty($cart_products[$cart['products'][$cart['promotion_item_id']]['extra']['parent']['configuration']])) {
        $product = $cart_products[$cart['products'][$cart['promotion_item_id']]['extra']['parent']['configuration']];
        if ($product['main_category'] == RACKETS_CATEGORY_ID) {
            if (!isset($features[$product['product_id']])) {
                $features[$product['product_id']] = db_get_hash_array("SELECT feature_id, variant_id, value, value_int FROM ?:product_features_values WHERE product_id = ?i AND feature_id IN (?n) AND lang_code = ?s", 'feature_id', $product['product_id'], array(R_STRINGS_FEATURE_ID, TYPE_FEATURE_ID), CART_LANGUAGE);
            }
            if (!empty($features[$product['product_id']])) {
                return fn_is_free_strings($product, $features[$product['product_id']]) ? 'Y' : 'N';
            }
        }
    }

    return 'N';
}

function fn_promotion_validate_store_review(&$promotion, $auth, $promotion_id = 0)
{
    return fn_count_new_reviews($auth['user_id'], 'E');
}

function fn_promotion_validate_product_review(&$promotion, $auth, $promotion_id = 0)
{
    static $reviews;

    if (!isset($reviews)) {
        $reviews = fn_count_new_reviews($auth['user_id'], 'P');
    }

    return $reviews;
}

function fn_promotion_validate_ip_state($promotion)
{
    return !empty($_SESSION['ip_data']['state_id']) ? $_SESSION['ip_data']['state_id'] : false;
}

function fn_promotion_validate_ip_city($promotion)
{
    return !empty($_SESSION['ip_data']['city']) ? $_SESSION['ip_data']['city'] : false;
}

function fn_get_subscriber_statuses($lang_code = CART_LANGUAGE)
{
    $statuses = array (
        'C' => __('confirmed'),
        'P' => __('pending'),
        'L' => __('declined'),
    );

    return $statuses;
}

function fn_email_exist($emails)
{
    $results = array();

    foreach ($emails as $i => $email) {
        $results[$email] = false;
        $mailSegments = explode('@', $email);
        if (!empty($mailSegments[1])) {
            $domain = $mailSegments[1];
            if (substr($domain, -1) != '.') {
                $domain .= '.';
            }

            $mxRecordsAvailable = getmxrr($domain, $mxRecords, $mxWeight);
            if (!empty($mxRecordsAvailable)) {
                $mxHosts = array_combine($mxRecords,$mxWeight);
                asort($mxHosts, SORT_NUMERIC);
                $mxHost = array_keys($mxHosts)[0];
                if (!empty($mxHost)) {
                //    echo $mxHost . "<br>";
                    $mxSocket = fsockopen($mxHost, 25, $errno, $errstr, 2);
                    if (!empty($mxSocket)) {
                        $response = "";
                        // say HELO to mailserver
                        $response .= fn_send_command($mxSocket, "EHLO mx1.validemail.com");
                        // initialize sending mail
                        $response .= fn_send_command($mxSocket, "MAIL FROM:<info@tennishouse.ru>");
                        // try recipient address, will return 250 when ok..
                        $rcptText = fn_send_command($mxSocket, "RCPT TO:<" . $email . ">");
                        $response .= $rcptText;
                        if (substr($rcptText, 0, 3) == "250") {
                            $results[$email] = true;
                        }
                        // quit mail server connection
                        fn_send_command($mxSocket, "QUIT");
                        fclose($mxSocket);
                    }
                }
            }
        }
    }

    return $results;
}
function fn_send_command($mxSocket, $command)
{
//  print htmlspecialchars($command) . "<br>";
  fwrite($mxSocket, $command . "\r\n");
  $response = "";
  stream_set_timeout($mxSocket, 1);
  // Wait at most 10 * timeout for the server to respond.
  // In most cases the response arrives within timeout time and, therefore, there's no need to wait any longer in such
  // cases (i.e. keep timeout short). However, there are cases when a server is slow to respond and that for-loop will
  // give it some slack. See http://stackoverflow.com/q/36961265/131929 for the whole story.
  for ($i = 0; $i < 10; $i++) {
    while ($line = fgets($mxSocket)) {
      $response .= $line;
    }
    // Only continue the for-loop if the server hasn't sent anything yet.
    if ($response != "") {
      break;
    }
  }
//  print nl2br($response);
  return $response;
}

function fn_get_image_by_pair_id($pair_id)
{
    $icon_pairs = $detailed_pairs = $pair_data = array();

    $pair_data = db_get_row(
        "SELECT ?:images_links.*, icon_images.image_path AS icon_image_path, icon_desc.description AS icon_alt, icon_images.image_x AS icon_image_x, icon_images.image_y AS icon_image_y, icon_images.image_id as icon_image_id"
        . ", detailed_images.image_path AS detailed_image_path, detailed_desc.description AS detailed_alt, detailed_images.image_x AS detailed_image_x, detailed_images.image_y AS detailed_image_y, detailed_images.image_id as detailed_image_id"
        . " FROM ?:images_links"
        . " LEFT JOIN ?:images AS icon_images ON ?:images_links.image_id = icon_images.image_id"
        . " LEFT JOIN ?:common_descriptions AS icon_desc ON icon_desc.object_id = icon_images.image_id AND icon_desc.object_holder = 'images' AND icon_desc.lang_code = ?s"
        . " LEFT JOIN ?:images AS detailed_images ON ?:images_links.detailed_id = detailed_images.image_id"
        . " LEFT JOIN ?:common_descriptions AS detailed_desc ON detailed_desc.object_id = detailed_images.image_id AND detailed_desc.object_holder = 'images' AND detailed_desc.lang_code = ?s"
        . " WHERE ?:images_links.pair_id = ?i",
        CART_LANGUAGE, CART_LANGUAGE, $pair_id
    );

    if (!empty($pair_data['icon_image_id'])) { //get icon data if exist
        $tmp = $pair_data;
        $tmp['images_image_id'] = $pair_data['icon_image_id'];
        $tmp['image_path'] = $pair_data['icon_image_path'];
        $tmp['alt'] = $pair_data['icon_alt'];
        $tmp['image_x'] = $pair_data['icon_image_x'];
        $tmp['image_y'] = $pair_data['icon_image_y'];
        $icon = fn_attach_absolute_image_paths($tmp, $pair_data['object_type']);

        $pair_data['icon'] = array(
            'image_path' => $icon['image_path'],
            'alt' => $icon['alt'],
            'image_x' => $icon['image_x'],
            'image_y' => $icon['image_y'],
            'http_image_path' => $icon['http_image_path'],
            'absolute_path' => $icon['absolute_path'],
            'relative_path' => $icon['relative_path']
        );
    }

    if (!empty($pair_data['detailed_id'])) {
        $tmp = $pair_data;
        $tmp['images_image_id'] = $pair_data['detailed_image_id'];
        $tmp['image_path'] = $pair_data['detailed_image_path'];
        $tmp['alt'] = $pair_data['detailed_alt'];
        $tmp['image_x'] = $pair_data['detailed_image_x'];
        $tmp['image_y'] = $pair_data['detailed_image_y'];
        $detailed = fn_attach_absolute_image_paths($tmp, 'detailed');
        $pair_data['detailed'] = array(
            'image_path' => $detailed['image_path'],
            'alt' => $detailed['alt'],
            'image_x' => $detailed['image_x'],
            'image_y' => $detailed['image_y'],
            'http_image_path' => $detailed['http_image_path'],
            'absolute_path' => $detailed['absolute_path'],
            'relative_path' => $detailed['relative_path']
        );
    }

    return $pair_data;
}

function fn_get_posts_object_ids($posts, $keep_type = true)
{
    $post_ids = $review_ids = array();
    foreach ($posts as $obj_type => $_post_ids) {
        if (!empty($keep_type)) {
            $post_ids[$obj_type] = array_merge((empty($post_ids[$obj_type]) ? array() : $post_ids[$obj_type]), array_keys($_post_ids));
        } else {
            $post_ids = array_merge($post_ids, array_keys($_post_ids));
        }
        foreach ($_post_ids as $i => $_post) {
            if (!empty($_post['message'])) {
                if (!empty($keep_type)) {
                    $review_ids[$obj_type][] = $_post['post_id'];
                } else {
                    $review_ids[] = $_post['post_id'];
                }
            }
        }
    }

    return array($post_ids, $review_ids);
}

function fn_user_rated($discussion)
{
    $auth = $_SESSION['auth'];
    if (!empty($discussion['all_posts'])) {
        foreach ($discussion['all_posts'] as $i => $post) {
            if (!empty($post['rating_value'])) {
                if (!empty($auth['user_id']) && $post['user_id'] == $auth['user_id']) {
                    return $post;
                }
                if (empty($auth['user_id']) && !empty($_SESSION['post_ids'][$discussion['object_type']]) && in_array($post['post_id'], array_keys($_SESSION['post_ids'][$discussion['object_type']]))) {
                    return $post;
                }
            }
        }
    }

    return false;
}

function fn_get_product_review_discount(&$products)
{
    $result = false;
    $params = array(
        'active' => true,
        'expand' => true,
        'promotion_id' => REVIEW_PROMO_ID,
    );

    list($promotions,) = fn_get_promotions($params);

    $one_product = !is_array(reset($products));
    if (!empty($one_product)) {
        $products = array($products);
    }
    if (!empty($promotions[REVIEW_PROMO_ID]['conditions'])) {
        $params = array(
            'condition_name' => 'product_review'
        );
        fn_remove_condition($promotions[REVIEW_PROMO_ID], $params);
        foreach ($products as $i => &$product) {
            if (($promotions[REVIEW_PROMO_ID]['no_sum_up'] == 'N' || ($promotions[REVIEW_PROMO_ID]['no_sum_up'] == 'Y' && empty($product['discount']))) && empty($product['promotions'][REVIEW_PROMO_ID]) && ($promotions[REVIEW_PROMO_ID]['stop'] == 'N' || ($promotions[REVIEW_PROMO_ID]['stop'] == 'Y' && empty($product['discount'])))) {
                $cart_products = array();
                if (fn_promotion_check(REVIEW_PROMO_ID, $promotions[REVIEW_PROMO_ID]['conditions'], $product, $_SESSION['auth'], $cart_products) && !empty($promotions[REVIEW_PROMO_ID]['bonuses'])) {
                    foreach ($promotions[REVIEW_PROMO_ID]['bonuses'] as $bonus) {
                        if ($bonus['bonus'] == 'product_discount') {
                            $product['review_discount'] = $result = $bonus['discount_value'];
                        }
                    }
                }
            }
        }
    }
    if (!empty($one_product)) {
        $products = array_shift($products);
    }

    return $result;
}

function fn_allow_user_thread_review_reward($thread_id, $object_type, $user_id, $exclude_post_id = 0)
{
    $settings = Registry::get('addons.development');
    if ($settings['review_reward_' . $object_type] <= 0) {
        return false;
    }
    $now = getdate(TIME);
    $time_limit = mktime(0, 0, 0, $now['mon'] - $settings['review_time_limit_' . $object_type], $now['mday'], $now['year']);
    $count = 0;
    if (!empty($user_id)) {
        $posts = db_get_array("SELECT ?:discussion_posts.* FROM ?:discussion_posts LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id WHERE ?:discussion_posts.user_id = ?i AND ?:discussion.object_type = ?s", $user_id, $object_type);
    } elseif (AREA == 'C') {
        $posts = $_SESSION['post_ids'][$object_type];
    }
    if (!empty($posts)) {
        foreach ($posts as $i => $post) {
            if (!empty($post['is_rewarded']) && $post['is_rewarded'] == 'Y') {
                if ($post['timestamp'] >= $time_limit) {
                    $count++;
                }
                if ($post['thread_id'] == $thread_id && (empty($exclude_post_id) || (!empty($exclude_post_id) && $post['post_id'] != $exclude_post_id))) {
                    return false;
                }
            }
        }
    }

    return ($count < $settings['review_number_limit_' . $object_type]) ? true : false;
}

function fn_allow_user_review_reward($user_id, $object_type)
{
    $settings = Registry::get('addons.development');
    $now = getdate(TIME);
    $time_limit = mktime(0, 0, 0, $now['mon'] - $settings['review_time_limit_' . $object_type], $now['mday'], $now['year']);
    if (empty($user_id)) {
        $limit = 0;
        if (!empty($_SESSION['post_ids'][$object_type])) {
            foreach ($_SESSION['post_ids'][$object_type] as $post_id => $post_data) {
                if ($post_data['is_rewarded'] == 'Y' && $post_data['timestamp'] >= $time_limit) {
                    $limit++;
                }
            }
        }
    } else {
        $limit = db_get_field("SELECT COUNT(*) FROM ?:discussion_posts WHERE user_id = ?i AND is_rewarded = 'Y' AND timestamp >= ?i", $user_id, $time_limit);
    }

    return ($limit < $settings['review_number_limit_' . $object_type]) ? true : false;
}

function fn_add_img_alt($name, $product_name)
{
    if (!empty($_REQUEST[$name . '_image_data'])) {
        foreach ($_REQUEST[$name . '_image_data'] as $i => $pair) {
//             $_REQUEST[$name . '_image_data'][$i]['image_alt'] = empty($_REQUEST[$name . '_image_data'][$i]['image_alt']) ? $product_name : $_REQUEST[$name . '_image_data'][$i]['image_alt'];
//             $_REQUEST[$name . '_image_data'][$i]['detailed_alt'] = empty($_REQUEST[$name . '_image_data'][$i]['detailed_alt']) ? $product_name : $_REQUEST[$name . '_image_data'][$i]['detailed_alt'];
            $_REQUEST[$name . '_image_data'][$i]['new_alt'] = $product_name;
        }
    }
}

function fn_is_icon_feature($feature_id)
{
    return (in_array($feature_id, array(R_WEIGHT_FEATURE_ID, R_HEADSIZE_FEATURE_ID))) ? true : false;
}

function fn_check_category_discussion($id_path)
{
    $enable_discussion = array(RACKETS_CATEGORY_ID, BALLS_CATEGORY_ID, STRINGS_CATEGORY_ID, SHOES_CATEGORY_ID, OVERGRIPS_CATEGORY_ID, BALL_MACHINE_CATEGORY_ID, STR_MACHINE_CATEGORY_ID, TREADMILL_CATEGORY_ID, INDOOR_CYCLE_CATEGORY_ID, ELLIPTICAL_CATEGORY_ID);
    $disable_discussion = array(BALL_MACHINE_ACC_CATEGORY_ID);
    if (!empty(array_intersect($id_path, $enable_discussion)) && empty(array_intersect($id_path, $disable_discussion))) {
        return 'B';
    } else {
        return 'D';
    }
}

function fn_check_category_comparison($id_path)
{
    $enable_discussion = array(RACKETS_CATEGORY_ID, STRINGS_CATEGORY_ID, BALL_MACHINE_CATEGORY_ID, BAGS_CATEGORY_ID);
    $disable_discussion = array(BALL_MACHINE_ACC_CATEGORY_ID);
    if (!empty(array_intersect($id_path, $enable_discussion)) && empty(array_intersect($id_path, $disable_discussion))) {
        return 'Y';
    } else {
        return 'N';
    }
}

function fn_get_big_cities()
{
    $result = array();
    $city_codes = unserialize(BIG_CITIES_SDEK);
    $cities = db_get_hash_array("SELECT a.*, b.city FROM ?:rus_cities_sdek AS a LEFT JOIN ?:rus_city_sdek_descriptions AS b ON a.city_id = b.city_id AND b.lang_code = ?s WHERE city_code IN (?n)", 'city_code', CART_LANGUAGE, $city_codes);

    foreach ($city_codes as $code) {
        $result[$code] = $cities[$code];
        $result[$code]['city_id_type'] = 'sdek';
    }

    return $result;
}

function fn_get_approximate_shipping($location)
{
    $priority = array(COURIER_SH_ID, SDEK_STOCK_SH_ID, SDEK_DOOR_SH_ID, RU_POST_SH_ID);
    $shipping_time = 0;
    $group = array(
        'package_info' => array(
            'W' => 1,
            'C' => 5000,
            'I' => 1,
            'origination' => array(
                'city' => Registry::get('settings.Company.company_city'),
                'country' => Registry::get('settings.Company.company_country'),
                'state' => Registry::get('settings.Company.company_state'),
            ),
            'location' => $location
        ),
        'company_id' => 1
    );
    $shippings = array();
    $shippings_group = Shippings::getShippingsList($group);
    foreach ($shippings_group as $shipping_id => $shipping) {

        $_shipping = $shipping;
        $_shipping['package_info'] = $group['package_info'];
        $_shipping['keys'] = array(
            'shipping_id' => $shipping_id,
        );
        $shippings[] = $_shipping;

        $shipping['rate'] = 0;
    }

    $rates = Shippings::calculateRates($shippings);
    $est_ship = array();

    if (!empty($rates)) {
        foreach ($rates as $i => $sh_r) {
            if ($sh_r['price'] !== false && !empty($sh_r['delivery_time']) && empty($sh_r['error'])) {
                $est_ship[$sh_r['keys']['shipping_id']] = preg_replace('/[^\-0-9]/', '', $sh_r['delivery_time']);
            }
        }
        if (!empty($est_ship)) {
            foreach ($priority as $k => $sh_id) {
                if (!empty($est_ship[$sh_id])) {
                    return $est_ship[$sh_id];
                }
            }
            return reset($est_ship);
        }
    }

    return false;
}

function fn_get_similar_category_products($params)
{
    $result = array();
    $_params = array (
        'subcats' => 'Y',
        'sort_by' => 'popularity',
        'limit' => $params['limit']
    );
    if (!empty($_SESSION['product_category'])) {
        $_params['cid'] = $_SESSION['product_category'];
        list($result,) = fn_get_products($_params);
    }
    if (empty($result[0]) && !empty($_SESSION['main_product_category'])) {
        $_params['cid'] = $_SESSION['main_product_category'];
        list($result,) = fn_get_products($_params);
    }

    return array($result);
}

function fn_format_submenu(&$menu_items)
{
    if (!empty($menu_items)) {
        foreach ($menu_items as $j => $item) {
            if (!empty($item['subitems'])) {
                $menu_items[$j]['expand'] = false;
                if (!empty($item['is_virtual']) && $item['is_virtual'] == 'Y' && !empty($item['parent_id'])) {
                    $menu_items[$j]['href'] = 'categories.view?category_id=' . $item['parent_id'];
                }
                if (!empty($item['level']) && $item['level'] > 1 && count($menu_items[$j]['subitems']) < 2 && !empty($menu_items[$j]['is_feature']) && $menu_items[$j]['is_feature'] == 'Y') {
                    unset($menu_items[$j]);
                } else {
                    fn_format_submenu($menu_items[$j]['subitems']);
                }
            } elseif (!empty($item['level']) && $item['level'] > 1 && !empty($item['is_virtual']) && $item['is_virtual'] == 'Y' && !empty($menu_items[$j]['is_feature']) && $menu_items[$j]['is_feature'] == 'Y') {
                unset($menu_items[$j]);
            }
        }
    }
}

function fn_get_catalog_panel_categoies()
{
    $params = array(
        'param_3' => 'C:0:N'
    );
    $menu = fn_top_menu_form(array($params));
    $menu[0]['subitems'][] = array(
        'item' => __("sale_products"),
        'href' => 'products.sale',
        'object_id' => 'sale'
    );
    $menu_items = $menu[0]['subitems'];
    fn_format_submenu($menu_items);

    return $menu_items;
}

function fn_get_catalog_panel_pages()
{
//     $params = array(
//         'param_3' => 'A:53:Y'
//     );
//     $menu_items = array();
//     $players = array(
//         'item' => __("players"),
//         'href' => 'players.list',
//     );
//     $menu_items[] = $players;
//     $kb_items = fn_top_menu_form(array($params));
//     $menu_items[] = reset($kb_items);
//     $reviews = array(
//         'item' => __("players"),
//         'href' => 'players.list',
//     );
    $block['content']['menu'] = 2;
    $menu_items = fn_get_menu_items_th(true, $block, true);
    foreach ($menu_items as $i => $m_item) {
        if (in_array($m_item['id_path'], array(152, 153, 163))) {
            unset($menu_items[$i]);
        }
        if ($m_item['id_path'] == 158) {
            unset($menu_items[$i]['subitems']);
        }
    }
    $racket_finder = array(
        'param_id' => RACKET_FINDER_MENU_ITEM_ID,
        'status' => 'A',
        'active' => 1,
        'item' => __('find_tennis_racket_menu'),
        'href' => 'racket_finder.view'
    );
    $menu_items = fn_insert_before_key($menu_items, LCENTER_MENU_ITEM_ID, RACKET_FINDER_MENU_ITEM_ID, $racket_finder);
    return $menu_items;
}

function fn_process_php_errors($errno, $errstr, $errfile, $errline, $errcontext)
{
    if (strpos($errfile, DIR_ROOT . '/var/') === false && strpos($errfile, DIR_ROOT . '/app/lib/') === false) {
        LogFacade::error("Error #" . $errno . ":" . $errstr . " in " . $errfile . " at line " . $errline);
    }
}

function fn_get_discounted_products($params, $items_per_page = 0)
{
    list($products, $params) = fn_get_products($params);
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
    $_result = $result = array();
    foreach ($products as $i => $product) {
        if ($product['base_price'] > $product['price'] || $product['list_price'] > $product['price']) {
            $_result[] = $product;
        }
    }
    shuffle($_result);
    if (!empty($params['products_limit'])) {
        $_result = array_slice($_result, 0, $params['products_limit']);
    }

    return array($_result, $params);
}

function fn_get_menu_items_th($value, $block, $block_scheme)
{
    $menu_items = array();

    if (!empty($block['content']['menu']) && Menu::getStatus($block['content']['menu']) == 'A') {
        $params = array(
            'section' => 'A',
            'get_params' => true,
            'icon_name' => '',
            'multi_level' => true,
            'use_localization' => true,
            'status' => 'A',
            'generate_levels' => true,
            'request' => array(
                'menu_id' => $block['content']['menu'],
            )
        );

        $menu_items = fn_top_menu_form(fn_get_static_data($params));
        $block['properties'] = !empty($block['properties']) ? $block['properties'] : array();
        fn_dropdown_appearance_cut_second_third_levels($menu_items, 'subitems', $block['properties']);

        foreach ($menu_items as $i => $item) {
            if (!empty($item['param_3'])) {
                list($type, $id, $use_name) = fn_explode(':', $item['param_3']);
                $menu_items[$i]['type'] = $type;
                if ($type == 'P') {
                    $menu_items[$i]['show_more'] = true;
                    $menu_items[$i]['show_more_text'] = __('see_all_players');
                    foreach ($item['subitems'] as $j => $group) {
                        $menu_items[$i]['subitems'][$j]['show_more'] = false;
                    }
                } elseif ($type == 'C') {
                    fn_format_submenu($menu_items[$i]['subitems']);
                    foreach ($menu_items[$i]['subitems'] as $j => $group) {
                        if (!empty($group['subitems'])) {
                            $menu_items[$i]['subitems'][$j]['expand'] = false;
                            foreach ($group['subitems'] as $k => $item) {
                                if (!empty($item['code']) && fn_gender_match($item['code'])) {
                                    $menu_items[$i]['subitems'][$j]['expand'] = $k;
                                    break;
                                }
                                if ($item['is_virtual'] == 'Y' && !empty($item['parent_id'])) {
                                    $menu_items[$i]['subitems'][$j]['subitems'][$k]['href'] = 'categories.view?category_id=' . $item['parent_id'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }
// fn_print_die($menu_items);
    return $menu_items;
}

function fn_get_news_feed($params)
{
    $news = array();
    if (!empty($params['player_id'])) {
        $params['rss_feed_link'] = db_get_field("SELECT rss_link FROM ?:players WHERE player_id = ?i", $params['player_id']);
        if (!empty($params['rss_feed_link'])) {
            $news = fn_get_rss_news($params);
        }
    } elseif (!empty($params['rss_feed_link'])) {
        $news = fn_get_rss_news($params);
    }

    return array($news, $params);
}

function fn_get_rss_news($params)
{
    $news_feed = array();
    if (!empty($params['rss_feed_link'])) {
        $extra = array(
            'request_timeout' => 10
        );
        $response = Http::get($params['rss_feed_link'], array(), $extra);
        if (!empty($response)) {
            libxml_use_internal_errors(true);
            $xml = @simplexml_load_string($response);
            if ($xml !== false) {
                foreach ($xml->channel->item as $item) {
                    $news = array();
                    $news['title'] = (string) $item->title;
                    $news['link'] = (string) $item->link;
                    $news['description'] = (string) $item->description;
                    $news['image'] = (string) $item->image->url;
                    $aResult = strptime((string) $item->pubDate, '%a, %d %b %Y %T %z');
                    $news['timestamp'] = mktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'], $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);
                    $news_date = date('Y-m-d', $news['timestamp']);
                    $today = date('Y-m-d');
                    $yesterday = date('Y-m-d', strtotime('yesterday'));
                    if ($news_date == $today) {
                        $news['today'] = true;
                    } elseif ($news_date == $yesterday) {
                        $news['yesterday'] = true;
                    }
                    $news_feed[] = $news;
                    if (!empty($params['number_of_news']) && count($news_feed) >= $params['number_of_news']) {
                        break;
                    }
                }
            }
        }
    }

    return $news_feed;
}

function fn_reset_product_warehouses($product_id)
{
    $data = array();
    $_data[] = array(
        'warehouse_hash' => fn_generate_cart_id($product_id, array('warehouse_id' => TH_WAREHOUSE_ID)),
        'warehouse_id' => TH_WAREHOUSE_ID,
        'product_id' => $product_id,
        'amount' => 0
    );
    $brand_warehouse_ids = db_get_fields("SELECT ?:warehouse_brands.warehouse_id FROM ?:warehouse_brands LEFT JOIN ?:product_features_values ON ?:product_features_values.variant_id = ?:warehouse_brands.brand_id WHERE ?:product_features_values.feature_id = ?i AND ?:product_features_values.product_id = ?i", BRAND_FEATURE_ID, $product_id);
    if (!empty($brand_warehouse_ids)) {
        foreach ($brand_warehouse_ids as $br_id) {
            $_data[] = array(
                'warehouse_hash' => fn_generate_cart_id($product_id, array('warehouse_id' => $br_id)),
                'warehouse_id' => $br_id,
                'product_id' => $product_id,
                'amount' => 0
            );
        }
    }
    db_query("REPLACE ?:product_warehouses_inventory ?m", $_data);
}

function fn_update_product_tracking($product_id)
{
    $options_left = db_get_fields("SELECT po.option_id, go.option_id FROM ?:product_options AS po LEFT JOIN ?:product_global_option_links AS go ON go.option_id = po.option_id WHERE (po.product_id = ?i OR go.product_id = ?i) AND po.option_type IN ('S','R','C') AND po.inventory = 'Y'", $product_id, $product_id);
    if (empty($options_left)) {
        $tracking = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $product_id);
        if ($tracking == 'O') {
            db_query("UPDATE ?:products SET tracking = 'B' WHERE product_id = ?i", $product_id);
            fn_reset_product_warehouses($product_id);
        }
    } else {
        db_query("UPDATE ?:products SET tracking = 'O' WHERE product_id = ?i", $product_id);
    }
}

function fn_filter_categroies(&$categories)
{
    if (!empty($categories)) {
        foreach ($categories as $i => $cat) {
            if (empty($cat['subcategories']) && $cat['product_count'] == 0 && empty($cat['has_children'])) {
                unset($categories[$i]);
            } elseif (!empty($cat['subcategories'])) {
                fn_filter_categroies($categories[$i]['subcategories']);
            }
        }
    }
}

function fn_set_store_gender_mode($mode)
{
    if (strlen($mode) != 1) {
        return false;
    }

    $gender_mode = fn_get_store_gender_mode();

    if (empty($gender_mode) || (!(in_array($gender_mode, array('B', 'G')) && $mode == 'K') && !(in_array($gender_mode, array('M', 'F')) && $mode == 'A'))) {
        fn_set_session_data('gender_mode', $mode);
    }
}

function fn_format_categorization(&$category_data, $ctz_data, $type)
{
    if (empty($category_data[$type])) {
        foreach (array_reverse($category_data['cat_ids']) as $i => $cat_id) {
            if (!empty($ctz_data[$cat_id][$type])) {
                $category_data[$type] = $ctz_data[$cat_id][$type];
                break;
            }
        }
    }
}

function fn_generate_features_cash()
{
    $stats = fn_get_memcached_stats();
    if ($stats['status']) {
        FeaturesCache::generate(CART_LANGUAGE);
    }
    return array(true, array());
}

function fn_update_rankings($ids = array())
{
    if (!empty($ids)) {
        $players = db_get_array("SELECT player_id, data_link, gender FROM ?:players WHERE data_link != '' AND player_id IN (?n)", $ids);
    } else {
        $players = db_get_array("SELECT player_id, data_link, gender FROM ?:players WHERE data_link != ''");
    }
    $update = array();
    if (!empty($players)) {
        foreach ($players as $i => $player) {
            $result = Http::get($player['data_link']);
            if ($result) {
                $player_data = array(
                    'player_id' => $player['player_id']
                );
                if ($player['gender'] == 'M') {
                    if (preg_match('/<div class="player-ranking-position">.*?(\d+).*?<\/div>/', preg_replace('/[\r\n\t]/', '', $result), $match)) {
                        if (preg_match('/>(\d+)</', preg_replace('/[\s\r\n\t]/', '', $match[0]), $_match)) {
                            $player_data['ranking'] = isset($_match['1']) ? $_match['1'] : 'n/a';
                        } else {
                            $player_data['ranking'] = 0;
                        }
                    }
                    if (preg_match('/id="playersStatsTable".*?>(.*?)<\/table>/', preg_replace('/[\r\n\t]/', '', $result), $match)) {
                        if (preg_match('/>Career<\/div>(.*)/', $match[1], $match)) {
                            if (preg_match_all('/>(\d+)</', $match[1], $_match)) {
                                $player_data['titles'] = $player_data['data']['career_titles'] = isset($_match[1][1]) ? $_match[1][1] : 'n/a';
                            }
                            if (preg_match('/>\$([\d,]+)</', $match[1], $_match)) {
                                $player_data['data']['career_prize'] = isset($_match[1]) ? intval(str_replace(',', '', $_match[1])) : 'n/a';
                            }
                            if (preg_match('/>(\d+)-(\d+)</', $match[1], $_match)) {
                                $player_data['data']['career_won'] = isset($_match[1]) ? $_match[1] : 'n/a';
                                $player_data['data']['career_lost'] = isset($_match[2]) ? $_match[2] : 'n/a';
                            }
                        }
                    }
                    $update[] = $player_data;
                } else {
                    if (preg_match('/<span class="profile-header-image-col__rank-pos.*?".*?data-single="(\d+)"/', preg_replace('/[\r\n\t]/', '', $result), $match)) {
                        $player_data['ranking'] = isset($match['1']) ? $match['1'] : 'n/a';
                    }
                    if (preg_match('/data-single="Singles Titles".*?data-single="Singles Titles".*?data-single="(\d+)".*?<\/div>.*?Career<\/div>/', preg_replace('/[\r\n\t]/', '', $result), $match)) {
                        $player_data['titles'] = $player_data['data']['career_titles'] = isset($match[1]) ? $match[1] : 'n/a';
                    }
                    if (preg_match('/>Prize Money<\/div>.*?>Prize Money<\/div>.*?data-single="(\d+)".*?Career<\/div>/', preg_replace('/[\r\n]/', '', $result), $match)) {
                        $player_data['data']['career_prize'] = isset($match[1]) ? $match[1] : 'n/a';
                    }
                    if (preg_match('/data-single="W\/L Singles".*?data-single="W\/L Singles".*?data-single="(\d+)".*?data-single="(\d+)".*?<\/div>.*?Career<\/div>/', preg_replace('/[\r\n\t]/', '', $result), $match)) {
                        $player_data['data']['career_won'] = isset($match[1]) ? $match[1] : 'n/a';
                        $player_data['data']['career_lost'] = isset($match[2]) ? $match[2] : 'n/a';
                    }
                    $update[] = $player_data;
                }
            }
        }
    }
    $errors = array();
    if (!empty($update)) {
        foreach ($update as $i => $_dt) {
            if (fn_check_player_data($_dt)) {
                if (!empty($_dt['data'])) {
                    $_dt['data'] = serialize($_dt['data']);
                }
                $_dt['status'] = 'A';
                db_query("UPDATE ?:players SET ?u WHERE player_id = ?i", $_dt, $_dt['player_id']);
            } else {
                db_query("UPDATE ?:players SET status = 'H' WHERE player_id = ?i", $_dt['player_id']);
                $errors[] = $_dt;
            }
        }
        if (empty($errors) && count($players) == count($update)) {
            //fn_set_notification('N', __('notice'), __('rankings_updated_successfully', array('[total]' => count($players), '[updated]' => count($update))));
        } elseif (!empty($errors)) {
            $ids = '';
            foreach ($errors as $i => $dt) {
                $ids = (($ids != '') ? ', ' : ' ') . $dt['player_id'];
            }
            LogFacade::error("Rankings update error ids:" . $ids);

            return array(false, $errors);
        }
    }
    return array(true, $errors);
}

function fn_check_player_data($player_data)
{
    $scheme = array(
        'player_id' => 1,
        'ranking' => 1,
        'titles' => 1,
        'data' => array(
            'career_titles' => 1,
            'career_prize' => 1,
            'career_won' => 1,
            'career_lost' => 1
        )

    );

    foreach ($scheme as $key => $value) {
        if (!isset($player_data[$key]) || $player_data[$key] === 'n/a') {
            return false;
        } elseif (is_array($value)) {
            foreach ($value as $_key => $_value) {
                if (!isset($player_data[$key][$_key]) || $player_data[$key][$_key] == 'n/a') {
                    return false;
                }
            }
        }
    }

    return true;
}

function fn_update_rub_rate()
{
    $update_limits = array(
        'USD' => 0,
        'EUR' => 0,
    );
    $rates = fn_get_currency_exchange_rates();
    $update_prices = false;
    $errors = array();
    if ($rates) {
        foreach ($rates as $code => $rate) {
            if (!empty(Registry::get('currencies.' . $code)) && (Registry::get('currencies.' . $code . '.coefficient') < $rate || (Registry::get('currencies.' . $code . '.coefficient') > $rate && (empty($update_limits[$code]) || Registry::get('currencies.' . $code . '.coefficient') - $rate > $update_limits[$code])))) {
                $update_prices = true;
                $currency_data = array('coefficient' => $rate);
                db_query("UPDATE ?:currencies SET ?u WHERE currency_code = ?s", $currency_data, $code);
            }
        }
    }
    if ($update_prices) {
        $params = array();
        fn_init_currency($params);
        fn_update_prices();
        fn_set_notification('N', __('notice'), __('currencies_updated_successfully'));
    }

    return array(true, $errors);
}

function fn_update_product_exception($product_id, $product_options, $new_amount)
{
    $exist = fn_check_combination($product_options, $product_id);
    if ($new_amount < 1) {
        if (!$exist) {
            $_data = array(
                'product_id' => $product_id,
                'combination' => serialize($product_options)
            );
            db_query("INSERT INTO ?:product_options_exceptions ?e", $_data);
        }
    } else {
        if ($exist) {
            db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i AND combination = ?s", $product_id, serialize($product_options));
        }
    }
    fn_update_combinations($product_id);
}

function fn_update_combinations($product_id)
{
    $combinations = db_get_array("SELECT ?:product_options_inventory.combination, SUM(?:product_warehouses_inventory.amount) AS amount FROM ?:product_warehouses_inventory LEFT JOIN ?:product_options_inventory ON ?:product_options_inventory.combination_hash = ?:product_warehouses_inventory.combination_hash WHERE ?:product_warehouses_inventory.product_id = ?i GROUP BY ?:product_warehouses_inventory.combination_hash", $product_id);
    if (!empty($combinations)) {
        $option_variants_avail = $option_variants = array();
        foreach ($combinations as $i => $combination) {
            $options_array = fn_get_product_options_by_combination($combination['combination']);
            if ($combination['amount'] < 1) {
                foreach ($options_array as $option_id => $variant_id) {
                    if (!in_array($option_id, array_keys($option_variants_avail))) {
                        $option_variants_avail[$option_id] = array();
                    }
                }
            } else {
                foreach ($options_array as $option_id => $variant_id) {
                    if (empty($option_variants_avail[$option_id]) || !in_array($variant_id, $option_variants_avail[$option_id])) {
                        $option_variants_avail[$option_id][] = $option_variants[] = $variant_id;
                    }
                }
            }
        }
        if (!empty($option_variants_avail)) {
            $features = db_get_hash_single_array("SELECT a.feature_id, option_id FROM ?:product_options AS a INNER JOIN ?:product_features ON ?:product_features.feature_id = a.feature_id WHERE option_id IN (?n)", array('option_id', 'feature_id'), array_keys($option_variants_avail));
            if (!empty($option_variants)) {
                $feature_variants = db_get_hash_single_array("SELECT feature_variant_id, variant_id FROM ?:product_option_variants WHERE variant_id IN (?n)", array('variant_id', 'feature_variant_id'), $option_variants);
            }
            $features_data = array();
            foreach ($option_variants_avail as $option_id => $variants) {
                if (!empty($features[$option_id])) {
                    $features_data[$features[$option_id]] = array();
                    if (!empty($variants)) {
                        foreach ($variants as $j => $variant_id) {
                            if (!empty($feature_variants[$variant_id])) {
                                $features_data[$features[$option_id]][] = $feature_variants[$variant_id];
                            }
                        }
                    }
                }
            }
            if (!empty($features_data)) {
                $add_new_variant = array();
                fn_update_product_features_value($product_id, $features_data, $add_new_variant, CART_LANGUAGE);
            }
        }
    }
}

function fn_update_product_exceptions($product_id, $combinations = array())
{
    if (empty($combinations)) {
        $combinations = db_get_hash_array("SELECT ?:product_options_inventory.combination_hash, SUM(?:product_warehouses_inventory.amount) AS amount FROM ?:product_options_inventory LEFT JOIN ?:product_warehouses_inventory ON ?:product_warehouses_inventory.combination_hash = ?:product_options_inventory.combination_hash WHERE ?:product_options_inventory.product_id = ?i GROUP BY ?:product_options_inventory.combination_hash", 'combination_hash', $product_id);
    }
    if (!empty($combinations)) {
        $combination_options = db_get_hash_single_array("SELECT combination, combination_hash FROM ?:product_options_inventory WHERE combination_hash IN (?n)", array('combination_hash', 'combination'), array_keys($combinations));
        if (!empty($combination_options)) {
            foreach ($combination_options as $hash => $combination) {
                $options_array = fn_get_product_options_by_combination($combination);

                db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i AND combination = ?s", $product_id, serialize($options_array));
                if (!empty($combinations[$hash]) && $combinations[$hash]['amount'] < 1) {
                    $_data = array(
                        'product_id' => $product_id,
                        'combination' => serialize($options_array)
                    );
                    db_query("INSERT INTO ?:product_options_exceptions ?e", $_data);
                }
            }
        }
    }
    fn_update_combinations($product_id);
}

function fn_gather_additional_products_data_cs(&$products, $params)
{
    if (empty($products)) {
        return;
    } else {
        foreach ($products as $i => $prods) {
            if (!empty($prods['items'])) {
                fn_gather_additional_products_data($products[$i]['items'], $params);
            }
        }
    }
}

function fn_feature_has_size_chart($feature_id)
{
    return in_array($feature_id, array(BRAND_FEATURE_ID, SHOES_GENDER_FEATURE_ID, CLOTHES_GENDER_FEATURE_ID));
}

function fn_get_memcached_stats()
{
    $result = array();
    if (class_exists('Memcached')) {
        $result['status'] = true;
        $stats = Memcache::instance()->call('stats');
        $localhost = !empty($stats['localhost:11211']) ? $stats['localhost:11211'] : false;
        if (!empty($localhost)) {
            if (!empty($localhost['limit_maxbytes'])) {
                $result['used_prc'] = ceil($localhost['bytes'] / $localhost['limit_maxbytes'] * 100);
            }
            $result['used'] = ceil($localhost['bytes'] / 1024);
        }
    } else {
        $result['status'] = false;
    }

    return $result;
}

function fn_add_product_features($pid, $data)
{
    $addition = array();
    foreach ($data as $v) {
        $v['product_id'] = $pid;
        $addition[] = $v;
    }
    FeaturesCache::updateProductFeaturesValue($pid, $addition);
}

function fn_update_feature_value_int($variant_id, $value_int, $lang_code)
{
    $tmp = db_get_row("SELECT feature_id, value_int FROM ?:product_features_values WHERE variant_id = ?i AND lang_code = ?s", $variant_id, $lang_code);
    if (!empty($tmp['feature_id']) && !empty($tmp['value_int'])) {
        FeaturesCache::updateFeatureValueInt($tmp['feature_id'], $tmp['value_int'], $value_int, $lang_code);
    }
}

function fn_change_feature_category($feature_id, $new_categories)
{
    $product_ids = db_get_fields("SELECT product_id FROM ?:products_categories WHERE link_type = 'M' AND category_id IN (?a)", $new_categories);
    if (!empty($product_ids)) {
        $params = array(
            'delete' => array('not_product_id' => $product_ids),
            'condition' => array('feature_id' => array($feature_id))
        );
        FeaturesCache::clearoutFeatures($params);
    }
}

function fn_get_block_categories($category_id)
{
    $categories = array();
    $_params = array (
        'category_id' => $category_id,
        'visible' => true,
        'get_images' => true,
        'limit' => 10,
        'skip_filter' => true
    );
    list($subcategories, ) = fn_get_categories($_params, CART_LANGUAGE);
    if (!empty($subcategories)) {
        $subcategory = reset($subcategories);
        $params = array (
            'category_id' => $subcategory['category_id'],
            'visible' => true,
            'get_images' => true,
            'limit' => 10
        );

        list($categories, ) = fn_get_categories($params, CART_LANGUAGE);
    }

    return $categories;
}

function fn_get_brands()
{
    list($variants) = fn_get_product_feature_variants(array(
        'feature_id' => BRAND_FEATURE_ID
    ));

    return $variants;
}

function fn_get_currency_exchange_rates()
{
    $url = 'http://www.cbr.ru/scripts/XML_daily.asp';
    $result_xml = Http::get($url);
    $_result = @simplexml_load_string($result_xml);
    $result = array();
    if (!empty($_result->Valute)) {
        foreach ($_result->Valute as $cur_rate) {
            $result[(string) $cur_rate->CharCode] = str_replace(',', '.', (string) $cur_rate->Value);
        }
    }

    return $result;
}

function fn_get_subtitle_feature($features, $type = 'R')
{
    if ($type == 'R') {
        $feature_ids = array(BABOLAT_SERIES_FEATURE_ID, HEAD_SERIES_FEATURE_ID, WILSON_SERIES_FEATURE_ID, DUNLOP_SERIES_FEATURE_ID, PRINCE_SERIES_FEATURE_ID, YONEX_SERIES_FEATURE_ID, PROKENNEX_SERIES_FEATURE_ID);
    } else if ($type == 'A') {
        $feature_ids = array(CLOTHES_TYPE_FEATURE_ID);
    } else if ($type == 'S') {
        $feature_ids = array(SHOES_SURFACE_FEATURE_ID);
    } else if ($type == 'B') {
        $feature_ids = array(BAG_SIZE_FEATURE_ID);
    } else if ($type == 'ST') {
        $feature_ids = array(STRING_TYPE_FEATURE_ID);
    } else if ($type == 'BL') {
        $feature_ids = array(BALLS_TYPE_FEATURE_ID);
    } else if ($type == 'OG') {
        $feature_ids = array(OG_TYPE_FEATURE_ID);
    } else if ($type == 'BG') {
        $feature_ids = array(BG_TYPE_FEATURE_ID);
    } else if ($type == 'TM') {
        $feature_ids = array(TM_TYPE_FEATURE_ID);
    }
    if (!empty($feature_ids)) {
        foreach ($features as $feature_id => $feature) {
            $key = array_search($feature_id, $feature_ids);
            if ($key !== false) {
                return $features[$feature_ids[$key]];
            }
        }
    }

    return false;
}

function fn_display_subheaders($category_id)
{
    return /*in_array($category_id, array(STRINGS_CATEGORY_ID))*/true;
}

function fn_get_product_cross_sales($params)
{
    $result = array();
    if (!empty($_SESSION['product_features'][TYPE_FEATURE_ID])) {
        if ($_SESSION['product_features'][TYPE_FEATURE_ID]['variant_id'] == KIDS_RACKET_FV_ID) {
        } else {
            if (!empty($_SESSION['product_features'][R_STRINGS_FEATURE_ID]['value']) && $_SESSION['product_features'][R_STRINGS_FEATURE_ID]['value'] == 'N') {
                $params_array = array(POLYESTER_MATERIAL_CATEGORY_ID, HYBRID_MATERIAL_CATEGORY_ID, NATURAL_GUT_MATERIAL_CATEGORY_ID, NYLON_MATERIAL_CATEGORY_ID);
                $_params = array (
                    'sort_by' => 'random',
                    'limit' => 3,
                    'subcats' => 'Y',
                    'features_hash' => 'V' . TW_M_STRINGS_FV_ID,
                    'amount_from' => 1
                );
                $result[] = array(
                    'title' => __('strings'),
                    'items' => fn_get_result_products($_params, 'cid', $params_array)
                );
            }
        }
    }

    return array($result, $params);
}

function fn_get_cross_sales($params)
{
    $result = array();
    if (!empty($params['category_ids'])) {
        if (!empty($_SESSION['cart']['product_categories'])) {
            $params['category_ids'] = array_diff($params['category_ids'], $_SESSION['cart']['product_categories']);
        }
        if (!empty($params['category_ids'])) {
            $cat_params = array(
                'item_ids' => implode(',', $params['category_ids']),
                'simple' => false,
                'group_by_level' => false,
                'skip_filter' => true,
                'get_description' => true
            );
            list($categories, ) = fn_get_categories($cat_params);
            $products = array();
            $_params = array (
                'sort_by' => 'random',
                'limit' => 10,
                'subcats' => 'Y',
                'amount_from' => 1,
            );
            if (!empty($params['price_to'])) {
                $_params['price_to'] = $params['price_to'];
            }
            foreach ($params['category_ids'] as $i => $cat_id) {
                $_params['cid'] = $cat_id;
                list($products[$cat_id],) = fn_get_products($_params);
                fn_gather_additional_products_data($products[$cat_id], array(
                    'get_icon' => false,
                    'get_detailed' => true,
                    'get_additional' => false,
                    'get_options' => true,
                    'get_discounts' => true,
                    'get_features' => false,
                    'get_title_features' => false,
                    'allow_duplication' => false
                ));
            }

            foreach ($categories as $k => $c_data) {
                $categories[$k]['products'] = $products[$c_data['category_id']];
            }
            $result = $categories;
        }
    }

    return $result;
}

function fn_get_result_products($params, $key, $param_array)
{
    $result = array();
    foreach ($param_array as $val) {
        $params[$key] = $val;
        list($prods,) = fn_get_products($params);
        $result = array_merge($result, $prods);
    }

    return $result;
}

function fn_get_same_brand_products($params)
{
    $result = array();
    $_limit = $params['limit'];
    unset($params['limit']);
    list($products, ) = fn_get_products($params);
    $ids = array();
    if (!empty($products)) {
        $_products = array();
        foreach ($products as $i => $product) {
            $ids[] = $product['product_id'];
            $_products[$product['product_id']] = $product;
        }
        $objective_cat_ids = array(RACKETS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, BAGS_CATEGORY_ID, STRINGS_CATEGORY_ID, BALLS_CATEGORY_ID);
        $category_path = db_get_field("SELECT id_path FROM ?:categories AS c LEFT JOIN ?:products_categories AS pc ON pc.category_id = c.category_id AND pc.link_type = 'M' LEFT JOIN ?:products AS p ON p.product_id = pc.product_id WHERE p.product_id = ?i", $params['same_brand_pid']);
        $show_cat_ids = array_diff($objective_cat_ids, explode('/', $category_path));
        if (!empty($show_cat_ids)) {
            fn_gender_categories($show_cat_ids);
            $limit = ceil($_limit / count($show_cat_ids));
            $_params = array (
                'sort_by' => 'random',
                'limit' => $limit,
                'subcats' => 'Y',
                'item_ids' => implode(',', $ids)
            );
            foreach ($show_cat_ids as $id) {
                $_params['cid'] = $id;
                list($prods,) = fn_get_products($_params);
                foreach ($prods as $i => $prod) {
                    unset($_products[$prod['product_id']]);
                }
                $result = array_merge($result, $prods);
            }
        }
        if (count($result) < $_limit && !empty($_products)) {
            $result = array_merge($result, array_slice($_products, 0, $_limit - count($result)));
        }
        shuffle($result);
    }
    $params['limit'] = $_limit;

    return array($result, $params);
}

function fn_get_checkout_cross_sales($params)
{
    $result = array();
    if (!empty($_SESSION['cart']['products'])) {
        $objective_cat_ids = array(RACKETS_CATEGORY_ID, APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, BAGS_CATEGORY_ID, STRINGS_CATEGORY_ID, BALLS_CATEGORY_ID, OVERGRIPS_CATEGORY_ID, DAMPENERS_CATEGORY_ID);
        $show_cat_ids = array_diff($objective_cat_ids, $_SESSION['cart']['product_categories']);
        if (!empty($show_cat_ids)) {
            fn_gender_categories($show_cat_ids);
            $limit = ceil($params['limit'] / count($show_cat_ids));
            $_params = array (
//                 'sort_by' => 'bestsellers',
//                 'sort_order' => 'desc',
                'sort_by' => 'random',
                'limit' => $limit,
                'subcats' => 'Y'
            );
            foreach ($show_cat_ids as $id) {
                $_params['cid'] = $id;
                list($prods,) = fn_get_products($_params);
                $result = array_merge($result, $prods);
            }
        }
        if (!empty($result)) {
            shuffle($result);
        }
    }

    return array($result, $params);
}

function fn_gender_categories(&$show_cat_ids)
{
    $gender_categories = array(APPAREL_CATEGORY_ID, SHOES_CATEGORY_ID, RACKETS_CATEGORY_ID);
    $gender = fn_get_store_gender_mode();
    if (!empty($gender)) {
        foreach ($gender_categories as $i => $c_id) {
            $cat_it = array_search($c_id, $show_cat_ids);
            if ($cat_it !== false) {
                $modes = array(
                    $gender,
                    'U'
                );
                if (in_array($gender, array('B', 'G'))) {
                    $modes[] = 'K';
                }
                if ($gender == 'K') {
                    $modes[] = 'B';
                    $modes[] = 'G';
                }
                if (in_array($gender, array('M', 'F'))) {
                    $modes[] = 'A';
                }
                if ($gender == 'A') {
                    $modes[] = 'M';
                    $modes[] = 'F';
                }
                $_condition = array();
                foreach ($modes as $j => $mode) {
                    $_condition[] = db_quote("code = ?s", $mode);
                }
                $condition = "(" . implode(' OR ', $_condition) . ")";
                $gender_cids = db_get_fields("SELECT category_id FROM ?:categories WHERE $condition AND id_path LIKE ?l", $c_id . '/%');
                if (!empty($gender_cids)) {
                    $show_cat_ids[$cat_it] = $gender_cids;
                }
            }
        }
    }
}

function fn_check_vars($description)
{
    if (preg_match_all('/\{([a-zA-Z_]*)\}/', $description, $matches)) {
        foreach ($matches[0] as $i => $vl) {
            if ($vl == '{free_shipping_cost}') {
                $description = str_replace($vl, Registry::get('addons.development.free_shipping_cost'), $description);
            }
            if ($vl == '{company_phone}') {
                $description = str_replace($vl, Registry::get('settings.Company.company_phone'), $description);
            }
        }
    }
    return $description;
}

function fn_render_captured_blocks($description, $smarty_capture)
{
    if (preg_match_all('/\[\-([a-zA-Z1-9_]*)\-\]/', $description, $matches)) {
        $blocks = array();
        foreach ($matches[1] as $i => $name) {
            $blocks[] = !empty($smarty_capture['block_' . $name]) ? $smarty_capture['block_' . $name] : '';
        }
        $description = str_replace(
            $matches[0],
            $blocks,
            $description
        );
    }

    return $description;
}

function fn_get_product_global_data($product_data, $data_names, $categories_data = array())
{
    if (empty($product_data['category_ids']) && !empty($product_data['product_id'])) {
        $product_data['category_ids'] = db_get_fields("SELECT category_id FROM ?:products_categories WHERE product_id = ?i ORDER BY link_type DESC", $product_data['product_id']);
    }
    $result = array();
    if (!empty($product_data['category_ids'])) {
        if (empty($categories_data)) {
            $paths = db_get_hash_single_array("SELECT category_id, id_path FROM ?:categories WHERE category_id IN (?n)", array('category_id', 'id_path'), $product_data['category_ids']);
        } else {
            $paths = array();
            foreach ($categories_data as $c_id => $c_data) {
                $paths[$c_id] = $c_data['id_path'] ?? '';
            }
        }
        $all_ids = array();
        $use_order = array();
        if (!empty($paths)) {
            foreach ($product_data['category_ids'] as $cat_id) {
                if (!empty($paths[$cat_id])) {
                    $ids = explode('/', $paths[$cat_id]);
                    foreach(array_reverse($ids) as $j => $cat_id) {
                        if (empty($use_order[$j]) || !in_array($cat_id, $use_order[$j])) {
                            $use_order[$j][] = $cat_id;
                        }
                    }
                    $all_ids = array_merge($all_ids, $ids);
                }
            }
        }
        $fields = implode(', ', $data_names);
        if (empty($categories_data)) {
            $data = db_get_hash_array("SELECT category_id, $fields FROM ?:categories WHERE category_id IN (?n)", 'category_id', array_unique($all_ids));
        } else {
            $data = $categories_data;
        }
        $types = db_get_hash_single_array("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '?:categories'", array('COLUMN_NAME', 'DATA_TYPE'));
        if (!empty($use_order)) {
            foreach ($data_names as $j => $dt_name) {
                foreach ($use_order as $lvl => $cat_ids) {
                    foreach ($cat_ids as $i => $ct_id) {
                        if (empty($result[$dt_name]) && ((in_array($types[$dt_name], array('int', 'mediumint', 'smallint', 'tinyint', 'bigint', 'float', 'decimal', 'double', 'real', 'bit', 'boolean', 'serial')) && !empty(floatval($data[$ct_id][$dt_name]))) || ($types[$dt_name] != 'decimal' && !empty($data[$ct_id][$dt_name])))) {
                            $result[$dt_name] = $data[$ct_id][$dt_name];
                            break 2;
                        }
                    }
                }
            }
        }
    }
    foreach ($data_names as $i => $dt_name) {
        if (empty($result[$dt_name]) && !empty(Registry::get('addons.development.' . $dt_name))) {
            $result[$dt_name] = Registry::get('addons.development.' . $dt_name);
        }
    }

    return $result;
}

function fn_get_category_global_data($category_data, $data_names)
{
    $cat_ids = array();
    if (!empty($category_data['path'])) {
        $cat_ids = explode('/', $category_data['path']);
    }
    $result = array();
    if (!empty($cat_ids)) {
        $fields = implode(', ', $data_names);
        $data = db_get_hash_array("SELECT category_id, $fields FROM ?:categories WHERE category_id IN (?n)", 'category_id', $cat_ids);
        $types = db_get_hash_single_array("SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '?:categories'", array('COLUMN_NAME', 'DATA_TYPE'));
        foreach ($data_names as $j => $dt_name) {
            foreach (array_reverse($cat_ids) as $i => $ct_id) {
                if (empty($result[$dt_name]) && ((in_array($types[$dt_name], array('int', 'mediumint', 'smallint', 'tinyint', 'bigint', 'float', 'decimal', 'double', 'real', 'bit', 'boolean', 'serial')) && !empty(floatval($data[$ct_id][$dt_name]))) || (!in_array($types[$dt_name], array('int', 'mediumint', 'smallint', 'tinyint', 'bigint', 'float', 'decimal', 'double', 'real', 'bit', 'boolean', 'serial')) && !empty($data[$ct_id][$dt_name])))) {
                    $result[$dt_name] = $data[$ct_id][$dt_name];
                    break;
                }
            }
        }
    }
    foreach ($data_names as $i => $dt_name) {
        if (empty($result[$dt_name]) && !empty(Registry::get('addons.development.' . $dt_name))) {
            $result[$dt_name] = Registry::get('addons.development.' . $dt_name);
        }
    }

    return $result;
}

function fn_round_price($price)
{
    return ceil(ceil($price) / 10) * 10;
}

function fn_calculate_base_price($product_data)
{
    $net_cost = $product_data['net_cost'] * Registry::get('currencies.' . $product_data['net_currency_code'] . '.coefficient');
    $base_price = $net_cost + $net_cost * $product_data['margin'] / 100;

    return $base_price;
}

function fn_get_product_margin(&$product)
{
    $error = false;
    if (!empty($product['global_margin']) && !empty($product['net_currency_code'])) {
        $_md = explode(';', $product['global_margin']);
        if (count($_md) == 2) {
            $min_md = explode(':', $_md[0]);
            $max_md = explode(':', $_md[1]);
            if (count($min_md) == 2 && count($max_md) == 2) {
                $min_md[0] = $min_md[0] * Registry::get('currencies.' . $product['global_net_currency_code'] . '.coefficient');
                $max_md[0] = $max_md[0] * Registry::get('currencies.' . $product['global_net_currency_code'] . '.coefficient');
                $net_cost = $product['net_cost'] * Registry::get('currencies.' . $product['net_currency_code'] . '.coefficient');
                if ($net_cost <= $min_md[0]) {
                    $product['margin'] = $min_md[1];
                } elseif ($net_cost >= $max_md[0]) {
                    $product['margin'] = $max_md[1];
                } else {
                    $product['margin'] = ceil((($net_cost - $min_md[0]) * ($max_md[1] - $min_md[1]) / ($max_md[0] - $min_md[0])) + $min_md[1]);
                }
            } else {
                $error = true;
            }
        } else {
            $error = true;
        }
    } else {
        $error = true;
    }
    if ($error) {
        fn_set_notification('E', __('error'), __('error_incorrect_margin_data'));
    }
}

function fn_process_update_prices($products)
{
    $result = array();
    if (!empty($products)) {
        $prices = db_get_hash_multi_array("SELECT * FROM ?:product_prices WHERE product_id IN (?n) AND lower_limit IN ('1', '2')", array('product_id', 'lower_limit'), array_keys($products));
        if (!empty($prices)) {
            foreach ($prices as $product_id => $prs) {
                if (!empty($prs)) {
                    if (empty($products[$product_id]['margin']) || $products[$product_id]['margin'] == 0) {
                        $global_data = fn_get_product_global_data($products[$product_id], array('margin', 'net_currency_code'));
                        $products[$product_id]['global_margin'] = $global_data['margin'];
                        $products[$product_id]['global_net_currency_code'] = $global_data['net_currency_code'];
                        if (empty($products[$product_id]['net_currency_code'])) {
                            $products[$product_id]['net_currency_code'] = $global_data['net_currency_code'];
                        }
                        fn_get_product_margin($products[$product_id]);
                        if ($products[$product_id]['margin'] > 0) {
                            db_query("UPDATE ?:products SET margin = ?d, net_currency_code = ?s WHERE product_id = ?i", $products[$product_id]['margin'], $products[$product_id]['net_currency_code'], $product_id);
                        }
                    }
                    $base_price = fn_calculate_base_price($products[$product_id]);
                    foreach ($prs as $i => $p_data) {
                        if ($p_data['lower_limit'] == 1 || ($p_data['lower_limit'] > 1 && $p_data['percentage_discount'] > 0)) {
                            $prices[$product_id][$i]['price'] = fn_round_price($base_price);
                        } else {
                            $prices[$product_id][$i]['price'] = fn_round_price($base_price - $base_price * RACKETS_QTY_DSC_PRC / 100);
                        }
                        $result[] = $prices[$product_id][$i];
                    }
                }
            }
        }
    }

    if (!empty($result)) {
        db_query("REPLACE INTO ?:product_prices ?m", $result);
    }
}

function fn_update_prices()
{
    $products = db_get_hash_array("SELECT prods.product_id, prods.margin, prods.net_cost, prods.net_currency_code, prods.price_mode, cats.category_id AS main_category FROM ?:products AS prods LEFT JOIN ?:products_categories AS cats ON prods.product_id = cats.product_id AND cats.link_type = 'M' WHERE prods.price_mode = 'D' AND prods.net_cost > 0 AND update_with_currencies = 'Y'", 'product_id');
    $result = fn_process_update_prices($products);
}

function fn_get_categories_types($category_ids)
{
    $category_ids = is_array($category_ids) ? $category_ids : array($category_ids);
    $paths = db_get_hash_single_array("SELECT id_path, category_id FROM ?:categories WHERE category_id IN (?n)", array("category_id", "id_path"), $category_ids);
    $result = array();
    foreach ($paths as $i => $path) {
        $result[$i] = fn_identify_type_category_id($path);
    }
    return $result;
}

function fn_identify_category_type($path)
{
    return fn_get_category_type(fn_identify_type_category_id($path));
}
function fn_get_category_type($category_id)
{
    $types = array(
        RACKETS_CATEGORY_ID => 'R',
        APPAREL_CATEGORY_ID => 'A',
        SHOES_CATEGORY_ID => 'S',
        BAGS_CATEGORY_ID => 'B',
        SPORTS_NUTRITION_CATEGORY_ID => 'N',
        STRINGS_CATEGORY_ID => 'ST',
        BALLS_CATEGORY_ID => 'BL',
        GRIPS_CATEGORY_ID => 'G',
        OVERGRIPS_CATEGORY_ID => 'OG',
        BASEGRIPS_CATEGORY_ID => 'BG',
        DAMPENERS_CATEGORY_ID => 'DP',
        BALL_HOPPER_CATEGORY_ID => 'BH',
        BALL_MACHINE_CATEGORY_ID => 'BM',
        STR_MACHINE_CATEGORY_ID => 'SM',
        BALL_MACHINE_ACC_CATEGORY_ID => 'BA',
        TREADMILL_CATEGORY_ID => 'TM',
    );

    return !empty($types[$category_id]) ? array($category_id, $types[$category_id]) : array('', '');
}

function fn_identify_type_category_id($path)
{
    $type = '';
    if (!empty($path)) {
        $cats = explode('/', $path);
        if (in_array(RACKETS_CATEGORY_ID, $cats)) {
            $type = RACKETS_CATEGORY_ID;
        } elseif (in_array(APPAREL_CATEGORY_ID, $cats)) {
            $type = APPAREL_CATEGORY_ID;
        } elseif (in_array(SHOES_CATEGORY_ID, $cats)) {
            $type = SHOES_CATEGORY_ID;
        } elseif (in_array(BAGS_CATEGORY_ID, $cats)) {
            $type = BAGS_CATEGORY_ID;
        } elseif (in_array(SPORTS_NUTRITION_CATEGORY_ID, $cats)) {
            $type = SPORTS_NUTRITION_CATEGORY_ID;
        } elseif (in_array(STRINGS_CATEGORY_ID, $cats)) {
            $type = STRINGS_CATEGORY_ID;
        } elseif (in_array(BALLS_CATEGORY_ID, $cats)) {
            $type = BALLS_CATEGORY_ID;
        } elseif (in_array(GRIPS_CATEGORY_ID, $cats)) {
            $type = GRIPS_CATEGORY_ID;
        } elseif (in_array(OVERGRIPS_CATEGORY_ID, $cats)) {
            $type = OVERGRIPS_CATEGORY_ID;
        } elseif (in_array(BASEGRIPS_CATEGORY_ID, $cats)) {
            $type = BASEGRIPS_CATEGORY_ID;
        } elseif (in_array(DAMPENERS_CATEGORY_ID, $cats)) {
            $type = DAMPENERS_CATEGORY_ID;
        } elseif (in_array(STR_MACHINE_CATEGORY_ID, $cats)) {
            $type = STR_MACHINE_CATEGORY_ID;
        } elseif (in_array(BALL_HOPPER_CATEGORY_ID, $cats)) {
            $type = BALL_HOPPER_CATEGORY_ID;
        } elseif (in_array(BALL_MACHINE_ACC_CATEGORY_ID, $cats)) {
            $type = BALL_MACHINE_ACC_CATEGORY_ID;
        } elseif (in_array(BALL_MACHINE_CATEGORY_ID, $cats)) {
            $type = BALL_MACHINE_CATEGORY_ID;
        } elseif (in_array(TREADMILL_CATEGORY_ID, $cats)) {
            $type = TREADMILL_CATEGORY_ID;
        }
    }

    return $type;
}

function fn_insert_before_key($originalArray, $originalKey, $insertKey, $insertValue )
{
    $newArray = array();
    $inserted = false;

    foreach( $originalArray as $key => $value ) {

        if( !$inserted && $key === $originalKey ) {
            if (!empty($insertKey)) {
                $newArray[$insertKey] = $insertValue;
            } else {
                $newArray[] = $insertValue;
            }
            $inserted = true;
        }

        if (!empty($insertKey)) {
            $newArray[ $key ] = $value;
        } else {
            $newArray[] = $value;
        }

    }

    return $newArray;

}

function fn_get_player_data($player_id)
{
    $field_list = "?:players.*";
    $join = '';

    fn_set_hook('get_player_data', $player_id, $field_list, $join, $condition);

    $player_data = db_get_row("SELECT $field_list FROM ?:players LEFT JOIN ?:players_gear ON ?:players.player_id = ?:players_gear.player_id ?p WHERE ?:players.player_id = ?i  ?p", $join, $player_id, $condition);

    if (!empty($player_data)) {
        if (!empty($player_data['website'])) {
            $player_data['website'] = (strpos($player_data['website'], 'http://') === false) ? ('http://' . $player_data['website']) : $player_data['website'];
        }
        $player_data['main_pair'] = fn_get_image_pairs($player_id, 'player', 'M', true, true);
        $player_data['bg_image'] = fn_get_image_pairs($player_id, 'player', 'B', false, true);
        $player_data['gear'] = db_get_fields("SELECT product_id FROM ?:players_gear WHERE player_id = ?i", $player_id);
        $player_data['data'] = unserialize($player_data['data']);
    }

    fn_set_hook('get_player_data_post', $player_data);

    return (!empty($player_data) ? $player_data : false);
}

function fn_get_block_players()
{
    $players = array();
    $_params = array (
        'gender' => 'M',
        'limit' => 3
    );
    list($players['male'], ) = fn_get_players($_params);
    $_params = array (
        'gender' => 'F',
        'limit' => 3
    );
    list($players['female'], ) = fn_get_players($_params);

    return $players;
}

function fn_get_players($params)
{
    $fields = array (
        '?:players.*',
        'GROUP_CONCAT(?:players_gear.product_id) as gear'
    );

    $condition = $join = '';
    $join .= db_quote(" LEFT JOIN ?:players_gear ON ?:players_gear.player_id = ?:players.player_id ");

    if (AREA == 'C') {
        $_statuses = array('A'); // Show enabled players
        $condition .= db_quote(" AND ?:players.status IN (?a)", $_statuses);
    }

    if (!empty($params['player'])) {
        $condition .= db_quote(" AND ?:players.player LIKE ?l", "%".trim($params['player'])."%");
    }

    if (!empty($params['gender'])) {
        $condition .= db_quote(" AND ?:players.gender = ?s", $params['gender']);
    }

    if (!empty($params['ranking'])) {
        $condition .= db_quote(" AND ?:players.ranking = ?s", $params['ranking']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:players.status IN (?a)", $params['status']);
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:players.player_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['product_id'])) {
        $condition .= db_quote(' AND ?:players_gear.product_id = ?i', $params['product_id']);
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:players.player_id != ?i', $params['except_id']);
    }

    if (AREA == 'C') {
        $condition .= db_quote(' AND ?:players_gear.product_id IS NOT NULL');
    }

    $limit = '';

    fn_set_hook('get_players', $params, $join, $condition, $fields);

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $players = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:players ?p WHERE 1 ?p GROUP BY ?:players.player_id ORDER BY ?:players.ranking != '0' DESC, ?:players.ranking ASC ?p", 'player_id', $join, $condition, $limit);

    if (empty($players)) {
        return array(array(), $params);
    }

    if (empty($params['plain'])) {
        $players_images = fn_get_image_pairs(array_keys($players), 'player', 'M', true, false);
        foreach ($players as $k => $v) {
            if (!empty($players_images[$v['player_id']])) {
                $players[$k]['main_pair'] = reset($players_images[$v['player_id']]);
            }
            $players[$k]['gear'] = explode(',', $players[$k]['gear']);
        }
    }

    fn_set_hook('get_players_post', $players, $params);

    return array($players, $params);
}

function fn_delete_player($player_id)
{
    if (empty($player_id)) {
        return false;
    }

    // Log player deletion
    fn_log_event('players', 'delete', array(
        'player_id' => $player_id,
    ));
    $variant_variant_id = db_get_field("SELECT feature_variant_id FROM ?:players WHERE player_id = ?i", $player_id);
    if (!empty($variant_variant_id)) {
        fn_delete_product_feature_variants(0, array($variant_variant_id));
    }

    // Deleting player
    db_query("DELETE FROM ?:players WHERE player_id = ?i", $player_id);
    db_query("DELETE FROM ?:players_gear WHERE player_id = ?i", $player_id);

    // Deleting player images
    fn_delete_image_pairs($player_id, 'player');

    fn_set_hook('delete_player', $player_id);

    return true;
}

function fn_update_player($player_data, $player_id = 0)
{
    $_data = $player_data;

    if (isset($player_data['birthday'])) {
        $_data['birthday'] = fn_parse_date($player_data['birthday']);
    }

    if (isset($_data['ranking']) && empty($_data['ranking']) && $_data['ranking'] != '0') {
        $_data['ranking'] = db_get_field("SELECT max(ranking) FROM ?:players");
        $_data['ranking'] = $_data['ranking'] + 1;
    }

    $variant_data = array();
    if (isset($player_data['player'])) {
        $variant_data['variant'] = $player_data['player'];
    }

    // create new player
    if (empty($player_id)) {

        $create = true;

        $variant_data['variant_id'] = $_data['feature_variant_id'] = fn_update_product_feature_variant(PLAYER_FEATURE_ID, 'M', $variant_data);

        $player_id = db_query("INSERT INTO ?:players ?e", $_data);
        $existing_gear = array();

    // update existing player
    } else {

        $arow = db_query("UPDATE ?:players SET ?u WHERE player_id = ?i", $_data, $player_id);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('player'))),'','404');
            $player_id = false;
        }
        $existing_gear = db_get_fields("SELECT product_id FROM ?:players_gear WHERE player_id = ?i", $player_id);
        $variant_data['variant_id'] = db_get_field("SELECT feature_variant_id FROM ?:players WHERE player_id = ?i", $player_id);
        fn_update_product_feature_variant(PLAYER_FEATURE_ID, 'M', $variant_data);
    }

    if (!empty($player_id) && isset($_data['gear'])) {

        // Log player add/update
        fn_log_event('players', !empty($create) ? 'create' : 'update', array(
            'player_id' => $player_id,
        ));

        $_data['gear'] = (empty($_data['gear'])) ? array() : explode(',', $_data['gear']);
        $to_delete = array_diff($existing_gear, $_data['gear']);
        $to_update = array_merge($existing_gear, $_data['gear']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:players_gear WHERE product_id IN (?n) AND player_id = ?i", $to_delete, $player_id);
            db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id IN (?n)", PLAYER_FEATURE_ID, $to_delete);
        }
        $to_add = array_diff($_data['gear'], $existing_gear);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'player_id' => $player_id,
                    'product_id' => $gr
                );
                db_query("REPLACE INTO ?:players_gear ?e", $__data);
                $i_data = array(
                    'feature_id' => PLAYER_FEATURE_ID,
                    'product_id' => $gr,
                    'variant_id' => $variant_data['variant_id'],
                    'lang_code' => DESCR_SL
                );
                db_query("REPLACE INTO ?:product_features_values ?e", $i_data);
            }
        }

        if (!empty($to_update)) {
            foreach ($to_update as $i => $pr_id) {
                $features = db_get_array("SELECT * FROM ?:product_features_values WHERE product_id = ?i", $pr_id);
                FeaturesCache::updateProductFeaturesValue($pr_id, $features, DESCR_SL);
            }
        }
    }

    fn_set_hook('update_player_post', $_data, $player_id);

    return $player_id;

}

function fn_get_technology_data($technology_id)
{
    $field_list = "?:technologies.*";

    fn_set_hook('get_technology_data', $technology_id, $field_list, $join, $condition);

    $technology_data = db_get_row("SELECT $field_list FROM ?:technologies LEFT JOIN ?:product_technologies ON ?:technologies.technology_id = ?:product_technologies.technology_id ?p WHERE ?:technologies.technology_id = ?i  ?p", $join, $technology_id, $condition);

    if (!empty($technology_data)) {
        $technology_data['main_pair'] = fn_get_image_pairs($technology_id, 'technology', 'M', true, true);
        $technology_data['products'] = db_get_fields("SELECT product_id FROM ?:product_technologies WHERE technology_id = ?i", $technology_id);
    }

    fn_set_hook('get_technology_data_post', $technology_data);

    return (!empty($technology_data) ? $technology_data : false);
}

function fn_delete_technology($technology_id)
{
    if (empty($technology_id)) {
        return false;
    }

    // Log technology deletion
    fn_log_event('technologies', 'delete', array(
        'technology_id' => $technology_id,
    ));

    // Deleting technology
    db_query("DELETE FROM ?:technologies WHERE technology_id = ?i", $technology_id);
    db_query("DELETE FROM ?:product_technologies WHERE technology_id = ?i", $technology_id);

    // Deleting technology images
    fn_delete_image_pairs($technology_id, 'technology');

    fn_set_hook('delete_technology', $technology_id);

    return true;
}

function fn_get_technologies($params)
{
    $fields = array (
        '?:technologies.*',
        'GROUP_CONCAT(?:product_technologies.product_id) as products'
    );

    $condition = $join = '';
    $join .= db_quote(" LEFT JOIN ?:product_technologies ON ?:product_technologies.technology_id = ?:technologies.technology_id ");

    if (!empty($params['technology'])) {
        $condition .= db_quote(" AND ?:technologies.name LIKE ?l", "%".trim($params['technology'])."%");
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:technologies.technology_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['product_id'])) {
        $condition .= db_quote(' AND ?:product_technologies.product_id = ?i', $params['product_id']);
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:technologies.technology_id != ?i', $params['except_id']);
    }

    if (AREA == 'C') {
        $condition .= db_quote(' AND ?:product_technologies.product_id IS NOT NULL');
    }

    $limit = '';

    fn_set_hook('get_technologies', $params, $join, $condition, $fields);

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $technologies = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:technologies ?p WHERE 1 ?p GROUP BY ?:technologies.technology_id ORDER BY ?:technologies.name ASC ?p", 'technology_id', $join, $condition, $limit);

    if (empty($technologies)) {
        return array(array(), $params);
    }

    if (empty($params['plain'])) {
        $technologies_images = fn_get_image_pairs(array_keys($technologies), 'technology', 'M', true, false);
        foreach ($technologies as $k => $v) {
            $technologies[$k]['width'] = 80;
            $technologies[$k]['height'] = 80;
            if (!empty($technologies_images[$v['technology_id']])) {
                $technologies[$k]['main_pair'] = reset($technologies_images[$v['technology_id']]);
                $ratio = $technologies[$k]['main_pair']['icon']['image_x'] / $technologies[$k]['main_pair']['icon']['image_y'];
//                 if ($ratio > 1) {
//                     $technologies[$k]['width'] = 80;
//                     $technologies[$k]['height'] = 80;
//                     $technologies[$k]['height'] = round((100 - 50 / $ratio) / $ratio);
//                 } else {
//                     $technologies[$k]['width'] = round((100 - 50 * $ratio) * $ratio);
//                     $technologies[$k]['height'] = 80;
//                     $technologies[$k]['height'] = 80;
//                 }
            }
            $technologies[$k]['products'] = explode(',', $technologies[$k]['products']);
        }
    }

    fn_set_hook('get_technologies_post', $technologies, $params);

    return array($technologies, $params);
}

function fn_update_technology($technology_data, $technology_id = 0)
{
    $_data = $technology_data;

    // create new technology
    if (empty($technology_id)) {

        $create = true;

        $technology_id = db_query("INSERT INTO ?:technologies ?e", $_data);
        $existing_products = array();

    // update existing technology
    } else {

        $arow = db_query("UPDATE ?:technologies SET ?u WHERE technology_id = ?i", $_data, $technology_id);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('technology'))),'','404');
            $technology_id = false;
        }
        $existing_products = db_get_fields("SELECT product_id FROM ?:product_technologies WHERE technology_id = ?i", $technology_id);
    }

    if (!empty($technology_id) && isset($_data['products'])) {

        // Log technology add/update
        fn_log_event('technologies', !empty($create) ? 'create' : 'update', array(
            'technology_id' => $technology_id,
        ));

        $_data['products'] = (empty($_data['products'])) ? array() : explode(',', $_data['products']);
        $to_delete = array_diff($existing_products, $_data['products']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:product_technologies WHERE product_id IN (?n) AND technology_id = ?i", $to_delete, $technology_id);
        }
        $to_add = array_diff($_data['products'], $existing_products);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'technology_id' => $technology_id,
                    'product_id' => $gr
                );
                db_query("REPLACE INTO ?:product_technologies ?e", $__data);
            }
        }
    }

    fn_set_hook('update_technology_post', $_data, $technology_id);

    return $technology_id;

}

function fn_get_state_parts($state)
{
    $state_parts = preg_split("/( |\-)/", trim(str_replace(array('область', 'республика', 'автономный', 'округ', 'автономная', 'край'), array('', '', '', '', '', ''), fn_strtolower($state))));
    foreach ($state_parts as $i => $pr) {
        if (empty($pr) || $pr == '—') {
            unset($state_parts[$i]);
        }
    }

    return $state_parts;
}

function fn_find_state_match($state, $country_code = 'RU')
{
    $state_parts = fn_get_state_parts($state);

    list($states,) = fn_get_states(array('country_code' => $country_code));
    $match = array();
    foreach ($states as $i => $st_dt) {
        $_state_parts = fn_get_state_parts($st_dt['state']);
        $match[$i] = round(100 / count($state_parts) * count(array_intersect($_state_parts, $state_parts)), 2);
    }
    if (!empty($match)) {
        arsort($match);
        return $states[key($match)];
    }

    return false;
}

function fn_get_warehouse_data($warehouse_id)
{
    $field_list = "?:warehouses.*, GROUP_CONCAT(?:warehouse_brands.brand_id SEPARATOR ',') AS brand_ids";

    fn_set_hook('get_warehouse_data', $warehouse_id, $field_list, $join, $condition);

    $warehouse_data = db_get_row("SELECT $field_list FROM ?:warehouses LEFT JOIN ?:warehouse_brands ON ?:warehouse_brands.warehouse_id = ?:warehouses.warehouse_id ?p WHERE ?:warehouses.warehouse_id = ?i  ?p GROUP BY ?:warehouses.warehouse_id", $join, $warehouse_id, $condition);

    if (!empty($warehouse_data['brand_ids'])) {
        $warehouse_data['brand_ids'] = explode(',', $warehouse_data['brand_ids']);
    }

    fn_set_hook('get_warehouse_data_post', $warehouse_data);

    return (!empty($warehouse_data) ? $warehouse_data : false);
}

function fn_delete_warehouse($warehouse_id)
{
    if (empty($warehouse_id) || $warehouse_id == TH_WAREHOUSE_ID) {
        return false;
    }

    // Log warehouse deletion
    fn_log_event('warehouses', 'delete', array(
        'warehouse_id' => $warehouse_id,
    ));

    // Deleting warehouse
    db_query("DELETE FROM ?:warehouses WHERE warehouse_id = ?i", $warehouse_id);
    db_query("DELETE FROM ?:warehouse_brands WHERE warehouse_id = ?i", $warehouse_id);
    db_query("DELETE FROM ?:product_warehouses_inventory WHERE warehouse_id = ?i", $warehouse_id);

    fn_set_hook('delete_warehouse', $warehouse_id);

    return true;
}

function fn_get_product_warehouses($product_id)
{
    $warehouses = array();
    $warehouse_ids = db_get_field("SELECT warehouse_ids FROM ?:products WHERE product_id = ?i", $product_id);
    if (!empty($warehouse_ids)) {
        $warehouse_ids = explode(',', $warehouse_ids);
        $warehouses = db_get_hash_array("SELECT warehouse_id, name FROM ?:warehouses WHERE warehouse_id IN (?n) ORDER BY priority ASC", 'warehouse_id', $warehouse_ids);
    }

    return $warehouses;
}

function fn_promotions_check_warehouses($promo, $product)
{
    $result = array();
    if (!empty($product['wh_amount'])) {
        $result = array_keys($product['wh_amount']);
    }

    return $result;
}

function fn_get_simple_warehouses()
{
    $warehouses = db_get_hash_single_array("SELECT warehouse_id, name FROM ?:warehouses WHERE 1 ORDER BY priority ASC", array('warehouse_id', 'name'));

    return $warehouses;
}

function fn_get_warehouses($params)
{
    $fields = array (
        '?:warehouses.*',
    );

    $condition = $join = '';

    if (!empty($params['warehouse'])) {
        $condition .= db_quote(" AND ?:warehouses.name LIKE ?l", "%".trim($params['warehouse'])."%");
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:warehouses.warehouse_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:warehouses.warehouse_id != ?i', $params['except_id']);
    }

    $limit = '';

    fn_set_hook('get_warehouses', $params, $join, $condition, $fields);

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $warehouses = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:warehouses ?p WHERE 1 ?p GROUP BY ?:warehouses.warehouse_id ORDER BY ?:warehouses.priority ASC ?p", 'warehouse_id', $join, $condition, $limit);

    if (empty($warehouses)) {
        return array(array(), $params);
    }

    fn_set_hook('get_warehouses_post', $warehouses, $params);

    return array($warehouses, $params);
}

function fn_update_warehouse($warehouse_data, $warehouse_id = 0)
{
    $_data = $warehouse_data;

    // create new warehouse
    if (empty($warehouse_id)) {

        $create = true;

        $warehouse_id = db_query("INSERT INTO ?:warehouses ?e", $_data);

        $existing_brands = array();
    // update existing warehouse
    } else {

        $arow = db_query("UPDATE ?:warehouses SET ?u WHERE warehouse_id = ?i", $_data, $warehouse_id);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('warehouse'))),'','404');
            $warehouse_id = false;
        }
        $existing_brands = db_get_fields("SELECT brand_id FROM ?:warehouse_brands WHERE warehouse_id = ?i", $warehouse_id);
    }

    if (!empty($warehouse_id) && isset($_data['brand_ids'])) {

        $to_delete = array_diff($existing_brands, $_data['brand_ids']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:warehouse_brands WHERE brand_id IN (?n) AND warehouse_id = ?i", $to_delete, $warehouse_id);
        }
        $to_add = array_diff($_data['brand_ids'], $existing_brands);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'warehouse_id' => $warehouse_id,
                    'brand_id' => $gr
                );
                db_query("REPLACE INTO ?:warehouse_brands ?e", $__data);
            }
        }

        // Log warehouse add/update
        fn_log_event('warehouses', !empty($create) ? 'create' : 'update', array(
            'warehouse_id' => $warehouse_id,
        ));

    }

    fn_set_hook('update_warehouse_post', $_data, $warehouse_id);

    return $warehouse_id;

}

function fn_get_warehouse_name($warehouse_id)
{
    return db_get_field("SELECT name FROM ?:warehouses WHERE warehouse_id = ?i", $warehouse_id);
}

function fn_development_get_brands()
{
    $params = array(
        'exclude_group' => true,
        'get_descriptions' => true,
        'feature_types' => array('E'),
        'variants' => true,
        'plain' => true,
    );

    list($features) = fn_get_product_features($params, 0);

    $variants = array();

    foreach ($features as $feature) {
        $variants = array_merge($variants, $feature['variants']);
    }

    return $variants;
}

function fn_rebuild_product_options_inventory_multi($product_ids, $products_options = array(), $product_data = array(), $amount = 50)
{
    if (empty($products_options)) {
        $products_options = fn_get_product_options($product_ids, DESCR_SL, true, true);
    }

    if (!empty($products_options)) {

        if (empty($product_data)) {
            $product_data = db_get_hash_array("SELECT product_code, warehouse_ids FROM ?:products WHERE product_id IN (?n)", 'product_id', $product_ids);
        }
        $inventory_data = db_get_hash_multi_array("SELECT combination_hash, amount, product_code, product_id FROM ?:product_options_inventory WHERE product_id IN (?n)", array('product_id', 'combination_hash'), $product_ids);

        $warehouse_data = db_get_hash_multi_array("SELECT * FROM ?:product_warehouses_inventory WHERE product_id IN (?n)", array('product_id', 'warehouse_hash'), $product_ids);
        db_query("DELETE FROM ?:product_warehouses_inventory WHERE product_id IN (?n) AND combination_hash != '0'", $product_ids);

        $new_inventory_data = $new_wh_inventory = $delete_combinations = array();
        foreach ($products_options as $product_id => $_options) {

            if (empty($_options)) {
                continue;
            }
            fn_set_hook('rebuild_product_options_inventory_pre', $product_id, $amount);

            $options = array_keys($_options);

            $variants = $variant_codes = array();
            foreach ($_options as $k => $option) {
                $variants[] = array_keys($option['variants']);
                $_codes = array();
                foreach ($option['variants'] as $vr_id => $vr_data) {
                    $_codes[$vr_id] = $vr_data['code_suffix'];
                }
                $variant_codes[$k] = $_codes;
            }
            fn_set_hook('look_through_variants_pre', $product_id, $amount, $options, $variants);

            $position = 0;
            $hashes = array();
            $combinations = fn_get_options_combinations($options, $variants);

            if (!empty($combinations)) {
                foreach ($combinations as $combination) {

                    $_data = array();
                    $_data['product_id'] = $product_id;

                    $_data['combination_hash'] = fn_generate_cart_id($product_id, array('product_options' => $combination));

                    if (array_search($_data['combination_hash'], $hashes) === false) {
                        $hashes[] = $_data['combination_hash'];
                        $_data['combination'] = fn_get_options_combination($combination);
                        $_data['position'] = $position++;

                        $_data['product_code'] = (!empty($product_data[$product_id]['product_code'])) ? $product_data[$product_id]['product_code'] : '';
                        foreach ($combination as $option_id => $variant_id) {
                            if (isset($variant_codes[$option_id][$variant_id])) {
                                $_data['product_code'] .= $variant_codes[$option_id][$variant_id];
                            }
                        }

                        $_data['amount'] = isset($inventory_data[$product_id][$_data['combination_hash']]['amount']) ? $inventory_data[$product_id][$_data['combination_hash']]['amount'] : $amount;
        //                $_data['product_code'] = isset($inventory_data[$product_id][$_data['combination_hash']]['product_code']) ? $inventory_data[$product_id][$_data['combination_hash']]['product_code'] : '';

                        fn_set_hook('look_through_variants_update_combination', $combination, $_data, $product_id, $amount, $options, $variants);

                        $new_inventory_data[] = $_data;
//                         $combinations[] = $combination;
                    }
//                     echo str_repeat('. ', count($combination));
                }
                if (!empty($hashes)) {
                    $warehouse_ids = explode(',', $product_data[$product_id]['warehouse_ids']);
                    if (!empty($warehouse_ids)) {
                        foreach ($combinations as $combination) {
                            foreach ($warehouse_ids as $i => $wh_id) {
                                $wh_hash = fn_generate_cart_id($product_id, array('product_options' => $combination, 'warehouse_id' => $wh_id));
                                $new_wh_inventory[] = array(
                                    'warehouse_hash' => $wh_hash,
                                    'warehouse_id' => $wh_id,
                                    'product_id' => $product_id,
                                    'combination_hash' => fn_generate_cart_id($product_id, array('product_options' => $combination)),
                                    'amount' => isset($warehouse_data[$product_id][$wh_hash]['amount']) ? $warehouse_data[$product_id][$wh_hash]['amount'] : 0
                                );
                            }
                        }
                    }
                }
            }

            fn_set_hook('look_through_variants_post', $combinations, $product_id, $amount, $options, $variants);

            if (!empty($inventory_data[$product_id])) {
                $delete_combinations = array_merge($delete_combinations, array_diff(array_keys($inventory_data[$product_id]), $hashes));
            }

            fn_set_hook('rebuild_product_options_inventory_post', $product_id);
        }

        if (!empty($delete_combinations)) {
            db_query("DELETE FROM ?:product_options_inventory WHERE combination_hash IN (?n)", $delete_combinations);
            foreach ($delete_combinations as $v) {
                fn_delete_image_pairs($v, 'product_option');
            }
        }
        if (!empty($new_inventory_data)) {
            db_query("REPLACE INTO ?:product_options_inventory ?m", $new_inventory_data);
        }
        if (!empty($new_wh_inventory)) {
            db_query("REPLACE INTO ?:product_warehouses_inventory ?m", $new_wh_inventory);
        }
    }
}

function fn_get_state_names($state_ids, $state_name)
{
    $result = array();
    foreach ($state_ids as $id) {
        $result[$id] = $state_name[$id];
    }

    return $result;
}
