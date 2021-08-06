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

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'anouncements_data'
    );

    //
    // Create/update anouncement
    //
    if ($mode == 'update') {
        if (!empty($_REQUEST['anouncements_data'])) {
            $groups = array();
            db_query("DELETE FROM ?:anouncements");
            foreach ($_REQUEST['anouncements_data'] as $k => $anc_data) {
                if ($anc_data['text'] != '') {
                    $ancs[] = array(
                        'anouncement_id' => $k,
                        'text' => $anc_data['text'],
                        'class' => $anc_data['class'],
                        'start_timestamp' => fn_parse_date($anc_data['start_timestamp']),
                        'end_timestamp' => fn_parse_date($anc_data['end_timestamp']),
                        'priority' => $anc_data['priority'],
                    );
                }
            }
            if (!empty($ancs)) {
                db_query("REPLACE INTO ?:anouncements ?m", $ancs);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, "anouncements.manage");
}

if ($mode == 'manage') {

    $anouncements = db_get_hash_array("SELECT * FROM ?:anouncements ORDER BY priority ASC", 'anouncement_id');
    Registry::get('view')->assign('anouncements', $anouncements);
    Registry::get('view')->assign('max_key', max(array_keys($anouncements)));
    
}