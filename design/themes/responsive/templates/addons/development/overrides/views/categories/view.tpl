<div id="category_products_{$block.block_id}">

{if $category_data.note_text}
    <div class="ty-category-title__note-block">{if $category_data.note_url}<a href="{"`$category_data.note_url`"|fn_url}" target="_blank">{/if}<div class="ty-category-title__note"><div class="ty-category-title__note-text">{$category_data.note_text}</div><div class="ty-category-title__note-question"></div></div>{if $category_data.note_url}</a>{/if}</div>
{/if}

{*if $subcategories && !$category_data.parent_id}
    {math equation="ceil(n/c)" assign="rows" n=$subcategories|count c="2"}
    {split data=$subcategories size=$rows assign="splitted_subcategories"}
    <ul class="subcategories clearfix">
    {foreach from=$splitted_subcategories item="ssubcateg"}
        {foreach from=$ssubcateg item=category name="ssubcateg"}
            {if $category}
                <div class="ty-column2">
                    <div class="ty-category-block-wrapper" id="category_block_{$category.category_id}" style="background: url('{$category.main_pair.detailed.http_image_path}')">
                        <div class="ty-block-categories__overlay"></div>
                        <div class="ty-block-categories-bottom-right">
                            <a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"><div class="ty-block-categories__item ty-block-categories__title">{$category.category}</div></a>
                        </div>
                    </div>
                    <a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"  id="category_block_link_{$category.category_id}"></a>
                    <script type="text/javascript">
                        Tygh.$(document).ready(function() {$ldelim}
                            $('#category_block_' + '{$category.category_id}').click(function(){$ldelim}
                                $('#category_block_link_' + '{$category.category_id}').click();
                            {$rdelim});
                        {$rdelim});
                    </script>
                </div>
            {/if}
        {/foreach}
    {/foreach}
    </ul>
{/if*}

{if $smarty.request.advanced_filter}
    {include file="views/products/components/product_filters_advanced_form.tpl" separate_form=true}
{/if}

{if $stb_feature.ranges}
    {assign var="request_data" value=$smarty.request}
    {assign var="ajax_div_ids" value="product_filters_*,products_search_*,category_products_*,product_features_*,breadcrumbs_*,currencies_*,languages_*,tabs_categorization,subtabs_categorization"}
    {if $smarty.server.QUERY_STRING|strpos:"dispatch=" !== false}
        {assign var="filter_qstring" value=$config.current_url|fn_query_remove:"result_ids":"full_render":"filter_id":"view_all":"req_range_id":"advanced_filter":"subcats":"page":"stc_id"}
    {else}
        {assign var="filter_qstring" value="products.search"}
    {/if}
    <div class="clearfix">
        {*<div class="ty-categorization-subtabs__title">{$stb_feature.description}:</div>*}
        <div class="ty-categorization-subtabs" id="subtabs_categorization">
            {$cst_width = 100 / $stb_feature.ranges|count}
            {strip}
            {foreach from=$stb_feature.ranges item=range key=key}
                {if $range.seo_variants == 'Y' && $stb_feature.selected_ranges}
                    {$request_data.features_hash = $smarty.request.features_hash|fn_clean_ranges_from_feature_hash:$stb_feature.selected_ranges:$stb_feature.field_type}
                {/if}
                {if !$range.is_selected}
                    {assign var="filter_query_elm" value=$request_data.features_hash|fn_add_range_to_url_hash:$range:$filter.field_type}
                    {assign var="href" value=$filter_qstring|fn_link_attach:"features_hash=`$filter_query_elm`"}
                {else}
                    {assign var="filter_query_elm" value=$request_data.features_hash|fn_delete_range_from_url:$range:$stb_feature.field_type}
                    {assign var="href" value=$filter_qstring|fn_link_attach:"features_hash=`$filter_query_elm`"}
                {/if}
                {if $range.range_id == $smarty.const.KIRSCHBAUM_BRAND_ID}
                    {$img_ht = "50"}
                {else}
                    {$img_ht = "35"}
                {/if}
                <div class="ty-categorization-subtabs__item {if $range.is_selected}ty-categorization-subtabs__item-active{/if}{if $range.disabled}ty-categorization-subtabs__item-disabled{/if}" style="width: {$cst_width}%;">
                    <div class="ty-categorization-subtabs__item-logo">{include file="addons/development/common/brand_logo.tpl" brand=$range brand_variant_id=$range.variant_id img_height=$img_ht}</div>
                    <a class="ty-categorization-subtabs__a text-hide {if $category_data.ajax_pagination == 'Y'}cm-ajax-force cm-ajax cm-ajax-full-render cm-history{/if}" data-ca-scroll=".cm-pagination-container" data-ca-target-id="{$ajax_div_ids}" {if !$range.disabled}href="{$href|fn_url}"{/if}>
                        {if $range.is_selected}{if $tb_feature_selected}<h3>{else}<h2>{/if}{/if}{$range.range_name}{if $range.is_selected}{if $tb_feature_selected}</h3>{else}</h2>{/if}{/if}
                    </a>
                </div>
            {/foreach}
            {/strip}
        <!--subtabs_categorization--></div>
    </div>
{/if}

{if $products}
{assign var="layouts" value=""|fn_get_products_views:false:0}
{if $category_data.product_columns}
    {assign var="product_columns" value=$category_data.product_columns}
{else}
    {assign var="product_columns" value=$settings.Appearance.columns_in_products_list}
{/if}

{if $layouts.$selected_layout.template}
    <div itemscope itemtype="http://schema.org/ItemList">
        {include file="`$layouts.$selected_layout.template`" columns=$product_columns microdata=true category_grid=true ajax_pagination=$category_data.ajax_pagination}
    </div>
{/if}

{elseif !$subcategories || $show_no_products_block}
<p class="ty-no-items cm-pagination-container">{__("text_no_products")}</p>
{else}
<div class="cm-pagination-container"></div>
{/if}
{if ($category_data.full_description || $runtime.customization_mode.live_editor)}
    <div class="ty-wysiwyg-content ty-mb-s ty-no-select ty-category-description" {live_edit name="category:full_description:{$category_data.category_id}"}>{$category_data.full_description nofilter}</div>
{/if}
<!--category_products_{$block.block_id}--></div>

{capture name="mainbox_title"}
{if $category_data.brand.image_pair.icon.image_path}
    <div class="ty-category__title">
        <div class="ty-category__title-logo">
            {if $category.brand_id == $smarty.const.KIRSCHBAUM_BRAND_ID}
                {$img_height = "70"}
            {else}
                {$img_height = "50"}
            {/if}
            {include file="addons/development/common/brand_logo.tpl"  brand=$category_data.brand brand_variant_id=$category_data.brand_id img_height=$img_height}
        </div>
        {if $category_data.description}
            <div class="ty-wysiwyg-content ty-category__title-descr ty-no-select" {live_edit name="category:description:{$category_data.category_id}"}>{$category_data.description nofilter}</div>
        {/if}
    </div>
{/if}
{/capture}
