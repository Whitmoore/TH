<div id="product_shipping_estimation">
    {if $smarty.session.approx_shipping.city && $smarty.session.approx_shipping.time}
        {capture name="user_location_popup"}
        <form name="user_location_form" action="{""|fn_url}" method="post" class="cm-ajax cm-ajax-force cm-autocomplete-form cm-label-placeholder" id="user_location_block">
            <input type="hidden" name="result_ids" value="product_shipping_estimation" />
            <input type="hidden" data-autocompletetype="country_code" name="country_code" value="" />
            <input type="hidden" data-autocompletetype="city_id" name="city_id" value="" data-submitonchange="true"/>
            <input type="hidden" data-autocompletetype="city_id_type" name="city_id_type" value="" />
            <input type="hidden" data-autocompletetype="state_raw" name="state_raw" value="" />
            <input type="hidden" data-autocompletetype="state" name="state" value="" />
            <div class="ty-product-shipping-section">
                {$cities = ""|fn_get_big_cities}
                {$c_columns = "3"}
                <div class="ty-product-shipping-title">{__("select_other_city")}</div>
                {$biggest = $cities|array_shift}
                <div class="ty-select-city-block">
                    <div class="ty-select-city-row">
                        <div class="ty-select-city-item" data-city-id="{$biggest.city_code}" data-city-id-type="{$biggest.city_id_type}" data-city="{$biggest.city}" data-state="{$biggest.state_code}" data-country="{$biggest.country_code}"><strong>{$biggest.city}</strong></div>
                    </div>
                    {split data=$cities size=$c_columns assign="splitted_cities"}
                    {foreach from=$splitted_cities item="scities"}
                        <div class="ty-select-city-row">
                        {foreach from=$scities item="city"}
                            <div class="ty-select-city-item" data-city-id="{$city.city_code}" data-city-id-type="{$city.city_id_type}" data-city="{$city.city}" data-state="{$city.state_code}" data-country="{$city.country_code}">{$city.city}</div>
                        {/foreach}
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="ty-product-shipping-section">
                <div class="ty-control-group">
                    <input data-autocompletetype="city" type="text" name="city" size="32" value="" class="ty-input-text " onchange="fn_city_change($(this).closest('.cm-autocomplete-form'));" onkeydown="fn_city_keydown($(this).closest('.cm-autocomplete-form'));"/>
                    <label class="ty-control-group__title">{__("enter_another_city")}</label>
                </div>
                {include file="buttons/button.tpl" but_text=__("select") but_name="dispatch[development.update_user_city]" but_meta="ty-btn__secondary cm-form-dialog-closer hidden" but_id="submit_on_change_button"}
            </div>
            <script type="text/javascript">
            {literal}
            (function(_, $) {
                $(function() {
                    $('#user_location_block.cm-autocomplete-form').each(function(){
                        fn_init_autocomplete($(this));
                    });
                    $('#user_location_block').each(function() {
                        var this_form = $(this);
                        $('.ty-select-city-item').click(function() {
                            $("[name='city_id']", this_form).val($(this).data('cityId'));
                            $("[name='city_id_type']", this_form).val($(this).data('cityIdType'));
                            $("[name='city']", this_form).val($(this).data('city'));
                            $("[name='state']", this_form).val($(this).data('state'));
                            $("[name='country_code']", this_form).val($(this).data('country'));
                            $('#submit_on_change_button', this_form).click();
                        });
                    });
                });
            }(Tygh, Tygh.$));
            {/literal}
            </script>
        </form>
        {/capture}

        {capture name="user_location_link"}
            {$id = "user_location"}
            {include file="common/popupbox.tpl"
                content=$smarty.capture.user_location_popup
                link_text=$smarty.session.approx_shipping.city
                text=__("your_city", ['[city]' => $smarty.session.approx_shipping.city])
                id=$id
                link_meta="ty-product-shipping-link"
            }
        {/capture}
        <div class="ty-product-shipping-estimation">{__("product_shipping_estimation_text", ['[city]' => $smarty.capture.user_location_link, '[ship_time]' => $smarty.session.approx_shipping.time])}</div>
    {elseif !$smarty.session.approx_shipping.is_complete}
        {literal}
        <script type="text/javascript">
            $.ceAjax('request', fn_url('development.product_shipping_estimation'), {
                hidden: true,
                result_ids: 'product_shipping_estimation',
                method: 'get'
            });
        </script>
        {/literal}
    {/if}
<!--product_shipping_estimation--></div>
