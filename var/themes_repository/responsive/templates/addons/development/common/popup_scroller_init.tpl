{script src="js/lib/owlcarousel/owl.carousel.min.js"}
<script type="text/javascript">
(function(_, $) {
    if ($('#scroll_list_{$id}').length) {
        $('#scroll_list_{$id}').owlCarousel({
            items: {$items_count|default:1},
            autoplay: false,
            autoplaySpeed: 400,
            autoplayHoverPause: true,
//             navigation: true,
//             navigationText: ['', ''],
            dots: false,
//             margin: 10
        });
    }
}(Tygh, Tygh.$));
</script>