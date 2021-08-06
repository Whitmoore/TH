    {$iw = $settings.Thumbnails.product_lists_thumbnail_width}
    {$ih = $settings.Thumbnails.product_lists_thumbnail_height}
{capture name="main_icon"}
    {if !$hide_links}<a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&`$product.ohash`{/if}"|fn_url}">{/if}
        {include file="addons/development/common/load_image.tpl" el_id="product_main_image_`$obj_id`_`$product.main_pair.pair_id`" pair_id=$product.main_pair.pair_id pair_data=$product.main_pair}
    {if !$hide_links}</a>{/if}
{/capture}

{if $product.image_pairs && $show_gallery}
    <div class="ty-center-block">
        <div class="ty-thumbs-wrapper owl-carousel cm-image-gallery" data-ca-items-count="1" data-ca-items-id="{$obj_id}" data-ca-items-responsive="true" data-ca-items-hide-navigation-text="true" {if $gallery_buttons}data-ca-items-gallery-buttons="{$gallery_buttons}"{/if} id="icons_{$obj_id}">
            {if $product.main_pair}
                <div class="cm-gallery-item cm-item-gallery">
                    {$smarty.capture.main_icon nofilter}
                </div>
            {/if}
            {foreach from=$product.image_pairs item="image_pair"}
                {if $image_pair}
                    <div class="cm-gallery-item cm-item-gallery" style="position: relative;">
                        {if !$hide_links}<a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&`$product.ohash`{/if}"|fn_url}">{/if}
                           {include file="addons/development/common/load_image.tpl" el_id="product_image_`$obj_id`_`$image_pair.pair_id`" pair_id=$image_pair.pair_id pair_data=$image_pair trigger_ids="prev_`$obj_id`,next_`$obj_id`" loader_class="cm-carousel-image-loader"}
                        {if !$hide_links}</a>{/if}
                    </div>
                {/if}
            {/foreach}
        </div>
    </div>
{else}
    {$smarty.capture.main_icon nofilter}
{/if}