<div class="top-cart-content ty-top-block__cell cm-hover-dropdown" id="cart_status_{$block.snapping_id}">
    {assign var="r_url" value=$config.current_url|escape:url}
    {hook name="checkout:cart_content"}
    <div class="ty-top-block__cell-inner ty-dropdown-box__title ty-dropdown-box ty-top-block__cart-content {if $smarty.session.cart.amount}ty-top-block__cart-content-full{/if} cm-link {if $highlight}ty-top-block-change-state{/if}" data-href="{"checkout.cart"|fn_url}">
        <div class="ty-top-block__cell-content">
            {hook name="checkout:dropdown_title"}
                <div class="ty-minicart__icon">
                    {if $smarty.session.cart.amount}
                        <div class="ty-minicart__icon-counter">{$smarty.session.cart.amount}</div>
                    {/if}
                </div>
                {if $smarty.session.cart.amount}
                    <span class="ty-minicart-title ty-hand">
                        <div class="ty-minicart-title__total">
                            {__("total")}
                            <div>{include file="common/price.tpl" value=$smarty.session.cart.display_subtotal}</div>
                        </div>
                    </span>
                {else}
                    <span class="ty-minicart-title ty-hand">
                        <div class="ty-minicart-title__total empty-cart">
                            {__("cart_is_empty")}
                        </div>
                    </span>
                {/if}
            {/hook}
        </div>
    </div>
    {if $smarty.session.cart.amount}
        <div class="cm-popup-box ty-dropdown-box__content cm-hover-dropdown-submenu" {if $is_open}style="display: block;"{/if}>
            {hook name="checkout:minicart"}
                <div class="ty-dropdown-box__content-inner">
                    <div class="ty-dropdown-box__content-title">{__("cart")}</div>
                    <div class="ty-dropdown-box__content-items cm-cart-content {if $block.properties.products_links_type == "thumb"}cm-cart-content-thumb{/if} {if $block.properties.display_delete_icons == "Y"}cm-cart-content-delete{/if}">
                        <div class="ty-cart-items">
                            {if $smarty.session.cart.amount}
                                <ul class="ty-cart-items__list">
                                    {hook name="index:cart_status"}
                                        {assign var="_cart_products" value=$smarty.session.cart.products|array_reverse:true}
                                        {foreach from=$_cart_products key="key" item="p" name="cart_products"}
                                            {if !$p.extra.parent}
                                                <li class="ty-cart-items__list-item">
                                                    {if $block.properties.products_links_type == "thumb"}
                                                        <div class="ty-cart-items__list-item-image">
                                                            {include file="common/image.tpl" image_width="100" image_height="100" images=$p.main_pair no_ids=true}
                                                        </div>
                                                    {/if}
                                                    <div class="ty-cart-items__list-item-desc">
                                                        <div class="ty-cart-items__list-item-category">{$p.category_name}</div>
                                                        <a href="{"products.view?product_id=`$p.product_id`{if $p.ohash}&{$p.ohash}{/if}"|fn_url}">{$p.product nofilter}</a>
                                                    </div>
                                                    <div class="ty-cart-items__list-item-subtotal">
                                                        <span>{$p.amount}</span><span>&nbsp;x&nbsp;</span>{include file="common/price.tpl" value=$p.display_price span_id="price_`$key`_`$block.snapping_id`" class="ty-cart-items__list-item-subtotal-price"}
                                                    </div>
                                                    {if $block.properties.display_delete_icons == "Y"}
                                                        <div class="ty-cart-items__list-item-tools-wrapper">
                                                        <div class="ty-cart-items__list-item-tools cm-cart-item-delete">
                                                            {if !$p.extra.exclude_from_calculate}
                                                                {include file="buttons/button.tpl" but_href="checkout.delete.from_status?cid=`$key`&redirect_url=`$r_url`" but_meta="cm-ajax cm-ajax-full-render" but_target_id="cart_status*,add_to_cart_button*" but_role="delete" but_name="delete_cart_item"}
                                                            {/if}
                                                        </div>
                                                        </div>
                                                    {/if}
                                                </li>
                                            {/if}
                                        {/foreach}
                                    {/hook}
                                </ul>
                            {else}
                                <div class="ty-cart-items__empty ty-center">{__("cart_is_empty")}</div>
                            {/if}
                        </div>
                    </div>
                    {if $block.properties.display_bottom_buttons == "Y"}
                        <div class="ty-cart-content__buttons-wrapper">
                            <div class="ty-cart-content__buttons">
                                <div class="ty-cart-content__total">
                                    <div class="ty-cart-content__total-title">{__("total")}</div>
                                    <div class="ty-cart-content__total-amount">{include file="common/price.tpl" value=$smarty.session.cart.total class=""}</div>
                                </div>
                                <div class="cm-cart-buttons ty-cart-content__buttons-container{if $smarty.session.cart.amount} full-cart{else} hidden{/if}">
                                    <div class="ty-float-right">
                                        <a href="{"checkout.cart"|fn_url}" rel="nofollow" class="ty-btn ty-btn__primary">{__("go_to_cart")}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>
            {/hook}
        </div>
    {/if}
    {/hook}
<!--cart_status_{$block.snapping_id}--></div>