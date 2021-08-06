<div class="ty-product-filters__group">
    {if !$range.checked}
        {assign var="filter_query_elm" value=$request_data.features_hash|fn_add_range_to_url_hash:$range:$filter.field_type}
    {else}
        {assign var="filter_query_elm" value=$request_data.features_hash|fn_delete_range_from_url:$range:$filter.field_type}
    {/if}
    {if $request_data.features_hash}
        {assign var="cur_features_hash" value="&features_hash=`$request_data.features_hash`"}
    {/if}
    
    {*if $filter.feature_type == "E" && (!$filter.simple_link || $filter.selected_ranges && $controller == "product_features")}
        {assign var="href" value="product_features.view?variant_id=`$range.range_id``$cur_features_hash`"}
    {else*}
        {assign var="href" value=$filter_qstring|fn_link_attach:"features_hash=`$filter_query_elm`"}
    {*/if*}
    {assign var="use_ajax" value=$href|fn_compare_dispatch:$current_url}
    <div class="ty-product-filters__item{if $range.checked} checked{/if}{if $range.disabled} disabled{/if} cm-filter-item" {if !$range.disabled || $range.checked}data-target-url="{$href}"{/if}><span class="ty-filter-icon"><i class="ty-icon-ok ty-filter-icon__check"></i><i class="ty-icon-cancel ty-filter-icon__delete"></i></span>{$filter.prefix}{$range.range_name|fn_text_placeholders}{$filter.suffix}&nbsp;{*if !$range.disabled}<span class="ty-product-filters__count">&nbsp;({$range.products})</span>{/if*}</div>
</div>
