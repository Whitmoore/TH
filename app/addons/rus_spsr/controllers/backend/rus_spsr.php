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

use Tygh\Shippings\SpsrFrame;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
}

if ($mode == 'regenerate_cities_cache') {

    $cities = SpsrFrame::getCities('');
    if (!empty($cities)) {
        $state_codes = array();
        $errors = array();
        db_query("TRUNCATE ?:rus_spsr_cities");
        db_query("TRUNCATE ?:rus_spsr_city_descriptions");
        foreach ($cities as $i => $city) {
            if (!in_array($city['Region_ID'], array_keys($state_codes))) {
                $city['RegionName'] = str_replace(array('Чувашия', 'Чечня', 'Ингушская респ.', 'Ханты-Мансийский авт. округ-Югра', 'Алтай респ.'), array('Чувашская Республика', 'Чеченская Республика', 'Республика Ингушетия', 'Ханты-Мансийский автономный округ — Югра', 'Республика Алтай'), $city['RegionName']);
                $city['RegionName'] = trim(str_replace(array('обл.', 'респ.', 'авт. округ', 'авт.'), array('', '', '', ''), $city['RegionName']));
                $states = db_get_array("SELECT a.code, b.state FROM ?:states AS a LEFT JOIN ?:state_descriptions AS b ON a.state_id = b.state_id AND b.lang_code = 'ru' WHERE b.state LIKE ?s AND a.country_code = 'RU'", '%' . $city['RegionName'] . '%');
                if (count($states) == 1) {
                    $state_codes[$city['Region_ID']] = $states[0]['code'];
                } elseif (count($states) > 1) {
                    $match = 0;
                    $choice = '';
                    foreach ($states as $j => $state) {
                        $prc = strlen($city['RegionName'])/strlen($state['state']);
                        if ($prc > $match) {
                            $match = $prc;
                            $choice = $state['code'];
                        }
                    }
                    if ($choice != '') {
                        $state_codes[$city['Region_ID']] = $choice;
                    }
                }
            }
            if (!empty($state_codes[$city['Region_ID']])) {
                $insert = array(
                    'country_code' => 'RU',
                    'state_code' => $state_codes[$city['Region_ID']],
                    'city_code' => $city['City_ID'],
                    'lang_code' => 'ru',
                    'city' => $city['CityName']
                );
                $insert['city_id'] = db_query("REPLACE INTO ?:rus_spsr_cities ?e", $insert);
                db_query("REPLACE INTO ?:rus_spsr_city_descriptions ?e", $insert);
            } else {
                $errors[] = $city;
            }
        }
        if (!empty($errors)) {
            //fn_print_r($errors);
            fn_set_notification('W', __('warning'), __('error_generate_cities'));
        } else {
            fn_set_notification('N', __('notice'), __('success_generate_cities'));
        }
    }
    exit;

}
