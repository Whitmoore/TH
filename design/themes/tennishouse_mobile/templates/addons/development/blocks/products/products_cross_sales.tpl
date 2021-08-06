{** block-description:tmpl_products_cross_sales **}

{if ($items|count == 1 || !$items[1]['items']) && $items[0]['items']}
    <div class="ty-product-cross-sales">
        {assign var="obj_prefix" value="`$block.block_id`000"}
        <div id="content_block_tab_{$block.block_id}" class="ty-wysiwyg-content ty-no-swipe">
            <div id="scroll_list_{$block.block_id}" class="owl-carousel ty-scroller-list ty-micro-mode">
                {$type_id = $smarty.const.TYPE_FEATURE_ID}
                {foreach from=$items[0]['items'] item="product" name="for_products"}
                    {include file="addons/development/common/products_list_item.tpl"
                    show_trunc_name=true
                    show_old_price=true
                    show_price=true
                    show_clean_price=true
                    show_list_discount=true
                    show_add_to_cart=true
                    show_product_options=false
                    hide_call_request=true
                    but_role="action"
                    show_discount_label=true
                    mode=$block.properties.mode
                    hide_links=true
                    hide_form=true
                    skip_to_add_cart=true}
                {/foreach}
            </div>
        </div>

        {include file="common/scroller_init.tpl" items_count="2"}
        <div class="ty-product-cross-sales-title">{$items[0]['title']}</div>
        <a class="ty-product-cross-sales-all" href="{"categories.view?category_id=`$smarty.const.STRINGS_CATEGORY_ID`&features_hash=V`$smarty.const.TW_M_STRINGS_FV_ID`"|fn_url}" rel="nofollow">{__("view_all")}</a>
    </div>
{elseif $items|count == 2 && $items[0]['items'] && $items[1]['items']}
    <div class="ty-product-cross-sales-left">
        {assign var="obj_prefix" value="`$block.block_id`000"}
        <div id="content_block_tab_{$block.block_id}" class="ty-wysiwyg-content ty-no-swipe">
            <div id="scroll_list_{$block.block_id}_1" class="owl-carousel ty-scroller-list ty-micro-mode">
                {$type_id = $smarty.const.TYPE_FEATURE_ID}
                {foreach from=$items[0]['items'] item="product" name="for_products"}
                    {include file="addons/development/common/products_list_item.tpl"
                    show_trunc_name=true
                    show_old_price=true
                    show_price=true
                    show_clean_price=true
                    show_list_discount=true
                    show_add_to_cart=true
                    show_product_options=false
                    hide_call_request=true
                    but_role="action"
                    show_discount_label=true
                    mode=$block.properties.mode
                    hide_links=true
                    hide_form=true
                    skip_to_add_cart=true}
                {/foreach}
            </div>
        </div>
        {include file="common/scroller_init.tpl" items_count="1" suf="_1"}
        <div class="ty-product-cross-sales-title">{$items[0]['title']}</div>
        <a class="ty-product-cross-sales-all" href="{"categories.view?category_id=`$smarty.const.STRINGS_CATEGORY_ID`&features_hash=V`$smarty.const.TW_M_STRINGS_FV_ID`"|fn_url}" rel="nofollow">{__("view_all")}</a>
    </div>
    <div class="ty-product-cross-sales-right">
        {assign var="obj_prefix" value="`$block.block_id`000"}
        <div id="content_block_tab_{$block.block_id}" class="ty-wysiwyg-content">
            <div id="scroll_list_{$block.block_id}_2" class="owl-carousel ty-scroller-list ty-micro-mode">
                {$type_id = $smarty.const.TYPE_FEATURE_ID}
                {foreach from=$items[1]['items'] item="product" name="for_products"}
                    {include file="addons/development/common/products_list_item.tpl"
                    show_trunc_name=true
                    show_old_price=true
                    show_price=true
                    show_clean_price=true
                    show_list_discount=true
                    show_add_to_cart=true
                    show_product_options=false
                    hide_call_request=true
                    but_role="action"
                    show_discount_label=true
                    mode=$block.properties.mode
                    hide_links=true
                    hide_form=true
                    skip_to_add_cart=true}
                {/foreach}
            </div>
        </div>

        {include file="common/scroller_init.tpl" items_count="1" suf="_2"}
        <div class="ty-product-cross-sales-title">{$items[1]['title']}</div>
        <a class="ty-product-cross-sales-all" href="{"categories.view?category_id=`$smarty.const.OVERGRIPS_CATEGORY_ID`"|fn_url}" rel="nofollow">{__("view_all")}</a>
    </div>
{/if}