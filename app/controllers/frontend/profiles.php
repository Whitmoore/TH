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
use Tygh\Session;
use Tygh\Mailer;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //
    // Create/Update user
    //
    if ($mode == 'update') {
        $is_update = !empty($auth['user_id']);

        if (!$is_update) {
            $is_valid_user_data = true;

            if (empty($_REQUEST['user_data']['email'])) {
                fn_set_notification('W', __('warning'), __('error_validator_required', array('[field]' => __('email'))));
                $is_valid_user_data = false;

            } elseif (!fn_validate_email($_REQUEST['user_data']['email'])) {
                fn_set_notification('W', __('error'), __('text_not_valid_email', array('[email]' => $_REQUEST['user_data']['email'])));
                $is_valid_user_data = false;
            }

            if (empty($_REQUEST['user_data']['password1'])) {

                fn_set_notification('W', __('warning'), __('error_validator_required', array('[field]' => __('password'))));

                $is_valid_user_data = false;

            }

            if (!$is_valid_user_data) {
                return array(CONTROLLER_STATUS_REDIRECT, 'profiles.add');
            }
        }

        fn_restore_processed_user_password($_REQUEST['user_data'], $_POST['user_data']);

        $res = fn_update_user($auth['user_id'], $_REQUEST['user_data'], $auth, !empty($_REQUEST['ship_to_another']), true);

        if ($res) {
            list($user_id, $profile_id) = $res;

            // Cleanup user info stored in cart
            if (!empty($_SESSION['cart']) && !empty($_SESSION['cart']['user_data'])) {
                $_SESSION['cart']['user_data'] = fn_array_merge($_SESSION['cart']['user_data'], $_REQUEST['user_data']);
            }

            // Delete anonymous authentication
            if ($cu_id = fn_get_session_data('cu_id') && !empty($auth['user_id'])) {
                fn_delete_session_data('cu_id');
            }

            Session::regenerateId();

            if (!empty($_REQUEST['return_url'])) {
                return array(CONTROLLER_STATUS_OK, $_REQUEST['return_url']);
            }

        } else {
            fn_save_post_data('user_data');
            fn_delete_notification('changes_saved');
        }

        if (!empty($user_id) && !$is_update) {
            $redirect_url = "profiles.success_add";
        } else {
            $redirect_url = "profiles." . (!empty($user_id) ? "update" : "add") . "?";

            if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
                $redirect_url .= "profile_id=$profile_id&";
            }

            if (!empty($_REQUEST['return_url'])) {
                $redirect_url .= 'return_url=' . urlencode($_REQUEST['return_url']);
            }
        }

        return array(CONTROLLER_STATUS_OK, $redirect_url);
    }
}

if ($mode == 'add') {

    if (!empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "profiles.update");
    }

    fn_add_breadcrumb(__('registration'));

    $user_data = array();
    if (!empty($_SESSION['cart']) && !empty($_SESSION['cart']['user_data'])) {
        $user_data = $_SESSION['cart']['user_data'];
    }

    $restored_user_data = fn_restore_post_data('user_data');
    if ($restored_user_data) {
        $user_data = fn_array_merge($user_data, $restored_user_data);
    }

    Registry::set('navigation.tabs.general', array (
        'title' => __('general'),
        'js' => true
    ));

    $params = array();
    if (isset($_REQUEST['user_type'])) {
        $params['user_type'] = $_REQUEST['user_type'];
    }

    $profile_fields = fn_get_profile_fields('C', array(), CART_LANGUAGE, $params);

    Registry::get('view')->assign('profile_fields', $profile_fields);
    Registry::get('view')->assign('user_data', $user_data);
    Registry::get('view')->assign('ship_to_another', fn_check_shipping_billing($user_data, $profile_fields));
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());

} elseif ($mode == 'update') {

    if (empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=".urlencode(Registry::get('config.current_url')));
    }

    $profile_id = empty($_REQUEST['profile_id']) ? 0 : $_REQUEST['profile_id'];
    fn_add_breadcrumb(__('editing_profile'));

    if (!empty($_REQUEST['profile']) && $_REQUEST['profile'] == 'new') {
        $user_data = fn_get_user_info($auth['user_id'], false);
    } else {
        $user_data = fn_get_user_info($auth['user_id'], true, $profile_id);
    }

    if (empty($user_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $restored_user_data = fn_restore_post_data('user_data');
    if ($restored_user_data) {
        $user_data = fn_array_merge($user_data, $restored_user_data);
    }

    Registry::set('navigation.tabs.general', array (
        'title' => __('general'),
        'js' => true
    ));

//     $show_usergroups = true;
//     if (Registry::get('settings.General.allow_usergroup_signup') != 'Y') {
//         $show_usergroups = fn_user_has_active_usergroups($user_data);
//     }

//     if ($show_usergroups) {
        $usergroups = fn_get_usergroups('C');
        if (!empty($usergroups)) {
//             Registry::set('navigation.tabs.usergroups', array (
//                 'title' => __('usergroups'),
//                 'js' => true
//             ));

            Registry::get('view')->assign('usergroups', $usergroups);
        }
//     }

    $profile_fields = fn_get_profile_fields();

    Registry::get('view')->assign('profile_fields', $profile_fields);
    Registry::get('view')->assign('user_data', $user_data);
    Registry::get('view')->assign('ship_to_another', fn_check_shipping_billing($user_data, $profile_fields));
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());
    if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
        Registry::get('view')->assign('user_profiles', fn_get_user_profiles($auth['user_id']));
    }

// Delete profile
} elseif ($mode == 'delete_profile') {

    fn_delete_user_profile($auth['user_id'], $_REQUEST['profile_id']);

    return array(CONTROLLER_STATUS_OK, "profiles.update");

} elseif ($mode == 'usergroups') {
    if (empty($auth['user_id']) || empty($_REQUEST['type']) || empty($_REQUEST['usergroup_id'])) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    if (fn_request_usergroup($auth['user_id'], $_REQUEST['usergroup_id'], $_REQUEST['type'])) {
        $user_data = fn_get_user_info($auth['user_id']);

        Mailer::sendMail(array(
            'to' => 'default_company_users_department',
            'from' => 'default_company_users_department',
            'reply_to' => $user_data['email'],
            'data' => array(
                'user_data' => $user_data,
                'usergroups' => fn_get_usergroups('F', Registry::get('settings.Appearance.backend_default_language')),
                'usergroup_id' => $_REQUEST['usergroup_id']
            ),
            'tpl' => 'profiles/usergroup_request.tpl',
            'company_id' => $user_data['company_id'],
        ), 'A', Registry::get('settings.Appearance.backend_default_language'));
    }

    return array(CONTROLLER_STATUS_OK, "profiles.update");

} elseif ($mode == 'success_add') {

    if (empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "profiles.add");
    }

    Registry::get('view')->assign('mail_server', fn_get_mail_server(db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $auth['user_id'])));
    fn_add_breadcrumb(__('registration'));
    
} elseif ($mode == 'send_email_confirmation') {

    if (!empty($_REQUEST['user_id'])) {
        $email = db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $_REQUEST['user_id']);
        $user_data = fn_get_user_short_info($_REQUEST['user_id']);
        
        if (!empty($email) && !empty($user_data)) {
            $ekey = fn_generate_ekey($_REQUEST['user_id'], 'E', SECONDS_IN_DAY);
            Mailer::sendMail(array(
                'to' => $email,
                'from' => 'company_users_department',
                'data' => array(
                    'user_data' => $user_data,
                    'confirm_email' => true,
                    'ekey' => $ekey
                ),
                'tpl' => 'profiles/create_profile.tpl',
                'company_id' => $user_data['company_id']
            ), fn_check_user_type_admin_area($user_data['user_type']) ? 'A' : 'C', CART_LANGUAGE);
            
            fn_set_notification('N', __('information'), __('confirm_email_note'));
            $user_data['confirmation_sent'] = 'Y';
            $user_data['mail_server'] = fn_get_mail_server($email);
            Registry::get('view')->assign('user_data', $user_data);
            Registry::get('view')->display('views/profiles/components/profiles_account.tpl');
        }
    }
    
    exit;
} elseif ($mode == 'confirm_email') {

    if (!empty($_REQUEST['ekey'])) {
        $u_id = fn_get_object_by_ekey($_REQUEST['ekey'], 'E');

        if (!empty($u_id)) {
            $user_status = fn_login_user($u_id);

            if ($user_status == LOGIN_STATUS_OK) {
                db_query("UPDATE ?:users SET email_confirmed = 'Y' WHERE user_id = ?i", $u_id);
                fn_set_notification('N', __('information'), __('success_email_confirmation_text'));

                return array(CONTROLLER_STATUS_REDIRECT, "profiles.update");
            } else {
                fn_set_notification('E', __('error'), __('error_login_not_exists'));
                
                return array(CONTROLLER_STATUS_REDIRECT, "index.index");
            }
        } else {
            fn_set_notification('E', __('error'), __('text_resend_email_confirmation_link'));

            return array(CONTROLLER_STATUS_REDIRECT, "index.index");
        }
    } else {
        return array(CONTROLLER_STATUS_REDIRECT, "index.index");
    }
} elseif ($mode == 'decline_email') {

    if (!empty($_REQUEST['ekey'])) {
        $u_id = fn_get_object_by_ekey($_REQUEST['ekey'], 'E');
        $user_data = fn_get_user_info($u_id, false);

        if (!empty($u_id) && !empty($user_data) && $user_data['email_confirmed'] != 'Y') {
        
            fn_set_hook('pre_delete_user', $u_id);
            fn_set_hook('delete_user', $u_id, $user_data);

            db_query("DELETE FROM ?:users WHERE user_id = ?i", $u_id);
            db_query('DELETE FROM ?:profile_fields_data WHERE object_id = ?i AND object_type = ?s', $u_id, 'U');
            db_query('DELETE FROM ?:user_session_products WHERE user_id = ?i', $u_id);
            db_query('DELETE FROM ?:user_data WHERE user_id = ?i', $u_id);
            db_query('UPDATE ?:orders SET user_id = 0 WHERE user_id = ?i', $u_id);

            $profile_ids = db_get_fields('SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i', $u_id);
            foreach ($profile_ids as $profile_id) {
                fn_delete_user_profile($u_id, $profile_id, true);
            }

            if (!fn_allowed_for('ULTIMATE:FREE')) {
                db_query('DELETE FROM ?:usergroup_links WHERE user_id = ?i', $u_id);
            }

            fn_set_hook('post_delete_user', $u_id, $user_data, $result);
            
        } else {
            fn_set_notification('E', __('error'), __('text_ekey_not_valid'));

            return array(CONTROLLER_STATUS_REDIRECT, "index.index");
        }
    } else {
        return array(CONTROLLER_STATUS_REDIRECT, "index.index");
    }
}

/**
 * Requests usergroup for customer
 *
 * @param int $user_id User identifier
 * @param int $usergroup_id Usergroup identifier
 * @param string $type Type of request (join|cancel)
 * @return bool True if request successfuly sent, false otherwise
 */
function fn_request_usergroup($user_id, $usergroup_id, $type)
{
    $success = false;
    if (!empty($user_id)) {
        $_data = array(
            'user_id' => $user_id,
            'usergroup_id' => $usergroup_id,
        );

        if ($type == 'cancel') {
            $_data['status'] = 'F';

        } elseif ($type == 'join') {
            $_data['status'] = 'P';
            $success = true;
        }

        if (!empty($_data['status'])) {
            db_query("REPLACE INTO ?:usergroup_links SET ?u", $_data);
        }
    }

    return $success;
}
