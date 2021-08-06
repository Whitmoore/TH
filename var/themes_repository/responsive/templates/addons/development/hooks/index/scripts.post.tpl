{if $app.antibot->getDriver()|get_class == "Tygh\Addons\Recaptcha\RecaptchaDriver"}
<script type="text/javascript">
    (function (_, $) {
        _.tr({
            error_validator_recaptcha: '{__("error_validator_recaptcha")|escape:"javascript"}'
        });

        $.extend(_, {
            recaptcha_settings: {
                site_key: '{$addons.development.recaptcha_site_key|escape:javascript nofilter}',
                theme: '{$addons.development.recaptcha_theme|escape:javascript nofilter}',
                type: '{$addons.development.recaptcha_type|escape:javascript nofilter}',
                size: '{$addons.development.recaptcha_size|escape:javascript nofilter}'
            }
        });
    }(Tygh, Tygh.$));

    // Proxies event handler to class method
    window.onRecaptchaLoaded = function () {
        Tygh.onRecaptchaLoaded();
    };
</script>
{script src="js/addons/development/recaptcha.js"}
<script src="https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoaded&render=explicit"></script>
{/if}
{script src="js/addons/development/jquery.selectbox-0.2.js"}
{script src="js/addons/development/jquery.mCustomScrollbar.concat.min.js"}

{script src="js/addons/development/jquery.kladr.min.js"}
{*script src="js/addons/development/core.js"}
{script src="js/addons/development/kladr.js"}
{script src="js/addons/development/kladr_zip.js"*}

{script src="js/addons/development/func.js"}

{script src="js/addons/development/jquery.roundabout.min.js"}
{script src="js/addons/development/jquery.roundabout-shapes.min.js"}

<script type="text/javascript">
var error_validator_city = '{__("error_validator_city")|escape:"javascript"}';
var change_pickup_location = '{__("change_pickup_location")|escape:"javascript"}';
var images_dir = '{$images_dir}';

{literal}

    function fn_rf_slide_in()
    {
        $('#rf_block').animate({width:'toggle'}, 350);
        $('#rf_slide_in').hide();
        $('#rf_slide_out').show();
        Tygh.$.ceAjax('request', fn_url('racket_finder.show_add'), {
            method: 'get',
            hidden: true
        });
    }
    function fn_rf_slide_out()
    {
        $('#rf_block').animate({width:'toggle'}, 350);
        $('#rf_slide_out').hide();
        $('#rf_slide_in').show();
        Tygh.$.ceAjax('request', fn_url('racket_finder.hide_add'), {
            method: 'get',
            hidden: true
        });
    }

    function fn_mouseleave_tooltip(trigger)
    {
        trigger.parents('.ty-top-mine__submenu-col').removeClass('ty-top-mine__submenu-col-hover');
        trigger.parents('.ty-menu__item-parent .ty-menu__item_full').each(function(){
            $(this).removeClass('is-hover-menu');
            if (!$(this).hasClass('is-hover')) {
                fn_hide_top_menu($(this));
            }
        });
    }

    function fn_mouseon_tooltip(trigger)
    {
        trigger.parents('.ty-top-mine__submenu-col').addClass('ty-top-mine__submenu-col-hover');
        trigger.parents('.ty-menu__item-parent .ty-menu__item_full').each(function(){
            $(this).addClass('is-hover-menu');
        });
    }

    function fn_stick_element(sticky, footer)
    {
        if (sticky.length > 0 && footer.length > 0 && (typeof(sticky.data('minStickyWidth')) == 'undefined' || $(window).width() >= sticky.data('minStickyWidth'))) {
            var top_margin = $('.tygh-top-panel').outerHeight(true) + 1;
            var bottom_margin = 20;
            var element_offset = sticky.parent().offset();
            if (!sticky.hasClass('sticky') && $(window).scrollTop() + top_margin >= sticky.offset().top && sticky.outerHeight(true) < footer.offset().top - sticky.offset().top - 50) {
                sticky.data('init_position', sticky.offset().top)
                sticky.parent().css('right', '0');
                sticky.addClass('sticky');
            }
            if (sticky.hasClass('sticky')) {
                sticky.css('left', element_offset.left - $(window).scrollLeft() + 'px');
                if (sticky.outerHeight(true) > footer.offset().top - sticky.data('init_position')) {
                    sticky.removeClass('sticky');
                } else if (sticky.outerHeight(true) + top_margin - bottom_margin > footer.offset().top - $(window).scrollTop()) {
                    sticky.css('top', footer.offset().top - sticky.outerHeight(true) - $(window).scrollTop() + bottom_margin + 'px');
                } else {
                    if ($(window).scrollTop() + top_margin < sticky.data('init_position')) {
                        sticky.css('top', sticky.data('init_position') - $(window).scrollTop() + 'px');
                    } else {
                        sticky.css('top', top_margin + 'px');
                    }
                }
            }
        }
    }
    function fn_fix_width()
    {
        width = $(window).width() < 1200 ? "1200px" : "100%";
        $('.tygh-top-panel').css({"left": - $(this).scrollLeft() + "px", "width": width});
        $('#tygh_container').css({"width": width});
    }
    function fn_hide_top_menu(top_menu)
    {
        if (!top_menu.find('.tooltip-shown').length) {
            setTimeout(function() {
                if (!top_menu.hasClass('is-hover')) {
                    top_menu.find('.ty-menu__submenu-items').hide();
                }
            }, 150);
        }
    }

    function fn_init_dropdown(obj)
    {
        obj.hover(function(e){
            $(this).addClass('is-hover');
            var trigger = $(this);
            setTimeout(function() {
                var submenu =  trigger.find('.cm-hover-dropdown-submenu');
                if (trigger.hasClass('is-hover') && submenu.length) {
                    submenu.show();
                    if (trigger.data('bgShadow')) {
                        $('#navigation_shadow').show();
                    }
                }
            }, 150);
        }, function(e){
            $(this).removeClass('is-hover');
            var trigger = $(this);
            if (!$(this).find('.tooltip-shown').length) {
                setTimeout(function() {
                    var submenu =  trigger.find('.cm-hover-dropdown-submenu');
                    if (!trigger.hasClass('is-hover') && submenu.length) {
                        submenu.hide();
                        if (trigger.data('bgShadow')) {
                            $('#navigation_shadow').hide();
                        }
                    }
                }, 150);
            }
        });
    }

    function fn_ymaps_hide_office()
    {
        $('#office_list').show();
        $('#office_details_list').hide();
        $('#office_details_list_block').children().hide();

        if (typeof(ymaps) != 'undefined') {
            plcmks = clusterer.getGeoObjects();
            for (var i = 0, l = plcmks.length; i < l; i++) {
                plcmks[i].options.set({'iconImageSize': [44, 44], 'iconImageOffset': [-15, -27]});
            }
        }
    }

    function fn_ymaps_show_office(id)
    {
        $('#office_list').hide();
        $('#office_details_list').show();
        $('#office_details_list_block').children().hide();
        $('#office_' + id + '_details').show();

        if (typeof(ymaps) != 'undefined') {
            myMap.panTo([$('#office_' + id).data('ymapsCoordY'), $('#office_' + id).data('ymapsCoordX')], {'checkZoomRange': true}).then(function(){
                myMap.setZoom(17, { smooth: 0 });

                plcmks = clusterer.getGeoObjects();
                for (var i = 0, l = plcmks.length; i < l; i++) {
                    if (plcmks[i].properties.get('id') == id) {
                        plcmks[i].options.set({'iconImageSize': [84, 88], 'iconImageOffset': [-32, -88]});
                    } else {
                        plcmks[i].options.set({'iconImageSize': [44, 44], 'iconImageOffset': [-15, -27]});
                    }
                }
            });
        }
    }

    function fn_init_placeholder(context)
    {
        $('.cm-label-placeholder :input', context).each(function(){

            // $(this).on('animationstart', function(event){
            //     if ($(this).attr('id') == 's_city') {
            //         fn_print_r(event.originalEvent.animationName);
            //     }
            //     if (event.originalEvent.animationName == 'onAutoFillStart') {
            //         $(this).addClass('cm-input-full');
            //     } else {
            //         $(this).removeClass('cm-input-full');
            //     }
            // });

            if ($(this).val()) {
                $(this).addClass('cm-input-full');
            } else {
                $(this).removeClass('cm-input-full');
            }
            $(this).on('keyup keypress change', function(){
                if ($(this).val()) {
                    $(this).addClass('cm-input-full');
                } else {
                    $(this).removeClass('cm-input-full');
                }
            });
        })
    }

    (function(_, $) {
        $(function() {
            $(document).ready(function() {
                if ($.fn.ceProductImageLoader) {
                    $('.cm-image-loader').ceProductImageLoader();
                }

                if ($('#tygh_main_container').hasClass('touch')) {
                    $('.ty-menu__item-link').click(function(e){
                        var submenu = $(this).parents('.ty-menu__item_full').find('.ty-menu__submenu-items');
                        if (submenu.length) {
                            submenu.slideToggle();
                            e.preventDefault();
                        }
                    });
                }
//                 $('.ty-menu__item-parent .ty-menu__item_full').hover(function(e){
//                     $(this).addClass('is-hover');
//                     var submenu = $(this);
//                     setTimeout(function() {
//                         if (submenu.hasClass('is-hover')) {
//                             submenu.find('.ty-menu__submenu-items').show();
//                         }
//                     }, 150);
//                 }, function(e){
//                     $(this).removeClass('is-hover');
//                     fn_hide_top_menu($(this));
//                 });
                $('.ty-menu__page-items').each(function(){
                    var submenu_width = $(this).find('li.ty-top-mine__submenu-col').length * 250;
                    if (submenu_width > 600) {
                        $(this).css('left', Math.max(0, (($(window).width() - submenu_width) / 2) + $(window).scrollLeft()) + 'px');
                    }
                });
            });

            if (!$('#tygh_main_container').hasClass('touch')) {
                fn_fix_width();
                var footer = $('#tygh_footer');
                $(".ty-sticky").each(function(){
                    fn_stick_element($(this), footer);
                });
                $(window).resize(function() {
                    fn_fix_width();
                    $(".ty-sticky").each(function(){
                        var init_position = 0;
                        fn_stick_element($(this), footer);
                    });
                });
                $(window).scroll(function() {
                    fn_fix_width();
                    $(".ty-sticky").each(function(){
                        var init_position = 0;
                        fn_stick_element($(this), footer);
                    });
                    $('.cm-parallax').each(function(){
                        if ($(window).scrollTop() < $(this).offset().top + $(this).outerHeight() - 115) {
                            $(this).css({ backgroundPosition: 'center '+ (109 - $(window).scrollTop() / $(this).data('speed')) + 'px' });
                        }
                    });
                });
            }

            $('.cm-roundabout').each(function(){
                var roundabout = $(this).find('.cm-roundabout-images');
                var roundabout_description = $(this).find('.cm-roundabout-descriptions');
                roundabout.roundabout({
                    childSelector: "div",
                    duration: 1000,
                    autoplay: true,
                    autoplayDuration: 5000,
                    autoplayPauseOnHover: true,
                });
                roundabout.bind( 'animationStart', function() {
                    roundabout_description.find('.ty-roundabout-item-description').fadeOut('fast');
                    roundabout.find('.ty-roundabout__brand-image').fadeOut('fast');
                });
                roundabout.bind( 'animationEnd', function() {
                    roundabout_description.find($('.ty-roundabout-item-description')[roundabout.roundabout('getChildInFocus')]).stop().fadeIn('fast');
                    roundabout.find($('.ty-roundabout__brand-image')[roundabout.roundabout('getChildInFocus')]).stop().fadeIn('fast');
                });
                roundabout_description.hover(function(e){
                    roundabout.roundabout("stopAutoplay");
                }, function(e){
                    roundabout.roundabout("startAutoplay");
                });
            });

            $('.cm-banner').each(function(){
                $(this).click(function(){
                    $(this).find('.cm-banner-link').click();
                    return false;
                });
            });
            $('.cm-ajax-search-block').hover(function(){
                $(this).addClass('is-hover');
            }, function(){
                $(this).removeClass('is-hover');
            });
            $('.cm-hover-dropdown').each(function(){
                fn_init_dropdown($(this));
            });
            $('.cm-link').each(function(){
                $(this).click(function(){
                    location.href = $(this).data('href');
                });
            });
            $('.cm-ajax-search').each(function(){
                $(this).focus(function(){
                    $('#top_search').show();
                }).blur(function(){
                    if (!$(this).parents('.cm-ajax-search-block').hasClass('is-hover')) {
                        $('#top_search').hide();
                    }
                }).keyup(function(){
                    if ($(this).val().length > 2) {
                        function fn_ajax_search(obj, val)
                        {
                            if (val == obj.val()) {
                                $.ceAjax('request', fn_url('products.ajax_search'), {
                                    method: 'post',
                                    hidden: true,
                                    result_ids: 'top_search',
                                    data: {q: obj.val()}
                                });
                            }
                        }
                        var val = $(this).val();
                        setTimeout(fn_ajax_search, 500, $(this), val);
                    }
                });
            });
            $('.cm-show-form').focus(function(e){
                fn_show_form(e);
            });
            $.ceEvent('on', 'ce.notificationshow', function(notification) {
                $.commonInit(notification);
            });
        });
    }(Tygh, Tygh.$));
{/literal}
</script>
