<div class="cm-reload-{$product.product_id}" id="product_configuration_{$product.product_id}_update">
    {if $product.edit_configuration}
        <input type="hidden" name="product_data[{$product.product_id}][cart_id]" value="{$product.edit_configuration}" />
    {/if}
    <input type="hidden" name="appearance[details_page]" value="{$details_page}" />
    {*<input type="hidden" name="appearance[auto_process]" value="" />*}
    {foreach from=$product.detailed_params key="param" item="value"}
        <input type="hidden" name="additional_info[{$param}]" value="{$value}" />
    {/foreach}
    {*if $product.has_strings}
        <div class="ty-control-group ty-product-options__item product-list-field clearfix">
            <label class="ty-pc-group__label ty-control-group__label">
                {__("string")}
            </label>
            <div class="ty-pc-group__products">
                <div class="ty-pc-group__products-item-block">
                    <select class="cm-dropdown cm-options-update" data-cesbClass="ty-sb-popup-large" disabled="disabled">
                        <option> - {__("prestrung")} - </option>
                    </select>
                </div>
            </div>
        </div>
        <div class="ty-pc-container-separator"></div>
    {/if*}
    {foreach from=$product.product_configurator_groups item="po" name="groups_name"}
    {if !$smarty.foreach.groups_name.first && $po.configurator_group_type != 'T'}<div class="ty-pc-container-separator"></div>{/if}
    <div class="ty-control-group ty-product-options__item product-list-field clearfix {if $po.group_id == $smarty.const.STRINGING_TENSION_GROUP_ID}ty-product-options__item-tension{/if}">
        <label class="ty-pc-group__label ty-control-group__label {if $po.required == 'Y'}cm-required cm-requirement-popup{/if} {if $po.configurator_group_type == "S" && $po.full_description}hidden{/if}" for="group_{$po.group_id}">
            {$po.configurator_group_name}
        </label>
        <div class="ty-pc-group__products" id="pc_{$po.group_id}">
            <input type="hidden" name="product_data[{$product.product_id}][configuration][{$po.group_id}][is_separate]" value="{$po.is_separate}" />
            {if $po.configurator_group_type == "S"}
                <div class="ty-pc-group__products-item-block">
                    <select name="product_data[{$product.product_id}][configuration][{$po.group_id}][product_ids][]" id="group_{$po.group_id}" class="cm-options-update" onchange="fn_change_options('{$obj_prefix}{$obj_id}', '{$obj_id}', '0');"  data-cesbClass="ty-sb-popup-large" {if $product.configuration_out_of_stock}disabled="disabled"{/if}>
                        <option id="product_{$po.group_id}_0" value=""> - {$po.full_description nofilter} - </option>
                        {if !$product.configuration_out_of_stock}
                        {foreach from=$po.products item="group_product"}
                            <option id="product_{$po.group_id}_{$group_product.product_id}" value="{$group_product.product_id}" {if $group_product.selected == "Y"}selected="selected"{/if} {if $group_product.disabled}disabled="disabled"{/if}>{$group_product.product}{if $show_price_values == true && !$group_product.no_price} - {include file="common/price.tpl" value=$group_product.price}{/if}{if $group_product.recommended == "Y"} ({__("recommended")}){/if}</option>
                        {/foreach}
                        {/if}
                    </select>
                </div>
                {if $po.selected_product && !$po.no_product}
                    <div class="ty-pc-group__products-item-link-block">
                        <a class="ty-pc-group__products-item-link" href="{"products.view?product_id=`$po.selected_product`"|fn_url}" target="_blank">{__("go_product_details")}</a>
                    </div>
                {/if}
                {foreach from=$po.products item="group_product" name="descr_links"}
                    {if $group_product.selected == "Y" && $group_product.product_options}
                        <div class="ty-pc-group__products-item-options">
                            {include file="addons/product_configurator/views/products/components/configuration_product_options.tpl" product=$group_product id=$group_product.product_id product_options=$group_product.product_options name="product_data[`$product.product_id`][configuration][`$po.group_id`][options]" request_obj_prefix=$obj_prefix request_obj_id=$obj_id}
                        </div>
                    {/if}
                {/foreach}
                {if $po.show_amount}
                    <div class="ty-pc-group__products-item-amount">
                        <div class="ty-qty clearfix{if $settings.Appearance.quantity_changer == "Y"} changer{/if}">
                            <label class="ty-control-group__label" for="pc_qty_{$po.group_id}">{$po.amount_field|default:__("quantity")}:</label>
                            <div class="ty-center ty-value-changer cm-value-changer">
                                {if $settings.Appearance.quantity_changer == "Y"}
                                    <div class="ty-center ty-value-changer cm-value-changer">
                                    <div class="ty-value-changer-decrease"><a class="cm-decrease ty-value-changer__decrease">&minus;</a></div>
                                {/if}
                                <div class="ty-value-changer-input"><input type="text" size="5" class="ty-value-changer__input cm-amount" id="pc_qty_{$po.group_id}" name="product_data[{$product.product_id}][configuration][{$po.group_id}][amount]" value="{$po.amount}" {if $product.qty_step > 1} data-ca-step="{$product.qty_step}"{/if} data-ca-min-qty="1" data-ca-max-qty="{$po.max_amount}" /></div>
                                <script type="text/javascript">
                                (function(_, $) {
                                    $(function() {
                                        $('#pc_qty_{$po.group_id}').on('change', function() {
                                            fn_change_options('{$obj_prefix}{$obj_id}', '{$obj_id}', '0');
                                        });
                                    });
                                }(Tygh, Tygh.$));
                                </script>
                                {if $settings.Appearance.quantity_changer == "Y"}
                                    <div class="ty-value-changer-increase"><a class="cm-increase ty-value-changer__increase">&#43;</a></div>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>
                {/if}
            {elseif $po.configurator_group_type == "R" }
                {if $po.products}
                <tbody id="group_{$po.group_id}">
                    {foreach from=$po.products item="group_product" name="vars"}
                    {if ($group_product.is_edp != "Y" && $group_product.tracking != "D" && ($group_product.amount <= 0 || $group_product.amount < $group_product.min_qty) && $settings.General.inventory_tracking == "Y" && $settings.General.allow_negative_amount != "Y") || ($group_product.zero_price_action != "P" && !$group_product.price|floatval)}
                        {assign var="disable_product" value=true}
                    {else}
                        {assign var="disable_product" value=false}
                    {/if}
                    {if $smarty.foreach.vars.first && $po.required != "Y"}
                    <tr>
                        <td><input  id="group_{$po.group_id}_product_0" type="radio" class="radio {if $disable_product}cm-configurator-disabled{/if}" name="product_data[{$product.product_id}][configuration][{$po.group_id}][product_ids]" value="0" onclick="fn_change_options('{$obj_id|default:$product.product_id}', '{$obj_id|default:$product.product_id}', '0');" checked="checked" {if $group_product.disabled || $disable_product}disabled="disabled"{/if} /></td>
                        <td>&nbsp;{__("none")}</td>
                        <td>&nbsp;</td>
                    </tr>
                    {/if}
                    
                    <tr>
                        <td><input type="radio" class="radio cm-no-change {if $disable_product}cm-configurator-disabled{/if}" id="group_{$po.group_id}_product_{$group_product.product_id}" name="product_data[{$product.product_id}][configuration][{$po.group_id}][product_ids]" value="{$group_product.product_id}" onclick="fn_change_options('{$obj_id|default:$product.product_id}', '{$obj_id|default:$product.product_id}', '0');" {if $group_product.selected == "Y" && false == $disable_product}checked="checked"{/if} {if $group_product.disabled == true || $disable_product}disabled="disabled"{/if} /></td>
                        <td style="width: 100%">{if $group_product.is_accessible}{include file="common/popupbox.tpl" id="description_`$po.group_id`_`$group_product.product_id`" link_text=$group_product.product text=$group_product.product href="products.configuration_product?group_id=`$po.group_id`&product_id=`$group_product.product_id`" content=""}{else}{$group_product.product}{/if}{if $group_product.recommended == "Y"} <span>({__("recommended")})</span>{/if}</td>
                        <td class="right">&nbsp;{if $show_price_values == true}<span class="price">{include file="common/price.tpl" value=$group_product.price}</span>{/if}</td>
                    </tr>
                    {/foreach}
                </tbody>
                {else}
                <span class="price strong"> {__("text_no_products_defined")}</span>
                {/if}
            {elseif $po.configurator_group_type == "C"}
                {if $po.products}
                    <tbody id="group_{$po.group_id}">
                    {foreach from=$po.products item="group_product"}
                    
                    {if ($group_product.is_edp != "Y" && $group_product.tracking != "D" && ($group_product.amount <= 0 || $group_product.amount < $group_product.min_qty) && $settings.General.inventory_tracking == "Y" && $settings.General.allow_negative_amount != "Y") || ($group_product.zero_price_action != "P" && !$group_product.price|floatval)}
                        {assign var="disable_product" value=true}
                    {else}
                        {assign var="disable_product" value=false}
                    {/if}
                    <tr>
                        <td>
                            <input type="checkbox" class="checkbox cm-no-change {if $disable_product}cm-configurator-disabled{/if}" id="group_{$po.group_id}_product_{$group_product.product_id}" name="product_data[{$product.product_id}][configuration][{$po.group_id}][product_ids][]" value="{$group_product.product_id}" onclick="fn_change_options('{$obj_id|default:$product.product_id}', '{$obj_id|default:$product.product_id}', '0');" {if $group_product.selected == "Y" && false == $disable_product}checked="checked"{/if} {if $group_product.disabled == true || $disable_product}disabled="disabled"{/if} /></td>
                        <td style="width: 100%">{if $group_product.is_accessible}{include file="common/popupbox.tpl" id="description_`$po.group_id`_`$group_product.product_id`" link_text=$group_product.product text=$group_product.product href="products.configuration_product?group_id=`$po.group_id`&product_id=`$group_product.product_id`" content=""}{else}{$group_product.product}{/if}{if $group_product.recommended == "Y"} <span>({__("recommended")})</span>{/if}</td>
                        <td class="right">&nbsp;{if $show_price_values == true}<span class="price">{include file="common/price.tpl" value=$group_product.price}</span>{/if}</td>
                    </tr>
                    {/foreach}
                    </tbody>
                {else}
                <p class="price">{__("text_no_products_defined")}</p>
                {/if}
            {elseif $po.configurator_group_type == "T"}
                {if $po.product}
                    <tbody id="group_{$po.group_id}">
                    <tr>
                        <td>
                            <input type="checkbox" class="checkbox cm-no-change cm-configurator-disabled hidden" id="group_{$po.group_id}_product_{$po.product.product_id}" name="product_data[{$product.product_id}][configuration][{$po.group_id}][product_ids][]" value="{$po.product.product_id}" checked="checked" /></td>
                        <td class="right">&nbsp;{if $show_price_values == true}<span class="price">{include file="common/price.tpl" value=$po.product.price}</span>{/if}</td>
                    </tr>
                    </tbody>
                    <div class="ty-pc-group__products-item-options">
                        {if $po.product.product_options}
                            {include file="addons/product_configurator/views/products/components/configuration_product_options.tpl" product=$po.product id=$po.product.product_id product_options=$po.product.product_options name="product_data[`$product.product_id`][configuration][`$po.group_id`][options]" request_obj_prefix=$obj_prefix request_obj_id=$obj_id}
                        {/if}
                    </div>
                {/if}
            {/if}
        </div>
    </div>
    {/foreach}
<!--product_configuration_{$product.product_id}_update--></div>

<script type="text/javascript">
(function(_, $) {
    $(function() {
        fn_change_options('{$obj_prefix}{$obj_id}', '{$obj_id}', '0');
    });
}(Tygh, Tygh.$));
</script>
