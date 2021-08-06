<div class="ty-step__container{if $edit}-active{/if} ty-step-three" data-ct-checkout="shipping_options" id="step_three">
    {if $settings.General.checkout_style != "multi_page"}
        <h3 class="ty-step__title{if $edit}-active{/if}{if $complete && !$edit}-complete{/if} clearfix">
            {*<span class="ty-step__title-left">2{if $profile_fields.B || $profile_fields.S}2{else}1{/if}{if $complete && !$edit}<i class="ty-step__title-icon ty-icon-ok"></i>{/if}</span>*}
            {*<i class="ty-step__title-arrow ty-icon-down-micro"></i>*}

            {if $complete && !$edit}
                {hook name="checkout:edit_link"}
                <span class="ty-step__title-right">
                    {include file="buttons/button.tpl" but_meta="cm-ajax" but_href="checkout.checkout?edit_step=step_three&from_step=$edit_step" but_target_id="checkout_*" but_text=__("change") but_role="tool"}
                </span>
                {/hook}
            {/if}

            {hook name="checkout:edit_link_title"}
            {if $complete && !$edit}
                <a class="ty-step__title-txt cm-ajax" href="{"checkout.checkout?edit_step=step_three&from_step=`$edit_step`"|fn_url}" data-ca-target-id="checkout_*">{__("shipping_options_text")}</a>
            {else}
                <span class="ty-step__title-txt">{__("shipping_options_text")}</span>
            {/if}
            {/hook}
        </h3>
    {/if}

    <div id="step_three_body" class="ty-step__body{if $edit}-active{/if} {if !$edit}hidden{/if} clearfix">
        {if $edit}
            <div class="clearfix">
                <div class="checkout__block">
                {hook name="checkout:select_shipping"}
                    {if !$cart.shipping_failed}
                        {*<div>{__("quarantine_shipping_delay")}</div>*}
                        {include file="views/checkout/components/shipping_rates.tpl" no_form=true display="radio"}
                        {if $edit}
                            <div class="ty-checkout__shipping-tips">
                            <p>{__("delivery_times_text")}</p>
                                {__("shipping_tips")}
                            </div>
                        {/if}
                    {else}
                        <p class="">{__("text_no_shipping_methods")}</p>
                    {/if}
                {/hook}
                </div>
            </div>

            {*<div class="ty-checkout-buttons">
                {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_name="dispatch[checkout.update_steps]" but_text=$but_text but_id="step_three_but"}
            </div>*}
        {/if}
    </div>
<!--step_three--></div>
