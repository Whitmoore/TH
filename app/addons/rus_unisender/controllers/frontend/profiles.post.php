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

// rus_build_unisender

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($mode == 'update') {
        if (Registry::get('settings.rus_unisender.main.unisender_show_on_registration') == 'Y') {
            $email = $_REQUEST['user_data']['email'];
            $subscriber_id = fn_unisender_get_subscriber_id($email);

            if (!empty($_REQUEST['unisender_lists']) && !fn_is_empty($_REQUEST['unisender_lists'])) {
                if (empty($subscriber_id)) {
                    $subscriber_id = fn_unisender_add_subscriber($email);
                }
                fn_unisender_subscribe($_REQUEST['user_data'], reset($_REQUEST['unisender_lists']), true);

            } else {
                if (!empty($subscriber_id)) {
                    fn_unisender_unsubscribe($subscriber_id);
                }
            }
        }
    }
}

if ($mode == 'add' || $mode == 'update') {
    if (Registry::get('settings.rus_unisender.main.unisender_show_on_registration') == 'Y') {
        Registry::get('view')->assign('unisender_page_mailing_lists', fn_unisender_get_enabled_lists());
    }
}

if ($mode == 'update') {
    if (Registry::get('settings.rus_unisender.main.unisender_show_on_registration') == 'Y') {
        $email = db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $_SESSION['auth']['user_id']);
        $mailing_lists = fn_unisender_get_user_lists($email);
        Registry::get('view')->assign('unisender_user_mailing_lists', $mailing_lists);
    }
}
