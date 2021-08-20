{assign var="callback_url" value="payment_notification.notify&payment=paykeeper"|fn_url:'C':'http'}

<div>{__("paykeeper_callback_notice")}:</div>
<div style="font-weight: bold; margin-bottom: 10px;">{$callback_url}</div>

<div class="control-group">
    <label class="control-label">{__("paykeeper_secret")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret]" value="{$processor_params.secret}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label">{__("paykeeper_url")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][url]" value="{$processor_params.url|default:'https://tennishouse.server.paykeeper.ru'}" >
    </div>
</div>