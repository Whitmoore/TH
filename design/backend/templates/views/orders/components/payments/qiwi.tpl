{* rus_build_pack dbazhenov *}
{script src="js/lib/inputmask/jquery.inputmask.min.js"}
{script src="js/addons/rus_build_pack/jquery.inputmask-multi.js"}
{script src="js/addons/rus_build_pack/input_mask.js"}

<div class="control-group">
    <label for="qiwi_phone_number" class="control-label cm-required">{__("phone")}</label>
    <div class="controls">
        <input id="qiwi_phone_number" size="35" type="text" name="payment_info[phone]" value="{$cart.user_data.b_phone}" class="input-big cm-mask" />
    </div>
</div>


