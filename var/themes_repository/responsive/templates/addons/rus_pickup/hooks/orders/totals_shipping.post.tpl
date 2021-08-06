{foreach from=$order_info.shipping item="shipping_method"}
	{if $shipping_method.store_data}
        <p class="ty-strong">
            {$shipping_method.store_data.name}
        </p>
        <p class="ty-muted">
            {$shipping_method.store_data.city}{if $shipping_method.store_data.pickup_address}, {$shipping_method.store_data.pickup_address}{/if}</br>
            {if $shipping_method.store_data.pickup_phone}
                {__("phone")}: {$shipping_method.store_data.pickup_phone}</br>
            {/if}
            {if $shipping_method.store_data.pickup_time}
                {__("rus_pickup.work_time")}: {$shipping_method.store_data.pickup_time}</br>
            {/if}
            {$shipping_method.store_data.description nofilter}
        </p>
    {/if}
{/foreach}