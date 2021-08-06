{assign var="multiship_shipping_select" value=$cart.shippings_extra.data.$group_key[$shipping.shipping_id]}

<div class="control-group" id="om_ajax_multiship">
    {if $cart.product_groups.$group_key.chosen_shippings.$group_key.module == 'multiship'}
    <p>
        <h4 class="subheader">{__("multiship_service_delivery")} {$multiship_shipping_select.delivery_name}</h4>

        {if $multiship_shipping_select.type == 1}
            {__('multiship_pickuppoint')}
        {else}
            {__('multiship_todoor')}
        {/if}

        <a name="multiship" id="multiship{$group_key}"
           data-mswidget-open
           data-shipping-id="{$shipping.shipping_id}"
           data-group-id="{$group_key}"
           data-weight="{$cart.product_groups.$group_key.package_info.W}"
           data-length="{$cart.shippings_extra.package_size.$group_key.length}"
           data-width="{$cart.shippings_extra.package_size.$group_key.width}"
           data-height="{$cart.shippings_extra.package_size.$group_key.height}"
           data-cost="{$cart.product_groups.$group_key.package_info.C}"
           data-city="{$cart.user_data.s_city}"
                >
            ({__(change)})
        </a>
    </p>
    <p>{$multiship_shipping_select.address}<br />
        {if $multiship_shipping_select.metro} {__('multiship_metro')} {$multiship_shipping_select.metro}.{/if}
        {if $multiship_shipping_select.location_name || $multiship_shipping_select.region_name}
            <span class="muted">
            {$multiship_shipping_select.location_name}, {$multiship_shipping_select.region_name}
            </span>
        {/if}
    </p>
    <p>{$multiship_shipping_select.contact_phone}</p>
    <p>
        {__('multiship_pay_accepted')}:
        {if $multiship_shipping_select.cash}
            {__('multiship_cash')}{if $multiship_shipping_select.card}, {/if}
        {/if}

        {if $multiship_shipping_select.card}
            {__('multiship_credit_card')}
        {/if}
    </p>
    <p>{$multiship_shipping_select.days} {__('multiship_days')}, {__('multiship_deliver_orient_day')} {$multiship_shipping_select.deliver_orient_day}</p>
    {/if}
<!--om_ajax_multiship--></div>
