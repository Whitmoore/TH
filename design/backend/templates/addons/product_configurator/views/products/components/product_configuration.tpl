{if !$smarty.foreach.groups_name.first && $po.configurator_group_type != 'T'}<div class="ty-pc-container-separator"></div>{/if}
<div class="ty-control-group ty-product-options__item product-list-field clearfix">
    {$group_id = $po.group_id}
    <input type="hidden" name="cart_products[{$key}][configuration][{$group_id}][object_id]" value="{$po.group_item_id}" />
    <label class="ty-pc-group__label ty-control-group__label {if $po.required == 'Y'}cm-required cm-requirement-popup{/if}" for="group_{$group_id}">
        {$po.configurator_group_name}
    </label>
    <div class="ty-pc-group__products" id="pc_{$group_id}">
        <input type="hidden" name="cart_products[{$key}][configuration][{$group_id}][is_separate]" value="true" />
        {if $po.configurator_group_type == "S"}
            <div class="ty-pc-group__products-item-block">
                <select name="cart_products[{$key}][configuration][{$group_id}][product_ids][]" id="group_{$group_id}" class="cm-dropdown cm-options-update" data-cesbClass="ty-sb-popup-large" {if $product.configuration_out_of_stock}disabled="disabled"{/if} onchange="$('#recalculate_cart').click();">
                    <option id="product_{$group_id}_0" value=""> - {$po.full_description nofilter} - </option>
                    {if !$product.configuration_out_of_stock}
                    {foreach from=$po.products item="group_product"}
                        <option id="product_{$group_id}_{$group_product.product_id}" value="{$group_product.product_id}" {if $group_product.selected == "Y"}selected="selected"{/if} {if $group_product.disabled}disabled="disabled"{/if}>{$group_product.product}{if $show_price_values == true && !$group_product.no_price} - {include file="common/price.tpl" value=$group_product.price}{/if}{if $group_product.recommended == "Y"} ({__("recommended")}){/if}</option>
                    {/foreach}
                    {/if}
                </select>
            </div>
            <div class="ty-pc-group__products-item-options">
                {foreach from=$po.products item="group_product" name="descr_links"}
                    {if $group_product.selected == "Y" && $group_product.product_options}
                        {include file="addons/product_configurator/views/products/components/configuration_product_options.tpl" product=$group_product id=$group_product.product_id product_options=$group_product.product_options name="cart_products[`$key`][configuration][`$group_id`][options]" request_obj_prefix=$obj_prefix request_obj_id=$key}
                    {/if}
                {/foreach}
            </div>
            {if $po.show_amount}
                <div class="ty-pc-group__products-item-amount">
                    <div class="ty-qty clearfix{if $settings.Appearance.quantity_changer == "Y"} changer{/if}">
                        <label class="ty-control-group__label" for="pc_qty_{$group_id}">{$po.amount_field|default:__("quantity")}:</label>
                        <div class="ty-center ty-value-changer cm-value-changer">
                            {if $settings.Appearance.quantity_changer == "Y"}
                                <a class="cm-increase ty-value-changer__increase">&#43;</a>
                            {/if}
                            <input type="text" size="5" class="ty-value-changer__input cm-amount" id="pc_qty_{$group_id}" name="cart_products[{$key}][configuration][{$group_id}][amount]" value="{$po.amount}" {if $product.qty_step > 1} data-ca-step="{$product.qty_step}"{/if} data-ca-min-qty="1" data-ca-max-qty="{$po.max_amount}" />
                            {if $settings.Appearance.quantity_changer == "Y"}
                                <a class="cm-decrease ty-value-changer__decrease">&minus;</a>
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}
        {elseif $po.configurator_group_type == "R" }
            {if $po.products}
            <tbody id="group_{$group_id}">
                {foreach from=$po.products item="group_product" name="vars"}
                {if ($group_product.is_edp != "Y" && $group_product.tracking != "D" && ($group_product.amount <= 0 || $group_product.amount < $group_product.min_qty) && $settings.General.inventory_tracking == "Y" && $settings.General.allow_negative_amount != "Y") || ($group_product.zero_price_action != "P" && !$group_product.price|floatval)}
                    {assign var="disable_product" value=true}
                {else}
                    {assign var="disable_product" value=false}
                {/if}
                {if $smarty.foreach.vars.first && $po.required != "Y"}
                <tr>
                    <td><input  id="group_{$group_id}_product_0" type="radio" class="radio {if $disable_product}cm-configurator-disabled{/if}" name="cart_products[{$key}][configuration][{$group_id}][product_ids][]" value="0" checked="checked" {if $group_product.disabled || $disable_product}disabled="disabled"{/if} /></td>
                    <td>&nbsp;{__("none")}</td>
                    <td>&nbsp;</td>
                </tr>
                {/if}
                
                <tr>
                    <td><input type="radio" class="radio cm-no-change {if $disable_product}cm-configurator-disabled{/if}" id="group_{$group_id}_product_{$group_product.product_id}" name="cart_products[{$key}][configuration][{$group_id}][product_ids][]" value="{$group_product.product_id}" {if $group_product.selected == "Y" && false == $disable_product}checked="checked"{/if} {if $group_product.disabled == true || $disable_product}disabled="disabled"{/if} /></td>
                    <td style="width: 100%">{if $group_product.is_accessible}{include file="common/popupbox.tpl" id="description_`$group_id`_`$group_product.product_id`" link_text=$group_product.product text=$group_product.product href="products.configuration_product?group_id=`$group_id`&product_id=`$group_product.product_id`" content=""}{else}{$group_product.product}{/if}{if $group_product.recommended == "Y"} <span>({__("recommended")})</span>{/if}</td>
                    <td class="right">&nbsp;{if $show_price_values == true}<span class="price">{include file="common/price.tpl" value=$group_product.price}</span>{/if}</td>
                </tr>
                {/foreach}
            </tbody>
            {else}
            <span class="price strong"> {__("text_no_products_defined")}</span>
            {/if}
        {elseif $po.configurator_group_type == "C"}
            {if $po.products}
                <tbody id="group_{$group_id}">
                {foreach from=$po.products item="group_product"}
                
                {if ($group_product.is_edp != "Y" && $group_product.tracking != "D" && ($group_product.amount <= 0 || $group_product.amount < $group_product.min_qty) && $settings.General.inventory_tracking == "Y" && $settings.General.allow_negative_amount != "Y") || ($group_product.zero_price_action != "P" && !$group_product.price|floatval)}
                    {assign var="disable_product" value=true}
                {else}
                    {assign var="disable_product" value=false}
                {/if}
                <tr>
                    <td>
                        <input type="checkbox" class="checkbox cm-no-change {if $disable_product}cm-configurator-disabled{/if}" id="group_{$group_id}_product_{$group_product.product_id}" name="cart_products[{$key}][configuration][{$group_id}][product_ids][]" value="{$group_product.product_id}" {if $group_product.selected == "Y" && false == $disable_product}checked="checked"{/if} {if $group_product.disabled == true || $disable_product}disabled="disabled"{/if} /></td>
                    <td style="width: 100%">{if $group_product.is_accessible}{include file="common/popupbox.tpl" id="description_`$group_id`_`$group_product.product_id`" link_text=$group_product.product text=$group_product.product href="products.configuration_product?group_id=`$group_id`&product_id=`$group_product.product_id`" content=""}{else}{$group_product.product}{/if}{if $group_product.recommended == "Y"} <span>({__("recommended")})</span>{/if}</td>
                    <td class="right">&nbsp;{if $show_price_values == true}<span class="price">{include file="common/price.tpl" value=$group_product.price}</span>{/if}</td>
                </tr>
                {/foreach}
                </tbody>
            {else}
            <p class="price">{__("text_no_products_defined")}</p>
            {/if}
        {elseif $po.configurator_group_type == "T"}
            {if $po.product}
                <div class="ty-pc-group__products-item-block">
                <input type="hidden" name="cart_products[{$key}][configuration][{$group_id}][product_ids][]" value="{$po.product.product_id}" />
                </div>
                <div class="ty-pc-group__products-item-options">
                    {if $po.product.product_options}
                        {include file="addons/product_configurator/views/products/components/configuration_product_options.tpl" product=$po.product id=$po.product.product_id product_options=$po.product.product_options name="cart_products[`$key`][configuration][`$group_id`][options]" request_obj_prefix=$obj_prefix request_obj_id=$cp.product_id}
                    {/if}
                </div>
            {/if}
        {/if}
    </div>
</div>