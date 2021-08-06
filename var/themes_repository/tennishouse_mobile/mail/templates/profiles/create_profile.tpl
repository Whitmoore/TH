{include file="common/letter_header.tpl"}

{__("dear")} {if $user_data.firstname}<b>{$user_data.firstname|fn_convert_case}</b>{/if},<br><br>

{if !$confirm_email}
    {__("create_profile_notification_header")} {$company_data.company_name}.<br><br>

    {hook name="profiles:create_profile"}
    {/hook}

    {include file="profiles/profiles_info.tpl" created=true}<br /><br />
{/if}

{__("text_confirm_email")}:<br /><br />

<div style="display: inline-block; text-align: center; width: 100%;">
<a href="{"profiles.confirm_email?ekey=`$ekey`"|fn_url:$zone:'http'}" style="-moz-user-select: none; border: 1px solid transparent; cursor: pointer; display: inline-block; font-family: Play,sans-serif; font-style: normal; font-weight: normal; line-height: 1.42857; margin-bottom: 0px; outline: 0px none; text-align: center; text-decoration: none; vertical-align: middle; background: rgb(255, 191, 0) none repeat scroll 0px 0px; color: rgb(255, 255, 255); width: 300px; padding: 13px 14px; font-size: 20px;">{__("confirm_email")}</a>
</div><br /><br /><br />

<div>
{__("text_decline_email")} <a href="{"profiles.decline_email?ekey=`$ekey`"|fn_url:$zone:'http'}">{__("not_my_account")}</a>
</div>

{include file="common/letter_footer.tpl"}