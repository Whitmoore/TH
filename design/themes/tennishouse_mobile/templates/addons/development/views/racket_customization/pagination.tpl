{assign var="id" value=$id|default:"pagination_contents"}
{assign var="pagination" value=$search|fn_generate_pagination}

{if $smarty.capture.pagination_open != "Y"}
    <div class="ty-pagination-container cm-pagination-container" id="{$id}">
{/if}

    {if $settings.Appearance.top_pagination == "Y" && $smarty.capture.pagination_open != "Y" || $smarty.capture.pagination_open == "Y"}
        {assign var="c_url" value=$c_url|default:$config.current_url|fn_query_remove:"page"}

        {if ($ajax_pagination == 'Y' && !$config.tweaks.disable_dhtml) || $force_ajax}
            {assign var="ajax_class" value="cm-ajax"}
        {/if}

        {if $smarty.capture.pagination_open == "Y"}
            <div class="ty-pagination__bottom">
        {else}
            <div class="ty-pagination-sorting">
        {/if}
        {if $pagination.total_pages > 1}
        <div class="ty-pagination">
            <div class="ty-pagination__item ty-pagination__btn ty-pagination__prev {if $pagination.prev_page}cm-sd-option{else}ty-pagination__item-disabled{/if}" data-page="{$pagination.prev_page}"><i class="ty-pagination__text-arrow">&larr;</i>&nbsp;<span class="ty-pagination__text">{__("prev_page")}</span></div>
            {if $pagination.prev_range}<span class="ty-pagination-el">...</span>{/if}
            <div class="ty-pagination__items">
                {foreach from=$pagination.navi_pages item="pg"}
                    {if $pg != $pagination.current_page}
                        <div data-page="{$pg}" class="cm-history ty-pagination__item cm-sd-option">{$pg}</div>
                    {else}
                        <span class="ty-pagination__selected">{$pg}</span>
                    {/if}
                {/foreach}
            </div>
            {if $pagination.next_range}<span class="ty-pagination-el">...</span>{/if}

            <div class="ty-pagination__item ty-pagination__btn ty-pagination__next {if $pagination.next_page}cm-sd-option{else}ty-pagination__item-disabled{/if}" data-page="{$pagination.next_page}"><span class="ty-pagination__text">{__("next")}</span>&nbsp;<i class="ty-pagination__text-arrow">&rarr;</i></div>
        </div>
        {/if}
        {if $smarty.capture.pagination_open == "Y"}
            </div>
        {else}
            {if !$no_sorting}
                {include file="views/products/components/sorting.tpl"}
            {/if}
            </div>        
        {/if}
    {else}
        <div><div data-page="{$pg}" class="hidden"></div></div>
    {/if}

{if $smarty.capture.pagination_open == "Y"}
    <!--{$id}--></div>
    {capture name="pagination_open"}N{/capture}
{elseif $smarty.capture.pagination_open != "Y"}
    {capture name="pagination_open"}Y{/capture}
{/if}
