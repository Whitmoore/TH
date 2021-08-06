{** block-description:tmpl_scroller **}

{if $block.properties.enable_quick_view == "Y"}
    {$quick_nav_ids = $items|fn_fields_from_multi_level:"product_id":"product_id"}
{/if}

{if $block.properties.hide_add_to_cart_button == "Y"}
        {assign var="_show_add_to_cart" value=false}
    {else}
        {assign var="_show_add_to_cart" value=true}
    {/if}
    {if $block.properties.show_price == "Y"}
        {assign var="_hide_price" value=false}
    {else}
        {assign var="_hide_price" value=true}
{/if}
{assign var="obj_prefix" value="`$block.block_id`000"}
<div id="content_block_tab_{$block.block_id}" class="ty-wysiwyg-content ty-no-swipe">
    <div id="scroll_list_{$block.block_id}" class="owl-carousel ty-scroller-list {if $block.properties.mode == 'S'}ty-small-mode{elseif $block.properties.mode == 'N'}ty-mini-mode{elseif $block.properties.mode == 'M'}ty-micro-mode{/if}">
        {$type_id = $smarty.const.TYPE_FEATURE_ID}
        {foreach from=$items item="product" name="for_products"}
            {include file="addons/development/common/products_list_item.tpl"
            show_trunc_name=true
            show_old_price=true
            show_price=true
            show_clean_price=true
            show_list_discount=true
            show_add_to_cart=$_show_add_to_cart|default:false
            hide_call_request=true
            but_role="action"
            show_discount_label=true
            mode=$block.properties.mode
            hide_form=true
            hide_links=true}
        {/foreach}
    </div>
    {*if $block.properties.all_items_url}
        <div class="ty-check-all__block-link">
            {include file="addons/development/common/form_link.tpl" form_method="post" hidden_input=["redirect_url" => "{$block.properties.all_items_url}"] link_text=__("check_all_items")|upper link_meta="ty-button-link ty-view-all-link" link_name="dispatch[development.redirect]" link_role=""}
        </div>
    {/if*}
</div>

{include file="common/scroller_init.tpl"}