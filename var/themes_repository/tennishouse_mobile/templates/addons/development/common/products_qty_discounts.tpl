{if $product.prices.0.price < $product.price}
<div class="ty-ti-qty-discount">
    {if $product.category_type == 'R'}
        {$prod_name = __("rackets_alt")}
    {/if}
    <div class="ty-product-block__price-actual">
        <span class="ty-price" id="line_ti_discounted_price_{$obj_prefix}{$obj_id}">{include file="common/price.tpl" value=$product.prices.0.price span_id="ti_discounted_price_`$obj_prefix``$obj_id`" class="ty-price-num"}</span>
    </div>
    <div>{__("ti_discount_text", ["[items]" => $product.prices.0.lower_limit, "[product]" => $prod_name])}</div>
</div>
{/if}