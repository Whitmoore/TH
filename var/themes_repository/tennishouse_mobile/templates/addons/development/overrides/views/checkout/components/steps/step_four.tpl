{script src="js/tygh/tabs.js"}

<div class="ty-step__container{if $edit}-active{/if} ty-step-four" data-ct-checkout="billing_options" id="step_four">
    {if $settings.General.checkout_style != "multi_page"}
        <h3 class="ty-step__title{if $edit}-active{/if} clearfix">
            {*<span class="ty-step__title-left">3{if $profile_fields.B || $profile_fields.S}3{else}2{/if}</span>*}
            {*<i class="ty-step__title-arrow ty-icon-down-micro"></i>*}

            {hook name="checkout:edit_link_title"}
            {if $complete && !$edit}
                <a class="ty-step__title-txt cm-ajax" href="{"checkout.checkout?edit_step=step_four&from_step=`$edit_step`"|fn_url}" data-ca-target-id="checkout_*">{__("billing_options_text")}</a>
            {else}
                <span class="ty-step__title-txt">{__("billing_options_text")}</span>
            {/if}
            {/hook}
        </h3>
    {/if}

    <div id="step_four_body" class="ty-step__body{if $edit}-active{/if} {if !$edit}hidden{/if}">
        <div class="clearfix ty-checkout__billing-tabs">

            {if $cart|fn_allow_place_order}
                {if $edit}
                    <div class="clearfix">
                        {if $cart.payment_id}
                            {include file="views/checkout/components/payments/payment_methods.tpl" payment_id=$cart.payment_id}
                        {else}
                            <div class="checkout__block"><h3 class="ty-subheader">{__("text_no_payments_needed")}</h3></div>
                            {include file="views/checkout/components/customer_notes.tpl"}
                        {/if}
                    </div>
                {/if}

            {else}
                {*if $cart.shipping_failed}
                    <p class="ty-error-text ty-center">{__("text_no_shipping_methods")}</p>
                {/if*}

                {if $cart.amount_failed}
                    <div class="checkout__block">
                        {capture name="order_limit"}
                            <strong>{include file="common/price.tpl" value=$settings.General.min_order_amount}</strong>
                        {/capture}
                        <p class="ty-error-text">{__("text_min_order_amount_required", ["[order_limit]" => $smarty.capture.order_limit])}</p>
                    </div>
                {/if}

                <div class="ty-checkout-buttons">
                    {include file="buttons/continue_shopping.tpl" but_href=$continue_url|fn_url but_role="action"}
                </div>

            {/if}
        </div>
    </div>
<!--step_four--></div>

<div id="place_order_data" class="hidden">
</div>
