{hook name="checkout:payment_method_check"}
    {if $order_id}
        {$url = "orders.details?order_id=`$order_id`"}
        {$result_ids = "elm_payments_list"}
        <script type="text/javascript">
        (function(_, $) {
            $(_.doc).on('click', '.cm-select-payment', function() {
                var self = $(this);

                $.ceAjax('request', fn_url('{$url}&payment_id=' + self.val()), {
                    result_ids: '{$result_ids}',
                    full_render: true
                });
            });

            $(_.doc).ready(function() {
                $('.payments-form').each(function(index, form) {
                    $.ceEvent('on', 'ce.formpost_' + $(form).attr('name'), function() {
                        $('#ajax_overlay').show();
                        $('#ajax_loading_box').html('<span class="ty-ajax-loading-box-with__text-wrapper">{__("placing_order")}</span>').addClass('ty-ajax-loading-box_text_block');
                        $.toggleStatusBox('show');
                    });
                });
            });
        }(Tygh, Tygh.$));
        </script>
    {else}
    <script type="text/javascript">
    (function(_, $) {
        $(_.doc).on('click', '.cm-select-payment', function() {
            fn_process_checkout($(this));
        });
    }(Tygh, Tygh.$));
    </script>
    {/if}
{/hook}

{if $order_id}
    <form name="payments_form_{$tab_id}" action="{""|fn_url}" method="post" class="payments-form">
    <input type="hidden" name="payment_id" value="{$payment_id}" />
    <input type="hidden" name="result_ids" value="{$result_ids}" />
    <input type="hidden" name="order_id" value="{$order_id}" />
{else}
    <div class="ty-checkout__billing-options ty-notab">
{/if}

{$payments = $payment_methods}
{*if $payments|count == 1*}
{if false}
    {assign var="payment" value=$payments|reset}

    {if $payment.template}
        {capture name="payment_template"}

            <div class="ty-payments-list__item">
                <div class="ty-payments-list__item-group">
                    <div class="ty-payments-list__item-title-block">
                        <label for="payment_{$payment.payment_id}" class="ty-payments-list__item-title">
                            {if $payment.image}
                                {include file="common/image.tpl" obj_id=$payment.payment_id images=$payment.image image_width="100" image_height="35" keep_transparent=true}
                            {/if}
                            {$payment.payment}
                        </label>
                        {if $payment.description}<div class="ty-payments-list__item-description">{$payment.description}</div>{/if}
                    </div>
                </div>
            </div>

            {include file=$payment.template card_id=$payment.payment_id}
        {/capture}
    {/if}

    {if $payment.template && $smarty.capture.payment_template|trim != ""}
        <div class="clearfix">
            {$payment.instructions nofilter}
            {$smarty.capture.payment_template nofilter}
        </div>
    {else}
        {include file="views/checkout/components/payments/payments_list.tpl" payments=[$payment]}
    {/if}

{else}
    {include file="views/checkout/components/payments/payments_list.tpl"}
{/if}

{if $order_id}
    {include file="views/checkout/components/customer_notes.tpl"}

    <div class="ty-checkout-buttons">
        {if $payment_method.params.button}
            {$payment_method.params.button}
        {else}
            <div class="ty-repay-button">
                    {include file="buttons/place_order.tpl" but_text=__("repay_order") but_name="dispatch[orders.repay]" but_role="big"}
            </div>
        {/if}
    </div>
    </form>

{else}
    {include file="views/checkout/components/terms_and_conditions.tpl" suffix=$tab_id}

    {assign var="show_checkout_button" value=false}
    {foreach from=$payments item="payment"}
        {if $payment_id == $payment.payment_id && $checkout_buttons[$payment_id]}
            {assign var="show_checkout_button" value=true}
        {/if}
    {/foreach}

    {if $auth.act_as_user}
        <div class="ty-control-group">
            <input type="checkbox" id="skip_payment" name="skip_payment" value="Y" class="checkbox" />
            <label for="skip_payment">{__("skip_payment")}</label>
        </div>
    {/if}

    {include file="views/checkout/components/customer_notes.tpl"}
    {if !$auth.user_id && !$cart.user_data.possible_user.user_id}
    <div class="ty-control-group">
        <input type="checkbox" id="create_profile" name="create_profile" value="Y" class="checkbox" checked="checked"/>
        <label for="create_profile">{__("create_profile_checkout_text")}</label>
    </div>
    {/if}
    {hook name="checkout:extra_payment_info"}
    {/hook}
    </div>
{/if}

<div class="processor-buttons hidden"></div>
