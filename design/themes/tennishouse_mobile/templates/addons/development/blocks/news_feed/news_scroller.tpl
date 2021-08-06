{if $items}
    <div class="ty-tennishouse-container ty-news-feed">
        <div class="ty-tennishouse-body">
            <div id="scroll_list_news_{$block.block_id}" class="owl-carousel ty-scroller-list">
                {foreach from=$items item="news" name="for_news"}
                    <div class="ty-scroller-news-list__item">
                        <span class="ty-news-date">{$news.timestamp|date_format:"%e %B %Y %A, %H:%M"}</span>
                                                
                        <div class="ty-news-item-title">
                            <a target="_blank" href="{$news.link}">
                                <div class="ty-news-item-column-descritpion">
                                    <div class="ty-news-item-row-title">{$news.title}</div>
                                    <div class="ty-news-item-row-description">{$news.description}</div>
                                </div>
                            </a>
                        </div>
                    </div>
                {/foreach}
            </div>

            {script src="js/lib/owlcarousel/owl.carousel.min.js"}
            <script type="text/javascript">
            (function(_, $) {
                $.ceEvent('on', 'ce.commoninit', function(context) {
                    var elm = context.find('#scroll_list_news_{$block.block_id}');

                    if (elm.length) {
                        elm.owlCarousel({
                            items: 1,
                            autoplay: true,
                            rewind: true,
                            autoplayTimeout: '15000',
                            autoplaySpeed: '400',
                            autoplayHoverPause: true,
                            dots: false,
                            nav: true,
                            animateOut: 'fadeOut',
                            animateIn: 'fadeIn',
                            navText: ['<div class="ty-arrow-bg"></div>', '<div class="ty-arrow-bg"></div>'],
                        });
                    }
                });
            }(Tygh, Tygh.$));
            </script>
        </div>
    </div>
{/if}