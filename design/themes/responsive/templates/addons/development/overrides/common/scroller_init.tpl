{script src="js/lib/owlcarousel/owl.carousel.min.js"}
{$items_count = $items_count|default:$block.properties.item_quantity}
<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        if ($('#scroll_list_{$block.block_id}{$suf}').length) {
            $('#scroll_list_{$block.block_id}{$suf}').owlCarousel({
                items: {$items_count|default:1},
                margin: 0,
                rewind: true,
                {if $block.properties.scroll_per_page == "Y"}
                slideBy: 'page',
                {/if}
                {if $block.properties.not_scroll_automatically != "Y"}
                    autoplay: true,
                    autoplayTimeout: '{$block.properties.pause_delay * 1000|default:0}',
                {/if}
                autoplaySpeed: {$block.properties.speed|default:400},
                autoplayHoverPause: true,
                dots: false
//                 nav: true,
//                 navText: ['', ''],
            });
        }
    });
}(Tygh, Tygh.$));
</script>