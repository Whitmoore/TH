{script src="js/lib/inputmask/jquery.inputmask.min.js"}
{script src="js/addons/rus_payments/jquery.inputmask-multi.js"}
{script src="js/addons/rus_payments/input_mask.js"}

<div class="ty-qiwi">
    <div class="ty-qiwi__control-group ty-control-group">
        <label for="qiwi_phone_number" class="ty-control-group__title cm-required">{__("phone")}</label>
        <input id="qiwi_phone_number" size="35" type="text" name="payment_info[phone]" value="{$phone_normalize}" class="ty-input-big cm-mask" />
    </div>
</div>
