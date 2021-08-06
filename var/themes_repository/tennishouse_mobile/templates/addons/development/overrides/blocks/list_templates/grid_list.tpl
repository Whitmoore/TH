{if $products}

    {script src="js/tygh/exceptions.js"}
    
    {if !$no_pagination}
        {include file="common/pagination.tpl" no_sorting=true}
    {/if}
    
    {if $sections_categorization && $sc_feature}
        {foreach from=$sc_feature item="f_category"}
            {$vt_id = $f_category.variant_id}
            {if $sections_categorization.$vt_id}
                {$products = $sections_categorization.$vt_id}
                <div class="ty-categorize">
                    <h4 class="ty-categorize__title">{*$f_category.description*} {$f_category.variant}</h4>
                    <div class="ty-categorize__description">{$f_category.variant_description}</div>
                </div>
                <div class="ty-categorize__section">
                    {include file="addons/development/common/grid_list_section.tpl" products=$products}
                </div>
            {/if}
        {/foreach}
        {if $sections_categorization.other}
            {$products = $sections_categorization.other}
            <div class="ty-categorize">
                <div class="ty-categorize__title">{__("other")}</div>
            </div>
            <div class="ty-categorize__section">
                {include file="addons/development/common/grid_list_section.tpl" products=$products}
            </div>
        {/if}
    {else}
        {include file="addons/development/common/grid_list_section.tpl" products=$products}
    {/if}
    
    {if !$no_pagination}
        {include file="common/pagination.tpl" no_sorting=true}
    {/if}
{/if}

{capture name="mainbox_title"}{$title}{/capture}