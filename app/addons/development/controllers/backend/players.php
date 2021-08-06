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

$_REQUEST['player_id'] = empty($_REQUEST['player_id']) ? 0 : $_REQUEST['player_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'player_data',
        'players_data'
    );

    //
    // Create/update player
    //
    if ($mode == 'update') {

        $player_id = fn_update_player($_REQUEST['player_data'], $_REQUEST['player_id']);

        if (!empty($player_id)) {
            fn_attach_image_pairs('player_main', 'player', $player_id);
            fn_attach_image_pairs('player_bg', 'player', $player_id);

            $suffix = ".update?player_id=$player_id" . (!empty($_REQUEST['player_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['player_data']['block_id'] : "");
        } else {
            $suffix = '.manage';
        }
    }

    //
    // Processing deleting of multiple player elements
    //
    if ($mode == 'm_delete') {

        if (isset($_REQUEST['player_ids'])) {
            foreach ($_REQUEST['player_ids'] as $v) {
                if (fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('ULTIMATE') && fn_check_company_id('players', 'player_id', $v))) {
                    fn_delete_player($v);
                }
            }
        }

        unset($_SESSION['player_ids']);

        fn_set_notification('N', __('notice'), __('text_players_have_been_deleted'));
        $suffix = ".manage";
    }

    if ($mode == 'm_update') {
    
        if (isset($_REQUEST['player_ids'])) {
            list($result, $errors) = fn_update_rankings($_REQUEST['player_ids']);
            if ($result) {
                fn_set_notification('N', __('notice'), __('text_player_info_update_succeeded'));
            } else {
                fn_set_notification('W', __('warning'), __('text_player_info_update_failed'));
            }
        }

        unset($_SESSION['player_ids']);
        $suffix = ".manage";
    }
    
    return array(CONTROLLER_STATUS_OK, "players$suffix");
}

//
// 'Add new player' page
//
if ($mode == 'add') {

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'products' => array (
            'title' => __('products'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    ));
    // [/Page sections]

//
// 'player update' page
//
} elseif ($mode == 'update') {

    $player_id = $_REQUEST['player_id'];
    // Get current player data
    $player_data = fn_get_player_data($player_id);

    if (empty($player_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    // [Page sections]
    $tabs = array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'products' => array (
            'title' => __('products'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    );

    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]
    Registry::get('view')->assign('player_data', $player_data);

}
//
// Delete player
//
elseif ($mode == 'delete') {

    if (!empty($_REQUEST['player_id'])) {
        fn_delete_player($_REQUEST['player_id']);
    }

    fn_set_notification('N', __('notice'), __('text_player_has_been_deleted'));

    return array(CONTROLLER_STATUS_REDIRECT, "players.manage");

//
// 'Management' page
//
} elseif ($mode == 'manage' || $mode == 'picker') {

    $params = $_REQUEST;
    list($players, $search) = fn_get_players($params);

    Registry::get('view')->assign('players', $players);
    Registry::get('view')->assign('search', $search);
    
} elseif ($mode == 'update_info') {
    list($result, $errors) = fn_update_rankings(array($_REQUEST['player_id']));
    if ($result) {
        fn_set_notification('N', __('notice'), __('text_player_info_update_succeeded'));
    } else {
        fn_set_notification('W', __('warning'), __('text_player_info_update_failed'));
    }
    exit;
}

//
// Categories picker
//
if ($mode == 'picker') {
    Registry::get('view')->display('addons/development/pickers/players/picker_contents.tpl');
    exit;
}
