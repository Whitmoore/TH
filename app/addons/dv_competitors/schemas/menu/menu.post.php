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

use \Tygh\Registry;

$schema['central']['products']['items']['competitors'] = array(
    'href' => 'competitors.manage',
    'position' => 220,
    'subitems' => array(
        'competitors' => array(
            'href' => 'competitors.manage',
            'position' => 221
        ),
        'competitive_prices' => array(
            'href' => 'competitors.prices',
            'position' => 222
        ),
    )
);

return $schema;
