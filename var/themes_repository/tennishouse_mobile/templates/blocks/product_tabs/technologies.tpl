{** block-description:technologies **}

{if $product.technologies}
    <div class="ty-technologies">
        {foreach from=$product.technologies item="tec"}
            <div class="ty-technologies__item">
                <div class="ty-technologies__item-logo">
                    {include file="common/image.tpl" obj_id=$tec.technology_id images=$tec.main_pair image_width="100" image_height="100" keep_transparent=true show_detailed_link=false}
                </div>
                <div class="ty-technologies__item-description">
                    {$tec.description nofilter}
                </div>
            </div>
        {/foreach}
    </div>
{/if}
