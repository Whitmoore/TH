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
//GTR

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;
//
// Defined variables
//

define('FILE_LIMIT', 1024 * 1024 * 50);
define('WRONG_KEY_1C', 'WRONG KEY');
define('EMPTY_USER_1C', 'NO USER');
define('COOKIE_1C', 'c-conn-import');
