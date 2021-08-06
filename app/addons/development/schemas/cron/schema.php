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

// The frequency parameters are used for the date() php function
return array(
    'F' => array(
        'frequency' => array(
            'H' => '02'
        ),
        'function' => 'fn_generate_features_cash',
        'name' => 'cache_features'
    ),
    'R' => array(
        'frequency' => array(
            'N' => '2',
            'H' => '02',
        ),
        'function' => 'fn_update_rankings',
        'name' => 'update_rankings'
    ),
    'E' => array(
        'frequency' => array(
            'H' => '02'
        ),
        'function' => 'fn_check_expired_points',
        'name' => 'check_expired_points'
    ),
    'P' => array(
        'frequency' => array(
            'H' => '02'
        ),
        'function' => 'fn_update_rub_rate',
        'name' => 'update_exchange_rates'
    ),
    'M' => array(
        'frequency' => array(
            'H' => '02'
        ),
        'function' => 'fn_get_generate_categories_menu_subitems',
        'name' => 'generate_menu'
    ),
    'S' => array(
        'frequency' => array(
            'H' => '10'
        ),
        'function' => 'fn_check_delivery_statuses',
        'name' => 'check_delivery_statuses'
    ),
    'T' => array(
        'frequency' => array(
            'i' => '00'
        ),
        'function' => 'fn_check_sms',
        'name' => 'check_sms_statuses'
    ),
    'Y' => array(
        'frequency' => array(
            'H' => '04,16'
        ),
        'function' => 'fn_synchronize_agents',
        'name' => 'catalog_synchronization'
    ),
    'D' => array(
        'frequency' => array(
            'H' => '04,07,10,13,16,19,22,01'
        ),
        'function' => 'fn_update_xml_feed',
        'name' => 'update_xml_feed'
    ),
    'L' => array(
        'frequency' => array(
            'm' => '12',
            'd' => '01',
            'H' => '02'
        ),
        'function' => 'fn_download_calendar',
        'name' => 'download_calendar'
    ),
    'B' => array(
        'frequency' => array(
            'H' => '00'
        ),
        'function' => 'fn_archieve_order_data',
        'name' => 'archieve_order_data'
    ),
    'O' => array(
        'frequency' => array(
            'd' => '01',
            'H' => '02'
        ),
        'function' => 'fn_update_sdek_cities',
        'name' => 'update_sdek_cities'
    ),
);
