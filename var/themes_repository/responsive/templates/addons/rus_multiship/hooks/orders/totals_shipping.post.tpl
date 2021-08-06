{foreach from=$order_info.shipping item="shipping_method"}
    {if $shipping_method.service_code == 'multiship'}
        {assign var="multiship_shipping" value=true}
    {/if}
{/foreach}

{if $multiship_shipping}
    {if $use_shipments}
        <ul>
            {foreach from=$order_info.shipping item="shipping_method"}
                <li>{if $shipping_method.shipping_extra.delivery_name} {$shipping_method.shipping_extra.delivery_name} {else} – {/if}
                    {if $shipping_method.shipping_extra}
                    <p class="ty-muted">
                        {if $shipping_method.shipping_extra.address}{$shipping_method.shipping_extra.address}<br />{/if}
                        {if $shipping_method.shipping_extra.contact_phone}{$shipping_method.shipping_extra.contact_phone}<br />{/if}
                    </p>
                    {/if}
                </li>
            {/foreach}
        </ul>
    {else}
        {foreach from=$order_info.shipping item="shipping" name="f_shipp"}
            {if $shipments[$shipping.group_key].carrier && $shipments[$shipping.group_key].tracking_number}
                {include file="common/carriers.tpl" carrier=$shipments[$shipping.group_key].carrier tracking_number=$shipments[$shipping.group_key].tracking_number}

                {if $shipping_method.shipping_extra.delivery_name} {$shipping_method.shipping_extra.delivery_name} {else} – {/if}
                {if $shipping_method.shipping_extra}
                    <p class="ty-muted">
                        {if $shipping_method.shipping_extra.address}{$shipping_method.shipping_extra.address}<br />{/if}
                        {if $shipping_method.shipping_extra.contact_phone}{$shipping_method.shipping_extra.contact_phone}<br />{/if}
                    </p>
                {/if}
                ({__("tracking_num")}<a {if $smarty.capture.carrier_url|strpos:"://"}target="_blank"{/if} href="{$smarty.capture.carrier_url nofilter}">{$shipments[$shipping.group_key].tracking_number}</a>)
            {else}
                {if $shipping_method.shipping_extra.delivery_name} {$shipping_method.shipping_extra.delivery_name} {else} – {/if}
                {if $shipping_method.shipping_extra}
                    <p class="ty-muted">
                        {if $shipping_method.shipping_extra.address}{$shipping_method.shipping_extra.address}<br />{/if}
                        {if $shipping_method.shipping_extra.contact_phone}{$shipping_method.shipping_extra.contact_phone}<br />{/if}
                    </p>
                {/if}
            {/if}
            {if !$smarty.foreach.f_shipp.last}<br>{/if}
        {/foreach}
    {/if}
{/if}
