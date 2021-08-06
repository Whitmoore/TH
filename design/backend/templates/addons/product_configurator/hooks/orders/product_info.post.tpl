{if $oi.extra.configuration_data}
    <div class="ty-orders-detail__pc-link">
        <a id="sw_product_configuration_{$key}" class="cm-combination ty-cart-content__detailed-link detailed-link">{__("configuration")}</a>
        <div id="product_configuration_{$key}" class="ty-orders-detail__pc-detail hidden">
            <table width="100%" class="table">
            <thead>
                <tr>
                    <th width="50%">{__("product")}</th>
                    <th width="10%">{__("price")}</th>
                    <th class="center" width="10%">{__("quantity")}</th>
                    {if $order_info.use_discount}
                    <th width="5%">{__("discount")}</th>
                    {/if}
                    {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                    <th width="10%">&nbsp;{__("tax")}</th>
                    {/if}
                    <th width="10%" class="right">&nbsp;{__("subtotal")}</th>
                </tr>
            </thead>
            {foreach from=$oi.extra.configuration_data item="_product"}
            <tr class="ty-valign-top">
                <td>
                    {if !$_product.deleted_product}<a href="{"products.update?product_id=`$_product.product_id`"|fn_url}">{/if}{$_product.product nofilter}{if !$_product.deleted_product}</a>{/if}
                    <div class="products-hint">
                        {if $_product.product_code}<p>{__("sku")}:{$_product.product_code}</p>{/if}
                        {if $_product.extra.warehouses}
                            <div class="ty-order-warehouses">
                            {foreach from=$_product.extra.warehouses item="wh_amount" key="wh_hash"}
                                {$_product.extra.warehouse_names.$wh_hash}: {$wh_amount}
                            {/foreach}
                            </div>
                        {/if}
                    </div>
                    {if $_product.product_options}<div class="options-info">{include file="common/options_info.tpl" product_options=$_product.product_option_data}</div>{/if}
                </td>
                <td class="nowrap">
                    {if $_product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$_product.original_price}{/if}</td>
                <td class="center">
                    &nbsp;{$_product.extra.step}<br />
                    {if !"ULTIMATE:FREE"|fn_allowed_for && $use_shipments && $_product.shipped_amount > 0}
                        &nbsp;<span class="muted"><small>({$_product.shipped_amount}&nbsp;{__("shipped")})</small></span>
                    {/if}
                </td>
                {if $order_info.use_discount}
                <td class="nowrap">
                    {if $_product.extra.discount|floatval}{include file="common/price.tpl" value=$_product.extra.discount}{elseif $_product.discount|floatval}{include file="common/price.tpl" value=$_product.discount}{else}-{/if}</td>
                {/if}
                {if $order_info.taxes && $settings.General.tax_calculation != "subtotal"}
                <td class="nowrap">
                    {if $_product.tax_value|floatval}{include file="common/price.tpl" value=$_product.tax_value}{else}-{/if}</td>
                {/if}
                <td class="nowrap">&nbsp;<span>{if $_product.extra.exclude_from_calculate}{__("free")}{else}{include file="common/price.tpl" value=$_product.display_subtotal}{/if}</span></td>
            </tr>
            {/foreach}
            </table>
        </div>
    </div>
{/if}