{if $content|trim}
{if !$active_tab}
    {assign var="active_tab" value=$smarty.request.selected_section}
{/if}

{if $navigation.tabs}

{assign var="empty_tab_ids" value=$content|empty_tabs}
{assign var="_tabs" value=false}
{if $top_order_actions}{$top_order_actions nofilter}{/if}
{script src="js/tygh/tabs.js"}
<div class="ty-tabs cm-j-tabs{if $track} cm-track{/if} clearfix">
    <ul class="ty-tabs__list" {if $tabs_section}id="tabs_{$tabs_section}"{/if}>
    {if $active_tabs|count < 4}
        {$tab_width = '400px'}
    {else}
        {$wdth = 100 / $active_tabs|count}
        {$tab_width = "`$wdth`%"}
    {/if}
    {foreach from=$navigation.tabs item=tab key=key name=tabs}
        {if ((!$tabs_section && !$tab.section) || ($tabs_section == $tab.section)) && !$key|in_array:$empty_tab_ids && $tab.display}
            {if !$active_tab && ($key != 'description' || ($key == 'description' && (($product.full_description || $product.short_description) || ($product.product_features && (!$product.prices || !($product.full_description || $product.short_description))))))}
                {assign var="active_tab" value=$key}
            {/if}
            {assign var="_tabs" value=true}
            <li id="{$key}" class="ty-tabs__item{if $tab.js} cm-js{elseif $tab.ajax} cm-js cm-ajax{/if}{if $key == $active_tab} active{/if}" style="width: {$tab_width}"><h2><a class="ty-tabs__a" {if $tab.href} href="{$tab.href|fn_url}"{/if}>{$tab.title}</a></h2></li>
        {/if}
    {/foreach}
    </ul>
</div>

{if $_tabs}
<div class="ty-tabs__wrapper">
    <div class="cm-tabs-content ty-tabs__content clearfix" id="tabs_content">
        {$content nofilter}
    </div>
</div>
<script type="text/javascript">
(function(_, $) {
    $(document).ready(function(){
        if ($('#tabs_content').length) {
            var mx_hght = 0;
            $('#tabs_content').children().each(function(){
                if ($(this).attr('id') != 'content_technologies' && mx_hght < $(this).outerHeight(true)) {
                    mx_hght = $(this).outerHeight(true);
                }
            });
//             $('#tabs_content').css("max-height", mx_hght + 30 + 'px');
            $('#tabs_content').children().each(function(){
                if ($(this).attr('id') != 'content_technologies') {
                    $(this).css("height", mx_hght + 'px');
                }
            });
        }
    });
}(Tygh, Tygh.$));
</script>
{/if}
<div class="ty-product-tabs-advantages" id="advantages_tab">
    {include file="addons/development/common/store_advantages.tpl"}
</div>

{if $onclick}
<script type="text/javascript">
    var hndl = {$ldelim}
        'tabs_{$tabs_section}': {$onclick}
    {$rdelim}
</script>
{/if}
{else}
    {$content nofilter}
{/if}
{/if}