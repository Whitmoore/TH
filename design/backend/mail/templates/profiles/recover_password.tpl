{include file="common/letter_header.tpl"}

{__("text_confirm_passwd_recovery")}:<br /><br />

<div style="display: inline-block; text-align: center; width: 100%;">
<a href="{"auth.recover_password?ekey=`$ekey`"|fn_url:$zone:'http'}" style="-moz-user-select: none; border: 1px solid transparent; cursor: pointer; display: inline-block; font-family: Play,sans-serif; font-style: normal; font-weight: normal; line-height: 1.42857; margin-bottom: 0px; outline: 0px none; text-align: center; text-decoration: none; vertical-align: middle; background: rgb(255, 191, 0) none repeat scroll 0px 0px; color: rgb(255, 255, 255); width: 300px; padding: 13px 14px; font-size: 20px;">{__("recover_password")}</a>
</div>

{include file="common/letter_footer.tpl"}