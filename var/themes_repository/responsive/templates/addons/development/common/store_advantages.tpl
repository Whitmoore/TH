{*script src="js/lib/owlcarousel/owl.carousel.min.js"*}
<div id="scroll_list_advantages">
    {if $product.category_type == 'A' || $product.category_type == 'S'}
        {capture append="store_advantages"}
        <div class="ty-product-tabs-advantages-item">
            <div class="ty-advantage-fitting-icon"></div>
            <div class="ty-product-tabs-advantages-item-title">{__("advantage_fitting_title")}</div>
            <div class="ty-product-tabs-advantages-item-text">{__("advantage_fitting_text")}</div>
        </div>
        {/capture}
    {/if}
    {capture append="store_advantages"}
    <a href="{"pages.view?page_id=`$smarty.const.SHIPPING_PAGE_ID`"|fn_url}">
    <div class="ty-product-tabs-advantages-item">
        <div class="ty-advantage-air-delivery-icon"></div>
        <div class="ty-product-tabs-advantages-item-title">{__("advantage_air_delivery_title")}</div>
        <div class="ty-product-tabs-advantages-item-text">{__("advantage_air_delivery_text")}</div>
    </div>
    </a>
    {/capture}
    {capture append="store_advantages"}
    <div class="ty-product-tabs-advantages-item">
        <div class="ty-advantage-payment-on-delivery-icon"></div>
        <div class="ty-product-tabs-advantages-item-title">{__("advantage_payment_on_delivery_title")}</div>
        <div class="ty-product-tabs-advantages-item-text">{__("advantage_payment_on_delivery_text")}</div>
    </div>
    {/capture}
    {if $product.category_type != 'A' && $product.category_type != 'S'}
        {capture append="store_advantages"}
        <div class="ty-product-tabs-advantages-item">
            <div class="ty-advantage-refunds-icon"></div>
            <div class="ty-product-tabs-advantages-item-title">{__("advantage_refund_title")}</div>
            <div class="ty-product-tabs-advantages-item-text">{__("advantage_refund_text")}</div>
        </div>
        {/capture}
    {/if}
    {$blocks_number = 1}
    {if $product.product_features|count > 3}
        {math equation = "floor(x / 4)" x=$product.product_features|count assign="blocks_number"}
    {/if}
    {foreach $store_advantages as $advantage}{if $advantage@iteration > $blocks_number}{break}{/if}{$advantage nofilter}{/foreach}
</div>
{*<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        $('#scroll_list_advantages').children().each(function(){
            $(this).owlCarousel({
                rewind: true,
                items: 1,
                autoplay: true,
                autoplayTimeout: 10000,
    //             autoplaySpeed: 10000,
                autoplayHoverPause: true,
                margin: 12,
                animateOut: 'fadeOut',
                animateIn: 'fadeIn',
            });
        });
    });
}(Tygh, Tygh.$));
</script>*}
