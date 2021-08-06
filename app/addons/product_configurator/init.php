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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'clone_product',
    'generate_cart_id',
    'get_products',
    'prepare_product_quick_view',
    'pre_add_to_cart',
    'post_add_to_cart',
    'get_cart_product_data',
    'add_to_cart',
    'pre_add_to_wishlist',
    'post_add_to_wishlist',
    'gather_additional_product_data_before_discounts',
    'buy_together_restricted_product',
    'calculate_options',
    'amazon_products',
    'update_product_pre',
    'add_product_to_cart_check_price',
    'update_cart_products_post',
    'get_additional_information',
    'create_order_details',
    'get_order_items_info_post',
    'reorder_item',
    'change_order_status',
    'gather_additional_products_data_pre',
    'gather_additional_product_data_post',
    'gather_additional_product_data_params',
    'gather_additional_product_data_before_options',
    'gather_additional_products_data_post',
    'calculate_cart_items_after_promotions',
    'calculate_cart_items_pre',
    'update_cart_data_post',
    'form_cart',
    'calculate_cart_items',
    'update_cart_products_pre',
    'reorder',
    'post_check_amount_in_stock'
);
