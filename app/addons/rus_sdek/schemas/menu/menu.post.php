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

$schema['central']['orders']['items']['shippings.sdek.sdek_menu'] = array(
    'attrs' => array(
        'class'=>'is-addon'
    ),
    'href' => 'sdek_status.manage',
    'position' => 400,
    'subitems' => array(
        'shippings.sdek.status_title' => array(
            'href' => 'sdek_status.manage',
            'position' => 203
        ),
        'shippings.sdek.regenerate_cities' => array(
            'href' => 'rus_sdek.regenerate_cities',
            'position' => 204
        ),
    )
);

return $schema;
