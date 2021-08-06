{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.service_code == 'multiship'}

    {assign var="multiship_shipping_select" value=$cart.shippings_extra.data.$group_key[$shipping.shipping_id]}

    <div class="ty-multiship ty-clearfix" >

            <div class="ty-multiship__title">
                <h4 class="ty-multiship__delivery-name ty-float-left"> {__("multiship_service_delivery")} {$multiship_shipping_select.delivery_name}</h4>

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
                    data-url="checkout.checkout"
                    value={__("change")}
                />
            </div>

            <div class="ty-multiship__type">
                {if $multiship_shipping_select.type == 1}
                    {__('multiship_pickuppoint')}
                {else}
                    {__('multiship_todoor')}
                {/if}
            </div>

            <div class="ty-clear-both">
                {if $multiship_shipping_select.photo}
                    <div class="ty-delivery__photo">
                        <img src="https://multiship.ru/{$multiship_shipping_select.photo}">
                    </div>
                {/if}

                <div class="ty-multiship__description">
                    <div>
                        {if $multiship_shipping_select.address}{$multiship_shipping_select.address}.{/if}
                        {if $multiship_shipping_select.metro} {__('multiship_metro')} {$multiship_shipping_select.metro}.{/if}
                    </div>

                    <div class="ty-multiship__location">
                         {$multiship_shipping_select.location_name}, {$multiship_shipping_select.region_name}
                    </div>

                    <p>{include file="addons/rus_multiship/views/rus_multiship/components/schedules.tpl" schedules=$multiship_shipping_select.schedule_days}</p>

                    {if $multiship_shipping_select.contact_phone}
                        <p>{$multiship_shipping_select.contact_phone}</p>
                    {/if}

                    <div>
                        <p>
                        {__('multiship_pay_accepted')}:
                        {if $multiship_shipping_select.cash}
                            {__('multiship_cash')}{if $multiship_shipping_select.card}, {/if}
                        {/if}

                        {if $multiship_shipping_select.card}
                            {__('multiship_credit_card')}
                        {/if}
                        </p>
                    </div>

                    <div>
                        <p><strong>{__('shipping_cost')}:</strong> {include file="common/price.tpl" value=$multiship_shipping_select.cost_with_rules class="nowrap"}
                        ({$multiship_shipping_select.days} {__('multiship_days')}, {__('multiship_deliver_orient_day')} {$multiship_shipping_select.deliver_orient_day})</p>
                    </div>
                </div>
            </div>

            {if $multiship_shipping_select.instruction}
            <div class="ty-multiship__instruction">
                <span><strong>{__('multiship_instruction')}:</strong><br /> {$multiship_shipping_select.instruction}</span>
            </div>
            {/if}
    </div>

{/if}