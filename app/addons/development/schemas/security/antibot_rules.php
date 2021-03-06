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

$schema = array(
    'profiles' => array(
        'update' => array(
            'request_method' => 'POST',
            'verification_scenario' => 'register',
            'save_post_data' => array(
                'user_data',
            ),
            'rewrite_controller_status' => array(
                CONTROLLER_STATUS_REDIRECT,
                'profiles.add',
            ),
        ),
    ),

    'orders' => array(
        'track_request' => array(
            'request_method' => 'POST',
            'verification_scenario' => 'track_orders',
            'terminate_process' => true,
        ),
    ),

    'auth' => array(
        'login' => array(
            'request_method' => 'POST',
            'verification_scenario' => 'login',
            'save_post_data' => array(
                'user_login',
            ),
            'rewrite_controller_status' => array(
                CONTROLLER_STATUS_REDIRECT,
            ),
        ),
    ),


    'checkout' => array(
        'add_profile' => array(
            'request_method' => 'POST',
            'verification_scenario' => 'register',
            'save_post_data' => array(
                'user_data',
            ),
            'rewrite_controller_status' => array(
                CONTROLLER_STATUS_REDIRECT,
                'checkout.checkout?login_type=register',
            ),
        ),

        'customer_info' => array(
            'request_method' => 'POST',
            'verification_scenario' => 'checkout',
            'condition' => function($request_data) {
                return \Tygh\Registry::get('settings.Checkout.disable_anonymous_checkout') != 'Y'
                    && empty(Tygh::$app['session']['cart']['user_data']['email']);
            },
            'save_post_data' => array(
                'user_data',
            ),
            'rewrite_controller_status' => array(
                CONTROLLER_STATUS_REDIRECT,
                'checkout.checkout?login_type=guest',
            ),
        ),

    ),
);

return $schema;