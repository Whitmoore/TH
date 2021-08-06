{assign var="product_amount" value=$product.inventory_amount|default:$product.amount}
{if $show_add_to_cart && $details_page && !($settings.General.inventory_tracking == "Y" && $settings.General.allow_negative_amount != "Y" && (($product_amount <= 0 || $product_amount < $product.min_qty) && $product.tracking != "ProductTracking::DO_NOT_TRACK"|enum))}
    {if !$hide_wishlist_button}
        {include file="addons/wishlist/views/wishlist/components/add_to_wishlist.tpl" product_id=$product.product_id likes=$product.likes}
    {/if}
{/if}