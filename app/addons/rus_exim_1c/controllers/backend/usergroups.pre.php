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

// rus_build_exim_1C dbazhenov

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['usergroup_ids'])) {
            fn_exim_1c_delete_usergroups($_REQUEST['usergroup_ids']);
        }
    }
}

if ($mode == 'delete') {
    fn_exim_1c_delete_usergroups((array) $_REQUEST['usergroup_id']);
}

function fn_exim_1c_delete_usergroups($usergroup_ids)
{
    db_query("DELETE FROM ?:rus_exim_1c_prices WHERE usergroup_id IN (?n)", $usergroup_ids);
}
