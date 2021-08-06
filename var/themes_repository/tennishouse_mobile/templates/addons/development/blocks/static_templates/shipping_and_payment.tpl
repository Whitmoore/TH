<div class="bottom-shipping-payment-text">
    <div class="span bottom-ps-text">
        <p class="ty-footer-menu__header">
            <span>{__("shipping")}</span>
        </p>
        {__('shipping_carriers_bottom_text')}:
        {$shippings = ""|fn_get_online_shipping_methods}
        <div class="bottom-ps-icons">
        {split data=$shippings size="2" assign="splitted_shippings" skip_complete=true}
        {foreach from=$splitted_shippings item="sshippings"}
            <div class="bottom-ps-icons-row">
            {foreach from=$sshippings item="shipping" key="shipping_id"}
                {*if $shipping.website}<a href="{$shipping.website}" target="_blank">{/if*}{include file="common/image.tpl" obj_id=$shipping.shipping_id images=$shipping.image image_width="100" image_height="30" keep_transparent=true}{*if $shipping.website}</a>{/if*}
            {/foreach}
            </div>
        {/foreach}
        </div>
    </div>
    <div class="span bottom-ps-text">
        <p class="ty-footer-menu__header">
            <span>{__("payment")}</span>
        </p>
        {__('payment_methods_bottom_text')}:
        {$payments = ""|fn_get_online_payment_methods}
        <div class="bottom-ps-icons">
        {split data=$payments size="2" assign="splitted_payments" skip_complete=true}
        {foreach from=$splitted_payments item="spayments"}
            <div class="bottom-ps-icons-row">
            {foreach from=$spayments item="payment" key="payment_id"}
                {*if $payment.website}<a href="{$payment.website}" target="_blank">{/if*}{include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width="100" image_height="30" keep_transparent=true}{*if $payment.website}</a>{/if*}
            {/foreach}
            </div>
        {/foreach}
        </div>
    </div>
</div>