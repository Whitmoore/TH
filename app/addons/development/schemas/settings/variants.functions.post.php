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

use Tygh\Registry;

/**
 * Gets settings variants for the option 'Image verification: Use for'
 *
 * @return array Available objects
 */
function fn_settings_variants_image_verification_use_for()
{
    $objects = array(
        'login' => __('use_for_login'),
        'register' => __('use_for_register'),
        'checkout' => __('use_for_checkout'),
        'track_orders' => __('use_for_track_orders'),
    );

    /**
     * Add objects that should use 'Image verification'
     *
     * @param array $objects Available objects
     */
    fn_set_hook('settings_variants_image_verification_use_for', $objects);

    return $objects;
}
