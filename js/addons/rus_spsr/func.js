(function(_, $) {
    $("#city").autocomplete({
        source: function( request, response ) {
            var check_country;
            check_country = "RU";
            getSpsrCities(check_country, request, response);
        }
    });

    function getSpsrCities(check_country, request, response) {

        $.ceAjax('request', fn_url('city_spsr.autocomplete_city?q=' + encodeURIComponent(request.term) + '&check_country=' + check_country), {
            callback: function(data) {
                response(data.autocomplete);
            }
        });
    }

    $(document).ready(function(){
        $('#spsr_get_city_link').on('click', fn_get_spsr_city);
    });

    function fn_get_spsr_city() {
        var city = $('#city').val();

        $.ceAjax('request', fn_url("city_spsr.spsr_get_city_data"), {
            data: {
                var_city: city,
                loc: 'shipping_settings',
                result_ids: 'spsr_city_div',
            },
        });
    }

}(Tygh, Tygh.$));
