{foreach from=$order_info.shipping item="shipping" key="shipping_id" name="f_shipp"}
    {if $shipping.service_code == 'multiship'}
        <div class="well orders-right-pane form-horizontal">
            <div class="control-group">
                {include file="common/subheader.tpl" title="{__("multiship_service_delivery")} {$shipping.shipping_extra.delivery_name}"}
                {if $shipping.shipping_extra.type == 1}
                    {__('multiship_pickuppoint')}
                {else}
                    {__('multiship_todoor')}
                {/if}
            </div>
            <p>{$shipping.shipping_extra.address}<br />
                {if $shipping.shipping_extra.metro} {__('multiship_metro')} {$shipping.shipping_extra.metro}.{/if}
                {if $shipping.shipping_extra.location_name || $shipping.shipping_extra.region_name}
                    <span class="muted">
                    {$shipping.shipping_extra.location_name}, {$shipping.shipping_extra.region_name}
                    </span>
                {/if}
            </p>
            <p>{$shipping.shipping_extra.contact_phone}</p>
            <p>
                {__('multiship_pay_accepted')}:
                {if $shipping.shipping_extra.cash}
                    {__('multiship_cash')}{if $shipping.shipping_extra.card}, {/if}
                {/if}

                {if $shipping.shipping_extra.card}
                    {__('multiship_credit_card')}
                {/if}
            </p>
            <p>{$shipping.shipping_extra.days} {__('multiship_days')}, {__('multiship_deliver_orient_day')} {$shipping.shipping_extra.deliver_orient_day}</p>
        </div>
    {/if}
{/foreach}


