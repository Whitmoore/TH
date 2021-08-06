{hook name="checkout:login_form"}
    <div class="ty-checkout__login">
        {include file="views/auth/login_form.tpl" id="checkout_login" style="checkout" result_ids="checkout*,account*"}
    </div>
{/hook}

{hook name="checkout:register_customer"}
    <div class="ty-checkout__register checkout-register">
        <div class="ty-checkout__register-content">
            {if $settings.General.approve_user_profiles != "Y" || $settings.General.disable_anonymous_checkout != "Y"}
                {include file="common/subheader.tpl" title=__("new_customer")}
            {/if}

        </div>

        {if $settings.General.disable_anonymous_checkout != "Y"}
            <div id="anonymous_checkout" class="">
                <form name="step_one_anonymous_checkout_form" class="{$ajax_form}" action="{""|fn_url}" method="post">
                    <input type="hidden" name="result_ids" value="checkout*,account*" />

                    {if !$contact_fields_filled}
                        <div class="ty-control-group">
                            <label for="guest_email" class="cm-required">{__("email")}</label>
                            <input type="text" id="guest_email" name="user_data[email]" size="32" value="" class="ty-input-text" />
                        </div>
                    {/if}

                    <div class="">
                        {include file="buttons/button.tpl" but_meta="ty-btn__primary" but_name="dispatch[checkout.customer_info.guest_checkout]" but_text=__("fill_in_checkout_data")}
                    </div>
                </form>
            </div>
        {/if}
    </div>
{/hook}