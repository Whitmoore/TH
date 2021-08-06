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

{script src="js/addons/development/jquery.mobile-1.4.5.min.js"}
{script src="js/addons/development/jquery.kladr.min.js"}

{script src="js/addons/development/func.js"}

<script type="text/javascript">
var error_validator_city = '{__("error_validator_city")|escape:"javascript"}';
var change_pickup_location = '{__("change_pickup_location")|escape:"javascript"}';
var images_dir = '{$images_dir}';

{literal}

    function fn_toggle_ymaps_view()
    {
        $('#view_as_list').toggle();
        $('#view_as_map').toggle();
        $('#map').toggle();
        $('#office_list').toggle();
    }

    function fn_ymaps_hide_office()
    {
        // $('#office_list').show();
        $('#office_details_list').hide();
        $('#office_details_list_block').children().hide();
    }

    function fn_ymaps_show_office(id)
    {
        // $('#office_list').hide();
        $('#office_details_list').show();
        $('#office_details_list_block').children().hide();
        $('#office_' + id + '_details').show();
    }

    function fn_init_placeholder(context)
    {
        $('.cm-label-placeholder :input', context).each(function(){

            $(this).on('animationstart', function(event){
                if (event.originalEvent.animationName == 'onAutoFillStart') {
                    $(this).parent('.ui-input-text').addClass('cm-input-full');
//                 } else if (event.originalEvent.animationName == 'onAutoFillCancel') {
//                     $(this).parent('.ui-input-text').removeClass('cm-input-full');
                }
            });


            if ($(this).val()) {
                $(this).parent('.ui-input-text').addClass('cm-input-full');
            } else {
                $(this).parent('.ui-input-text').removeClass('cm-input-full');
            }
            $(this).on('keyup keypress change', function(){
                if ($(this).val()) {
                    $(this).parent('.ui-input-text').addClass('cm-input-full');
                } else {
                    $(this).parent('.ui-input-text').removeClass('cm-input-full');
                }
            });
        })
    }

    function fn_init_customization_switch()
    {
        $('.cm-customization-switch').not( ".cm-customization-switch-processed" ).each(function(){
            $(this).click(function(){
                $('.cm-customization').each(function(){
                    $(this).toggle();
                });
            });
            $(this).addClass('cm-customization-switch-processed');
        });
    }

    (function(_, $) {
        $(document).ready(function(){
            $( "#left-panel" ).removeClass('hidden');
            $( "#right-panel" ).removeClass('hidden');
            fn_init_placeholder();
        });
        $(document).on( "pagecreate", '#mobile_page', function() {
            $(document).on( "swipeleft swiperight", '#mobile_page', function( e ) {
                if (!$( e.target ).parents('.ty-no-swipe').length) {
                    // We check if there is no open panel on the page because otherwise
                    // a swipe to close the left panel would also open the right panel (and v.v.).
                    // We do this by checking the data that the framework stores on the page element (panel: open).
                    if ( $( ".ui-page-active" ).jqmData( "panel" ) !== "open" ) {
                        if ( e.type === "swiperight" ) {
                            $( "#left-panel" ).panel( "open" );
                        } else if ( e.type === "swipeleft" ) {
                            $( "#right-panel" ).panel( "open" );
                        }
                    }
                }
            });
        });
        $('.cm-ajax-search-block').hover(function(){
            $(this).addClass('is-hover');
        }, function(){
            $(this).removeClass('is-hover');
        });
        $('.cm-link').each(function(){
            $(this).click(function(){
                location.href = $(this).data('href');
            });
        });
        $('.cm-ajax-search').each(function(){
            $(this).focus(function(){
                $('#top_search').removeClass('hidden');
            }).blur(function(){
                if (!$(this).parents('.cm-ajax-search-block').hasClass('is-hover')) {
                    $('#top_search').addClass('hidden');
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
        $.ceEvent('on', 'ce.commoninit', function(context) {
            fn_init_customization_switch();
        });
        $.ceEvent('on', 'ce.notificationshow', function(notification) {
            $('select', notification).each(function(){
                $(this).selectmenu();
            });
            $.commonInit(notification);
        });
    }(Tygh, Tygh.$));
{/literal}
</script>
