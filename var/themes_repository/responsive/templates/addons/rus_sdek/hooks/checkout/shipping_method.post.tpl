{if $cart.chosen_shipping.$group_key == $shipping.shipping_id && $shipping.module == 'sdek' && $display == "radio"}
    {include file="addons/rus_sdek/components/shipping_method.tpl"}
{/if}
