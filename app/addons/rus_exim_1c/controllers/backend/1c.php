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

list($dir_c, $dir_c_url) = fn_get_dir_c(Registry::get('runtime.forced_company_id'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';
    
    if ($mode == 'run_import') {
        if (!empty($_REQUEST['orders_files'])) {
            foreach ($_REQUEST['orders_files'] as $filename) {
                $xml = @simplexml_load_file($dir_c . 'orders/' . $filename);
                if (isset($xml->Документ)) {
                    if (fn_exim_1c_import_orders($xml) === true) {
                        fn_set_notification('N', __('notice'), __('text_exim_data_imported_clear'));
                    } else {
                        fn_set_notification('W', __('warning'), __('error_exim_no_data_exported'));
                    }
                } else {
                    fn_set_notification('W', __('warning'), __('error_exim_1c_incorrect_file'));
                }
            }
        }
        $suffix = '.orders_manage';
        die;
    }

    if ($mode == 'upload') {
        $xml_orders = fn_filter_uploaded_data('xml_orders');

        if (!empty($xml_orders)) {
            $xml_orders = array_shift($xml_orders);
            fn_mkdir($dir_c . 'orders/');
            if (fn_copy($xml_orders['path'], $dir_c . 'orders/' . $xml_orders['name'])) {
                fn_set_notification('N', __('notice'), __('done'));
            } else {
                fn_set_notification('W', __('warning'), __('cant_create_file'));
            }
        } else {
            fn_set_notification('W', __('warning'), __('cant_upload_file'));
        }
        $suffix = '.orders_manage';
    }

    if ($mode == 'delete_file') {
        if (!empty($_REQUEST['orders_files'])) {
            foreach ($_REQUEST['orders_files'] as $file) {
                if (fn_rm($dir_c . 'orders/' . $file)) {
                    fn_set_notification('N', __('notice'), __('done'));
                }
            }
        }
        $suffix = '.orders_manage';
    }
    
    if ($mode == 'save_prices') {
        
        if (Registry::get('addons.rus_exim_1c.exim_1c_export_add_prices') == 'Y') {
            $prices = $_REQUEST['prices_1c'];
            $base_prace_empty = false;
            if (!empty($_REQUEST['list_price_1c'])) {
                $_list_prices = explode(',', $_REQUEST['list_price_1c']);
                $id = 0;
                $list_prices = array();
                foreach($_list_prices as $key => $_list_price) {
                    $list_prices[$id]['price_1c'] = trim($_list_price); 
                    $list_prices[$id]['usergroup_id'] = -1;
                    $list_prices[$id]['type'] = 'list';
                    $id ++;
                }        
                $prices = array_merge($list_prices, $prices);
            }
            
            if (!empty($_REQUEST['base_price_1c'])) {
                $_base_prices = explode(',', $_REQUEST['base_price_1c']);
                $id = 0;
                $base_prices = array();
                foreach($_base_prices as $key => $_base_price) {
                    $base_prices[$id]['price_1c'] = trim($_base_price); 
                    $base_prices[$id]['usergroup_id'] = 0;
                    $base_prices[$id]['type'] = 'base';
                    $id ++;
                }
                $prices = array_merge($base_prices, $prices);
                db_query("DELETE FROM ?:rus_exim_1c_prices");
                foreach ($prices as $price) {
                    if (!empty($price['price_1c'])) {
                        db_query("INSERT INTO ?:rus_exim_1c_prices ?e", $price);
                    }
                }
            } else {
                fn_set_notification('W', __('warning'), __('base_price_empty'));
            }
        }
        
        if (Registry::get('addons.rus_exim_1c.exim_1c_add_tax') == 'Y') {
        fn_print_r($_REQUEST);
            $taxes_1c = $_REQUEST['taxes_1c'];
            db_query("DELETE FROM ?:rus_exim_1c_taxes");
            foreach ($taxes_1c as $tax_1c) {
                if (!empty($tax_1c['tax_1c'])) {
                    db_query("INSERT INTO ?:rus_exim_1c_taxes ?e", $tax_1c);
                }
            }
        }
        
        $suffix = '.prices';
    }
    
    return array(CONTROLLER_STATUS_OK, "1c$suffix");
   
}

if ($mode == 'orders_manage') {
    $files = fn_get_dir_contents($dir_c . 'orders', false, true, 'xml');
    sort($files, SORT_STRING);
    $orders_files = array();
    foreach ($files as $file) {
        $orders_files[$file]['size'] = filesize($dir_c . 'orders/' . $file);
        $orders_files[$file]['type'] = 'xml';
    }
    Registry::get('view')->assign('orders_files', $orders_files);

} elseif ($mode == 'delete_file') {
    if (!empty($_REQUEST['file'])) {
        if (fn_rm($dir_c . 'orders/' . $_REQUEST['file'])) {
            fn_set_notification('N', __('notice'), __('done'));
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT, "1c.orders_manage");

} elseif ($mode == 'get_file' && !empty($_REQUEST['file'])) {
    fn_get_file($dir_c . 'orders/' . fn_basename($_REQUEST['file']));
    
} elseif ($mode == 'prices') {
    if (Registry::get('addons.rus_exim_1c.exim_1c_add_tax') == 'Y') {
        $taxes = fn_get_taxes();
        $taxes_data = db_get_array("SELECT * FROM ?:rus_exim_1c_taxes");
        Registry::get('view')->assign('taxes_data', $taxes_data);
        Registry::get('view')->assign('taxes', $taxes);
    } 
    if (Registry::get('addons.rus_exim_1c.exim_1c_export_add_prices') == 'Y') {
        $prices_data = db_get_array("SELECT * FROM ?:rus_exim_1c_prices");
        $_prices = $prices_data;
        $_base_price_1c = array();
        $_list_price_1c = array();
        $base_price_1c = '';
        $list_price_1c = '';
        if (!empty($_prices)) {
            foreach ($_prices as $key => $price) {
                if ($price['usergroup_id'] == 0) {
                    $_base_price_1c[] = $price['price_1c'];
                    unset($prices_data[$key]);
                }
            }
        }
        $_prices = $prices_data;
        if (!empty($_prices)) {
            foreach ($_prices as $key => $price) {
                if ($price['usergroup_id'] == -1) {
                    $_list_price_1c[] = $price['price_1c'];
                    unset($prices_data[$key]);
                }    
            }
        }
        if (count($_base_price_1c) == 1) {
            $base_price_1c = $_base_price_1c[0];
        } elseif (count($_base_price_1c) > 1) {
            $base_price_1c = implode(',', $_base_price_1c);
        }
        if (count($_list_price_1c) == 1) {
            $list_price_1c = $_list_price_1c[0];
        } elseif (count($_list_price_1c) > 1) {
            $list_price_1c = implode(',', $_list_price_1c);
        }
        Registry::get('view')->assign('list_price_1c', $list_price_1c);
        Registry::get('view')->assign('base_price_1c', $base_price_1c);
        Registry::get('view')->assign('prices_data', $prices_data);

        if (Registry::get('addons.rus_exim_1c.exim_1c_export_check_prices') == 'Y') {    
            $result = array();
            if (file_exists($dir_c . 'offers.xml')) {
                $xml = @simplexml_load_file($dir_c . 'offers.xml');
                if (isset($xml->ПакетПредложений)) {
                    $result = fn_exim_1c_check_prices($xml->ПакетПредложений);
                }
            } else {
                fn_set_notification('W', __('warning'), __('offers_not_found'));
            } 
            Registry::get('view')->assign('resul_test', $result);
        }
    }
}
