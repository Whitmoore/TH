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

namespace Tygh\CmpUpdater;

class CmpUpdater
{
    private static $updater = null;

    public static function call($competitor_id, $func, $params = array())
    {
        $success = false;
        $results = array();
        $_parse_class = '\\Tygh\\CmpUpdater\\Competitors\\Cmp' . $competitor_id;

        if (!empty($_parse_class) && class_exists($_parse_class)) {
            self::$updater = new $_parse_class();
            list($success, $results) = call_user_func_array(array(self::$updater, $func), $params);
        }

        return array($success, $results);
    }

}
