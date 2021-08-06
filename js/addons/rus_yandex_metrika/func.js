(function(_, $) {
    $(document).on('click', 'button[type=submit][name^="dispatch[checkout.add"]', function() {
        $.ceEvent('one', 'ce.formajaxpost_' + $(this).parents('form').prop('name'), function(form, elm) {
            if (_.yandex_metrika.settings.collect_stats_for_goals.basket == 'Y') {
                window['yaCounter' + _.yandex_metrika.settings.id].reachGoal('basket', {});
            }
        });
    });

    $(document).on('click', '.cm-submit[id^="button_wishlist"]', function() {
        $.ceEvent('one', 'ce.formajaxpost_' + $(this).parents('form').prop('name'), function(form, elm) {
            if (_.yandex_metrika.settings.collect_stats_for_goals.wishlist == 'Y') {
                window['yaCounter' + _.yandex_metrika.settings.id].reachGoal('wishlist', {});
            }
        });
    });

    $(document).on('click', 'a[id^=opener_call_request]', function() {
        if (_.yandex_metrika.settings.collect_stats_for_goals.buy_with_one_click_form_opened == 'Y') {
            window['yaCounter' + _.yandex_metrika.settings.id].reachGoal('buy_with_one_click_form_opened', {});
        }
    });

    $.ceEvent('on', 'ce.formajaxpost_call_requests_form', function(form, elm) {
        if (_.yandex_metrika.settings.collect_stats_for_goals.call_request == 'Y') {
            window['yaCounter' + _.yandex_metrika.settings.id].reachGoal('call_request', {});
        }
    });
}(Tygh, jQuery));
