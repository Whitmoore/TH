{* rus_build_pack *}

<fieldset>

    <div class="control-group">
        <label class="control-label" for="user_key">{__("authentication_key")}</label>
        <div class="controls">
            <input id="user_key" type="text" name="shipping_data[service_params][user_key]" size="30" value="{$shipping.service_params.user_key}"/>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="user_key_password">{__("authentication_password")}</label>
        <div class="controls">
            <input id="user_key_password" type="password" name="shipping_data[service_params][user_key_password]" size="30" value="{$shipping.service_params.user_key_password}" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="package_type">{__("russianpost_shipping_type")}</label>
        <div class="controls">
            <select id="package_type" name="shipping_data[service_params][shipping_type]">
                <option value="rp_main" {if $shipping.service_params.shipping_type == "rp_main"}selected="selected"{/if}>{__("ship_russianpost_shipping_type_rp_main")}</option>
                <option value="rp_1class" {if $shipping.service_params.shipping_type == "rp_1class"}selected="selected"{/if}>{__("ship_russianpost_shipping_type_rp_1class")}</option>
            </select>
        </div>
    </div>

    <span>{__("ship_russianpost_register_text")}</span>

</fieldset>
