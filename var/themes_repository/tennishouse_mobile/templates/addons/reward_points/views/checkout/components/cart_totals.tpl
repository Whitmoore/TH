{if $cart.points_info.reward}
<li>
    <span>{__("points")}:</span>
    <strong>{include file="common/price.tpl" value=$cart.points_info.reward}</strong>
</li>
{/if}