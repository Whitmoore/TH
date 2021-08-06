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

$racket_finder = & $_SESSION['racket_finder'];
$step = & $_SESSION['finder_step'];
global $rf_schema;
$rf_schema = fn_get_schema('racket_finder', 'questions');
Registry::get('view')->assign('schema', $rf_schema);

if ($mode == 'submit') {

    $racket_finder = $_REQUEST['racket_finder'];
    if ($_REQUEST['direction'] == 'reset') {
        $racket_finder = array();
        unset($_REQUEST['step']);
    }
    $step = fn_get_step($racket_finder, $_REQUEST['step'], $_REQUEST['direction']);
    if ($step == 'result') {
        Registry::get('view')->assign('results', fn_get_results($racket_finder));
    }
    Registry::get('view')->assign('step', $step);
    Registry::get('view')->assign('racket_finder', $racket_finder);
    Registry::get('view')->display('addons/development/views/racket_finder/view.tpl');

    exit;
}
if ($mode == 'view') {

    $step = fn_get_step($racket_finder, $step);
    if ($step == 'result') {
        Registry::get('view')->assign('results', fn_get_results($racket_finder));
    }
    Registry::get('view')->assign('step', $step);
    fn_add_breadcrumb(__('tennis_raquets'), 'categories.view?category_id=' . RACKETS_CATEGORY_ID);
    fn_add_breadcrumb(__('racket_finder_page_title'));
    Registry::get('view')->assign('racket_finder', $racket_finder);
    Registry::get('view')->assign('meta_description', __('racket_finder_meta'));
}

if ($mode == 'hide_add') {
    $_SESSION['hide_rf_add'] = true;
    exit;
}

if ($mode == 'show_add') {
    $_SESSION['hide_rf_add'] = false;
    exit;
}

function fn_get_perc($inac = 0, $full = true)
{
    global $rf_schema;
    
    $total = 0;
    foreach ($rf_schema as $i => $quest) {
        if (!empty($quest['variants'])) {
            $total += count($quest['variants']);
        } else {
            $total++;
        }
    }
    
    if ($full) {
        return round(($total - $inac) * 100 / $total);
    } else {
        return round($inac * 100 / $total);
    }
}

function fn_get_results(&$racket_finder)
{
    global $rf_schema;
    
    $_params = array (
        'cid' => RACKETS_CATEGORY_ID,
        'subcats' => 'Y',
        'sort_by' => 'popularity'
//         'limit' => 5
    );
    $racket_level = 0; 
//     1 - 17in,
//     2 - 19in,
//     3 - 21in,
//     4 - 23in,
//     5 - 25in,
//     6 - 26in,
//     7 - 27in, <= 265
//     8 - 27in, 265 - 280
//     9 - 27in, 280 - 295
//     10 - 27in, > 295
//     11 - 27in, > 305
    if ($racket_finder['age'] < 16) {
        if ($racket_finder['height'] <= 100) {
            $racket_level = 1;
        } elseif ($racket_finder['height'] <= 115) {
            $racket_level = 2;
        } elseif ($racket_finder['height'] <= 125) {
            $racket_level = 3;
        } elseif ($racket_finder['height'] <= 135) {
            $racket_level = 4;
        } elseif ($racket_finder['height'] <= 145) {
            $racket_level = 5;
        } elseif ($racket_finder['height'] <= 155) {
            $racket_level = 6;
        } else {
            $racket_level = 7;
        }
        if ($racket_finder['skill'] == 'advanced') {
            $racket_level += 1;
        }
        if ($racket_finder['frequency'] == 'week') {
            $racket_level += 1;
        }
    } else {
        $racket_level = 7;
        if ($racket_finder['gender'] == 'female') {
            $racket_level += 0;
        } elseif ($racket_finder['gender'] == 'male') {
            $racket_level += 1;
        }
        if ($racket_finder['skill'] == 'beginner') {
            $racket_level += 0;
            if ($racket_finder['frequency'] == 'year') {
                $racket_level += 0;
            } elseif ($racket_finder['frequency'] == 'month') {
                $racket_level += 1;
            } elseif ($racket_finder['frequency'] == 'week') {
                $racket_level += 2;
            }
        } elseif ($racket_finder['skill'] == 'intermediate') {
            $racket_level += 1;
            if ($racket_finder['frequency'] == 'year') {
                $racket_level += 0;
            } elseif ($racket_finder['frequency'] == 'month') {
                $racket_level += 0;
            } elseif ($racket_finder['frequency'] == 'week') {
                $racket_level += 1;
            }
        } elseif ($racket_finder['skill'] == 'advanced') {
            $racket_level += 2;
            if ($racket_finder['frequency'] == 'year') {
                $racket_level += 0;
            } elseif ($racket_finder['frequency'] == 'month') {
                $racket_level += 0;
            } elseif ($racket_finder['frequency'] == 'week') {
                $racket_level += 0;
            }
        }
    }
    $levels = array(
        fn_get_perc() =>  $_params,
        fn_get_perc(1) =>  $_params
    );
    switch ($racket_level) {
        case 1:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_17_FV_ID
                )
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_19_FV_ID
                )
            );
            break;
        case 2:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_19_FV_ID
                )
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_21_FV_ID
                )
            );
            break;
        case 3:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_21_FV_ID
                )
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_23_FV_ID
                )
            );
            break;
        case 4:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_23_FV_ID
                )
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_25_FV_ID
                )
            );
            break;
        case 5:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_25_FV_ID
                )
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_26_FV_ID
                )
            );
            break;
        case 6:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => KIDS_26_FV_ID
                )
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'max_value' => 266
                ),
            );
            break;
        case 7:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'max_value' => 266
                ),
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'min_value' => 265,
                    'max_value' => 281
                ),
            );
            break;
        case 8:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'min_value' => 265,
                    'max_value' => 281
                ),
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'min_value' => 280,
                    'max_value' => 296
                ),
            );
            break;
        case 9:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'min_value' => 280,
                    'max_value' => 296
                ),
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'min_value' => 295
                ),
            );
            break;
        case 10:
            $levels[fn_get_perc()]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'min_value' => 295
                ),
            );
            $levels[fn_get_perc(1)]['features_condition'][] = array(
                R_LENGTH_FEATURE_ID => array(
                    'variant_id' => REGULAR_LENGTH_FV_ID
                ),
                R_WEIGHT_FEATURE_ID => array(
                    'min_value' => 280,
                    'max_value' => 296
                ),
            );
            break;
    }
    $results = array();
    $min_price = $max_price = 0;
    foreach ($levels as $percentage => $_params) {
        list($prods,) = fn_get_products($_params);
        if (!empty($prods)) {
            fn_gather_additional_products_data($prods, array(
                'get_icon' => true,
                'get_detailed' => true,
                'get_additional' => true,
                'get_options' => true,
                'get_discounts' => true,
                'get_features' => true,
                'get_title_features' => true,
                'features_display_on' => 'P'
            ));
            foreach ($prods as $i => $prod) {
                if ($min_price == 0 || $min_price > $prod['price']) {
                    $min_price = $prod['price'];
                }
                if ($max_price == 0 || $max_price < $prod['price']) {
                    $max_price = $prod['price'];
                }
                $prods[$i]['match_p'] = $percentage;
            }
            $results = array_merge($results, $prods);
        }
    }
    if (!empty($racket_finder['power'])) {
        $power = array_keys($rf_schema['power']['variants']);
        $rel_power = array(
            POWER_VHIGH_FV_ID => 2,
            POWER_HIGH_FV_ID => 2,
            POWER_MOD_FV_ID => 1,
            POWER_LOW_FV_ID => 0,
            POWER_VLOW_FV_ID => 0,
        );
        foreach ($results as $i => $prod) {
            if (!empty($prod['product_features'][R_HEADSIZE_FEATURE_ID]['value_int']) && $prod['product_features'][R_HEADSIZE_FEATURE_ID]['value_int'] > 645 && array_search($racket_finder['power'], $power) < 2) {
                unset($results[$i]);
                continue;
            }
            if (!empty($prod['product_features'][R_POWER_FEATURE_ID]['variant_id'])) {
                $results[$i]['match_p'] -= fn_get_perc(abs(array_search($racket_finder['power'], $power) - $rel_power[$prod['product_features'][R_POWER_FEATURE_ID]['variant_id']]), false);
            } else {
                $results[$i]['match_p'] -= fn_get_perc(1, false);
            }
        }
    }
    
    if (!empty($min_price) && !empty($max_price) && !empty($racket_finder['expectations'])) {
        $dif_price = $max_price - $min_price;
        $part = ($max_price - $min_price) / count($rf_schema['expectations']['variants']);
        $limits = array();
        $expectations = array_keys($rf_schema['expectations']['variants']);
        foreach ($rf_schema['expectations']['variants'] as $key => $txt) {
            $limits[$key] = array(
                'min' => $min_price + $part * array_search($key, $expectations),
                'max' => $min_price + $part * (array_search($key, $expectations) + 1)
            );
        }
        foreach ($results as $i => $prod) {
            $best_match = 10;
            foreach ($limits as $key => $lmt) {
                if ($prod['price'] >= $lmt['min'] && $prod['price'] <= $lmt && $best_match > abs(array_search($racket_finder['expectations'], $expectations) - array_search($key, $expectations))) {
                    $best_match = abs(array_search($racket_finder['expectations'], $expectations) - array_search($key, $expectations));
                }
            }
            $results[$i]['match_p'] -= fn_get_perc($best_match, false);
        }
    }
    
    usort($results, 'fn_sort_by_match_p');
    
    return $results;
}

function fn_sort_by_match_p($el1, $el2)
{
    if ($el1['match_p'] == $el2['match_p']) {
        return 0;
    }
    return ($el1['match_p'] > $el2['match_p']) ? -1 : 1;
}

function fn_is_kid($age)
{
    return ($age < 16) ? true : false;
}

function fn_is_adult($age)
{
    return ($age >= 16) ? true : false;
}

function fn_is_tall($height)
{
    return ($height >= 155) ? true : false;
}

function fn_get_step(&$racket_finder, $step = '', $direction = '')
{
    global $rf_schema;
    $schema_keys = array_keys($rf_schema);
    if (!empty($racket_finder)) {
        foreach ($rf_schema as $q_key => $q_data) {
            if (empty($racket_finder[$q_key])) {
                if (!isset($_step)) {
                    if (!empty($q_data['conditions'])) {
                        $match = false;
                        foreach ($q_data['conditions'] as $c_key => $func) {
                            if (call_user_func($func, $racket_finder[$c_key])) {
                                $match = true;
                                break;
                            }
                        }
                        if (!$match) {
                            continue;
                        }
                    }
                    $_step = $q_key;
                }
            } else {
                if (!empty($q_data['conditions'])) {
                    $match = false;
                    foreach ($q_data['conditions'] as $c_key => $func) {
                        if (call_user_func($func, $racket_finder[$c_key])) {
                            $match = true;
                            break;
                        }
                    }
                    if (!$match) {
                        unset($racket_finder[$q_key]);
                    }
                }
            }
        }
        if (empty($_step)) {
            $_step = 'result';
        }
    } else {
        $_step = $schema_keys[0];
    }
    
    if ($step == '') {
        $step = $_step;
    }
//     fn_print_die($racket_finder, $step, $_step, $schema_keys[0]);
    if ($direction != '') {
        $iter = array_search($step, $schema_keys);
        if ($direction == 'F') {
            if (!empty($racket_finder[$step])) {
                if ($iter >= count($schema_keys) - 1) {
                    $step = 'result';
                } else {
                    for ($i = $iter + 1; $i < count($schema_keys); $i++) {
                        if (!empty($rf_schema[$schema_keys[$i]]['conditions'])) {
                            $match = false;
                            foreach ($rf_schema[$schema_keys[$i]]['conditions'] as $c_key => $func) {
                                if (call_user_func($func, $racket_finder[$c_key])) {
                                    $match = true;
                                    break;
                                }
                            }
                            if (!$match) {
                                continue;
                            }
                        }
                        $step = $schema_keys[$i];
                        break;
                    }
                }
            }
        } elseif ($direction == 'B') {
            if ($iter == 0) {
                $step = $schema_keys[0];
            } else {
                for ($i = $iter - 1; $i >= 0; $i--) {
                    if (!empty($rf_schema[$schema_keys[$i]]['conditions'])) {
                        $match = false;
                        foreach ($rf_schema[$schema_keys[$i]]['conditions'] as $c_key => $func) {
                            if (call_user_func($func, $racket_finder[$c_key])) {
                                $match = true;
                                break;
                            }
                        }
                        if (!$match) {
                            continue;
                        }
                    }
                    $step = $schema_keys[$i];
                    break;
                }
            }
        } else {
            if (!empty($racket_finder[$direction])) {
                $step = $direction;
            }
        }
    }
    if (($_step != 'result' && array_search($step, $schema_keys) > array_search($_step, $schema_keys)) || ($step == 'result' && $_step != 'result')) {
        $step = $_step;
    }
    
    return $step;
}