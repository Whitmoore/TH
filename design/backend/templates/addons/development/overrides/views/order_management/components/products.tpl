<div class="buttons-container">
    {if $cart_products}
        {btn type="delete_selected" dispatch="dispatch[order_management.delete]" form="om_cart_form" class="cm-skip-validation" icon="icon-trash"}
    {/if}
    {include file="pickers/products/picker.tpl" company_id=$order_company_id display="options_price" extra_var="order_management.add" data_id="om" no_container=true}
</div>

<table width="100%" class="table table-middle">
<thead>
    <tr>
        <th class="left">
            {include file="common/check_items.tpl"}</th>
        <th width="50%">{__("product")}</th>
        <th width="20%" colspan="2">{__("price")}</th>
        {if $cart.use_discount}
        <th width="10%" colspan="2">{__("discount")}</th>
        {/if}
        <th class="center">{__("quantity")}</th>
        <th class="center">{__("subtotal")}</th>
        <th>&nbsp;</th>
    </tr>
</thead>

{capture name="extra_items"}
    {hook name="order_management:products_extra_items"}{/hook}
{/capture}

{foreach from=$cart_products item="cp" key="key"}
{hook name="order_management:items_list_row"}
<tr>
    <td class="left">
        <input type="checkbox" name="cart_ids[]" value="{$key}" class="cm-item" /></td>
    <td>
        <a href="{"products.update?product_id=`$cp.product_id`"|fn_url}">{$cp.product nofilter}</a>
        {include file="views/companies/components/company_name.tpl" object=$cp}
    </td>
    <td width="3%">
        {if $cp.exclude_from_calculate}
            {__("free")}
            {else}
            <input type="hidden" name="cart_products[{$key}][stored_price]" value="N" />
            <input class="inline" type="checkbox" name="cart_products[{$key}][stored_price]" value="Y" {if $cp.stored_price == "Y"}checked="checked"{/if} onchange="Tygh.$('#db_price_{$key},#manual_price_{$key}').toggle();"/>
        {/if}
    </td>
    <td class="left">
    {if !$cp.exclude_from_calculate}
        {if $cp.stored_price == "Y"}
            {math equation="price - modifier" price=$cp.original_price modifier=$cp.modifiers_price|default:0 assign="original_price"}
        {else}
            {assign var="original_price" value=$cp.original_price}
        {/if}
        <span class="{if $cp.stored_price == "Y"}hidden{/if}" id="db_price_{$key}">{include file="common/price.tpl" value=$original_price}</span>
        <div class="{if $cp.stored_price != "Y"}hidden{/if}" id="manual_price_{$key}">
            {include file="common/price.tpl" value=$cp.base_price view="input" input_name="cart_products[`$key`][price]" class="input-hidden input-mini" }
        </div>
    {/if}
    </td>
    {if $cart.use_discount}
    <td width="3%">
        {if $cp.exclude_from_calculate}
            {__("free")}
        {else}
            <input type="hidden" name="cart_products[{$key}][stored_discount]" value="N" />
            <input class="inline" type="checkbox" name="cart_products[{$key}][stored_discount]" value="Y" {if $cp.stored_discount == "Y"}checked="checked"{/if} onchange="Tygh.$('#db_discount_{$key},#manual_discount_{$key}').toggle();"/>
        {/if}
    </td>
    <td class="no-padding nowrap">
    {if $cp.exclude_from_calculate}
        {include file="common/price.tpl" value=""}
    {else}
        {if $cart.order_id && !$cp.product_configurator_groups}
        <span class="{if $cp.stored_discount == "Y"}hidden{/if}" id="db_discount_{$key}">{include file="common/price.tpl" value=$cp.discount}</span>
        <div class="{if $cp.stored_discount != "Y"}hidden{/if}" id="manual_discount_{$key}">
            {include file="common/price.tpl" value=$cp.discount view="input" input_name="cart_products[`$key`][discount]" class="input-hidden input-mini" }
        </div>
        {else}
        {include file="common/price.tpl" value=$cp.discount}
        {/if}
    {/if}
    </td>
    {/if}
    <td class="center">
        <input type="hidden" name="cart_products[{$key}][product_id]" value="{$cp.product_id}" />
        {if $cp.exclude_from_calculate}
        <input type="hidden" size="3" name="cart_products[{$key}][amount]" value="{$cp.amount}" />
        {/if}
        <span class="cm-reload-{$key}" id="amount_update_{$key}">
            <input class="input-hidden input-micro" type="text" size="3" name="cart_products[{$key}][amount]" value="{$cp.amount}" {if $cp.exclude_from_calculate}disabled="disabled"{/if} />
        <!--amount_update_{$key}--></span>
    </td>
    <td class="center">
        <span class="" id="db_subtotal_{$key}">{include file="common/price.tpl" value=$cp.subtotal}</span>
    </td>
    <td class="nowrap">
        <div class="hidden-tools">
            <a class="cm-confirm icon-trash" href="{"order_management.delete?cart_id=`$key`"|fn_url}" title="{__("delete")}"></a>
        </div>
    </td>
</tr>
{if $cp.product_options}
<tr id="product_options_{$key}_{$cp.product_id}" class="cm-ex-op row-more row-gray">
    <td>&nbsp;</td>
    <td colspan="{if $cart.use_discount}8{else}7{/if}">
        {include file="views/products/components/select_product_options.tpl" product_options=$cp.product_options name="cart_products" id=$key use_exceptions="Y" product=$cp additional_class="option-item"}
        <div id="warning_{$key}" class="pull-left notification-title-e hidden">&nbsp;&nbsp;&nbsp;{__("nocombination")}</div>
    </td>
</tr>
{/if}
{/hook}
{foreachelse}
    {if $smarty.capture.extra_items|trim == ""}
        <tr>
            <td colspan="{if $cart.use_discount}8{else}7{/if}" class="center">
                <p class="muted">
                    {__("section_is_not_completed")}</br>
                    {__("orders_no_items")}
                </p>
            </td>
        </tr>
    {/if}
{/foreach}

    {$smarty.capture.extra_items nofilter}
</table>