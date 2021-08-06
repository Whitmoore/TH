{if $product.amount > 0 || ($product.product_type == 'C' && $product.hide_stock_info) || $product.tracking == 'D'}
    {include file="addons/development/blocks/product_templates/tennishouse_template_in_stock.tpl"}
{else}
    {include file="addons/development/blocks/product_templates/tennishouse_template_out_of_stock.tpl"}
{/if}