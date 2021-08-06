<div class="control-group">
    <label class="control-label" for="login">{__("login")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][login]" id="login" value="{$processor_params.login|default:'TENNISHOUSE-api'}" class="input-text-large"  size="60" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="password">{__("password")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][password]" id="password" value="{$processor_params.password|default:'zwwvmbLGA2'}" class="input-text-large"  size="60" />
    </div>
</div>