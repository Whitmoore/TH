<?php
/***************************************************************************
*                                                                          *
*   (c) 2020 PaulDreda    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

namespace Tygh\Sync;

class Sync
{
    private static $sync = null;

    public static function Synchronize($agent_id)
    {
        $success = false;
        $results = array();
        if ($agent_id == DRIADA_WAREHOUSE_ID) {
            $_sync_class = '\\Tygh\\Sync\\Agents\\Driada';
        }

        if (!empty($_sync_class) && class_exists($_sync_class)) {
            self::$sync = new $_sync_class();
            list($success, $results) = self::$sync->Synchronize();
        }

        return array($success, $results);
    }
}
