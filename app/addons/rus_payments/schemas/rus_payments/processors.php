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

$schema = array(
    'yandex_money' => array(
        'processor' => 'Yandex.Money',
        'processor_script' => 'yandex_money.php',
        'processor_template' => 'views/orders/components/payments/yandex_money.tpl',
        'admin_template' => 'yandex_money.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 1,
    ),
    'yandex_p2p' => array(
        'processor' => 'Yandex p2p',
        'processor_script' => 'yandex_p2p.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'yandex_p2p.tpl',
        'callback' => 'Y',
        'type' => 'P',
        'position' => 2,
    ),
    'sbrf' => array(
        'processor' => 'Cбербанк России',
        'processor_script' => '',
        'processor_template' => '',
        'admin_template' => 'sbrf_receipt.tpl',
        'callback' => 'N',
        'type' => 'P',
    ),
    'assist' => array(
        'processor' => 'Assist',
        'processor_script' => 'assist.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'assist.tpl',
        'callback' => 'N',
        'type' => 'P',
    ),
    'webmoney' => array(
        'processor' => 'WebMoney',
        'processor_script' => 'webmoney.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'webmoney.tpl',
        'callback' => 'N',
        'type' => 'P',
    ),
    'robokassa' => array(
        'processor' => 'Robokassa',
        'processor_script' => 'robokassa.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'robokassa.tpl',
        'callback' => 'N',
        'type' => 'P',
    ),
    'pay_at_home' => array(
        'processor' => 'Plati Doma',
        'processor_script' => 'pay_at_home.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'pay_at_home.tpl',
        'callback' => 'Y',
        'type' => 'P',
    ),
    'qiwi' => array(
        'processor' => 'Qiwi',
        'processor_script' => 'qiwi.php',
        'processor_template' => 'views/orders/components/payments/qiwi.tpl',
        'admin_template' => 'qiwi.tpl',
        'callback' => 'Y',
        'type' => 'P',
    ),
    'rbk' => array(
        'processor' => 'RBK Money',
        'processor_script' => 'rbk.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'rbk.tpl',
        'callback' => 'Y',
        'type' => 'P',
    ),
    'vsevcredit' => array(
        'processor' => 'Vsevcredit',
        'processor_script' => 'vsevcredit.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'vsevcredit.tpl',
        'callback' => 'Y',
        'type' => 'P',
    ),
    'paymaster' => array(
        'processor' => 'Paymaster',
        'processor_script' => 'paymaster.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'paymaster.tpl',
        'callback' => 'Y',
        'type' => 'P',
    ),
    'avangard' => array(
        'processor' => 'Avangard',
        'processor_script' => 'avangard.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'avangard.tpl',
        'callback' => 'Y',
        'type' => 'P',
    ),
    'payanyway' => array(
        'processor' => 'PayAnyWay',
        'processor_script' => 'payanyway.php',
        'processor_template' => 'views/orders/components/payments/payanyway.tpl',
        'admin_template' => 'payanyway.tpl',
        'callback' => 'Y',
        'type' => 'P',
    ),
    'payler' => array(
        'processor' => 'Payler',
        'processor_script' => 'payler.php',
        'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
        'admin_template' => 'payler.tpl',
        'callback' => 'N',
        'type' => 'P',
    ),
);

return $schema;
