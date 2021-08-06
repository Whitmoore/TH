<div class="control-group">
    <label class="control-label" for="ship_dhl_length">{__("ship_multiship_length")}</label>
    <div class="controls">
        <input id="ship_dhl_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_width">{__("ship_multiship_width")}</label>
    <div class="controls">
        <input id="ship_dhl_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}"/>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_dhl_height">{__("ship_multiship_height")}</label>
    <div class="controls">
        <input id="ship_dhl_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}"/>
    </div>
</div>