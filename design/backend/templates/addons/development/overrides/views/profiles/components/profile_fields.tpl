{if $profile_fields.$section}

{if !$nothing_extra}
    {include file="common/subheader.tpl" title=$title}
{/if}

{if $shipping_flag}
    <div class="shipping-flag">
        <input class="hidden" id="elm_ship_to_another" type="checkbox" name="ship_to_another" value="1" {if $ship_to_another}checked="checked"{/if} />

        <span class="shipping-flag-title">
            {if $section == "S"}
                {__("shipping_same_as_billing")}
            {else}
                {__("text_billing_same_with_shipping")}
            {/if}
        </span>

        <label class="radio inline">
            <input class="cm-switch-availability cm-switch-visibility cm-switch-inverse " type="radio" name="ship_to_another" value="0" id="sw_{$body_id}_suffix_yes" {if !$ship_to_another}checked="checked"{/if} />
            {__("yes")}
        </label>

        <label class="radio inline">
            <input class=" cm-switch-availability cm-switch-visibility" type="radio" name="ship_to_another" value="1" id="sw_{$body_id}_suffix_no" {if $ship_to_another}checked="checked"{/if} />
            {__("no")}
        </label>
    </div>

{elseif $section == "S"}
    {assign var="ship_to_another" value=true}
    <input type="hidden" name="ship_to_another" value="1" />
{/if}

{if $shipping_flag && !$ship_to_another}
    {assign var="disabled_param" value="disabled=\"disabled\""}
    {assign var="hide_fields" value=true}
{else}
    {assign var="disabled_param" value=""}
{/if}

<div class="clearfix">
{if $body_id}
    <div id="{$body_id}" class="{if $hide_fields}hidden{/if}">
{/if}

{if $location == "checkout" && $auth.user_id && $settings.General.user_multiple_profiles == "Y" && ($section == "B" || $section == "S")} {* Select user profile *}
<div class="control-group">
    <label class="control-label" for="elm_profile_id">{__("select_profile")}:</label>
    <div class="controls">
    <select name="profile_id" id="elm_profile_id" onchange="Tygh.$.ceAjax('request', '{"checkout.checkout"|fn_url:'C':'rel' nofilter}', {$ldelim}result_ids: 'checkout_steps, cart_items, checkout_totals', 'user_data[profile_id]': this.value, 'update_step': '{$update_step}', 'edit_steps[]': '{$update_step}', 'ship_to_another': '{$cart.ship_to_another}'{$rdelim});" class="select-expanded">
    {*if !$skip_create}
        <option selected="selected" value="0">-&nbsp;{__("create_profile")}&nbsp;-</option>
    {/if*}
    {foreach from=$user_profiles item="user_profile"}
        <option value="{$user_profile.profile_id}" {if $cart.profile_id == $user_profile.profile_id}selected="selected"{/if}>{$user_profile.profile_name}</option>
    {/foreach}
    </select>
    </div>
    {if $cart.user_data.profile_id && $cart.user_data.profile_type != "P"}
        <a class="cm-post {if $use_ajax}cm-ajax{/if}" href="{"profiles.delete_profile?profile_id=`$cart.profile_id`"|fn_url}" data-ca-target-id="checkout_steps,cart_items,checkout_totals">{__("delete")}</a>
    {/if}
</div>
{/if}

{foreach from=$profile_fields.$section item=field}
{if $field.field_name && $field.is_default == "Y"}
    {assign var="data_name" value="user_data"}
    {assign var="data_id" value=$field.field_name}
    {assign var="value" value=$user_data.$data_id}
{else}
    {assign var="data_name" value="user_data[fields]"}
    {assign var="data_id" value=$field.field_id}
    {assign var="value" value=$user_data.fields.$data_id}
{/if}

<div class="control-group">
    <label for="{$id_prefix}elm_{$field.field_id}" class="control-label cm-profile-field {if $field.required == "Y"}{*cm-required*}{/if}{if $field.field_type == "P"} cm-phone{/if}{if $field.field_type == "Z"} cm-zipcode{/if}{if $field.field_type == "E"} cm-email{/if} {if $field.field_type == "Z"}{if $section == "S"}cm-location-shipping{else}cm-location-billing{/if}{/if}">{$field.description}:</label>

    <div class="controls">

    {if $field.field_type == "A"}  {* State selectbox *}

        {$_country = $settings.General.default_country}
        {$_state = $value}

        <select {if $field.autocomplete_type}data-autocompletetype="{$field.autocomplete_type}"{/if} class="cm-state {if $section == "S"}cm-location-shipping{else}cm-location-billing{/if}" id="{$id_prefix}elm_{$field.field_id}" name="{$data_name}[{$data_id}]" {$disabled_param nofilter}>
            <option value="">- {__("select_state")} -</option>
            {if $states && $states.$_country}
                {foreach from=$states.$_country item=state}
                    <option {if $_state == $state.code}selected="selected"{/if} value="{$state.code}">{$state.state}</option>
                {/foreach}
            {/if}
        </select>
        <input type="hidden" {if $field.autocomplete_type}data-autocompletetype="state_raw"{/if} name="{$data_name}[{$data_id}_raw]" value="" />
        <input {if $field.autocomplete_type}data-autocompletetype="{$field.autocomplete_type}"{/if} type="text" id="elm_{$field.field_id}_d" name="{$data_name}[{$data_id}]" size="32" maxlength="64" value="{$_state}" disabled="disabled" class="cm-state {if $section == "S"}cm-location-shipping{else}cm-location-billing{/if} input-large hidden cm-skip-avail-switch" />

    {elseif $field.field_type == "O"}  {* Countries selectbox *}
        {assign var="_country" value=$value}
        <input {if $field.autocomplete_type}data-autocompletetype="country_code"{/if} type="hidden" id="{$id_prefix}elm_{$field.field_id}" name="{$data_name}[{$data_id}]" size="32" maxlength="64" value="{$_country}" class="ty-input-text cm-country {if $section == "S"}cm-location-shipping{else}cm-location-billing{/if}"/>

        <input {if $field.autocomplete_type}data-autocompletetype="{$field.autocomplete_type}"{/if} type="text" id="{$id_prefix}elm_{$field.field_id}_f" name="{$data_name}[{$data_id}_f]" size="32" value="{if $data_id == 's_country' || $data_id == 'b_country'}{$countries.$value}{else}{$value}{/if}" class="ty-input-text {if !$skip_field}{$_class}{else}cm-skip-avail-switch{/if}" {if !$skip_field}{$disabled_param nofilter}{/if} {if $data_id == 's_country' || $data_id == 'b_country'}placeholder="{__('just_name')}" onblur="" onkeydown=""{/if} />

        {*<select {if $field.autocomplete_type}data-autocompletetype="{$field.autocomplete_type}"{/if} id="{$id_prefix}elm_{$field.field_id}_f" class="cm-country {if $section == "S"}cm-location-shipping{else}cm-location-billing{/if}" name="{$data_name}[{$data_id}]" {$disabled_param nofilter}>
            {hook name="profiles:country_selectbox_items"}
            <option value="">- {__("select_country")} -</option>
            {foreach from=$countries item="country" key="code"}
            <option {if $_country == $code}selected="selected"{/if} value="{$code}">{$country}</option>
            {/foreach}
            {/hook}
        </select>*}

    {elseif $field.field_type == "C"}  {* Checkbox *}
        <input type="hidden" name="{$data_name}[{$data_id}]" value="N" {$disabled_param nofilter} />
        <label class="checkbox">
        <input type="checkbox" id="{$id_prefix}elm_{$field.field_id}" name="{$data_name}[{$data_id}]" value="Y" {if $value == "Y"}checked="checked"{/if} {$disabled_param nofilter} /></label>

    {elseif $field.field_type == "T"}  {* Textarea *}
        <textarea {if $field.autocomplete_type}data-autocompletetype="{$field.autocomplete_type}"{/if} class="input-large" id="{$id_prefix}elm_{$field.field_id}" name="{$data_name}[{$data_id}]" cols="32" rows="3" {$disabled_param nofilter}>{$value}</textarea>

    {elseif $field.field_type == "D"}  {* Date *}
        {include file="common/calendar.tpl" date_id="elm_`$field.field_id`" date_name="`$data_name`[`$data_id`]" date_val=$value extra=$disabled_param}

    {elseif $field.field_type == "S"}  {* Selectbox *}
        <select {if $field.autocomplete_type}data-autocompletetype="{$field.autocomplete_type}"{/if} id="{$id_prefix}elm_{$field.field_id}" name="{$data_name}[{$data_id}]" {$disabled_param nofilter}>
            {if $field.required != "Y"}
            <option value="">--</option>
            {/if}
            {foreach from=$field.values key=k item=v}
            <option {if $value == $k}selected="selected"{/if} value="{$k}">{$v}</option>
            {/foreach}
        </select>

    {elseif $field.field_type == "R"}  {* Radiogroup *}
        <div class="select-field">
        {foreach from=$field.values key=k item=v name="rfe"}
        <input class="radio" type="radio" id="{$id_prefix}elm_{$field.field_id}_{$k}" name="{$data_name}[{$data_id}]" value="{$k}" {if (!$value && $smarty.foreach.rfe.first) || $value == $k}checked="checked"{/if} {$disabled_param nofilter} /><label for="{$id_prefix}elm_{$field.field_id}_{$k}">{$v}</label>
        {/foreach}
        </div>

    {elseif $field.field_type == "N"}  {* Address type *}
        <input class="radio valign {if !$skip_field}{$_class}{else}cm-skip-avail-switch{/if}" type="radio" id="{$id_prefix}elm_{$field.field_id}_residential" name="{$data_name}[{$data_id}]" value="residential" {if !$value || $value == "residential"}checked="checked"{/if} {if !$skip_field}{$disabled_param nofilter}{/if} /><span class="radio">{__("address_residential")}</span>
        <input class="radio valign {if !$skip_field}{$_class}{else}cm-skip-avail-switch{/if}" type="radio" id="{$id_prefix}elm_{$field.field_id}_commercial" name="{$data_name}[{$data_id}]" value="commercial" {if $value == "commercial"}checked="checked"{/if} {if !$skip_field}{$disabled_param nofilter}{/if} /><span class="radio">{__("address_commercial")}</span>

    {else}  {* Simple input *}
        {if $data_id == 's_city' || $data_id == 'b_city'}
            {$city_id = "`$data_id`_id"}
            {$city_id_type = "`$data_id`_id_type"}
            <input type="hidden" {if $field.autocomplete_type}data-autocompletetype="city_id"{/if} name="{$data_name}[{$city_id}]" value="{$user_data.$city_id}" {if $is_checkout}data-submitonchange="true"{/if} />
            <input type="hidden" {if $field.autocomplete_type}data-autocompletetype="city_id_type"{/if} name="{$data_name}[{$city_id_type}]" value="{$user_data.$city_id_type}" />
        {/if}
        <input {if $field.autocomplete_type}data-autocompletetype="{$field.autocomplete_type}"{/if} type="text" id="{$id_prefix}elm_{$field.field_id}" name="{$data_name}[{$data_id}]" size="32" value="{$value}" class="input-large{if $field.class == 'shipping-phone' || $field.class == 'billing-phone'} cm-cr-mask-phone{/if}" {$disabled_param nofilter}  {if $data_id == 's_city' || $data_id == 'b_city'}placeholder="{__('just_name')}" onblur="fn_check_city($(this), false);" onchange="fn_city_change($(this).closest('.cm-autocomplete-form'));" onkeydown="fn_city_keydown($(this).closest('.cm-autocomplete-form'));"{/if}/>
    {/if}
    </div>
</div>
{/foreach}
<script type="text/javascript" class="cm-ajax-force">
(function(_, $) {
    $('.cm-autocomplete-form').each(function(){
        fn_init_autocomplete($(this));
    });
}(Tygh, Tygh.$));

</script>
{if $body_id}
</div>
{/if}
</div>

{/if}
