{** block-description:description **}

<div class="ty-title-heading ty-product-tabs__description-title">{$product.product}</div>
{if $product.full_description}
    <div itemprop="description" {live_edit name="product:full_description:{$product.product_id}"} class="ty-product-description" {if $product.prices}style="margin-bottom: 30px;"{/if}>
        {$product.full_description nofilter}
        {if $product.category_type == 'R'}
            <div class="ty-product-description-info">{__("rackets_description_note", ["[learning_center_link]" => "{"pages.view?page_id=55"|fn_url}"])}</div>
        {elseif $product.category_type == 'ST'}
            <div class="ty-product-description-info">{__("strings_description_note", ["[learning_center_link]" => "{"pages.view?page_id=65"|fn_url}"])}</div>
        {/if}
    </div>
{elseif $product.short_description}
    <div itemprop="description" {live_edit name="product:short_description:{$product.product_id}"} class="ty-product-description" {if $product.prices}style="margin-bottom: 30px;"{/if}>
        {$product.short_description nofilter}
        {if $product.category_type == 'R'}
            <div class="ty-product-description-info">{__("rackets_description_note", ["[learning_center_link]" => "{"pages.view?page_id=55"|fn_url}"])}</div>
        {elseif $product.category_type == 'ST'}
            <div class="ty-product-description-info">{__("strings_description_note", ["[learning_center_link]" => "{"pages.view?page_id=65"|fn_url}"])}</div>
        {/if}
    </div>
{/if}

{if $product.product_type != 'C' && !$product.prices && !($product.full_description || $product.short_description) && $product.product_features}
    <div itemprop="description" class="ty-product-features" {if $product.prices}style="margin-bottom: 30px;"{/if}>
        {include file="views/products/components/product_features.tpl" product_features=$product.product_features details_page=true feature_path="categories.view?category_id=`$product.main_category`"}
    </div>
{/if}
{if $product.prices}
    {include file="views/products/components/products_qty_discounts.tpl"}
{/if}