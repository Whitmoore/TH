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
use Tygh\Gm\Gml;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'view') {

    $user_data = fn_google_auth();

    $company_id = Registry::get('runtime.company_id');

    $options = Registry::get('addons.google_merchant');
    $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 0;

    $lang_code = DESCR_SL;
    if (Registry::isExist('languages.ru')) {
        $lang_code = 'ru';
    }

    $gml = new Gml($company_id, $options, $lang_code, $page, isset($_REQUEST['debug']));

    $gml->get();

    exit;

}
