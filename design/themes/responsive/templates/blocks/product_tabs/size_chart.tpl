{** block-description:size_chart **}

{if $product.size_chart}
    <div {*class="mCustomScrollbar" data-mcs-axis="x" data-mcs-theme="dark"*} style="overflow-x: auto;">
    {$product.size_chart nofilter}
    </div>
{/if}