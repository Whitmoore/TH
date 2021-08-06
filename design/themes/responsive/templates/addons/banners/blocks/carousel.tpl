{** block-description:carousel **}

{if $items}
    <div id="banner_slider_{$block.snapping_id}" class="banners owl-carousel cm-banner-carousel" data-slide-speed="{$block.properties.speed|default:400}" data-auto-play="{$block.properties.delay * 1000|default:false}" data-navigation="{$block.properties.navigation}">
        {foreach from=$items item="banner" key="key"}
            <div class="ty-banner__image-item">
                {if $banner.type == "G" && $banner.main_pair.image_id}
                    {if $banner.url != ""}<a class="banner__link" href="{$banner.url|fn_url}" {if $banner.target == "B"}target="_blank"{/if}>{/if}
                        {include file="common/image.tpl" images=$banner.main_pair class="ty-banner__image"}
                    {if $banner.url != ""}</a>{/if}
                {else}
                    <div class="ty-wysiwyg-content">
                        {$banner.description nofilter}
                    </div>
                {/if}
            </div>
        {/foreach}
    </div>
{/if}