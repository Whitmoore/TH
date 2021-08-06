{capture name="mainbox"}

{assign var="c_id_url" value=$config.current_url|fn_query_remove:"c_id"}
<select name="c_id" onchange="Tygh.$.redirect(this.value);">
    <option value="{$c_id_url|fn_url}"> {__("all")} </option>
    {foreach from=""|fn_get_competitors_list item="comp"}
        <option value="{$c_id_url|fn_link_attach:"c_id=`$comp.competitor_id`"|fn_url}" {if $c_id && $c_id == $comp.competitor_id}selected="selected"{/if}>{$comp.name}</option>
    {/foreach}
</select>

<div class="cm-j-tabs cm-track tabs tabs-with-conf">
    {assign var="mode_url" value=$config.current_url|fn_query_remove:"mode"}
    <ul class="nav nav-tabs">
        <li id="group_different" class="{if $mode == 'D'} active extra-tab{/if}">
            <a href="{$mode_url|fn_link_attach:"mode=D"|fn_url}">{__("cprices_differ")}</a>
        </li>
        <li id="group_no_competitor" class="{if $mode == 'N'} active extra-tab{/if}">
            <a href="{$mode_url|fn_link_attach:"mode=N"|fn_url}">{__("cprices_no_competitor")}</a>
        </li>
        <li id="group_no_competitor_all" class="{if $mode == 'A'} active extra-tab{/if}">
            <a href="{$mode_url|fn_link_attach:"mode=A"|fn_url}">{__("cprices_no_competitor_all")}</a>
        </li>
    </ul>
</div>

{if $mode == 'A'}
    <form action="{""|fn_url}" method="get" class="form-edit">
        <input type="hidden" name="mode" value="{$mode}">
        <input type="hidden" name="cid" value="{$search.cid}">
        <input type="hidden" name="c_id" value="{$c_id}">
        {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
            {if $search.cid}
                {assign var="s_cid" value=$search.cid}
            {else}
                {assign var="s_cid" value="0"}
            {/if}
            {include file="pickers/categories/picker.tpl" company_ids=$picker_selected_companies data_id="location_category" input_name="cid" item_ids=$s_cid hide_link=true hide_delete_button=true default_name=__("all_categories") extra=""}
        {else}
            {if $runtime.mode == "picker"}
                {assign var="trunc" value="38"}
            {else}
                {assign var="trunc" value="25"}
            {/if}
            {assign var="cid_url" value=$config.current_url|fn_query_remove:"cid"}
            <select name="cid" onchange="Tygh.$.redirect(this.value);" {if !$c_id}disabled="disabled"{/if}>
                <option value="{$cid_url|fn_url}" {if $category_data.parent_id == "0"}selected="selected"{/if}>- {__("all_categories")} -</option>
                {foreach from=0|fn_get_plain_categories_tree:false:$smarty.const.CART_LANGUAGE:$picker_selected_companies item="search_cat" name=search_cat}
                {if $search_cat.store}
                {if !$smarty.foreach.search_cat.first}
                    </optgroup>
                {/if}

                <optgroup label="{$search_cat.category}">
                    {assign var="close_optgroup" value=true}
                    {else}
                    <option value="{$cid_url|fn_link_attach:"cid=`$search_cat.category_id`"|fn_url}" {if $search_cat.disabled}disabled="disabled"{/if} {if $search.cid == $search_cat.category_id}selected="selected"{/if} title="{$search_cat.category}">{$search_cat.category|escape|truncate:$trunc:"...":true|indent:$search_cat.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
                    {/if}
                    {/foreach}
                    {if $close_optgroup}
                </optgroup>
                {/if}
            </select>
        {/if}
    </form>
{/if}

<form action="{""|fn_url}" method="post" name="c_prices_form" class="form-edit">
    <input type="hidden" name="mode" value="{$mode}">
    <input type="hidden" name="cid" value="{$search.cid}">
    <input type="hidden" name="c_id" value="{$c_id}">

{if $competitive_prices}
    {foreach from=$competitive_prices item=category}
        {if $mode != 'A'}<div class="center ty-c-prices-title">{$category.category_id|fn_get_category_name}</div>{/if}
        <div class="">
            {foreach from=$category.products item=product}
                {if $mode == 'D'}
                    <div class="ty-cp-row">
                        <div class="ty-cp-cell ty-cp-checkbox">
                            <input type="checkbox" name="product_ids[]" value="{$product.product_id},{$product.main_competitor.item_id}" class="checkbox cm-item" /></div>
                        <div class="ty-cp-cell ty-cp-org-name"><a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product}</a></div>
                        <div class="ty-cp-cell ty-cp-code">{$product.product_code}</div>
                        <div class="ty-cp-cell ty-cp-price {if $product.price <= $product.main_competitor.price}ty-c-price-lower{else}ty-c-price-higher{/if}">{include file="common/price.tpl" value=$product.price}</div>
                        <div class="ty-cp-cell ty-cp-code">{$product.main_competitor.code}</div>
                        <div class="ty-cp-cell ty-cp-competitor">{include file="addons/dv_competitors/common/product_competitors.tpl" product_data=$product}</div>
                        {*<div class="ty-cp-cell ty-cp-price">{include file="common/price.tpl" value=$product.main_competitor.price}</div>
                        <div class="ty-cp-cell ty-cp-c-name"><a target="_blank" href="{$product.main_competitor.link}">{$product.main_competitor.name}</a></div>
                        <div class="ty-cp-cell ty-cp-stock">{if $product.main_competitor.in_stock != 'Y'}{__("cprices_out_of_stock")}{/if}</div>*}
                    </div>
                {else}
                    <div class="ty-cp-row">
                        <div class="ty-cp-cell ty-cp-name"><a href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product}</a></div>
                        <div class="ty-cp-cell ty-cp-code">{$product.product_code}</div>
                        <div class="ty-cp-cell ty-cp-price">{include file="common/price.tpl" value=$product.price}</div>
                        {include file="addons/dv_competitors/common/add_pair.tpl" product=$product input="pairs[{$product.product_id}]"}
                    </div>
                {/if}
            {/foreach}
        </div>
    {/foreach}
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}


{capture name="buttons"}
    {if $competitive_prices && $mode == 'D'}
        {include file="buttons/button.tpl" but_text=__("equalize_prices") but_role="submit-link" but_name="dispatch[competitors.eqialize_prices]" but_target_form="c_prices_form"}
        {include file="buttons/button.tpl" but_text=__("equalize_prices_as_discounts") but_role="submit-link" but_name="dispatch[competitors.eqialize_prices.discounts]" but_target_form="c_prices_form"}
    {/if}
    {if $mode != 'D'}
        {include file="buttons/save.tpl" but_role="submit-link" but_name="dispatch[competitors.add_pairs]" but_target_form="c_prices_form"}
    {/if}
{/capture}
</form>
{/capture}

{include file="common/mainbox.tpl" title=__("competitive_prices") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
