<fieldset>

<div class="control-group">
    <label class="control-label" for="token">{__("shippings.dostavista.token")}</label>
    <div class="controls">
        <input id="token" class="input-slarge" type="text" name="shipping_data[service_params][token]" size="30" value="{$shipping.service_params.token|default:'0978F6B0364F3A8B476A86D5E78C149AE71B3EB5'}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="vehicle_type">{__("shippings.dostavista.vehicle_type")}</label>
    <div class="controls">
        <select id="vehicle_type" name="shipping_data[service_params][vehicle_type]">
            <option value="1" {if $shipping.service_params.vehicle_type == "1"}selected="selected"{/if}>{__("shippings.dostavista.vehicle_type_1")}</option>
            <option value="2" {if $shipping.service_params.vehicle_type == "2"}selected="selected"{/if}>{__("shippings.dostavista.vehicle_type_2")}</option>
            <option value="3" {if $shipping.service_params.vehicle_type == "3"}selected="selected"{/if}>{__("shippings.dostavista.vehicle_type_3")}</option>
            <option value="4" {if $shipping.service_params.vehicle_type == "4"}selected="selected"{/if}>{__("shippings.dostavista.vehicle_type_4")}</option>
            <option value="5" {if $shipping.service_params.vehicle_type == "5"}selected="selected"{/if}>{__("shippings.dostavista.vehicle_type_5")}</option>
            <option value="6" {if $shipping.service_params.vehicle_type == "6"}selected="selected"{/if}>{__("shippings.dostavista.vehicle_type_6")}</option>
            <option value="7" {if $shipping.service_params.vehicle_type == "7"}selected="selected"{/if}>{__("shippings.dostavista.vehicle_type_7")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="token">{__("shippings.dostavista.default_matter")}</label>
    <div class="controls">
        <input id="token" class="input-slarge" type="text" name="shipping_data[service_params][default_matter]" size="30" value="{$shipping.service_params.default_matter|default:'Спорттовары'}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="transfer_fee_included">{__("transfer_fee_included")}:</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="shipping_data[service_params][transfer_fee_included]" value="N" />
            <input type="checkbox" name="shipping_data[service_params][transfer_fee_included]" id="transfer_fee_included" {if $shipping.service_params.transfer_fee_included == 'Y'}checked="checked"{/if} value="Y" />
        </label>
    </div>
</div>

</fieldset>
