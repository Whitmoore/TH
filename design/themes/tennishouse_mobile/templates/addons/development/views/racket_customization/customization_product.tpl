<div class="ty-sd-product__item clearfix">
    <div class="cm-reload-{$obj_prefix}{$obj_id} ty-sd-product__image" id="product_configuration_image_{$obj_prefix}{$obj_id}{$product.group_id}">
        <input type="hidden" name="product_data[{$obj_id}][configuration][{$product.group_id}][product_ids][]" value="{$product.product_id}">
        <input type="hidden" name="product_data[{$obj_id}][configuration][{$product.group_id}][is_separate]" value="true">
        {include file="common/image.tpl" images=$product.main_pair no_ids=true}
    <!--product_configuration_image_{$obj_prefix}{$obj_id}{$product.group_id}--></div>
    <div class="ty-sd-product__content clearfix">
        <div class="ty-sd-product__content-name-wrapper">
            <div class="ty-sd-product__content-name">
                {$product.product nofilter}
            </div>
            {if !$hide_delete}
                <div class="ty-sd__product-delete ty-delete-big cm-sd-option" data-step-remove="{$product.group_id}" title="{__("remove")}">&nbsp;<i class="ty-delete-big__icon ty-icon-cancel"></i></div>
            {/if}
        </div>
        {if $product.product_options}
            <div class="cm-reload-{$obj_prefix}{$obj_id} ty-sd-group__products-item-options" id="product_configuration_options_update_{$obj_prefix}{$obj_id}{$product.group_id}">
                {include file="addons/product_configurator/views/products/components/configuration_product_options.tpl" product=$product id=$product.product_id product_options=$product.product_options name="product_data[`$obj_id`][configuration][`$product.group_id`][options]" request_obj_prefix=$obj_prefix request_obj_id=$obj_id}
            <!--product_configuration_options_update_{$obj_prefix}{$obj_id}{$product.group_id}--></div>
        {/if}
    </div>
    <div class="ty-sd-product__price">
        {if $product.discount}
            <div class="ty-sd-product__price-val">
                <span class="ty-sd-product__price-val-disc">{include file="common/price.tpl" value=$product.original_price span_id="price_`$key`" class="ty-price-num ty-line-through"}</span>
                {include file="common/price.tpl" value=$product.display_price span_id="price_`$key`" class="ty-price-num ty-sd-product__price-discount"}
            </div>
            <div>
                <div class="ty-sd-product__price-disc">
                    {__("discount")}
                </div>
                <div class="ty-sd-product__price-disc">
                    {include file="common/price.tpl" value=$product.discount span_id="discount_subtotal_`$key`" class=""}
                    {if $product.discount_prc|floatval}({$product.discount_prc}%){/if}
                </div>
            </div>
        {else}
            {include file="common/price.tpl" value=$product.price span_id="price_`$key`" class="ty-price-num"}
        {/if}
    </div>
</div>
