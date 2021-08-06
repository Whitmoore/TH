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

$_GET['allow_initialization'] = true;

define('AREA', 'A');
require './../../../init.php';

fn_load_addon('rus_exim_1c');

if (Registry::get('addons.rus_exim_1c.status') != 'A') {
    fn_echo('ADDON DISABLED');
    exit;
}

if (!empty($_SERVER['PHP_AUTH_USER'])) {
    $_data['user_login'] = $_SERVER['PHP_AUTH_USER'];
} else {
    fn_exim_1c_auth_error(EMPTY_USER_1C);
    exit;
}

$_auth_1c = array();
list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($_data, $_auth_1c);

if (empty($_SERVER['PHP_AUTH_USER']) || !($user_login == $_SERVER['PHP_AUTH_USER'] && $user_data['password'] == fn_generate_salted_password($_SERVER['PHP_AUTH_PW'], $salt))) {
    fn_exim_1c_auth_error(WRONG_KEY_1C);
    exit;
}

if ((!fn_rus_exim_1c_allowed_access($user_data)) && ($user_data['is_root'] != 'Y')) {
    fn_echo('ACCESS DENIED');
    exit;
}

$company_id = 0;
if (PRODUCT_EDITION == 'ULTIMATE') {
    if (Registry::get('runtime.simple_ultimate')) {
        $company_id = Registry::get('runtime.forced_company_id');
    } else {
        if ($user_data['company_id'] == 0) {
            fn_echo('SHOP IS NOT SIMPLE');
            exit;
        } else {
            $company_id = $user_data['company_id'];
            Registry::set('runtime.company_id', $company_id);
		}
    }
} elseif ($user_data['user_type'] == 'V') {
    if ($user_data['company_id'] == 0) {
        fn_echo('SHOP IS NOT SIMPLE');
        exit;
    } else {
        $company_id = $user_data['company_id'];
        Registry::set('runtime.company_id', $company_id);
    }
} else {
    Registry::set('runtime.company_id', $company_id);
}

list($dir_c, $dir_c_url) = fn_get_dir_c($company_id);

$cookie = COOKIE_1C;
$cookie_id = uniqid();
$file_limit = FILE_LIMIT;
$type = $_REQUEST['type'];
$mode = $_REQUEST['mode'];

$filename = (!empty($_REQUEST['filename'])) ? fn_basename($_REQUEST['filename']) : '';

if ($_REQUEST['type'] == 'test') {
    sleep($_REQUEST['time'] * 60);
    fn_echo('success');
    exit;
}

if ($type == 'catalog') {
    if ($mode == 'checkauth') {
        fn_exim_1c_checkauth($cookie, $cookie_id);
    } elseif ($mode == 'init') {
        fn_echo("zip=no\n");
        fn_echo("file_limit=$file_limit\n");
        fn_exim_1c_clear_1c_dir($dir_c);
    } elseif ($mode == 'file') {
        if (fn_exim_1c_get_external_file($dir_c, $filename) === false) {
            fn_echo("failure");
            exit;
        }
        fn_echo("success\n");
    } elseif ($mode == 'import') {
        $fileinfo = pathinfo($filename);
        $xml = @simplexml_load_file($dir_c . $filename);
        if ($xml === false) {
            fn_echo("failure");
            exit;
        }
        if ($fileinfo['filename'] == 'import') {
            if (Registry::get('addons.rus_exim_1c.exim_1c_export_check_prices') != 'Y') {
                if (isset($xml->Классификатор)) {              
                    fn_exim_1c_import_categories($xml->Классификатор, 0, $user_data['user_type'], $company_id);
                    fn_exim_1c_collect_features($xml->Классификатор, $company_id);
                }
                if (isset($xml->Каталог)) {
                    fn_exim_1c_import_products($xml->Каталог, $user_data, $company_id);
                }
            }
        } elseif ($fileinfo['filename'] == 'offers') {
            if (isset($xml->ПакетПредложений)) {
                if (Registry::get('addons.rus_exim_1c.exim_1c_export_check_prices') != 'Y') {
                    $changes = strval($xml->ПакетПредложений->attributes()->СодержитТолькоИзменения);
                    fn_exim_1c_import_offers($xml->ПакетПредложений, $changes, $company_id);
                }
            }
        } elseif ($fileinfo['filename'] == 'images') {
            fn_exim_1c_import_product_image_from_ftp($xml, $company_id);
        }
        fn_echo("success\n");
    } elseif ($mode == 'test_connection') {
        fn_echo("success\n");   
    } elseif ($mode == 'get_dir') {
        if (!is_dir($dir_c)) {
            fn_mkdir($dir_c);
        }
        fn_echo("success\n");
        fn_echo(fn_get_rel_dir($dir_c));  
    }

} elseif (($type == 'sale') && ($user_data['user_type'] != 'V') && (Registry::get('addons.rus_exim_1c.exim_1c_export_check_prices') != 'Y')) {
    if ($mode == 'checkauth') {
        fn_exim_1c_checkauth($cookie, $cookie_id);
    } elseif ($mode == 'init') {
        fn_echo("zip=no\n");
        fn_echo("file_limit=$file_limit\n");

    //export 1C orders to CS-Cart
    } elseif ($mode == 'file') {
        if (fn_exim_1c_get_external_file($dir_c, $filename) === false) {
            fn_echo("failure");
            exit;
        }
        fn_echo("success\n");
        $xml = @simplexml_load_file($dir_c . $filename);
        if (isset($xml->Документ)) {
            //[requires rework]
            //fn_exim_1c_import_orders($xml, $company_id);
        }
    //export CS-Cart orders to 1C
    } elseif ($mode == 'query') {
        fn_exim_1c_export_orders($company_id);
    } elseif ($mode == 'success') {
        fn_echo("success");
    }
}
