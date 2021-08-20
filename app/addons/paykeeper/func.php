<?php



if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_paykeeper_delete_payment_processors()
{
    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id IN (SELECT payment_id FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = 'paykeeper.php'))");
    db_query("DELETE FROM ?:payments WHERE processor_id IN (SELECT processor_id FROM ?:payment_processors WHERE processor_script = 'paykeeper.php')");
    db_query("DELETE FROM ?:payment_processors WHERE processor_script = 'paykeeper.php'");
}