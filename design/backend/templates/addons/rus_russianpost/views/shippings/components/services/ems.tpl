{* rus_build_pack dbazhenov *}
<fieldset>

<div class="control-group">
    <label class="control-label" for="ship_ems_mode">{__("ems_mode")}</label>
    <div class="controls">
    <select id="ship_ems_mode" name="shipping_data[service_params][mode]">
        <option value="regions" {if $shipping.service_params.mode == "regions"}selected="selected"{/if}>{__("regions")}</option>
        <option value="cities" {if $shipping.service_params.mode == "cities"}selected="selected"{/if}>{__("cities")}</option>
    </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_ems_delivery_time_plus">{__("ems_delivery_time_plus")}</label>
    <div class="controls">
        <input id="ship_ems_delivery_time_plus" type="text" name="shipping_data[service_params][delivery_time_plus]" size="30" value="{$shipping.service_params.delivery_time_plus}" />
    </div>
</div>

</fieldset>