© {if $smarty.const.TIME|date_format:"%Y" != $settings.Company.company_start_year}{$settings.Company.company_start_year}-{/if}{$smarty.const.TIME|date_format:"%Y"} {$settings.Company.company_name} {$settings.Company.company_phone}

{__("newsletter_footer_text_txt", ["[newsletter_email]" => $from_email])}

{if $unsubscribe_link}
{__("newsletter_footer_text_unsubscribe_txt", ["[unsubscribe]" => $unsubscribe_link])}
{/if}
{__("email_footer_text")}

{__("phone_working_hours_text", ["[hours]" => __("phone_working_hours")])}
{__("orders_working_hours_text", ["[hours]" => __("orders_working_hours")])}