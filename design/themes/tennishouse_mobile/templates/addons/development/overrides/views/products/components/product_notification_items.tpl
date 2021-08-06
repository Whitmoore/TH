{hook name="products:notification_items"}
    {if $added_products}
        {foreach from=$added_products item=product key="key"}
            {hook name="products:notification_product"}
            <div class="ty-product-notification__item clearfix">
                <div class="ty-product-notification__image">
                    {include file="common/image.tpl" images=$product.main_pair no_ids=true image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                </div>
                <div class="ty-product-notification__content clearfix">
                    <div class="ty-product-notification__content-name">
                        <a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&{$product.ohash}{/if}"|fn_url}" class="ty-product-notification__product-name">{$product.product_id|fn_get_product_name nofilter}</a>
                        {if $product.product_option_data}
                            {include file="common/options_info.tpl" product_options=$product.product_option_data}
                        {/if}
                    </div>
                    {if !($settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart" && !$auth.user_id)}
                        {if !$hide_amount}
                            <div class="ty-product-notification__content-amount">
                                {$product.amount}
                            </div>
                            <div class="ty-product-notification__price">
                                {if $product.discount}
                                    <div class="ty-product-notification__price-val">
                                        <span class="ty-product-notification__price-val-disc">{include file="common/price.tpl" value=$product.original_price span_id="price_`$key`" class="ty-price-num ty-line-through"}</span>
                                        {include file="common/price.tpl" value=$product.display_price span_id="price_`$key`" class="ty-price-num ty-cart-content__price-discount"}
                                    </div>
                                    <div>
                                        <div class="ty-product-notification__price-disc">
                                            {__("discount")}
                                        </div>
                                        <div class="ty-product-notification__price-disc">
                                            {include file="common/price.tpl" value=$product.discount span_id="discount_subtotal_`$key`" class=""}
                                            {if $product.discount_prc|floatval}({$product.discount_prc}%){/if}
                                        </div>
                                    </div>
                                {else}
                                    {include file="common/price.tpl" value=$product.display_price span_id="price_`$key`" class="ty-price-num"}
                                {/if}
                            </div>
                        {/if}
                    {/if}
                </div>
            </div>
            {/hook}
        {/foreach}
    {else}
    {$empty_text}
    {/if}
{/hook}
