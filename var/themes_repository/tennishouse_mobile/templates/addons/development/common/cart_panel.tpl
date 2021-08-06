{assign var="r_url" value=$config.current_url|escape:url}
<div class="ty-dropdown-box" id="cart_status_panel">
    <div class="cm-cart-content {if $block.properties.products_links_type == "thumb"}cm-cart-content-thumb{/if} {if $block.properties.display_delete_icons == "Y"}cm-cart-content-delete{/if}">
        <div class="ty-cart-items">
            {if $smarty.session.cart.amount}
                <ul class="ty-cart-items__list">
                    {hook name="index:cart_status"}
                        {assign var="_cart_products" value=$smarty.session.cart.products|array_reverse:true}
                        {foreach from=$_cart_products key="key" item="p" name="cart_products"}
                            {if !$p.extra.parent}
                                <li class="ty-cart-items__list-item">
                                    <div class="ty-cart-items__list-item-image">
                                        {include file="common/image.tpl" image_width="40" image_height="40" images=$p.main_pair no_ids=true}
                                    </div>
                                    <div class="ty-cart-items__list-item-desc">
                                        <a href="{"products.view?product_id=`$p.product_id`{if $p.ohash}&{$p.ohash}{/if}"|fn_url}">{$p.product_id|fn_get_product_name nofilter}</a>
                                    <p>
                                        <span>{$p.amount}</span><span>&nbsp;x&nbsp;</span>{include file="common/price.tpl" value=$p.display_price span_id="price_`$key`_panel" class="ty-price-num"}
                                    </p>
                                    </div>
                                    <div class="ty-cart-items__list-item-tools cm-cart-item-delete">
                                        {if !$p.extra.exclude_from_calculate}
                                            {include file="buttons/button.tpl" but_href="checkout.delete.from_status?cart_id=`$key`&redirect_url=`$r_url`" but_meta="cm-ajax cm-ajax-full-render" but_target_id="cart_status*,cart_status_top,add_to_cart_button*" but_role="delete" but_name="delete_cart_item"}
                                        {/if}
                                    </div>
                                </li>
                            {/if}
                        {/foreach}
                    {/hook}
                </ul>
            {else}
                <div class="ty-cart-items__empty ty-center">{__("cart_is_empty")}</div>
            {/if}
        </div>

        <div class="cm-cart-buttons ty-cart-content__buttons buttons-container{if $smarty.session.cart.amount} full-cart{else} hidden{/if}">
            <div class="ty-cart-checkout-button">
                <a href="{"checkout.cart"|fn_url}" rel="nofollow" class="ty-btn ty-btn__primary">{__("view_cart")}</a>
            </div>
        </div>
</div>
<!--cart_status_panel--></div>