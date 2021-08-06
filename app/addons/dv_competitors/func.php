<?php

use Tygh\Registry;
use Tygh\CmpUpdater\CmpUpdater;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_update_cmp($mode)
{
    $competitors = db_get_fields("SELECT competitor_id FROM ?:competitors WHERE status = 'A'");
    $results = $status = array();
    $func = 'update' . $mode;
    $_SESSION['cmp_update_start'] = time();
    foreach ($competitors as $cmp_id) {
        list($status[$cmp_id], $results[$cmp_id]) = CmpUpdater::call($cmp_id, $func);
    }
    unset($_SESSION['cmp_update_start']);

    return array($status, $results);
}

function fn_update_competitive_catalog()
{
    return fn_update_cmp('Competitor');
}

function fn_update_competitive_prices($data = array())
{
    return fn_update_cmp('Prices');
}

function fn_actualize_prices()
{
    $details = array();

    $params = array(
        // 'hide_out_of_stock' => 'Y',
        'show_out_of_stock' => true,
        'competition' => array(
            'mode' => 'A',
            'status' => 'A',
            // 'in_stock' => 'Y'
        ),
        // 'pid' => 1607,
        'price_mode' => 'M'
    );
    list($products, $search) = fn_get_products($params);

    if (!empty($products)) {
        $data = array();
        foreach ($products as $product) {
            if (!empty($product['main_competitor'])) {
                $new_price = $product['main_competitor']['price'];
            } else {
                $new_price = 0;
            }
            $link = '<a href="' . fn_url('products.update?product_id=' . $product['product_id'], 'A', 'current', CART_LANGUAGE, true) . '" target="_blank">' . $product['product'] . '</a>';
            if (!empty($new_price) && (($product['price'] > $new_price && in_array($product['competitor_price_action'], array('B', 'D'))) || ($product['price'] < $new_price && in_array($product['competitor_price_action'], array('B', 'U')) && $product['amount'] > 0))) {
                $data[] = array(
                    'product_id' => $product['product_id'],
                    'price' => $new_price,
                    'lower_limit' => 1,
                    'type' => 'A',
                    'usergroup_id' => 0
                );
                $details[$link] = array(
                    $product['main_competitor']['item_id'] => '<a href="' . $product['main_competitor']['link'] . '" target="_blank">' . $product['main_competitor']['name'] . '</a>',
                    'price' => fn_format_price($product['price']) . ' -> ' . fn_format_price($new_price)
                );
            }
            $list_price = max($new_price ?? 0, $product['price']);
            if (!empty($list_price) && $list_price > 0 && (empty($product['list_price']) || $product['list_price'] == 0 || $product['list_price'] < $list_price)) {
                db_query("UPDATE ?:products SET list_price = ?i WHERE product_id = ?i", $list_price, $product['product_id']);
                $details[$link]['list_price'] = fn_format_price($product['list_price']) . ' -> ' . fn_format_price($list_price);
            }
        }

        if (!empty($data)) {
            foreach ($data as $prices) {
                fn_update_product_prices($prices['product_id'], $prices);
            }
        }
    }

    return array(true, $details);
}

function fn_get_competitors($params = array())
{
    $fields = array (
        '?:competitors.*',
    );

    $condition = $join = '';

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:competitors.competitor_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:competitors.competitor_id != ?i', $params['except_id']);
    }

    $limit = '';

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $competitors = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:competitors ?p WHERE 1 ?p ORDER BY ?:competitors.competitor_id ASC ?p", 'competitor_id', $join, $condition, $limit);

    if (empty($competitors)) {
        return array(array(), $params);
    }

    return array($competitors, $params);
}

function fn_get_competitors_list()
{
    list($competitors,) = fn_get_competitors();

    return $competitors;
}

function fn_update_competitor($competitor_data, $competitor_id = 0)
{
    $_data = $competitor_data;

    // create new competitor
    if (empty($competitor_id)) {

        $create = true;

        $competitor_id = db_query("INSERT INTO ?:competitors ?e", $_data);

    // update existing competitor
    } else {

        $arow = db_query("UPDATE ?:competitors SET ?u WHERE competitor_id = ?i", $_data, $competitor_id);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('competitor'))),'','404');
            $competitor_id = false;
        }
    }

    if (!empty($competitor_id) && isset($_data['brand_ids'])) {

        // Log competitor add/update
        fn_log_event('competitors', !empty($create) ? 'create' : 'update', array(
            'competitor_id' => $competitor_id,
        ));

    }

    return $competitor_id;

}

function fn_get_competitor_data($competitor_id)
{
    $field_list = "?:competitors.*";
    $join = $condition = '';

    $competitor_data = db_get_row("SELECT $field_list FROM ?:competitors ?p WHERE ?:competitors.competitor_id = ?i ?p", $join, $competitor_id, $condition);

    if (!empty($competitor_data['update_log'])) {
        $competitor_data['update_log'] = unserialize($competitor_data['update_log']);
    }

    return (!empty($competitor_data) ? $competitor_data : false);
}

function fn_delete_competitor($competitor_id)
{
    if (empty($competitor_id)) {
        return false;
    }

    // Log competitor deletion
    fn_log_event('competitors', 'delete', array(
        'competitor_id' => $competitor_id,
    ));

    // Deleting competitor
    db_query("DELETE FROM ?:competitors WHERE competitor_id = ?i", $competitor_id);
    db_query("DELETE FROM ?:competitive_pairs WHERE competitor_id = ?i", $competitor_id);
    db_query("DELETE FROM ?:competitive_prices WHERE competitor_id = ?i", $competitor_id);

    return true;
}

function fn_dv_competitors_get_product_data($product_id, &$field_list, &$join, $auth, $lang_code, &$condition, $get_add_pairs, $get_main_pair, $get_taxes, $get_qty_discounts, $preview, $features, $skip_company_condition)
{
    if (AREA == 'A' && !empty($get_add_pairs) && !empty($get_main_pair)) {
        $join .= db_quote(" LEFT JOIN ?:competitive_pairs ON ?:competitive_pairs.product_id = ?:products.product_id AND ?:competitive_pairs.action = 'T' LEFT JOIN ?:competitive_prices AS cp1 ON cp1.item_id = ?:competitive_pairs.competitive_id LEFT JOIN ?:competitors AS cmp1 ON cmp1.competitor_id = cp1.competitor_id LEFT JOIN ?:competitive_prices AS cp ON cp.code = ?:products.product_code LEFT JOIN ?:competitive_pairs AS cpr ON cpr.competitive_id = cp.item_id AND cpr.product_id = ?:products.product_id LEFT JOIN ?:competitors AS cmp ON cmp.competitor_id = cp.competitor_id");
        $field_list .= ", GROUP_CONCAT(CONCAT_WS('|', CONCAT_WS('_', cp.item_id, cp.price, cp.competitor_id, cp.in_stock, cmp.status, IF(cpr.action IS NULL, 'T', cpr.action), 0), CONCAT_WS('_', cp1.item_id, cp1.price, cp1.competitor_id, cp1.in_stock, cmp1.status, ?:competitive_pairs.action, 1)) SEPARATOR '|') AS competitors";
    }
}

function fn_explode_competitors(&$products, $competition_params = array())
{
    if (!empty($products)) {
        $item_ids = array();
        foreach ($products as $i => &$product) {
            if (!empty($product['competitors'])) {
                $pairs = explode('|', $product['competitors']);
                $comp = $sorting = $sorted = array();
                foreach ($pairs as $pair) {
                    $p_data = explode('_', $pair);
                    if (count($p_data) == 7) {
                        $keys = array('item_id', 'price', 'competitor_id', 'in_stock', 'status', 'action', 'pair_id');
                        $_pr = array_combine($keys, $p_data);
                        if ($_pr['action'] == 'U') {
                            continue;
                        }
                        $passed = true;
                        if (!empty($competition_params)) {
                            $conditions = array_intersect(array_keys($competition_params), $keys);
                            if (!empty($conditions)) {
                                foreach ($conditions as $cnd) {
                                    if ($_pr[$cnd] != $competition_params[$cnd]) {
                                        $passed = false;
                                        break;
                                    }
                                }
                            }
                        }
                        if (!empty($passed)) {
                            if (empty($comp[$_pr['competitor_id']])) {
                                $comp[$_pr['competitor_id']] = $_pr;
                                $sorting[$_pr['competitor_id']] = $_pr['price'];
                                $item_ids[] = $_pr['item_id'];
                            } elseif ((empty($comp[$_pr['competitor_id']]['pair_id']) && !empty($_pr['pair_id'])) || $comp[$_pr['competitor_id']]['price'] > $_pr['price']) {
                                $comp[$_pr['competitor_id']] = $_pr;
                                $sorting[$_pr['competitor_id']] = $_pr['price'];
                                $item_ids[] = $_pr['item_id'];
                            }
                        }
                    }
                }
                asort($sorting);
                foreach ($sorting as $key => $price) {
                    $sorted[$key] = $comp[$key];
                }
                $product['competitors'] = $sorted;
            }
        }
        if (!empty($item_ids)) {
            $item_data = db_get_hash_array("SELECT cp.item_id, cp.name, cp.code, cp.link, cmp.name as competitor_name FROM ?:competitive_prices AS cp LEFT JOIN ?:competitors AS cmp ON cmp.competitor_id = cp.competitor_id WHERE cp.item_id IN (?n)", 'item_id', $item_ids);
        }
        foreach ($products as $j => &$product) {
            if (!empty($product['competitors'])) {
                $result = array();
                foreach ($product['competitors'] as $i => &$_cmp) {
                    if (!empty($item_data[$_cmp['item_id']])) {
                        $_cmp = array_merge($_cmp, $item_data[$_cmp['item_id']]);
                    }
                    if ($_cmp['in_stock'] == 'Y' && ($_cmp['price'] > $product['net_cost'] * Registry::get('currencies.' . $product['net_currency_code'] . '.coefficient') || !empty($_cmp['pair_id'])) && (empty($result) || $result['price'] > $_cmp['price'])) {
                        $result = $i;
                    }
                }
                if (!empty($result)) {
                    $product['competitors'][$result]['is_main'] = true;
                    $product['main_competitor'] = $product['competitors'][$result];
                    unset($product['competitors'][$result]);
                    array_unshift($product['competitors'], $product['main_competitor']);
                }
            }
            if (!empty($competition_params['mode'])) {
                if ($competition_params['mode'] == 'D' && (empty($product['main_competitor']) || $product['main_competitor']['price'] == $product['price'])) {
                    unset($products[$j]);
                }
                if ($competition_params['mode'] == 'N' && !empty($product['competitors'])) {
                    unset($products[$j]);
                }
            }
        }
    }
}

function fn_dv_competitors_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    if (!empty($product_data['competitors'])) {
        $products = array($product_data);
        fn_explode_competitors($products);
        $product_data = reset($products);
    }
}

function fn_dv_competitors_update_product_post($product_data, $product_id, $lang_code, $create)
{
    if (!empty($product_data['competitor_pair']['c_id']) && !empty($product_data['competitor_pair']['action']) && !empty($product_data['competitor_pair']['obj_id'])) {
            db_query("DELETE FROM ?:competitive_pairs WHERE product_id = ?i AND competitive_id = ?i", $product_id, $product_data['competitor_pair']['obj_id']);

            $code = db_get_field("SELECT code FROM ?:competitive_prices WHERE item_id = ?i", $product_data['competitor_pair']['obj_id']);
            if ($product_data['competitor_pair']['action'] == 'T') {
                $data = array(
                    'competitive_id' => $product_data['competitor_pair']['obj_id'],
                    'product_id' => $product_id,
                    'competitor_id' => $product_data['competitor_pair']['c_id'],
                    'action' => 'T'
                );
                db_query("REPLACE INTO ?:competitive_pairs ?e", $data);
            } elseif ($product_data['competitor_pair']['action'] == 'U' && !empty($code) && $code == $product_data['product_code']) {
                $data = array(
                    'competitive_id' => $product_data['competitor_pair']['obj_id'],
                    'product_id' => $product_id,
                    'competitor_id' => $product_data['competitor_pair']['c_id'],
                    'action' => 'U'
                );
                db_query("REPLACE INTO ?:competitive_pairs ?e", $data);
            }
        // } else {
        //     db_query("DELETE FROM ?:competitive_pairs WHERE product_id = ?i AND competitor_id = ?i", $product_id, $product_data['competitor_pair']['c_id']);
        // }
    }
}
function fn_dv_competitors_get_products(&$params, &$fields, &$sortings, &$condition, &$join, $sorting, &$group_by, $lang_code, $having)
{
    if (!empty($params['competition'])) {
        $join .= db_quote(" LEFT JOIN ?:competitive_pairs ON ?:competitive_pairs.product_id = products.product_id AND ?:competitive_pairs.action = 'T' LEFT JOIN ?:competitive_prices AS cp1 ON cp1.item_id = ?:competitive_pairs.competitive_id LEFT JOIN ?:competitors AS cmp1 ON cmp1.competitor_id = cp1.competitor_id LEFT JOIN ?:competitive_prices AS cp ON cp.code = products.product_code LEFT JOIN ?:competitive_pairs AS cpr ON cpr.competitive_id = cp.item_id AND cpr.product_id = products.product_id LEFT JOIN ?:competitors AS cmp ON cmp.competitor_id = cp.competitor_id");
        $fields[] = "GROUP_CONCAT(CONCAT_WS('|', CONCAT_WS('_', cp.item_id, cp.price, cp.competitor_id, cp.in_stock, cmp.status, IF(cpr.action IS NULL, 'T', cpr.action), 0), CONCAT_WS('_', cp1.item_id, cp1.price, cp1.competitor_id, cp1.in_stock, cmp1.status, ?:competitive_pairs.action, 1)) SEPARATOR '|') AS competitors";
    }
}
function fn_dv_competitors_get_products_post(&$products, &$params, $lang_code)
{
    fn_explode_competitors($products, $params['competition'] ?? array());
}
