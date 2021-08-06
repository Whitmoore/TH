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

namespace Tygh;

use Tygh\Registry;
use Tygh\Memcache;

class FeaturesCache
{
    private static $cache_levels = array(
        'lang_code',
        'feature_id',
        'type',
        'variant',
        'product_id'
    );
    
    public static function generate($lang_code = '')
    {
        $feature_values = db_get_array("SELECT * FROM ?:product_features_values");
        $result = array();
        self::formatResults($result, $feature_values, $lang_code);
        self::set($result);
    }

    private static function set($result, $key = 'features', $type = 'F')
    {
        if (USE_FEATURE_CACHE) {
            Memcache::instance()->call('set', $key, $result, $type);
            fn_put_contents(DIR_ROOT . '/var/features_cache/cache', serialize($result), '', 0777);
        }
    }
    
    private static function get($key = 'features', $type = 'F')
    {
        if (USE_FEATURE_CACHE) {
            $memcache_features = Memcache::instance()->call('get', $key, $type);
            if (empty($memcache_features) && is_readable(DIR_ROOT . '/var/features_cache/cache')) {
                $memcache_features = unserialize(fn_get_contents(DIR_ROOT . '/var/features_cache/cache'));
            }
            
            return $memcache_features;
        }
        
        return false;
    }
    
    public static function updateFeatureValueInt($feature_id, $old_value_int, $new_value_int, $lang_code)
    {
        $memcache_features = self::get();
        if (!empty($memcache_features[$lang_code][$feature_id]['values'][$old_value_int])) {
            $tmp = $memcache_features[$lang_code][$feature_id]['values'][$old_value_int];
            unset($memcache_features[$lang_code][$feature_id]['values'][$old_value_int]);
            $memcache_features[$lang_code][$feature_id]['values'][(string)number_format($new_value_int, 2, '.', '')] = $tmp;
        }
        self::set($memcache_features);
    }
    
    public static function clearoutFeatures($params)
    {
        $memcache_features = self::get();
        if (!empty($memcache_features)) {
            self::clearoutFeatureValues($params, $memcache_features);
        }
        self::set($memcache_features);
    }
    
    public static function deleteFeature($feature_id)
    {
        $memcache_features = self::get();
        if (!empty($memcache_features)) {
            $params = array(
                'delete' => array('feature_id' => array($feature_id))
            );
            self::clearoutFeatureValues($params, $memcache_features);
        }
        self::set($memcache_features);
    }
    
    public static function deleteVariants($variant_ids)
    {
        $memcache_features = self::get();
        if (!empty($memcache_features)) {
            $value_ints = db_get_array("SELECT feature_id, value_int FROM ?:product_features_values WHERE variant_id IN (?n)", $variant_ids);
            if (!empty($value_ints)) {
                foreach ($value_ints as $i => $data) {
                    if (!empty($data['value_int'])) {
                        $params = array(
                            'delete' => array('variant' => array($data['value_int'])),
                            'condition' => array('feature_id' => array($data['feature_id']))
                        );
                        self::clearoutFeatureValues($params, $memcache_features);
                    }
                }
            }
            $params = array(
                'delete' => array('variant' => $variant_ids)
            );
            self::clearoutFeatureValues($params, $memcache_features);
        }
        self::set($memcache_features);
    }
    
    public static function deleteProduct($product_id)
    {
        $memcache_features = self::get();
        if (!empty($memcache_features)) {
            $params = array(
                'delete' => array('product_id' => array($product_id))
            );
            self::clearoutFeatureValues($params, $memcache_features);
        }
        self::set($memcache_features);
    }
    
    public static function updateProductFeaturesValue($product_id, $features, $lang_code = CART_LANGUAGE)
    {
        $memcache_features = self::get();
        if (!empty($memcache_features)) {
            $params = array(
                'delete' => array('product_id' => array($product_id))
            );
            self::clearoutFeatureValues($params, $memcache_features);
        }
        if (!empty($features)) {
            self::formatResults($memcache_features, $features, $lang_code);
        }
        self::set($memcache_features);
    }
    
    public static function getProductsConditions($features_condition, &$join, &$condition, $lang_code = CART_LANGUAGE, $products_table = 'products')
    {
        $memcache_features = self::get();
        if (!empty($memcache_features[$lang_code])) {
            $memcache_features = $memcache_features[$lang_code];
            $_product_conditions = array();
            foreach ($features_condition as $i => $features) {
                $product_ids = array();
                foreach ($features as $feature_id => $feature_data) {
                    if (!empty($memcache_features[$feature_id])) {
                        if (!empty($feature_data['variants'])) {
                            $pr_ids = array();
                            foreach ($feature_data['variants'] as $i => $variant) {
                                if (!empty($memcache_features[$feature_id]['variants'][$variant['variant_id']])) {
                                    $pr_ids = array_merge($pr_ids, $memcache_features[$feature_id]['variants'][$variant['variant_id']]);
                                }
                            }
                            if (!empty($pr_ids)) {
                                $product_ids[] = $pr_ids;
                            }
                        } elseif (!empty($feature_data['variant_id']) && !empty($memcache_features[$feature_id]['variants'][$feature_data['variant_id']])) {
                            $product_ids[] = $memcache_features[$feature_id]['variants'][$feature_data['variant_id']];
                        } elseif (!empty($feature_data['value']) && !empty($memcache_features[$feature_id]['values'][$feature_data['value']])) {
                            $product_ids[] = $memcache_features[$feature_id]['values'][$feature_data['value']];
                        } else {
                            $_prod_ids = array();
                            if (!empty($feature_data['not_variant']) && !empty($memcache_features[$feature_id]['variants'])) {
                                foreach ($memcache_features[$feature_id]['variants'] as $variant_id => $_product_ids) {
                                    if (!empty($_product_ids) && $feature_data['not_variant'] != $variant_id) {
                                        $_prod_ids = array_merge($_prod_ids, $_product_ids);
                                    }
                                }
                            } elseif ((!empty($feature_data['not_value']) || !empty($feature_data['min_value']) || !empty($feature_data['max_value'])) && !empty($memcache_features[$feature_id]['values'])) {
                                foreach ($memcache_features[$feature_id]['values'] as $variant_id => $_product_ids) {
                                    if (!empty($_product_ids)) {
                                        $use = true;
                                        if (!empty($feature_data['min_value']) && $variant_id < $feature_data['min_value']) {
                                            $use = false;
                                        }
                                        if (!empty($feature_data['max_value']) && $variant_id > $feature_data['max_value']) {
                                            $use = false;
                                        }
                                        if ($use) {
                                            $_prod_ids = array_merge($_prod_ids, $_product_ids);
                                        }
                                    }
                                }
                            }
                            if (!empty($_prod_ids)) {
                                $product_ids[] = $_prod_ids;
                            }
                        }
                    }
                }
                if (!empty($product_ids)) {
                    $result = array_shift($product_ids);
                    foreach ($product_ids as $i => $pr_ids) {
                        $result = array_intersect($result, $pr_ids);
                    }
                    $_product_conditions[] = db_quote("$products_table.product_id IN (?n)", $result);
                }
            }
            if (!empty($_product_conditions)) {
                $condition .= db_quote(" AND (?p) ", implode(' OR ', $_product_conditions));
            } else {
                $condition .= db_quote(" AND FALSE ");
            }
        } else {
            $_conditions = array();
            foreach ($features_condition as $i => $features) {
                $conditions = array();
                foreach ($features as $feature_id => $feature_data) {
                    $join .= db_quote(" LEFT JOIN ?:product_features_values AS feature_?i ON feature_?i.product_id = $products_table.product_id AND feature_?i.feature_id = ?i AND feature_?i.lang_code = ?s", $feature_id, $feature_id, $feature_id, $feature_id, $feature_id, $lang_code);
                    if (!empty($feature_data['variants'])) {
                        $where_conditions = array();
                        foreach ($feature_data['variants'] as $i => $variant) {
                            if (!empty($variant['variant_id'])) {
                                $where_conditions[] = db_quote("feature_?i.variant_id = ?i", $feature_id, $variant['variant_id']);
                            }
                        }
                        if (!empty($where_conditions)) {
                            $conditions[] = db_quote("?p", implode(" OR ", $where_conditions));
                        }
                    } elseif (!empty($feature_data['variant_id'])) {
                        $conditions[] = db_quote("feature_?i.variant_id = ?i", $feature_id, $feature_data['variant_id']);
                    } elseif (!empty($feature_data['value'])) {
                        $conditions[] = db_quote("feature_?i.value = ?i", $feature_id, $feature_data['value']);
                    } elseif (!empty($feature_data['not_variant'])) {
                        $conditions[] = db_quote("feature_?i.variant_id != ?i", $feature_id, $feature_data['not_variant']);
                    } elseif (!empty($feature_data['not_value'])) {
                        $conditions[] = db_quote("feature_?i.value != ?i", $feature_id, $feature_data['not_value']);
                    } elseif (!empty($feature_data['min_value']) || !empty($feature_data['max_value'])) {
                        if (!empty($feature_data['min_value'])) {
                            $conditions[] = db_quote("feature_?i.value_int > ?d", $feature_id, $feature_data['min_value']);
                        }
                        if (!empty($feature_data['max_value'])) {
                            $conditions[] = db_quote("feature_?i.value_int < ?d", $feature_id, $feature_data['max_value']);
                        }
                    }
                }
                if (!empty($conditions)) {
                    $_conditions[] = db_quote("?p", implode(' AND ', $conditions));
                }
            }
            if (!empty($_conditions)) {
                $condition .= db_quote(" AND (?p)", implode(' OR ', $_conditions));
            }
        }
    }
    
    private static function clearoutFeatureValues($params, &$memcache_features, $level = 0)
    {
        if (!empty($memcache_features)) {
            foreach ($memcache_features as $i => $j) {
                if (!empty($params['delete'][self::$cache_levels[$level]]) && in_array($i, $params['delete'][self::$cache_levels[$level]])) {
                    unset($memcache_features[$i]);
                } elseif (empty($params['condition'][self::$cache_levels[$level]]) || (!empty($params['condition'][self::$cache_levels[$level]]) && in_array($i, $params['condition'][self::$cache_levels[$level]]))) {
                    if (is_array($j)) {
                        self::clearoutFeatureValues($params, $memcache_features[$i], $level + 1);
                    } elseif ((!empty($params['delete'][self::$cache_levels[$level]]) && in_array($j, $params['delete'][self::$cache_levels[$level]])) || (!empty($params['delete']['not_' . self::$cache_levels[$level]]) && !in_array($j, $params['delete']['not_' . self::$cache_levels[$level]]))) {
                        unset($memcache_features[$i]);
                    }
                }
            }
        }
    }
    
    public static function advancedVariantIds($advanced_variant_ids, &$join, &$condition, $lang_code = CART_LANGUAGE)
    {
        $memcache_features = self::get();
        if (!empty($memcache_features[$lang_code])) {
            $product_ids = array();
            foreach ($advanced_variant_ids as $feature_id => $variant_ids) {
                $params = array(
                    'feature_id' => array($feature_id),
                    'variant' => array_keys($variant_ids)
                );
                $product_ids[] = self::getProductIds($params, $memcache_features);
            }
            if (!empty($product_ids)) {
                $result = array_shift($product_ids);
                foreach ($product_ids as $i => $pr_ids) {
                    $result = array_intersect($result, $pr_ids);
                }
                $product_ids = $result;
            }
            $condition .= db_quote(" AND products.product_id IN (?n) ", $product_ids);
        } else {
            $join .= db_quote(" LEFT JOIN ?:product_features_values ON ?:product_features_values.product_id = products.product_id AND ?:product_features_values.lang_code = ?s LEFT JOIN (SELECT product_id, GROUP_CONCAT(?:product_features_values.variant_id) AS advanced_variants FROM ?:product_features_values WHERE feature_id IN (?n) AND lang_code = ?s GROUP BY product_id) AS pfv_advanced ON pfv_advanced.product_id = products.product_id", $lang_code, array_keys($advanced_variant_ids), $lang_code);

            $where_and_conditions = array();
            foreach ($advanced_variant_ids as $k => $variant_ids) {
                $where_or_conditions = array();
                foreach ($variant_ids as $variant_id => $v) {
                    $where_or_conditions[] = db_quote(" FIND_IN_SET('?i', advanced_variants)", $variant_id);
                }
                $where_and_conditions[] = '(' . implode(' OR ', $where_or_conditions) . ')';
            }
            $condition .= ' AND ' . implode(' AND ', $where_and_conditions);
        }
    }
    
    private static function getProductIds($params, &$memcache_features, $level = 0, &$product_ids = array())
    {
        if (!empty($memcache_features)) {
            foreach ($memcache_features as $i => $j) {
                if (empty($params[self::$cache_levels[$level]]) || (!empty($params[self::$cache_levels[$level]]) && in_array($i, $params[self::$cache_levels[$level]]))) {
                    if (is_array($j)) {
                        self::getProductIds($params, $memcache_features[$i], $level + 1, $product_ids);
                    } else {
                        $product_ids[] = $memcache_features[$i];
                    }
                }
            }
        }
        
        return $product_ids;
    }
    
    private static function formatResults(&$result, $feature_values, $lang_code = '')
    {
        if (!empty($feature_values)) {
            foreach ($feature_values as $i => $feature_value) {
                if (empty($lang_code) || $feature_value['lang_code'] == $lang_code) {
                    if (!empty($feature_value['value_int']) && (empty($result[$feature_value['feature_id']][$feature_value['value_int']]) || !in_array($feature_value['product_id'], $result[$feature_value['feature_id']][$feature_value['value_int']]))) {
                        $result[$feature_value['lang_code']][$feature_value['feature_id']]['values'][$feature_value['value_int']][] = $feature_value['product_id'];
                    }
                    if (!empty($feature_value['variant_id']) && (empty($result[$feature_value['feature_id']][$feature_value['variant_id']]) || !in_array($feature_value['product_id'], $result[$feature_value['feature_id']][$feature_value['variant_id']]))) {
                        $result[$feature_value['lang_code']][$feature_value['feature_id']]['variants'][$feature_value['variant_id']][] = $feature_value['product_id'];
                    }
                }
            }
        }
    }   
}