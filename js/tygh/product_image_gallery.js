/*
    FIXME: Backward compatibility
    We combined two scrollers here. Now all themes (4.0 & 4.1) work good.
*/

(function(_, $) {

    var ITEMS_COUNT_DEFAULT = 3;
    var scroller_type = 'owlcarousel'; // can be 'owlcarousel' or 'jcarousel'

    var methods = {
        init: function() {
            var container = $(this);
            var params = {
                items_count: container.data('caItemsCount') ? container.data('caItemsCount') : ITEMS_COUNT_DEFAULT,
                items_responsive: container.data('caItemsResponsive') ? true : false,
                hide_navigation_text: container.data('caItemsHideNavigationText') ? true : false,
                gallery_buttons: container.data('caItemsGalleryButtons') ? container.data('caItemsGalleryButtons') : false,
                obj_id: container.data('caItemsId') ? container.data('caItemsId') : false
            };

            if (container.hasClass('jcarousel-skin') || container.parent().hasClass('jcarousel-skin')) {
                scroller_type = 'jcarousel';
            } else {
                scroller_type = 'owlcarousel';
            }

            if (methods.countElms(container) <= params.items_count) {
                container.removeClass('owl-carousel');
            }

            if (methods.countElms(container) > params.items_count || (container.hasClass('jcarousel-skin') && methods.countElms(container) > params.items_count)) {
                if (container.data('owl-carousel') || container.data('jcarousel')) {
                    return true;
                }

                methods.check(container, params);
            }
            
            methods.bind(container);

            return true;
        },

        load: function(container, params) {
            if (scroller_type == 'owlcarousel') {
                container.owlCarousel({
                    items: params.items_count,
                    rewind: true,
                    responsive: params.items_responsive,
                    dots: false,
                    nav: true,
                    margin: 10,
                    obj_id: params.obj_id,
                    gallery_buttons: params.gallery_buttons,
                    navText: params.hide_navigation_text ? ['<div class="ty-arrow-bg"></div>', '<div class="ty-arrow-bg"></div>'] : (params.items_count == 1 ? ['<i class="icon-left-circle ty-icon-left-circle"></i>', '<i class="icon-right-circle ty-icon-right-circle"></i>'] : ['<i class="icon-left-open-thin ty-icon-left-open-thin"></i>', '<i class="icon-right-open-thin ty-icon-right-open-thin"></i>']),
                    theme: params.items_count == 1 ? 'owl-one-theme' : 'owl-more-theme',
                    onInitialized : function(item) {
                        $(item).css({'visibility':'visible', 'position':'relative'});
                        if ($.fn.ceProductImageLoader) {
                            $('.cm-carousel-image-loader', container).ceProductImageLoader();
                        }
                    }
                });
            } else {
                $('li', container).show();
                container.jcarousel({
                    scroll: 1,
                    wrap: 'circular',
                    animation: 'fast',
                    initCallback: $.ceScrollerMethods.init_callback,
                    itemFallbackDimension: params.i_width,
                    item_width: params.i_width,
                    item_height: params.i_height,
                    clip_width: params.c_width,
                    clip_height: params.i_height,
                    buttonNextHTML: '<div><i class="icon-right-open-thin ty-icon-right-open-thin"></i></div>',
                    buttonPrevHTML: '<div><i class="icon-left-open-thin ty-icon-left-open-thin"></i></div>',
                    buttonNextEvent: 'click',
                    buttonPrevEvent: 'click',
                    size: methods.countElms(container)
                });
            }
        },

        check: function(container, params) {
            if (container.data('owl-carousel') || container.data('jcarousel')) {
                return true;
            }
        
            if (!params.i_width || !params.i_height) {
                var t_elm = false;

                if ($('.cm-gallery-item', container).length) {
                    var load = false;

                    // check images are loaded
                    $('.cm-gallery-item', container).each(function() {
                        var elm = $(this);
                        var i_elm = $('img', elm);

                        if (i_elm.length) {
                            if (elm.outerWidth() >= i_elm.width()) {
                                // find first loaded image
                                t_elm = elm;
                                return false;
                            }
                            load = true;
                        }
                    });
                    if (!t_elm) {
                        if (load) {
                            var check_load = function() {
                                methods.check(container, params);
                            }
                            // wait until image is loaded
                            setTimeout(check_load, 500);
                            return false;
                        } else {
                            t_elm = $('.cm-gallery-item:first', container);
                        }
                    }
                } else {
                    t_elm = $('img:first', container);
                }

                params.i_width = t_elm.outerWidth(true);
                params.i_height = t_elm.outerHeight(true);
                params.c_width = params.i_width * params.items_count;

                if (scroller_type == 'owlcarousel') {
                    container.closest('.cm-image-gallery-wrapper').width(params.c_width);
                }
            }

            return methods.load(container, params);
        },

        bind: function(container) {
            container.click(function(e) {
                var jelm = $(e.target);
                var pjelm;

                // Check elm clicking
                if (scroller_type == 'owlcarousel') {
                    var in_elm = jelm.parents('.cm-item-gallery') || jelm.parents('div.cm-thumbnails-mini') ? true : false;
                } else {
                    var in_elm = jelm.parents('li') || jelm.parents('div.cm-thumbnails-mini') ? true : false;
                }
                
                if (in_elm && !jelm.is('img')) { // Check if the object is image or SWF embed object or parent is SWF-container
                    return false;
                }

                if (jelm.hasClass('cm-thumbnails-mini') || (pjelm = jelm.parents('a:first.cm-thumbnails-mini'))) {
                    jelm = (pjelm && pjelm.length) ? pjelm : jelm;

                    if (scroller_type == 'owlcarousel') {
                        var image_box = $('.cm-preview-wrapper:first');
                    } else {
                        var jc_box = $(this).parents('.jcarousel-skin:first');
                        var image_box = (jc_box.length) ? jc_box.parents(':first') : $(this).parents(':first');
                    }

                    $('.cm-image-previewer', image_box).each(function() {
                        if ($(this).hasClass('cm-thumbnails-mini')) {
                            return;
                        }

                        var id = $(this).prop('id');
                        var c_id = jelm.data('caGalleryLargeId');
                        if (id == c_id) {
                            $('.cm-thumbnails-mini', container).removeClass('active');
                            jelm.addClass('active');
                            $(this).show();
                            $('div', $(this)).show(); // Special for Flash containers
                            $('#box_' + id).show();
                        } else {
                            $(this).hide();
                            $('div', $(this)).hide(); // Special for Flash containers
                            $('#box_' + id).hide();
                        }
                    });
                }
            });
        },

        countElms: function(container) {
            if (scroller_type == 'owlcarousel') {
                return $('.cm-gallery-item', container).length;
            } else {
                return $('li', container).length;
            }
        }
    };

    $.fn.ceProductImageGallery = function(method) {

        if (!$().owlCarousel) {
            var gelms = $(this);
            $.getScript('js/lib/owlcarousel/owl.carousel.min.js', function() {
                gelms.ceProductImageGallery();
            });
            return false;
        }

        if (!$().jcarousel) {
            var gelms = $(this);
            $.getScript('js/lib/jcarousel/jquery.jcarousel.js', function() {
                gelms.ceProductImageGallery();
            });
            return false;
        }

        return $(this).each(function(i, elm) {

            // These vars are local for each element
            var errors = {};

            if (methods[method]) {
                return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
            } else if ( typeof method === 'object' || ! method ) {
                return methods.init.apply(this, arguments);
            } else {
                $.error('ty.productimagegallery: method ' +  method + ' does not exist');
            }
        });
    };
})(Tygh, Tygh.$);