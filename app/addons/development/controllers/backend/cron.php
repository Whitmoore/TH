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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';

    if ($mode == 'm_delete') {
        if (isset($_REQUEST['log_ids'])) {
            db_query("DELETE FROM ?:cron_logs WHERE log_id IN (?n)", $_REQUEST['log_ids']);
        }
        $suffix = ".manage";
    }

    return array(CONTROLLER_STATUS_OK, "cron$suffix");
}

if ($mode == 'manage') {

    list($logs, $search) = fn_get_cron_logs($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('logs', $logs);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('log_types', fn_get_cron_log_types());

} elseif ($mode == 'view') {

    $log = db_get_row("SELECT * FROM ?:cron_logs WHERE log_id = ?i", $_REQUEST['log_id']);
    $log['results'] = !empty($log['results']) ? unserialize($log['results']) : array();
    Registry::get('view')->assign('log_types', fn_get_cron_log_types());
    Registry::get('view')->assign('log', $log);

} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['log_id'])) {
        db_query("DELETE FROM ?:cron_logs WHERE log_id = ?i", $_REQUEST['log_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "cron.manage");

} elseif ($mode == 'run') {

    $scheme = fn_get_schema('cron', 'schema');

    if (!empty($scheme[$_REQUEST['cron_type']])) {
        fn_run_cron_script($_REQUEST['cron_type'], $scheme[$_REQUEST['cron_type']]);
    }

    list($logs, $search) = fn_get_cron_logs($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('logs', $logs);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('log_types', fn_get_cron_log_types());
    Registry::get('view')->display('addons/development/views/cron/manage.tpl');
    exit;
}
