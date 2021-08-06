{if ($settings.General.display_options_modifiers == "Y" && ($auth.user_id  || ($settings.General.allow_anonymous_shopping != "hide_price_and_add_to_cart" && !$auth.user_id)))}
{assign var="show_modifiers" value=true}
{/if}

<input type="hidden" name="appearance[details_page]" value="{$details_page}" />
<input type="hidden" name="appearance[auto_process]" id="auto_process_form" value="" />
{foreach from=$product.detailed_params key="param" item="value"}
    <input type="hidden" name="additional_info[{$param}]" value="{$value}" />
{/foreach}

{if $product_options}

{if $obj_prefix}
    <input type="hidden" name="appearance[obj_prefix]" value="{$obj_prefix}" />
{/if}

{if $location == "cart" || $product.object_id}
    <input type="hidden" name="{$name}[{$id}][object_id]" value="{$id|default:$obj_id}" />
{/if}

{if $extra_id}
    <input type="hidden" name="extra_id" value="{$extra_id}" />
{/if}

{* Simultaneous options *}
{if $product.options_type == "S" && $location == "cart"}
    {$disabled = true}
{/if}



<div class="cm-picker-product-options ty-product-options" id="opt_{$obj_prefix}{$id}">
    {foreach name="product_options" from=$product_options item="po"}
    
    {assign var="selected_variant" value=""}
    <div class="ty-control-group ty-product-options__item{if !$capture_options_vs_qty} product-list-field{/if} clearfix" id="opt_{$obj_prefix}{$id}_{$po.option_id}">
        {capture name="variant_images"}
            {if !$po.disabled && !$disabled}
                {foreach from=$po.variants item="var"}
                    {if $var.variant_id == $po.value}{assign var="_class" value="is-selected"}{else}{assign var="_class" value="ty-product-variant-image-unselected"}{/if}
                    {if $var.images}
                        {include file="common/image.tpl" class="ty-hand $_class ty-product-options__image" images=$var.images|reset image_width="65" image_height="65" obj_id="variant_image_`$obj_prefix``$id`_`$po.option_id`_`$var.variant_id`" image_onclick="fn_set_option_value('`$obj_prefix``$id`', '`$po.option_id`', '`$var.variant_id`'); void(0);"}
                    {elseif $po.has_variant_additional || ($var.image_pair.image_id && $po.variants|count == 1)}
                        {$main_pair = $product.org_main_pair|default:$product.main_pair}
                        {include file="common/image.tpl" class="ty-hand $_class ty-product-options__image" images=$main_pair image_width="65" image_height="65" obj_id="variant_image_`$obj_prefix``$id`_`$po.option_id`_`$var.variant_id`" image_onclick="fn_set_option_value('`$obj_prefix``$id`', '`$po.option_id`', '`$var.variant_id`'); void(0);"}
                    {elseif $var.image_pair.image_id}
                        {include file="common/image.tpl" class="ty-hand $_class ty-product-options__image ty-product-options__image-plain" images=$var.image_pair image_width="20" image_height="20" obj_id="variant_image_`$obj_prefix``$id`_`$po.option_id`_`$var.variant_id`" image_onclick="fn_set_option_value('`$obj_prefix``$id`', '`$po.option_id`', '`$var.variant_id`'); void(0);"}
                    {/if}
                {/foreach}
            {/if}
        {/capture}
        <div class="{if $smarty.capture.variant_images|trim} hidden{/if}">
        {if !("SRC"|strpos:$po.option_type !== false && !$po.variants && $po.missing_variants_handling == "H")}
            <label {if $po.option_type !== "R" && $po.option_type !== "F"}for="option_{$obj_prefix}{$id}_{$po.option_id}"{/if} class="ty-control-group__label ty-product-options__item-label{if $po.required == "Y"} cm-required cm-requirement-popup{/if}{if $po.regexp} cm-regexp{/if} {if $po.option_type == 'S' || $po.option_type == "I" || $po.option_type == "T"}hidden{/if}" {if $po.regexp}data-ca-regexp="{$po.regexp}" data-ca-message="{$po.incorrect_message}"{/if}>
                {$po.option_name}
            </label>
            {if $po.option_type == "S"} {*Selectbox*}
                {if ($po.disabled || $disabled) && !$po.not_required}<input type="hidden" value="{$po.value}" name="{$name}[{$id}][product_options][{$po.option_id}]" id="option_{$obj_prefix}{$id}_{$po.option_id}" />{/if}
                <select name="{$name}[{$id}][product_options][{$po.option_id}]" {if !$po.disabled && !$disabled}id="option_{$obj_prefix}{$id}_{$po.option_id}"{/if} onchange="{if $product.options_update}fn_change_options('{$obj_prefix}{$id}', '{$id}', '{$po.option_id}');{else} fn_change_variant_image('{$obj_prefix}{$id}', '{$po.option_id}');{/if}" class="cm-dropdown {if $product.exclude_from_calculate && !$product.aoc || $po.disabled || $disabled}disabled{/if}{if $product.options_update} cm-options-update{/if}" {if $product.exclude_from_calculate && !$product.aoc || $po.disabled || $disabled || !$po.variants}disabled="disabled"{/if} data-no-scroll="true">
                    {if $product.options_type == "S"}
                        {if !$runtime.checkout || $po.disabled || $disabled || ($runtime.checkout && !$po.value)}
                            <option value="">{if $po.disabled || $disabled}{if $po.default_text} - {$po.default_text} - {else}{__("select_option_above")}{/if}{elseif $po.default_text} - {$po.default_text} - {else}{__("please_select_one")}{/if}</option>
                        {/if}
                    {/if}
                    {foreach from=$po.variants item="vr" name=vars}
                        {if !($po.disabled || $disabled) || (($po.disabled || $disabled) && $po.value && $po.value == $vr.variant_id)}
                            <option value="{$vr.variant_id}" {if $po.value == $vr.variant_id}{assign var="selected_variant" value=$vr.variant_id}selected="selected"{/if}>{$po.option_name} {$vr.variant_name} {if $show_modifiers}{hook name="products:options_modifiers"}{if $vr.modifier|floatval}({include file="common/modifier.tpl" mod_type=$vr.modifier_type mod_value=$vr.modifier display_sign=true}){/if}{/hook}{/if}</option>
                        {/if}
                    {/foreach}
                </select>
                {if !$po.variants}
                    <input type="hidden" name="{$name}[{$id}][product_options][{$po.option_id}]" value="{$po.value}" id="option_{$obj_prefix}{$id}_{$po.option_id}" />
                {/if}
            {elseif $po.option_type == "R"} {*Radiobutton*}
                {if $po.variants}
                    <ul id="option_{$obj_prefix}{$id}_{$po.option_id}_group" class="ty-product-options__elem">
                        {if !$po.disabled && !$disabled}
                            <li class="hidden"><input type="hidden" name="{$name}[{$id}][product_options][{$po.option_id}]" value="{$po.value}" id="option_{$obj_prefix}{$id}_{$po.option_id}" /></li>
                            
                            {foreach from=$po.variants item="vr" name="vars"}
                                <li><label id="option_description_{$obj_prefix}{$id}_{$po.option_id}_{$vr.variant_id}" class="ty-product-options__box option-items"><input type="radio" class="radio" name="{$name}[{$id}][product_options][{$po.option_id}]" value="{$vr.variant_id}" {if $po.value == $vr.variant_id }{assign var="selected_variant" value=$vr.variant_id}checked="checked"{/if} onclick="{if $product.options_update}fn_change_options('{$obj_prefix}{$id}', '{$id}', '{$po.option_id}');{else} fn_change_variant_image('{$obj_prefix}{$id}', '{$po.option_id}', '{$vr.variant_id}');{/if}" {if $product.exclude_from_calculate && !$product.aoc || $po.disabled || $disabled}disabled="disabled"{/if} />
                                {$vr.variant_name}&nbsp;{if  $show_modifiers}{hook name="products:options_modifiers"}{if $vr.modifier|floatval}({include file="common/modifier.tpl" mod_type=$vr.modifier_type mod_value=$vr.modifier display_sign=true}){/if}{/hook}{/if}</label></li>
                            {/foreach}
                        {elseif $po.value}
                            {$po.variants[$po.value].variant_name}
                        {/if}
                    </ul>
                    {if !$po.value && $product.options_type == "S" && !($po.disabled || $disabled)}<p class="ty-product-options__description ty-clear-both">{__("please_select_one")}</p>{elseif !$po.value && $product.options_type == "S" && ($po.disabled || $disabled)}<p class="ty-product-options__description ty-clear-both">{if $po.default_text} - {$po.default_text} - {else}{__("select_option_above")}{/if}</p>{/if}
                {else}
                    <input type="hidden" name="{$name}[{$id}][product_options][{$po.option_id}]" value="{$po.value}" id="option_{$obj_prefix}{$id}_{$po.option_id}" />
                    <span>{__("na")}</span>
                {/if}

            {elseif $po.option_type == "C"} {*Checkbox*}
                {foreach from=$po.variants item="vr"}
                {if $vr.position == 0}
                    <input id="unchecked_option_{$obj_prefix}{$id}_{$po.option_id}" type="hidden" name="{$name}[{$id}][product_options][{$po.option_id}]" value="{$vr.variant_id}" {if $po.disabled || $disabled}disabled="disabled"{/if} />
                {else}
                    <label class="ty-product-options__box option-items">
                        <span class="cm-field-container">
                            <input id="option_{$obj_prefix}{$id}_{$po.option_id}" type="checkbox" name="{$name}[{$id}][product_options][{$po.option_id}]" value="{$vr.variant_id}" class="checkbox" {if $po.value == $vr.variant_id}checked="checked"{/if} {if $product.exclude_from_calculate && !$product.aoc || $po.disabled || $disabled}disabled="disabled"{/if} {if $product.options_update}onclick="fn_change_options('{$obj_prefix}{$id}', '{$id}', '{$po.option_id}');"{/if}/>
                            {if $show_modifiers}{hook name="products:options_modifiers"}{if $vr.modifier|floatval}({include file="common/modifier.tpl" mod_type=$vr.modifier_type mod_value=$vr.modifier display_sign=true}){/if}{/hook}{/if}
                        </span>
                    </label>
                {/if}
                {foreachelse}
                    <label class="ty-product-options__box option-items"><input type="checkbox" class="checkbox" disabled="disabled" />
                    {if $show_modifiers}{hook name="products:options_modifiers"}{if $vr.modifier|floatval}({include file="common/modifier.tpl" mod_type=$vr.modifier_type mod_value=$vr.modifier display_sign=true}){/if}{/hook}{/if}</label>
                {/foreach}

            {elseif $po.option_type == "I"} {*Input*}
                <input id="option_{$obj_prefix}{$id}_{$po.option_id}" placeholder="{$po.option_name}" type="text" name="{$name}[{$id}][product_options][{$po.option_id}]" value="{$po.value|default:$po.inner_hint}" {if $product.exclude_from_calculate && !$product.aoc}disabled="disabled"{/if} class="ty-valign ty-input-text{if $po.inner_hint} cm-hint{/if}{if $product.exclude_from_calculate && !$product.aoc} disabled{/if}" {if $po.inner_hint}title="{$po.inner_hint}"{/if} {if $disabled}disabled="disabled"{/if} />
            {elseif $po.option_type == "T"} {*Textarea*}
                <textarea id="option_{$obj_prefix}{$id}_{$po.option_id}" placeholder="{$po.option_name}" class="ty-product-options__textarea{if $po.inner_hint} cm-hint{/if}{if $product.exclude_from_calculate && !$product.aoc} disabled{/if}" rows="3" name="{$name}[{$id}][product_options][{$po.option_id}]" {if $product.exclude_from_calculate && !$product.aoc}disabled="disabled"{/if} {if $po.inner_hint}title="{$po.inner_hint}"{/if} {if $disabled}disabled="disabled"{/if}>{$po.value|default:$po.inner_hint}</textarea>
            {elseif $po.option_type == "F"} {*File*}
                <div class="ty-product-options__elem ty-product-options__fileuploader">
                    {include file="common/fileuploader.tpl" images=$product.extra.custom_files[$po.option_id] var_name="`$name`[`$po.option_id``$id`]" multiupload=$po.multiupload hidden_name="`$name`[custom_files][`$po.option_id``$id`]" hidden_value="`$id`_`$po.option_id`" label_id="option_`$obj_prefix``$id`_`$po.option_id`" prefix=$obj_prefix}
                </div>
            {/if}
            {if $po.note_text}
                {include file="addons/development/common/tooltip.tpl" note_text=$po.note_text note_url=$po.note_url tooltipclass="ty-option-tooltip"}
            {elseif $po.popup_content}
                <div class="ty-sizing-table">
                    {include file="common/popupbox.tpl"
                    content=$po.popup_content
                    link_text=$po.popup_title
                    text=$po.popup_title
                    id="sizing_table_`$product.product_id`"
                    link_meta=$link_meta}
                </div>
            {/if}
        {/if}
        </div>

        {if $po.comment}
            <div class="ty-product-options__description">{$po.comment}</div>
        {/if}

        {if $smarty.capture.variant_images|trim}
            <script type="text/javascript">
            (function(_, $) {
                $('#option_{$obj_prefix}{$id}_{$po.option_id}').removeClass('cm-dropdown');
            }(Tygh, Tygh.$));
            </script>
            <div class="ty-product-variant-image">{$smarty.capture.variant_images nofilter}</div>
        {/if}
    </div>
    {/foreach}
</div>
{if $product.show_exception_warning == "Y"}
    <p id="warning_{$obj_prefix}{$id}" class="cm-no-combinations{if $location != "cart"}-{$obj_prefix}{$id}{/if}">{__("nocombination")}</p>
{/if}
{/if}

{if !$no_script}
<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.formpre_{$form_name|default:"product_form_`$obj_prefix``$id`"}', function(frm, elm) {
        if ($('.cm-no-combinations{if $location != "cart"}-{$obj_prefix}{$id}{/if}').length) {
            $.ceNotification('show', {
                type: 'W', 
                title: _.tr('warning'), 
                message: _.tr('cannot_buy'),
            });

            return false;
        }
            
        return true;
    });
}(Tygh, Tygh.$));
</script>
{/if}
