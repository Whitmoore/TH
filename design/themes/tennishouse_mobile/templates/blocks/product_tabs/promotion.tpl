{** block-description:promotion **}

{if $product.available_promotions}
    {foreach from=$product.available_promotions item="promo"}
        <div>
            <div class="ty-promotion-image-title">{include file="common/image.tpl" obj_id=$obj_id_prefix images=$promo.main_pair}</div>
            <div class="ty-wysiwyg-content">
                <div class="ty-page-description">
                    {$promo.detailed_description nofilter}
                </div>
            </div>
        </div>
        <a href="{"promotions.view?promotion_id=`$promo.promotion_id`"|fn_url}" class="ty-promo-descr-link">{__("read_about_promo")}</a>
    {/foreach}
{/if}