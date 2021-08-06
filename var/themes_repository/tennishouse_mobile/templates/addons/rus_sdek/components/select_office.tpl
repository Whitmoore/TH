<script src="https://api-maps.yandex.ru/2.1/?apikey={$addons.development.ymaps_api_key|escape:javascript nofilter}&lang=ru_RU" type="text/javascript"></script>

<div id="map" class="ty-pickup-location-container-map"></div>
<div class="ty-pickup-location-container" id="ymaps_block">
    <div class="ty-pickup-location-tools">
        <div class="ty-checkout-select-office-city" id="ymaps_select_city">
            <div class="ty-control-group ty-profile-field__item">
                <input data-autocompletetype="city" id="s_city" type="text" size="32" value="{$city}" class="ty-input-text" tabindex="-1" autocomplete="nope" data-autocomplete-extra="map" />
                <label class="ty-control-group__title cm-profile-field">{__("locality")}</label>
            </div>
        <!--ymaps_select_city--></div>
        <div class="ty-pickup-location-view">
            <div class="ty-pickup-location-view-map" id="view_as_map" onclick="fn_toggle_ymaps_view()">{__("ymaps_display_as_map")}</div>
            <div class="ty-pickup-location-view-list" id="view_as_list" onclick="fn_toggle_ymaps_view()">{__("ymaps_display_as_list")}</div>
        </div>
    </div>
    <div class="ty-pickup-location-container-list">
        <div class="ty-checkout-select-office" id="office_list">
            {foreach from=$offices item=office}
                <div class="ty-one-office" id="office_{$office.Code}" onclick="fn_ymaps_show_office($(this).data('ymapsCode'));" data-ymaps-coord-x="{$office.coordX}" data-ymaps-coord-y="{$office.coordY}" data-ymaps-code="{$office.Code}">
                    <div class="ty-one-office__name">{$office.Name}</div>
                    <div class="ty-one-office__description">
                        {$office.City}, {$office.Address}<br />
                        {if $office.MetroStation}{__('metro_station')}: {['Ст.', 'ст.', 'м.', 'М.']|str_replace:'':$office.MetroStation}<br />{/if}
                        {$office.WorkTime}<br />
                    </div>
                </div>
            {foreachelse}
                <div class="ty-checkout-select-office-empty">{__("no_offices_in_this_city")}</div>
            {/foreach}
        <!--office_list--></div>
    </div>
    {if $offices}
        <div class="ty-checkout-select-office-details" id="office_details_list">
            <div onclick="fn_ymaps_hide_office();" class="ty-office-details-back"><div>{__('back_to_list')}</div></div>
            <div id="office_details_list_block" class="ty-office-details-block">
                {foreach from=$offices item=office}
                    <div class="ty-one-office-details" id="office_{$office.Code}_details">
                        <div class="ty-one-office__name" id="office_name_{$office.Code}">{$office.Name}</div>
                        <div class="ty-one-office__description">
                            <div id="office_address_{$office.Code}">{$office.City}, {$office.Address}</div>
                            {if $office.MetroStation}<div id="office_metro_{$office.Code}" data-metro-station="м. {['Ст.', 'ст.', 'м.', 'М.']|str_replace:'':$office.MetroStation}">{__('metro_station')}: {['Ст.', 'ст.', 'м.', 'М.']|str_replace:'':$office.MetroStation}</div>{/if}
                            <div class="ty-one-office__description-title">{__("phone")}</div>
                            {$office.Phone}<br />
                            <div class="ty-one-office__description-title">{__("working_hours_text")}</div>
                            {$work_time = ','|fn_explode:$office.WorkTime}
                            {foreach from=$work_time item="time"}
                                {$time}<br />
                            {/foreach}
                            {if $office.AddressComment}
                                <div class="ty-one-office__description-title">{__("how_to_get")}</div>
                                {$office.AddressComment}<br />
                            {/if}
                        </div>
                        {include file="buttons/button.tpl" but_text=__("select_this_office") but_meta="ty-btn__secondary cm-dialog-closer ty-select-office" but_id="select_this_office" but_onclick="fn_select_office('`$office.Code`');"}
                    </div>
                {/foreach}
            </div>
        <!--office_details_list--></div>
    {/if}
<!--ymaps_block--></div>
