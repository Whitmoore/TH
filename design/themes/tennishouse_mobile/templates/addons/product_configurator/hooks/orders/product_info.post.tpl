{*if $product.extra.configuration_data}
    <div class="ty-product-extra-info">
        <a id="sw_product_extra_{$key}" class="cm-combination ty-product-extra-info-link detailed-link">{__("configuration")}</a>
        <div id="product_extra_{$key}" class="ty-product-extra-info-block hidden">
            <table class="ty-orders-detail__table ty-table">
            <thead>
                <tr>
                    <th class="ty-orders-detail__table-product">{__("product")}</th>
                    <th class="ty-orders-detail__table-price">{__("price")}</th>
                    <th class="ty-orders-detail__table-quantity">{__("quantity")}</th>
                    {if $order_info.use_discount}
                        <th class="ty-orders-detail__table-discount">{__("discount")}</th>
                    {/if}
                    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                        <th class="ty-orders-detail__table-tax">{__("tax")}</th>
                    {/if}
                    <th class="ty-orders-detail__table-subtotal">{__("subtotal")}</th>
                </tr>
            </thead>

            {foreach from=$product.extra.configuration_data item="_product"}
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
                            {if $_product.extra.discount|floatval}{include file="common/price.tpl" value=$_product.extra.discount}{else}-{/if}
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
        </table>
        </div>
    </div>
{/if*}