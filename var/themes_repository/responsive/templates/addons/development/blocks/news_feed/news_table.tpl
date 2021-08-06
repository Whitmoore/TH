{if $items}
    <div class="ty-rss-news-table">
        {split data=$items size=$block.properties.number_of_columns|default:"3" assign="splitted_news"}
        {$columns_num = $block.properties.number_of_columns|default:"3"}
        {$news_width = (100 - 0.5 * ($columns_num - 1)) / $columns_num}
        {foreach from=$splitted_news item="snews" name="snws"}
            <div class="ty-rss-news-table__row">
            {foreach from=$snews item="nws" name="sn"}
                <div class="ty-rss-news-table__block" style="width: {$news_width}%;"><a href="{$nws.link}" target="_blank">
                    <picture>
                        <source srcset="{$nws.image}">
                        <img src="{$nws.image}" alt="{$nws.title}">
                    </picture>
                    <div class="ty-rss-news-table__overlay"></div>
                    <div class="ty-rss-news-table__block-item">
                        <div class="ty-rss-news-table__block-item-wrapper">
                            <div class="ty-rss-news-table__date">{if $nws.today}{__("today")}{elseif $nws.yesterday}{__("yesterday")}{else}{$nws.timestamp|date_format:"%e %B"}{/if}, {$nws.timestamp|date_format:"%H:%M"}</div>
                            <div class="ty-rss-news-table__title">{$nws.title}</div>
                            {*<div class="ty-rss-news__descr">{$nws.description}</div>*}
                        </div>
                    </div>
                </a></div>
            {/foreach}
            </div>
        {/foreach}
    </div>
{/if}