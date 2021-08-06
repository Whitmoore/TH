<div class="ty-promotion_list">
{foreach from=$promotions key="promotion_id" item="promotion"}
    <a href="{"promotions.view?promotion_id=`$promotion_id`"|fn_url}" class="ty-promotion_list_item">
    {hook name="promotions:list_item"}
        <div class="ty-promotion_list_item-image">
            {include file="common/image.tpl" obj_id=$obj_id_prefix images=$promotion.main_pair}
        </div>
        <div class="ty-promotion_list_item-descr">
            <h2 class="ty-promotion_list_item-descr-header">{$promotion.name}</h2>
            <div class="ty-promotion_list_item-description">{$promotion.short_description nofilter}</div>
        </div>
    {/hook}
    </a>
{foreachelse}
    <p>{__("text_no_active_promotions")}</p>
{/foreach}
</div>

{capture name="mainbox_title"}{__("active_promotions")}{/capture}