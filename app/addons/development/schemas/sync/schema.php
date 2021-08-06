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

$scheme[DRIADA_WAREHOUSE_ID] = array(
    'product_code' => array(
        'value' => 'vendorCode',
        'type' => 'field'
    ),
    'price' => array(
        'value' => 'price',
        'type' => 'field'
    ),
    'net_cost' => array(
        'value' => 'priceopt',
        'type' => 'field'
    ),
    'net_currency_code' => array(
        'value' => 'RUB',
        'type' => 'const'
    ),
    'category_ids' => array(
        'value' => 'categoryId',
        'type' => 'field',
        'post_func' => 'parseAgentCategory'
    ),
    'product' => array(
        'value' => 'model',
        'type' => 'field'
    ),
    'full_description' => array(
        'value' => 'description',
        'type' => 'field'
    ),
    'raw_features' => array(
        'type' => 'array',
        'value' => 'param',
        'key' => 'name'
    ),
    'images' => array(
        'type' => 'attribute',
        'value' => 'pictures',
    ),
    'combinations' => array(
        'type' => 'combinations',
        'post_func' => 'parseCombination',
    ),
    'referral_url' => array(
        'type' => 'field',
        'value' => 'url',
    ),
    'show_stock' => array(
        'value' => 'N',
        'type' => 'const'
    ),
    'price_mode' => array(
        'value' => 'S',
        'type' => 'const'
    ),
);

return $scheme;
