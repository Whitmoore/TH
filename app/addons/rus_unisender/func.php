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

// rus_unisender

use Tygh\Http;
use Tygh\Registry;
use Tygh\Mailer;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_rus_unisender_update_profile($action, $user_data, $current_user_data)
{
    if ($action == 'add') {
        if (fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id'))) {
            $arr_id = array($user_data['user_id']);
            fn_add_users_to_unisender($arr_id, false);
        }
    }
}

function fn_settings_variants_addons_rus_unisender_list_name()
{
    return fn_unisender_get_lists();
}

function fn_check_sms()
{
    $errors = array();
    $sms_list = db_get_hash_array("SELECT a.sms_id, a.sms_status FROM ?:sms_statuses AS a LEFT JOIN ?:orders AS b ON a.order_id = b.order_id WHERE b.status NOT IN (?a) AND (a.sms_status = 'ok_sent' OR a.sms_status = 'not_sent')", 'sms_id', ORDER_COMPLETE_STATUSES);
    if (!empty($sms_list)) {
        $api_key = Registry::get('addons.rus_unisender.api_key');
        $data = array(
            'api_key' => $api_key,
        );
        foreach ($sms_list as $sms_data) {
            $_data = $data;
            $_data['sms_id'] = $sms_data['sms_id'];
            $response = array();
            fn_unisender_api('checkSms', $_data, $response);
            if (!empty($response['status']) && $response['status'] != $sms_data['sms_status']) {
                db_query("UPDATE ?:sms_statuses SET sms_status = ?s WHERE sms_id = ?i", $response['status'], $sms_data['sms_id']);
            }
        }
    }
    
    return array(true, $errors);
}

function fn_add_users_to_unisender($user_ids, $notify = true)
{
    $api_key = Registry::get('addons.rus_unisender.api_key');
    $list_name = Registry::get('addons.rus_unisender.list_name');

    if (empty($list_name) && $notify) {
        fn_set_notification('E', __('notice'), __('addons.rus_unisender.users_not_added_list'));

        return false;
    }

    if (empty($api_key) && $notify) {
        fn_set_notification('E', __('notice'), __('addons.rus_unisender.users_not_added_key'));

        return false;
    }

    $post = array(
        'api_key' => $api_key,
        'double_optin' => '1'
    );

    $post['field_names'] = fn_unisender_get_export_fields();
    $post['data'] = fn_uniseder_get_export_users_fields($user_ids);

    if (fn_unisender_api('importContacts', $post, $response, $notify)) {
        if ($notify) {
            fn_set_notification('N', __('notice'), __('addons.rus_unisender.users_added'));

            if (!empty($response['invalid'])) {
                foreach ($response['log'] as $log) {
                    fn_set_notification('W', __('notice'), $post['data'][$log['index']][0] . ": " . $log['message']);
                }
            }
        }

        return true;
    }

    return false;
}

function fn_unisender_subscribe($user_data, $list_id, $activated = false)
{
    $api_key = Registry::get('addons.rus_unisender.api_key');

    if (!empty($api_key) && !empty($user_data['email'])) {
        $ip = fn_get_ip();
        $user_field = fn_unisender_get_user_fields();

        $post = array(
            'api_key' => $api_key,
            'fields' => fn_uniseder_get_fields($user_data, $user_field),
            'request_ip' => $ip['host'],
            'request_time' => date('Y-m-d H:m:s', TIME),
            'overwrite' => 2,
            'double_optin' => '0',
            'list_ids' => $list_id,
        );

        if ($activated) {
            $post['double_optin'] = 3;
        }

        if (fn_unisender_api('subscribe', $post, $response)) {

            $data = array(
                'subscriber_id' => fn_unisender_get_subscriber_id($user_data['email'], true),
                'list_id' => $list_id,
            );
            db_query("REPLACE INTO ?:unisender_user_mailing_lists ?e", $data);

            return true;
        }
    }

    return false;
}

function fn_unisender_unsubscribe($subscriber_id)
{
    if (!empty($subscriber_id)) {
        $email = db_get_field("SELECT email FROM ?:unisender_subscribers WHERE subscriber_id = ?i", $subscriber_id);
        $post = array(
            'api_key' => Registry::get('addons.rus_unisender.api_key'),
            'list_ids' => Registry::get('addons.rus_unisender.list_name'),
            'contact_type' => 'email',
            'contact' => $email,
        );

        if (fn_unisender_api('exclude', $post, $response)) {
            db_query("DELETE FROM ?:unisender_user_mailing_lists WHERE subscriber_id = ?i", $subscriber_id);
        }
    }

    return true;
}

function fn_unisender_add_subscriber($email)
{
    return db_query("INSERT INTO ?:unisender_subscribers ?e", array('email' => $email));
}

function fn_unisender_get_subscriber_id($email, $create = false)
{
    $subscriber_id = db_get_field("SELECT subscriber_id FROM ?:unisender_subscribers WHERE email = ?s", $email);

    if (empty($subscriber_id) && $create) {
        $subscriber_id = fn_unisender_add_subscriber($email);
    }

    return $subscriber_id;
}

function fn_unisender_get_user_lists($email)
{
    $user_lists = array();
    if (!empty($email)) {
        $user_lists = db_get_hash_array("SELECT * FROM ?:unisender_subscribers AS s INNER JOIN ?:unisender_user_mailing_lists AS uml ON s.subscriber_id = uml.subscriber_id WHERE s.email = ?s", 'list_id', $email);
        $lists = fn_unisender_get_lists();

        foreach ($user_lists as $key => $data) {
            $user_lists[$key]['title'] = $lists[$key];
        }
    }

    return $user_lists;
}

function fn_unisender_get_enabled_lists()
{
    $list_id = Registry::get('addons.rus_unisender.list_name');
    $lists = fn_unisender_get_lists();

    $enable_lists = array();

    foreach ($lists as $key => $data) {
        if ($list_id == $key) {
            $enable_lists[$key]['title'] = $lists[$key];
            $enable_lists[$key]['list_id'] = $key;
        }
    }

    return $enable_lists;
}

function fn_unisender_get_lists()
{
    $lists = array();
    $api_key = Registry::get('addons.rus_unisender.api_key');

    if (!empty($api_key)) {
        if (fn_unisender_api('getLists', array('api_key' => $api_key), $response)) {
            $lists[] = __('none');
            foreach ($response as $list) {
                $lists[$list['id']] = $list['title'];
            }
        } else {
            $lists[] = __('addons.rus_unisender.enter_api_key');
        }

    } else {
        $lists[] = __('addons.rus_unisender.enter_api_key');
    }

    return $lists;
}

function fn_unisender_api($method, $post, &$response, $notify = true)
{

    if (!isset($post['api_key']) || empty($post['api_key'])) {
        $error_text = 'Unisender: ' . __('addons.rus_unisender.invalid_api_key');
        $response = $error_text;

        if (AREA != 'C' && $notify) {
            fn_set_notification('E', __('notice'), $error_text);
        }

        return false;
    }

    Registry::set('log_cut', true);

    $result = Http::post('https://api.unisender.com/ru/api/' . $method . '?format=json', $post);
    $_result = json_decode($result, true);
    $response = '';
    if (empty($_result)) {
        if ($notify) {
            fn_set_notification('E', __('notice'), __('addons.rus_unisender.no_response'));
        }
        $response = __('no_response');

    } elseif (!empty($_result['error']) && AREA != 'C') {

        $error_text = __('error_message_not_sent') . ' Unisender: ' . $_result['error'];

        if ($_result['code'] == 'invalid_api_key') {
            $error_text = 'Unisender: ' . __('addons.rus_unisender.invalid_api_key');

        } elseif ($_result['error'] == 'ZS140227-02') {
            $error_text = __('addons.rus_unisender.error_ZS140227_02');

        } elseif ($_result['error'] == 'AK100311-03') {
            $error_text = __('addons.rus_unisender.error_AK100311_03');
        }

        if ($notify) {
            fn_set_notification('E', __('notice'), $error_text);
        }

        $response = $error_text;
    }

    if (empty($response) && !empty($_result['result'])) {
        $response = $_result['result'];

        return true;
    } else {
        return false;
    }
}

function fn_rus_unisender_send_sms($text_sms, $phone, $order_id = 0, $status_to = '', $notify=true)
{
    if (!empty($phone) && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
        $post = array(
            'api_key' => Registry::get('addons.rus_unisender.api_key'),
            'sender' => fn_substr(Registry::get('addons.rus_unisender.sender'), 0, 11),
            'phone' => fn_unisender_format_phone($phone),
        );

        $send_sms = false;

//         if ($order_id == 0) {
            $post['text'] = $text_sms;
            $send_sms = true;

//         } else {
//             $statuses = Registry::get('addons.rus_unisender.order_status_sms');
// 
//             if (array_key_exists($status_to, $statuses)) {
//                 $status_data = fn_get_status_data($status_to, STATUSES_ORDER);
//                 $status_description = $status_data['description'];
// 
//                 $text = str_replace('[status]', $status_description, $text_sms);
//                 $post['text'] = $text;
// 
//                 $send_sms = true;
//             }
//         }

        if ($send_sms) {
            if (!fn_unisender_api('sendSms', $post, $response)) {
                fn_set_notification('N', __('notice'), $response);
                if (AREA == 'C') {
                    $email = Registry::get('settings.Company.company_site_administrator');
                    Mailer::sendMail(array(
                        'to' => $email,
                        'from' => 'company_site_administrator',
                        'data' => array(
                            'phone' => $phone,
                            'error' => $response
                        ),
                        'tpl' => 'addons/rus_unisender/unisender.tpl',
                        'company_id' => fn_get_company_id('orders', 'order_id', $order_id),
                    ), 'C', CART_LANGUAGE);
                }
                
            } else {
            
                if (!empty($response['sms_id'])) {
                    $data = array(
                        'sms_id' => $response['sms_id'],
                        'phone' => $phone,
                        'text' => $post['text'],
                        'order_id' => $order_id,
                        'order_status' => $status_to,
                        'timestamp' => TIME,
                    );
                    db_query("REPLACE INTO ?:sms_statuses ?e", $data);
                }
                
                if (AREA == 'A') {
                    fn_set_notification('N', __('notice'), $post['text'], 'F');
                }
            }
        }
    }

    return false;
}

function fn_settings_variants_addons_rus_unisender_order_status_sms()
{
    $lists = array();
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, true);
    foreach ($order_statuses as $key => $value) {
        $lists[$key] = $value['description'];
    }

    return $lists;
}

function fn_rus_unisender_get_notification_rules(&$force_notification, $params, $disable_notification)
{
    $force_notification['UC'] = true;

    if (AREA == 'A' && empty($params['notify_unisender_users'])) {
        $force_notification['UC'] = false;
    }
}

function fn_rus_unisender_change_order_status($status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order)
{
    $addon_settings = Registry::get('addons.rus_unisender');
    if ($addon_settings['send_sms_user'] == 'Y' && (!empty($force_notification['UC']) && $force_notification['UC'])) {
        $text = $addon_settings['send_sms_user_text'];
        $text = str_replace('[order_id]', $order_info['order_id'], $text);
        fn_rus_unisender_send_sms($text, $order_info['phone'], $order_info['order_id'], $status_to);
    }
}

 //admin_sms
function fn_rus_unisender_place_order($order_id, $action, $order_status, $cart, $auth)
{

    if (Registry::get('addons.rus_unisender.send_sms_admin') == 'Y') {

        $admin_phone = Registry::get('addons.rus_unisender.admin_phone');
        if (!empty($admin_phone)) {

            $storefront = fn_get_company_name(fn_get_company_id('orders', 'order_id', $order_id));

            $text = Registry::get('addons.rus_unisender.send_sms_admin_text');
            $text = str_replace('[order_id]', $order_id, $text);
            $text = str_replace('[storefront]', $storefront, $text);
            $post = array(
                'api_key' => Registry::get('addons.rus_unisender.api_key'),
                'phone' => $admin_phone,
                'sender' => fn_substr(Registry::get('addons.rus_unisender.admin_sender'), 0, 11),
                'text' => $text
            );

            if (!fn_unisender_api('sendSms', $post, $response)) {
                $email = Registry::get('settings.Company.company_site_administrator');
                Mailer::sendMail(array(
                    'to' => $email,
                    'from' => 'company_site_administrator',
                    'data' => array(
                        'phone' => $admin_phone,
                        'error' => $response
                    ),
                    'tpl' => 'addons/rus_unisender/unisender.tpl',
                    'company_id' => fn_get_company_id('orders', 'order_id', $order_id),
                ), 'C', CART_LANGUAGE);
            }
        }
    }
}

function fn_unisender_get_fields($params = array())
{
    $condition = '1';

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND status = ?s", $params['status']);
    }

    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND company_id = ?i", Registry::get('runtime.company_id'));
    }

    return db_get_array("SELECT * FROM ?:unisender_fields WHERE ?p", $condition);
}

function fn_unisender_get_unisender_fields()
{
    $api_key = Registry::get('addons.rus_unisender.api_key');

    if (fn_unisender_api('getFields', array('api_key' => $api_key), $response)) {
        return $response;
    } else {
        return array();
    }
}

function fn_unisender_update_fields($fields)
{
    if (!empty($fields)) {
        $field_ids = array();
        $field_unique = array();

        foreach ($fields as $field) {

            if (in_array($field['unisender_field'], $field_unique)) {
                continue;
            }

            $field_unique[] = $field['unisender_field'];

            if (Registry::get('runtime.company_id')) {
                $field['company_id'] = Registry::get('runtime.company_id');
            }

            if (empty($field['field_id'])) {

                if (!empty($field['field']) && !empty($field['unisender_field'])) {
                    $field_ids[] = db_query("INSERT INTO ?:unisender_fields ?e", $field);
                }

            } else {
                $field_ids[] = $field['field_id'];
                $field_id = $field['field_id'];
                unset($field['field_id']);

                db_query("UPDATE ?:unisender_fields SET ?u WHERE field_id = ?i", $field, $field_id);
            }
        }

        $all_field_ids = db_get_fields("SELECT field_id FROM ?:unisender_fields");
        $remove_field_ids = array_diff($all_field_ids, $field_ids);

        $condition = '';
        if (Registry::get('runtime.company_id')) {
            $condition .= db_quote(" AND company_id = ?i", Registry::get('runtime.company_id'));
        }

        if (!empty($remove_field_ids)) {
            db_query("DELETE FROM ?:unisender_fields WHERE field_id IN (?a) ?p", $remove_field_ids, $condition);
        }
    }
}

function fn_unisender_get_user_fields()
{
    $export_field = array();
    $fields = fn_unisender_get_fields(array('status' => 'Y'));

    foreach ($fields as $field) {
        $export_field[$field['unisender_field']] = $field['field'];
    }

    return $export_field;
}

function fn_unisender_get_export_fields()
{
    $export_field = array(
        'email',
        'email_list_ids',
        'email_status',
        'phone',
        'phone_list_ids',
        'phone_status'
    );

    $fields = fn_unisender_get_fields(array('status' => 'Y'));

    foreach ($fields as $field) {
        $export_field[] = $field['unisender_field'];
    }

    return $export_field;
}

function fn_uniseder_get_export_users_fields($user_ids)
{
    $list_name = Registry::get('addons.rus_unisender.list_name');
    $user_field = fn_unisender_get_user_fields();

    $fields = array(
        '?:users.user_id',
        'email',
        'phone'
    );

    foreach ($user_field as $field_name) {
        $fields[] = $field_name;
    }

    $join = db_quote("INNER JOIN ?:user_profiles ON ?:users.user_id = ?:user_profiles.user_id");
    $users_data = db_get_hash_array("SELECT ?p FROM ?:users ?p WHERE ?:users.user_id IN (?a)", "user_id", implode(',', $fields), $join, $user_ids);

    $data = array();

    foreach ($users_data as $key => $user_data) {
        $data[$key] = fn_uniseder_get_fields($user_data, $user_field, $list_name);
        $data[$key] = array_values($data[$key]);
    }

    return array_values($data);
}

function fn_uniseder_get_fields($user_data, $user_field, $list_name = '')
{
    $data = array();
    $data['email'] = $user_data['email'];

    if (!empty($list_name)) {
        $data['email_list_ids'] = $list_name;
    }

    $data['email_status'] = 'active';
    $data['phone'] = !empty($user_data['phone']) ? $user_data['phone'] : '';

    if (!empty($list_name)) {
        $data['phone_list_ids'] = $list_name;
    }

    $data['phone_status'] = 'active';

    foreach ($user_field as $field_key => $field_name) {
        $data[$field_key] = !empty($user_data[$field_name]) ? $user_data[$field_name] : '';
    }

    return $data;
}

function fn_unisender_format_phone($phone)
{
    $phone = str_replace(array(' ', '(', ')', '-'), '', $phone);

    if (strlen($phone) == 11) {
        if ($phone[0] == '8') {
            $phone = '+7' . substr($phone, 1, 10);
        }
    } elseif (strlen($phone) == 10) {
        $phone = '+7' . $phone;
    }

    return $phone;
}
