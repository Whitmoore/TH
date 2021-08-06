{hook name="buttons:add_to_cart"}
    <div style="display: inline-block;" id="add_to_cart_button_{$product.product_hash}">
    {assign var="c_url" value=$config.current_url|escape:url}
    {if $settings.General.allow_anonymous_shopping == "allow_shopping" || $auth.user_id}
        {if $product.in_cart}
            {include file="buttons/button.tpl" but_id=$but_id but_text=__("in_cart") but_href="checkout.cart" but_role="text" but_name="" but_meta="ty-btn__primary ty-btn__big ty-btn__add-to-cart ty-btn__add-to-cart-in-cart" but_icon="ty-in-cart-icon"}
            {include file="buttons/button.tpl" but_id="`$but_id`_hidden" but_name=$but_name but_role="big" but_meta="hidden"}
        {else}
            {include file="buttons/button.tpl" but_id=$but_id but_text=$but_text|default:__("add_to_cart") but_name=$but_name but_onclick=$but_onclick but_href=$but_href but_target=$but_target but_role=$but_role|default:"text" but_meta="ty-btn__primary ty-btn__big ty-btn__add-to-cart cm-form-dialog-closer"}
        {/if}
    {else}

        {if $runtime.controller == "auth" && $runtime.mode == "login_form"}
            {assign var="login_url" value=$config.current_url}
        {else}
            {assign var="login_url" value="auth.login_form?return_url=`$c_url`"}
        {/if}

        {include file="buttons/button.tpl" but_id=$but_id but_text=__("sign_in_to_buy") but_href=$login_url but_role=$but_role|default:"text" but_name=""}
        <p>{__("text_login_to_add_to_cart")}</p>
    {/if}
    <!--add_to_cart_button_{$product.product_hash}--></div>
{/hook}
