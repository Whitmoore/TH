function fn_send_call_request(obj_id) {

    var cart_changed = true;

    var params = [];

    if ($('form[name="product_form_' + obj_id + '"]').length) {
        $('#product_data_block').html('');

        var elms = $(':input:not([type=radio]):not([type=checkbox])', 'form[name="product_form_' + obj_id + '"]');
        $.each(elms, function(id, elm) {
            if (elm.type != 'submit' && elm.type != 'file' && !($(this).hasClass('cm-hint') && elm.value == elm.defaultValue) && elm.name.length != 0 && elm.name.match("^product_data")) {
                $('#product_data_block').append('<input type="hidden" name="' + elm.name + '" value="' + elm.value + '" />');
            }
        });

        var radio = $('input[type=radio]:checked, input[type=checkbox]', 'form[name="product_form_' + obj_id + '"]');
        $.each(radio, function(id, elm) {
            if (elm.name.match("^product_data")) {
                if ($(elm).prop('disabled')) {
                    return true;
                }
                var value = elm.value;
                if ($(elm).is('input[type=checkbox]:checked')) {
                    if (!$(elm).hasClass('cm-no-change')) {
                        value = $(elm).val();
                    }
                } else if ($(elm).is('input[type=checkbox]')) {
                    if (!$(elm).hasClass('cm-no-change')) {
                        value = 'unchecked';
                    } else {
                        value = '';
                    }
                }
                $('#product_data_block').append('<input type="hidden" name="' + elm.name + '" value="' + value + '" />');
            }
        });
    }
}

function fn_format_obj(obj, query, is_label)
{
    if (obj.parents) {
        var objs = [];
        for (var k = obj.parents.length - 1; k > -1; k--) {
            var parent = obj.parents[k];
            if (parent.contentType == $.kladr.type.street) {
                objs = objs.concat(parent);
            }
        }
        if (!is_label) {
            objs.push(obj);
        }
        var lastIds = [],
                address = '';
        $.each(objs, function (i, obj) {
                var name = '',
                        type = '',
                        j;

                if ($.type(obj) === 'object') {
                    for (j = 0; j < lastIds.length; j++) {
                        if (lastIds[j] == obj.id) {
                            return;
                        }
                    }

                    lastIds.push(obj.id);

                    name = obj.name;
                    type = obj.typeShort + '. ';
                } else {
                    name = obj;
                }

                if (address) address += ', ';
                address += type + name;
        });
        if (is_label) {
            if (address) address += ' ';
            var name = obj.name.toLowerCase();
            query = query.name.toLowerCase();

            var start = name.indexOf(query);
            start = start > 0 ? start : 0;

            if (obj.typeShort) {
                address += obj.typeShort + '. ';
            }

            if (query.length < obj.name.length) {
                address += obj.name.substr(0, start);
                address += '<strong>' + obj.name.substr(start, query.length) + '</strong>';
                address += obj.name.substr(start + query.length, obj.name.length - query.length - start);
            } else {
                address += '<strong>' + obj.name + '</strong>';
            }
        }
        return address;
    }
    return (obj.typeShort ? obj.typeShort + '. ' : '') + obj.name;
}

function fn_check_city(obj, init)
{
    var form = obj.closest('.cm-autocomplete-form');
    var city_id = $("[data-autocompletetype='city_id']", form);
    var zip = $("[data-autocompletetype='postal-code']", form);
    var country_code = $("[data-autocompletetype='country_code']", form);
    var state = $("[data-autocompletetype='state']", form);

    lbl = $("label[for='" + obj.attr('id') + "']");
    $('#' + obj.attr('id') + '_error_message').remove();

    if (city_id.length && obj.val() != '') {
        if (city_id.val() == '') {
            lbl.parent().addClass('error');
            obj.addClass('cm-failed-field');
            lbl.addClass('cm-failed-label');

            if (!obj.hasClass('cm-no-failed-msg')) {
                obj.after('<span id="' + obj.attr('id') + '_error_message" class="help-inline"><p>' + error_validator_city + '</p></span>');
            }

            return false;
        } else {
            lbl.parent().removeClass('error');
            obj.removeClass('cm-failed-field');
            lbl.removeClass('cm-failed-label');
        }
        // } else if (city_id.hasClass('cm-city-selected') && state.length) {
        //     state.parent('.ty-control-group').show();
        //     if (zip.length) {
        //         zip.parent('.ty-control-group').show();
        //     }
        // }
    }

    return true;
}

function fn_get_countries(request, response)
{
    $.ceAjax('request', fn_url('city.autocomplete_country?q=' + encodeURIComponent(request.term)), {
        hidden: true,
        callback: function(data) {
            response(data.autocomplete);
        }
    });
}

function fn_get_cities(elm, request, response)
{
    $.ceAjax('request', fn_url('city.autocomplete_city?city=' + request.term), {
        hidden: true,
        callback: function(data) {
            response(data.cities);
        }
    });
}

function fn_city_keydown(auto_form)
{
    var city_id = $("[data-autocompletetype='city_id']", auto_form);
    var state = $("[data-autocompletetype='state']", auto_form);

    if (city_id.val() != '') {
        city_id.val('');
        city_id.addClass('cm-city-changed');
    }

}

function fn_city_change(auto_form)
{
    var city_id = $("[data-autocompletetype='city_id']", auto_form);

    if (city_id.hasClass('cm-city-changed') && city_id.data('submitonchange')) {
        auto_form.addClass('cm-skip-validation');
        $('#submit_on_change_button', auto_form).click();
    }
}

function fn_process_checkout(obj)
{
    var auto_form = obj.closest('form');
    if (auto_form.length) {
        auto_form.addClass('cm-skip-validation');
        auto_form.submit();
    }
}

function fn_change_autocomplete_location(form, location)
{
    var city_id = $("[data-autocompletetype='city_id']", form);
    var city_id_type = $("[data-autocompletetype='city_id_type']", form);
    var zip = $("[data-autocompletetype='postal-code']", form);
    var address = $("[data-autocompletetype='street-address']", form);
    var state = $("[data-autocompletetype='state']", form);

    if (location.city_id != city_id.val()) {
        city_id.addClass('cm-city-changed');
    }
    city_id.addClass('cm-city-selected');

    $.each( location, function( key, value ) {
        if ($("[data-autocompletetype='" + key + "']", form).length) {

            old_val = $("[data-autocompletetype='" + key + "']", form).val();
            $("[data-autocompletetype='" + key + "']", form).val(value);
            if (key == 'country_code' && old_val != value) {
                $("[data-autocompletetype='" + key + "']", form).trigger('change');
            }
            if (!city_id.data('submitonchange') && key == 'state_raw') {
                $.ceAjax('request', fn_url('development.find_state_match'), {
                    method: 'post',
                    hidden: true,
                    data: {state: value},
                    callback: function(data) {
                        if (typeof(state.attr('sb')) != 'undefined') {
                            $('#' + state.attr('sb') + '_' + data.text.replace(/\"/g, "")).trigger('click.sb');
                        } else {
                            state.val(data.text.replace(/\"/g, ""));
                            if ($.mobile && state.is("select")) {
                                state.selectmenu();
                                state.selectmenu('refresh');
                            }
                        }
                    },
                });
            }
        }
    });

    if (city_id.val() != '' && city_id_type.val() == 'kladr') {
        if (address.length) {
            address.kladr({
                oneString: true,
                verify: false,
                parentType: $.kladr.type.city,
                parentId: location.city_id,
                type: $.kladr.type.street,
                labelFormat: function (obj, query) {
                    return fn_format_obj(obj, query, true);
                },
                valueFormat: function (obj, query) {
                    return fn_format_obj(obj, query, false);
                },
                select: function (obj) {
                    if (zip.length) {
                        zip.val(obj.zip);
                    }
                }
            });
        }
    }

    if (typeof(location.city) != 'undefined') {
        $("[data-autocompletetype='city']", form).each(function(){
            $(this).val(location.city);
        });
    }
}

function fn_init_autocomplete_city(city, form)
{
    var address = $("[data-autocompletetype='street-address']", form);
    var zip = $("[data-autocompletetype='postal-code']", form);
    var city_id = $("[data-autocompletetype='city_id']", form);
    var city_id_type = $("[data-autocompletetype='city_id_type']", form);

    $.widget( "app.autocomplete", $.ui.autocomplete, {
        _renderItem: function( ul, item ) {
            var re = new RegExp( "(" + this.term + ")", "i" ),
                $li = $( "<li/>" ).appendTo( ul );

            $( "<a/>" )/*.attr( "href", "#" )*/
               .html( item.label.replace( re, "<span class='ui-autocomplete-term'>$1</span>" ) )
               .appendTo( $li );

            return $li;
        }
    });

    if (city.length) {
        city.autocomplete({
            source: function(request, response) {
                fn_get_cities(form, request, response);
            },
            select: function( event, ui ) {
                if (typeof(city.data('autocompleteExtra')) == 'undefined') {
                    fn_change_autocomplete_location(form, ui.item);
                } else {
                    form.append('<input type="hidden" id="preselected_data"/>');
                    $('#preselected_data', form).data('data', JSON.stringify(ui.item));
                }
                fn_check_city(city, false);

                city.trigger('change');
            },
        }).bind('focus', function(){ $(this).autocomplete("search"); } );

        if (typeof(city.data('autocompleteExtra')) == 'undefined') {
            city.on('change', function(){
                fn_city_change(form);
            }).on('keydown', function(){
                fn_city_keydown(form);
            }).on('blur', function(){
                fn_check_city(city, false);
            });
        } else {
            if (city.data('autocompleteExtra') == 'map') {
                city.on('change', function(){
                    fn_reload_city_offices(true);
                })
            }
        }

        if (city_id.val() != '' && city_id_type.val() == 'kladr') {
            $.kladr.setDefault({
                verify: true,
            });
            address.kladr({
                oneString: true,
                verify: false,
                parentType: $.kladr.type.city,
                parentId: city_id.val(),
                type: $.kladr.type.street,
                labelFormat: function (obj, query) {
                    return fn_format_obj(obj, query, true);
                },
                valueFormat: function (obj, query) {
                    return fn_format_obj(obj, query, false);
                },
                select: function (obj) {
                    if (zip.length) {
                        zip.val(obj.zip);
                    }
                }
            });
        }

        fn_check_city(city, true);
    }
}

function fn_init_autocomplete(elm)
{
    var country = $("[data-autocompletetype='country']", elm);
    var country_code = $("[data-autocompletetype='country_code']", elm);

    if (country.length && !country.hasClass('cm-autocomplete-form-done')) {
        country.autocomplete({
            source: function(request, response) {
                var type = this.element.attr('name').substr(10,1);
                fn_get_countries(request, response);
            },
            select: function( event, ui ) {
                if (country_code.length) {
                    country_code.val(ui.item.code);
                }
                $("[data-autocompletetype='city']", elm).each(function(){
                    if (!$(this).hasClass('cm-autocomplete-form-done')) {
                        fn_init_autocomplete_city($(this), elm)
                        $(this).addClass('cm-autocomplete-form-done');
                    }
                })

                country_code.trigger('change');
            }
        });
        if (country.data("ui-autocomplete")) {
            country.data("ui-autocomplete")._renderItem = function (ul, item) {
                var newText = String(item.value).replace(
                        new RegExp(this.term, "gi"),
                        "<span class='ui-autocomplete-term'>$&</span>");

                return $("<li></li>")
                    .data("ui-autocomplete-item", item)
                    .append("<div>" + newText + "</div>")
                    .appendTo(ul);
            };
        }
        country.addClass('cm-autocomplete-form-done');
    }

    $("[data-autocompletetype='city']", elm).each(function(){
        if (!$(this).hasClass('cm-autocomplete-form-done')) {
            fn_init_autocomplete_city($(this), elm)
            $(this).addClass('cm-autocomplete-form-done');
        }
    })
}

function fn_select_office(code)
{
    $('#office_id').val(code);
    $('#office_id').trigger('change');

    $('#opener_pickup_location').removeClass('ty-btn');
    $('#opener_pickup_location span').html(change_pickup_location);

    name = $('#office_name_' + code).html() + ' | ';
    if (typeof($('#office_metro_' + code).data('metroStation')) != 'undefined') {
        name += $('#office_metro_' + code).data('metroStation') + ' | ';
    }
    name += $('#office_address_' + code).html();
    $('#selected_office_data').html(name).removeClass('hidden');

    var auto_form = $('#office_list').closest('.cm-autocomplete-form');
    var preselected_data = $('#preselected_data', auto_form);
    if (preselected_data.length) {
        var data = JSON.parse(preselected_data.data('data'));
        fn_change_autocomplete_location(auto_form, data);
    }

    fn_process_checkout($('#office_list'));
}

function fn_reload_city_offices(relocate)
{
    var auto_form = $('#office_list').closest('.cm-autocomplete-form');
    var preselected_data = $('#preselected_data', auto_form);

    if (preselected_data.length) {
        $.ceAjax('request', fn_url('sdek.select_office'), {
            method: 'post',
            result_ids: 'ymaps_block',
            data: {form: preselected_data.data('data')},
            callback: function(data) {
                if (typeof(ymaps) != 'undefined' && typeof(myMap) != 'undefined') {
                    myMap.geoObjects.removeAll();
                }
                fn_init_ymaps($('#office_list'), true);
                fn_init_autocomplete(auto_form);
                fn_init_placeholder();
            },
        });
    }
}

function fn_init_auto_save(context)
{
    $('.cm-auto-save', context).each(function(){
        if (typeof($(this).data('autoSaveDispatch')) != 'undefined') {
            var auto_form = $(this);

            $(":input", auto_form).each(function() {
                $(this).on('keyup', function() {
                    var this_val = $(this).val();
                    var this_elm = $(this);
                    setTimeout(function() {
                        if (this_elm.val() == this_val) {
                            $.ceAjax('request', fn_url(auto_form.data('autoSaveDispatch') + '?' + auto_form.serialize().replace(/&dispatch=([^&]+)/, '')), {
                                method: 'get',
                                hidden: true,
                            });
                        }
                    }, 1000);
                });
            });
        }
    });
}

function fn_init_ymaps(offices_list, relocate)
{

    if (typeof(ymaps) != 'undefined') {
        auto_form = offices_list.closest('.cm-autocomplete-form');
        var state = $("[data-autocompletetype='state']", auto_form);
        var city = $("[data-autocompletetype='city']", auto_form);
        var country = $("[data-autocompletetype='country_code']", auto_form);

        ymaps.ready(function () {

            if ((typeof(myMap) == 'undefined' || $('#map').html().length == 0) && $("#ymaps_select_city [data-autocompletetype='city']").val()) {
                myMap = new ymaps.Map("map", {
                    center: [55.76, 37.64],
                    zoom: 5,
                    controls: ['zoomControl']
                }, {suppressMapOpenBlock: true});

                var searchControl = new ymaps.control.SearchControl({
                    options: {
                        float: 'left',
                        floatIndex: 100,
                        noPlacemark: true,
                        maxWidth: [30, 72, 500],
                        fitMaxWidth: true,
                    }
                });
                searchControl.events.add('resultselect', function (e) {
                    var result = searchControl.getResult(0);
                    result.then(function (res) {
                        properties = res.properties.get('metaDataProperty.GeocoderMetaData.Address.Components');
                        var form = {};
                        if (properties.length) {
                            for (var i = 0; i < properties.length; i++) {
                                if (properties[i].kind == 'country') {
                                    form.country = properties[i].name;
                                }
                                if (properties[i].kind == 'locality') {
                                    form.city = properties[i].name;
                                }
                                if (properties[i].kind == 'province') {
                                    form.state_raw = properties[i].name;
                                }
                            }
                            if (form.country && form.city && form.state_raw) {
                                $.ceAjax('request', fn_url('sdek.select_office'), {
                                    method: 'post',
                                    result_ids: 'ymaps_block',
                                    data: {form: JSON.stringify(form)},
                                    callback: function(data) {
                                        if (data.preselected_data) {
                                            if (!$('#preselected_data', auto_form).length) {
                                                auto_form.append('<input type="hidden" id="preselected_data"/>');
                                            }
                                            $('#preselected_data', auto_form).data('data', JSON.stringify(data.preselected_data));
                                        }
                                        myMap.geoObjects.removeAll();
                                        fn_init_ymaps($('#office_list'), false);
                                        fn_ymaps_refresh_offices();
                                        fn_init_autocomplete(auto_form);
                                        fn_init_placeholder();
                                    },
                                });
                            }
                        }

                    }, function (err) {
                        console.log("Ошибка");
                    });
                });
                myMap.controls.add(searchControl);

            }
            fn_rebuild_offices(offices_list, relocate);
        });
    }
}

function fn_rebuild_offices(offices_list, relocate = true)
{
    if (typeof(ymaps) == 'undefined' || typeof(myMap) == 'undefined') {
        return false;
    }
    offices = [];
    var clusterIcons = [{
        href: images_dir + '/addons/development/cluster.svg',
        size: [48, 48],
        offset: [-24, -24]
    }];
    clusterer = new ymaps.Clusterer({
        clusterIcons: clusterIcons,
    });
    offices_list.children('.ty-one-office').each(function() {
        var plcm = new ymaps.Placemark([$(this).data('ymapsCoordY'), $(this).data('ymapsCoordX')], {'id': $(this).data('ymapsCode')}, {
            iconLayout: 'default#image',
            iconImageHref: images_dir + '/addons/development/placemark.svg',
            iconImageSize: [44, 44],
            iconImageOffset: [-15, -27]
        });
        plcm.events.add('click', function() {
            fn_ymaps_show_office(plcm.properties.get('id'));
        });
        offices.push(plcm);
    });

    if (offices.length > 0) {
        clusterer.add(offices);
        myMap.geoObjects.add(clusterer);

        if (relocate) {
            var coords = ymaps.util.bounds.getCenterAndZoom(myMap.geoObjects.getBounds(), myMap.container.getSize(), myMap.options.get('projection'));
            if (offices.length > 1) {
                zoom = coords.zoom;
            } else {
                zoom = 14;
            }
            myMap.setCenter(coords.center, zoom);
        }

        myMap.events.add('boundschange', function () {
            fn_ymaps_refresh_offices();
        });
    } else {
        var preselected_data = offices_list.closest('.cm-autocomplete-form').find('#preselected_data');
        if (preselected_data.length) {
            var data = JSON.parse(preselected_data.data('data'));
        }

        if (typeof(data.label) != 'undefined') {
            var map_center = data.label;
        } else if (typeof(data.state_raw) != 'undefined' && typeof(data.city) != 'undefined' && typeof(data.country) != 'undefined') {
            var map_center = data.city + ', ' + data.state_raw + ', ' + data.country;
        } else {
            var map_center = $("#ymaps_select_city [data-autocompletetype='city']").val();
        }

        ymaps.geocode(map_center, {
            results: 1
        }).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0),
            bounds = firstGeoObject.properties.get('boundedBy');

            myMap.setBounds(bounds, {
                checkZoomRange: true
            });
        });

    }
}

function fn_ymaps_refresh_offices()
{
    if (typeof(ymaps) == 'undefined' || !$('#map').is(':visible')) {
        return false;
    }
    $('#office_list').children('.ty-one-office').each(function(){
        $(this).hide();
    });
    var visibleObjects = ymaps.geoQuery(offices).searchIntersect(myMap);

    visibleObjects.each(function(x) {
        $('#office_' + x.properties.get('id')).show();
    });

    if (!visibleObjects.getLength()) {
        $('#no_offices_zoom_tip').show();
        $('#more_offices_zoom_tip').hide();
    } else {
        if (ymaps.geoQuery(offices).getLength() > visibleObjects.getLength()) {
            $('#more_offices_zoom_tip').show();
        } else {
            $('#more_offices_zoom_tip').hide();
        }
        $('#no_offices_zoom_tip').hide();
    }
}

function fn_close_anouncement()
{
    $('#anouncement_block').slideUp();
    $.ceAjax('request', fn_url('development.hide_anouncement'), {
        method: 'get',
        hidden: true
    });
}

function fn_hide_form(e, event) {
    if (!$(e.target).hasClass('ty-product-add-review-wrapper') && !$(e.target).parents(".ty-product-add-review-wrapper").size()) {
        $(event.target).parents(".ty-product-add-review").removeClass('ty-product-add-review-is-focus');
        $('.cm-show-form').focus(function(e){
            fn_show_form(e);
        });
        $(this).unbind( e )
    }
}
function fn_show_form(event)
{
    $(event.target).parents(".ty-product-add-review").addClass('ty-product-add-review-is-focus');
    $("body").click(function(e) {
        fn_hide_form(e, event);
    });
    $(this).unbind( event )
}

function fn_send_sd_form(obj, sd_param_value)
{
    if (obj.data('form')) {
        var form_name = obj.data('form');
    } else {
        var form_name = 'stringing_form';
    }
    $('form[name="' + form_name + '"]').prepend('<input type="hidden" id="sd_hidden_params" name="sd_change" value="' + sd_param_value + '">');
    $('form[name="' + form_name + '"] label').each(function() {
        if ($(this).hasClass('cm-required')) {
            $(this).removeClass('cm-required').addClass('cm-required-stop');
        }
        if ($(this).hasClass('cm-requirement-popup')) {
            $(this).removeClass('cm-requirement-popup').addClass('cm-requirement-popup-stop');
        }
    });
    $('form[name="' + form_name + '"] :submit').click();
    $.ceEvent('on', 'ce.formajaxpost_' + form_name, function(notification) {
        $('#sd_hidden_params').remove();
        $('form[name="' + form_name + '"] label').each(function() {
            if ($(this).hasClass('cm-required-stop')) {
                $(this).removeClass('cm-required-stop').addClass('cm-required');
            }
            if ($(this).hasClass('cm-requirement-popup-stop')) {
                $(this).removeClass('cm-requirement-popup-stop').addClass('cm-requirement-popup');
            }
        });
    });
}

function fn_init_sd_option(context)
{
    $('.cm-sd-option', context).not( ".cm-sd-option-processed" ).each(function(){
        if ($(this).is("select")) {
            $(this).change(function(){
                fn_send_sd_form($(this), $(this).attr('name') + '=' + $(this).val());
            });
        } else {
            $(this).click(function(){
                fn_send_sd_form($(this), $.param($(this).data()));
            });
        }
        $(this).addClass('cm-sd-option-processed');
    });
}

function fn_init_clipboard_copy(context)
{
    $('.cm-copy-clipboard', context).each(function(){
        $(this).focus(function(){
            $(this).select();
            document.execCommand('copy');
        })
    });
}
