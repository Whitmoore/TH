{script src="js/addons/development/jquery.kladr.min.js"}
{script src="js/addons/development/func.js"}
<script type="text/javascript">
var error_validator_city = '{__("error_validator_city")|escape:"javascript"}';
{literal}

    function fn_product_shipping_settings(elm)
    {
        var jelm = Tygh.$(elm);
        var available = false;

        Tygh.$('input', jelm.parent()).each(function() {
            if (parseInt(Tygh.$(this).val()) > 0) {
                available = true;
            }
        });

        Tygh.$('input.shipping-dependence').prop('disabled', (available ? false : true));

    }

    function fn_get_feature_variants(obj, item_id)
    {
        Tygh.$.ceAjax('request', fn_url('development.get_feature_variants?feature_id=' + obj.val() + '&id=' + item_id + '&data_name=' + obj.data('ca-data-name')), {
            result_ids: 'feature_variants_' + item_id + '_' + obj.data('ca-target-id'),
            caching: false
        });
    }

{/literal}
</script>
