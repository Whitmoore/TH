<div class="ty-orders-promotion">
{include file="common/subheader.tpl" title=__("promotions")}

{foreach from=$promotions item="promotion" name="pfe" key="promotion_id"}
<div class="ty-orders-promotion__block">
    <div class="ty-orders-promotion__title">{$promotion.name}</div>

    {foreach from=$order_info.promotions.$promotion_id.bonuses item="bonus"}
    {if $bonus.bonus == "give_coupon"}
    <div class="ty-control-group">
        <label class="ty-orders-promotion__coupon-title">{__("coupon_code")}:</label>
        {$bonus.coupon_code}
    </div>
    {/if}
    {/foreach}

    {*if $promotion.short_description|trim}
        <div class="ty-orders-promotion__description">{$promotion.short_description nofilter}</div>
    {/if*}
</div>
{/foreach}
</div>