{if $shipping.service_code == 'multiship'}
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
        {assign var="rate" value=__("free_shipping")}
    {/if}

    <p>
        <input type="radio" class="ty-valign" id="sh_{$group_key}_{$shipping.shipping_id}" name="shipping_ids[{$group_key}]" value="{$shipping.shipping_id}" onclick="fn_calculate_total_shipping();" {$checked} /><label for="sh_{$group_key}_{$shipping.shipping_id}" class="ty-valign">{$shipping.shipping} {$delivery_time} - {$rate nofilter}</label>

        <input type="submit" name="multiship" id="multiship{$group_key}" class="ty-multiship__btn ty-btn__secondary ty-btn"
               data-mswidget-open
               data-shipping-id="{$shipping.shipping_id}"
               data-group-id="{$group_key}"
               data-weight="{$cart.product_groups.$group_key.package_info.W}"
               data-length="{$cart.shippings_extra.package_size.$group_key.length}"
               data-width="{$cart.shippings_extra.package_size.$group_key.width}"
               data-height="{$cart.shippings_extra.package_size.$group_key.height}"
               data-cost="{$cart.product_groups.$group_key.package_info.C}"
               data-city="{$cart.user_data.s_city}"
               data-url="checkout.shipping_estimation"
               value={__("change")}
                />

    </p>
{/if}

{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.service_code == 'multiship'}

    {assign var="multiship_shipping_select" value=$cart.shippings_extra.data.$group_key[$shipping.shipping_id]}

    <div class="ty-multiship ty-clearfix" >

        <div class="ty-multiship__title">
            <span class="ty-multiship__delivery-name">{$multiship_shipping_select.delivery_name}</span><br>
            {__("multiship_service_delivery")} -
            {if $multiship_shipping_select.type == 1}
                {__('multiship_pickuppoint')}
            {else}
                {__('multiship_todoor')}
            {/if}
        </div>

        <div>
            {if $multiship_shipping_select.address}{$multiship_shipping_select.address}.{/if}
            {if $multiship_shipping_select.metro} {__('multiship_metro')} {$multiship_shipping_select.metro}.{/if}
        </div>

        <div class="ty-multiship__description ty-clear-both">
            <p>{$multiship_shipping_select.days} {__('multiship_days')}, {__('multiship_deliver_orient_day')} {$multiship_shipping_select.deliver_orient_day}</p>
        </div>


    </div>

{/if}