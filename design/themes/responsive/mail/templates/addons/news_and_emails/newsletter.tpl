{include file="addons/news_and_emails/letter_header.tpl"}

{if $firstname}{__("dear")} <b>{$firstname|fn_convert_case}</b>,<br />{/if}

<div valign="middle" style="display:inline-block;vertical-align:middle;max-width:638px;">
<table style="border:none;border-collapse:collapse;padding:0px;margin:0px;" cellspacing="0" cellpadding="0" border="0">
    <tbody>
    {foreach from=$body.html item="n_body" name="newsletters"}
        {if $smarty.foreach.newsletters.iteration == 2}
            <tr><td style="height:0;padding:0;font-size:0;border-bottom: 5px solid #ebebeb;"></td></tr>
            <tr><td style="height:30px;padding:0;border:0;font-size: 22px;font-weight: bold;" height="10">{__("post_newsletter_text")}:</td></tr>
        {/if}
        <tr>
            <td style="max-width:638px;padding:0;vertical-align:middle;text-align: left;font-size: 0;" valign="middle">
                {$n_body nofilter}
            </td>
        </tr>
        <tr><td style="height:10px;padding:0;font-size:0;border:0" height="10"></td></tr>
    {/foreach}
    </tbody>
</table>
</div>

{include file="addons/news_and_emails/letter_footer.tpl"}