{if $image_title}
    {if $tb_feature.variants || $stb_feature.variants}
        <div class="clearfix" id="tabs_categorization">ccc
            {assign var="ajax_div_ids" value="product_filters_*,products_search_*,category_products_*,product_features_*,breadcrumbs_*,currencies_*,languages_*,tabs_categorization"}
            {if $tb_feature.variants}
                {if $smarty.server.QUERY_STRING|strpos:"dispatch=" !== false}
                    {assign var="filter_qstring" value=$config.current_url|fn_query_remove:"result_ids":"full_render":"filter_id":"view_all":"req_range_id":"advanced_filter":"subcats":"page":"tc_id"}
                {else}
                    {assign var="filter_qstring" value="products.search"}
                {/if}
                <div class="ty-categorization-tabs clearfix">
                    {$columns = $tb_feature.variants|count}
                    {foreach from=$tb_feature.variants item=tab key=key}
                        {if !$active_tab}
                            {assign var="active_tab" value=$key}
                        {/if}
                        <div class="ty-categorization-tabs__item ty-categorization-tabs__item{$columns} {if $key == $active_tab}ty-categorization-tabs__item-active{/if}"><a class="ty-categorization-tabs__a cm-ajax-force cm-ajax cm-ajax-full-render cm-history" data-ca-scroll=".cm-pagination-container" data-ca-target-id="{$ajax_div_ids}" {if $key != $active_tab}href="{$filter_qstring|fn_link_attach:"tc_id=`$key`"|fn_url}"{/if}>{if $key == $active_tab && $key != 'all'}<h2>{else}<span>{/if}{$tab.variant}{if $key == $active_tab && $key != 'all'}</h2>{else}</span>{/if}</a></div>
                    {/foreach}
                </div>
            {/if}
        <!--tabs_categorization--></div>
    {/if}
{/if}