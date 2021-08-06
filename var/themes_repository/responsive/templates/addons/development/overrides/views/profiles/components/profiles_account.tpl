{if !$nothing_extra}
    {include file="common/subheader.tpl" title=__("user_account_info")}
{/if}

{hook name="profiles:account_info"}
    {if $settings.General.use_email_as_login != "Y"}
        <div class="ty-control-group ty-profile-field__item">
            <input id="user_login_profile" type="text" name="user_data[user_login]" size="32" placeholder="{__("username")}" maxlength="32" value="{$user_data.user_login}" class="ty-input-text" />
            <label for="user_login_profile" class="ty-control-group__title cm-required cm-trim">{__("username")}</label>
        </div>
    {/if}

    {if $settings.General.use_email_as_login == "Y" || $nothing_extra || $runtime.checkout}
        <div class="ty-position-relative">
        <div class="ty-control-group ty-profile-field__item {if $runtime.mode == 'update'}ty-profile-email{/if}">
            <input type="text" id="email" name="user_data[email]" size="32" maxlength="128" placeholder="{__("email")}" value="{$user_data.email}" class="ty-input-text" {if $runtime.mode != "update"}autofocus{/if} />
            <label for="email" class="ty-control-group__title cm-required cm-email cm-trim">{__("email")}</label>
        </div>
        {if $user_data.user_id}
            <div id="email_confirmation">
                {if $user_data.email_confirmed == 'Y'}
                    <div class="ty-email-confirmation__yes-block">
                        <div class="ty-email-confirmation_yes"><div class="ty-email-confirmation_yes-text">{__("confirmed")}</div></div>
                    </div>
                {else}
                    <div class="ty-email-confirmation__not-block">
                        <div class="ty-email-confirmation_not"><div class="ty-email-confirmation_not-text">
                            {if $user_data.confirmation_sent == 'Y'}
                                    {if $user_data.mail_server}
                                        <a href="{$user_data.mail_server}" target="_blank" class="ty-btn ty-btn__primary">{__("check_email")}</a>
                                    {else}
                                        <span class="ty-email-confirmation_sent">{__("confirmation_sent_check_email")}.</span> 
                                    {/if}
                                    <a class="cm-ajax" href="{"profiles.send_email_confirmation?user_id=`$user_data.user_id`"|fn_url}" data-ca-target-id="email_confirmation">{__("confirm_email_again")}?</a>
                            {else}
                                <a class="cm-ajax ty-btn ty-btn__tertiary" href="{"profiles.send_email_confirmation?user_id=`$user_data.user_id`"|fn_url}" data-ca-target-id="email_confirmation">{__("confirm_email")}</a>
                            {/if}
                        </div></div>
                    </div>
                {/if}
            <!--email_confirmation--></div>
        {/if}
        </div>
    {/if}

    <div class="ty-control-group ty-profile-field__item">
        <input type="password" id="password1" name="user_data[password1]" size="32" maxlength="32" placeholder="{__("password")}" value="{if $runtime.mode == "update"}            {/if}" class="ty-input-text cm-autocomplete-off" />
        <label for="password1" class="ty-control-group__title cm-required cm-password">{__("password")}</label>
    </div>
{/hook}