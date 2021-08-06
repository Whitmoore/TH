{if $cart.display_subtotal >= $addons.development.free_shipping_cost / 2 && $cart.display_subtotal < $addons.development.free_shipping_cost}
    {$left_amount = $addons.development.free_shipping_cost - $cart.display_subtotal}
    {capture name="free_shipping_left_amount"}
        {include file="common/price.tpl" value=$left_amount}
    {/capture}
    <div style="font-style: italic;">{__("free_shipping_tip", ["[left_amount]" => $smarty.capture.free_shipping_left_amount])}</div>
{/if}