{if $filter.ranges|fn_is_not_empty}
{$columns = 6}
{split data=$filter.ranges size=$columns assign="splitted_ranges" skip_complete=true}
<div class="ty-product-filters {if $collapse}hidden{/if}" id="content_{$filter_uid}">
    <div id="ranges_{$filter_uid}" class="ty-product-filters__item-more">
        {foreach from=$splitted_ranges item="column"}
            {foreach from=$column item="range"}
                {if $range.seo_variants == 'Y' && $filter.selected_ranges}
                    {$request_data.features_hash = $request_data.features_hash|fn_clean_ranges_from_feature_hash:$filter.selected_ranges:$filter.field_type}
                {/if}
                <div class="ty-column{$columns}">
                    <div class="ty-product-filters__group">
                    {if $filter.selected_ranges && $range.range_id|array_key_exists:$filter.selected_ranges}
                        {capture name="has_selected"}Y{/capture}
                            {strip}
                                {assign var="fh" value=$request_data.features_hash|fn_delete_range_from_url:$range:$filter.field_type}
                                {if $fh}
                                    {assign var="attach_query" value="features_hash=`$fh`"}
                                {/if}
                                {if $filter.feature_type == "E" && $range.range_id == $request_data.variant_id}
                                    {assign var="reset_lnk" value=$reset_qstring}
                                {else}
                                    {assign var="reset_lnk" value=$filter_qstring}
                                {/if}
                                {if $fh}
                                    {assign var="href" value=$reset_lnk|fn_link_attach:$attach_query}
                                {else}
                                    {assign var="href" value=$reset_lnk}
                                {/if}
                                {assign var="use_ajax" value=$href|fn_compare_dispatch:$current_url}
                                {if $filter.seo_variants == 'Y'}
                                    <a href="{$href|fn_url}"><div class="ty-product-filters__item checked"><span class="ty-filter-icon"><i class="ty-icon-ok ty-filter-icon__check"></i><i class="ty-icon-cancel ty-filter-icon__delete"></i></span>{$filter.prefix}{$range.range_name|fn_text_placeholders}{$filter.suffix}</div>{if !$range.checked && !$range.disabled}</a>{/if}
                                {else}
                                    <div data-target-url="{$href}" class="ty-product-filters__item checked cm-filter-item"><span class="ty-filter-icon"><i class="ty-icon-ok ty-filter-icon__check"></i><i class="ty-icon-cancel ty-filter-icon__delete"></i></span>{$filter.prefix}{$range.range_name|fn_text_placeholders}{$filter.suffix}</div>
                                {/if}
                            {/strip}
                    {else}
                        {include file="blocks/product_filters/components/variant_item.tpl" range=$range filter=$filter ajax_div_ids=$ajax_div_ids filter_qstring=$filter_qstring reset_qstring=$reset_qstring allow_ajax=$allow_ajax}
                    {/if}
                    </div>
                </div>
            {/foreach}
        {/foreach}
    </div>
</div>
{/if}