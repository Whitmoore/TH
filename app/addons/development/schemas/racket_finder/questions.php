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
    'age' => array(
        'type' => 'input',
        'options' => array(
            'max_length' => 2,
            'mask' => '99',
            'placeholder' => __('full_age')
        ),
        'title' => __('your_age')
    ),
    'height' => array(
        'type' => 'input',
        'options' => array(
            'max_length' => 3,
            'mask' => '999',
            'placeholder' => __('cm')
        ),
        'title' => __('your_height'),
        'conditions' => array(
            'age' => 'fn_is_kid'
        )
    ),
    'gender' => array(
        'type' => 'select',
        'title' => __('your_gender'),
        'variants' => array(
            'male' => __('male'),
            'female' => __('female'),
        )
    ),
    'skill' => array(
        'type' => 'select',
        'title' => __('your_skill_level'),
        'variants' => array(
            'beginner' => __('skill_level_beginner'),
            'intermediate' => __('skill_level_intermediate'),
            'advanced' => __('skill_level_advanced'),
        )
    ),
    'frequency' => array(
        'type' => 'select',
        'title' => __('your_frequency'),
        'variants' => array(
            'year' => __('frequency_year'),
            'month' => __('frequency_month'),
            'week' => __('frequency_week'),
        )
    ),
    'power' => array(
        'type' => 'select',
        'title' => __('power'),
        'variants' => array(
            'low' => __('power_low'),
            'moderate' => __('power_moderate'),
            'high' => __('power_high'),
        ),
        'conditions' => array(
            'age' => 'fn_is_adult',
            'height' => 'fn_is_tall'
        )
    ),
    'expectations' => array(
        'type' => 'select',
        'title' => __('your_expectations'),
        'variants' => array(
            'cheap' => __('expectations_cheap'),
            'good' => __('expectations_good_price'),
            'best' => __('expectations_best'),
        )
    ),
);

return $schema;
