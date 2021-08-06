(function(_, $) {
    $(document).ready(function() {

        if (! ('ymaps' in window)) {
            $.getScript('//api-maps.yandex.ru/2.0/?load=package.standard,package.geoQuery&lang=ru-RU');
        }

        if (window.mswidget !== undefined) {

            mswidget.ready(function(){
                mswidget.initCartWidget({
                    //получить указанный пользователем город
                    'getCity': function () {
                        if (mswidget.city) {
                            return {value: mswidget.city};
                        } else {
                            return false;
                        }
                    },

                    //id элемента-контейнера
                    'el': 'mswidget',

                    //габариты 1 единицы усредненного товара
                    'length': 1,
                    'width': 1,
                    'height': 1,
                    'city': '',

                    'group_id': 0,
                    'shipping_id': 0,

                    //общее количество товаров в корзине
                    'totalItemsQuantity': function () { return $('#multiship' + mswidget.group_id).data('amount'); },
                    //общий вес товаров в корзине
                    'weight': function () { return $('#multiship' + mswidget.group_id).data('weight'); },
                    //общая стоимость товаров в корзине
                    'cost': function () { return $('#multiship' + mswidget.group_id).data('cost'); },

                    'itemsDimensions': function () {return [
                        [mswidget.width, mswidget.height, mswidget.length, 1]
                    ]},

                    //обработка смены варианта доставки
                    'onDeliveryChange': function (delivery) {
                        //если выбран вариант доставки, выводим его описание и закрываем виджет, иначе произошел сброс варианта,
                        //очищаем описание
                        if (delivery) {

                            if (Tygh.area == 'C') {
                                params = [];
                                parents = $('#shipping_rates_list');
                                radio = $('input[type=radio]:checked', parents);

                                if (mswidget.url == 'checkout.checkout') {
                                    checkout = true;
                                    method_request = 'get';
                                } else {
                                    checkout = false;
                                    method_request = 'post';
                                }

                                $.each(radio, function(id, elm) {
                                    params.push({name: elm.name, value: elm.value});
                                });

                                params.push({name: 'price_ids[' + mswidget.group_id + ']', value: delivery.price_id});

                                if (!checkout) {
                                    params.push({name: 'shipping_ids[' + mswidget.group_id + ']', value: mswidget.shipping_id});
                                }

                                if (delivery.pickuppoint_id) {
                                    params.push({name: 'pickuppoint_ids[' + mswidget.group_id + ']', value: delivery.pickuppoint_id});
                                } else {
                                    params.push({name: 'pickuppoint_ids[' + mswidget.group_id + ']', value: 0});
                                }

                                url = fn_url(mswidget.url);

                                for (i in params) {
                                    url += '&' + params[i]['name'] + '=' + escape(params[i]['value']);
                                }

                                $.ceAjax('request', url, {
                                    result_ids: 'shipping_rates_list,checkout_info_summary_*,checkout_info_order_info_*,shipping_estimation',
                                    method: method_request,
                                    full_render: true
                                });

                            } else {

                                var url = 'order_management.update_shipping?shipping_id=' + mswidget.shipping_id;
                                url += '&price_id=' + delivery.price_id;

                                if (delivery.pickuppoint_id) {
                                    url += '&pickuppoint_id=' + delivery.pickuppoint_id;
                                } else {
                                    url += '&pickuppoint_id=0';
                                }

                                if (typeof(supplier_id) != 'undefined') {
                                    url += '&supplier_id=' + supplier_id;
                                }

                                url = fn_url(url);

                                $.ceAjax('request', url, {
                                    result_ids: result_ids
                                });
                            }

                            mswidget.cartWidget.close();
                        }
                    },

                    //завершение загрузки корзинного виджета
                    'onLoad': function () {

                        $(document).on('click', 'input[name="multiship"]', function () {
                            mswidget.group_id = $(this).data('group-id');
                            mswidget.shipping_id = $(this).data('shipping-id');
                            mswidget.width = $(this).data('width');
                            mswidget.height = $(this).data('height');
                            mswidget.length = $(this).data('length');
                            mswidget.city = $(this).data('city');
                            mswidget.url = $(this).data('url');
                        })

                        $(document).on('click', 'a[name="multiship"]', function () {
                            mswidget.group_id = $(this).data('group-id');
                            mswidget.shipping_id = $(this).data('shipping-id');
                            mswidget.width = $(this).data('width');
                            mswidget.height = $(this).data('height');
                            mswidget.length = $(this).data('length');
                            mswidget.city = $(this).data('city');
                            mswidget.url = $(this).data('url');
                        })
                    }
                })
            })
        }
    })

}(Tygh, Tygh.$));
