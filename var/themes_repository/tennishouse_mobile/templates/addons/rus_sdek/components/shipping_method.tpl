<div data-group-key="{$group_key}" id="select_office">
    {if $shipping.data.offices}
        {assign var="shipping_id" value=$shipping.shipping_id}
        {$selected_code = $select_office.$group_key.$shipping_id}

        <input type="hidden" name="select_office[{$group_key}][{$shipping_id}]" value="{$selected_code}" id="office_id" >

        <div class="ty-get-additional-shipping-info">
            <div class="ty-selected-office-data {if !$selected_code || !$shipping.data.offices.$selected_code}hidden{/if}" id="selected_office_data">
                {if $selected_code && $shipping.data.offices.$selected_code}
                    {$shipping.data.offices.$selected_code.Name} | {if $shipping.data.offices.$selected_code.MetroStation}м. {['Ст.', 'ст.', 'м.', 'М.']|str_replace:'':$shipping.data.offices.$selected_code.MetroStation} | {/if}
                    {$shipping.data.offices.$selected_code.City}, {$shipping.data.offices.$selected_code.Address}
                {/if}
            </div>
            {capture name="pickup_location_select"}
                {include file="addons/rus_sdek/components/select_office.tpl" offices=$shipping.data.offices city=$cart.user_data.s_city}
            {/capture}

            {$id = "user_location"}
            {if $selected_code && $shipping.data.offices.$selected_code}
                {$link_meta = ""}
                {$link_text = __("change_pickup_location")}
            {else}
                {$link_text = __("select_pickup_location")}
                {$link_meta = "ty-btn"}
            {/if}
            {include file="common/popupbox.tpl"
                content=$smarty.capture.pickup_location_select
                link_text=$link_text
                text=__("pickup_location_selection")
                id="pickup_location"
                link_meta=$link_meta
                full_mode="true"
                edit_onclick="fn_init_ymaps($('#office_list'))"
            }
        </div>
    {else if $shipping.data.get_offices}
        <script type="text/javascript" class="cm-ajax-force">
        (function(_, $) {
            $(document).ready(function() {
                var auto_form = $('#select_office').closest('.cm-autocomplete-form');
                if (auto_form.length) {
                    var copy_form = auto_form.clone();
                    $('[name=result_ids]', copy_form).val('select_office');
                    var form_data = copy_form.serializeArray();
                    form_data.push({
                        name: 'group_key',
                        value: $('#select_office').data('groupKey')
                    });
                    $.ceAjax('request', fn_url('sdek.get_offices'), {
                        method: 'post',
                        hidden: true,
                        result_ids: 'select_office',
                        data: form_data,
                        callback: function(data) {
                            fn_init_autocomplete(auto_form);
                            fn_init_placeholder();
                        },
                    });
                }
            });
        }(Tygh, Tygh.$));
        </script>
    {/if}
<!--select_office--></div>
