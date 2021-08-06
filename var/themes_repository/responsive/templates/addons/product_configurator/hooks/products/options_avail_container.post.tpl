{if $product.product_configurator_groups}
    {if $product.product_options}<div class="ty-pc-container-separator"></div>{/if}
    <div class="ty-pc-container">
        {include file="addons/product_configurator/views/products/components/product_configuration.tpl"}
    </div>
{/if}