(function(_, $){

    $.ceEvent('on', 'ce.commoninit', function(context) {

        // inputmask
        $('.cm-cr-mask-time').mask('00:00');

        // inputmask-multi
        var mask_elements = $('.cm-cr-mask-phone');
        // $('.cm-cr-mask-phone').mask('+7(Z00)000-00-00', {
        //     translation: {
        //         'Z': {
        //             pattern: /9/
        //         }
        //     },
        //     clearIfNotMatch: true
        // });
        if (mask_elements.length && _.call_requests_phone_masks_list) {
            var maskList = $.masksSort(_.call_requests_phone_masks_list, ['#'], /[0-9]|#/, "mask");
            var maskOpts = {
                inputmask: {
                    definitions: {
                        '#': {
                            validator: "[0-9]",
                            cardinality: 1
                        }
                    },
                    showMaskOnHover: false,
                    autoUnmask: false,
                },
                match: /[0-9]/,
                replace: '#',
                list: maskList,
                listKey: "mask"
            };

            mask_elements.each(function(){
                $(this).inputmasks(maskOpts);
            });
        }

    });

    $.ceEvent('on', 'ce.formpre_call_requests_form', function(form, elm) {
        var val_email = form.find('[name="call_data[email]"]').val(),
            val_phone = form.find('[name="call_data[phone]"]').val(),
            allow = !!(val_email || val_phone),
            error_box = form.find('.cm-cr-error-box'),
            dlg = $.ceDialog('get_last');

        if (!allow) {
            error_box.toggle(!allow);
            dlg.ceDialog('reload');
        }

        return allow;
    });

})(Tygh, Tygh.$);
