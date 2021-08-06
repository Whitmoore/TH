{script src="js/lib/owlcarousel/owl.carousel.min.js"}
<script type="text/javascript">
(function(_, $) {
    $.ceEvent('on', 'ce.commoninit', function(context) {
        $('.cm-banner-carousel').each(function(){
            if ($(this).length) {
                $(this).addClass('owl-theme');
                $(this).owlCarousel({
                    items: 1,
                    autoplay: true,
                    rewind: true,
                    autoplaySpeed: $(this).data('slideSpeed'),
                    autoplayTimeout: $(this).data('autoPlay'),
                    autoplayHoverPause: true,
                    dots: ($(this).data('navigation') == 'P' || $(this).data('navigation') == 'D') ? true : false,
                    animateOut: 'fadeOut',
                    animateIn: 'fadeIn',
//                     paginationNumbers: ($(this).data('navigation') == 'P') ? true : false,
//                     navigation: ($(this).data('navigation') == 'A') ? true : false,
//                     navigationText: ($(this).data('navigation') == 'A') ? ['{__("prev_page")}', '{__("next")}'] : false
                });
            }
        });
    });
}(Tygh, Tygh.$));
</script>