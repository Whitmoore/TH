{if $product.extra.configuration}
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
        <div class="ty-pc-product-notification">
            {foreach from=$product.configuration item="_product" key="_key"}
                <div class="ty-product-notification__item clearfix">
                    <div class="ty-product-notification__image">
                        {if $_key == 'CONSULT_STRINGING'}
                            <div class="ty-image-wrapper"><div class="ty-product-notification__image-consult-stringing"></div></div>
                        {else}
                            {include file="common/image.tpl" images=$_product.main_pair no_ids=true image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
                        {/if}
                    </div>
                    <div class="ty-product-notification__content clearfix">
                        <div class="ty-product-notification__content-name">
                            {if $_product.product_id}<a href="{"products.view?product_id=`$_product.product_id`"|fn_url}" class="ty-product-notification__product-name">{/if}{if $_product.product}{$_product.product}{else}{$_product.product_id|fn_get_product_name nofilter}{/if}{if $_product.product_id}</a>{/if}
                            {if $_product.product_option_data}
                                {include file="common/options_info.tpl" product_options=$_product.product_option_data}
                            {/if}
                        </div>
                        {if !($settings.General.allow_anonymous_shopping == "hide_price_and_add_to_cart" && !$auth.user_id)}
                            {if !$hide_amount}
                                <div class="ty-product-notification__content-amount">
                                    {$_product.amount}
                                </div>
                                <div class="ty-product-notification__price">
                                    {if $_product.discount}
                                        <div class="ty-product-notification__price-val">
                                            <span class="ty-product-notification__price-val-disc">{include file="common/price.tpl" value=$_product.original_price span_id="price_`$key`" class="ty-price-num ty-line-through"}</span>
                                            {include file="common/price.tpl" value=$_product.display_price span_id="price_`$key`" class="ty-price-num ty-cart-content__price-discount"}
                                        </div>
                                        <div>
                                            <div class="ty-product-notification__price-disc">
                                                {__("discount")}
                                            </div>
                                            <div class="ty-product-notification__price-disc">
                                                {include file="common/price.tpl" value=$_product.discount span_id="discount_subtotal_`$key`" class=""}
                                                {if $_product.discount_prc|floatval}({$_product.discount_prc}%){/if}
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
            {/foreach}
        </div>
    </div>
</div>
{elseif $product.extra.parent}
&nbsp;
{/if}