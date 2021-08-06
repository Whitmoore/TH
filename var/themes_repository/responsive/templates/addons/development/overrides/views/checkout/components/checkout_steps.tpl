{*if $settings.General.checkout_style != "multi_page"*}
    {assign var="ajax_form" value="cm-ajax"}
{*else}
    {assign var="ajax_form" value=""}
{/if*}
{if $runtime.action}
    {assign var="_action" value=".`$runtime.action`"}
{/if}

{include file="views/profiles/components/profiles_scripts.tpl"}

{if $settings.General.checkout_style != "multi_page"}

    <div class="clearfix cm-save-fields checkout-steps" id="checkout_steps">
    <form name="checkout_steps" class="{$ajax_form} cm-ajax-full-render cm-auto-save cm-label-placeholder ty-mini-form-mode cm-autocomplete-form" data-auto-save-dispatch="checkout.auto_save_user" action="{""|fn_url}" method="post" autocomplete="off">
        <input type="hidden" name="result_ids" value="checkout*,account*" />
        <input type="hidden" name="dispatch" value="checkout.update_steps{$_action}" />
        {*if $edit_step == "step_one"}{$edit = true}{else}{$edit = false}{/if}
        {include file="views/checkout/components/steps/step_one.tpl" step="one" complete=$completed_steps.step_one edit=$edit but_text=__("continue")*}

        {if $profile_fields.B || $profile_fields.S}
            {if $edit_step == "step_two"}{$edit = true}{else}{$edit = false}{/if}
            {include file="views/checkout/components/steps/step_two.tpl" step="two" complete=false edit=true but_text=__("continue")}
        {/if}
        <div class="ty-checkout-buttons hidden">
            {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_name="dispatch[checkout.update_steps`$_action`]" but_text=__("continue") but_id="submit_on_change_button"}
        </div>

        {if !$cart.hide_shipping_payment}
            {if $edit_step == "step_three"}{$edit = true}{else}{$edit = false}{/if}
            {include file="views/checkout/components/steps/step_three.tpl" step="three" complete=false edit=true but_text=__("continue")}

            {if $edit_step == "step_four"}{$edit = true}{else}{$edit = false}{/if}
            {include file="views/checkout/components/steps/step_four.tpl" step="four" complete=false edit=true}

            {if $iframe_mode}
                <div class="ty-payment-method-iframe__box">
                    <iframe width="100%" height="700" id="order_iframe_{$smarty.const.TIME}" src="{"checkout.process_payment"|fn_checkout_url:$smarty.const.AREA}" style="border: 0px" frameBorder="0" ></iframe>
                    {if $cart_agreements || $settings.General.agree_terms_conditions == "Y"}
                    <div id="payment_method_iframe{$tab_id}" class="ty-payment-method-iframe">
                        <div class="ty-payment-method-iframe__label">
                            <div class="ty-payment-method-iframe__text">{__("checkout_terms_n_conditions_alert")}</div>
                        </div>
                    </div>
                    {/if}
                </div>
            {else}
                <div class="ty-checkout-buttons ty-checkout-buttons__submit-order">
                    {if !$show_checkout_button}
                        {if $cart.payment_id|in_array:$smarty.const.ONLINE_PAYMENT_IDS}
                            {$but_text = __("submit_pay_my_order")}
                        {else}
                            {$but_text = __("submit_my_order")}
                        {/if}
                        {math equation="x+y" x=$cart.total y=$cart.payment_surcharge assign="_total"}
                        {capture name="order_total"}
                            {include file="common/price.tpl" value=$_total|default:$cart.total}
                        {/capture}
                        {include file="buttons/place_order.tpl" but_text="`$but_text` (`$smarty.capture.order_total`)" but_name="dispatch[checkout.place_order]" but_role="big" but_id="place_order_`$tab_id`" but_meta="cm-no-ajax ty-place-order"}
                    {else}
                        {$checkout_buttons[$cart.payment_id] nofilter}
                    {/if}
                </div>
            {/if}
        {/if}

    </form>
    <!--checkout_steps--></div>

{else}
    <div class="checkout-steps cm-save-fields clearfix" id="checkout_steps">
        {$smarty.capture.checkout_error_content nofilter}

        {if $edit_step == "step_one"}
            {include file="views/checkout/components/steps/step_one.tpl" complete=$completed_steps.step_one edit=true but_text=__("continue")}

        {elseif $edit_step == "step_two"}
            {include file="views/checkout/components/steps/step_two.tpl" complete=$completed_steps.step_two edit=true but_text=__("continue")}

        {elseif $edit_step == "step_three"}
            {include file="views/checkout/components/steps/step_three.tpl" complete=$completed_steps.step_three edit=true but_text=__("continue")}

        {elseif $edit_step == "step_four"}
            {include file="views/checkout/components/steps/step_four.tpl" edit=true complete=$completed_steps.step_four}
        {/if}
    <!--checkout_steps--></div>
{/if}
