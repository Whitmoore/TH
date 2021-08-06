{** block-description:features **}

{if $product.product_type != 'C' && $product.product_features && ($product.prices || ($product.full_description || $product.short_description))}
    <div class="ty-product-features">
        {include file="views/products/components/product_features.tpl" product_features=$product.product_features details_page=true feature_path="categories.view?category_id=`$product.main_category`"}
    </div>
{/if}