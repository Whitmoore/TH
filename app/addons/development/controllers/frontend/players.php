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

}

if ($mode == 'view') {

    $player_data = !empty($_REQUEST['player_id']) ? fn_get_player_data($_REQUEST['player_id']) : array();

    if (empty($player_data) || empty($player_data['status']) || (!empty($player_data['status']) && $player_data['status'] == 'D')) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    fn_add_breadcrumb(__('professionals') . ' ' . (($player_data['gender'] == 'M') ? __("atp") : __("wta") ), 'players.list');
    fn_add_breadcrumb($player_data['player']);

    if (!empty($player_data['bg_image'])) {
        Registry::get('view')->assign('image_title', $player_data['bg_image']);
    }
    Registry::get('view')->assign('player_data', $player_data);
    Registry::get('view')->assign('seo_canonical', array(
        'current' => fn_url('players.view?player_id=' . $_REQUEST['player_id'])
    ));

} elseif ($mode == 'list') {

    fn_add_breadcrumb(__('players_atp_wta'));

    $params = $_REQUEST;
    
    $params['gender'] = 'M';
    list($atp_players,) = fn_get_players($params);
    Registry::get('view')->assign('atp_players', $atp_players);

    $params['gender'] = 'F';
    list($wta_players,) = fn_get_players($params);
    Registry::get('view')->assign('wta_players', $wta_players);
    
    $meta_players = '';
    if (!empty($atp_players)) {
        $num = 0;
        foreach ($atp_players as $i => $player) {
            if ($num < 5) {
                $meta_players .= ($meta_players == '') ? $player['player'] : ', ' . $player['player'];
                $num++;
            } else {
                break;
            }
        }
    }
    if (!empty($wta_players)) {
        $num = 0;
        foreach ($wta_players as $i => $player) {
            if ($num < 5) {
                $meta_players .= ($meta_players == '') ? $player['player'] : ', ' . $player['player'];
                $num++;
            } else {
                break;
            }
        }
    }
    Registry::get('view')->assign('meta_players', $meta_players);
}
