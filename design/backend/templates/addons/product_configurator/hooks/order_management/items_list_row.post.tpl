{if $cart.use_discount}
    {$colspan = 7}
{else}
    {$colspan = 6}
{/if}
{foreach from=$cp.product_configurator_groups item="pg" key="group_id"}
    <input type="hidden" name="cart_products[{$key}][cart_id]" value="{$key}" />
    {if $pg.selected_id}
        {$_key = $pg.selected_id}
        {$po = $cp.configuration.$_key}
        <tr class="cm-reload-{$key} cm-ex-op row-more row-gray" id="product_configuration_update_{$key}_{$pg.group_id}">
            <td class="left">&nbsp;</td>
            <td>
                {include file="addons/product_configurator/views/products/components/product_configuration.tpl" key_conf=$_key product=$cp po=$pg show_price_values=true}
            </td>
            <td width="3%">
                {if $po.exclude_from_calculate}
                    {__("free")}
                    {else}
                    <input type="hidden" name="cart_products[{$key}][configuration][{$po.group_id}][stored_price]" value="N" />
                    <input class="inline" type="checkbox" name="cart_products[{$key}][configuration][{$po.group_id}][stored_price]" value="Y" {if $po.stored_price == "Y"}checked="checked"{/if} onchange="Tygh.$('#db_price_{$_key},#manual_price_{$_key}').toggle();"/>
                {/if}
            </td>
            <td class="left">
            {if !$po.exclude_from_calculate}
                {if $po.stored_price == "Y"}
                    {math equation="price - modifier" price=$po.original_price modifier=$po.modifiers_price|default:0 assign="original_price"}
                {else}
                    {assign var="original_price" value=$po.original_price}
                {/if}
                <span class="{if $po.stored_price == "Y"}hidden{/if}" id="db_price_{$_key}">{include file="common/price.tpl" value=$original_price}</span>
                <div class="{if $po.stored_price != "Y"}hidden{/if}" id="manual_price_{$_key}">
                    {include file="common/price.tpl" value=$po.base_price view="input" input_name="cart_products[`$key`][configuration][`$po.group_id`][price]" class="input-hidden input-mini" }
                </div>
            {/if}
            </td>
            {if $cart.use_discount}
            <td class="no-padding nowrap">
            {if $po.exclude_from_calculate}
                {include file="common/price.tpl" value=""}
            {else}
                {*if $cart.order_id}
                <input type="hidden" name="cart_products[{$key}][configuration][{$po.group_id}][stored_discount]" value="Y" />
                <input type="text" class="input-hidden input-mini cm-numeric" size="5" name="cart_products[{$key}][configuration][{$po.group_id}][discount]" value="{$po.discount}" data-a-sign="{$currencies.$primary_currency.symbol|strip_tags nofilter}" data-a-dec="," data-a-sep="." />
                {else*}
                {include file="common/price.tpl" value=$po.discount}
                {*/if*}
            {/if}
            </td>
            {/if}
            <td class="center">
    <!--            <input type="hidden" name="cart_products[{$key}][configuration][{$po.group_id}][product_ids][]" value="{$po.product_id}" />-->
                {if $po.exclude_from_calculate}
                <input type="hidden" size="3" name="cart_products[{$key}][configuration][{$po.group_id}][amount]" value="{$po.amount}" />
                {/if}
                <span class="cm-reload-{$_key}" id="amount_update_{$_key}">
                    <input class="input-hidden input-micro" type="text" size="3" name="cart_products[{$key}][configuration][{$po.group_id}][amount]" value="{$po.step}" {if $po.exclude_from_calculate}disabled="disabled"{/if} />
                <!--amount_update_{$_key}--></span>
            </td>
            <td class="center">
                <span class="" id="db_subtotal_{$_key}">{include file="common/price.tpl" value=$po.subtotal}</span>
            </td>
            <td class="nowrap">
                <div class="hidden-tools">
                    <a class="cm-confirm icon-trash" href="{"order_management.delete?cart_id=`$_key`"|fn_url}" title="{__("delete")}"></a>
                </div>
            </td>
        <!--product_configuration_update_{$key}_{$pg.group_id}--></tr>
    {else}
        <tr class="cm-reload-{$key} cm-ex-op row-more row-gray" id="product_configuration_update_{$key}_{$pg.group_id}">
            <td class="left">&nbsp;</td>
            <td colspan="{$colspan}">
                {include file="addons/product_configurator/views/products/components/product_configuration.tpl" product=$cp po=$pg show_price_values=true}
            </td>
        <!--product_configuration_update_{$key}_{$pg.group_id}--></tr>
    {/if}
{/foreach}