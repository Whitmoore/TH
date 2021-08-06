<input type="hidden" name="appearance[dont_show_points]" value="{$dont_show_points}" />
{if $show_price_values && !$dont_show_points && ($product.amount > 0 || ($product.product_type == 'C' && $product.hide_stock_info) || $product.tracking == 'D')}
    {include file="addons/reward_points/views/products/components/product_representation.tpl"}
{/if}