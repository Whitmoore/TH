<div itemprop="offers" itemscope="" itemtype="http://schema.org/Offer">
    {$product_amount = $product.inventory_amount|default:$product.amount}
    {if ($product_amount <= 0 || $product_amount < $product.min_qty) && $settings.General.inventory_tracking == "Y"}
    <link itemprop="availability" href="http://schema.org/OutOfStock" />
    {else}
    <link itemprop="availability" href="http://schema.org/InStock" />
    {/if}
    <meta itemprop="priceCurrency" content="{$currencies[$smarty.const.CART_PRIMARY_CURRENCY].currency_code}" />
    <meta itemprop="price" content="{$product.price|fn_format_price:$primary_currency}" />
</div>

{hook name="products:seo_snippet_attributes"}
{/hook}

{* moved from addons/seo/addons/discussion/hooks/products/seo_snippet_attributes.pre.tpl*}
{if $product.discussion.search.total_items && $product.discussion.average_rating}
<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating"> 
    <meta itemprop="reviewCount" content="{$product.discussion.search.total_items}">
    <meta itemprop="ratingValue" content="{$product.discussion.average_rating}">
</div>
{/if}