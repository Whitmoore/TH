{capture name="buttons"}
    <div class="ty-float-left">
        {include file="buttons/button.tpl" but_text=__("continue_shopping") but_meta="cm-notification-close"}
    </div>
    {if $settings.General.checkout_redirect != "Y"}
        <div class="ty-float-right">
            {include file="buttons/button.tpl" but_text=__("go_to_cart") but_href="checkout.cart" but_role="action" but_meta="ty-btn__primary"}
        </div>
    {/if}
{/capture}
{capture name="info"}
    <div class="clearfix"></div>
    <hr class="ty-product-notification__divider" />

    <div class="ty-product-notification__total-info clearfix">
        <div class="ty-product-notification__amount ty-float-left"> {__("items_in_cart", [$smarty.session.cart.amount])}</div>
        <div class="ty-product-notification__subtotal ty-float-right">
            {__("cart_subtotal")} {include file="common/price.tpl" value=$smarty.session.cart.display_subtotal class="ty-price-num"}
        </div>
    </div>
{/capture}
{capture name="cross"}
    {if $cross_sales}
    <div class="ty-cross-recomendation">{__("cross_recomendation_text")}</div>
    <div class="ty-cross-section ty-products-scroller">
        {math equation="80 / x" x=$cross_sales|count assign="cell_width"}
        {if $cross_sales|count == 1}
            {$item_count = 2}
        {else}
            {$item_count = 1}
        {/if}
        {foreach from=$cross_sales item="cross_category"}
            <div style="width: {$cell_width}%" class="ty-cross-section__item">
                <div class="ty-product-cross-sales">
                    {assign var="obj_prefix" value="`$cross_category.category_id`_`$smarty.const.TIME`"}
                    <div id="content_block_tab_{$cross_category.category_id}_{$smarty.const.TIME}" class="ty-wysiwyg-content">
                        <div id="scroll_list_{$cross_category.category_id}_{$smarty.const.TIME}" class="owl-carousel ty-scroller-list ty-micro-mode">
                            {$type_id = $smarty.const.TYPE_FEATURE_ID}
                            {foreach from=$cross_category.products item="product" name="for_products"}
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
                                mode="micro"
                                hide_links=true
                                skip_to_add_cart=true}
                            {/foreach}
                        </div>
                    </div>

                    {include file="addons/development/common/popup_scroller_init.tpl" items_count=$item_count id="`$cross_category.category_id`_`$smarty.const.TIME`"}
                    <div class="ty-product-cross-sales-title">{$cross_category.category}</div>
                    <div class="ty-product-cross-sales-title-tooltip">{if $cross_category.description}{include file="addons/development/common/tooltip.tpl" note_text=$cross_category.description tooltip_title=__("what_for") tooltipclass="ty-category-tooltip"}{/if}</div>
                </div>
            </div>
        {/foreach}
    </div>
    {/if}
{/capture}
{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons product_info=$smarty.capture.info product_cross=$smarty.capture.cross}