{script src="js/addons/rus_sdek/func.js"}
<fieldset>

<div class="control-group">
    <label class="control-label" for="authlogin">{__("shippings_sdek.authlogin")}</label>
    <div class="controls">
        <input id="authlogin" type="text" name="shipping_data[service_params][authlogin]" size="30" value="{$shipping.service_params.authlogin}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="authpassword">{__("shippings_sdek.authpassword")}</label>
    <div class="controls">
        <input id="authpassword" type="text" name="shipping_data[service_params][authpassword]" size="30" value="{$shipping.service_params.authpassword}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="dateexecute">{__("shippings.sdek.dateexecute")}</label>
    <div class="controls">
        <select id="dateexecute" name="shipping_data[service_params][dateexecute]">
            <option value="0" {if $shipping.service_params.dateexecute == "0"}selected="selected"{/if}>0</option>
            <option value="1" {if $shipping.service_params.dateexecute == "1"}selected="selected"{/if}>1</option>
            <option value="2" {if $shipping.service_params.dateexecute == "2"}selected="selected"{/if}>2</option>
            <option value="3" {if $shipping.service_params.dateexecute == "3"}selected="selected"{/if}>3</option>
            <option value="4" {if $shipping.service_params.dateexecute == "4"}selected="selected"{/if}>4</option>
            <option value="5" {if $shipping.service_params.dateexecute == "5"}selected="selected"{/if}>5</option>
            <option value="6" {if $shipping.service_params.dateexecute == "6"}selected="selected"{/if}>6</option>
            <option value="7" {if $shipping.service_params.dateexecute == "7"}selected="selected"{/if}>7</option>
            <option value="8" {if $shipping.service_params.dateexecute == "8"}selected="selected"{/if}>8</option>
            <option value="9" {if $shipping.service_params.dateexecute == "9"}selected="selected"{/if}>9</option>
            <option value="10" {if $shipping.service_params.dateexecute == "10"}selected="selected"{/if}>10</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="city">{__("shippings.sdek.sendercityid")}</label>
    <div class="controls">
        <input x-autocompletetype="city" type="text" id="city" name="shipping_data[service_params][sendercityid]" size="30" maxlength="64" value="{$shipping.service_params.sendercityid}" class=""/>
        <a href="#" id="sdek_get_city_link">{__("shipping.sdek.get_city_data")} {include file="common/tooltip.tpl" tooltip=__("shipping.sdek.get_city_data.tooltip")}</a>
    </div>
</div>

<div id="sdek_city_div">
{if $shipping.service_params.from_city_id && !$sdek_new_city_data}
    <div class="control-group">
        <label class="control-label" for="sdek_city_id">{__("shipping.sdek.city_id")} {include file="common/tooltip.tpl" tooltip=__("shipping.sdek.city_id.tooltip")}:</label>
        <div class="controls">
            <input id="sdek_city_id" type="text" name="shipping_data[service_params][from_city_id]" size="60" value="{$shipping.service_params.from_city_id}"/>
        </div>
    </div>
    {else}
    <div class="control-group">
        <label class="control-label" for="sdek_city_id">{__("shipping.sdek.city_id")} {include file="common/tooltip.tpl" tooltip=__("shipping.sdek.city_id.tooltip")}:</label>
        <div class="controls">
            <input id="sdek_city_id" type="text" name="shipping_data[service_params][from_city_id]" size="60" value="{$sdek_new_city_data.from_city_id}"/>
        </div>
    </div>
{/if}
<!--sdek_city_div--></div>

<div class="control-group">
    <label class="control-label" for="sendercitypostcode">{__("shippings.sdek.sendercitypostcode")}</label>
    <div class="controls">
        <input id="sendercitypostcode" type="text" name="shipping_data[service_params][sendercitypostcode]" size="30" value="{$shipping.service_params.sendercitypostcode}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_tariff">{__("shippings.sdek.tariffid")}</label>
    <div class="controls">
        <select id="ship_tariff" name="shipping_data[service_params][tariffid]">
            <optgroup label="{__("sdek_online_stores_services")}">
                <option value="136" {if ($shipping.service_params.tariffid) == 136}selected="selected"{/if}>{__("sdek_tariff_136")}</option>
                <option value="137" {if ($shipping.service_params.tariffid) == 137}selected="selected"{/if}>{__("sdek_tariff_137")}</option>
                <option value="138" {if ($shipping.service_params.tariffid) == 138}selected="selected"{/if}>{__("sdek_tariff_138")}</option>
                <option value="139" {if ($shipping.service_params.tariffid) == 139}selected="selected"{/if}>{__("sdek_tariff_139")}</option>
                <option value="233" {if ($shipping.service_params.tariffid) == 233}selected="selected"{/if}>{__("sdek_tariff_233")}</option>
                <option value="234" {if ($shipping.service_params.tariffid) == 234}selected="selected"{/if}>{__("sdek_tariff_234")}</option>
            </optgroup>
            <optgroup label="{__("sdek_regular_services")}">
                <option value="1" {if ($shipping.service_params.tariffid) == 1}selected="selected"{/if}>{__("sdek_tariff_1")}</option>
                <option value="3" {if ($shipping.service_params.tariffid) == 3}selected="selected"{/if}>{__("sdek_tariff_3")}</option>
                <option value="5" {if ($shipping.service_params.tariffid) == 5}selected="selected"{/if}>{__("sdek_tariff_5")}</option>
                <option value="10" {if ($shipping.service_params.tariffid) == 10}selected="selected"{/if}>{__("sdek_tariff_10")}</option>
                <option value="11" {if ($shipping.service_params.tariffid) == 11}selected="selected"{/if}>{__("sdek_tariff_11")}</option>
                <option value="12" {if ($shipping.service_params.tariffid) == 12}selected="selected"{/if}>{__("sdek_tariff_12")}</option>
                <option value="15" {if ($shipping.service_params.tariffid) == 15}selected="selected"{/if}>{__("sdek_tariff_15")}</option>
                <option value="16" {if ($shipping.service_params.tariffid) == 16}selected="selected"{/if}>{__("sdek_tariff_16")}</option>
                <option value="17" {if ($shipping.service_params.tariffid) == 17}selected="selected"{/if}>{__("sdek_tariff_17")}</option>
                <option value="18" {if ($shipping.service_params.tariffid) == 18}selected="selected"{/if}>{__("sdek_tariff_18")}</option>
                <option value="57" {if ($shipping.service_params.tariffid) == 57}selected="selected"{/if}>{__("sdek_tariff_57")}</option>
                <option value="58" {if ($shipping.service_params.tariffid) == 58}selected="selected"{/if}>{__("sdek_tariff_58")}</option>
                <option value="59" {if ($shipping.service_params.tariffid) == 59}selected="selected"{/if}>{__("sdek_tariff_59")}</option>
                <option value="60" {if ($shipping.service_params.tariffid) == 60}selected="selected"{/if}>{__("sdek_tariff_60")}</option>
                <option value="61" {if ($shipping.service_params.tariffid) == 61}selected="selected"{/if}>{__("sdek_tariff_61")}</option>
                <option value="62" {if ($shipping.service_params.tariffid) == 62}selected="selected"{/if}>{__("sdek_tariff_62")}</option>
                <option value="63" {if ($shipping.service_params.tariffid) == 63}selected="selected"{/if}>{__("sdek_tariff_63")}</option>
            </optgroup>
        </select>
    </div>
</div>

{if $shipping.service_params.tariffid && $shipping.service_params.from_city_id}
    {$from_office_ids = $smarty.const.SDEK_FROM_OFFICE_SERVICES|unserialize}

    {if $shipping.service_params.tariffid|in_array:$from_office_ids}
        {$offices = $shipping.service_params.from_city_id|fn_get_city_offices}
        {if $offices}
        <div class="control-group">
            <label class="control-label" for="shipment_point">{__("shippings.sdek.shipment_point")}</label>
            <div class="controls">
                <select id="shipment_point" name="shipping_data[service_params][shipment_point]">
                    {foreach from=$offices item="offc"}
                        <option value="{$offc.Code}" {if ($shipping.service_params.shipment_point) == $offc.Code}selected="selected"{/if}>{$offc.Address}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        {/if}
    {else}
        <div class="control-group">
            <label class="control-label" for="from_location">{__("shippings.sdek.from_location")}</label>
            <div class="controls">
                <input id="from_location" type="text" name="shipping_data[service_params][from_location]" size="60" value="{$shipping.service_params.from_location}"/>
            </div>
        </div>
    {/if}
{/if}

<div class="control-group">
    <label class="control-label" for="max_weight">{__("max_box_weight")}</label>
    <div class="controls">
    <input id="max_weight" type="text" name="shipping_data[service_params][max_weight_of_box]" size="30" value="{$shipping.service_params.max_weight_of_box|default:0}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_width">{__("ship_width")}</label>
    <div class="controls">
        <input id="ship_width" type="text" name="shipping_data[service_params][width]" size="30" value="{$shipping.service_params.width}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_height">{__("ship_height")}</label>
    <div class="controls">
        <input id="ship_height" type="text" name="shipping_data[service_params][height]" size="30" value="{$shipping.service_params.height}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_length">{__("ship_length")}</label>
    <div class="controls">
        <input id="ship_length" type="text" name="shipping_data[service_params][length]" size="30" value="{$shipping.service_params.length}" />
    </div>
</div>
</fieldset>
