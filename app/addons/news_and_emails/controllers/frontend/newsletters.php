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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

Use Tygh\Registry;
use Tygh\Mailer;

// Add email to maillist
if ($mode == 'add_subscriber') {

    $confirmation_text = '';
    if (empty($_REQUEST['subscribe_email']) || fn_validate_email($_REQUEST['subscribe_email']) == false) {
        fn_set_notification('E', __('error'), __('error_invalid_emails', array(
            '[emails]' => $_REQUEST['subscribe_email']
        )));
    } else {
        $confirmation_text = fn_add_subscriber($_REQUEST['subscribe_email'], 'C', true);
    }
    
    Registry::get('view')->assign('confirmation_text', $confirmation_text);
    Registry::get('view')->display('addons/news_and_emails/blocks/static_templates/subscribe.tpl');
    
    exit;
} elseif ($mode == 'unsubscribe') {
    
    if (!empty($_REQUEST['subscribe_data']) && !empty($_REQUEST['subscribe_data']['list_id']) && !empty($_REQUEST['subscribe_data']['s_id'])) {
        fn_update_subscriptions($_REQUEST['subscribe_data']['s_id'], array($_REQUEST['subscribe_data']['list_id']), NULL, fn_get_notification_rules(true));

        Registry::get('view')->assign('subscribed', true);
        Registry::get('view')->display('addons/news_and_emails/views/newsletters/unsubscribe.tpl');
    
        exit;
    } elseif (!empty($_REQUEST['unsubscribe_data']) && !empty($_REQUEST['unsubscribe_data']['key']) && !empty($_REQUEST['unsubscribe_data']['list_id']) && !empty($_REQUEST['unsubscribe_data']['s_id'])) {
        $email = db_get_field("SELECT email FROM ?:subscribers WHERE subscriber_id = ?i", $_REQUEST['unsubscribe_data']['s_id']);
        Registry::get('view')->assign('email', $email);
        $num = db_get_field("SELECT COUNT(*) FROM ?:user_mailing_lists WHERE unsubscribe_key = ?s AND list_id = ?i AND subscriber_id = ?i", $_REQUEST['unsubscribe_data']['key'], $_REQUEST['unsubscribe_data']['list_id'], $_REQUEST['unsubscribe_data']['s_id']);

        if (!empty($num)) {
            db_query("DELETE FROM ?:user_mailing_lists WHERE unsubscribe_key = ?s AND list_id = ?i AND subscriber_id = ?i", $_REQUEST['unsubscribe_data']['key'], $_REQUEST['unsubscribe_data']['list_id'], $_REQUEST['unsubscribe_data']['s_id']);
            if (!empty($_REQUEST['unsubscribe_data']['notes'])) {
                db_query("UPDATE ?:subscribers SET notes = ?s WHERE subscriber_id = ?i", $_REQUEST['unsubscribe_data']['notes'], $_REQUEST['unsubscribe_data']['s_id']);
            }
            
//             // if this subscription was the only one, we delete the subscriber as each subscruber
//             // must have at least one subscription
//             $left = db_get_field("SELECT COUNT(*) FROM ?:user_mailing_lists WHERE subscriber_id = ?i", $_REQUEST['s_id']);
//             if (empty($left)) {
//                 db_query("DELETE FROM ?:subscribers WHERE subscriber_id = ?i", $_REQUEST['s_id']);
//             }
// 
//             fn_set_notification('N', __('notice'), __('text_subscriber_removed'));
            Registry::get('view')->assign('unsubscribed', true);
            Registry::get('view')->assign('list_id', $_REQUEST['unsubscribe_data']['list_id']);
            Registry::get('view')->assign('s_id', $_REQUEST['unsubscribe_data']['s_id']);
        }
        Registry::get('view')->display('addons/news_and_emails/views/newsletters/unsubscribe.tpl');
    
        exit;
    } elseif (!empty($_REQUEST['key']) && !empty($_REQUEST['list_id']) && !empty($_REQUEST['s_id'])) {
        $email = db_get_field("SELECT email FROM ?:subscribers WHERE subscriber_id = ?i", $_REQUEST['s_id']);
        Registry::get('view')->assign('email', $email);
        $num = db_get_field("SELECT COUNT(*) FROM ?:user_mailing_lists WHERE unsubscribe_key = ?s AND list_id = ?i AND subscriber_id = ?i", $_REQUEST['key'], $_REQUEST['list_id'], $_REQUEST['s_id']);

        if (!empty($num)) {
            if (!empty($_REQUEST['one_step'])) {
                db_query("DELETE FROM ?:user_mailing_lists WHERE unsubscribe_key = ?s AND list_id = ?i AND subscriber_id = ?i", $_REQUEST['key'], $_REQUEST['list_id'], $_REQUEST['s_id']);
                exit;
            } else {
                Registry::get('view')->assign('subscriber_found', true);
                Registry::get('view')->assign('key',  $_REQUEST['key']);
                Registry::get('view')->assign('list_id', $_REQUEST['list_id']);
                Registry::get('view')->assign('s_id', $_REQUEST['s_id']);
            }
        }
    }

//     return array(CONTROLLER_STATUS_REDIRECT, fn_url());

} elseif ($mode == 'activate') {
    if (!empty($_REQUEST['key']) && !empty($_REQUEST['list_id']) && !empty($_REQUEST['s_id'])) {
        if (!empty($_REQUEST['list_id'])) {
            $num = db_get_field("SELECT COUNT(*) FROM ?:user_mailing_lists WHERE activation_key = ?s AND list_id = ?i AND subscriber_id = ?i", $_REQUEST['key'], $_REQUEST['list_id'], $_REQUEST['s_id']);

            if (!empty($num)) {
                db_query('UPDATE ?:user_mailing_lists SET ?u WHERE activation_key = ?s AND list_id = ?i AND subscriber_id = ?i', array('confirmed' => 1), $_REQUEST['key'], $_REQUEST['list_id'], $_REQUEST['s_id']);

                fn_set_notification('N', __('notice'), __('text_subscriber_activated'));
            }
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT, fn_url());

} elseif ($mode == 'track') {
    if (!empty($_REQUEST['link'])) {
        list($link_id, $newsletter_id, $campaign_id) = explode('-', $_REQUEST['link']);

        $_where = array(
            'link_id' => (int) $link_id,
            'newsletter_id' => (int) $newsletter_id,
            'campaign_id' => (int) $campaign_id
        );

        $link = db_get_row("SELECT * FROM ?:newsletter_links WHERE ?w", $_where);

        if (!empty($link)) {
            $link['clicks']++;
            db_query("UPDATE ?:newsletter_links SET clicks=?i WHERE ?w", $link['clicks'], $_where);
        }

        return array(CONTROLLER_STATUS_REDIRECT, $link['url'], true);
    }
} elseif ($mode == 'confirm_email') {
    if (!empty($_REQUEST['ekey'])) {
        $s_id = fn_get_object_by_ekey($_REQUEST['ekey'], 'S');

        if (!empty($s_id)) {
            db_query("UPDATE ?:subscribers SET status = 'C' WHERE subscriber_id = ?i", $s_id);
            fn_set_notification('N', __('information'), __('success_email_confirmation_text'));

            return array(CONTROLLER_STATUS_REDIRECT, "newsletters.confirm_email");
        } else {
            fn_set_notification('E', __('error'), __('text_resend_email_confirmation_link'));

            return array(CONTROLLER_STATUS_REDIRECT, "index.index");
        }
    } else {
        return array(CONTROLLER_STATUS_REDIRECT, "index.index");
    }
}
