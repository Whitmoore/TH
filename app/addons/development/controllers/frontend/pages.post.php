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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

//
// View page details
//
if ($mode == 'view') {

    $page = Registry::get('view')->gettemplatevars('page');

    if (in_array(LC_RACKETS_PAGE_ID, explode('/', $page['id_path']))) {
        Registry::get('view')->assign('show_racket_finder', true);
    }
    if (!empty($page['image'])) {
        Registry::get('view')->assign('image_title', $page['image']);
        Registry::get('view')->assign('image_title_text', $page['page']);
    }
    if (empty($page['description'])) {
        $page['children'] = db_get_fields("SELECT page_id FROM ?:pages WHERE parent_id = ?i ORDER BY position ASC", $page['page_id']);
        if (!empty($page['children'])) {
            return array(CONTROLLER_STATUS_REDIRECT, 'pages.view?page_id=' . reset($page['children']));
        }
    }
    Registry::get('view')->assign('seo_canonical', array(
        'current' => fn_url('pages.view?page_id=' . $_REQUEST['page_id'])
    ));
}
