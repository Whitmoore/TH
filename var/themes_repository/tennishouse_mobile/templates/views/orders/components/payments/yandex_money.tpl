{assign var="yandex_payments" value=$payment_info.processor_params.payments}

{if $yandex_payments|count > 1}
    <div class="ty-control-group">
        <label class="ty-control-group__title" for="yandex_payment">{__("select_yandex_payment")}</label>
        <select name="payment_info[yandex_payment_type]" id="yandex_payment">
            {if $payment_info.processor_params.payments.pc}
                <option value="pc"> {__('yandex_payment_yandex')}</option>
            {/if}
            {if $payment_info.processor_params.payments.ac}
                <option value="ac"> {__('yandex_payment_card')}</option>
            {/if}
            {if $payment_info.processor_params.payments.gp}
                <option value="gp"> {__('yandex_payment_terminal')}</option>
            {/if}
            {if $payment_info.processor_params.payments.mc}
                <option value="mc"> {__('yandex_payment_phone')}</option>
            {/if}
            {if $payment_info.processor_params.payments.nv}
                <option value="nv"> {__('yandex_payment_webmoney')}</option>
            {/if}
        </select>
    </div>
{elseif is_array($yandex_payments)}
    <input type="hidden" name="payment_info[yandex_payment_type]" value="{$yandex_payments|key}">
{else}
    <input type="hidden" name="payment_info[yandex_payment_type]" value="">
{/if}