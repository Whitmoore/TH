{** block-description:original **}

<div id="product_filters_{$block.block_id}">
<div  class="ty-product-filters__wrapper">
{if "AJAX_REQUEST"|defined && $block.request_data}
    {assign var="request_data" value=$block.request_data}
{else}
    {assign var="request_data" value=$smarty.request}
{/if}
{if "AJAX_REQUEST"|defined && $block.request_data.current_url}
    {assign var="current_url" value=$block.request_data.current_url}
{else}
    {assign var="current_url" value=$config.current_url}
{/if}

{if $items && !$request_data.advanced_filter}

{if $smarty.server.QUERY_STRING|strpos:"dispatch=" !== false}
    {assign var="curl" value=$current_url}
    {assign var="filter_qstring" value=$curl|fn_query_remove:"result_ids":"full_render":"filter_id":"view_all":"req_range_id":"advanced_filter":"features_hash":"subcats":"page"}
{else}
    {assign var="filter_qstring" value="products.search"}
{/if}

{assign var="reset_qstring" value="products.search"}

{if $request_data.category_id && $settings.General.show_products_from_subcategories == "Y"}
    {assign var="filter_qstring" value=$filter_qstring|fn_link_attach:"subcats=Y"}
    {assign var="reset_qstring" value=$reset_qstring|fn_link_attach:"subcats=Y"}
{/if}

{assign var="allow_ajax" value=true}
{assign var="ajax_div_ids" value="product_filters_*,products_search_*,category_products_*,product_features_*,breadcrumbs_*,currencies_*,languages_*,tabs_categorization,subtabs_categorization"}

{assign var="has_selected" value=false}
{foreach from=$items item="filter" name="filters"}
    {if $filter.slider || $filter.selected_ranges || $filter.ranges}
        {assign var="filter_uid" value="`$block.block_id`_`$filter.filter_id`"}
        {assign var="cookie_name_show_filter" value="content_`$filter_uid`"}
        {assign var="collapse" value=true}
        {*if $filter.display == "N"}
            {assign var="collapse" value=true}
            {if $smarty.cookies.$cookie_name_show_filter}
                {assign var="collapse" value=false}
            {/if}
        {else}
            {assign var="collapse" value=false}
            {if $smarty.cookies.$cookie_name_show_filter}
                {assign var="collapse" value=true}
            {/if}
        {/if*}

        <div class="ty-product-filters__block {if !$collapse}is-hover{/if}">
            <div id="sw_content_{$filter_uid}" class="ty-product-filters__switch">
                <span class="ty-product-filters__title">
                    {$filter.filter}
                </span>
                {if $filter.open || $filter.selected_ranges}<i class="ty-icon-ok"></i>{/if}
                {*<i class="ty-product-filters__switch-right ty-icon-left-open"></i>*}
                {include file="addons/development/common/tooltip.tpl" note_url=$filter.note_url note_text=$filter.note_text tooltipclass="ty-filter-tooltip"}
            </div>
            
            {if $filter.slider}
                {include file="blocks/product_filters/components/product_filter_slider.tpl" filter_uid=$filter_uid id="slider_`$filter_uid`" filter=$filter ajax_div_ids=$ajax_div_ids dynamic=true filter_qstring=$filter_qstring reset_qstring=$reset_qstring allow_ajax=$allow_ajax}
            {else}
                {include file="blocks/product_filters/components/product_filter_variants.tpl" filter_uid=$filter_uid filter=$filter ajax_div_ids=$ajax_div_ids collapse=$collapse filter_qstring=$filter_qstring reset_qstring=$reset_qstring allow_ajax=$allow_ajax}
            {/if}
        </div>
    {/if}
{/foreach}
<script type="text/javascript">

(function(_, $) {$ldelim}

    $(document).ready(function() {$ldelim}
        $('.cm-filter-item').click(function(e){$ldelim}
            if ($(this).data('targetUrl')) {$ldelim}
                $.ceAjax('request', fn_url($(this).data('targetUrl')), {$ldelim}
                    full_render: true,
                    force_exec: true,
                    save_history: true,
                    caching: true,
                    result_ids: '{$ajax_div_ids}',
                    scroll: '.cm-pagination-container',
                {$rdelim});
            {$rdelim}
        {$rdelim});
        $('.ty-product-filters__switch').click(function(e){$ldelim}
            var block = $(this).parent();
            block.toggleClass('is-hover');
            var block_id = block.find('.ty-product-filters__switch').prop('id').replace(/^(on_|off_|sw_)/, '');
            if (block.hasClass('is-hover')) {
                $.cookie.set(block_id, 1);
            } else {
                $.cookie.set(block_id, 0);
            }
            block.find('.ty-product-filters').toggle();
            block.find('.ty-price-slider').toggle();
        {$rdelim});
        $('.ty-product-filters__block').hover(function(e){$ldelim}
            $(this).addClass('is-hover');
            var block_id = $(this).find('.ty-product-filters__switch').prop('id').replace(/^(on_|off_|sw_)/, '');
            var submenu = $(this);
            setTimeout(function() {$ldelim}
                if (submenu.hasClass('is-hover')) {
                    $.cookie.set(block_id, 1);
                    submenu.find('.ty-product-filters').show();
                    submenu.find('.ty-price-slider').show();
                }
            {$rdelim}, 150);
        {$rdelim}, function(e){$ldelim}
            if ($(this).find(":focus").length == 0) {$ldelim}
                $(this).removeClass('is-hover');
                var block_id = $(this).find('.ty-product-filters__switch').prop('id').replace(/^(on_|off_|sw_)/, '');
                var submenu = $(this);
                setTimeout(function() {$ldelim}
                    if (!submenu.hasClass('is-hover')) {
                        $.cookie.remove(block_id);
                        submenu.find('.ty-product-filters').hide();
                        submenu.find('.ty-price-slider').hide();
                    }
                {$rdelim}, 150);
            {$rdelim}
        {$rdelim});
    {$rdelim});
    
{$rdelim}(Tygh, Tygh.$));
</script>

<div class="ty-product-filters__tools clearfix">
    {*<a {if "FILTER_CUSTOM_ADVANCED"|defined}href="{"products.search?advanced_filter=Y"|fn_url}"{else}href="{$filter_qstring|fn_link_attach:"advanced_filter=Y"|fn_url}"{/if} rel="nofollow" class="ty-product-filters__advanced-button">{__("advanced")}</a>*}
    {if $smarty.capture.has_selected}
    <a href="{if $request_data.category_id}{assign var="use_ajax" value=true}{"categories.view?category_id=`$request_data.category_id`"|fn_url}{else}{assign var="use_ajax" value=false}{""|fn_url}{/if}" rel="nofollow" class="ty-product-filters__reset-button{if $allow_ajax && $use_ajax} cm-ajax cm-ajax-force cm-ajax-full-render cm-history" data-ca-target-id="{$ajax_div_ids}{/if}"><i class="ty-product-filters__reset-icon ty-icon-cw"></i> {__("reset")}</a>
    {/if}
</div>

{/if}
</div>
<!--product_filters_{$block.block_id}--></div>