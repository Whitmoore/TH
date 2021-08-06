{** block-description:my_account **}

{capture name="title"}
    {*<a class="ty-account-info__title" href="{"profiles.update"|fn_url}">*}
        <div class="ty-top-block__cell-content"><i class="ty-icon-profile"></i>&nbsp;</div>
    {*</a>*}
{/capture}

<div class="ty-dropdown-box__content-inner" id="account_info_{$block.snapping_id}">
    {assign var="return_current_url" value=$config.current_url|escape:url}
    {if $auth.user_id}
        <div class="ty-dropdown-box__content-title">
            {if $user_info.firstname || $user_info.lastname}
                {$user_info.firstname} {$user_info.lastname}
            {else}
                {if $settings.General.use_email_as_login == 'Y'}
                    {$user_info.email}
                {else}
                    {$user_info.user_login}
                {/if}
            {/if}
        </div>
    {*elseif $user_data.firstname || $user_data.lastname}
        {$user_data.firstname} {$user_data.lastname}
    {elseif $settings.General.use_email_as_login == 'Y' && $user_data.email}
        >{$user_data.email}
    {elseif $settings.General.use_email_as_login != 'Y' && $user_data.user_login}
        {$user_data.user_login*}
    {/if}
    <ul class="ty-account-info">
        {hook name="profiles:my_account_menu"}
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="{"profiles.update"|fn_url}" rel="nofollow" >{__("my_account")}</a></li>
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="{"orders.search"|fn_url}" rel="nofollow">{__("orders")}</a></li>
        {/hook}

        {if "MULTIVENDOR"|fn_allowed_for && $settings.Vendors.apply_for_vendor == "Y" && !$user_info.company_id}
            <li class="ty-account-info__item ty-dropdown-box__item"><a class="ty-account-info__a underlined" href="{"companies.apply_for_vendor?return_previous_url=`$return_current_url`"|fn_url}" rel="nofollow">{__("apply_for_vendor_account")}</a></li>
        {/if}
    </ul>

    {if $settings.Appearance.display_track_orders == 'Y'}
        <div class="ty-account-info__orders updates-wrapper track-orders" id="track_orders_block_{$block.snapping_id}">
            <form action="{""|fn_url}" method="get" class="cm-ajax cm-ajax-full-render" name="track_order_quick">
                <input type="hidden" name="result_ids" value="track_orders_block_*" />
                <input type="hidden" name="return_url" value="{$smarty.request.return_url|default:$config.current_url}" />

                <div class="ty-account-info__orders-txt">{__("track_my_order")}</div>

                <div class="ty-account-info__orders-input ty-control-group ty-input-append">
                    <label for="track_order_item{$block.snapping_id}" class="cm-required hidden">{__("track_my_order")}</label>
                    <input type="text" size="20" class="ty-input-text cm-hint" id="track_order_item{$block.snapping_id}" name="track_data" value="" placeholder="{__("order_id")}{if !$auth.user_id}/{__("email")}{/if}"/>
                    {include file="buttons/go.tpl" but_name="orders.track_request" alt=__("go")}
                    {include file="common/image_verification.tpl" option="track_orders" align="left" sidebox=true}
                </div>
            </form>
        <!--track_orders_block_{$block.snapping_id}--></div>
    {/if}
    <div class="ty-account-info__buttons buttons-container">
        {if $auth.user_id}
            <a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" rel="nofollow" class="ty-btn__reg-link">{__("sign_out")}</a>
        {else}
            <a href="{if $runtime.controller == "auth" && $runtime.mode == "login_form"}{$config.current_url|fn_url}{else}{"auth.login_form?return_url=`$return_current_url`"|fn_url}{/if}" {if $settings.Security.secure_auth != "Y"} data-ca-target-id="login_block{$block.snapping_id}" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__secondary"{else} class="ty-btn ty-btn__primary"{/if} rel="nofollow">{__("sign_in")}</a>
            <a href="{"profiles.add"|fn_url}" rel="nofollow" class="ty-btn__reg-link">{__("register")}</a>
            {if $settings.Security.secure_auth != "Y"}
                <div  id="login_block{$block.snapping_id}" class="hidden" title="{__("sign_in")}">
                    <div class="ty-login-popup">
                        {include file="views/auth/login_form.tpl" style="popup" id="popup`$block.snapping_id`"}
                    </div>
                </div>
            {/if}
        {/if}
    </div>
<!--account_info_{$block.snapping_id}--></div>