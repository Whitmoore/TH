{strip}

{if $capture_image}
    {capture name="image"}
{/if}

{if !$obj_id}
    {math equation="rand()" assign="obj_id"}
{/if}

{$image_retina_width = $image_width * 3}
{$image_retina_height = $image_height * 3}
{$image_data=$images|fn_image_to_display:$image_retina_width:$image_retina_height:$keep_transparent}
{$generate_image=$image_data.generate_image && !$external}
{if !$image_width}
    {$image_width = $image_data.width * $image_height / $image_data.height}
{/if}
{if !$image_height}
    {$image_height = $image_data.height * $image_width / $image_data.width}
{/if}
{if $image_width && $image_width != $image_data.width && $image_height && $image_height != $image_data.height}
    <div class="ty-image-wrapper" style="width: {$image_width}px; height: {$image_height}px;">
{/if}

{if $show_detailed_link}
    <a id="det_img_link_{$obj_id}" {if $image_data.detailed_image_path && $image_id}data-ca-image-id="{$image_id}"{/if} class="{$link_class} {if $image_data.detailed_image_path}cm-previewer ty-previewer{/if}" {if $image_data.detailed_image_path}href="{$image_data.detailed_image_path}" title="{$images.detailed.alt}"{/if}>
{/if}
{if $image_data.image_path}
    <img {if $itemprop}itemprop="{$itemprop}"{/if} class="ty-pict {$valign} {$class} {if $generate_image}ty-spinner{/if}"  {if $obj_id && !$no_ids}id="det_img_{$obj_id}"{/if} {if $generate_image}data-ca-image-path="{$image_data.image_path}"{/if} src="{if $generate_image}{$images_dir}/icons/spacer.gif{else}{$image_data.image_path}{/if}" alt="{$image_data.alt}" title="{$image_data.alt}" {if $image_onclick}onclick="{$image_onclick}"{/if} />
{else}
    <span class="ty-no-image" style="min-width: {$image_width|default:$image_height}px; min-height: {$image_height|default:$image_width}px;"><i class="ty-no-image__icon ty-icon-image" title="{__("no_image")}"></i></span>
{/if}
{if $show_detailed_link}
    {if $images.detailed_id}
        <span class="ty-previewer__icon hidden-phone"></span>
    {/if}
</a>
{/if}

{if $capture_image}
    {/capture}
    {capture name="icon_image_path"}
        {$image_data.image_path}
    {/capture}
    {capture name="detailed_image_path"}
        {$image_data.detailed_image_path}
    {/capture}
{/if}
{if $image_width && $image_width != $image_data.width && $image_height && $image_height != $image_data.height}
    </div>
{/if}

{/strip}
