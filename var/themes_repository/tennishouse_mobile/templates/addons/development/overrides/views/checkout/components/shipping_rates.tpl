{if $show_header == true}
    {include file="common/subheader.tpl" title=__("select_shipping_method")}
{/if}


{if !$no_form}
    <form {if $use_ajax}class="cm-ajax"{/if} action="{""|fn_url}" method="post" name="shippings_form">
        <input type="hidden" name="redirect_mode" value="checkout" />
        {if $use_ajax}
            <input type="hidden" name="result_ids" value="checkout_totals,checkout_steps" />
        {/if}
{/if}

{hook name="checkout:shipping_rates"}

{if $display == "show"}
    <div class="step-complete-wrapper">
{/if}

    <div id="shipping_rates_list">

        {foreach from=$product_groups key="group_key" item=group name="spg"}
            {* Group name *}
            {if !"ULTIMATE"|fn_allowed_for || $product_groups|count > 1}
                <span class="ty-shipping-options__vendor-name">{$group.name}</span>
            {/if}

            {* Products list *}
            {if !"ULTIMATE"|fn_allowed_for || $product_groups|count > 1}
                <ul class="ty-shipping-options__products">
                    {foreach from=$group.products item="product"}
                        {if !(($product.is_edp == 'Y' && $product.edp_shipping != 'Y') || $product.free_shipping == 'Y')}
                            <li class="ty-shipping-options__products-item">
                                {if $product.product}
                                    {$product.product nofilter}
                                {else}
                                    {$product.product_id|fn_get_product_name}
                                {/if}
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            {/if}

            {* Shippings list *}
            {if $group.shippings && !$group.all_edp_free_shipping && !$group.all_free_shipping && !$group.free_shipping && !$group.shipping_no_required}

                {if $display == "select"}
                    <p>
                        <select id="ssr_{$company_id}" name="shipping_ids[{$company_id}]" {if $onchange}onchange="{$onchange}"{/if}>
                {/if}

                {foreach from=$group.shippings item="shipping"}

                    {if $cart.chosen_shipping.$group_key == $shipping.shipping_id}
                        {assign var="checked" value="checked=\"checked\""}
                        {assign var="selected" value="selected=\"selected\""}
                        {assign var="strong_begin" value="<strong>"}
                        {assign var="strong_end" value="</strong>"}
                    {else}
                        {assign var="checked" value=""}
                        {assign var="selected" value=""}
                        {assign var="strong_begin" value=""}
                        {assign var="strong_end" value=""}
                    {/if}

                    {if $shipping.delivery_time}
                        {assign var="delivery_time" value="(`$shipping.delivery_time`)"}
                    {else}
                        {assign var="delivery_time" value=""}
                    {/if}

                    {if $shipping.rate}
                        {capture assign="rate"}{include file="common/price.tpl" value=$shipping.rate}{/capture}
                        {if $shipping.inc_tax}
                            {assign var="rate" value="`$rate` ("}
                            {if $shipping.taxed_price && $shipping.taxed_price != $shipping.rate}
                                {capture assign="tax"}{include file="common/price.tpl" value=$shipping.taxed_price class="ty-nowrap"}{/capture}
                                {assign var="rate" value="`$rate` (`$tax` "}
                            {/if}
                            {assign var="inc_tax_lang" value=__('inc_tax')}
                            {assign var="rate" value="`$rate``$inc_tax_lang`)"}
                        {/if}
                    {else}
                        {assign var="rate" value=__("free")}
                    {/if}

                    <div class="ty-shipping-options__method">
                    {hook name="checkout:shipping_method"}
                        {if $display == "radio"}
                                <div class="ty-shipping-options__method-group" id="shipping_group_{$group_key}_{$shipping.shipping_id}">
                                    <div class="ty-shipping-options__method-info">
                                        <input type="radio" class="ty-valign" id="sh_{$group_key}_{$shipping.shipping_id}" name="shipping_ids[{$group_key}]" value="{$shipping.shipping_id}" onchange="fn_process_checkout($(this));" {$checked} />
                                        <label for="sh_{$group_key}_{$shipping.shipping_id}" class="ty-valign ty-shipping-options__item-title">
                                            {if $shipping.icon}
                                                {include file="common/image.tpl" obj_id=$shipping.shipping_id images=$shipping.icon image_width="70" image_height="35" keep_transparent=true}
                                            {/if}
                                            <div>{$shipping.shipping} {$delivery_time} - <b>{$rate nofilter}</b>{if $shipping.original_rate && $shipping.original_rate > $shipping.rate} {include file="common/price.tpl" value=$shipping.original_rate class="ty-line-through"}{/if}</div>
                                        </label>
                                    </div>
                                    {*<div class="ty-shipping-options__method-payments">
                                        {foreach from=$shipping.available_payments key="payment_type" item="payment_status"}
                                            <div>
                                                <div class="ty-shipping-options__method-payments-mark">
                                                    {if $payment_status == 'Y'}
                                                        <i class="ty-shipping-options__method-payments-check-icon"></i>
                                                    {else}
                                                        <i class="ty-shipping-options__method-payments-cross-icon"></i>
                                                    {/if}
                                                </div><div class="ty-shipping-options__method-payments-type">{if $payment_status == 'Y'}{__($payment_type)}{else}{__("`$payment_type`_disabled")}{/if}</div>
                                            </div>
                                        {/foreach}
                                    </div>*}
                                </div>
                                {*if $shipping.website}
                                <div class="shipping_carrier_link">
                                    <a target="_blank" href="{$shipping.website}">{$shipping.website}</a>
                                </div>
                                {/if*}
                                <script type="text/javascript" class="cm-ajax-force">
                                (function(_, $) {
                                    $('#shipping_group_{$group_key}_{$shipping.shipping_id}').click(function(){$ldelim}
                                        if (!$('#sh_{$group_key}_{$shipping.shipping_id}').is(':checked')) {
                                            $('#sh_{$group_key}_{$shipping.shipping_id}').click();
                                        }
                                    {$rdelim});
                                }(Tygh, Tygh.$));
                                </script>

                        {elseif $display == "select"}
                            <option value="{$shipping.shipping_id}" {$selected}>{$shipping.shipping} {$delivery_time} - {$rate nofilter}</option>

                        {elseif $display == "show"}
                            <div>
                                {$strong_begin}{$rate.name} {$delivery_time} - {$rate nofilter}{$strong_begin}
                            </div>
                        {/if}
                    {/hook}
                    </div>

                {/foreach}

                {if $display == "select"}
                        </select>
                    <p>
                {/if}

                {*if $smarty.foreach.spg.last && !$group.all_edp_free_shipping && !($group.all_free_shipping || $group.free_shipping)}
                    <p class="ty-shipping-options__total">{__("total")}:&nbsp;<strong>{include file="common/price.tpl" value=$cart.display_shipping_cost class="ty-price"}</strong>{if $cart.original_shipping_cost && $cart.try_on && $cart.original_shipping_cost > $cart.shipping_cost} {__("try_on_shipping_cost", ["[cost]" => {include file="common/price.tpl" value=$cart.original_shipping_cost class="ty-price"}, "[amount]" => {include file="common/price.tpl" value=$addons.development.free_shipping_cost class="ty-price"}])}{/if}</p>
                {/if*}

            {else}
                {if $group.all_free_shipping}
                     <p>{__("free_shipping")}</p>
                {elseif $group.all_edp_free_shipping || $group.shipping_no_required }
                    <p>{__("no_shipping_required")}</p>
                {else}
                    <p class="ty-error-text">
                        {if $display == "show"}
                            <strong>{__("text_no_shipping_methods")}</strong>
                        {else}
                            {__("text_no_shipping_methods")}
                        {/if}
                    </p>
                {/if}
            {/if}

        {foreachelse}
            <p>
                {if !$cart.shipping_required}
                    {__("no_shipping_required")}
                {elseif $cart.free_shipping}
                    {__("free_shipping")}
                {/if}
            </p>
        {/foreach}

    <!--shipping_rates_list--></div>

{if $display == "show"}
    </div>
{/if}

{/hook}

{if !$no_form}
        <div class="cm-noscript buttons-container ty-center">{include file="buttons/button.tpl" but_name="dispatch[checkout.update_shipping]" but_text=__("select")}</div>
    </form>
{/if}
