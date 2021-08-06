<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2020 PaulDreda    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/


namespace Tygh\Sync\Agents;

use Tygh\Http;

class Driada extends Agent
{
    public function __construct()
    {
        parent::__construct(DRIADA_WAREHOUSE_ID);
        $this->source = 'http://driada-sport.ru/data/files/XML_prise.xml';
        $this->create_new = true;
    }

    protected function parseAgent()
    {
        $shop = $this->feed->shop;
        $result = array();
        if (!empty($shop)) {
            foreach($shop->categories->category as $cat) {
                $attributes = array();
                foreach($cat->attributes() as $key => $val) {
                    $attributes[(string) $key] = (int) $val;
                }
                $result['categories'][$attributes['id']] = $attributes;
                $result['categories'][$attributes['id']]['name'] = (string)$cat;
            }

            foreach($shop->offers->offer as $offer) {
                if ((string)$offer->attributes()['available'] == 'true') {
                    $product_data = $this->parseProduct($offer);
                    if (!empty($product_data['product_code'])) {
                        $result['products'][$product_data['product_code']] = $product_data;
                    }
                }
            }
        }

        return $result;
    }

    private function parseCombination(&$result, $key)
    {
        $result[$key][] = array(
            'amount' => 1
        );
    }

    private function parseProduct($object)
    {
        $sync_schema = $this->getSyncSchema();
        $result = array();
        if (!empty($sync_schema)) {
            foreach ($sync_schema as $product_key => $schema_data) {
                if ($schema_data['type'] == 'attribute') {
                    foreach ($object->{$schema_data['value']}->attributes() as $key => $val) {
                        $result[$product_key][] = (string)$val;
                    }
                } elseif ($schema_data['type'] == 'field') {
                    $result[$product_key] = (string) $object->{$schema_data['value']} ?? false;
                } elseif ($schema_data['type'] == 'const') {
                    $result[$product_key] = $schema_data['value'];
                } elseif ($schema_data['type'] == 'array') {
                    foreach ($object->{$schema_data['value']} as $obj) {
                        $result[$product_key][(string) $obj->attributes()[$schema_data['key']]] = (string) $obj;
                    }
                } elseif ($schema_data['type'] == 'combinations') {
                    $result[$product_key] = array();
                }

                if (!empty($schema_data['post_func']) && method_exists($this, $schema_data['post_func'])) {
                    $this->{$schema_data['post_func']}($result, $product_key);
                }
            }
        }

        return $result;
    }

    protected function parseAgentFeatures(&$data)
    {
        if (empty($data['raw_features']) || empty($data['main_category'])) {
            return true;
        }
        static $features = array();
        static $schema = array();

        if (empty($schema)) {
            $schema = fn_get_schema('sync', 'features');
        }
        $product_features = $add_new_variant = array();
        if (!empty($schema[$this->agent][$data['main_category']])) {
            $s_features = $schema[$this->agent][$data['main_category']]['features'];
            if (empty($features[$this->agent][$data['main_category']]) && !empty($schema[$this->agent][$data['main_category']]['parent_group_id'])) {
                $params = array(
                    'parent_id' => $schema[$this->agent][$data['main_category']]['parent_group_id'],
                    'exclude_group' => true,
                    'plain' => true,
                    'variants' => true
                );
                list($features[$this->agent][$data['main_category']], $search, $has_ungroupped) = fn_get_product_features($params);
            }
            if (!empty($features[$this->agent][$data['main_category']])) {
                $d_features = $features[$this->agent][$data['main_category']];
                foreach ($data['raw_features'] as $d_key => $d_val) {
                    if (!empty($s_features[$d_key]) && !empty($d_features[$s_features[$d_key]])) {
                        if (in_array($d_features[$s_features[$d_key]]['feature_type'], array('S', 'N', 'E'))) {
                            $matched = false;
                            foreach ($d_features[$s_features[$d_key]]['variants'] as $feature_variant) {
                                if (mb_convert_case($feature_variant['variant'], MB_CASE_LOWER) == mb_convert_case($d_val, MB_CASE_LOWER)) {
                                    $product_features[$s_features[$d_key]] = $feature_variant['variant_id'];
                                    $matched = true;
                                    break;
                                }
                            }
                            if (empty($matched)) {
                                $product_features[$s_features[$d_key]] = 'disable_select';
                                $add_new_variant[$s_features[$d_key]] = array(
                                    'variant' => mb_convert_case($d_val, MB_CASE_TITLE_SIMPLE)
                                );
                            }
                        } elseif ($d_features[$s_features[$d_key]]['feature_type'] == 'M') {
                            $variants = array_map('trim', explode(',', mb_convert_case($d_val, MB_CASE_LOWER)));
                            if (!empty($variants)) {
                                $existing = $add_new = array();
                                foreach ($d_features[$s_features[$d_key]]['variants'] as $feature_variant) {
                                    $existing[$feature_variant['variant_id']] = mb_convert_case($feature_variant['variant'], MB_CASE_LOWER);
                                }
                                foreach ($variants as $f_var) {
                                    if (($key = array_search($f_var, $existing)) !== false) {
                                        $product_features[$s_features[$d_key]][$key] = $key;
                                    } else {
                                        $add_new[] = array(
                                            'variant' => $f_var
                                        );
                                    }
                                }
                                if (!empty($add_new)) {
                                    foreach ($add_new as $new_var) {
                                        $new_key = fn_add_feature_variant($s_features[$d_key], $new_var);
                                        if (!empty($new_key)) {
                                            $product_features[$s_features[$d_key]][$new_key] = $new_key;
                                            $d_features[$s_features[$d_key]]['variants'][] = array(
                                                'variant_id' => $new_key,
                                                'variant' => $new_var
                                            );
                                        }
                                    }
                                }
                            }
                        } elseif ($d_features[$s_features[$d_key]]['feature_type'] == 'C') {
                            if (mb_convert_case($d_val, MB_CASE_LOWER) == 'есть') {
                                $product_features[$s_features[$d_key]] = 'Y';
                            } else {
                                $product_features[$s_features[$d_key]] = 'N';
                            }
                        } else {
                            $product_features[$s_features[$d_key]] = $d_val;
                        }

                        unset($data['raw_features'][$d_key]);
                    }
                }
            }
        }
        if (!empty($product_features)) {
            $data['product_features'] = $product_features;
        }
        if (!empty($add_new_variant)) {
            $data['add_new_variant'] = $add_new_variant;
            unset($features[$this->agent][$data['main_category']]);
        }
    }

    private function parseAgentCategory(&$result, $key)
    {
        static $schema = array();

        if (empty($schema)) {
            $schema = fn_get_schema('sync', 'categories');
        }

        $category_id = $schema[$this->agent][$result[$key]] ?? false;
        if (!empty($category_id)) {
            $result[$key] = array($category_id);
            $result['main_category'] = $category_id;
        } else {
            unset($result[$key]);
        }
    }
}
