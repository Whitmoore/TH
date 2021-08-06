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

use Tygh\FeaturesCache;
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_development_clone_product_options_post($from_product_id, $to_product_id, $from_global_option_id)
{
    fn_update_product_exceptions($to_product_id);
}

function fn_development_dispatch_before_display()
{
    if (empty($_SESSION['hide_anouncement'])) {
        $anouncement = db_get_row("SELECT text, class FROM ?:anouncements WHERE start_timestamp <= ?i AND end_timestamp + 86399 >= ?i ORDER BY priority ASC", TIME, TIME);
        if (!empty($anouncement)) {
            fn_parse_catalog_promo($anouncement['text'], 'anouncements');
            Registry::get('view')->assign('anouncement', $anouncement);
        }
    }
}

function fn_development_update_promotion_post($data, $promotion_id)
{
    if ($data['zone'] == 'cart') {
        $bonuses = unserialize($data['bonuses']);
        $is_item_discount = 'N';
        if (!empty($bonuses)) {
            foreach ($bonuses as $bonus) {
                if (in_array($bonus['bonus'], array('discount_on_products', 'discount_on_categories', 'cart_product_discount'))) {
                    $is_item_discount = 'Y';
                    break;
                }
            }
        }
        db_query("UPDATE ?:promotions SET is_item_discount = ?s WHERE promotion_id = ?i", $is_item_discount, $promotion_id);
    }
}

function fn_development_delete_category_post($category_id, $recurse, $category_ids)
{
    db_query("DELETE FROM ?:category_feature_seo WHERE category_id = ?i", $category_id);
}

function fn_development_get_filters_products_count_query_params(&$values_fields, $join, $sliders_join, $feature_ids, $where, $sliders_where, $filter_vq, $filter_rq)
{
    $values_fields[] = '?:product_features.seo_variants';
}

function fn_development_get_product_filters_before_select($fields, $join, &$condition, $group_by, $sorting, $limit, $params, $lang_code)
{
    if (isset($params['feature_parent_id'])) {
        if (!empty($params['feature_parent_id'])) {
            $condition .= db_quote(" AND ?:product_features.parent_id = ?i", $params['feature_parent_id']);
        } else {
            $condition .= db_quote(" AND (?:product_features.parent_id = '0' OR ?:product_features.parent_id IS NULL)", $params['feature_parent_id']);
        }
    }
    if (!empty($params['feature_type'])) {
        $types = is_array($params['feature_type']) ? $params['feature_type'] : fn_explode(',', $params['feature_type']);
        $condition .= db_quote(" AND ?:product_features.feature_type IN (?a)", $params['feature_type']);
    }
}

function fn_development_get_promotions($params, $fields, $sortings, &$condition, $join)
{
    if (!empty($params['show_on_site'])) {
        $condition .= db_quote(" AND ?:promotions.show_on_site = ?s", $params['show_on_site']);
    }
    if (!empty($params['is_item_discount'])) {
        $condition .= db_quote(" AND ?:promotions.is_item_discount = ?s", $params['is_item_discount']);
    }
}

function fn_development_get_user_info_before($condition, $user_id, &$user_fields, &$join)
{
    if (AREA == 'A') {
        $user_fields[] = '?:ekeys.ekey';
        $user_fields[] = '?:ekeys.ttl';
        $join .= " LEFT JOIN ?:ekeys ON ?:ekeys.object_id = ?:users.user_id AND ?:ekeys.object_type = 'L'";
    }
}

function fn_development_update_profile($action, $user_data, $current_user_data)
{
    if ($action == 'add') {
        fn_assign_user_posts($user_data['user_id']);
    }
}

function fn_development_login_user_post($user_id, $cu_id, $udata, $auth, $condition, $result)
{
    if ($result == LOGIN_STATUS_OK) {
        fn_assign_user_posts($user_id);
    }
}

function fn_development_delete_post_pre($post_id)
{
    $post_data = db_get_row("SELECT ?:discussion_posts.user_id, ?:discussion_posts.is_rewarded, ?:discussion.object_type, ?:discussion.object_id FROM ?:discussion_posts INNER JOIN ?:users ON ?:users.user_id = ?:discussion_posts.user_id LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id WHERE ?:discussion_posts.post_id = ?i", $post_id);
    if (!empty($post_data) && !empty($post_data['user_id']) && !empty($post_data['object_type']) && $post_data['object_type'] == 'P' && $post_data['is_rewarded'] == 'Y') {
        fn_change_user_points(-Registry::get('addons.development.product_review'), $post_data['user_id'], serialize(array('post_id' => $post_id, 'object_id' => $post_data['object_id'], 'type' => $post_data['object_type'])), CHANGE_DUE_REVIEW);
    }
}

function fn_development_set_point_payment(&$user_points, $auth)
{
    if (empty($auth['user_id']) && !empty($_SESSION['post_ids'])) {
        $settings = Registry::get('addons.development');

        foreach ($_SESSION['post_ids'] as $type => $posts) {
            if (!empty($settings['review_reward_' . $type])) {
                $count = 0;
                foreach ($posts as $p_id => $post) {
                    if (!empty($post['is_rewarded']) && $post['is_rewarded'] == 'Y') {
                        $count++;
                    }
                }
                $user_points += $count < $settings['review_number_limit_' . $type] ? $count * $settings['review_reward_' . $type] : $settings['review_number_limit_' . $type] * $settings['review_reward_' . $type];
            }
        }
        $_SESSION['user_points'] = $user_points;
    }
}

function fn_development_add_post_post($post_data, $object)
{
    $auth = $_SESSION['auth'];
    if (empty($auth['user_id'])) {
        $_SESSION['post_ids'][$object['object_type']] = empty($_SESSION['post_ids'][$object['object_type']]) ? array() : $_SESSION['post_ids'][$object['object_type']];
        $_SESSION['post_ids'][$object['object_type']][$post_data['post_id']] = $post_data;
    }
    $allow_reward = fn_allow_user_thread_review_reward($post_data['thread_id'], $object['object_type'], $post_data['user_id'], $post_data['post_id']);
    if (AREA == 'C' && $object['object_type'] == 'P') {
        $product = fn_get_product_data($object['object_id'], $auth, CART_LANGUAGE, '', true, true, true, true, fn_is_preview_action($auth, $_REQUEST));
        fn_gather_additional_product_data($product, true, true);
        fn_get_product_review_discount($product);
        Registry::get('view')->assign('review_discount', !empty($product['review_discount']) ? $product['review_discount'] : false);
        Registry::get('view')->assign('product', $product);
    }

    if (AREA == 'C' && !empty($_REQUEST['force_review']) && $_REQUEST['force_review'] == 'Y') {
        $discussion = $object;
        $discussion['post_data'] = $post_data;
        $discussion['thread_id'] = $post_data['thread_id'];
        Registry::get('view')->assign('discussion', $discussion);
        Registry::get('view')->assign('allow_reward', $allow_reward);

        Registry::get('view')->assign('obj_id', $object['object_id']);
        $msg = Registry::get('view')->fetch('addons/discussion/views/discussion/components/force_review.tpl');
        fn_delete_notification('thank_you_for_review');
        fn_set_notification('I', __('thank_you_for_rating'), $msg);
    } else {
        fn_set_notification('N', __('notice'), __('thank_you_for_review'), 'F', 'thank_you_for_review');
    }
    if (!empty($post_data['message'])) {
        $settings = Registry::get('addons.development');
        if (!empty($allow_reward)) {
            if (!empty($post_data['user_id'])) {
                fn_change_user_points($settings['review_reward_' . $object['object_type']], $post_data['user_id'], serialize(array('post_id' => $post_data['post_id'], 'object_id' => $object['object_id'], 'type' => $object['object_type'])), CHANGE_DUE_REVIEW);
                db_query("UPDATE ?:discussion_posts SET is_rewarded = 'Y' WHERE post_id = ?i", $post_data['post_id']);
                if (AREA == 'C') {
                    fn_delete_notification('thank_you_for_review');
                    fn_set_notification('N', __('notice'), __('product_review_added_reward_text', array('[amount]' => $settings['review_reward_' . $object['object_type']])), 'F');
                }
            } elseif (AREA == 'C') {
                db_query("UPDATE ?:discussion_posts SET is_rewarded = 'Y' WHERE post_id = ?i", $post_data['post_id']);
                $_SESSION['post_ids'][$object['object_type']][$post_data['post_id']]['is_rewarded'] = 'Y';
                fn_delete_notification('thank_you_for_review');
                fn_set_notification('N', __('notice'), __('product_review_added_reward_text', array('[amount]' => $settings['review_reward_' . $object['object_type']])), 'F');
            }
        }
        if (!empty($product['promotions'][REVIEW_PROMO_ID]) && defined('AJAX_REQUEST')) {
            Registry::get('view')->display('views/products/view.tpl');
        }
    }
}

function fn_development_tools_change_status($params, $result)
{
    if (!empty($result) && $params['old_status'] != $params['status'] && $params['table'] == 'discussion_posts' && !empty($params['id'])) {
        $post_data = db_get_row("SELECT ?:discussion_posts.user_id, ?:discussion_messages.message, ?:discussion_posts.is_rewarded, ?:discussion.object_type, ?:discussion.object_id, ?:discussion.thread_id FROM ?:discussion_posts INNER JOIN ?:users ON ?:users.user_id = ?:discussion_posts.user_id LEFT JOIN ?:discussion ON ?:discussion.thread_id = ?:discussion_posts.thread_id LEFT JOIN ?:discussion_messages ON ?:discussion_messages.post_id = ?:discussion_posts.post_id WHERE ?:discussion_posts.post_id = ?i", $params['id']);
        if (!empty($post_data) && !empty($post_data['user_id']) && !empty($post_data['object_type'])) {
            $amount = Registry::get('addons.development.review_reward_' . $post_data['object_type']);
            if (!empty($amount)) {
                if ($params['status'] == 'A' && $post_data['is_rewarded'] == 'N') {
                    $allow_reward = fn_allow_user_thread_review_reward($post_data['thread_id'], $post_data['object_type'], $post_data['user_id'], $params['id']);
                    if (!empty($allow_reward)) {
                        fn_change_user_points($amount, $post_data['user_id'], serialize(array('post_id' => $params['id'], 'object_id' => $post_data['object_id'], 'type' => $post_data['object_type'], 'to' => $params['status'], 'from' => $params['old_status'])), CHANGE_DUE_REVIEW);
                        db_query("UPDATE ?:discussion_posts SET is_rewarded = 'Y' WHERE post_id = ?i", $params['id']);
                    }
                } elseif ($params['status'] == 'D' && $post_data['is_rewarded'] == 'Y') {
                    fn_change_user_points(-$amount, $post_data['user_id'], serialize(array('post_id' => $params['id'], 'object_id' => $post_data['object_id'], 'type' => $post_data['object_type'], 'to' => $params['status'], 'from' => $params['old_status'])), CHANGE_DUE_REVIEW);
                    db_query("UPDATE ?:discussion_posts SET is_rewarded = 'N' WHERE post_id = ?i", $params['id']);
                }
            }
        }
    }
}

function fn_development_get_notification_rules(&$force_notification, $params, $disable_notification)
{
    if ($disable_notification) {
        $force_notification['S'] = false;
    } else {
        if (!empty($params['notify_user_sms']) || $params === true) {
            $force_notification['S'] = true;
        } else {
            if (AREA == 'A') {
                $force_notification['S'] = false;
            }
        }
    }
}

function fn_development_order_notification($order_info, $order_statuses, $force_notification)
{
    if (!is_array($force_notification)) {
        $force_notification = fn_get_notification_rules($force_notification, !$force_notification);
    }
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, ($order_info['lang_code'] ? $order_info['lang_code'] : CART_LANGUAGE), $order_info['company_id']);
    $status_params = $order_statuses[$order_info['status']]['params'];

    $notify_user_sms = isset($force_notification['S']) ? $force_notification['S'] : (!empty($status_params['notify_sms']) && $status_params['notify_sms'] == 'Y' ? true : false);

    if ($notify_user_sms == true) {

        $order_status = $order_statuses[$order_info['status']];
        $status_settings = $order_statuses[$order_info['status']]['params'];
        $profile_fields = fn_get_profile_fields('I', '', $order_info['lang_code']);
        $phone = preg_replace('/[^0-9]/', '', $order_info['phone']);
        if (!empty($order_status['sms_text']) && strlen($phone) == 11 && $phone[0] == '7') {
            if (strpos($order_status['sms_text'], '[order_id]')) {
                $order_status['sms_text'] = str_replace('[order_id]', (!empty($order_info['order_number']) ? $order_info['order_number'] : $order_info['order_id']), $order_status['sms_text']);
            }
            if (strpos($order_status['sms_text'], '[amount]')) {
                $order_status['sms_text'] = str_replace('[amount]', $order_info['total'], $order_status['sms_text']);
            }
            if (strpos($order_status['sms_text'], '[delivery_time]')) {
                $delivery_time = '';
                if (!empty($order_info['s_city']) && !empty($order_info['delivery_time'])) {
                    $delivery_time = ' ' . __("destination_delivery_time", array('[city]' => $order_info['s_city'])) . ': ' . $order_info['delivery_time'] . __("days");
                }
                $order_status['sms_text'] = str_replace('[delivery_time]', $delivery_time, $order_status['sms_text']);
            }
            if (strpos($order_status['sms_text'], '[tracking]')) {
                $tracking = '';
                if (!empty($order_info['tracking_number'])) {
                    $tracking .= ' №' . $order_info['tracking_number'];
                }
                $order_status['sms_text'] = str_replace('[tracking]', $tracking, $order_status['sms_text']);
            }
            if (strpos($order_status['sms_text'], '[office_info]')) {
                $office_info = '';
                if (!empty($order_info['shipping'][0]['office_data'])) {
                    $office_info = ' ' . __("destination_point") . ': ' . $order_info['shipping'][0]['office_data']['City'] . ',' . $order_info['shipping'][0]['office_data']['Address'] . ',' . $order_info['shipping'][0]['office_data']['WorkTime'] . ',' . $order_info['shipping'][0]['office_data']['Phone'];
                }
                $order_status['sms_text'] = str_replace('[office_info]', $office_info, $order_status['sms_text']);
            }
            fn_rus_unisender_send_sms($order_status['sms_text'], $phone, $order_info['order_id'], $order_info['status']);
        }
    }
}

function fn_development_get_status_params_definition(&$status_params, $type)
{
    $notify_sms = array (
        'type' => 'checkbox',
        'label' => 'notify_customer_sms',
        'default_value' => 'N'
    );
    $status_params = fn_insert_before_key($status_params, 'notify_department', 'notify_sms', $notify_sms);
}

function fn_development_get_user_info($user_id, $get_profile, $profile_id, &$user_data)
{
    if (AREA == 'C') {
        $user_data['confirmation_sent'] = (fn_ekey_exists($user_id, 'E')) ? 'Y' : 'N';
        $user_data['mail_server'] = fn_get_mail_server($user_data['email']);
    }
}

function fn_development_update_user_pre($user_id, &$user_data, $auth, $ship_to_another, $notify_user, $send_password)
{
    fn_check_state($user_data);

    $names = array(
        'р-н',
        'район',
        'г.',
        'гор.',
        'город',
        'п.г.т.',
        'посёлок городского типа',
        'р.п.',
        'рабочий посёлок',
        'к.п.',
        'курортный посёлок',
        'к.',
        'кишлак',
        'пс',
        'поселковый совет',
//         'сс',
        'сельсовет',
        'смн',
        'сомон',
        'вл.',
        'волость',
        'д.п.',
        'дачный поселковый совет',
        'п.',
        'посёлок сельского типа',
        'н.п.',
        'населённый пункт',
        'п. ст.',
        'посёлок при станции',
        'ж/д ст.',
        'железнодорожная станция',
        'с.',
        'село',
        'м.',
        'местечко',
        'д.',
        'дер.',
        'деревня',
        'сл.',
        'слобода',
        'ст.',
        'станция',
        'ст-ца',
        'станица',
        'х.',
        'хутор',
        'у.',
        'улус',
        'рзд.',
        'разъезд',
        'клх',
        'колхоз',
        'свх',
        'совхоз',
        'зим.',
        'зимовье',
    );
    if (!empty($user_data['s_city'])) {
        $user_data['s_city'] = str_replace($names . ' ', '', $user_data['s_city']);
    }
    if (!empty($user_data['b_city'])) {
        $user_data['b_city'] = str_replace($names . ' ', '', $user_data['b_city']);
    }
}

function fn_development_change_order_status_post($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order)
{
    if (empty($order_info['delivery_date']) && in_array($status_to, ORDER_COMPLETE_STATUSES)) {
        db_query("UPDATE ?:orders SET delivery_date = ?i WHERE order_id = ?i", TIME, $order_info['order_id']);
    }
    $saving_data = db_get_hash_array("SELECT * FROM ?:savings_groups ORDER BY amount ASC", 'group_id');
    if (!empty($saving_data) && !empty($order_info['user_id'])) {
        $orders_total = db_get_field("SELECT SUM(total) FROM ?:orders WHERE user_id = ?i AND status IN (?a)", $order_info['user_id'], ORDER_COMPLETE_STATUSES);
        $usergroup_ids = array();
        foreach ($saving_data as $i => $group_data) {
            $usergroup_ids[] = $group_data['usergroup_id'];
            if ($orders_total > $group_data['amount']) {
                $usergroup_id = $group_data['usergroup_id'];
            }
        }
        if (!empty($usergroup_ids)) {
            foreach ($usergroup_ids as $i => $ug_id) {
                fn_change_usergroup_status('F', $order_info['user_id'], $ug_id);
            }
        }
        if (!empty($usergroup_id)) {
            fn_change_usergroup_status('A', $order_info['user_id'], $usergroup_id);
        }
    }
}

function fn_development_create_order(&$order)
{
    $now = getdate($order['timestamp']);
    $date = fn_date_format($order['timestamp'], '%y%m%d');
    $time_from = mktime(0, 0, 0, $now['mon'], $now['mday'], $now['year']);
    $time_to = mktime(23, 59, 59, $now['mon'], $now['mday'], $now['year']);

    $ord_numbs = db_get_fields("SELECT order_number FROM ?:orders WHERE timestamp >= ?i AND timestamp <= ?i", $time_from, $time_to);

    while (in_array($order['order_number'] = $date . '-' . fn_generate_rand(), $ord_numbs)) {}

    if (!empty($order['delivery_time'])) {
        if (strpos($order['delivery_time'], '-') !== false) {
            $dt = explode('-', $order['delivery_time']);
            $days = array_pop($dt);
        } else {
            $days = $order['delivery_time'];
        }
        $order['est_delivery_date'] = fn_get_working_date($days);
    }
}

function fn_development_pre_place_order($cart, &$allow, $product_groups)
{
    if (!$allow && !empty($cart['is_call_request'])) {
        $allow = true;
    }
}

function fn_development_delete_feature_post($feature_id, $variant_ids)
{
    db_query("UPDATE ?:product_options SET feature_id = '0' WHERE feature_id = ?i", $feature_id);
    db_query("UPDATE ?:product_option_variants SET feature_variant_id = '0' WHERE feature_variant_id IN (?n)", $variant_ids);
}

function fn_development_get_product_feature_data_before_select(&$fields, $join, $condition, $feature_id, $get_variants, $get_variant_images, $lang_code)
{
    $fields[] = '?:product_features.parent_variant_id';
    $fields[] = '?:product_features.note_url';
    $fields[] = '?:product_features.note_text';
    $fields[] = '?:product_features.seo_variants';
}

function fn_development_update_product_feature_pre(&$feature_data, $feature_id, $lang_code)
{
    if (!in_array($feature_data['feature_type'], unserialize(SEO_VARIANTS_ALLOWED))) {
        $feature_data['seo_variants'] = 'N';
    }
}

function fn_development_get_orders($params, &$fields, $sortings, &$condition, &$join, $group)
{
    if (!empty($params['order_number'])) {
        $condition .= db_quote(" AND ?:orders.order_number LIKE ?l", "%" . trim($params['order_number']) . "%");
    }
    if (!empty($params['phone'])) {
        $condition .= db_quote(" AND ?:orders.phone = ?s", trim($params['phone']));
    }
    if (!empty($params['overdue_delivery']) && $params['overdue_delivery'] == 'Y') {
        $condition .= " AND " . fn_get_overdue_delivery_condition('?:orders');
    }
    if (!empty($params['origin']) && !empty(array_diff(array('T', 'M'), $params['origin']))) {
        if (in_array('T', $params['origin'])) {
            $condition .= db_quote(" AND ?:orders.phone != ''");
        } elseif (in_array('M', $params['origin'])) {
            $condition .= db_quote(" AND ?:orders.phone = ''");
        }
    }
    if (AREA == 'A') {
        $fields[] = "?:orders.delivery_date";
        $fields[] = "?:orders.est_delivery_date";
        $fields[] = "?:sms_statuses.sms_status";
        $fields[] = db_quote("IF (" . fn_get_overdue_delivery_condition('?:orders') . ", 1, 0) AS delivery_overdue");
        $join .= " LEFT JOIN ?:sms_statuses ON ?:sms_statuses.order_id = ?:orders.order_id AND ?:sms_statuses.timestamp = (SELECT MAX(?:sms_statuses.timestamp) FROM ?:sms_statuses WHERE ?:sms_statuses.order_id = ?:orders.order_id)";
    }

}

function fn_development_pre_get_orders($params, &$fields, $sortings, $get_totals, $lang_code)
{
    $fields[] = "?:orders.net_total";
    $fields[] = "?:orders.s_country";
    $fields[] = "?:orders.s_city";
    $fields[] = "?:orders.order_number";
}

function fn_development_get_order_info(&$order, $additional_data)
{
//     if (fn_strtolower($order['s_city']) == fn_strtolower(Registry::get('settings.Company.company_city'))) {
//         unset($order['delivery_time']);
//     }
    if ($order['s_country'] != 'RU') {
        $order['s_currency'] = db_get_field("SELECT currency_code FROM ?:countries WHERE code = ?s", $order['s_country']);
    }
    $order['income'] = $order['total'] - $order['net_total'];
    foreach ($order['products'] as $i => $prod) {
        if (!empty($prod['extra']['warehouses'])) {
            $order['products'][$i]['extra']['warehouse_names'] = db_get_hash_single_array("SELECT ?:warehouses.name, ?:product_warehouses_inventory.warehouse_hash FROM ?:warehouses LEFT JOIN ?:product_warehouses_inventory ON ?:product_warehouses_inventory.warehouse_id = ?:warehouses.warehouse_id WHERE ?:product_warehouses_inventory.warehouse_hash IN (?n)", array('warehouse_hash', 'name'), array_keys($prod['extra']['warehouses']));
        }
        if (!empty($prod['extra']['configuration_data'])) {
            foreach ($prod['extra']['configuration_data'] as $ii => $_product) {
                if (!empty($_product['extra']['warehouses']) && is_array($_product['extra']['warehouses'])) {
                    $order['products'][$i]['extra']['configuration_data'][$ii]['extra']['warehouse_names'] = db_get_hash_single_array("SELECT ?:warehouses.name, ?:product_warehouses_inventory.warehouse_hash FROM ?:warehouses LEFT JOIN ?:product_warehouses_inventory ON ?:product_warehouses_inventory.warehouse_id = ?:warehouses.warehouse_id WHERE ?:product_warehouses_inventory.warehouse_hash IN (?n)", array('warehouse_hash', 'name'), array_keys($_product['extra']['warehouses']));
                }
            }
        }
    }
    if (AREA == 'A') {
        $order['sms'] = db_get_hash_array("SELECT * FROM ?:sms_statuses WHERE order_id = ?i", 'sms_id', $order['order_id']);
    }
    $order['order_number'] = !empty($order['order_number']) ? $order['order_number'] : $order['order_id'];
}

function fn_development_gather_additional_product_data_before_options(&$product, $auth, $params)
{
    if (!empty($params['get_discounts']) && (!empty($product['qty_discount_price']) || floatval($product['list_price']))) {
        if (floatval($product['list_price']) && $product['list_price'] > $product['price']) {
            $discount = $product['list_discount'] = $product['list_price'] - $product['price'];
            $product['base_price'] = $product['price'] = $product['list_price'];
        }
        if (!empty($product['qty_discount_price']) && $product['qty_discount_price'] < $product['price'] && (empty($discount) || ($product['price'] - $discount > $product['qty_discount_price']))) {
            $discount = $product['price'] - $product['qty_discount_price'];
        }
        if (!empty($discount)) {
            $product['discount'] = $discount;
            $product['discount_prc'] = sprintf('%d', round($product['discount'] * 100 / $product['price']));
        }
    }
}

function fn_development_get_cart_product_data($product_id, &$_pdata, &$product, $auth, $cart, $hash)
{
    if (!empty($_pdata['qty_discount_price']) || floatval($_pdata['list_price'])) {
        if (floatval($_pdata['list_price']) && $_pdata['list_price'] > $_pdata['price']) {
            $discount = $_pdata['list_discount'] = $_pdata['list_price'] - $_pdata['price'];
            $_pdata['base_price'] = $_pdata['price'] = $_pdata['list_price'];
        }
        if (!empty($_pdata['qty_discount_price']) && $_pdata['qty_discount_price'] < $_pdata['price'] && (empty($discount) || ($_pdata['price'] - $discount > $_pdata['qty_discount_price']))) {
            $discount = $_pdata['price'] - $_pdata['qty_discount_price'];
        }
        if (!empty($discount)) {
            $_pdata['discount'] = $discount;
            $_pdata['discount_prc'] = sprintf('%d', round($_pdata['discount'] * 100 / $_pdata['price']));
        }
    }
}

function fn_development_get_cart_product_data_post($hash, $product, $skip_promotion, &$cart, $auth, $promotion_amount, &$_pdata)
{
    $net_cost_rub = 0;
//     if (!empty($_pdata['configuration'])) {
//         foreach ($_pdata['configuration'] as $i => $pc) {
//             $nc = (!empty($pc['net_cost']) && !empty($pc['net_currency_code'])) ? $pc['net_cost'] * Registry::get('currencies.' . $pc['net_currency_code'] . '.coefficient') : $pc['price'];
//             $net_cost_rub += $nc * $pc['step'];
//         }
//     }
    if (empty($_pdata['net_currency_code'])) {
        $global_data = fn_get_product_global_data($_pdata, array('net_currency_code'));
        $_pdata['net_currency_code'] = !empty($global_data['net_currency_code']) ? $global_data['net_currency_code'] : CART_PRIMARY_CURRENCY;
    }
    $net_cost = (!empty($_pdata['net_cost']) && !empty($_pdata['net_currency_code'])) ? $_pdata['net_cost'] * Registry::get('currencies.' . $_pdata['net_currency_code'] . '.coefficient') : $_pdata['price'];
    $_pdata['net_cost_rub'] = ($net_cost + $net_cost_rub) * $product['amount'];
    $cart['net_subtotal'] += $_pdata['net_cost_rub'];

    if (!empty($_pdata['discount'])) {
        $_pdata['subtotal'] = ($_pdata['price'] - $_pdata['discount']) * $_pdata['amount'];
    }
}

function fn_development_calculate_cart_post(&$cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $cart_products, $product_groups)
{
    if (empty($cart['potential_promotions'][REVIEW_PROMO_ID])) {
        $cart['review_discount'] = fn_get_product_review_discount($cart_products);
    } else {
        unset($cart['review_discount']);
    }
    $cart['net_total'] = $cart['stored_net_total'] != 'Y' ? $cart['net_subtotal'] : $cart['net_total'];
    if (!empty($cart['org_payment_surcharge']) && $cart['stored_net_total'] != 'Y') {
        $cart['net_total'] += $cart['org_payment_surcharge'];
    }
    if (!empty($cart['shipping'])) {
        foreach ($cart['shipping'] as $i => $shp) {
            if ($cart['stored_net_total'] != 'Y') {
                $cart['net_total'] += $shp['original_rate'];
            }
            if (!empty($shp['delivery_time'])) {
                $cart['delivery_time'] = preg_replace('/[^\-0-9]/', '', $shp['delivery_time']);
            }
        }
    }
    $possible_user = array();
    if (empty($auth['user_id']) && !empty($cart['user_data']['email'])) {
        $email_exists = fn_is_user_exists(0, $cart['user_data']);

        if (!empty($email_exists)) {
            $possible_user = array(
                'user_id' => $email_exists
            );
            $user_points = fn_get_user_additional_data(POINTS, $cart['user_data']['possible_user']['user_id']);
            if (!empty($user_points) && $cart['points_info']['max_allowed'] > 0 && $cart['points_info']['total_price'] > 0) {
                $possible_user['allowed_points'] = min($cart['points_info']['max_allowed'], $user_points, $cart['points_info']['total_price']);
            }
        }
    }
    if (!empty($possible_user)) {
        $cart['user_data']['possible_user'] = $possible_user;
    } else {
        unset($cart['user_data']['possible_user']);
    }

}

function fn_development_pre_get_cart_product_data($hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, &$fields, $join)
{
    $fields[] = '?:products.list_price';
    $fields[] = '?:products.net_cost';
    $fields[] = '?:products.net_currency_code';
}

function fn_development_get_page_data(&$page_data, $lang_code)
{
    $page_data['image'] = fn_get_image_pairs($page_data['page_id'], 'page', 'M', false, true, $lang_code);
}

function fn_development_update_page_post($page_data, $page_id, $lang_code, $create, $old_page_data)
{
    fn_attach_image_pairs('page_bg', 'page', $page_id, DESCR_SL);
}

function fn_development_seo_is_indexed_page(&$indexed_pages)
{
//     $indexed_pages['products.view']['noindex'][] = 'ohash';
}

function fn_development_apply_option_modifiers_pre($product_options, $base_value, &$orig_options, $extra, $fields, $type)
{
    if (empty($orig_options) && !empty($extra['product_data']['product_options'])) {
        $orig_options = $extra['product_data']['product_options'];
    }
}

function fn_development_get_filters_products_count_pre(&$params)
{
    $params['get_all'] = true;
    $tc_id = Registry::get('view')->gettemplatevars('active_tab');
    $tab_ids = Registry::get('view')->gettemplatevars('tab_ids');
    if (!empty($tab_ids[$tc_id])) {
        $params['features_hash'] = (!empty($params['features_hash']) ? ($params['features_hash'] . '.') : '') . 'V' . implode('.V', $tab_ids[$tc_id]);
    }
}

function fn_development_get_filters_products_count_before_select($filters, $view_all, &$params)
{
    $tc_id = Registry::get('view')->gettemplatevars('active_tab');
    $tab_ids = Registry::get('view')->gettemplatevars('tab_ids');
    if (!empty($tab_ids[$tc_id])) {
        str_replace('V' . implode('.V', $tab_ids[$tc_id]), '', $params['features_hash']);
    }
}

function fn_development_cron_routine()
{
    db_query("DELETE FROM ?:cron_logs WHERE timestamp < ?i", TIME - SECONDS_IN_DAY * Registry::get('addons.development.cron_logs_delete_time'));

    $scheme = fn_get_schema('cron', 'schema');

    if (!empty($scheme)) {
        foreach ($scheme as $type => $data) {

            if (Settings::instance()->getValue('cron_script_' . $type, 'Cron') != 'Y') {
                continue;
            }
            $run = true;
            if (!empty($data['frequency'])) {
                foreach ($data['frequency'] as $param => $vals) {
                    $vals = explode(',', $vals);
                    $cond = false;
                    foreach ($vals as $val) {
                        if (date($param) == $val) {
                            $cond = true;
                            break;
                        }
                    }
                    if (empty($cond)) {
                        $run = false;
                        break;
                    }
                }
            }
            if (empty($run) || !function_exists($data['function'])) {
                continue;
            }

            $now = getdate(time());
            $time_from = mktime($now['hours'], 0, 0, $now['mon'], $now['mday'], $now['year']);
            $time_to = mktime($now['hours'] + 1, 0, 0, $now['mon'], $now['mday'], $now['year']);

            $is_executed = db_get_field("SELECT log_id FROM ?:cron_logs WHERE type = ?s AND status IN (?a) AND timestamp >= ?i AND timestamp <= ?i", $type, array('F', 'S'), $time_from, $time_to);
            if (empty($is_executed) ) {
                fn_run_cron_script($type, $data);
            }
        }
    }
}

function fn_development_validate_sef_object($path, $seo, $vars, &$result, $objects)
{
    if ($seo['type'] == 'l') {
        if ($path == '/players' && !empty($seo['object_id'])) {
            $result = true;
        } else {
            $result = false;
        }
    }
    if ($seo['type'] == 'm') {
        if ($path == '/promotions' && !empty($seo['object_id'])) {
            $result = true;
        } else {
            $result = false;
        }
    }
}

function fn_development_clone_product($from_product_id, $to_product_id)
{
    $warehouse_inventory = db_get_array("SELECT * FROM ?:product_warehouses_inventory WHERE product_id = ?i AND combination_hash = '0'", $from_product_id);
    if (!empty($warehouse_inventory)) {
        foreach ($warehouse_inventory as $i => $wh_data) {
            $wh_data['product_id'] = $to_product_id;
            $wh_data['amount'] = 0;
            $wh_data['warehouse_hash'] = fn_generate_cart_id($to_product_id, array('warehouse_id' => $wh_data['warehouse_id']));
            db_query("INSERT INTO ?:product_warehouses_inventory ?e", $wh_data);
        }
    }
}

function fn_development_delete_product_option_post($option_id, $pid, $option_deleted)
{
    if ($option_deleted) {
        db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i", $pid);
    }

    fn_update_product_tracking($pid);
}

function fn_development_shippings_get_shippings_list_conditions($group, $shippings, &$fields, $join, &$condition, $order_by)
{
    $fields[] = "?:shippings.website, ?:shippings.payment_ids";
    $condition .= db_quote(' AND ((?:shippings.min_total <= ?d OR ?:shippings.min_total = 0.00)', $group['package_info']['C']);
    $condition .= db_quote(' AND (?:shippings.max_total >= ?d OR ?:shippings.max_total = 0.00))', $group['package_info']['C']);
}

function fn_development_shippings_get_shippings_list_post($group, $lang, $area, &$shippings_info)
{
    $destination_id = fn_get_available_destination($group['package_info']['location']);
    $shipping_ids = db_get_field("SELECT shipping_ids FROM ?:destinations WHERE destination_id = ?i", $destination_id);
    if (!empty($shipping_ids)) {
        $shipping_ids = unserialize($shipping_ids);
    }

    foreach ($shippings_info as $key => $shipping_info) {
        if (!empty($shipping_ids) && !in_array($shipping_info['shipping_id'], $shipping_ids)) {
            unset($shippings_info[$key]);
            continue;
        }
        $shippings_info[$key]['icon'] = fn_get_image_pairs($shipping_info['shipping_id'], 'shipping', 'M', true, true, $lang);
        $_payment_ids = unserialize($shippings_info[$key]['payment_ids']);
        $payment_ids = array();
        if (!empty($_payment_ids)) {
            foreach ($_payment_ids as $p_ids => $p_data) {
                if ($p_data['enabled'] === 'Y') {
                    $payment_ids[] = $p_ids;
                }
            }
        }
        if (!empty($payment_ids)) {
            $payment_categories = db_get_fields("SELECT payment_category FROM ?:payments WHERE payment_id IN (?n)", array_unique($payment_ids));
            if (!empty($payment_categories)) {
                foreach ($payment_categories as $i => $p_type) {
                    $shippings_info[$key]['available_payments'][$p_type] = 'Y';
                }
            }
        }
    }

    foreach ($shippings_info as $i => $shipping) {
        if ($shipping['min_weight'] > 0) {
            $keep = false;
            foreach ($group['products'] as $j => $product) {
                if ($product['weight'] >= $shipping['min_weight']) {
                    $keep = true;
                }
            }
            if (!$keep) {
                unset($shippings_info[$i]);
            }
        }
    }
}

function fn_development_update_payment_surcharge(&$cart, $auth, $lang_code)
{
    $surcharge = $cart['payment_surcharge'];
    $included = true;
    if (!empty($cart['chosen_shipping']) && !empty($cart['payment_id'])) {
        $ids = db_get_hash_single_array("SELECT payment_ids, shipping_id FROM ?:shippings WHERE shipping_id IN (?a)", array('shipping_id', 'payment_ids'), $cart['chosen_shipping']);
        foreach ($cart['chosen_shipping'] as $i => $sh_id) {
            if (!empty($ids[$sh_id])) {
                $payments = unserialize($ids[$sh_id]);
                if (!empty($payments[$cart['payment_id']]) && $payments[$cart['payment_id']]['enabled'] == 'Y') {
                    if ($payments[$cart['payment_id']]['included'] == 'N') {
                        $included = false;
                    }
                    if (floatval($payments[$cart['payment_id']]['a_surcharge'])) {
                        $surcharge += $payments[$cart['payment_id']]['a_surcharge'];
                    }
                    if (floatval($payments[$cart['payment_id']]['p_surcharge']) && !empty($cart['total'])) {
                        $surcharge += fn_format_price($cart['total'] * $payments[$cart['payment_id']]['p_surcharge'] / 100);
                    }
                }
            }
        }
    }
    $cart['org_payment_surcharge'] = $cart['payment_surcharge'] = $surcharge;
    if ($included) {
        $cart['payment_surcharge'] = 0;
    }
}

function fn_development_prepare_checkout_payment_methods($cart, $auth, &$payment_groups)
{
    if (!empty($cart['chosen_shipping'])) {
        $allowed_payments = $available_payments = array();
        $ids = db_get_hash_single_array("SELECT payment_ids, shipping_id FROM ?:shippings WHERE shipping_id IN (?a)", array('shipping_id', 'payment_ids'), $cart['chosen_shipping']);
        foreach ($cart['chosen_shipping'] as $i => $sh_id) {
            $available_payments = array_merge($available_payments, $cart['shipping'][$sh_id]['available_payments']);
            if (!empty($ids[$sh_id])) {
                $payments = unserialize($ids[$sh_id]);
                foreach ($payments as $p_id => $p_data) {
                    if ($p_data['enabled'] == 'Y') {
                        $allowed_payments[$p_id][] = $p_data;
                    }
                }
            }
        }
        if (!empty($allowed_payments) && !empty($payment_groups)) {
            foreach ($payment_groups as $j => $sh) {
                if (!in_array($sh['payment_id'], array_keys($allowed_payments)) || empty($available_payments[$sh['payment_category']]) || $available_payments[$sh['payment_category']] == 'N') {
                    unset($payment_groups[$j]);
                } else {
                    foreach ($allowed_payments[$sh['payment_id']] as $k => $sg) {
                        if (floatval($sg['a_surcharge'])) {
                            $payment_groups[$j]['surcharge_value'] += $sg['a_surcharge'];
                        }
                        if (floatval($sg['p_surcharge']) && !empty($cart['total'])) {
                            $payment_groups[$j]['surcharge_value'] += fn_format_price($cart['total'] * $sg['p_surcharge'] / 100);
                        }
                    }
                }
            }
        }
    }
}

function fn_development_update_shipping(&$shipping_data, $shipping_id, $lang_code)
{
    if (isset($shipping_data['payment_ids'])) {
        $shipping_data['payment_ids'] = !empty($shipping_data['payment_ids']) ? serialize($shipping_data['payment_ids']) : serialize(array());
    }
}

function fn_development_get_product_option_data_post(&$opt, $product_id, $lang_code)
{
    if (!empty($opt['variants'])) {
        $image_pairs = fn_get_image_pairs(array_keys($opt['variants']), 'variant_additional', 'Z', true, true, $lang_code);
        foreach ($opt['variants'] as $i => $variant) {
            if (!empty($image_pairs[$variant['variant_id']])) {
                $opt['variants'][$i]['images'] = $image_pairs[$variant['variant_id']];
            }
        }
    }
}

function fn_development_update_product_option_post($option_data, $option_id, $deleted_variants, $lang_code)
{
    if ($option_data['inventory'] == 'Y' && !empty($option_data['product_id'])) {
        db_query("UPDATE ?:products SET tracking = 'O' WHERE product_id = ?i", $option_data['product_id']);
    }
    if (!empty($option_data['feature_id'])) {
        $feature_data = fn_get_product_feature_data($option_data['feature_id'], true);
    }
    $option_variants = array();

    if (!empty($option_data['variants'])) {
        foreach ($option_data['variants'] as $i => $variant) {
            if (!empty($variant['variant_id'])) {
                $feature_variant_id = 0;
                if (!empty($feature_data)) {
                    if (!empty($feature_data['variants'])) {
                        foreach ($feature_data['variants'] as $j => $f_variant) {
                            if ($variant['variant_name'] == $f_variant['variant']) {
                                $feature_variant_id = $f_variant['variant_id'];
                                break;
                            }
                        }
                    }
                    if (empty($feature_variant_id)) {
                        $f_v_data = array(
                            'variant' => $variant['variant_name']
                        );
                        $feature_variant_id = fn_update_product_feature_variant($feature_data['feature_id'], $feature_data['feature_type'], $f_v_data);
                    }
                }
                db_query('UPDATE ?:product_option_variants SET feature_variant_id = ?i WHERE variant_id = ?i', $feature_variant_id, $variant['variant_id']);

                $product_code = db_get_field("SELECT product_code FROM ?:products WHERE product_id = ?i", $option_data['product_id']);
                $alt_text = fn_get_product_name($option_data['product_id']) . ' ' . $option_data['option_name'] . ' ' . $variant['variant_name'] . (!empty($product_code) ? ' ' . $product_code . $variant['code_suffix'] : '');
                fn_add_img_alt('variant_additional_' . $variant['variant_id'], $alt_text);
                fn_attach_image_pairs('variant_additional_' . $variant['variant_id'], 'variant_additional', $variant['variant_id'], $lang_code);
                fn_add_img_alt('variant_add_additional_' . $variant['variant_id'], $alt_text);
                fn_attach_image_pairs('variant_add_additional_' . $variant['variant_id'], 'variant_additional', $variant['variant_id'], $lang_code);
            }
        }
    }
}

function fn_development_get_product_option_data_pre($option_id, $product_id, &$fields, $condition, $join, &$extra_variant_fields, $lang_code)
{
    $extra_variant_fields .= ' a.code_suffix, a.color_codes,';
    $fields .= ', b.default_text, b.option_note';
}

function fn_development_get_product_features(&$fields, $join, &$condition, $params)
{
    $fields[] = 'pf.note_url';
    $fields[] = 'pf.note_text';
    $fields[] = 'pf.seo_variants';
    $fields[] = 'pf.parent_variant_id';

    if (!empty($params['seo_variants'])) {
        $condition .= " AND pf.seo_variants = 'Y'";
    }
}

function fn_development_get_product_features_list_before_select(&$fields, $join, $condition, $product, $display_on, $lang_code)
{
    $fields .= db_quote(", vd.mens_clothes_size_chart, vd.womens_clothes_size_chart, vd.mens_shoes_size_chart, vd.womens_shoes_size_chart");
}

function fn_development_redirect_complete(&$meta_redirect)
{
    $meta_redirect = false;
}

function fn_development_delete_product_feature_variants($feature_id, $variant_ids)
{
    if (!empty($feature_id)) {
        FeaturesCache::deleteFeature($feature_id);
    } elseif (!empty($variant_ids)) {
        FeaturesCache::deleteVariants($variant_ids);
    }
}

function fn_development_update_product_features_value($product_id, $product_features, $add_new_variant, $lang_code)
{
    $features = db_get_array("SELECT * FROM ?:product_features_values WHERE product_id = ?i", $product_id);
    FeaturesCache::updateProductFeaturesValue($product_id, $features, $lang_code);
}

function fn_development_get_category_data_post($category_id, $field_list, $get_main_pair, $skip_company_condition, $lang_code, &$category_data)
{
    if (!empty($category_data['brand_id'])) {
        list($brands) = fn_get_product_feature_variants(array(
            'feature_id' => BRAND_FEATURE_ID,
            'feature_type' => 'E',
            'variant_ids' => $category_data['brand_id'],
            'get_images' => true
        ));
        $category_data['brand'] = $brands[$category_data['brand_id']];
    }
    if (AREA == 'C')  {
        if (!empty($category_data['id_path']) && (empty($category_data['tabs_categorization']) || empty($category_data['sections_categorization']) || empty($category_data['subtabs_categorization']))) {
            $category_data['cat_ids'] = explode('/', $category_data['id_path']);
            $categorization_data = db_get_hash_array("SELECT category_id, tabs_categorization, subtabs_categorization, sections_categorization FROM ?:categories WHERE category_id IN (?n)", 'category_id', $category_data['cat_ids']);
            fn_format_categorization($category_data, $categorization_data, 'tabs_categorization');
            fn_format_categorization($category_data, $categorization_data, 'subtabs_categorization');
            fn_format_categorization($category_data, $categorization_data, 'sections_categorization');
        }
        $data_array = array('note_url', 'note_text', 'products_sorting', 'canonical', 'default_layout', 'selected_layouts', 'product_columns'/*, 'all_items_tab', 'extended_tabs_categorization'*/, 'products_per_page');
        $data = fn_get_category_global_data($category_data, $data_array);
        foreach ($data_array as $i => $f_name) {
            if (empty($category_data[$f_name]) && !empty($data[$f_name])) {
                $category_data[$f_name] = $data[$f_name];
            }
        }
    }
    if (!empty($category_data['sections_categorization'])) {
        $category_data['sections_categorization'] = unserialize($category_data['sections_categorization']);
    }
    if (!empty($category_data['qty_discounts'])) {
        $category_data['prices'] = unserialize($category_data['qty_discounts']);
    }
    if (!empty($category_data['qty_discounts'])) {
        $category_data['prices'] = unserialize($category_data['qty_discounts']);
    }
    if (!empty($category_data['shipping_params'])) {
        $category_data = array_merge(unserialize($category_data['shipping_params']), $category_data);
    }
    if (!empty($category_data['id_path'])) {
        list($category_data['type_id'], $category_data['type']) = fn_identify_category_type($category_data['id_path']);
    }
}

function fn_development_get_product_feature_variants(&$fields, $join, &$condition, $group_by, $sorting, $lang_code, $limit)
{
    if (!empty($params['variant_ids'])) {
        $params['variant_ids'] = is_array($params['variant_ids']) ? $params['variant_ids'] : array($params['variant_ids']);
        $condition .= db_quote(" AND ?:product_feature_variants.variant_id IN (?n)", $params['variant_ids']);
    }
}

function fn_development_get_categories_post(&$categories_list, $params, $lang_code)
{
    if (AREA == 'C' && empty($params['skip_filter'])) {
        fn_filter_categroies($categories_list);
    }
    if (!empty($params['roundabout']) && !empty($categories_list)) {
        $brand_ids = array();
        foreach ($categories_list as $i => $category) {
            if (!empty($category['brand_id'])) {
                $brand_ids[] = $category['brand_id'];
            }
        }
        if (!empty($brand_ids)) {
            list($brands) = fn_get_product_feature_variants(array(
                'feature_id' => BRAND_FEATURE_ID,
                'feature_type' => 'E',
                'variant_ids' => $brand_ids,
                'get_images' => true
            ));
            foreach ($categories_list as $i => $category) {
                if (!empty($category['brand_id'])) {
                    $categories_list[$i]['brand'] = $brands[$category['brand_id']];
                }
            }
        }
    }
}

function fn_development_get_lang_var_post(&$value, $var_name)
{
    $value = fn_check_vars($value);
}

function fn_development_get_categories(&$params, $join, $condition, &$fields, $group_by, $sortings, $lang_code)
{
    $fields[] = '?:categories.note_url';
    $fields[] = '?:categories.note_text';
    $fields[] = '?:categories.product_count';
    $fields[] = '?:categories.code';
//     $fields[] = '?:categories.is_virtual';
    $fields[] = '?:categories.menu_subitems';
    if (!empty($params['roundabout']) || !empty($params['get_description'])) {
        $fields[] = '?:category_descriptions.description';
        if (!empty($params['roundabout'])) {
            $params['get_images'] = true;
            $fields[] = '?:categories.brand_id';
        }
    }
}

function fn_development_top_menu_form(&$v, $type, $id, $use_name)
{
    if ($type == 'P') {
        $params = array('plain' => true);
        list($players,) = fn_get_players($params);
        if (!empty($players)) {
            $atp = array();
            $wta = array();
            foreach($players as $i => $player) {
                if ($player['gender'] == 'M') {
                    $atp[] = $player;
                } else {
                    $wta[] = $player;
                }
            }
            $v['subitems'] = array(
                array('descr' => __('atp'), 'param' => 'players.list', 'subitems' => fn_top_menu_standardize($atp, 'player_id', 'player', '', 'players.view?player_id=')),
                array('descr' => __('wta'), 'param' => 'players.list', 'subitems' => fn_top_menu_standardize($wta, 'player_id', 'player', '', 'players.view?player_id=')),
            );
        }
    }
}

function fn_development_get_product_options(&$fields, $condition, $join, &$extra_variant_fields, $product_ids, $lang_code)
{
    $fields .= ", b.default_text";
    $extra_variant_fields .= "a.code_suffix, a.color_codes, a.feature_variant_id, ";
}

function fn_development_get_selected_product_options_post(&$_opts, $product_id, $selected_options, $lang_code)
{
    $_opts = fn_sort_array_by_key($_opts, 'show_on_catalog', SORT_DESC);
}

function fn_development_get_product_options_post($product_ids, $lang_code, $only_selectable, $inventory, $only_avail, &$options)
{
    $variant_ids = array();
    foreach ($options as $product_id => $_options) {
        $options[$product_id] = fn_sort_array_by_key($_options, 'show_on_catalog', SORT_DESC);
        foreach ($_options as $option_id => $_option) {
            if (!empty($_option['variants'])) {
                $variant_ids = array_merge($variant_ids, array_keys($_option['variants']));
            }
        }
    }
    if (!empty($variant_ids) && Registry::get('runtime.controller') == 'products') {
        $image_pairs = fn_get_image_pairs($variant_ids, 'variant_additional', 'Z', true, true, $lang_code);
        foreach ($options as $product_id => $_options) {
            foreach ($_options as $option_id => $_option) {
                if (!empty($_option['variants'])) {
                    foreach ($_option['variants'] as $variant_id => $variant) {
                        if (!empty($image_pairs[$variant_id])) {
                            $options[$product_id][$option_id]['variants'][$variant_id]['images'] = $image_pairs[$variant_id];
                            $options[$product_id][$option_id]['has_variant_additional'] = true;
                        }
                    }
                }
            }
        }
    }
}

function fn_development_calculate_cart_items(&$cart, &$cart_products, $auth)
{
    if (!empty($cart_products)) {
        $main_ids = array();
        foreach ($cart_products as $i => $product) {
            $main_ids[] = $product['main_category'];
            $cart['products'][$i]['main_category'] = $product['main_category'];

            $color_ids = $color_prod_image_pairs = array();
            if ($product['tracking'] == 'O' && !empty($product['product_options'])) {
                foreach ($product['product_options'] as $i => $opt_data) {
                    if (!empty($opt_data['show_on_catalog']) && $opt_data['show_on_catalog'] == 'Y' && !empty($opt_data['variants']) && !empty($opt_data['value'])) {
                        $color_ids[] = $opt_data['value'];
                    }
                }
            }
        }
        $id_paths = db_get_hash_single_array("SELECT category_id, id_path FROM ?:categories WHERE category_id IN (?n)", array('category_id', 'id_path'), array_unique($main_ids));
        $cart['product_categories'] = array();
        if (!empty($id_paths)) {
            foreach ($id_paths as $i => $path) {
                $cart['product_categories'] = array_merge($cart['product_categories'], explode('/', $path));
            }
            foreach ($cart_products as $i => $product) {
                $cart_products[$i]['id_path'] = $id_paths[$product['main_category']];
            }
        }
        $cart['product_categories'] = array_unique($cart['product_categories']);

        if (!empty($color_ids)) {
            $color_prod_image_pairs = fn_get_image_pairs($color_ids, 'variant_additional', 'Z', false, true, CART_LANGUAGE);
        }

        if (!empty($color_prod_image_pairs)) {
            foreach ($cart_products as $cart_id => $product) {
                if ($product['tracking'] == 'O' && !empty($product['product_options'])) {
                    foreach ($product['product_options'] as $i => $opt_data) {
                        if (!empty($opt_data['show_on_catalog']) && $opt_data['show_on_catalog'] == 'Y' && !empty($opt_data['variants']) && !empty($opt_data['value'])) {
                            $cart['products'][$cart_id]['ohash'] = 'ohash[' . $opt_data['option_id'] . ']=' . $opt_data['value'];
                            if (!empty($color_prod_image_pairs[$opt_data['value']])) {
                                $cart['products'][$cart_id]['main_pair'] = reset($color_prod_image_pairs[$opt_data['value']]);
                            }
                        }
                    }
                }
            }
        }
    }
}

// ДЛЯ СПИСКА ТОВАРОВ
function fn_development_gather_additional_products_data_post($product_ids, $params, &$products, $auth)
{
    $size_ids = array(
        'R' => array(BABOLAT_GRIP_OPT_ID, BABOLAT_KIDS_GRIP_OPT_ID, WILSON_GRIP_OPT_ID, HEAD_GRIP_OPT_ID),
        'A' => array(APPAREL_SIZE_OPT_ID, APPAREL_KIDS_SIZE_OPT_ID),
        'S' => SHOE_SIZE_OPT_ID
    );
    if (AREA == 'C' && empty($params['get_for_one_product']) && !empty($products)) {
        $color_ids = $color_image_pairs = $color_prod_image_pairs = $features_condition = array();
        if (!empty($params['get_options'])) {
            $warehouse_condition = '';
            if (!empty($_SESSION['wid'])) {
                $warehouse_condition .= db_quote("AND ?:product_warehouses_inventory.warehouse_id = ?i", $_SESSION['wid']);
            }
            $avail_combinations = db_get_hash_multi_array("SELECT ?:product_warehouses_inventory.product_id, ?:product_options_inventory.combination FROM ?:product_warehouses_inventory LEFT JOIN ?:product_options_inventory ON ?:product_warehouses_inventory.combination_hash = ?:product_options_inventory.combination_hash WHERE ?:product_warehouses_inventory.amount > 0 $warehouse_condition AND ?:product_warehouses_inventory.product_id IN (?n)", array('product_id', 'combination'), $product_ids);
        }

        foreach ($products as $i => $product) {
            if (!empty($params['get_options'])) {
                // Для иконок цветов в списке товаров и размеров
                if ($product['tracking'] == 'O' && !empty($product['product_options'])) {
                    if (!empty($params['get_inventory']) && !empty($avail_combinations[$product['product_id']])) {
                        $products[$i]['inventory'] = $avail_combinations[$product['product_id']];
                    }
                    $show_opt_id = $size_id = false;
                    foreach ($product['product_options'] as $ii => $opt_data) {
                        if (!empty($opt_data['parent_option_id']) && $opt_data['parent_option_id'] == GLOBAL_COLOR_OPT_ID && !empty($opt_data['variants'])) {
                            $show_opt_id = $opt_data['option_id'];
                            break;
                        }
                    }
                    if (!empty($product['type']) && !empty($size_ids[$product['type']])) {
                        if (!is_array($size_ids[$product['type']]) && !empty($product['product_options'][$size_ids[$product['type']]])) {
                            $size_id = $size_ids[$product['type']];
                        } elseif (is_array($size_ids[$product['type']])) {
                            foreach ($size_ids[$product['type']] as $k => $opt_id) {
                                if (!empty($product['product_options'][$opt_id])) {
                                    $size_id = $opt_id;
                                    break;
                                }
                            }
                        }
                    }
                    if ((!empty($show_opt_id) || !empty($size_id)) && !empty($avail_combinations[$product['product_id']])) {
                        if (!empty($size_id)) {
                            $products[$i]['sizes']['feature_id'] = $product['product_options'][$size_id]['feature_id'];
                            $products[$i]['sizes']['name'] = __('available_option_' . $size_id);
                        }
                        foreach (array_keys($avail_combinations[$product['product_id']]) as $ii => $combination) {
                            $options = fn_get_product_options_by_combination($combination);
                            if (!empty($show_opt_id) && !empty($options[$show_opt_id])) {
                                $color_ids[] = $options[$show_opt_id];
                            }
                            if (!empty($size_id) && !empty($options[$size_id]) && !empty($product['product_options'][$size_id]['variants'][$options[$size_id]])) {
                                $products[$i]['sizes']['variants'][$product['product_options'][$size_id]['variants'][$options[$size_id]]['variant_id']] = $product['product_options'][$size_id]['variants'][$options[$size_id]]['variant_name'];
                                $products[$i]['sizes']['feature_variants'][$product['product_options'][$size_id]['variants'][$options[$size_id]]['variant_id']] = $product['product_options'][$size_id]['variants'][$options[$size_id]]['feature_variant_id'];
                                if (!empty($show_opt_id) && !empty($options[$show_opt_id]) && !empty($product['product_options'][$show_opt_id]['variants'][$options[$show_opt_id]])) {
                                    $products[$i]['sizes']['color_variants'][$product['product_options'][$show_opt_id]['variants'][$options[$show_opt_id]]['variant_id']][$options[$size_id]] = 1;
                                }
                            }
                        }
                        if (!empty($products[$i]['sizes']['variants'])) {
                            ksort($products[$i]['sizes']['variants']);
                        }
                    }
                }
            }
            if (!empty($params['get_title_features'])) {
                // Условие для хак-к
                $find_set = array(
                    " f.categories_path = '' "
                );
                $path = explode('/', $product['id_path']);
                foreach ($path as $k => $v) {
                    $find_set[] = db_quote(" FIND_IN_SET(?i, f.categories_path) ", $v);
                }
                $find_in_set = db_quote(" AND (?p)", implode('OR', $find_set));
                $features_condition[] = db_quote(" (v.product_id = ?i ?p) ", $product['product_id'], $find_in_set);
            }
        }

        if (!empty($params['get_title_features'])) {
            $condition = db_quote("f.display_on_catalog = 'Y' AND f.status = 'A' AND IF(f.parent_id, (SELECT status FROM ?:product_features as df WHERE df.feature_id = f.parent_id), 'A') = 'A' ?p AND (v.variant_id != 0 OR (f.feature_type != 'C' AND v.value != '') OR (f.feature_type = 'C') OR v.value_int != '') AND v.lang_code = ?s", db_quote(" AND (?p)", implode('OR', $features_condition)), CART_LANGUAGE);
            $fields = db_quote("f.feature_type, fd.description, v.variant_id, v.value, v.feature_id, GROUP_CONCAT(vd.variant SEPARATOR ',') as variants, v.product_id, gf.position as gposition");
            $join = db_quote(
                " LEFT JOIN ?:product_features_descriptions as fd ON fd.feature_id = f.feature_id AND fd.lang_code = ?s"
                . " LEFT JOIN ?:product_features_values as v ON v.feature_id = f.feature_id"
                . " LEFT JOIN ?:product_feature_variant_descriptions as vd ON vd.variant_id = v.variant_id AND vd.lang_code = ?s"
                . " LEFT JOIN ?:product_features as gf ON gf.feature_id = f.parent_id AND gf.feature_type = ?s ",
                CART_LANGUAGE, CART_LANGUAGE, 'G');
            $products_features = db_get_hash_multi_array("SELECT $fields FROM ?:product_features as f $join WHERE $condition GROUP BY v.product_id, v.feature_id ORDER BY f.position", array('product_id', 'feature_id'));
            $brands = fn_get_product_feature_data(BRAND_FEATURE_ID, true, true);
        }

        if (!empty($params['get_options']) && !empty($color_ids)) {
            $color_image_pairs_add = fn_get_image_pairs($color_ids, 'variant_additional', 'Z', false, true, CART_LANGUAGE, true);
            if (Registry::get('settings.Appearance.catalog_options_mode') != 'Y' || empty($params['allow_duplication'])) {
                $color_image_pairs = fn_get_image_pairs($color_ids, 'variant_image', 'V', true, false, CART_LANGUAGE);
            }
        }

        foreach ($products as $i => &$product) {
            if (!empty($params['get_options']) && !(Registry::get('settings.Appearance.catalog_options_mode') == 'Y' && !empty($params['allow_duplication']))) {
                // Для иконок цветов в списке товаров
                if ($product['tracking'] == 'O' && !empty($product['product_options'])) {
                    foreach ($product['product_options'] as $j => $opt_data) {
                        if (!empty($opt_data['parent_option_id']) && $opt_data['parent_option_id'] == GLOBAL_COLOR_OPT_ID && !empty($opt_data['variants'])) {
                            foreach ($opt_data['variants'] as $k => $v_data) {
                                if (!empty($color_image_pairs_add[$v_data['variant_id']])) {
                                    $product['full_option_images'] = true;
                                    $product['option_images'][$v_data['variant_id']] = reset($color_image_pairs_add[$v_data['variant_id']]);
                                } else if (!empty($color_image_pairs[$v_data['variant_id']])) {
                                    $product['option_images'][$v_data['variant_id']] = reset($color_image_pairs[$v_data['variant_id']]);
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($product['selected_options']) && $product['tracking'] == 'O' && !empty($product['product_options'])) {
                foreach ($product['product_options'] as $i => $opt_data) {
                    if (!empty($product['selected_options'][$opt_data['option_id']]) && !empty($opt_data['parent_option_id']) && $opt_data['parent_option_id'] == GLOBAL_COLOR_OPT_ID) {
                        $product['ohash'] = 'ohash[' . $opt_data['option_id'] . ']=' . $product['selected_options'][$opt_data['option_id']];
                        if(!empty($color_image_pairs_add[$product['selected_options'][$opt_data['option_id']]])) {
                            $product['main_pair'] = reset($color_image_pairs_add[$product['selected_options'][$opt_data['option_id']]]);
                        }
                    }
                }
            }

            if (!empty($params['get_title_features'])) {
                if (!empty($products_features[$product['product_id']])) {
                    $series_feature = fn_get_subtitle_feature($products_features[$product['product_id']], $product['type']);
                    $variants = array();
                    if (!empty($series_feature['variants'])) {
                        $variants = explode(',', $series_feature['variants']);
                    }
                    $brand = $products_features[$product['product_id']][BRAND_FEATURE_ID]['variants'];
                    $product['brand'] = $brands['variants'][$products_features[$product['product_id']][BRAND_FEATURE_ID]['variant_id']];
                    if ($product['type'] == 'R') {
                        $product['subtitle'] = /*__("type") .  ' - ' .  */$products_features[$product['product_id']][TYPE_FEATURE_ID]['variants'];
                        $product['description_features'] = array();
                        $exceptions = unserialize(EXC_PRODUCT_ITEMS);
                        if (!in_array($product['product_id'], $exceptions)) {
                            foreach ($products_features[$product['product_id']] as $f_id => $ft) {
                                if (in_array($f_id, array(R_HEADSIZE_FEATURE_ID, R_WEIGHT_FEATURE_ID, TYPE_FEATURE_ID))) {
                                    $product['description_features'][] = $ft;
                                }
                            }
                        }
//                         if (!empty($variants)) {
//                             $product['subtitle'] = __("series") .  ' - ' .  reset($variants);
//                         } else {
//                             $product['subtitle'] = __("type") .  ' - ' .  $products_features[$product['product_id']][TYPE_FEATURE_ID]['variants'];
//                         }
                    } elseif ($product['type'] == 'A') {
//                         $product['subtitle'] = reset($variants) .  ' - ' .  $brand;
                    } elseif ($product['type'] == 'S') {
                        $product['subtitle'] = __("surface") .  ' - ' .  reset($variants);
                    } elseif ($product['type'] == 'B' && !empty($products_features[$product['product_id']][BAG_SIZE_FEATURE_ID]['variants'])) {
                        $product['subtitle'] = __("size") .  ' - ' .  $products_features[$product['product_id']][BAG_SIZE_FEATURE_ID]['variants'];
                    } elseif ($product['type'] == 'ST') {
//                         if (!empty($variants) && count($variants) > 1 && $series_feature['feature_type'] == 'M') {
//                             $product['subtitle'] = __("hybrid");
//                         } else {
                            $product['subtitle'] = __("structure") .  ' - ' .  reset($variants);
//                         }
                    } elseif ($product['type'] == 'BL') {
                        $product['subtitle'] = __("type") .  ' - ' .  reset($variants);
                        $product['description_features'] = array();
                        foreach ($products_features[$product['product_id']] as $f_id => $ft) {
                            if (in_array($f_id, array(BALLS_TYPE_FEATURE_ID))) {
                                $product['description_features'][] = $ft;
                            }
                        }
                    } elseif ($product['type'] == 'OG') {
                        $product['subtitle'] = __("type") .  ' - ' .  reset($variants);
                    } elseif ($product['type'] == 'BG') {
                        $product['subtitle'] = __("material") .  ' - ' .  reset($variants);
                    } elseif ($product['type'] == 'TM') {
                        $product['subtitle'] = __("type") .  ' - ' .  reset($variants);
                    }
                    $product['free_strings'] = fn_is_free_strings($product, $products_features[$product['product_id']]);
                }
            }
        }

        if (!empty($params['get_options']) && Registry::get('settings.Appearance.catalog_options_mode') == 'Y' && !empty($params['allow_duplication']) && !empty($color_ids)) {
            $color_prod_image_pairs_add = fn_get_image_pairs($color_ids, 'variant_additional', 'Z', false, $params['display_variant_additional_pairs'] ?? false, CART_LANGUAGE);
            $new_products = array();
            foreach ($products as $i => &$product) {
                $found = false;
                if ($product['tracking'] == 'O' && !empty($product['product_options'])) {
                    foreach ($product['product_options'] as $j => $opt_data) {
                        if (!empty($opt_data['parent_option_id']) && $opt_data['parent_option_id'] == GLOBAL_COLOR_OPT_ID && !empty($opt_data['variants'])) {
                            $iteration = 0;
                            $found = true;
                            foreach ($opt_data['variants'] as $k => $v_data) {
                                if (empty($params['av_ids'][$opt_data['feature_id']]) || (!empty($params['av_ids'][$opt_data['feature_id']]) && !empty($params['av_ids'][$opt_data['feature_id']][$v_data['feature_variant_id']]))) {
                                    $image_pair = !empty($color_image_pairs_add[$v_data['variant_id']]) ? reset($color_image_pairs_add[$v_data['variant_id']]) : array();
                                    $new_product = array();
                                    if (!empty($image_pair) && $image_pair['pair_id'] != $product['main_pair']['pair_id']) {
                                        $new_product = $product;
                                        $new_product['main_pair'] = reset($color_image_pairs_add[$v_data['variant_id']]);
                                        if (!empty($color_prod_image_pairs_add[$v_data['variant_id']])) {
                                            array_shift($color_prod_image_pairs_add[$v_data['variant_id']]);
                                            $new_product['image_pairs'] = $color_prod_image_pairs_add[$v_data['variant_id']];
                                        }
                                    } elseif (empty($image_pair)) {
                                        $new_product = $product;
                                    }
                                    if (!empty($new_product)) {
                                        $new_product['duplicated_data'] = array(
                                            'option_id' => $opt_data['option_id'],
                                            'variant_id'=> $v_data['variant_id']
                                        );
                                        if (!empty($new_product['sizes']) && !empty($new_product['sizes']['variants']) && !empty($new_product['sizes']['color_variants'][$v_data['variant_id']])) {
                                            $filter_passed = empty($params['av_ids'][$new_product['sizes']['feature_id']]) ? true : false;
                                            foreach ($new_product['sizes']['variants'] as $v_id => $v_name) {
                                                if (empty($new_product['sizes']['color_variants'][$v_data['variant_id']][$v_id])) {
                                                    unset($new_product['sizes']['variants'][$v_id]);
                                                } elseif (!$filter_passed && !empty($params['av_ids'][$new_product['sizes']['feature_id']][$new_product['sizes']['feature_variants'][$v_id]])) {
                                                    $filter_passed = true;
                                                }
                                            }
                                            if (!$filter_passed) {
                                                continue;
                                            }
                                        }
                                        if ($iteration > 0) {
                                            $new_product['ohash'] = 'ohash[' . $opt_data['option_id'] . ']=' . $v_data['variant_id'];
                                            $new_product['selected_options'][$opt_data['option_id']] = $v_data['variant_id'];
                                            $new_product['obj_prefix'] = $v_data['variant_id'];
                                        }
                                        if ($new_product['type'] == 'A' && !empty($v_data['variant_name'])) {
                                            $new_product['product'] .= ' ' . $v_data['variant_name'];
                                        }
                                        $new_products[] = $new_product;
                                    }
                                    $iteration++;
                                }
                            }
                        }
                    }
                }
                if (!$found)  {
                    $new_products[] = $product;
                }
            }
            $products = $new_products;
        }
    }
    if (empty($params['get_for_one_product'])) {
        foreach ($products as $k => $_product) {
            if (isset($_product['virtual_parent_id']) && !empty($_product['original_id'])) {
                $products[$_product['virtual_parent_id']]['configuration'][$_product['original_id']] = $_product;
                unset($products[$k]);
            }
        }
    }
}

// ДЛЯ ОДНОГО ТОВАРА
function fn_development_gather_additional_product_data_post(&$product, $auth, $params)
{
    if (AREA == 'C' && !empty($params['get_for_one_product'])) {
        $new_period = Registry::get('addons.development.new');
        $now = getdate(TIME);
        $time_limit = mktime($now['hours'], $now['minutes'], $now['seconds'], $now['mon'] - $new_period, $now['mday'], $now['year']);
        if (!empty($product['timestamp']) && $product['timestamp'] > $time_limit) {
            $product['tags']['new'] = 1;
        }
        if (!empty($params['get_options'])) {
            if (!empty($product['category_main_id'])) {
                $product['category_main_title'] = db_get_field("SELECT category FROM ?:category_descriptions WHERE category_id = ?i AND lang_code = ?s", $product['category_main_id'], CART_LANGUAGE);
            }
            if ($product['tracking'] == 'O' && !empty($product['combination_hash'])) {
                $combination = $product['combination_hash'];
            } elseif ($product['tracking'] == 'B') {
                $combination = 0;
            }
            if (isset($combination)) {
                if ($auth['user_id'] == 0 && !empty($_SESSION['product_notifications']['email'])) {
                    $subscription_id = db_get_field("SELECT subscription_id FROM ?:product_subscriptions WHERE product_id = ?i AND combination_hash = ?i AND email = ?s", $product['product_id'], $combination, $_SESSION['product_notifications']['email']);
                    if (!empty($subscription_id)) {
                        $product['inventory_notification'] = 'Y';
                        $product['inventory_notification_email'] = $_SESSION['product_notifications']['email'];
                    }
                } elseif (!empty($auth['user_id'])) {
                    $email = db_get_field("SELECT email FROM ?:product_subscriptions WHERE product_id = ?i AND combination_hash = ?i AND user_id = ?i", $product['product_id'], $combination, $auth['user_id']);
                    if (!empty($email)) {
                        $product['inventory_notification'] = 'Y';
                        $product['inventory_notification_email'] = $email;
                    }
                }
            }

            if (($params['get_icon'] == true || $params['get_detailed'] == true) && !empty($product['selected_options'])) {
                foreach ($product['selected_options'] as $option_id => $variant_id) {
                    if (!empty($product['product_options'][$option_id]['variants'][$variant_id]['images'])) {
                        $tmp = $product['product_options'][$option_id]['variants'][$variant_id]['images'];
                        $product['org_main_pair'] = $product['main_pair'];
                        $product['main_pair'] = reset($tmp);
                        unset($tmp[key($tmp)]);
                        $product['image_pairs'] = $tmp;
                        break;
                    }
                }
            }
        }
        $global_data = fn_get_product_global_data($product, array('product_pretitle'));
        $exceptions = unserialize(EXC_PRODUCT_ITEMS);
        if (!empty($global_data['product_pretitle']) && !in_array($product['product_id'], $exceptions)/*Теннисная ракетка браслет*/) {
            $product['product'] = $global_data['product_pretitle'] . ' ' . $product['product'];
        }
        if (!empty($product['variation_ids'])) {
            $params = array(
                'item_ids' => $product['variation_ids']
            );
            list($products,) = fn_get_products($params);
            $products_images = fn_get_image_pairs(explode(',', $product['variation_ids']), 'product', 'M', false, true, CART_LANGUAGE);
            if (!empty($products_images)) {
                foreach ($products as $key => $prod) {
                    if (!empty($products_images[$prod['product_id']])) {
                        $products[$key]['main_pair'] = reset($products_images[$prod['product_id']]);
                    }
                }
            }
            $product['variations'] = $products;
        }
        if (!empty($product['combination_hash'])) {
            $product['product_hash'] = $product['combination_hash'];
        } else {
            $product['product_hash'] = fn_generate_cart_id($product['product_id'], array('product_options' => $product['product_options']), true);
        }
        if (!empty($_SESSION['cart']['products']) && in_array($product['product_hash'], array_keys($_SESSION['cart']['products']))) {
            $product['in_cart'] = true;
        } else {
            $product['in_cart'] = false;
        }

        if (!empty($product['product_features'])) {
            $gender = '';
            if (!empty($product['product_features'][CLOTHES_GENDER_FEATURE_ID])) {
                $variant_id = $product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variant_id'];
                if ($variant_id == C_GENDER_M_FV_ID) {
                    $gender = 'mens';
                } elseif ($variant_id == C_GENDER_W_FV_ID) {
                    $gender = 'womens';
                }
                $product['size_chart'] = $product['product_features'][CLOTHES_GENDER_FEATURE_ID]['variants'][$variant_id]['size_chart'];
            } elseif (!empty($product['product_features'][SHOES_GENDER_FEATURE_ID])) {
                $variant_id = $product['product_features'][SHOES_GENDER_FEATURE_ID]['variant_id'];
                if ($variant_id == S_GENDER_M_FV_ID) {
                    $gender = 'mens';
                } elseif ($variant_id == S_GENDER_W_FV_ID) {
                    $gender = 'womens';
                }
                $product['size_chart'] = $product['product_features'][SHOES_GENDER_FEATURE_ID]['variants'][$variant_id]['size_chart'];
            }
            if (!empty($product['header_features'][BRAND_FEATURE_ID])) {
                $cat_type = '';
                $brand_id = $product['header_features'][BRAND_FEATURE_ID]['variant_id'];
                if ($product['category_type'] == 'A') {
                    $cat_type = 'clothes';
                } elseif ($product['category_type'] == 'S') {
                    $cat_type = 'shoes';
                }
                if (!empty($product['header_features'][BRAND_FEATURE_ID]['variants'][$brand_id][$gender . '_' . $cat_type . '_size_chart'])) {
                    $product['size_chart'] = $product['header_features'][BRAND_FEATURE_ID]['variants'][$brand_id][$gender . '_' . $cat_type . '_size_chart'];
                }
            }
        }
        if (!empty($product['size_chart']) && !empty($product['product_options'])) {
            if (!empty($product['product_options'][SHOE_SIZE_OPT_ID])) {
                $product['product_options'][SHOE_SIZE_OPT_ID]['popup_content'] = $product['size_chart'];
                $product['product_options'][SHOE_SIZE_OPT_ID]['popup_title'] = __('sizing_table');
            } elseif (!empty($product['product_options'][APPAREL_SIZE_OPT_ID])) {
                $product['product_options'][APPAREL_SIZE_OPT_ID]['popup_content'] = $product['size_chart'];
                $product['product_options'][APPAREL_SIZE_OPT_ID]['popup_title'] = __('sizing_table');
            } elseif (!empty($product['product_options'][APPAREL_KIDS_SIZE_OPT_ID])) {
                $product['product_options'][APPAREL_KIDS_SIZE_OPT_ID]['popup_content'] = $product['size_chart'];
                $product['product_options'][APPAREL_KIDS_SIZE_OPT_ID]['popup_title'] = __('sizing_table');
            }
        }
        $product['free_strings'] = fn_is_free_strings($product, $product['product_features']);
    }
    if (AREA == 'C') {
        $product['is_liked'] = fn_check_wishlist($product['product_id']);
    }
}

function fn_development_get_product_features_list_post(&$features_list, $product, $display_on, $lang_code)
{
    if (!empty($features_list[BRAND_FEATURE_ID]['variants'])) {
        $image_pairs = fn_get_image_pairs(array_keys($features_list[BRAND_FEATURE_ID]['variants']), 'feature_variant', 'V', true, true, CART_LANGUAGE);
        foreach ($features_list[BRAND_FEATURE_ID]['variants'] as $variant_id => $variant) {
            $features_list[BRAND_FEATURE_ID]['variants'][$variant_id]['image_pair'] = array_pop($image_pairs[$variant_id]);
        }
    }
}

function fn_development_update_product_filter(&$filter_data, $filter_id, $lang_code)
{
    if (!empty($filter_data['feature_type']) && $filter_data['feature_type'] == 'N') {
        if (!empty($filter_data['is_slider']) && $filter_data['is_slider'] != 'Y') {
            db_query("UPDATE ?:product_filters SET field_type = '' WHERE filter_id = ?i", $filter_id);
        } else {
            $field_type = db_get_field("SELECT field_type FROM ?:product_filters WHERE filter_id = ?i", $filter_id);
            if (empty($field_type)) {
                $existing_codes = db_get_fields('SELECT field_type FROM ?:product_filters GROUP BY field_type');
                $existing_codes[] = 'P';
                $existing_codes[] = 'A';
                $existing_codes[] = 'F';
                $existing_codes[] = 'V';
                $existing_codes[] = 'R';
                $existing_codes[] = 'B';
                $codes = array_diff(range('A', 'Z'), $existing_codes);
                $field_type = reset($codes);
                db_query("UPDATE ?:product_filters SET field_type = ?s WHERE filter_id = ?i", $field_type, $filter_id);
            }
        }
    }
}

function fn_development_add_range_to_url_hash_pre(&$hash, $range, $field_type)
{
    $fields = fn_get_product_filter_fields();
    foreach ($fields as $i => $fld) {
        if ($field_type == $i && !empty($fld['slider']) && !in_array($i, array('P', 'A'))) {
            $pattern = '/(' . $i . '\d+-\d+\.?)|(\.?' . $i . '\d+-\d+)/';
            $hash = preg_replace($pattern, '', $hash);
        }
    }

}

function fn_development_get_product_filter_fields(&$filters)
{
    static $fields;

    if (!isset($fields)) {
        $fields = db_get_array("SELECT ?:product_filters.field_type, ?:product_filter_descriptions.filter, ?:product_filters.feature_id FROM ?:product_filters LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_filters.feature_id LEFT JOIN ?:product_filter_descriptions ON ?:product_filter_descriptions.filter_id = ?:product_filters.filter_id AND ?:product_filter_descriptions.lang_code = ?s WHERE ?:product_filters.is_slider = 'Y' AND ?:product_features.feature_type = 'N' AND ?:product_filters.field_type != ''", CART_LANGUAGE);
    }

    if (!empty($fields)) {
        foreach ($fields as $i => $field) {
            $filters[$field['field_type']] = array(
                'description' => $field['filter'],
                'feature_id' => $field['feature_id'],
                'condition_type' => 'S',
                'slider' => true,
            );
        }
    }
}

function fn_development_get_filter_range_name_post(&$range_name, $range_type, $range_id)
{
    if (!in_array($range_type, array('P', 'A', 'V', 'R'))) {
        $from = fn_strtolower(__('range_from'));
        $to = fn_strtolower(__('range_to'));
        $fields = fn_get_product_filter_fields();
        $data = explode('-', $range_id);
        $from_val = !empty($data[0]) ? $data[0] : 0;
        $to_val = !empty($data[1]) ? $data[1] : 0;
        $range_name = $fields[$range_type]['description'] . " : $from $from_val $to $to_val";
    }
}

function fn_development_get_filters_products_count_before_select_filters(&$sf_fields, &$sf_join, &$condition, $sf_sorting, $params, $av_ids)
{
    $sf_fields .= db_quote(", ?:product_filters.is_slider, ?:product_filters.units, ?:product_filters.note_url, ?:product_filters.note_text, ?:product_features.seo_variants");
    $sf_join .= db_quote("LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_filters.feature_id");
    $condition .= db_quote(" AND (?:product_features.parent_variant_id = '0' OR ?:product_features.parent_variant_id IS NULL OR ?:product_features.parent_variant_id IN (?n))", $av_ids);
}

function fn_development_update_category_pre(&$category_data, $category_id, $lang_code)
{
    if (!empty($category_data['sections_categorization'])) {
        $category_data['sections_categorization'] = serialize($category_data['sections_categorization']);
    } else {
        $category_data['sections_categorization'] = '';
    }
    if (!empty($category_data['prices'])) {
        foreach ($category_data['prices'] as $i => $v) {
            if (empty($v['lower_limit']) || $v['lower_limit'] == 1) {
                unset($category_data['prices'][$i]);
                continue;
            }
        }
        if (!empty($category_data['apply_qty_discounts']) && $category_data['apply_qty_discounts'] == 'Y') {
            $products = array();
            $_params = array (
                'cid' => $category_id,
                'subcats' => 'Y',
                'product_type' => 'P'
            );
            list($prods,) = fn_get_products($_params);
            if (!empty($prods)) {
                foreach ($prods as $i => $prod) {
                    if ($prod['price'] > 0) {
                        $products[] = $prod['product_id'];
                    }
                }
            }
            if (!empty($products)) {

                db_query("DELETE FROM ?:product_prices WHERE product_id IN (?n) AND lower_limit > 1", $products);

                if (!empty($category_data['prices'])) {
                    $result = array();
                    foreach ($prods as $i => $prod) {
                        if ($prod['price'] > 0) {
                            foreach ($category_data['prices'] as $v) {
                                $v['type'] = !empty($v['type']) ? $v['type'] : 'A';
                                $v['usergroup_id'] = !empty($v['usergroup_id']) ? $v['usergroup_id'] : 0;
                                $v['product_id'] = $prod['product_id'];
                                $v['percentage_discount'] = ($v['price'] > 100) ? 100 : $v['price'];
                                $v['price'] = $prod['price'];
                                unset($v['type']);

                                $result[] = $v;
                            }
                        }
                    }
                    db_query("REPLACE INTO ?:product_prices ?m", $result);
                }
            }
        }
        $category_data['qty_discounts'] = serialize($category_data['prices']);
    } else {
        $category_data['qty_discounts'] = '';
    }
    if (!empty($category_data['cross_categories'])) {
        $category_data['cross_categories'] = serialize($category_data['cross_categories']);
    }

    $shipping_params = array();
    if (!empty($category_id)) {
        $shipping_params = db_get_field('SELECT shipping_params FROM ?:categories WHERE category_id = ?i', $category_id);
        if (!empty($shipping_params)) {
            $shipping_params = unserialize($shipping_params);
        }
    }

    // Save the product shipping params
    $_shipping_params = array(
        'min_items_in_box' => isset($category_data['min_items_in_box']) ? intval($category_data['min_items_in_box']) : (!empty($shipping_params['min_items_in_box']) ? $shipping_params['min_items_in_box'] : 0),
        'max_items_in_box' => isset($category_data['max_items_in_box']) ? intval($category_data['max_items_in_box']) : (!empty($shipping_params['max_items_in_box']) ? $shipping_params['max_items_in_box'] : 0),
        'box_length' => isset($category_data['box_length']) ? intval($category_data['box_length']) : (!empty($shipping_params['box_length']) ? $shipping_params['box_length'] : 0),
        'box_width' => isset($category_data['box_width']) ? intval($category_data['box_width']) : (!empty($shipping_params['box_width']) ? $shipping_params['box_width'] : 0),
        'box_height' => isset($category_data['box_height']) ? intval($category_data['box_height']) : (!empty($shipping_params['box_height']) ? $shipping_params['box_height'] : 0),
    );

    $category_data['shipping_params'] = serialize($_shipping_params);
}

function fn_development_get_products(&$params, &$fields, &$sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having)
{
    if (!empty($params['price_mode'])) {
        $condition .= db_quote(" AND products.price_mode = ?s", $params['price_mode']);
    }
    if (!empty($params['approval_status'])) {
        $condition .= db_quote(" AND products.approval_status = ?s", $params['approval_status']);
    }
    if (!empty($params['product_type'])) {
        $condition .= db_quote(" AND products.product_type = ?s", $params['product_type']);
    }
    if (!empty($params['not_product_codes'])) {
        $condition .= db_quote(" AND products.product_code NOT IN (?a)", $params['not_product_codes']);
    }
    if (!empty($params['has_description'])) {
        $condition .= db_quote(" AND IF(?s = 'Y', descr1.full_description != '', descr1.full_description = '')", $params['has_description']);
    }
    if (!empty($params['has_sdescription'])) {
        $condition .= db_quote(" AND IF(?s = 'Y', descr1.short_description != '', descr1.short_description = '')", $params['has_sdescription']);
    }
    if (!empty($params['warehouse_id'])) {
        $condition .= db_quote(" AND (products.warehouse_ids LIKE ?l OR products.warehouse_ids LIKE ?l OR products.warehouse_ids LIKE ?l OR products.warehouse_ids LIKE ?l)", $params['warehouse_id'], $params['warehouse_id'] . ',%', '%,' . $params['warehouse_id'], '%,' . $params['warehouse_id'] . ',%');
    }
    if (!empty($params['tabs_categorization']) || !empty($params['subtabs_categorization']) || !empty($params['sections_categorization'])) {
        $params['post_items_per_page'] = $params['items_per_page'];
        $params['items_per_page'] = 0;
        if (!empty($params['tabs_categorization'])) {
            $join .= db_quote(" LEFT JOIN ?:product_features_values AS tabs_categorization ON tabs_categorization.product_id = products.product_id AND tabs_categorization.feature_id = ?i", $params['tabs_categorization']);
            $fields[] = 'tabs_categorization.variant_id AS tabs_categorization';
        }
        if (!empty($params['subtabs_categorization'])) {
            $join .= db_quote(" LEFT JOIN ?:product_features_values AS subtabs_categorization ON subtabs_categorization.product_id = products.product_id AND subtabs_categorization.feature_id = ?i", $params['subtabs_categorization']);
            $fields[] = 'subtabs_categorization.variant_id AS subtabs_categorization';
        }
        if (!empty($params['sections_categorization'])) {
            $join .= db_quote(" LEFT JOIN ?:product_features_values AS sections_categorization ON sections_categorization.product_id = products.product_id AND sections_categorization.feature_id IN (?n)", $params['sections_categorization']);
            $fields[] = "GROUP_CONCAT(',', sections_categorization.variant_id) AS sections_categorization";
        }
    }
    if (!empty($params['similar_pid'])) {
        $similar_products_features = array(
            'R' => array(
                array('34' => 'self', '33' => '939'), // komplekt
                array('33' => '282', R_LENGTH_FEATURE_ID => 'self'), // kids rackets
                array(R_BALANCE_FEATURE_ID => 'self', R_LENGTH_FEATURE_ID => 'self', R_HEADSIZE_FEATURE_ID => 'self', R_WEIGHT_FEATURE_ID => 'self', R_STIFFNESS_FEATURE_ID => 'self') // regular rackets
            ),
            'A' => array(
                array('52' => 'self', '50' => 'self')
            ),
            'S' => array(
                array('54' => 'self')
            ),
            'B' => array(
                array('84' => 'self', '58' => '940'),
                array('58' => 'self')
            ),
            'ST' => array(
                array('77' => 'self', '60' => 'self')
            ),
            'BL' => array(
                array('64' => 'self')
            ),
            'OG' => array(
                array('66' => 'self')
            ),
            'BG' => array(
                array('72' => 'self')
            ),
            'PC' => array(
                array('34' => 'self'),
                array('84' => 'self')
            ),
        );
        $digit_features = array(R_WEIGHT_FEATURE_ID, R_STIFFNESS_FEATURE_ID, R_BALANCE_FEATURE_ID);
        if (!empty($_SESSION['category_type']) && !empty($similar_products_features[$_SESSION['category_type']]) && !empty($_SESSION['product_features'])) {
            foreach ($similar_products_features[$_SESSION['category_type']] as $i => $ors) {
                $feature_condition = array();
                foreach ($ors as $feature_id => $val) {
                    if (!empty($_SESSION['product_features'][$feature_id])) {
                        if (!empty($_SESSION['product_features'][$feature_id]['variants'])) {
                            foreach ($_SESSION['product_features'][$feature_id]['variants'] as $j => $variant) {
                                if (!empty($variant['variant_id'])) {
                                    $feature_condition[$feature_id]['variants'][] = array(
                                        'variant_id' => ($val == 'self') ? $variant['variant_id'] : $val,
                                    );
                                }
                            }
                        } else {
                            if (!in_array($feature_id, $digit_features)) {
                                if (!empty($_SESSION['product_features'][$feature_id]['variant_id'])) {
                                    $feature_condition[$feature_id] = array(
                                        'variant_id' => ($val == 'self') ? $_SESSION['product_features'][$feature_id]['variant_id'] : $val,
                                    );
                                } elseif (!empty($_SESSION['product_features'][$feature_id]['value'])) {
                                    $feature_condition[$feature_id] = array(
                                        'value' => ($val == 'self') ? $_SESSION['product_features'][$feature_id]['value'] : $val,
                                    );
                                }
                            } else {
                                if ($feature_id == R_WEIGHT_FEATURE_ID) {
                                    $margin_value = 5;
                                }
                                if ($feature_id == R_STIFFNESS_FEATURE_ID) {
                                    $margin_value = 2;
                                }
                                if ($feature_id == R_BALANCE_FEATURE_ID) {
                                    $margin_value = 0.32;
                                }
                                $feature_condition[$feature_id] = array(
                                    'min_value' => ($val == 'self') ? $_SESSION['product_features'][$feature_id]['variant_name'] - $margin_value : $val,
                                    'max_value' => ($val == 'self') ? $_SESSION['product_features'][$feature_id]['variant_name'] + $margin_value : $val
                                );
                            }
                        }
                    } elseif (!empty($_SESSION['product_category'])) {
                        $condition .= db_quote(" AND ?:categories.category_id = ?i", $_SESSION['product_category']);
                    } else {
                        $condition .= " AND NULL";
                    }
                }
                $params['features_condition'][] = $feature_condition;
            }
        } elseif (!empty($_SESSION['product_category'])) {
            $condition .= db_quote(" AND ?:categories.category_id = ?i", $_SESSION['product_category']);
        } else {
            $condition .= " AND NULL";
        }
    }
    if (!empty($params['same_brand_pid'])) {
        if (!empty($_SESSION['product_features'][BRAND_FEATURE_ID])) {
            $params['features_condition'][] = array(
                BRAND_FEATURE_ID => array(
                    'variant_id' => $_SESSION['product_features'][BRAND_FEATURE_ID]['variant_id']
                )
            );
        } else {
            $condition .= " AND NULL";
        }
    }
    if (!empty($params['features_condition'])) {
        FeaturesCache::getProductsConditions($params['features_condition'], $join, $condition, $lang_code);
    }
    if (!empty($params['view_statuses'])) {
        $condition = str_replace(db_quote(' AND products.status IN (?a)', array('A')), db_quote(' AND products.status IN (?a)', $params['view_statuses']), $condition);
    }
    if (!empty($params['player_id'])) {
        $join .= db_quote(" LEFT JOIN ?:players_gear AS p_gear ON p_gear.product_id = products.product_id");
        $condition .= db_quote(' AND p_gear.player_id = ?i', $params['player_id']);
    }
    $join .= db_quote(" LEFT JOIN ?:product_tags ON ?:product_tags.product_id = products.product_id");
    $fields[] = 'GROUP_CONCAT(DISTINCT(?:product_tags.tag)) AS tags';

    if (in_array('description', $params['extend'])) {
//         $fields['short_description'] = 'descr1.short_description';

        if (in_array('full_description', $params['extend'])) {
            $fields['full_description'] = "IF(descr1.full_description = '', descr1.features_description, descr1.full_description) as full_description";
        } else {
            $fields['full_description'] = "IF(descr1.short_description = '', IF(descr1.full_description = '', descr1.features_description, descr1.full_description), '') as full_description";
        }
    }
}

function fn_development_get_products_pre(&$params, $items_per_page, $lang_code)
{
    if (!empty($params['q'])) {
        $params['q'] = preg_replace("/теннис[\w]*/iu", '', $params['q']);
    }
    if (!empty($params['shoes_surface'])) {
        $params['cid'] = SHOES_CATEGORY_ID;
        $params['subcats'] = 'Y';
        $feature_hash = 'V' . ALLCOURT_SURFACE_FV_ID;
        if ($params['shoes_surface'] == 'clay') {
            $feature_hash .= '.V' . CLAY_SURFACE_FV_ID;
        }
        if ($params['shoes_surface'] == 'grass') {
            $feature_hash .= '.V' . GRASS_SURFACE_FV_ID;
        }
        $params['features_hash'] = (!empty($params['features_hash']) ? '.' : '') . $feature_hash;
    }
    if (!empty($params['rackets_type'])) {
        $params['cid'] = RACKETS_CATEGORY_ID;
        $params['subcats'] = 'Y';
        if ($params['rackets_type'] == 'power') {
            $feature_hash = 'V' . POWER_RACKET_FV_ID;
        }
        if ($params['rackets_type'] == 'club') {
            $feature_hash = 'V' . CLUB_RACKET_FV_ID;
        }
        if ($params['rackets_type'] == 'pro') {
            $feature_hash = 'V' . PRO_RACKET_FV_ID;
        }
        $feature_condition = array();
        if ($params['rackets_type'] == 'heavy_head_light') {
            $feature_condition[R_WEIGHT_FEATURE_ID] = array(
                'min_value' => 300
            );
            $feature_condition[R_BALANCE_FEATURE_ID] = array(
                'max_value' => 35
            );
        }
        if ($params['rackets_type'] == 'light_head_heavy') {
            $feature_condition[R_WEIGHT_FEATURE_ID] = array(
                'max_value' => 300
            );
            $feature_condition[R_BALANCE_FEATURE_ID] = array(
                'min_value' => 35
            );
        }
        if ($params['rackets_type'] == 'stiff') {
            $feature_condition[R_STIFFNESS_FEATURE_ID] = array(
                'min_value' => 65
            );
        }
        if ($params['rackets_type'] == 'soft') {
            $feature_condition[R_STIFFNESS_FEATURE_ID] = array(
                'max_value' => 64
            );
        }
        if ($params['rackets_type'] == 'regular_head') {
            $feature_condition[R_HEADSIZE_FEATURE_ID] = array(
                'min_value' => 612,
                'max_value' => 677
            );
        }
        if ($params['rackets_type'] == 'regular_length') {
            $feature_condition[R_LENGTH_FEATURE_ID] = array(
                'variant_id' => REGULAR_LENGTH_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_17') {
            $feature_condition[R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_17_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_19') {
            $feature_condition[R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_19_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_21') {
            $feature_condition[R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_21_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_23') {
            $feature_condition[R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_23_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_25') {
            $feature_condition[R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_25_FV_ID
            );
        }
        if ($params['rackets_type'] == 'kids_26') {
            $feature_condition[R_LENGTH_FEATURE_ID] = array(
                'variant_id' => KIDS_26_FV_ID
            );
        }
        if ($params['rackets_type'] == 'closed_pattern') {
            $feature_condition[R_STRING_PATTERN_FEATURE_ID] = array(
                'variant_id' => CLOSED_PATTERN_FV_ID
            );
        }
        if ($params['rackets_type'] == 'open_pattern') {
            $feature_condition[R_STRING_PATTERN_FEATURE_ID] = array(
                'not_variant' => CLOSED_PATTERN_FV_ID
            );
        }
        if (!empty($feature_condition)) {
            $params['features_condition'][] = $feature_condition;
        }
        if (!empty($feature_hash)) {
            $params['features_hash'] = (!empty($params['features_hash']) ? '.' : '') . $feature_hash;
        }
    }
    if (!empty($params['strings_type'])) {
        $params['cid'] = STRINGS_CATEGORY_ID;
        $params['subcats'] = 'Y';
        if ($params['strings_type'] == 'natural_gut') {
            $params['cid'] = NATURAL_GUT_MATERIAL_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'nylon') {
            $params['cid'] = NYLON_MATERIAL_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'polyester') {
            $params['cid'] = POLYESTER_MATERIAL_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'hybrid') {
            $params['cid'] = HYBRID_MATERIAL_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'monofil') {
            $params['cid'] = MONO_STRUCTURE_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'multifil') {
            $params['cid'] = MULTI_STRUCTURE_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'textured') {
            $params['cid'] = TEXTURED_STRUCTURE_CATEGORY_ID;
        }
        if ($params['strings_type'] == 'synthetic_gut') {
            $params['cid'] = SYNTH_GUT_STRUCTURE_CATEGORY_ID;
        }
        if (!empty($feature_hash)) {
            $params['features_hash'] = (!empty($params['features_hash']) ? '.' : '') . $feature_hash;
        }
    }
}

function fn_development_delete_product_post($product_id, $product_deleted)
{
    if ($product_deleted) {
        db_query("DELETE FROM ?:players_gear WHERE product_id = ?i", $product_id);
        db_query("DELETE FROM ?:product_technologies WHERE product_id = ?i", $product_id);
        db_query("DELETE FROM ?:product_warehouses_inventory WHERE product_id = ?i", $product_id);
        FeaturesCache::deleteProduct($product_id);
    }
}

function fn_development_generate_cart_id(&$_cid, $extra, $only_selectable)
{
    if (isset($extra['warehouse_id'])) {
        $_cid[] = $extra['warehouse_id'];
    }
    if (isset($extra['combination_hash'])) {
        $_cid[] = $extra['combination_hash'];
    }
}

function fn_development_update_product_post($product_data, $product_id, $lang_code, $create)
{
    if (isset($product_data['players'])) {
        if ($create) {
            $existing_gear = array();
        } else {
            $existing_gear = db_get_fields("SELECT player_id FROM ?:players_gear WHERE product_id = ?i", $product_id);
        }
        $product_data['players'] = (empty($product_data['players'])) ? array() : explode(',', $product_data['players']);
        $to_delete = array_diff($existing_gear, $product_data['players']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:players_gear WHERE player_id IN (?n) AND product_id = ?i", $to_delete, $product_id);
        }
        $to_add = array_diff($product_data['players'], $existing_gear);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'product_id' => $product_id,
                    'player_id' => $gr
                );
                db_query("REPLACE INTO ?:players_gear ?e", $__data);
            }
        }
    }

    if (isset($product_data['technologies'])) {
        if ($create) {
            $existing_products = array();
        } else {
            $existing_products = db_get_fields("SELECT technology_id FROM ?:product_technologies WHERE product_id = ?i", $product_id);
        }
        $product_data['technologies'] = (empty($product_data['technologies'])) ? array() : explode(',', $product_data['technologies']);
        $to_delete = array_diff($existing_products, $product_data['technologies']);

        if (!empty($to_delete)) {
            db_query("DELETE FROM ?:product_technologies WHERE technology_id IN (?n) AND product_id = ?i", $to_delete, $product_id);
        }
        $to_add = array_diff($product_data['technologies'], $existing_products);

        if (!empty($to_add)) {
            foreach ($to_add as $i => $gr) {
                $__data = array(
                    'product_id' => $product_id,
                    'technology_id' => $gr
                );
                db_query("REPLACE INTO ?:product_technologies ?e", $__data);
            }
        }
    }

    if (isset($product_data['warehouse_inventory']) && isset($product_data['warehouse_ids'])) { // to exclude products list update
        if (isset($product_data['warehouse_inventory'])) {
            if ($create) {
                $_data = array(
                    'warehouse_hash' => fn_generate_cart_id($product_id, array('warehouse_id' => TH_WAREHOUSE_ID)),
                    'warehouse_id' => TH_WAREHOUSE_ID,
                    'product_id' => $product_id,
                    'amount' => $product_data['warehouse_inventory']
                );
                db_query("REPLACE ?:product_warehouses_inventory ?e", $_data);

            } elseif (isset($product_data['warehouse_inventory'])) {
                foreach ($product_data['warehouse_inventory'] as $wh_hash => $wh_data) {
                    db_query("UPDATE ?:product_warehouses_inventory SET ?u WHERE warehouse_hash = ?i", $wh_data, $wh_hash);
                }
            }
        }

        $warehouse_ids = explode(',', $product_data['warehouse_ids']);
        db_query("DELETE FROM ?:product_warehouses_inventory WHERE warehouse_id NOT IN (?a) AND product_id = ?i", $warehouse_ids, $product_id);

        $to_add = array_diff($warehouse_ids, db_get_fields("SELECT warehouse_id FROM ?:product_warehouses_inventory WHERE product_id = ?i AND combination_hash = '0'", $product_id));
        if (!empty($to_add)) {
            $combinations = db_get_array("SELECT * FROM ?:product_options_inventory WHERE product_id = ?i", $product_id);
            foreach ($to_add as $i => $wh_id) {
                $_data = array(
                    'warehouse_hash' => fn_generate_cart_id($product_id, array('warehouse_id' => $wh_id)),
                    'warehouse_id' => $wh_id,
                    'product_id' => $product_id,
                    'amount' => 0
                );
                db_query("REPLACE ?:product_warehouses_inventory ?e", $_data);
                if (!empty($combinations)) {
                    foreach ($combinations as $k => $combination) {
                        $options = fn_get_product_options_by_combination($combination['combination']);
                        $_data = array(
                            'warehouse_hash' => fn_generate_cart_id($product_id, array('product_options' => $options, 'warehouse_id' => $wh_id)),
                            'warehouse_id' => $wh_id,
                            'product_id' => $product_id,
                            'combination_hash' => $combination['combination_hash'],
                            'amount' => 0
                        );
                        db_query("REPLACE ?:product_warehouses_inventory ?e", $_data);
                    }
                }
            }
        }
    }

    // Generate thumbail to speed up the first loading of the products list
    $main_pair = fn_get_image_pairs($product_id, 'product', 'M', true, true, $lang_code);
    if (!empty($main_pair)) {
        fn_image_to_display($main_pair, Registry::get('settings.Thumbnails.product_lists_thumbnail_width'), Registry::get('settings.Thumbnails.product_lists_thumbnail_height'));
    }

    $rebuild_model = false;
    if (!empty($product_data['old_data'])) {

        if ($product_data['old_data']['product_code'] != $product_data['product_code']) {
            fn_rebuild_inventory_codes($product_id);
        }

        if ($product_data['old_data']['brand_id'] != $product_data['product_features'][BRAND_FEATURE_ID] || $product_data['old_data']['product'] != $product_data['product']) {
            $rebuild_model = true;
        }
    }

    if (!empty($rebuild_model) || empty($product_data['model'])) {
        $brand_name = db_get_field("SELECT b.variant FROM ?:product_features_values AS a LEFT JOIN ?:product_feature_variant_descriptions AS b ON b.variant_id = a.variant_id AND b.lang_code = ?s WHERE a.product_id = ?i AND a.feature_id = ?i", $lang_code, $product_id, BRAND_FEATURE_ID);

        if (!empty($brand_name)) {
            $model = trim(preg_replace(array('/[а-яА-Я]/u', '/' . $brand_name . '/iu'), '', $product_data['product']));
        } else {
            $model = $product_data['product'];
        }

        db_query("UPDATE ?:products SET model = ?s WHERE product_id = ?i", $model, $product_id);
    }
}

function fn_development_update_product_pre(&$product_data, $product_id, $lang_code, $can_update)
{
    if (!empty($product_data['category_ids'])) { // to exclude products list update
        $id_path = explode('/', db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $product_data['main_category']));
        if (!empty($product_data['product_type'])) {
            if ($product_data['product_type'] != 'P') {
                $product_data['discussion_type'] = 'D';
            } else {
                $product_data['discussion_type'] = fn_check_category_discussion($id_path);
            }
        }
        $product_data['feature_comparison'] = fn_check_category_comparison($id_path);

        $players = (empty($product_data['players'])) ? array() : explode(',', $product_data['players']);
        $variant_ids = db_get_fields("SELECT feature_variant_id FROM ?:players WHERE player_id IN (?n)", $players);
        $product_data['product_features'][PLAYER_FEATURE_ID] = array_combine($variant_ids, $variant_ids);

        $global_data = fn_get_product_global_data($product_data, array('shipping_weight', 'margin', 'net_currency_code'));
        $product_data['global_margin'] = $global_data['margin'];
        $product_data['global_net_currency_code'] = !empty($global_data['net_currency_code']) ? $global_data['net_currency_code'] : CART_PRIMARY_CURRENCY;
        if (empty($product_data['net_currency_code']) && !empty($global_data['net_currency_code'])) {
            $product_data['net_currency_code'] = $global_data['net_currency_code'];
        }
        if (empty($product_data['margin']) || $product_data['margin'] == 0) {
            fn_get_product_margin($product_data);
        }

        $old_data = db_get_row("SELECT price_mode, margin, net_cost, net_currency_code, product_code, product, variant_id AS brand_id FROM ?:products LEFT JOIN ?:product_descriptions ON ?:product_descriptions.product_id = ?:products.product_id AND ?:product_descriptions.lang_code = ?s LEFT JOIN ?:product_features_values ON ?:product_features_values.product_id = ?:products.product_id AND ?:product_features_values.feature_id = ?i WHERE ?:products.product_id = ?i", $lang_code, BRAND_FEATURE_ID, $product_id);

        if (!empty($product_data['price_mode']) && $product_data['price_mode'] == 'D' && $product_data['net_cost'] > 0 && $product_data['margin'] > 0 && !empty($product_data['net_currency_code']) && (empty($old_data['price_mode']) || $product_data['price_mode'] != $old_data['price_mode'] || $product_data['margin'] != $old_data['margin'] || $product_data['net_cost'] != $old_data['net_cost'] || $product_data['net_currency_code'] != $old_data['net_currency_code'])) {
            $base_price = fn_calculate_base_price($product_data);
            $product_data['price'] = fn_round_price($base_price);
            if (!empty($product_data['prices'])) {
                foreach ($product_data['prices'] as $i => $p_data) {
                    if (!empty($p_data['lower_limit'])) {
                        if ($p_data['lower_limit'] == 1) {
                            $product_data['prices'][$i]['price'] = fn_round_price($base_price);
                        } elseif ($p_data['type'] == 'A') {
                            $product_data['prices'][$i]['price'] = fn_round_price($base_price - $base_price * RACKETS_QTY_DSC_PRC / 100);
                        }
                    }
                }
            }
        }

        if (!empty($old_data)) {
            $product_data['old_data'] = $old_data;
        }
        if (empty($product_data['weight']) && !empty($global_data['shipping_weight'])) {
            $product_data['weight'] = $global_data['shipping_weight'];
        }

        $product_data['warehouse_ids'] = array(TH_WAREHOUSE_ID);
        if (!empty($product_data['product_features'][BRAND_FEATURE_ID])) {
            $brand_warehouse_ids = db_get_fields("SELECT warehouse_id FROM ?:warehouse_brands WHERE brand_id = ?i", $product_data['product_features'][BRAND_FEATURE_ID]);
            if (!empty($brand_warehouse_ids)) {
                $product_data['warehouse_ids'] = array_merge($product_data['warehouse_ids'], $brand_warehouse_ids);
            }
        }
        if (!empty($product_data['warehouse_ids'])) {
            $product_data['warehouse_ids'] = implode(',', $product_data['warehouse_ids']);
        }
        $variations = db_get_fields("SELECT product_id FROM ?:products WHERE product_code = ?s", $product_data['product_code']);
        $new_variations = !empty($product_data['variation_ids']) ? explode(',', $product_data['variation_ids']) : array();
        if (!empty($variations)) {
            $new_variations = array_merge($new_variations, array_diff($variations, $new_variations));
        }
        $key = array_search($product_id, $new_variations);
        if ($key !== false) {
            unset($new_variations[$key]);
        }
        $new_variations = array_filter(array_unique($new_variations));
        $product_data['variation_ids'] = implode(',', $new_variations);

        $old_variations = db_get_field("SELECT variation_ids FROM ?:products WHERE product_id = ?i", $product_id);
        if (!empty($old_variations)) {
            $old_variations = explode(',', $old_variations);
            $diff = array_diff($old_variations, $new_variations);
            if (!empty($diff)) {
                foreach ($diff as $pr_id) {
                    $pr_variations = db_get_field("SELECT variation_ids FROM ?:products WHERE product_id = ?i", $pr_id);
                    $pr_variations = explode(',', $pr_variations);
                    $key = array_search($product_id, $pr_variations);
                    if ($key !== false) {
                        unset($pr_variations[$key]);
                        db_query("UPDATE ?:products SET variation_ids = ?s WHERE product_id = ?i", implode(',', array_filter(array_unique($pr_variations))), $pr_id);
                    }
                }
            }
        }
        if (!empty($new_variations)) {
            foreach ($new_variations as $i => $pr_id) {
                $pr_variations = db_get_field("SELECT variation_ids FROM ?:products WHERE product_id = ?i", $pr_id);
                $pr_variations = explode(',', $pr_variations);
                $pr_variations[] = $product_id;
                db_query("UPDATE ?:products SET variation_ids = ?s WHERE product_id = ?i", implode(',', array_filter(array_unique($pr_variations))), $pr_id);
            }
        }
        if (!empty($product_data['generate_description_out_of_features']) && $product_data['generate_description_out_of_features'] == 'Y') {
            $product_ids = array($product_id);
            list($description,) = fn_generate_product_features_descriptions($product_ids);
            if (!empty($description[$product_id])) {
                $product_data['features_description'] = '<p>' . $description[$product_id] . '</p>';
            }
        }
    }
}

function fn_development_update_category_post($category_data, $category_id, $lang_code)
{
    if (!empty($category_data['apply_to_products'])) {
        $products = $prod_ids = array();
        $_params = array (
            'cid' => $category_id,
            'subcats' => 'Y'
        );
        list($prods,) = fn_get_products($_params);
        foreach ($prods as $i => $prod) {
            $prod_ids[] = $prod['product_id'];
        }
        if (!empty($prods)) {
            if (!empty($category_data['apply_to_products']['shipping_weight']) && $category_data['apply_to_products']['shipping_weight'] == 'Y' && !empty($prod_ids)) {
                db_query("UPDATE ?:products SET weight = ?d WHERE product_id IN (?n)", $category_data['shipping_weight'], $prod_ids);
            }
            if (!empty($category_data['apply_to_products']['margin']) && $category_data['apply_to_products']['margin'] == 'Y') {
                foreach ($prods as $i => $prod) {
                    if (!empty($prod['price_mode']) && $prod['price_mode'] == 'D' && $prod['net_cost'] > 0) {
                        unset($prod['margin']);
                        $products[$prod['product_id']] = $prod;
                    }
                }
                if (!empty($products)) {
                    fn_process_update_prices($products);
                }
            }
            if (!empty($category_data['apply_to_products']['products_price_mode']) && !empty($prod_ids)) {
                db_query("UPDATE ?:products SET price_mode = ?s WHERE product_id IN (?n)", $category_data['products_price_mode'], $prod_ids);
            }
            if (!empty($category_data['apply_to_products']['shipping_params'])) {
                db_query("UPDATE ?:products SET shipping_params = ?s WHERE product_id IN (?n)", $category_data['shipping_params'], $prod_ids);
            }
        }
    }
}

function fn_development_get_product_data($product_id, &$field_list, &$join, $auth, $lang_code, $condition)
{
    $join .= db_quote(" LEFT JOIN ?:product_tags ON ?:product_tags.product_id = ?:products.product_id");
    $field_list .= ", GROUP_CONCAT(DISTINCT CONCAT_WS('_', ?:product_tags.promotion_id, ?:product_tags.tag) SEPARATOR ',') AS tags";
}

function fn_development_get_product_data_post(&$product_data, $auth, $preview, $lang_code)
{
    if (AREA == 'A') {
        $plain = true;
    } else {
        $plain = false;
    }
    list($players, ) = fn_get_players(array('product_id' => $product_data['product_id'], 'plain' => $plain));
    list($technologies, ) = fn_get_technologies(array('product_id' => $product_data['product_id'], 'plain' => $plain));
    if (AREA == 'A') {
        $product_data['players'] = implode(',', array_keys($players));
        $product_data['technologies'] = implode(',', array_keys($technologies));
//         list($warehouses, ) = fn_get_warehouses(array('product_id' => $product_data['product_id']));
//         $product_data['warehouses'] = implode(',', array_keys($warehouses));
        $product_data['warehouse_inventory'] = db_get_hash_array("SELECT pwh.warehouse_hash, pwh.amount, wh.name FROM ?:product_warehouses_inventory AS pwh LEFT JOIN ?:warehouses AS wh ON pwh.warehouse_id = wh.warehouse_id WHERE pwh.product_id = ?i AND pwh.combination_hash = '0'", 'warehouse_hash', $product_data['product_id']);
    } else {
        $product_data['players'] = $players;
        $product_data['technologies'] = $technologies;
    }
    $types_ids = fn_get_categories_types($product_data['main_category']);
    $product_data['id_path'] = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $product_data['main_category']);
    list($product_data['type_id'], $product_data['type']) = fn_identify_category_type($product_data['id_path']);
    list($product_data['category_type_id'], $product_data['category_type']) = (!empty($product_data['product_type']) && $product_data['product_type'] == 'C') ? array('', 'PC') : fn_get_category_type($types_ids[$product_data['main_category']]);
    if (!in_array($product_data['category_type'], array('A', 'S'))) {
        $product_data['offer_help'] = true;
    }
    $product_data['category_main_id'] = $types_ids[$product_data['main_category']];
    if (!empty($product_data['tags'])) {
        $tags = explode(',', $product_data['tags']);
        $product_data['tags'] = array();
        $promo_ids = array();
        foreach ($tags as $tag) {
            $pair = explode('_', $tag);
            $product_data['tags'][$pair[0]] = $pair[1];
            if ($pair[1] == PROMOTION_TAG) {
                $promo_ids[] = $pair[0];
            }
        }
        if (!empty($promo_ids)) {
            $params = array (
                'active' => true,
                'get_hidden' => false,
                'show_on_site' => 'Y',
                'plain' => false,
                'promotion_id' => $promo_ids
            );

            list($product_data['available_promotions']) = fn_get_promotions($params);
        }
    } else {
        $product_data['tags'] = array();
    }
    if (AREA == 'C') {
        $product_data['is_free_shipping'] = fn_is_free_shipping($product_data);
        if (!empty($product_data['features_description'])) {
            if (empty($product_data['full_description'])) {
                $product_data['full_description'] = $product_data['features_description'];
            } else {
                $product_data['short_description'] .= $product_data['features_description'];
            }
        }
    }
    if (AREA == 'A') {
        $product_data['price_history'] = db_get_array("SELECT * FROM ?:price_history WHERE product_id = ?i ORDER BY timestamp ASC", $product_data['product_id']);
    }
}

function fn_development_get_products_post(&$products, &$params, $lang_code)
{
    if (!empty($products)) {
        $main_ids = array();
        foreach ($products as $i => $product) {
            $main_ids = array_merge($main_ids, $product['category_ids']);
        }
        $id_paths = db_get_hash_single_array("SELECT category_id, id_path FROM ?:categories WHERE category_id IN (?n)", array('category_id', 'id_path'), array_unique($main_ids));
        $new_period = Registry::get('addons.development.new');
        $now = getdate(TIME);
        $time_limit = mktime($now['hours'], $now['minutes'], $now['seconds'], $now['mon'] - $new_period, $now['mday'], $now['year']);
        foreach ($products as $i => $product) {
            $products[$i]['tags'] = !empty($product['tags']) ? explode(',', $product['tags']) : array();
            $products[$i]['id_path'] = $id_paths[$product['main_category']];
            foreach ($product['category_ids'] as $j => $cat_id) {
                $products[$i]['all_path'][$cat_id] = $id_paths[$cat_id];
            }
            list($products[$i]['type_id'], $products[$i]['type']) = fn_identify_category_type($products[$i]['id_path']);
            if (!empty($product['timestamp']) && $product['timestamp'] > $time_limit) {
                $products[$i]['tags']['new'] = 1;
            }
        }
    }
    if (!empty($params['shuffle']) && $params['shuffle'] == 'Y') {
        shuffle($products);
    }
}

function fn_development_is_shared_product_pre($product_id, $company_id, &$return)
{
    $return = 'N';
}

function fn_development_render_blocks($grid, &$block, $object, $content)
{
    if (AREA == 'C' && !empty($block['properties']['capture_content']) && $block['properties']['capture_content'] == 'Y' && $block['object_type'] == 'products' && !empty($block['object_id'])) {
        $product = Registry::get('view')->getTemplateVars('product');
        if (!empty($product) && $product['tracking'] != 'D' && ($product['product_type'] != 'C' && $product['amount'] <= 0) || ($product['product_type'] == 'C' && empty($product['hide_stock_info']))) {
            $dmode = fn_get_session_data('dmode');
            if ($dmode != 'M') {
                $block['extra_properties']['columns_number'] = 2;
            }
            if ($block['content']['items']['filling'] == 'similar_products') {
                $block['extra_properties']['name'] = 'Похожие товары в наличии';
            }
        }
    }
}

function fn_development_render_block_content_pre($template_variable, $field, $block_scheme, &$block)
{
    if (!empty($block['extra_properties'])) {
        if (!empty($block['extra_properties']['columns_number'])) {
            $block['properties']['columns_number'] = $block['extra_properties']['columns_number'];
        }
        if (!empty($block['extra_properties']['name'])) {
            Registry::get('view')->assign('title', $block['extra_properties']['name']);
        }
        Registry::get('view')->assign('block', $block);
    }
    if (!empty($block['content']['items']['filling']) && in_array($block['content']['items']['filling'], array('similar_products', 'also_bought', 'same_brand_products'))) {
        $request_data = !empty($block['request_data']) ? $block['request_data'] : $_REQUEST;
        if ($block['content']['items']['filling'] == 'similar_products') {
            $block['properties']['all_items_url'] = 'products.search?search_performed=Y&similar_pid=' . $request_data['product_id'];
        } elseif ($block['content']['items']['filling'] == 'also_bought') {
            $block['properties']['all_items_url'] = 'products.search?search_performed=Y&also_bought_for_product_id=' . $request_data['product_id'];
        } elseif ($block['content']['items']['filling'] == 'same_brand_products' && !empty($_SESSION['product_features'][BRAND_FEATURE_ID]['variant_id'])) {
            $block['properties']['all_items_url'] = 'products.search?search_performed=Y&features_hash=' . BRAND_FEATURE_TYPE . $_SESSION['product_features'][BRAND_FEATURE_ID]['variant_id'];
        }
        Registry::get('view')->assign('block', $block);
    }
}

function fn_development_render_block_register_cache($block, &$cache_name, &$block_scheme, $register_cache, $display_block)
{
    if (isset($block['content']['items']['filling']) && isset($block_scheme['content']['items']['fillings'][$block['content']['items']['filling']]['cache'])) {
        $block_scheme['cache'] = $block_scheme['content']['items']['fillings'][$block['content']['items']['filling']]['cache'];
    }
    if (!isset($block_scheme['cache']) && isset($block['properties']['template']) && isset($block_scheme['templates'][$block['properties']['template']]['cache'])) {
        $block_scheme['cache'] = $block_scheme['templates'][$block['properties']['template']]['cache'];
    }
    if (isset($block_scheme['cache']['no_object'])) {
        $grid_id = !empty($block['grid_id']) ? $block['grid_id'] : 0;
        $cache_name = 'block_content_'
            . $block['block_id'] . '_' . $block['snapping_id'] . '_' . $block['type']
            . '_' . $grid_id;
    }
//     if (in_array($_REQUEST['dispatch'], array('categories.view', 'products.sale')) && $block['type'] == 'main') {
//         $block_scheme['cache'] = array();
//         $params = $_REQUEST;
//         unset($params['dispatch']);
//         unset($params['save_view_results']);
//         $block_scheme['cache']['request_handlers'] = array_keys($params);
//         $block_scheme['cache']['update_handlers'] = array ('products', 'product_descriptions', 'product_prices', 'products_categories', 'categories', 'category_descriptions', 'product_warehouses_inventory');
//     }
}
