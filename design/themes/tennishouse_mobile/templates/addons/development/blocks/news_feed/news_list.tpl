{if $items}
    <div class="ty-rss-news" data-mcs-theme="dark" id="news_feed_{$block.block_id}">
        {foreach from=$items item="nws"}
            <div class="ty-rss-news__block">
                <div class="ty-rss-news__image"><img src="{$nws.image}" alt="{$nws.title}" width="100px"/></div>
                <div class="ty-rss-news__text-block">
                    <div class="ty-rss-news__title"><a href="{$nws.link}" target="_blank">{$nws.title}</a></div>
                    <div class="ty-rss-news__descr">{$nws.description}</div>
                </div>
                <div class="ty-rss-news__date">{if $nws.today}{__("today")}{elseif $nws.yesterday}{__("yesterday")}{else}{$nws.timestamp|date_format:"%e %B"}{/if}, {$nws.timestamp|date_format:"%H:%M"}</div>
            </div>
        {/foreach}
    </div>
{/if}