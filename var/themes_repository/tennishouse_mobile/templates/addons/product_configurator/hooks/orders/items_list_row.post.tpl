{if $product.extra.configuration_data}
    {$colspan = 4}
    {if $order_info.use_discount}
    {$colspan = $colspan + 1}
    {/if}
    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
    {$colspan = $colspan + 1}
    {/if}
    <tr><td colspan="{$colspan}">
    <span class="ty-pc-title">{__("configuration")}:</span>
    <table class="ty-cart-content ty-table ty-pc-table">

    {assign var="prods" value=false}

    <tbody>
    {foreach from=$product.extra.configuration_data item="_product" key="key_conf"}
        <tr class="ty-valign-top">
            <td class="ty-position-relative">
                {if $_product.is_accessible}<a href="{"products.view?product_id=`$_product.product_id`"|fn_url}">{/if}
                    {$_product.product nofilter}
                {if $_product.is_accessible}</a>{/if}

                {if $_product.extra.is_edp == "Y"}
                    <div class="ty-right"><a href="{"orders.order_downloads?order_id=`$order_info.order_id`"|fn_url}">[{__("download")}]</a></div>
                {/if}
                {if $_product.product_code}
                    <div class="ty-orders-detail__table-code">{__("sku")}:&nbsp;{$_product.product_code}</div>
                {/if}
                {if $_product.product_options}{include file="common/options_info.tpl" product_options=$_product.product_option_data inline_option=true}{/if}
            </td>
            <td class="ty-right">
                {if $_product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$_product.original_price}{/if}
            </td>
            <td class="ty-center">&nbsp;{$_product.extra.step}</td>
            {if $order_info.use_discount}
                <td class="ty-right">
                    {if $_product.discount|floatval}{include file="common/price.tpl" value=$_product.discount class="ty-cart-content__price-discount"}{elseif $_product.extra.discount|floatval}{include file="common/price.tpl" value=$_product.extra.discount class="ty-cart-content__price-discount"}{else}-{/if}
                    {if $_product.discount_prc|floatval} <span class="ty-cart-content__price-discount">({$_product.discount_prc}%)</span>{/if}
                </td>
            {/if}
            {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                <td class="ty-center">
                    {if $_product.tax_value|floatval}{include file="common/price.tpl" value=$_product.tax_value}{else}-{/if}
                </td>
            {/if}
            <td class="ty-right">
                &nbsp;{if $_product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$_product.display_subtotal}{/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
    </table>
    </td></tr>
{/if}