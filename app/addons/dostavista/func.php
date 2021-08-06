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

if ( !defined('AREA') ) { die('Access denied'); }

function fn_dostavista_install()
{
    db_query("REPLACE INTO ?:settings_objects (`object_id`, `edition_type`, `name`, `section_id`, `section_tab_id`, `type`, `value`, `position`, `is_global`, `handler`, `parent_id`) VALUES ('8372', 'ROOT', 'dostavista_enabled', '7', '0', 'C', 'Y', '150', 'Y', '', '0')");

    foreach (fn_get_translation_languages() as $lang_code => $v) {
        db_query("REPLACE INTO ?:settings_descriptions (`object_id`, `object_type`, `lang_code`, `value`, `tooltip`) VALUES ('8372', 'O', ?s, 'Включить Dostavista', '')", $lang_code);
    }

    $service = array(
        'service_id' => 599,
        'status' => 'A',
        'module' => 'dostavista',
        'code' => '1',
        'sp_file' => '',
        'description' => 'Dostavista',
    );

    db_query('REPLACE INTO ?:shipping_services ?e', $service);

    foreach (fn_get_translation_languages() as $lang_code => $v) {
        $service['lang_code'] = $lang_code;
        db_query('REPLACE INTO ?:shipping_service_descriptions ?e', $service);
    }
}

function fn_dostavista_uninstall()
{
    $dostavista_id = db_get_field('SELECT object_id FROM ?:settings_objects WHERE name = ?s', 'dostavista_enabled');

    db_query('DELETE FROM ?:settings_objects WHERE object_id = ?i', $dostavista_id);
    db_query('DELETE FROM ?:settings_descriptions WHERE object_id = ?i', $dostavista_id);

    $service_ids = db_get_fields('SELECT service_id FROM ?:shipping_services WHERE module = ?s', 'dostavista');
    db_query('DELETE FROM ?:shipping_services WHERE service_id IN (?a)', $service_ids);
    db_query('DELETE FROM ?:shipping_service_descriptions WHERE service_id IN (?a)', $service_ids);
}
