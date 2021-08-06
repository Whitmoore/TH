{script src="js/addons/rus_spsr/func.js"}
<fieldset>

<div class="control-group">
    <label class="control-label" for="mode">{__("mode")}</label>
    <div class="controls">
        <select id="mode" name="shipping_data[service_params][mode]">
            <option value="T" {if $shipping.service_params.mode == "T"}selected="selected"{/if}>{__("test")}</option>
            <option value="L" {if $shipping.service_params.mode == "L"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="login">{__("shippings_spsr.login")}</label>
    <div class="controls">
        <input id="login" type="text" name="shipping_data[service_params][login]" size="30" value="{$shipping.service_params.login}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="password">{__("shippings_spsr.password")}</label>
    <div class="controls">
        <input id="password" type="text" name="shipping_data[service_params][password]" size="30" value="{$shipping.service_params.password}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="icn">{__("shippings_spsr.icn")}</label>
    <div class="controls">
        <input id="icn" type="text" name="shipping_data[service_params][icn]" size="30" value="{$shipping.service_params.icn}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="city">{__("shippings.spsr.sendercityid")}</label>
    <div class="controls">
        <input x-autocompletetype="city" type="text" id="city" name="shipping_data[service_params][sendercityid]" size="30" maxlength="64" value="{$shipping.service_params.sendercityid}" class=""/>
        <a href="#" id="spsr_get_city_link">{__("shipping.spsr.get_city_data")} {include file="common/tooltip.tpl" tooltip=__("shipping.spsr.get_city_data.tooltip")}</a>
    </div>
</div>

<div id="spsr_city_div">
{if $shipping.service_params.from_city_id && !$spsr_new_city_data}
    <div class="control-group">
        <label class="control-label" for="spsr_city_id">{__("shipping.spsr.city_id")} {include file="common/tooltip.tpl" tooltip=__("shipping.spsr.city_id.tooltip")}:</label>
        <div class="controls">
            <input id="spsr_city_id" type="text" name="shipping_data[service_params][from_city_id]" size="60" value="{$shipping.service_params.from_city_id}"/>
        </div>
    </div>
    {else}
    <div class="control-group">
        <label class="control-label" for="spsr_city_id">{__("shipping.spsr.city_id")} {include file="common/tooltip.tpl" tooltip=__("shipping.spsr.city_id.tooltip")}:</label>
        <div class="controls">
            <input id="spsr_city_id" type="text" name="shipping_data[service_params][from_city_id]" size="60" value="{$spsr_new_city_data.from_city_id}"/>
        </div>
    </div>
{/if}
<!--spsr_city_div--></div>

<div class="control-group">
    <label class="control-label" for="sendercitypostcode">{__("shippings.spsr.sendercitypost")}</label>
    <div class="controls">
        <input id="sendercitypostcode" type="text" name="shipping_data[service_params][sendercitypostcode]" size="30" value="{$shipping.service_params.sendercitypostcode}" />
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="ship_tariff">{__("shippings.spsr.tarifftype")}</label>
    <div class="controls">
        <select id="ship_tariff" name="shipping_data[service_params][tarifftype]">
            <option value='Услуги по доставке "Гепард-экспресс"' {if ($shipping.service_params.tarifftype) == 'Услуги по доставке "Гепард-экспресс"'}selected="selected"{/if}>Услуги по доставке "Гепард-экспресс"</option>
            <option value='Услуги по доставке "Гепард-экспресс 13"' {if ($shipping.service_params.tarifftype) == 'Услуги по доставке "Гепард-экспресс 13"'}selected="selected"{/if}>Услуги по доставке "Гепард-экспресс 13"</option>
            <option value='Услуги по доставке "Гепард-экспресс 18"' {if ($shipping.service_params.tarifftype) == 'Услуги по доставке "Гепард-экспресс 18"'}selected="selected"{/if}>Услуги по доставке "Гепард-экспресс 18"</option>
            <option value='Услуги по доставке "Пеликан-стандарт"' {if ($shipping.service_params.tarifftype) == 'Услуги по доставке "Пеликан-стандарт"'}selected="selected"{/if}>Услуги по доставке "Пеликан-стандарт"</option>
            <option value='Услуги по доставке "Пеликан-эконом"' {if ($shipping.service_params.tarifftype) == 'Услуги по доставке "Пеликан-эконом"'}selected="selected"{/if}>Услуги по доставке "Пеликан-эконом"</option>
        </select>
    </div>
</div>

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
