<div id="pc_info_{$group_id}_{$product.product_id}">
{if $show_info}
    <a class="ty-pc-group__products-item-link" href="{"products.view?product_id=`$product.product_id`"|fn_url}" target="_blank">
    <div class="ty-product-info">
        <div class="ty-product-info__image">
            <div class="product-image border-image-wrap">
                {include file="views/products/components/product_icon.tpl" product=$product show_gallery=false hide_links=true}
            </div>
            <div class="ty-grid-list__brand-image">
                {include file="addons/development/common/brand_logo.tpl"  brand=$product.brand brand_variant_id=$product.brand.variant_id}
            </div>
        </div>
        <div class="ty-product-info__block">
            <div class="ty-product-info__block-title">{$product.product}</div>
            {if $product.subtitle}
            <div class="ty-product-info__block-subtitle">
                {$product.subtitle}
            </div>
            {/if}
            <div class="ty-product-block__price-actual">
                {if $product.price|floatval || $product.zero_price_action == "P" || ($hide_add_to_cart_button == "Y" && $product.zero_price_action == "A")}
                    <span class="ty-price{if !$product.price|floatval && !$product.zero_price_action} hidden{/if}" id="line_discounted_price_{$obj_prefix}{$obj_id}">{include file="common/price.tpl" value=$product.price span_id="discounted_price_`$obj_prefix``$obj_id`" class="ty-price-num" live_editor_name="product:price:{$product.product_id}"}</span>
                {/if}
            </div>
            {if $product.full_description || $product.short_description}
            <div class="ty-product-info__block-description">
                {$product.full_description|default:$product.short_description|strip_tags:false|truncate:300 nofilter}
            </div>
            {/if}
        </div>
    </div>
{else}
    <div class="ty-pc-ajax-loading-box"></div>
{/if}
<!--pc_info_{$group_id}_{$product.product_id}--></div>