{assign var="redirect_url" value="payment_notification.notify?payment=avangard"|fn_url:'C':'https'}
<p>
    {__("text_avangard_redirect_url", ["[redirect_url]" => $redirect_url])}
</p>
<hr>

<div class="control-group">
    <label class="control-label" for="shop_id">{__("avangard_shop_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][shop_id]" id="shop_id" value="{$processor_params.shop_id}"  size="60">
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="password">{__("avangard_password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="av_sign">{__("addons.rus_payments.avangard_av_sign")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][av_sign]" id="av_sign" value="{$processor_params.av_sign}"  size="60">
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="logging">{__("addons.rus_payments.logging")}:</label>
    <div class="controls">
        <input type="checkbox" name="payment_data[processor_params][logging]" id="logging" value="Y" {if $processor_params.logging == 'Y'} checked="checked"{/if}/>
    </div>
</div>