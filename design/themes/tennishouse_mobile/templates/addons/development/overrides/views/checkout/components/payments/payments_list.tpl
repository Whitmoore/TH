<div class="ty-other-pay clearfix">
    <div class="ty-payments-list">
        {hook name="checkout:payment_method"}
            {foreach from=$payments item="payment"}

                {if $payment_id == $payment.payment_id}
                    {$instructions = $payment.instructions}
                {/if}

                <div class="ty-payments-list__item">
                    <div class="ty-payments-list__item-group" id="payment_group_{$payment.payment_id}">
                        <input id="payment_{$payment.payment_id}" class="cm-select-payment" type="radio" name="payment_id" value="{$payment.payment_id}" {if $payment_id == $payment.payment_id}checked="checked"{/if} />
                        <div class="ty-payments-list__item-title-block">
                            <label for="payment_{$payment.payment_id}" class="ty-payments-list__item-title">
                                {if $payment.image}
                                    {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width="100" image_height="35" keep_transparent=true}
                                {/if}
                                <div>{$payment.payment}</div>
                            </label>
                        </div>
                    </div>
                    <div class="payment_carrier_link">
                        {*if $payment.website}
                            <a target="_blank" href="{$payment.website}">{$payment.website}</a>
                        {/if*}
                        {if $payment.description}<div class="ty-payments-list__item-description">{$payment.description}</div>{/if}
                    </div>
                    <script type="text/javascript" class="cm-ajax-force">
                    (function(_, $) {
                        $('#payment_group_{$payment.payment_id}').click(function(){$ldelim}
                            if (!$('#payment_{$payment.payment_id}').is(':checked')) {
                                $('#payment_{$payment.payment_id}').click();
                            }
                        {$rdelim});
                    }(Tygh, Tygh.$));
                    </script>
                </div>

                {if $payment_id == $payment.payment_id}
                    {if $payment.template && $payment.template != "cc_outside.tpl"}
                        <div>
                            {include file=$payment.template}
                        </div>
                    {/if}
                {/if}

            {/foreach}
        {/hook}
    </div>
    <div class="ty-payments-list__instruction">
        {$instructions nofilter}
    </div>
</div>
