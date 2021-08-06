{if $image_title}
<div class="ty-image-title cm-parallax" style="background: url({$image_title.detailed.image_path}) center 109px no-repeat fixed;" data-speed="1.5">
    {if $image_title_text}
    <h1 class="ty-image-title__name">
        {$image_title_text}
    </h1>
    {/if}
</div>
{/if}