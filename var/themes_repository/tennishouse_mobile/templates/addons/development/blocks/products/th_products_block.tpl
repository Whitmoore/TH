{** block-description:tmpl_th_products_block **}

{if $block.properties.is_capture == 'Y'}
    {capture name="block_tab_`$block.block_id`"}
{/if}

{assign var="obj_prefix" value="`$block.block_id`000"}
<div id="content_block_tab_{$block.block_id}" class="ty-wysiwyg-content ty-no-swipe">
    {if $block.properties.format == 'S'}
        <div id="scroll_list_{$block.block_id}" class="owl-carousel ty-scroller-list {if $block.properties.mode == 'S'}ty-small-mode{elseif $block.properties.mode == 'N'}ty-mini-mode{elseif $block.properties.mode == 'M'}ty-micro-mode{/if}">
            {$type_id = $smarty.const.TYPE_FEATURE_ID}
            {foreach from=$items item="product" name="for_products"}
                {include file="addons/development/common/products_list_item.tpl"
                show_trunc_name=true
                show_old_price=true
                show_price=true
                show_clean_price=true
                show_list_discount=true
                show_add_to_cart=false
                hide_call_request=true
                but_role="action"
                show_discount_label=true
                mode=$block.properties.mode
                hide_links=true
                hide_form=true}
            {/foreach}
        </div>
    {elseif $block.properties.format == 'G'}
        {include file="blocks/list_templates/grid_list.tpl"
        products=$items
        columns=$block.properties.columns_number|default:"5"
        form_prefix="block_manager"
        no_sorting="Y"
        no_pagination="Y"
        no_ids="Y"
        obj_prefix=$block.content.items.filling
        show_trunc_name=true
        show_old_price=true
        show_price=true
        show_rating=true
        show_clean_price=true
        show_list_discount=true
        show_add_to_cart=$show_add_to_cart|default:false
        but_role="action"
        show_discount_label=true
        hide_links=true
        hide_form=true}
    {/if}
    {*if $block.properties.all_items_url}
        <div class="ty-check-all__block-link">
            {include file="addons/development/common/form_link.tpl" form_method="post" hidden_input=["redirect_url" => "{$block.properties.all_items_url}"] link_text=__("check_all_items")|upper link_meta="ty-button-link ty-view-all-link" link_name="dispatch[development.redirect]" link_role=""}
        </div>
    {/if*}
</div>

{include file="common/scroller_init.tpl"}
{if $block.properties.is_capture == 'Y'}
    {/capture}
{/if}
