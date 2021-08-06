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

class Customization
{
    public $racket = array();
    public $dialog_data = array();
    public $request = array();
    public $product_id = null;
    protected $auth = array();
    public $cart = array();
    public $cart_products = array();

    public function __construct($params)
    {
        if (empty($_SESSION['cart'])) {
            fn_clear_cart($_SESSION['cart']);
        }

        $this->request = $params;
        $this->auth = $_SESSION['auth'];

        if (!empty($this->request['cart_id']) && !empty($_SESSION['cart']['products'][$this->request['cart_id']])) {

            $this->product_id = $_SESSION['cart']['products'][$this->request['cart_id']]['product_id'];
//             $this->racket = &$_SESSION['add_product_request'][$this->product_id]['product'];

            $this->racket = array(
                'product_id' => $this->product_id,
                'amount' => $_SESSION['cart']['products'][$this->request['cart_id']]['amount']
            );
            if (!empty($_SESSION['cart']['products'][$this->request['cart_id']]['product_options'])) {
                $this->racket['product_options'] = $_SESSION['cart']['products'][$this->request['cart_id']]['product_options'];
            }

            $option = 3;
            if (!empty($_SESSION['cart']['products'][$this->request['cart_id']]['configuration'])) {
                if (count($_SESSION['cart']['products'][$this->request['cart_id']]['configuration']) == 1 && reset($_SESSION['cart']['products'][$this->request['cart_id']]['configuration'])['product_id'] == STRINGING_PRODUCT_ID) {
                    $option = 2;
                }
                $this->racket['configuration'] = array(
                    $option => $_SESSION['cart']['products'][$this->request['cart_id']]['extra']['configuration']
                );
            }

//             $this->dialog_data = &$_SESSION['add_product_request'][$this->product_id]['dialog_data'];

            $this->dialog_data = array(
                'option' => $option,
                'step' => STRINGING_GROUP_ID,
                'reload' => 1,
                'edit_configuration' => $this->request['cart_id'],
            );
        } else {

            $this->product_id = !empty($this->request['product_id']) ? $this->request['product_id'] : array_key_first($this->request['product_data']);

            $this->racket = &$_SESSION['add_product_request'][$this->product_id]['product'];
            if (empty($this->racket)) {
                $this->racket = array();
            }

            $this->dialog_data = &$_SESSION['add_product_request'][$this->product_id]['dialog_data'];
            if (empty($this->dialog_data)) {
                $this->dialog_data = array();
            }

        }

        if (!empty($this->request['sd_data'])) {
            parse_str($this->request['sd_data'], $_params);
            if (!empty($_params)) {
                $this->dialog_data = array_merge($this->dialog_data, $_params);
            }
        }

        $this->dialog_data['view'] = 'P';
        if (!empty($this->request['sd_change'])) {
            parse_str($this->request['sd_change'], $_params);
            if (!empty($_params)) {
                if (isset($_params['brand']) || isset($_params['page']) || isset($_params['step']) || isset($_params['productAdd'])) {
                    $this->dialog_data['view'] = 'L';
                }
                if (!empty($_params['step']) || !empty($_params['stepRemove'])) {
                    unset($this->dialog_data['page']);
                    unset($this->dialog_data['brand']);
                }

                $this->dialog_data = array_merge($this->dialog_data, $_params);
            }
        }
    }

    public function addProduct()
    {
        if (!empty($this->dialog_data['productAdd']) && !empty($this->dialog_data['step']) && !empty($this->dialog_data['option'])) {
//             if (empty($this->racket['configuration'][$this->dialog_data['option']][$this->dialog_data['step']]) || reset($this->racket['configuration'][$this->dialog_data['option']][$this->dialog_data['step']]['product_ids']) != $this->dialog_data['productAdd']) {
                $this->racket['configuration'][$this->dialog_data['option']][$this->dialog_data['step']] = array(
                    'is_separate' => true,
                    'product_ids' => array($this->dialog_data['productAdd']),
                    'options' => array()
                );
//             }
            if (!empty($this->request['product_data'][$this->product_id]['configuration'][$this->dialog_data['step']]['options'][$this->dialog_data['productAdd']])) {
                $this->racket['configuration'][$this->dialog_data['option']][$this->dialog_data['step']]['options'][$this->dialog_data['productAdd']] = $this->request['product_data'][$this->product_id]['configuration'][$this->dialog_data['step']]['options'][$this->dialog_data['productAdd']];
            }
            if ($this->dialog_data['step'] == STRINGING_GROUP_ID) {
                $this->racket['configuration'][$this->dialog_data['option']][STRINGING_TENSION_GROUP_ID] = array(
                    'is_separate' => true,
                    'product_ids' => array(STRINGING_PRODUCT_ID),
                    'options' => array()
                );
            }
            unset($this->request['product_data'][$this->product_id]['configuration'][$this->dialog_data['step']]);
            unset($this->dialog_data['productAdd']);
        }
    }

    public function removeProduct()
    {
        if (!empty($this->dialog_data['stepRemove']) && !empty($this->dialog_data['option'])) {
            unset($this->racket['configuration'][$this->dialog_data['option']][$this->dialog_data['stepRemove']]);
            if ($this->dialog_data['stepRemove'] == STRINGING_GROUP_ID) {
                unset($this->racket['configuration'][$this->dialog_data['option']][STRINGING_TENSION_GROUP_ID]);
                unset($this->request['product_data'][$this->product_id]['configuration'][STRINGING_TENSION_GROUP_ID]);
            }
            unset($this->request['product_data'][$this->product_id]['configuration'][$this->dialog_data['stepRemove']]);
            unset($this->dialog_data['stepRemove']);
            $this->dialog_data['step'] = 0;
        }
    }

    public function updateConf()
    {
        if (!empty($this->request['product_data'][$this->product_id])) {
            if (!empty($this->request['product_data'][$this->product_id]['configuration'])) {
                $configuration = $this->request['product_data'][$this->product_id]['configuration'];
                unset($this->request['product_data'][$this->product_id]['configuration']);
            }

            $this->racket = array_merge($this->racket, $this->request['product_data'][$this->product_id]);

            $steps = array(
                STRINGING_GROUP_ID => true,
                DAMPENER_GROUP_ID => true,
                OVERGRIP_GROUP_ID => true
            );

            if (!empty($configuration) && !empty($this->dialog_data['option'])) {
                foreach ($configuration as $gr_id => $opts) {
                    unset($steps[$gr_id]);
                    $this->racket['configuration'][$this->dialog_data['option']][$gr_id]['product_ids'] = $opts['product_ids'];
                    $this->racket['configuration'][$this->dialog_data['option']][$gr_id]['is_separate'] = $opts['is_separate'];
                    if (!empty($opts['options'])) {
                        $this->racket['configuration'][$this->dialog_data['option']][$gr_id]['options'] = $opts['options'];
                    }
                }
            }
            if (empty($this->dialog_data['step'])) {
                $this->dialog_data['step'] = !empty($steps) ? array_key_first($steps) : STRINGING_GROUP_ID;
            }
        }
    }

    public function initOptions()
    {
        $ids = array($this->product_id);
        $id_ref = array();
        if (!empty($this->racket['configuration'][$this->dialog_data['option']])) {
            foreach ($this->racket['configuration'][$this->dialog_data['option']] as $gr_id => $gr) {
                $id = reset($gr['product_ids']);
                $ids[] = $id;
                $id_ref[$id] = $gr_id;
            }
        }
        $params = array(
            'pid' => $ids,
        );
        list($products, $search) = fn_get_products($params);
        foreach ($products as $i => $prod) {
            if ($prod['product_id'] == $this->product_id) {
                if (isset($this->request['changed_option']) && array_key_first($this->request['changed_option']) == $this->product_id) {
                    $products[$i]['changed_option'] = reset($this->request['changed_option']);
//                 } elseif (isset($this->request['changed_option'])) {
//                     $products[$i]['changed_configuration_option'] = $this->request['changed_option'];
                }
                $products[$i]['selected_options'] = empty($this->racket['product_options']) ? array() : $this->racket['product_options'];
                if (!empty($this->racket['configuration'][$this->dialog_data['option']])) {
                    $products[$i]['selected_configuration'] = $this->racket['configuration'][$this->dialog_data['option']];
                }
                $parent_it = $i;
            } else {
                $products[$i]['original_id'] = $id_ref[$prod['product_id']];
                $products[$i]['virtual_parent_id'] = 0;
                $products[$i]['get_default_options'] = 'A';
                if (!empty($this->racket['configuration'][$this->dialog_data['option']][$id_ref[$prod['product_id']]]['options'][$prod['product_id']]['product_options'])) {
                    $products[$i]['selected_options'] = $this->racket['configuration'][$this->dialog_data['option']][$id_ref[$prod['product_id']]]['options'][$prod['product_id']]['product_options'];
                }
                if (isset($this->request['changed_option']) && array_key_first($this->request['changed_option']) == $prod['product_id']) {
                    $products[$i]['changed_option'] = reset($this->request['changed_option']);
                }
            }
        }
        foreach ($products as $i => $prod) {
            if (isset($products[$i]['virtual_parent_id'])) {
                $products[$i]['virtual_parent_id'] = $parent_it;
            }
        }

        fn_gather_additional_products_data($products, array('get_icon' => false, 'get_detailed' => false, 'get_options' => true, 'get_discounts' => true, 'get_features' => true, 'get_title_features' => true));
        $product = reset($products);

        if (!empty($product['selected_options'])) {
            $this->racket['product_options'] = $product['selected_options'];
        }
        if (!empty($product['configuration'])) {
            foreach ($product['configuration'] as $group_id => $group) {
                if (!empty($group['selected_options'])) {
                    $this->racket['configuration'][$this->dialog_data['option']][$group_id]['options'][$group['product_id']]['product_options'] = $group['selected_options'];
                }
            }
        }
        if (!empty($product['product_features'][R_STRINGS_FEATURE_ID]['value']) && $product['product_features'][R_STRINGS_FEATURE_ID]['value'] == 'Y') {
            $this->racket['is_strung'] = true;
            if ($this->dialog_data['step'] == STRINGING_GROUP_ID) {
                $this->dialog_data['step'] = DAMPENER_GROUP_ID;
            }
            $this->dialog_data['option'] = 3;
        } else {
            $this->racket['is_strung'] = false;
        }
        $this->racket['free_strings'] = !empty($product['free_strings']) ? true : false;
    }

    private function setNotification($previous_state, $prev_cart_products)
    {
        $added_products = array();
        if (md5(serialize($this->cart['products'])) != $previous_state && empty($this->cart['skip_notification'])) {
            $product_cnt = 0;
            foreach ($this->cart['products'] as $key => $data) {
                if (empty($prev_cart_products[$key]) || !empty($prev_cart_products[$key]) && $prev_cart_products[$key]['amount'] != $data['amount']) {
                    $added_products[$key] = $this->cart_products[$key];
                    $added_products[$key]['product_option_data'] = fn_get_selected_product_options_info($data['product_options']);
                    if (!empty($prev_cart_products[$key])) {
                        $added_products[$key]['amount'] = $data['amount'] - $prev_cart_products[$key]['amount'];
                    }
                    $product_cnt += $added_products[$key]['amount'];
                }
            }

            if (!empty($added_products)) {
                Registry::get('view')->assign('added_products', $added_products);
                if (Registry::get('config.tweaks.disable_dhtml') && Registry::get('config.tweaks.redirect_to_cart')) {
                    Registry::get('view')->assign('continue_url', (!empty($this->request['redirect_url']) && empty($this->request['appearance']['details_page'])) ? $this->request['redirect_url'] : $_SESSION['continue_url']);
                }
                Registry::get('view')->assign('highlight', true);
                $msg = Registry::get('view')->fetch('views/checkout/components/product_notification.tpl');
                fn_set_notification('I', __($product_cnt > 1 ? 'products_added_to_cart' : 'product_added_to_cart'), $msg);
                $this->cart['recalculate'] = true;
            } else {
                fn_set_notification('N', __('notice'), __('product_in_cart'));
            }
        }
    }

    public function getGroupRef()
    {
        $group_ref = array();
        foreach ($this->cart['products'] as $item_id => $pr_data) {
            $this->racket['product_options'] = $pr_data['product_options'];
            if (!empty($pr_data['configuration'])) {
                foreach ($pr_data['configuration'] as $c_item_id => $c_prod) {
                    if (!empty($this->cart_products[$item_id]['configuration'][$c_item_id]['group_id'])) {
                        $this->racket['configuration'][$this->dialog_data['option']][$this->cart_products[$item_id]['configuration'][$c_item_id]['group_id']]['options'][$c_prod['product_id']]['product_options'] = $c_prod['product_options'];
                        $group_ref[$this->cart_products[$item_id]['configuration'][$c_item_id]['group_id']] = $c_item_id;
                    }
                }
            }
        }

        return $group_ref;
    }

    public function resetState()
    {
        $this->racket = array();
        $this->dialog_data = array();
    }

    public function calculateCart()
    {
        $add_product = array(
            $this->product_id => $this->racket
        );
        if (!empty($add_product[$this->product_id]['configuration'])) {
            $add_product[$this->product_id]['configuration'] = !empty($add_product[$this->product_id]['configuration'][$this->dialog_data['option']]) ? $add_product[$this->product_id]['configuration'][$this->dialog_data['option']] : array();
        }

        $prev_cart_products = array();
        if (!empty($this->dialog_data['add'])) {
            $this->cart = & $_SESSION['cart'];
            if (!empty($this->dialog_data['edit_configuration'])) {
                fn_delete_cart_product($this->cart, $this->dialog_data['edit_configuration']);
                $this->cart['skip_notification'] = true;
            } else {
                $prev_cart_products = empty($this->cart['products']) ? array() : $this->cart['products'];
            }
        } else {
            $this->cart = array();
            fn_clear_cart($this->cart);
        }

        fn_add_product_to_cart($add_product, $this->cart, $this->auth, false, true);

        if (!empty($this->dialog_data['add'])) {
            $previous_state = md5(serialize($this->cart['products']));
            $this->cart['change_cart_products'] = true;
        }

        $this->cart['calculate_shipping'] = false;
        list ($this->cart_products, $product_groups) = fn_calculate_cart_content($this->cart, $this->auth, 'S', true, 'F', true);
        fn_save_cart_content($this->cart, $this->auth['user_id']);
        if (empty($this->dialog_data['add'])) {
            unset($_SESSION['notifications']);
        }

        fn_gather_additional_products_data($this->cart_products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => false));

        if (!empty($this->dialog_data['add'])) {

            $this->setNotification($previous_state, $prev_cart_products);
            unset($this->cart['skip_notification']);
        }
    }


    public static function displayCustomization($request)
    {
        $customization = new Customization($request);

        // Add new product to conf
        $customization->addProduct();

        // Remove product from conf
        $customization->removeProduct();

        // Update conf
        $customization->updateConf();

        if (!empty($customization->dialog_data['home'])) {
            unset($customization->dialog_data['home']);
            $customization->dialog_data['option'] = 0;
        }

        if (!empty($customization->racket)) {

            $customization->initOptions();

            // Calculate cart prices
            $customization->calculateCart();

            if (!empty($customization->dialog_data['add'])) {
                unset($customization->dialog_data['add']);
                if (Registry::get('config.tweaks.disable_dhtml') && Registry::get('config.tweaks.redirect_to_cart') && !defined('AJAX_REQUEST')) {
                    if (!empty($customization->request['redirect_url']) && empty($customization->request['appearance']['details_page'])) {
                        $_SESSION['continue_url'] = fn_url_remove_service_params($customization->request['redirect_url']);
                    }
                    unset($customization->request['redirect_url']);
                    return array(CONTROLLER_STATUS_OK, "checkout.cart");
                }

                if (defined('AJAX_REQUEST')) {
                    if (!empty($customization->dialog_data['edit_configuration'])) {
                        Registry::get('view')->assign('cart_products', $customization->cart_products);
                        Registry::get('view')->assign('cart', $customization->cart);
                        Registry::get('view')->assign('show_images', true);
                        Registry::get('view')->assign('product_groups', $customization->cart['product_groups']);
                        Registry::set('runtime.mode', 'cart');
                        Registry::get('view')->display('views/checkout/cart.tpl');
                    } else {
                        $dmode = fn_get_session_data('dmode');
                        if ($dmode == 'M') {
                            Registry::get('view')->display('addons/development/blocks/static_templates/top_block.tpl');
                            Registry::get('view')->display('addons/development/common/cart_panel.tpl');
                        } else {
                            $cart_content = array('snapping_id' => 'cart_content', 'properties' => array('products_links_type' => 'thumb', 'display_delete_icons' => 'Y', 'display_bottom_buttons' => 'Y'));
                            Registry::get('view')->assign('block', $cart_content);
                            Registry::get('view')->assign('force_items_deletion', true);
                            Registry::get('view')->display('blocks/cart_content.tpl');
                        }
                    }
                }
                $customization->resetState();

                exit;
            } else {
                $group_ref = $customization->getGroupRef();
                $racket = reset($customization->cart_products);
                Registry::get('view')->assign('group_ref', $group_ref);
                Registry::get('view')->assign('product', $racket);
            }
        }

        if ($customization->dialog_data['option'] == '3') {

            $params = $customization->dialog_data;
            unset($params['page']);
            $params['extend'] = array('categories', 'description');

            if ($customization->dialog_data['step'] == STRINGING_GROUP_ID) {
                $params['cid'] = STRINGS_CATEGORY_ID;
            } elseif ($customization->dialog_data['step'] == DAMPENER_GROUP_ID) {
                $params['cid'] = DAMPENERS_CATEGORY_ID;
            } elseif ($customization->dialog_data['step'] == OVERGRIP_GROUP_ID) {
                $params['cid'] = OVERGRIPS_CATEGORY_ID;
            }
            list($products, $search) = fn_get_products($params);

            fn_gather_additional_products_data($products, array(
                'get_icon' => false,
                'get_detailed' => false,
                'check_detailed' => true,
                'get_additional' => false,
                'check_additional' => true,
                'get_options' => true,
                'get_discounts' => true,
                'get_features' => false,
                'get_title_features' => true,
                'allow_duplication' => false,
            ));

            $selected_products = array();
            if (!empty($customization->racket['configuration'][$customization->dialog_data['option']])) {
                foreach ($customization->racket['configuration'][$customization->dialog_data['option']] as $gr_id => $conf) {
                    $selected_products = array_merge($selected_products, $conf['product_ids']);
                }
            }

            $brands = array();
            foreach ($products as $i => $product) {
                if (in_array($product['product_id'], $selected_products)) {
                    $products[$i]['is_selected'] = true;
                }
                if (!in_array($product['brand']['variant_id'], array_keys($brands))) {
                    $brands[$product['brand']['variant_id']] = $product['brand']['variant'];
                }
                if (!empty($customization->dialog_data['brand']) && $product['brand']['variant_id'] != $customization->dialog_data['brand']) {
                    unset($products[$i]);
                }
            }
            $search['items_per_page'] = 25;
            $search['total_items'] = count($products);
            $search['page'] = !empty($customization->dialog_data['page']) ? $customization->dialog_data['page'] : 1;
            $products = array_slice($products, $search['items_per_page'] * ($search['page'] - 1), $search['items_per_page']);

            Registry::get('view')->assign('brands', $brands);
            Registry::get('view')->assign('search', $search);
            Registry::get('view')->assign('products', $products);
            Registry::get('view')->assign('current_url', fn_url('racket_customization.view?product_id=' . $customization->product_id));

        }
        Registry::get('view')->assign('product_id', $customization->product_id);
        Registry::get('view')->assign('racket', $customization->racket);

        if (!empty($customization->dialog_data['reload'])) {
            unset($customization->dialog_data['reload']);
            Registry::get('view')->assign('dialog_data', $customization->dialog_data);
            $msg = Registry::get('view')->fetch('addons/development/views/racket_customization/customization.tpl');
            if (!empty($customization->dialog_data['option']) && $customization->dialog_data['option'] == '3') {
                if (fn_get_session_data('dmode') != 'M') {
                    $window_size = 'notification-content-full-window-padded';
                } else {
                    $window_size = 'notification-content-full-window';
                }
                if (empty($customization->dialog_data['edit_configuration']) && empty($customization->racket['is_strung'])) {
                    $title = '<div class="ty-customization-menu cm-sd-option" data-home="1" data-reload="true"></div><div class="ty-customization-title">' . __('racket_customization_dialog_title') . '</div>';
                } else {
                    $title = '<div class="ty-customization-title">' . __('racket_customization_dialog_title') . '</div>';
                }
                fn_set_notification('I', $title, $msg, 'K', serialize(array('dialog_class' => 'notification-content-stringing notification-content-customization ' . $window_size)));
            } elseif (!empty($customization->dialog_data['option']) && $customization->dialog_data['option'] == '2') {
                $title = '<div class="ty-customization-menu cm-sd-option" data-home="1" data-reload="true"></div><div class="ty-customization-title">' . __('racket_customization_dialog_title') . '</div>';
                if (fn_get_session_data('dmode') != 'M') {
                    $window_size = '';
                } else {
                    $window_size = 'notification-content-condition-sized-window';
                }
                fn_set_notification('I', $title, $msg, 'K', serialize(array('dialog_class' => 'notification-content-stringing ' . $window_size)));
            } else {
                if (fn_get_session_data('dmode') != 'M') {
                    $window_size = '';
                } else {
                    $window_size = 'notification-content-condition-sized-window';
                }
                fn_set_notification('I', '<div class="ty-customization-title">' . __('racket_unstrung_dialog_title') . '</div>', $msg, 'K', serialize(array('dialog_class' => 'notification-content-stringing ' . $window_size)));
            }
            Registry::get('view')->display('addons/development/views/racket_customization/customization.tpl');
        } else {
            Registry::get('view')->assign('dialog_data', $customization->dialog_data);
            Registry::get('view')->display('addons/development/views/racket_customization/customization.tpl');
        }
    }
}
