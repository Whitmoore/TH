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
use Tygh\Settings;
use Tygh\BackendMenu;
use Tygh\Navigation\Breadcrumbs;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$imported = db_get_field("SELECT COUNT(product_id) FROM ?:products WHERE is_imported = 'Y' AND approval_status = 'P'");

if (!empty($imported) && !($_REQUEST['dispatch'] == 'products.manage' && !empty($_REQUEST['approval_status']) && $_REQUEST['approval_status'] == 'P')) {
    if (!fn_notification_exists('extra', 'approve_imported')) {
        // fn_set_notification('N', __('notice'), __('imported_products_need_approval', array(
        //     '[number]' => $imported,
        //     '[products_link]' => fn_url('products.manage?approval_status=P', 'A')
        // )), 'S', 'approve_imported');
    }
} else {
    fn_delete_notification('approve_imported');
}
