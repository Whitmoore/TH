{*if $product.configuration}
    <div class="ty-product-extra-info">
        <a id="sw_product_extra_{$key}" class="cm-combination ty-product-extra-info-link detailed-link">{__("configuration")}</a>
        <div id="product_extra_{$key}" class="ty-product-extra-info-block hidden">
            <table class="table margin-top cart-configuration">
                <tr>
                    <th style="width: 50%">{__("product")}</th>
                    <th style="width: 10%">{__("price")}</th>
                    <th style="width: 10%">{__("qty")}</th>
                    <th class="right" style="width: 10%">{__("subtotal")}</th>
                </tr>
                {foreach from=$product.configuration item="_product" key="key_conf"}
                    <tr {cycle values=",class=\"table-row\""}>
                        <td>
                            {if $_product.is_accessible}<a href="{"products.view?product_id=`$_product.product_id`"|fn_url}">{$_product.product}</a>{else}{$_product.product}{/if}
                            {if $_product.product_options}
                                {include file="common/options_info.tpl" product_options=$_product.product_options fields_prefix="cart_products[`$key_conf`][product_options]" no_block=trues}
                            {/if}
                        </td>
                        <td class="center">
                            {include file="common/price.tpl" value=$_product.price}</td>
                        <td class="center">
                            <input type="hidden" name="cart_products[{$key_conf}][product_id]" value="{$_product.product_id}" />
                            {if $_product.step}{$_product.step}{else}{$_product.amount}{/if}
                        </td>
                        <td class="right">
                            {include file="common/price.tpl" value=$_product.display_subtotal}
                        </td>
                    </tr>
                {/foreach}
                <tr class="table-footer">
                    <td colspan="4">&nbsp;</td>
                </tr>
            </table>
        </div>
    </div>
{/if*}
