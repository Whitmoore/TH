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

    return;
}

if ($mode == 'update' || $mode == 'manage') {

    $processors = Registry::get('view')->getTemplateVars('payment_processors');

    if (!empty($processors)) {
        $rus_payments = array();
        foreach (fn_get_schema('rus_payments', 'processors') as $rus_payment) {
            $rus_payments[$rus_payment['processor']] = $rus_payment;
        }

        foreach ($processors as &$processor) {
            $processor['position'] = 'default_' . $processor['processor'];
            if (!empty($rus_payments[$processor['processor']])) {
                $processor['russian'] = 'Y';
                if (isset($rus_payments[$processor['processor']]['position'])) {
                    $processor['position'] = 'a_' . $rus_payments[$processor['processor']]['position'];
                }
            }
        }
        $processors = fn_sort_array_by_key($processors, 'position');

        Registry::get('view')->assign('payment_processors', $processors);
    }

} elseif ($mode == 'yandex_get_md5_password') {

    $md5 = md5(TIME . $_REQUEST['md5_shoppassword']);
    $md5 = substr($md5, 0, 20);

    Registry::get('view')->assign('ya_md5', $md5);
    Registry::get('view')->display('views/payments/components/cc_processors/yandex_money.tpl');

    exit;
}

