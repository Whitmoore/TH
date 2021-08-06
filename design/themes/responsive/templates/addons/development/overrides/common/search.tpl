<div class="ty-top-block__cell-content">
    <form action="{""|fn_url}" name="search_form" method="get" class="">
        <input type="hidden" name="result_ids" value="top_search" />
        <input type="hidden" name="match" value="all" />
        <input type="hidden" name="subcats" value="Y" />
        <input type="hidden" name="pname" value="Y" />
        <input type="hidden" name="pfull" value="Y" />
        <input type="hidden" name="search_performed" value="Y" />
        <div class="ty-search-block">
            {hook name="search:additional_fields"}{/hook}

            {strip}
                {if $settings.General.search_objects}
                    {assign var="search_title" value=__("search")}
                {else}
                    {assign var="search_title" value=__("search_products")}
                {/if}
                <input type="text" name="q" value="{$search.q}" id="search_input{if $smarty.capture.search_input_id}_{$smarty.capture.search_input_id}{/if}" title="{$search_title}" class="ty-search-block__input cm-hint cm-ajax-search" autocomplete="off" />
                {if $settings.General.search_objects}
                    {include file="buttons/magnifier.tpl" but_name="search.results" alt=__("search")}
                {else}
                    {include file="buttons/magnifier.tpl" but_name="products.search" alt=__("search")}
                {/if}
            {/strip}

            {capture name="search_input_id"}
                {math equation="x + y" x=$smarty.capture.search_input_id|default:1 y=1 assign="search_input_id"}
                {$search_input_id}
            {/capture}
        </div>
        <div class="ty-search-results" id="top_search">
            {if $results}
                {foreach from=$results item="product"}
                    <a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&`$product.ohash`{/if}"|fn_url}"><div class="ty-search-results-item">
                        {include file="common/image.tpl" show_detailed_link=false images=$product.main_pair.detailed no_ids=true image_height="40"}
                        <span class="ty-search-results-item-title">{$product.product nofilter}</span>
                    </div></a>
                {/foreach}
                {if $results_count > $addons.development.ajax_search_results_number}
                    {include file="buttons/button.tpl" but_name="dispatch[products.search]" but_meta="ty-ajax-search-view-all" but_text=__("view_all_result", ["[results_count]" => $results_count])}
                {/if}
            {/if}
        <!--top_search--></div>
    </form>
</div>