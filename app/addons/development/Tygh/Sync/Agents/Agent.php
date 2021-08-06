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

class Agent
{
    protected $agent;
    protected $source;
    protected $feed;
    protected $brand_ids;
    protected $create_new;

    private $in_stock = array();
    private $import_statuses = array();
    private $parsed_data = array();
    private $missing_features = array();

    public function __construct($agent_id)
    {
        $this->agent = $agent_id;
        $this->in_stock = $this->missing_features = $this->parsed_data = array();
        $this->import_statuses = array(
            'F' => array(),
            'N' => array(),
            'U' => array()
        );
        $this->brand_ids = db_get_fields("SELECT brand_id FROM ?:warehouse_brands WHERE warehouse_id = ?i", $this->agent);
    }

    // U - updated, N - new, M - missing, B - broken
    private function markResult($status, $product_code, $product_id = false, $extra = 'OK')
    {
        $this->import_statuses[$status][$product_code] = $extra;
        if (!empty($product_id) && in_array($status, array('U', 'N')) && !in_array($product_id, $this->in_stock)) {
            $this->in_stock[] = $product_id;
        }
    }

    private function newInventory($inventory, $amount)
    {
        $result = $inventory;
        unset($result['combination']);
        $result['amount'] = $amount;

        return $result;
    }

    public function Synchronize()
    {
        $this->getFeed();
        $success = false;
        $results = array();
        if (!empty($this->feed)) {
            $this->parsed_data = $this->parseAgent();

            if (!empty($this->parsed_data['products'])) {

                $params = array(
                    'features_hash' => 'V' . implode('.V', $this->brand_ids),
                    'warehouse_id' => $this->agent,
                    'area' => 'A'
                    //'force_get_by_ids' => 'Y',
                );

                list($products,) = fn_get_products($params);
                $all_ids = $updated_by_combinations = $product_data = $product_codes_data = $updated_warehouse_inventories = $new_warehouse_inventories = $trash = $products_options = array();

                if (!empty($products)) {
                    foreach ($products as $i => $p_data) {
                        if (!empty($p_data['product_code'])) {
                            $all_ids[] = $p_data['product_id'];
                            $product_data[$p_data['product_id']] = $p_data;
                            $product_codes_data[$p_data['product_code']][$p_data['product_id']] = $p_data;
                        }
                    }
                }

                $_ignore_list = db_get_fields("SELECT ignore_list FROM ?:brand_ignore_list WHERE brand_id IN (?n)", $this->brand_ids);
                $ignore_list = array();
                if (!empty($_ignore_list)) {
                    foreach ($_ignore_list as $i => $i_list) {
                        $ignore_list = array_merge($ignore_list, unserialize($i_list));
                    }
                }

                if (!empty($all_ids)) {
                    $products_options = fn_get_product_options($all_ids, DESCR_SL, true, true);
                    fn_rebuild_product_options_inventory_multi($all_ids, $products_options, $product_data);
                }

                $warehouse_inventories = db_get_hash_multi_array("SELECT ?:product_warehouses_inventory.*, ?:product_options_inventory.combination FROM ?:product_warehouses_inventory LEFT JOIN ?:product_options_inventory ON ?:product_options_inventory.combination_hash = ?:product_warehouses_inventory.combination_hash WHERE ?:product_warehouses_inventory.warehouse_id = ?i AND ?:product_warehouses_inventory.product_id IN (?n)", array('product_id', 'combination_hash'), $this->agent, array_keys($product_data));

                $other_inventories = db_get_hash_multi_array("SELECT SUM(amount) AS amount, combination_hash, product_id FROM ?:product_warehouses_inventory WHERE combination_hash != '0' AND warehouse_id != ?i AND product_id IN (?n) GROUP BY combination_hash", array('product_id', 'combination_hash'), $this->agent, array_keys($product_data));

                foreach ($this->parsed_data['products'] as $product_code => $data) {

                    if (!empty($ignore_list) && in_array($product_code, $ignore_list)) {
                        continue;
                    }

                    $this->prepareData($data);
                    list($validated, $allow_new) = $this->validateProduct($data);

                    if (empty($validated)) {
                        $this->markResult('F', $product_code, 0, 'Validation failed');
                        continue;
                    }

                    if (empty($product_codes_data[$product_code])) {

                        $combination_hash = db_get_array("SELECT combination_hash, product_id FROM ?:product_options_inventory WHERE product_code = ?s", $product_code);

                        if (empty($combination_hash)) {

                            if (!empty($this->create_new) && !empty($allow_new)) {
                                $product_id = $this->createProduct($data);

                                if (!empty($product_id)) {
                                    $this->markResult('N', $product_code, $product_id);
                                } else {
                                    $this->markResult('F', $product_code, 0, 'Creation failed');
                                }
                            } else {
                                $this->markResult('F', $product_code, 0, 'Creation not allowed');
                            }

                        } elseif (count($combination_hash) == 1 && count($data['combinations']) == 1 && !empty($warehouse_inventories[$combination_hash[0]['product_id']][$combination_hash[0]['combination_hash']])) {

                            $new_warehouse_inventories[] = $this->newInventory($warehouse_inventories[$combination_hash[0]['product_id']][$combination_hash[0]['combination_hash']], $data['combinations'][0]['amount']);

                            $updated_by_combinations[$combination_hash[0]['product_id']] = (empty($updated_by_combinations[$combination_hash[0]['product_id']])) ? array() : $updated_by_combinations[$combination_hash[0]['product_id']];
                            $updated_by_combinations[$combination_hash[0]['product_id']][] = $combination_hash[0]['combination_hash'];

                            $this->updateProduct($data, $combination_hash[0]['product_id']);
                            $this->markResult('U', $product_code, $combination_hash[0]['product_id']);

                        } else {
                            $this->markResult('F', $product_code, 0, 'Number of combinations error');
                        }

                    } else {

                        if (count($data['combinations']) == 1)  {

                            $variant = $data['combinations'][0];
                            foreach ($product_codes_data[$product_code] as $product_id => $_product) {
                                if ($_product['tracking'] == 'B' && empty($products_options[$product_id]) && !empty($warehouse_inventories[$product_id][0])) {
                                    $new_warehouse_inventories[] = $this->newInventory($warehouse_inventories[$product_id][0], floor($variant['amount'] / $_product['import_divider']));
                                    $this->markResult('U', $product_code, $product_id);
                                } else {
                                    $this->markResult('F', $product_code, 0, 'Tracking/Options error');
                                    break;
                                }
                            }

                        } else {

                            $combinations_data = array();
                            $is_broken = false;
                            foreach ($data['combinations'] as $i => $variant) {
                                if (!$this->parseOption($variant, $products_options, $product_codes_data, $combinations_data)) {
                                    $this->markResult('F', $product_code, 0, 'Options parse failed');
                                    $is_broken = true;
                                    break;
                                }
                            }

                            if (!empty($is_broken)) {
                                continue;
                            }

                            if (!empty($combinations_data)) {
                                $ttl_updated = 0;
                                foreach ($combinations_data as $product_id => $comb_data) {
                                    $ttl_updated += count($comb_data);
                                    $total_amount = 0;
                                    foreach ($warehouse_inventories[$product_id] as $k => $v) {
                                        $total_amount += $warehouse_inventories[$product_id][$k]['amount'];
                                        if (!empty($comb_data[$k])) {
                                            $warehouse_inventories[$product_id][$k]['amount'] = $comb_data[$k]['amount'];
                                            unset($comb_data[$k]);
                                        } else {
                                            $warehouse_inventories[$product_id][$k]['amount'] = 0;
                                        }
                                        $new_warehouse_inventories[] = $this->newInventory($warehouse_inventories[$product_id][$k], $warehouse_inventories[$product_id][$k]['amount']);
                                    }
                                    $updated_warehouse_inventories[$product_id] = $warehouse_inventories[$product_id];
                                    if (!empty($comb_data)) {
                                        $this->markResult('F', $product_code, 0, 'New/Warehouse combinations error');
                                    } else {
                                        $this->markResult('U', $product_code, $product_id);
                                    }
                                }
                                // if ($ttl_updated != count($data['combinations'])) {
                                //     $this->markResult('F', $product_code);
                                // }
                            } else {
                                $this->markResult('F', $product_code, 0, 'No combinations parsed');
                                continue;
                            }
                        }
                        // if (!empty($_REQUEST['debug']) && ($product_id == $_REQUEST['debug'] || $product_code == $_REQUEST['debug'])) {
                        //     fn_print_r($combinations_data);
                        // }

                        foreach ($product_codes_data[$product_code] as $product_id => $_product) {
                            $this->updateProduct($data, $product_id);
                        }
                    }
                }

                if (!empty($new_warehouse_inventories)) {
                    db_query("REPLACE ?:product_warehouses_inventory ?m", $new_warehouse_inventories);
                }

                $option_exceptions = array();
                if (!empty($updated_warehouse_inventories)) {
                    db_query("DELETE FROM ?:product_options_exceptions WHERE product_id IN (?n)", array_keys($updated_warehouse_inventories));
                    $features = db_get_hash_single_array("SELECT a.feature_id, option_id FROM ?:product_options AS a INNER JOIN ?:product_features ON ?:product_features.feature_id = a.feature_id", array('option_id', 'feature_id'));
                    $feature_variants = db_get_hash_single_array("SELECT feature_variant_id, variant_id FROM ?:product_option_variants", array('variant_id', 'feature_variant_id'));
                    foreach ($updated_warehouse_inventories as $pr_id => $combinations) {
                        $option_variants_avail = $option_variants = array();
                        foreach ($combinations as $hash => $wh_combination) {
                            $options_array = fn_get_product_options_by_combination($wh_combination['combination']);
                            if ($combinations[$hash]['amount'] < 1 && $other_inventories[$pr_id][$hash]['amount'] < 1) {
                                $option_exceptions[] = array(
                                    'product_id' => $pr_id,
                                    'combination' => serialize($options_array)
                                );
                            } else {
                                foreach ($options_array as $option_id => $variant_id) {
                                    if (empty($option_variants_avail[$option_id]) || !in_array($variant_id, $option_variants_avail[$option_id])) {
                                        $option_variants_avail[$option_id][] = $option_variants[] = $variant_id;
                                    }
                                }
                            }
                        }
                        if (!empty($option_variants_avail)) {
                            $features_data = array();
                            foreach ($option_variants_avail as $option_id => $variants) {
                                if (!empty($features[$option_id])) {
                                    $features_data[$features[$option_id]] = array();
                                    if (!empty($variants)) {
                                        foreach ($variants as $j => $variant_id) {
                                            if (!empty($feature_variants[$variant_id])) {
                                                $features_data[$features[$option_id]][] = $feature_variants[$variant_id];
                                            }
                                        }
                                    }
                                }
                            }
                            if (!empty($features_data)) {
                                $add_new_variant = array();
                                fn_update_product_features_value($pr_id, $features_data, $add_new_variant, CART_LANGUAGE);
                            }
                        }
                    }
                }

                $out_of_stock = array_diff($all_ids, $this->in_stock);
                db_query("UPDATE ?:products SET updated_timestamp = ?i WHERE product_id IN (?n)", time(), $this->in_stock);
                if (!empty($out_of_stock)) {
                    db_query("UPDATE ?:product_warehouses_inventory SET amount = '0' WHERE product_id IN (?n) AND warehouse_id = ?i", $out_of_stock, $this->agent);
                    db_query("DELETE FROM ?:product_options_exceptions WHERE product_id IN (?n)", $out_of_stock);
                    $all_combinations = db_get_hash_multi_array("SELECT combination_hash, combination, product_id FROM ?:product_options_inventory WHERE product_id IN (?n)", array('product_id', 'combination_hash'), $out_of_stock);
                    foreach ($out_of_stock as $os_i => $pr_id) {
                        if (!empty($all_combinations[$pr_id])) {
                            foreach ($all_combinations[$pr_id] as $t => $comb_dt) {
                                if (empty($other_inventories[$pr_id]) || $other_inventories[$pr_id][$comb_dt['combination_hash']]['amount'] < 1) {
                                    $options_array = fn_get_product_options_by_combination($comb_dt['combination']);
                                    $option_exceptions[] = array(
                                        'product_id' => $pr_id,
                                        'combination' => serialize($options_array)
                                    );
                                }
                            }
                        }
                    }
                }

                if (!empty($updated_by_combinations)) {
                    $all_combinations = db_get_hash_multi_array("SELECT combination_hash, combination, product_id FROM ?:product_options_inventory WHERE product_id IN (?n)", array('product_id', 'combination_hash'), array_keys($updated_by_combinations));
                    db_query("DELETE FROM ?:product_options_exceptions WHERE product_id IN (?n)", array_keys($updated_by_combinations));
                    foreach ($updated_by_combinations as $pr_id => $combs) {
                        $out = array_diff(array_keys($all_combinations[$pr_id]), $combs);
                        if (!empty($out)) {
                            db_query("UPDATE ?:product_warehouses_inventory SET amount = '0' WHERE combination_hash IN (?n) AND warehouse_id = ?i", $out, $this->agent);
                            foreach ($out as $t => $comb_hash) {
                                if ($other_inventories[$pr_id][$comb_hash]['amount'] < 1) {
                                    $options_array = fn_get_product_options_by_combination($all_combinations[$pr_id][$comb_hash]['combination']);
                                    $option_exceptions[] = array(
                                        'product_id' => $pr_id,
                                        'combination' => serialize($options_array)
                                    );
                                }
                            }
                        }
                    }
                }
                if (!empty($option_exceptions)) {
                    db_query("REPLACE INTO ?:product_options_exceptions ?m", $option_exceptions);
                }

                $success = true;
                $results = array(
                    'statuses' => $this->import_statuses,
                    // 'data' => $this->parsed_data,
                    'missing_features' => $this->missing_features
                );
            }
        }

        return array($success, $results);
    }

    private function parseOption($variant, $products_options, $product_codes_data, &$combinations_data)
    {
        $_broken_product = $option_data = $var_id_tmp = $options_count = $missing_variants = $max = array();
        foreach ($product_codes_data[$product_code] as $product_id => $_product) {
            if ($_product['tracking'] == 'O' && !empty($products_options[$product_id])) {
                $option_data[$product_id] = (empty($option_data[$product_id])) ? array() : $option_data[$product_id];
                $options_count[$product_id] = array_keys($products_options[$product_id]);
                $variants = explode(',', fn_normalize_string($variant['name']));
                $prev_numeric = false;
                $prev_id = '';
                foreach ($variants as $j => $variant_name) {
                    if (is_numeric($variant_name) && $prev_numeric) {
                        $variants[$prev_id] = $variants[$prev_id] . '.' . $variant_name;
                        unset($variants[$j]);
                        continue;
                    } elseif (is_numeric($variant_name)) {
                        $prev_numeric = true;
                    }
                    $prev_id = $j;
                }
                foreach ($variants as $j => $variant_name) {
                    $max[$j] = (isset($max[$j])) ? $max[$j] : 0;
                    $variant_name = fn_format_variant_name($variant_name);
                    $variant_found = false;
                    foreach ($products_options[$product_id] as $k => $opt_data) {
                        if (!empty($opt_data['variants'])) {

                            $variant_name = str_ireplace(fn_strtolower($opt_data['option_name']), '', fn_strtolower($variant_name));
                            foreach ($opt_data['variants'] as $kk => $vr_data) {
                                $var_name = fn_format_variant_name($vr_data['variant_name']);
                                if (strlen($var_name) > 0 && strpos($variant_name, $var_name) !== false) {
                                    $prc = round(strlen($var_name)/strlen($variant_name), 2) * 100;
                                    $var_id_tmp[$j][$opt_data['option_id']][$prc] = $vr_data['variant_id'];
                                    if ($prc > $max[$j]) {
                                        $max[$j] = $prc;
                                    }
                                }
                                if (strlen($variant_name) > 0 && strpos($var_name, $variant_name) !== false) {
                                    $prc = round(strlen($variant_name)/strlen($var_name), 2) * 100;
                                    $var_id_tmp[$j][$opt_data['option_id']][round(strlen($variant_name)/strlen($var_name), 2) * 100] = $vr_data['variant_id'];
                                    if ($prc > $max[$j]) {
                                        $max[$j] = $prc;
                                    }
                                }
                                if ($var_name === $variant_name) {
                                    if (empty($option_data[$product_id][$opt_data['option_id']])) {
                                        $option_data[$product_id][$opt_data['option_id']] = $vr_data['variant_id'];
                                        $variant_found = true;
                                        break 2;
                                    } else {
                                        return false;
                                    }
                                }
                            }
                        }
                    }
                    if (!$variant_found) {
                        $missing_variants[$product_id][] = $j;
                    }
                }
            } else {
                return false;
            }
        }
        if (!empty($option_data)) {
            $combination_hash = false;
            foreach ($option_data as $product_id => $opt_data) {
                if (count($options_count[$product_id]) != count($option_data[$product_id]) && !empty($missing_variants[$product_id])) {
                    $diff = array_diff($options_count[$product_id], array_keys($option_data[$product_id]));
                    if (!empty($diff)) {
                        foreach ($diff as $b => $opt_id) {
                            foreach ($missing_variants[$product_id] as $r => $var_num) {
                                if (!empty($var_id_tmp[$var_num][$opt_id][$max[$var_num]])) {
                                    $option_data[$product_id][$opt_id] = $var_id_tmp[$var_num][$opt_id][$max[$var_num]];
                                }
                            }
                        }
                    }
                    if (count($options_count[$product_id]) != count($option_data[$product_id])) {
                        continue;
                    }
                }
                $combination_hash = fn_generate_cart_id($product_id, array('product_options' => $option_data[$product_id]));
                $combinations_data[$product_id][$combination_hash]['amount'] = $variant['amount'];
            }
            if (empty($combination_hash)) {
                return false;
            }
        } else {
            return false;
        }

        return true;
    }


    private function prepareData(&$product)
    {
        static $brand_names = array();

        if (method_exists($this, 'parseAgentFeatures')) {
            $this->parseAgentFeatures($product);
        }

        if (empty($product['product_features'][BRAND_FEATURE_ID])) {
            if (empty($brand_names)) {
                $brand_names = db_get_hash_single_array("SELECT b.variant, b.variant_id FROM ?:product_feature_variants AS a LEFT JOIN ?:product_feature_variant_descriptions AS b ON a.variant_id = b.variant_id AND b.lang_code = ?s WHERE a.feature_id = ?i AND a.variant_id IN (?n)", array('variant_id', 'variant'), DESCR_SL, BRAND_FEATURE_ID, $this->brand_ids);
            }
            if (!empty($brand_names)) {
                foreach ($brand_names as $br_id => $br_name) {
                    if (mb_strpos(fn_strtolower($product['product']), fn_strtolower($br_name)) !== false) {
                        $product['product_features'][BRAND_FEATURE_ID] = $br_id;
                        break;
                    }
                }
            }
        }
    }

    private function updateProduct($product, $_product_id = 0)
    {
        $product_id = fn_update_product($product, $_product_id, DESCR_SL);

        if (!empty($product_id)) {
            if (!empty($product['raw_features']) && !empty($product['main_category'])) {
                $this->missing_features[$product['main_category']] = $this->missing_features[$product['main_category']] ?? array();
                foreach ($product['raw_features'] as $f_name => $f_value) {
                    $this->missing_features[$product['main_category']][$f_name][$f_value] = ($this->missing_features[$product['main_category']][$f_name][$f_value] ?? 0) + 1;
                }
            }
        }

        return $product_id;
    }

    private function createProduct($product)
    {
        $_REQUEST['product_main_image_data'] = $_REQUEST['file_product_main_image_detailed'] = $_REQUEST['type_product_main_image_detailed'] = $_REQUEST['product_add_additional_image_data'] = $_REQUEST['file_product_add_additional_image_detailed'] = $_REQUEST['type_product_add_additional_image_detailed'] = array();

        if (!empty($product['images'])) {
            $_REQUEST['object_type'] = 'P';
            foreach ($product['images'] as $i => $image) {
                if ($i === 0) {
                    $data_sufix = 'product_main_image_data';
                    $sufix = 'product_main_image_detailed';
                    $image_data = array(
                        'pair_id' => null,
                        'type' => 'M',
                        'object_id' => 0,
                        'image_alt' => null,
                        'detailed_alt' => null,
                    );
                } else {
                    $data_sufix = 'product_add_additional_image_data';
                    $sufix = 'product_add_additional_image_detailed';
                    $image_data = array(
                        'position' => null,
                        'pair_id' => null,
                        'type' => 'A',
                        'object_id' => 0,
                        'image_alt' => null,
                        'detailed_alt' => null,
                    );
                }

                $_REQUEST[$data_sufix][] = $image_data;
                $_REQUEST['file_' . $sufix][] = $image;
                $_REQUEST['type_' . $sufix][] = 'url';
            }
        }
        $product['status'] = 'H';
        $product['warehouse_inventory'] = 0;
        $product['product_type'] = 'P';
        $product['is_imported'] = 'Y';
        $product['approval_status'] = 'P';
        $product['full_description'] = nl2br($product['full_description']);

        $product_id = $this->updateProduct($product, 0);

        $_REQUEST['product_main_image_data'] = $_REQUEST['file_product_main_image_detailed'] = $_REQUEST['type_product_main_image_detailed'] = $_REQUEST['product_add_additional_image_data'] = $_REQUEST['file_product_add_additional_image_detailed'] = $_REQUEST['type_product_add_additional_image_detailed'] = array();

        if (!empty($product_id)) {
            $_data = array();

            if (count($product['combinations']) == 1) {
                $_data[] = array(
                    'warehouse_hash' => fn_generate_cart_id($product_id, array('warehouse_id' => $this->agent)),
                    'warehouse_id' => $this->agent,
                    'product_id' => $product_id,
                    'amount' => $product['combinations'][0]['amount']
                );
            } else {

            }

            if (!empty($_data)) {
                db_query("REPLACE ?:product_warehouses_inventory ?m", $_data);
            }
        }

        return $product_id;
    }

    private function validateProduct($product)
    {
        $correct = $allow_new = true;

        if (!empty($product['product_code']) && !empty($product['combinations']) && !empty($product['product_features'][BRAND_FEATURE_ID])) {
            foreach ($product['combinations'] as $comb) {
                if (empty($comb['amount']) || (empty($comb['name']) && count($product['combinations']) > 1)) {
                    $correct = false;
                }
            }
            if (empty($correct) || empty($product['price']) || empty($product['category_ids']) || empty($product['product'])) {
                $allow_new = false;
            }
        } else {
            $correct = $allow_new = false;
        }

        return array($correct, $allow_new);
    }

    protected function getFeed()
    {
        $result = Http::get($this->source);
        if (!empty($result)) {
            libxml_use_internal_errors(true);
            $this->feed = @simplexml_load_string($result);
        }
    }

    protected function getSyncSchema()
    {
        static $schema = array();

        if (empty($schema)) {
            $schema = fn_get_schema('sync', 'schema');
        }

        return $schema[$this->agent] ?? array();
    }
}
