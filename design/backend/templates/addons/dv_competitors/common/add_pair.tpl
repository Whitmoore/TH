<div class="ty-add-cpair" id="add_pair_{$product.product_id}">
    <div class="ty-cp-cell ty-cp-item-id">
        <input type="text" name="{$input}[obj_id]" size="55" class="ty-cp-item-id-input" id="cp_item_id_{$product.product_id}" {if $product.main_competitor.pair_id && $c_id && $c_id == $product.main_competitor.competitor_id}value="{$product.main_competitor.item_id}" data-original-id="{$product.main_competitor.item_id}"{elseif $pair.competitive_id}value="{$pair.competitive_id}" data-original-id="{$pair.competitive_id}"{/if}/>
    </div>
    <div class="ty-cp-cell ty-cp-c-id">
        <select name="{$input}[c_id]" onchange="fn_check_pair({$product.product_id}, $(this).val(), '{$input}');" style="width: 100%">
            <option value=""> - </option>
            {foreach from=""|fn_get_competitors_list item="comp"}
                <option value="{$comp.competitor_id}" {if $c_id && $c_id == $comp.competitor_id}selected="selected"{/if}>{$comp.name}</option>
            {/foreach}
        </select>
    </div>
    <div class="ty-cp-cell ty-cp-input" id="dv_block_{$product.product_id}">
        <input type="text" size="55" value="{if $pair.name}{$pair.name}{elseif $product.main_competitor.pair_id && $c_id && $c_id == $product.main_competitor.competitor_id }{$product.main_competitor.name}{else}{$product.product}{/if}" data-product-id="{$product.product_id}" class="input-large cm-competitor-products" id="input_{$product.product_id}" {if $c_id}data-competitor-id="{$c_id}"{/if}/>
        {include file="addons/dv_competitors/views/competitors/prices_results.tpl" product_id=$product.product_id}
    </div>
    <div class="ty-cp-cell ty-cp-action">
        <select name="{$input}[action]" style="width: 100%" id="cp_action_{$product.product_id}" {if !($pair.competitive_id || ($product.main_competitor.pair_id && $c_id && $c_id == $product.main_competitor.competitor_id))}disabled="disabled"{/if}>
            <option value=""> - </option>
            <option value="T">{__("tie")}</option>
            <option value="U" {if $pair.competitive_id}selected="selected"{/if}>{__("untie")}</option>
        </select>
    </div>
    <script type="text/javascript">
        (function (_, $) {ldelim}
            fn_init_cmp_products_search($('#input_' + '{$product.product_id}'));
        {rdelim}(Tygh, Tygh.$));
        $('#dv_block_' + '{$product.product_id}').hover(function(){ldelim}
            $(this).addClass('is-hover');
        {rdelim}, function(){ldelim}
            $(this).removeClass('is-hover');
        {rdelim});
    </script>
<!--add_pair_{$product.product_id}--></div>
