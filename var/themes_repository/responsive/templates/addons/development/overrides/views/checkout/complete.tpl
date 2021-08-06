<div class="ty-checkout-complete__order-success">
    <p>{__("text_order_placed_successfully", ["[phone]" => $order_info.phone])}</p>
    {if $order_info.delivery_time}
        <div class="ty-delivery-time__checkout">
            <div class="ty-process-time__checkout">{__("order_process_time")}: 1 {__("workdays")}</div>
            {if $order_info.delivery_time}
                <div class="ty-service-delivery-time__checkout">{__("destination_delivery_time", ["[city]" => $order_info.s_city])}: {$order_info.delivery_time} {__("workdays")}</div>
            {/if}
        </div>
    {/if}
    <p>{__("text_order_placed_successfully_question")}</p>
    {*if $order_info}
        {if $order_info.child_ids}
            <a href="{"orders.search?period=A&order_id=`$order_info.child_ids`"|fn_url}">{__("order_details")}</a>.
        {else}
            <a href="{"orders.details?order_id=`$order_info.order_number`"|fn_url}">{__("order_details")}</a>.
        {/if}
    {/if*}
</div>

{if $order_info && $settings.General.allow_create_account_after_order == "Y" && !$auth.user_id}
<div class="ty-checkout-complete__create-account">
    <h3 class="ty-subheader">{__("create_account")}</h3>
    <div class="ty-login">
        <form name="order_register_form" action="{""|fn_url}" method="post">
            <input type="hidden" name="order_id" value="{$order_info.order_id}" />

            {if $settings.General.use_email_as_login != "Y"}
            <div class="ty-control-group">
                <label for="user_login_profile" class="ty-control-group__label ty-login__filed-label cm-required">{__("username")}</label>
                <input id="user_login_profile" type="text" name="user_data[user_login]" size="32" maxlength="32" placeholder="{__("username")}" value="" class="ty-login__input" />
            </div>
            {/if}

            <div class="ty-control-group">
                <label for="password1" class="ty-control-group__title ty-login__filed-label cm-required cm-password">{__("password")}</label>
                <input type="password" id="password1" name="user_data[password1]" size="32" maxlength="32" placeholder="{__("password")}" value="" class="cm-autocomplete-off ty-login__input" />
            </div>

            <div class="buttons-container clearfix">
                <p>{include file="buttons/button.tpl" but_name="dispatch[checkout.create_profile]" but_text=__("create")}</p>
            </div>
        </form>
        </div>
    </div>
    {if $order_info.payment_method.instructions}
        <div class="ty-checkout-complete__login-info">
            {hook name="checkout:payment_instruction"}
                <div class="ty-login-info">
                    <h4 class="ty-subheader">{__("payment_instructions")}</h4>
                    <div class="ty-wysiwyg-content">
                        {$order_info.payment_method.instructions nofilter}
                    </div>
                </div>
            {/hook}
        </div>
    {/if}
{else}
    {if $order_info.payment_method.instructions}
        <div class="ty-checkout-complete__login-info ty-checkout-complete_width_full">
            {hook name="checkout:payment_instruction"}
                <h4 class="ty-subheader">{__("payment_instructions")}</h4>
                <div class="ty-wysiwyg-content">
                    <br>
                    {$order_info.payment_method.instructions nofilter}
                </div>
            {/hook}
        </div>
    {/if}
{/if}

    {* place any code you wish to display on this page right after the order has been placed *}
    {hook name="checkout:order_confirmation"}
    {/hook}

    <div class="ty-checkout-complete__buttons buttons-container {if !$order_info || !$settings.General.allow_create_account_after_order == "Y" || $auth.user_id} ty-mt-s{/if}">
        {hook name="checkout:complete_button"}
            <div class="ty-checkout-complete__buttons-left">
                {if $order_info}
                    {if $order_info.child_ids}
                        {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("order_details") but_href="orders.search?period=A&order_id=`$order_info.child_ids`"}
                    {else}
                        {include file="buttons/button.tpl" but_text=__("order_details") but_meta="ty-btn__secondary" but_href="orders.details?order_id=`$order_info.order_number`"}
                    {/if}
                {/if}
                &nbsp;{include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_text=__("view_orders") but_href="orders.search"}
            </div>
            {*<div class="ty-checkout-complete__buttons-right">
                {include file="buttons/continue_shopping.tpl" but_role="text" but_meta="ty-checkout-complete__button-vmid" but_href=$continue_url|fn_url}
            </div>*}
        {/hook}
    </div>

{capture name="mainbox_title"}{__("order")}{/capture}