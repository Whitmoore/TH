(function($){

    var methods = {

        init: function() {
            return this.each(function() {
                var elm = $(this);
                var id = elm.prop('id');
                if (typeof(elm.data('caPairId')) != 'undefined') {
                    if (typeof(elm.data('caTriggerIds')) != 'undefined') {
                        var tr_ids = elm.data('caTriggerIds').split(',');
                        $.each(tr_ids, function(name, value) {
                            if ($('#' + value).length) {
                                $('#' + value).one("click", function() {
                                    elm.html('<div class="ty-carousel-loading-box"></div>');
                                    $.ceAjax('request', fn_url('development.load_image?id=' + elm.data('caPairId') + '&el_id=' + id), {
                                        caching: true,
                                        result_ids: id,
                                        hidden: true
                                    });
                                });
                            }
                        });
                    } else {
                        elm.html('<div class="ty-carousel-loading-box"></div>');
                        $.ceAjax('request', fn_url('development.load_image?id=' + elm.data('caPairId') + '&el_id=' + id), {
                            caching: true,
                            result_ids: id,
                            hidden: true
                        });
                    }
                }
            });
        }
    };

    $.fn.ceProductImageLoader = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('ty.carouselimageloader: method ' +  method + ' does not exist');
        }
    };
})($);