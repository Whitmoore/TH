{if $cart.products.$key.extra.configuration}
<p class="center">
    {include file="buttons/button.tpl" but_text=__("edit") but_href="racket_customization.view?cart_id=`$key`" but_role="text" but_meta="cm-ajax"}
</p>
{/if}