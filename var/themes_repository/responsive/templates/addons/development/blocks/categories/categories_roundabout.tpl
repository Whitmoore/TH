{** block-description:tmpl_roundabout **}

{$imageSize = "300"}
<div class="ty-roundabout-wrapper cm-roundabout">
    <div id="roundabout_images_{$block.block_id}" class="ty-rounabout-list cm-roundabout-images">
        {foreach from=$items item="category" name="categories_images"}
            <div id="roundabout_category_{$category.category_id}" class="ty-roundabout-item" style="width: {$imageSize}px;height: {$imageSize}px;" onclick="if ($(this).hasClass('roundabout-in-focus')) {ldelim}$('#roundabout_category_link_{$category.category_id}').click();{rdelim}">
                {include file="common/image.tpl"
                show_detailed_link=false
                images=$category.main_pair
                no_ids=true
                image_width=$imageSize
                keep_transparent=true}
                <div class="ty-roundabout__brand-image" {if !$smarty.foreach.categories_images.first}style="display: none;"{/if}>
                    {if $category.brand_id == $smarty.const.KIRSCHBAUM_BRAND_ID}
                        {$img_height = "50"}
                    {else}
                        {$img_height = "35"}
                    {/if}
                    {include file="addons/development/common/brand_logo.tpl"  brand=$category.brand brand_variant_id=$category.brand_id img_height=$img_height}
                </div>
            </div>
        {/foreach}
    </div>
    <div id="roundabout_description_{$block.block_id}" class="ty-rounabout-description cm-roundabout-descriptions">
        {foreach from=$items item="category" name="roundabout_descr"}
            <div class="ty-roundabout-item-description" {if !$smarty.foreach.roundabout_descr.first}style="display: none;"{/if}>
                <h3>{$category.category}</h3>
                <div class="ty-wysiwyg-content ty-mb-s">{$category.description nofilter}</div>
                <a id="roundabout_category_link_{$category.category_id}" href="{"categories.view?category_id=`$category.category_id`"|fn_url}">
                    <div class="ty-roundabout-view-collection">{__("view_collection")}</div>
                </a>
            </div>
        {/foreach}
    </div>
</div>