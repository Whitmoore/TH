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
use Tygh\Shippings\RusSdek;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'regenerate_cities') {

        $file = fn_filter_uploaded_data('csv_file');
        if (!empty($file) && file_exists($file[0]['path'])) {

            $f = false;
            if ($file[0]['path'] !== false) {
                $f = fopen($file[0]['path'], 'rb');
            }

            if ($f) {
                $max_line_size = 65536; // 64 Кб
                $result = array();

                $delimiter = ',';
                $headers = array();
                $state_codes = array();
                $errors = array();
                db_query("TRUNCATE ?:rus_cities_sdek");
                db_query("TRUNCATE ?:rus_city_sdek_descriptions");
                while (($data = fn_fgetcsv($f, $max_line_size, $delimiter)) !== false) {
                    if (empty($headers)) {
                        $headers = $data;
                    } else {
                        if (!empty($data[0]) && !empty($data[2]) && !empty($data[3])) {
                            if (!in_array($data[3], array_keys($state_codes))) {
                                $data[3] = str_replace(array('Саха респ. (Якутия)', 'Удмуртия', 'Чувашия'), array('Республика Саха (Якутия)', 'Удмуртская Республика', 'Чувашская Республика'), $data[3]);
                                $data[3] = trim(str_replace(array('АР', 'обл.', 'респ.', 'авт. округ', 'авт.'), array('', '', '', '', ''), $data[3]));
                                $states = db_get_array("SELECT a.code, b.state FROM ?:states AS a LEFT JOIN ?:state_descriptions AS b ON a.state_id = b.state_id AND b.lang_code = 'ru' WHERE b.state LIKE ?s AND a.country_code = 'RU'", '%' . $data[3] . '%');
                                if ($data[3] == 'Саха  (Якутия)') {
                                    fn_print_die(db_quote("SELECT a.code, b.state FROM ?:states AS a LEFT JOIN ?:state_descriptions AS b ON a.state_id = b.state_id AND b.lang_code = 'ru' WHERE b.state LIKE ?s AND a.country_code = 'RU'", '%' . $data[3] . '%'));
                                }
                                if (count($states) == 1) {
                                    $state_codes[$data[3]] = $states[0]['code'];
                                } elseif (count($states) > 1) {
                                    $match = 0;
                                    $choice = '';
                                    foreach ($states as $j => $state) {
                                        $prc = strlen($data[3])/strlen($state['state']);
                                        if ($prc > $match) {
                                            $match = $prc;
                                            $choice = $state['code'];
                                        }
                                    }
                                    if ($choice != '') {
                                        $state_codes[$data[3]] = $choice;
                                    }
                                }
                            }
                            if (!empty($state_codes[$data[3]])) {
                                $insert = array(
                                    'country_code' => 'RU',
                                    'state_code' => $state_codes[$data[3]],
                                    'city_code' => $data[0],
                                    'lang_code' => 'ru',
                                    'city' => $data[2]
                                );
                                $insert['city_id'] = db_query("REPLACE INTO ?:rus_cities_sdek ?e", $insert);
                                db_query("REPLACE INTO ?:rus_city_sdek_descriptions ?e", $insert);
                            } else {
                                $errors[] = $data;
                            }
                        } else {
                            $errors[] = $data;
                        }
                    }
                }
                if (!empty($errors)) {
                    //fn_print_r($errors);
                    fn_set_notification('W', __('warning'), __('error_generate_cities'));
                } else {
                    fn_set_notification('N', __('notice'), __('success_generate_cities'));
                }
            }
        }
        exit;
    }
}

