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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define("MULTISHIP_API", true);
fn_define('ORDER_DRAFT_STATUS', -2);

fn_define("MULTISHIP_STATUS_NONE", 0);
fn_define("MULTISHIP_STATUS_CREATED", 1);
fn_define("MULTISHIP_STATUS_NEW", 2);
fn_define("MULTISHIP_STATUS_SENT", 3);
fn_define("MULTISHIP_STATUS_NOT_ARRIVED", 4);
fn_define("MULTISHIP_STATUS_PREPARED", 5);
fn_define("MULTISHIP_STATUS_INTRANSIT", 6);
fn_define("MULTISHIP_STATUS_STORED", 7);
fn_define("MULTISHIP_STATUS_DELIVERED", 8);
fn_define("MULTISHIP_STATUS_PREPAREDTOSENDER", 9);
fn_define("MULTISHIP_STATUS_RETURNEDTODELIVERY", 10);
fn_define("MULTISHIP_STATUS_RETURNEDTOSENDER", 11);
fn_define("MULTISHIP_STATUS_RETURNEDTOSHOP", 12);
fn_define("MULTISHIP_STATUS_ERROR", 13);
fn_define("MULTISHIP_STATUS_LOST", 14);

// Перечень возможных ошибок при работе с API
fn_define("MULTISHIP_ERROR_SUCCESS", "");
fn_define("MULTISHIP_ERROR_WRONG_PARAM", "Неверный тип входных данных!");
fn_define("MULTISHIP_ERROR_VALIDATION_EMPTY", "Ошибка валидации: заполнены не все поля!");
fn_define("MULTISHIP_ERROR_VALIDATION", "Ошибка валидации: поля заполнены неверно!");
fn_define("MULTISHIP_ERROR_CONFIG", "Ошибка: не заполнен файл настроек! Для продолжения работы запросите, пожалуйста, в MultiShip ключи доступа к API и разместите их в файле /config/config.php!");

fn_register_hooks(
    'calculate_cart_taxes_pre'
);
