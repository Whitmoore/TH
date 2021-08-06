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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($mode == 'update') {
        if (!empty($_REQUEST['subscribe_to_store_newsletters'])) {
            if ($_REQUEST['subscribe_to_store_newsletters'] == 'Y') {
                fn_add_subscriber($_REQUEST['user_data']['email']);
            } else {
                $subscriber = db_get_row("SELECT * FROM ?:subscribers WHERE email = ?s", $_REQUEST['user_data']['email']);
                if (!empty($subscriber)) {
                    fn_delete_subscribers($subscriber['subscriber_id'], false);
                }
            }
        }
    }

    return;
}

if ($mode == 'update') {
    $user_data = Registry::get('view')->gettemplatevars('user_data');
    $subscriber = db_get_row("SELECT * FROM ?:subscribers WHERE email = ?s", $user_data['email']);
}

if ($mode == 'add' || $mode == 'update') {
    $params = array(
        'registration' => true
    );
    if (!empty($subscriber)) {
        $params['subscribed'] = $subscriber['subscriber_id'];
    }
    list($page_mailing_lists) = fn_get_mailing_lists($params);
    Registry::get('view')->assign('page_mailing_lists', $page_mailing_lists);
}