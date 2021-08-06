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

$scheme['conditions']['warehouses'] = array(
    'operators' => array ('eq', 'neq'),
    'type' => 'select',
    'variants_function' => array('fn_get_simple_warehouses'),
    'field_function' => array('fn_promotions_check_warehouses', '#this', '@product'),
    'zones' => array('catalog')
);
$scheme['conditions']['promo_codes'] = array(
    'type' => 'list',
    'field_function' => array('fn_promotion_validate_promo_code', '#this', '@cart', '#id'),
    'zones' => array('cart'),
    'applicability' => array( // applicable for "positive" groups only
        'group' => array(
            'set_value' => true
        ),
    ),
);
$scheme['conditions']['product_review'] = array(
    'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_product_review', '#this', '@auth', '#id'),
    'zones' => array('cart', 'catalog'),
    'applicability' => array( // applicable for "positive" groups only
        'group' => array(
            'set_value' => true
        ),
    ),
);
$scheme['conditions']['store_review'] = array(
    'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_store_review', '#this', '@auth', '#id'),
    'zones' => array('cart', 'catalog'),
    'applicability' => array( // applicable for "positive" groups only
        'group' => array(
            'set_value' => true
        ),
    ),
);
$scheme['conditions']['no_list_discount'] = array(
    'type' => 'statement',
    'field_function' => array('fn_promotion_validate_no_list_discount', '#this', '@product', '#id'),
    'zones' => array('catalog'),
    'applicability' => array( // applicable for "positive" groups only
        'group' => array(
            'set_value' => true
        ),
    ),
);
$scheme['conditions']['no_catalog_discount'] = array(
    'type' => 'statement',
    'field_function' => array('fn_promotion_validate_no_catalog_discount', '#this', '@cart', '@cart_products', '#id'),
    'zones' => array('cart'),
    'applicability' => array( // applicable for "positive" groups only
        'group' => array(
            'set_value' => true
        ),
    ),
);
$scheme['conditions']['free_strings'] = array(
    'type' => 'statement',
    'field_function' => array('fn_promotion_validate_free_strings', '#this', '@cart', '@cart_products', '#id'),
    'zones' => array('cart'),
    'applicability' => array( // applicable for "positive" groups only
        'group' => array(
            'set_value' => true
        ),
    ),
);
$scheme['conditions']['catalog_coupon_code'] = array(
    'operators' => array ('eq', 'in'),
    // 'cont' - 'contains' was removed as ambiguous, but you can uncomment it back
    //'operators' => array ('eq', 'cont', 'in'),
    'type' => 'input',
    'field_function' => array('fn_promotion_validate_catalog_coupon', '#this', '@product', '#id'),
    'zones' => array('catalog'),
    'applicability' => array( // applicable for "positive" groups only
        'group' => array(
            'set_value' => true
        ),
    ),
);
$scheme['conditions']['ip_city'] = array(
    'operators' => array ('in', 'nin'),
    'type' => 'city_textbox',
    'field_function' => array('fn_promotion_validate_ip_city', '#this'),
    'zones' => array('cart', 'catalog')
);
$scheme['conditions']['ip_state'] = array(
    'operators' => array ('in', 'nin'),
    'type' => 'state_selectbox',
    'variants_function' => array('fn_destination_get_states', CART_LANGUAGE),
    'field_function' => array('fn_promotion_validate_ip_state', '#this'),
    'zones' => array('cart', 'catalog')
);
$scheme['bonuses']['cart_product_discount'] = array (
    'function' => array('fn_promotion_apply_cart_mod_rule', '#this', '@cart', '@auth', '@cart_products'),
    'discount_bonuses' => array('to_percentage', 'by_percentage', 'to_fixed', 'by_fixed'),
    'zones' => array('cart'),
);
$scheme['bonuses']['discount_on_products']['function'] = array('fn_promotion_apply_cart_mod_rule', '#this', '@cart', '@auth', '@cart_products');
$scheme['bonuses']['discount_on_categories']['function'] = array('fn_promotion_apply_cart_mod_rule', '#this', '@cart', '@auth', '@cart_products');
    
return $scheme;
