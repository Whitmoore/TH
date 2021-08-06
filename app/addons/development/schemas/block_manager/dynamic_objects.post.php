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

$scheme['players'] = array (
    'admin_dispatch' => 'players.update',
    'customer_dispatch' => 'players.view',
    'key' => 'player_id',
    'picker' => 'addons/development/pickers/players/picker.tpl',
    'picker_params' => array (
        'type' => 'links',
    ),
);

return $scheme;
