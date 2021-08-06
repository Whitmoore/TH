<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'get_product_data_post',
    'get_product_data',
    'update_product_post',
    'get_products',
    'get_products_post'
);
