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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_REQUEST['technology_id'] = empty($_REQUEST['technology_id']) ? 0 : $_REQUEST['technology_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'technology_data',
        'technologies_data'
    );

    //
    // Create/update technology
    //
    if ($mode == 'update') {

        $technology_id = fn_update_technology($_REQUEST['technology_data'], $_REQUEST['technology_id']);

        if (!empty($technology_id)) {
            fn_attach_image_pairs('technology_main', 'technology', $technology_id);

            $suffix = ".update?technology_id=$technology_id" . (!empty($_REQUEST['technology_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['technology_data']['block_id'] : "");
        } else {
            $suffix = '.manage';
        }
    }

    //
    // Processing deleting of multiple technology elements
    //
    if ($mode == 'm_delete') {

        if (isset($_REQUEST['technology_ids'])) {
            foreach ($_REQUEST['technology_ids'] as $v) {
                if (fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('ULTIMATE') && fn_check_company_id('technologies', 'technology_id', $v))) {
                    fn_delete_technology($v);
                }
            }
        }

        unset($_SESSION['technology_ids']);

        fn_set_notification('N', __('notice'), __('text_technologies_have_been_deleted'));
        $suffix = ".manage";
    }

    if ($mode == 'm_update') {
        if (!empty($_REQUEST['technologies_data'])) {
            foreach ($_REQUEST['technologies_data'] as $k => $v) {
                fn_update_technology($v, $k);
            }
        }
        $suffix = ".manage";
    }
    
    return array(CONTROLLER_STATUS_OK, "technologies$suffix");
}

//
// 'Add new technology' page
//
if ($mode == 'add') {

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'products' => array (
            'title' => __('products'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    ));
    // [/Page sections]

//
// 'technology update' page
//
} elseif ($mode == 'update') {

    $technology_id = $_REQUEST['technology_id'];
    // Get current technology data
    $technology_data = fn_get_technology_data($technology_id);

    if (empty($technology_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    // [Page sections]
    $tabs = array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'products' => array (
            'title' => __('products'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    );

    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]
    Registry::get('view')->assign('technology_data', $technology_data);

}
//
// Delete technology
//
elseif ($mode == 'delete') {

    if (!empty($_REQUEST['technology_id'])) {
        fn_delete_technology($_REQUEST['technology_id']);
    }

    fn_set_notification('N', __('notice'), __('text_technology_has_been_deleted'));

    return array(CONTROLLER_STATUS_REDIRECT, "technologies.manage");

//
// 'Management' page
//
} elseif ($mode == 'manage' || $mode == 'picker') {

    $params = $_REQUEST;
    list($technologies, $search) = fn_get_technologies($params);

    Registry::get('view')->assign('technologies', $technologies);
    Registry::get('view')->assign('search', $search);
    
}

//
// Categories picker
//
if ($mode == 'picker') {
    Registry::get('view')->display('addons/development/pickers/technologies/picker_contents.tpl');
    exit;
}
