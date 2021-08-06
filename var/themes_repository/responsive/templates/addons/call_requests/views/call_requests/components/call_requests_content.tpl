<form id="form_{$id}" action="{""|fn_url}" method="post" class="cm-ajax {if $is_product}cm-autocomplete-form{/if} cm-label-placeholder ty-call-request-form ty-mini-form-mode">
<input type="hidden" name="redirect_url" value="{$config.current_url}" />
<div id="product_data_block"></div>

{if $is_product}
    <div id="call_request_product_block">
    {if $product}
        <input type="hidden" name="call_data[product_id]" value="{$product.product_id}" />
        <div class="ty-cr-product-info-container">
            <div class="ty-cr-product-info-image">
                {include file="common/image.tpl" images=$product.main_pair image_width=$settings.Thumbnails.product_cart_thumbnail_width image_height=$settings.Thumbnails.product_cart_thumbnail_height}
            </div>
            <div class="ty-cr-product-info-header">
                <div class="ty-product-block-title">{$product.product}</div>
            </div>
        </div>
    {/if}
    </div>

    <div class="ty-cr-information">
        {__("call_requests.information", ['[working_hours]' => __("phone_working_hours")])}
    </div>

    <div class="ty-control-group">
        <input id="call_data_{$id}_name" size="50" class="ty-input-text-full" placeholder="{__("firstname_lastname")}" type="text" name="call_data[name]" value="{if $smarty.session.auth.user_data.firstname || $smarty.session.auth.user_data.lastname}{$smarty.session.auth.user_data.firstname} {$smarty.session.auth.user_data.lastname}{/if}" />
        <label class="ty-control-group__title" for="call_data_{$id}_name">{__("firstname_lastname")}</label>
    </div>

    <div class="ty-control-group">
        <input id="call_data_{$id}_phone" class="ty-input-text-full cm-cr-mask-phone" placeholder="{__("phone")}" size="50" type="text" name="call_data[phone]" value="{$smarty.session.auth.user_data.phone}" />
        <label for="call_data_{$id}_phone" class="ty-control-group__title cm-required">{__("phone")}</label>
    </div>


    <div class="ty-control-group">
        <input id="call_data_{$id}_email" class="ty-input-text-full" size="50" placeholder="{__("email")}" type="text" name="call_data[email]" value="{$smarty.session.auth.user_data.email}" />
        <label for="call_data_{$id}_email" class="ty-control-group__title cm-email">{__("email")}</label>
    </div>

    <div class="cr-popup-error-box">
        <div class="hidden cm-cr-error-box help-inline">
            <p>{__("call_requests.enter_phone_or_email_text")}</p>
        </div>
    </div>

    {*

        <div class="ty-control-group">
            <label for="call_data_{$id}_convenient_time" class="ty-control-group__title">{__("call_requests.convenient_time")}</label>
            <input id="call_data_{$id}_convenient_time" class="ty-input-text cm-cr-mask-time" size="6" type="text" name="call_data[time_from]" value="" placeholder="09:00" /> -
            <input id="call_data_{$id}_convenient_time" class="ty-input-text cm-cr-mask-time" size="6" type="text" name="call_data[time_to]" value="" placeholder="20:00" />
        </div>

    *}

    <div class="ty-control-group">
        <input type="hidden" data-autocompletetype="country_code" name="call_data[country]" value="{$smarty.session.auth.user_data.s_country}" />
        <input type="hidden" data-autocompletetype="city_id" name="call_data[city_id]" value="{$smarty.session.auth.user_data.s_city_id}" />
        <input type="hidden" data-autocompletetype="city_id_type" name="call_data[city_id_type]" value="{$smarty.session.auth.user_data.s_city_id_type}" />
        <input type="hidden" data-autocompletetype="state" name="call_data[state]" value="{$smarty.session.auth.user_data.s_state}" />
        <input type="hidden" data-autocompletetype="state_raw" name="call_data[state_raw]" value="{$smarty.session.auth.user_data.s_state_raw}" />
        <input  data-autocompletetype="city" id="call_data_{$id}_city" class="ty-input-text-full" size="50" type="text" name="call_data[city]" value="{$smarty.session.auth.user_data.s_city}" />
        <label for="call_data_{$id}_city" class="ty-control-group__title">{__("city")}</label>
    </div>
    <div class="ty-control-group">
        <input data-autocompletetype="street-address" id="call_data_{$id}_address" class="ty-input-text-full" size="50" type="text" name="call_data[address]" value="{$smarty.session.auth.user_data.s_address}" />
        <label for="call_data_{$id}_address" class="ty-control-group__title">{__("address")}</label>
    </div>
    {$button_text = __("buy_now_cr")}
{else}
    <div class="ty-order-call_back">
        <div class="ty-call-back-info">
            {__("call_requests_order_call_back", ['[working_hours]' => __("phone_working_hours")])}
        </div>
        <div class="ty-control-group">
            <input id="call_data_{$id}_name" size="30" class="ty-input-text-full" placeholder="{__("your_name")}" type="text" name="call_data[name]" value="{if $smarty.session.auth.user_data.firstname || $smarty.session.auth.user_data.lastname}{$smarty.session.auth.user_data.firstname} {$smarty.session.auth.user_data.lastname}{/if}" />
            <label class="ty-control-group__title" for="call_data_{$id}_name">{__("your_name")}</label>
        </div>
        <div class="ty-control-group">
            <input id="call_data_{$id}_phone" class="ty-input-text-full cm-cr-mask-phone" placeholder="{__("phone")}" size="30" type="tel" name="call_data[phone]" value="{$smarty.session.auth.user_data.phone}" />
            <label for="call_data_{$id}_phone" class="ty-control-group__title cm-required">{__("phone")}</label>
        </div>
    </div>
    {$button_text = __("send")}
{/if}
{include file="common/image_verification.tpl" option="call_request" align="left"}

<div class="buttons-container">
    {include file="buttons/button.tpl" but_name="dispatch[call_requests.request]" but_text=$button_text but_role="submit" but_meta="ty-btn__primary ty-btn__big ty-btn cm-dialog-closer" but_onclick="fn_send_call_request(`$product.product_id`);"}
</div>

</form>
