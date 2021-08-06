<ul class="ty-cart-items__list">
    {hook name="index:cart_status"}
        {foreach from=$items key="key" item="p" name="cart_products"}
            {if !$p.extra.parent}
                <li class="ty-cart-items__list-item">
                    {if $block.properties.products_links_type == "thumb"}
                        <div class="ty-cart-items__list-item-image">
                            {include file="common/image.tpl" image_width="100" image_height="100" images=$p.main_pair no_ids=true}
                        </div>
                    {/if}
                    <div class="ty-cart-items__list-item-desc">
                        <a href="{"products.view?product_id=`$p.product_id`{if $p.ohash}&{$p.ohash}{/if}"|fn_url}">{$p.product_id|fn_get_product_name nofilter}</a>
                    </div>
                    <div class="ty-cart-items__list-item-subtotal">
                        <span>{$p.amount}</span><span>&nbsp;x&nbsp;</span>{include file="common/price.tpl" value=$p.display_price span_id="price_`$key`_`$block.snapping_id`" class="ty-cart-items__list-item-subtotal-price"}
                    </div>
                    {if $block.properties.display_delete_icons == "Y"}
                        <div class="ty-cart-items__list-item-tools-wrapper">
                        <div class="ty-cart-items__list-item-tools cm-cart-item-delete">
                            {if !$p.extra.exclude_from_calculate}
                                {include file="buttons/button.tpl" but_href="checkout.delete.from_status?cart_id=`$key`&redirect_url=`$r_url`" but_meta="cm-ajax cm-ajax-full-render" but_target_id="cart_status*" but_role="delete" but_name="delete_cart_item"}
                            {/if}
                        </div>
                        </div>
                    {/if}
                </li>
            {/if}
        {/foreach}
    {/hook}
</ul>
