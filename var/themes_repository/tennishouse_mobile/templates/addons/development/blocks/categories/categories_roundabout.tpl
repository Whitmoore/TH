{** block-description:tmpl_roundabout **}

{$imageSize = "300"}
{script src="js/addons/development/jquery.roundabout.min.js"}
{script src="js/addons/development/jquery.roundabout-shapes.min.js"}
<div class="ty-roundabout-wrapper">
    <div id="roundabout_images_{$block.block_id}" class="ty-rounabout-list">
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
    <div id="roundabout_description_{$block.block_id}" class="ty-rounabout-description">
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
<script type="text/javascript">
var block_id = '{$block.block_id}';
var startWidth = '{$imageSize}';
{literal}
(function(_, $) {

    $(document).ready(function() {
        var roundabout = $('#roundabout_images_' + block_id);
        var roundabout_description = $('#roundabout_description_' + block_id);
        roundabout.roundabout({
            childSelector: "div",
            duration: 1000,
            autoplay: true,
            autoplayDuration: 5000,
            autoplayPauseOnHover: true,
        });
        roundabout.bind( 'animationStart', function() {
            roundabout_description.find('.ty-roundabout-item-description').fadeOut('fast');
            roundabout.find('.ty-roundabout__brand-image').fadeOut('fast');
        });
        roundabout.bind( 'animationEnd', function() {
            roundabout_description.find($('.ty-roundabout-item-description')[roundabout.roundabout('getChildInFocus')]).stop().fadeIn('fast');
            roundabout.find($('.ty-roundabout__brand-image')[roundabout.roundabout('getChildInFocus')]).stop().fadeIn('fast');
        });
        roundabout_description.hover(function(e){
            roundabout.roundabout("stopAutoplay");
        }, function(e){
            roundabout.roundabout("startAutoplay");
        });
    });
    
}(Tygh, Tygh.$));
{/literal}
</script>