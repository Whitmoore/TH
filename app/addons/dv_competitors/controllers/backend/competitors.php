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
use Tygh\CmpUpdater\CmpUpdater;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_REQUEST['competitor_id'] = empty($_REQUEST['competitor_id']) ? 0 : $_REQUEST['competitor_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'competitor_data',
        'competitors_data'
    );

    //
    // Create/update competitor
    //
    if ($mode == 'update') {

        $competitor_id = fn_update_competitor($_REQUEST['competitor_data'], $_REQUEST['competitor_id']);

        if (!empty($competitor_id)) {
            $suffix = ".update?competitor_id=$competitor_id" . (!empty($_REQUEST['competitor_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['competitor_data']['block_id'] : "");
        } else {
            $suffix = '.manage';
        }
    }

    //
    // Processing deleting of multiple competitor elements
    //
    if ($mode == 'm_delete') {

        if (isset($_REQUEST['competitor_ids'])) {
            foreach ($_REQUEST['competitor_ids'] as $v) {
                if (fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('ULTIMATE') && fn_check_company_id('competitors', 'competitor_id', $v))) {
                    fn_delete_competitor($v);
                }
            }
        }

        unset($_SESSION['competitor_ids']);

        fn_set_notification('N', __('notice'), __('text_competitors_have_been_deleted'));
        $suffix = ".manage";
    }

    if ($mode == 'm_update') {
        if (!empty($_REQUEST['competitors_data'])) {
            foreach ($_REQUEST['competitors_data'] as $k => $v) {
                fn_update_competitor($v, $k);
            }
        }
        $suffix = ".manage";
    }

    if ($mode == 'eqialize_prices') {
        if (!empty($_REQUEST['product_ids'])) {
            $result = $data = array();
            foreach ($_REQUEST['product_ids'] as $pair) {
                $_pair = explode(',', $pair);
                $result[$_pair[0]] = $_pair[1];
            }
            if (!empty($action) && $action == 'discounts') {
                $prices = db_get_hash_single_array("SELECT product_id, price FROM ?:product_prices WHERE product_id IN (?n) AND lower_limit = '1'", array('product_id', 'price'), array_keys($result));
                if (!empty($prices)) {
                    foreach ($prices as $prod_id => $prc) {
                        db_query("UPDATE ?:products SET list_price = IF (list_price > ?i, list_price, ?i) WHERE product_id = ?i", $prc, $prc, $prod_id);
                    }
                }
            }
            $price = db_get_hash_single_array("SELECT item_id, price FROM ?:competitive_prices WHERE item_id IN (?n)", array('item_id', 'price'), array_values($result));
            foreach ($result as $product_id => $item_id) {
                $data[] = array(
                    'product_id' => $product_id,
                    'price' => $price[$item_id],
                    'type' => 'A',
                    'lower_limit' => 1,
                    'usergroup_id' => 0
                );
            }
            if (!empty($data)) {
                foreach ($data as $prices) {
                    fn_update_product_prices($prices['product_id'], $prices);
                }
            }
        }

        $suffix = ".prices?mode=" . $_REQUEST['mode'] . (!empty($_REQUEST['c_id']) ? '&c_id=' . $_REQUEST['c_id'] : '') . (!empty($_REQUEST['cid']) ? '&cid=' . $_REQUEST['cid'] : '');
    }

    if ($mode == 'add_pairs') {
        if (!empty($_REQUEST['pairs'])) {
            $data = $to_delete = array();
            foreach ($_REQUEST['pairs'] as $product_id => $_dt) {
                if (!empty($_dt['c_id'])) {
                    if (empty($_dt['obj_id'])) {
                        $to_delete[$_dt['c_id']][] = $product_id;
                    } elseif (!empty($_dt['c_id'])) {
                        $data[] = array(
                            'competitive_id' => $_dt['obj_id'],
                            'product_id' => $product_id,
                            'competitor_id' => $_dt['c_id']
                        );
                    }
                }
            }
            if (!empty($to_delete)) {
                foreach ($to_delete as $c_id => $ids) {
                    db_query("DELETE FROM ?:competitive_pairs WHERE product_id IN (?n) AND competitor_id = ?i", $ids, $c_id);
                }
            }
            if (!empty($data)) {
                db_query("REPLACE INTO ?:competitive_pairs ?m", $data);
            }
        }
        $suffix = ".prices?mode=" . $_REQUEST['mode'] . (!empty($_REQUEST['c_id']) ? '&c_id=' . $_REQUEST['c_id'] : '') . (!empty($_REQUEST['cid']) ? '&cid=' . $_REQUEST['cid'] : '');
    }

    if ($mode == 'autocomplete_cproducts') {

        if (!empty($_REQUEST['q']) && !empty($_REQUEST['c_id'])) {

            $params['q'] = trim($_REQUEST['q']);
            $pieces = fn_explode(' ', $params['q']);
            $search_type = ' AND ';

            foreach ($pieces as $piece) {
                if (strlen($piece) == 0) {
                    continue;
                }

                $_condition[] = db_quote("name LIKE ?l", '%' . $piece . '%');
            }

            $_cond = implode($search_type, $_condition);
            $_cond .= db_quote(" AND competitor_id = ?i", $_REQUEST['c_id']);

            $products = db_get_array("SELECT * FROM ?:competitive_prices WHERE $_cond ORDER BY item_id DESC");

            Registry::get('view')->assign('results', $products);
            Registry::get('view')->assign('ac_done', true);
            Registry::get('view')->assign('product_id', $_REQUEST['id']);
            Registry::get('view')->display('addons/dv_competitors/views/competitors/prices_results.tpl');
        }
        exit;
    }

    if ($mode == 'check_pair') {

        if (!empty($_REQUEST['p_id']) && !empty($_REQUEST['c_id'])) {

            $pair = db_get_row("SELECT a.competitive_id, b.name FROM ?:competitive_pairs AS a LEFT JOIN ?:competitive_prices AS b ON a.competitive_id = b.item_id WHERE a.product_id = ?i AND a.competitor_id = ?i AND a.action = 'T'", $_REQUEST['p_id'], $_REQUEST['c_id']);

            if (empty($pair)) {
                $pair = db_get_row("SELECT a.item_id as competitive_id, a.name FROM ?:competitive_prices AS a LEFT JOIN ?:products AS b ON a.code = b.product_code LEFT JOIN ?:competitive_pairs AS c ON c.competitive_id = a.item_id WHERE b.product_id = ?i AND a.competitor_id = ?i AND (c.competitive_id IS NULL OR c.action != 'U')", $_REQUEST['p_id'], $_REQUEST['c_id']);
            }

            $product = array(
                'product_id' => $_REQUEST['p_id'],
                'product' => fn_get_product_name($_REQUEST['p_id'])
            );

            Registry::get('view')->assign('input', $_REQUEST['input']);
            Registry::get('view')->assign('pair', $pair);
            Registry::get('view')->assign('product', $product);
            Registry::get('view')->assign('c_id', $_REQUEST['c_id']);
            Registry::get('view')->display('addons/dv_competitors/common/add_pair.tpl');
        }
        exit;
    }

    if ($mode == 'parse') {

        if (!empty($_REQUEST['link']) && !empty($_REQUEST['competitor_id'])) {

            list($status, $result) = CmpUpdater::call($_REQUEST['competitor_id'], 'testParse', array($_REQUEST['link']));

            Registry::get('view')->assign('result', $result[0]);
            Registry::get('view')->assign('competitor_id', $_REQUEST['competitor_id']);
            Registry::get('view')->assign('link', $_REQUEST['link']);
            Registry::get('view')->display('addons/dv_competitors/common/parse_link.tpl');
        }
        exit;
    }

    return array(CONTROLLER_STATUS_OK, "competitors$suffix");
}

//
// 'Add new competitor' page
//
if ($mode == 'add') {

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
    ));
    // [/Page sections]

//
// 'competitor update' page
//
} elseif ($mode == 'update') {

    $competitor_id = $_REQUEST['competitor_id'];
    // Get current competitor data
    $competitor_data = fn_get_competitor_data($competitor_id);

    if (empty($competitor_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }


    // [Page sections]
    $tabs = array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'parsing' => array(
            'title' => __('parsing'),
            'js' => true
        )
    );
    if (!empty($competitor_data['update_log'])) {
        $tabs['update_log'] = array(
            'title' => __('update_log'),
            'js' => true
        );
    }

    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]
    Registry::get('view')->assign('competitor_data', $competitor_data);

}
//
// Delete competitor
//
elseif ($mode == 'delete') {

    if (!empty($_REQUEST['competitor_id'])) {
        fn_delete_competitor($_REQUEST['competitor_id']);
    }

    fn_set_notification('N', __('notice'), __('text_competitor_has_been_deleted'));

    return array(CONTROLLER_STATUS_REDIRECT, "competitors.manage");

//
// 'Management' page
//
} elseif ($mode == 'manage') {

    $params = $_REQUEST;
    list($competitors, $search) = fn_get_competitors($params);

    Registry::get('view')->assign('competitors', $competitors);
    Registry::get('view')->assign('search', $search);

} elseif ($mode == 'prices') {

    $cp_mode = empty($_REQUEST['mode']) ? 'D' : $_REQUEST['mode'];

    if ($cp_mode == 'N') {
        $params = array(
            'hide_out_of_stock' => 'Y',
            'competition' => array(
                'mode' => 'N'
            ),
            'sort_by' => 'price',
            'sort_order' => 'desc',
            // 'pid' => 1285,
        );
        if (!empty($_REQUEST['c_id'])) {
            $params['competition']['competitor_id'] = $_REQUEST['c_id'];
        }
        list($products, $search) = fn_get_products($params);
    } elseif ($cp_mode == 'D') {
        $params = array(
            // 'hide_out_of_stock' => 'Y',
            'competition' => array(
                'mode' => 'D',
            ),
            // 'pid' => 1838,
            // 'pid' => 787,
        );
        if (!empty($_REQUEST['c_id'])) {
            $params['competition']['competitor_id'] = $_REQUEST['c_id'];
        }
        list($products, $search) = fn_get_products($params);
    } elseif ($cp_mode == 'A' && !empty($_REQUEST['cid']) && !empty($_REQUEST['c_id'])) {
        $params = array(
            'competition' => array(
                'mode' => 'A',
                'competitor_id' => $_REQUEST['c_id']
            ),
            'hide_out_of_stock' => 'Y',
            'cid' => $_REQUEST['cid'],
            // 'pid' => 1285,
        );
        list($products, $search) = fn_get_products($params);
    }


    $result = $_result = array();
    if (!empty($products)) {
        foreach ($products as $product) {
            if (empty($result[$product['main_category']])) {
                $result[$product['main_category']]['category_id'] = $product['main_category'];
            }
            $result[$product['main_category']]['products'][] = $product;
        }
        $sorting = db_get_fields("SELECT category_id FROM ?:categories WHERE category_id IN (?n) ORDER BY parent_id, position", array_keys($result));
        foreach ($sorting as $cid) {
            $_result[$cid] = $result[$cid];
        }
    }
    list($competitors,) = fn_get_competitors();

    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('mode', $cp_mode);
    Registry::get('view')->assign('c_id', $_REQUEST['c_id']);
    Registry::get('view')->assign('competitive_prices', $_result);
    Registry::get('view')->assign('competitors', $competitors);

} elseif ($mode == 'update_competitor') {

        if (!empty($_REQUEST['competitor_id'])) {
            list($status, $results) = CmpUpdater::call($_REQUEST['competitor_id'], 'updateCompetitor');
        }

        return array(CONTROLLER_STATUS_REDIRECT, "competitors.update?competitor_id=" . $_REQUEST['competitor_id']);

} elseif ($mode == 'update_competitors' && !empty($_REQUEST['product_id'])) {

    $competitors = db_get_hash_multi_array("SELECT cp.* FROM ?:products LEFT JOIN ?:competitive_prices AS cp ON ?:products.product_code = cp.code LEFT JOIN ?:competitive_pairs ON ?:competitive_pairs.competitive_id = cp.item_id WHERE ?:products.product_id = ?i OR ?:competitive_pairs.product_id = ?i", array('competitor_id', 'item_id'), $_REQUEST['product_id'], $_REQUEST['product_id']);

    foreach ($competitors as $comp_id => $cmts) {
        list($status, $results) = CmpUpdater::call($comp_id, 'updatePrices', array($cmts));
    }

    return array(CONTROLLER_STATUS_REDIRECT, "products.update?product_id=" . $_REQUEST['product_id']);
}
