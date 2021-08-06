{if !$show_empty}
    {split data=$products size=$columns|default:"2" assign="splitted_products"}
{else}
    {split data=$products size=$columns|default:"2" assign="splitted_products" skip_complete=true}
{/if}

{math equation="100 / x" x=$columns|default:"2" assign="cell_width"}
{if $item_number == "Y"}
    {assign var="cur_number" value=1}
{/if}

{if $settings.Appearance.enable_quick_view == 'Y'}
    {$quick_nav_ids = $products|fn_fields_from_multi_level:"product_id":"product_id"}
{/if}
<div class="grid-list {if $extended}ty-grid-list__item-wrapper-extended{elseif $block.properties.mode == 'S'}ty-small-mode{elseif $block.properties.mode == 'N'}ty-mini-mode{elseif $block.properties.mode == 'M'}ty-micro-mode{/if} {if $category_grid}ty-grid-list__item-wrapper-regular{/if}">
    {strip}
        {$type_id = $smarty.const.TYPE_FEATURE_ID}
        {foreach from=$splitted_products item="sproducts" name="sprod"}
            {foreach from=$sproducts item="product" name="sproducts"}
                <div class="ty-column{$columns}">
                    {if $product}
                        {include file="addons/development/common/products_list_item.tpl" mode=$block.properties.mode|default:"R" hide_form=true hide_links=true trunc_number=75}
                    {/if}
                </div>
            {/foreach}
            {if $show_empty && $smarty.foreach.sprod.last}
                {assign var="iteration" value=$smarty.foreach.sproducts.iteration}
                {capture name="iteration"}{$iteration}{/capture}
                {hook name="products:products_multicolumns_extra"}
                {/hook}
                {assign var="iteration" value=$smarty.capture.iteration}
                {if $iteration % $columns != 0}
                    {math assign="empty_count" equation="c - it%c" it=$iteration c=$columns}
                    {section loop=$empty_count name="empty_rows"}
                        <div class="ty-column{$columns}">
                            <div class="ty-product-empty">
                                <span class="ty-product-empty__text">{__("empty")}</span>
                            </div>
                        </div>
                    {/section}
                {/if}
            {/if}
        {/foreach}
    {/strip}
    {*if $block.properties.all_items_url}
        <div class="ty-check-all__block-link">
            {include file="addons/development/common/form_link.tpl" form_method="post" hidden_input=["redirect_url" => "{$block.properties.all_items_url}"] link_text=__("check_all_items")|upper link_meta="ty-button-link ty-view-all-link" link_name="dispatch[development.redirect]" link_role=""}
        </div>
    {/if*}
</div>
