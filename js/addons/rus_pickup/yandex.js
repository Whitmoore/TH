(function(_, $) {
    (function($) {

        var map = null;
        var saved_point = null;
        var marker = null;
        var map_params = null;

        var init_check = false;
        var show_check = false;

        var latitude = 0;
        var longitude = 0;
        var zoom = 0;

        var latitude_name = '';
        var longitude_name = '';
        var map_container = '';

        function updatePoint(point)
        {
            if (saved_point && marker) {
                map.geoObjects.remove(marker);
            }

            marker = new csymaps.Placemark(point);

            map.geoObjects.add(marker);

            saved_point = point;

        }

        function addMapListeners()
        {
            map.events.add('click', function(event) {
                updatePoint(event.get('coords'));
            });
        }

        var methods = {

            init: function(options, callback) {

                if (! ('csymaps' in window)) {
                    
                    $.getScript('//api-maps.yandex.ru/2.1/?ns=csymaps&lang=' + options.language, function() {
                        csymaps.ready(function() {
                            $.ceMap('init', options, callback);
                        });
                    });


                    return false;
                }

                latitude = options.latitude;
                longitude = options.longitude;
                map_container = options.map_container;

                storeData = options.storeData;
                zoom = options.zoom;

                // Required fields - zoom, center
                map_params = {
                    zoom: 12,
                    type: 'yandex#map',
                    center: [latitude, longitude],
                    controls: ['default'],
                }

                if (_.area == 'A') {
                    $.extend(map_params, {
                        draggableCursor: 'crosshair',
                        draggingCursor: 'pointer'
                    });
                } else {
                    $.extend(map_params, {
                        zoom: zoom,
                        controls: options.controls,
                    });
                }

                if (typeof(callback) == 'function') {
                    callback();
                }
            },

            show: function(options)
            {

                if (!map_params) {
                    return $.ceMap('init', options, function() {
                        $.ceMap('show', options);
                    });
                } 

                if (map) {
                    map.destroy();
                } 
                    
                map = new csymaps.Map(document.getElementById(options.map_container), map_params);



                var marker;

                storeData = options.storeData;

                for (var keyvar = 0; keyvar < storeData.length; keyvar++) {

                    var marker_html = '<div style="padding-right: 10px"><strong>' + storeData[keyvar]['name'] + ' (' + storeData[keyvar]['pickup_surcharge'] + ' ' + storeData[keyvar]['currency']+ ')</strong><p>';

                    if (storeData[keyvar]['city'] != '') {
                        marker_html += storeData[keyvar]['city'] + ', ';
                    }
                    if (storeData[keyvar]['pickup_address'] != '') {
                        marker_html += storeData[keyvar]['pickup_address'];
                    }
                    if (storeData[keyvar]['pickup_phone'] != '') {
                        marker_html += '<br/>' + storeData[keyvar]['pickup_phone'];
                    }
                    if (storeData[keyvar]['pickup_time'] != '') {
                        marker_html += '<br/>' + storeData[keyvar]['pickup_time'];
                    }
                    if (storeData[keyvar]['description'] != '') {
                        marker_html += '<br/>' + storeData[keyvar]['description'];
                    }

                    marker_html += '<p><a data-ca-shipping-id="' + storeData[keyvar]['shipping_id'] + '" data-ca-group-key="' + storeData[keyvar]['group_key'] + '" data-ca-location-id="' + storeData[keyvar]['store_location_id'] + '" class="cm-map-select-location ty-btn ty-btn__tertiary text-button">Выбрать</a></p>';
                    marker_html += '</p><\/div>';

                    marker = new csymaps.Placemark([ storeData[keyvar]['latitude'], storeData[keyvar]['longitude'] ], {
                        balloonContentBody: marker_html,
                    });

                    map.geoObjects.add(marker);

                }

                if (storeData.length == 1) {

                    map.setCenter(marker.geometry.getCoordinates());
  
                    map.setZoom(zoom);

                } else {

                    csymaps.geoQuery(map.geoObjects).applyBoundsToMap(map);

                    var select = $('.ty-one-store__radio:checked').attr('value');

                    if (!select) {
                        var select = $('.one-store__radio:checked').attr('value');
                    }

                    if (select) {
                        $.each(storeData, function( key, value ) {
                            if (value['store_location_id'] == select) {
                                map.setCenter([value['latitude'],value['longitude']]);  
                                map.setZoom(zoom);
                            }
                        });
                    }
                }

            },

            saveLocation: function()
            {
                if (saved_point) {
                    $('#' + latitude_name).val(saved_point[0]);
                    $('#' + latitude_name + '_hidden').val(saved_point[0]);
                    $('#' + longitude_name).val(saved_point[1]);
                    $('#' + longitude_name + '_hidden').val(saved_point[1]);
                }

                saved_point = null;
            },

            selectLocation: function(location, group_key, shipping_id)
            {
                $('#store_' + group_key + '_' + shipping_id + '_' + location).prop("checked", true);


                fn_calculate_total_shipping_cost();

            },

            viewLocation: function(latitude, longitude)
            {
                map.setCenter([latitude, longitude]);
                map.setZoom(zoom);
            },

            viewLocations: function()
            {
                csymaps.geoQuery(map.geoObjects).applyBoundsToMap(map);
            }
        }

        $.extend({
            ceMap: function(method) {
                if (methods[method]) {
                    return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
                } else {
                    $.error('ty.map: method ' +  method + ' does not exist');
                }
            }
        });
    })($);

    $(document).ready(function() {

        $(document).on('click', '.cm-map-dialog', function () {
            $.ceMap('showDialog', 'elm_country', 'elm_city', 'elm_latitude', 'elm_longitude');
        });

        $(document).on('click', '.cm-map-save-location', function () {
            $.ceMap('saveLocation');
        });

        $(document).on('click', '.cm-map-select-location', function () {
            var jelm = $(this);
            var location = jelm.data('ca-location-id');
            var group_key = jelm.data('ca-group-key');
            var shipping_id = jelm.data('ca-shipping-id');

            $.ceMap('selectLocation', location, group_key, shipping_id);
        });

        $(document).on('click', '.cm-map-view-location', function () {
            var jelm = $(this);
            var latitude = jelm.data('ca-latitude');
            var longitude = jelm.data('ca-longitude');

            $.ceMap('viewLocation', latitude, longitude);
        });

        $(document).on('click', '.cm-map-view-locations', function () {
            $.ceMap('viewLocations');
        });

    });
}(Tygh, Tygh.$));

