{if $image_title}
    <div class="ty-category-title cm-parallax" style="background: url({$category_data.main_pair.detailed.image_path}) center 109px no-repeat fixed;" data-speed="1.5">
        {*<img src="{$category_data.main_pair.detailed.image_path}" alt="{$category_data.category}" width="1015"/>*}
        <h1 class="ty-image-title__name">
            {if $category_data.feature_title}{$category_data.feature_title}{else}{$category_data.category}{/if}
        </h1>
    </div>
    {if $tb_feature.ranges || $stb_feature.variants}
        {assign var="request_data" value=$smarty.request}
        <div class="ty-categorization clearfix" id="tabs_categorization">
            {assign var="ajax_div_ids" value="product_filters_*,products_search_*,category_products_*,product_features_*,breadcrumbs_*,currencies_*,languages_*,tabs_categorization"}
            {if $tb_feature.ranges}
                {if $smarty.server.QUERY_STRING|strpos:"dispatch=" !== false}
                    {assign var="filter_qstring" value=$config.current_url|fn_query_remove:"result_ids":"full_render":"filter_id":"view_all":"req_range_id":"advanced_filter":"subcats":"page":"tc_id"}
                {else}
                    {assign var="filter_qstring" value="products.search"}
                {/if}
                <div class="ty-categorization-tabs clearfix">
                    <div class="ty-categorization-tabs-bg"></div>
                    <div class="ty-categorization-tabs__list-wrapper">
                    <ul class="ty-categorization-tabs__list" {if $tb_feature.ranges|count < 4}style="width:{$tb_feature.ranges|count * 325.5}px"{else}style="width:100%"{/if}>
                    {$tc_width = 100 / $tb_feature.ranges|count}
                    {foreach from=$tb_feature.ranges item=range key=key}
                        {if $range.seo_variants == 'Y' && $tb_feature.selected_ranges}
                            {$request_data.features_hash = $smarty.request.features_hash|fn_clean_ranges_from_feature_hash:$tb_feature.selected_ranges:$tb_feature.field_type}
                        {/if}
                        {if !$range.is_selected}
                            {assign var="filter_query_elm" value=$request_data.features_hash|fn_add_range_to_url_hash:$range:$filter.field_type}
                            {assign var="href" value=$filter_qstring|fn_link_attach:"features_hash=`$filter_query_elm`"}
                        {else}
                            {assign var="filter_query_elm" value=$request_data.features_hash|fn_delete_range_from_url:$range:$tb_feature.field_type}
                            {assign var="href" value=$filter_qstring|fn_link_attach:"features_hash=`$filter_query_elm`"}
                        {/if}

                        <li class="ty-categorization-tabs__item {if $range.is_selected} active{/if}{if $range.disabled}ty-categorization-tabs__item-disabled{/if}" style="width: {$tc_width}%;">
                            <a class="ty-categorization-tabs__a {if $category_data.ajax_pagination == 'Y'}cm-ajax-force cm-ajax cm-ajax-full-render cm-history{/if}" data-ca-scroll=".cm-pagination-container" data-ca-target-id="{$ajax_div_ids}" {if !$range.disabled}href="{$href|fn_url}"{/if}>{if $range.is_selected}<h2>{else}<span>{/if}{$range.range_name}{if $range.is_selected}</h2>{else}</span>{/if}</a>
                        </li>
                    {/foreach}
                    </ul>
                    </div>
                </div>
            {/if}
        <!--tabs_categorization--></div>
    {/if}
{/if}