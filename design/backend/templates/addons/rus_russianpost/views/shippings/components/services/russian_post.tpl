{* rus_build_pack dbazhenov *}
<fieldset>
<div class="control-group">
	<label for="ship_russian_post_shipping_type" class="control-label">{__("russian_post_shipping_type")}:</label>
    <div class="controls">
        <select id="ship_russian_post_shipping_type" name="shipping_data[service_params][shipping_type]">
            <option value="ground" {if $shipping.service_params.shipping_type == "ground"}selected="selected"{/if}>{__("ground")}</option>
            <option value="air" {if $shipping.service_params.shipping_type == "air"}selected="selected"{/if}>{__("air")}</option>
        </select>
    </div>
</div>

<div class="control-group">
	<label for="ship_russian_post_package_type" class="control-label">{__("russian_post_package_type")}:</label>
    <div class="controls">
        <select id="ship_russian_post_package_type" name="shipping_data[service_params][package_type]">
            <option value="zak_band" {if $shipping.service_params.package_type == "zak_band"}selected="selected"{/if}>{__("zak_band")}</option>
            <option value="zak_pis" {if $shipping.service_params.package_type == "zak_pis"}selected="selected"{/if}>{__("zak_pis")}</option>
            <option value="cen_band" {if $shipping.service_params.package_type == "cen_band"}selected="selected"{/if}>{__("cen_band")}</option>
            <option value="cen_pos" {if $shipping.service_params.package_type == "cen_pos"}selected="selected"{/if}>{__("cen_pos")}</option>
            <option value="cen_pis" {if $shipping.service_params.package_type == "cen_pis"}selected="selected"{/if}>{__("cen_pis")}</option>
        </select>
    </div>
</div>

</fieldset>
<script type="text/javascript">
//<![CDATA[
var elm = Tygh.$('#ship_russian_post_shipping_type');
fn_disable_rupost_package_type(elm);
elm.on('change', function(e) {$ldelim}
    fn_disable_rupost_package_type(Tygh.$(this));
{$rdelim});
function fn_disable_rupost_package_type(elm) {$ldelim}
    if (elm.val() == 'air') {$ldelim}
        Tygh.$('#ship_russian_post_package_type').find('[value="cen_band"],[value="cen_pos"]').attr('disabled', 'disabled');
    {$rdelim} else {$ldelim}
        Tygh.$('#ship_russian_post_package_type').find('[value="cen_band"],[value="cen_pos"]').removeAttr('disabled');
    {$rdelim}
{$rdelim}
//]]>
</script>
