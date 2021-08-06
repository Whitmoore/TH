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
use Tygh\Settings;
use Tygh\Shippings\Shippings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_REQUEST['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : $_REQUEST['shipping_id'];

if ($mode == 'update') {
    $tabs = Registry::get('navigation.tabs');
    $tabs['payments'] = array (
        'title' => __('payment_methods'),
        'js' => true
    );
    $tabs = Registry::set('navigation.tabs', $tabs);
    $payments = fn_get_simple_payment_methods(false);
    Registry::get('view')->assign('payment_methods', $payments);
}