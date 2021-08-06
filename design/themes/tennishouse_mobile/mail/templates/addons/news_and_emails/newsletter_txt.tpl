{if $firstname}{__("dear")} {$firstname|fn_convert_case},{/if}


{$body.txt nofilter}

{include file="addons/news_and_emails/letter_footer_txt.tpl"}