{if $product_data.competitors}
<div class="ty-product-price-cmp">
    {$competitor = $product_data.competitors|reset}
    <div class="ty-product-competitors">
        <div class="ty-main-competitor {if $competitor.is_main}ty-bold-competitor{/if}">
            {include file="common/price.tpl" value=$competitor.price} <a target="_blank" href="{$competitor.link}">{$competitor.name}</a>{if $competitor.in_stock != 'Y'}  {__("cprices_out_of_stock")}{/if} {$competitor.competitor_name}
        </div>
        {if $product_data.competitors}
            <div class="ty-other-competitors">
                {foreach from=$product_data.competitors item="cmptr"}
                    <div class="ty-other-competitor {if $cmptr.is_main}ty-bold-competitor{/if}">
                        {include file="common/price.tpl" value=$cmptr.price} <a target="_blank" href="{$cmptr.link}">{$cmptr.name}</a>{if $cmptr.in_stock != 'Y'}  {__("cprices_out_of_stock")}{/if} {$cmptr.competitor_name}
                    </div>
                {/foreach}
            </div>
        {/if}
    </div>
</div>
{/if}
