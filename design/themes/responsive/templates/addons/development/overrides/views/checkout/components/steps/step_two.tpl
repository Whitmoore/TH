<div class="ty-step__container{if $edit}-active{/if} ty-step-two" data-ct-checkout="billing_shipping_address" id="step_two">
    {if $settings.General.checkout_style != "multi_page"}
        <h3 class="ty-step__title{if $edit}-active{/if}{if $complete && !$edit}-complete{/if} clearfix">
            {*<span class="ty-step__title-left">1{if !$complete || $edit}1{/if}{if $complete && !$edit}<i class="ty-step__title-icon ty-icon-ok"></i>{/if}</span>*}
            {*<i class="ty-step__title-arrow ty-icon-down-micro"></i>*}


            {if $complete && !$edit}
                {hook name="checkout:edit_link"}
                <span class="ty-step__title-right">
                    {include file="buttons/button.tpl" but_meta="cm-ajax" but_href="checkout.checkout?edit_step=step_two&from_step=$edit_step" but_target_id="checkout_*" but_text=__("change") but_role="tool"}
                </span>
                {/hook}
            {/if}

            {hook name="checkout:edit_link_title"}
            {if $complete && !$edit}
                <a class="ty-step__title-txt cm-ajax" href="{"checkout.checkout?edit_step=step_two&from_step=`$edit_step`"|fn_url}" data-ca-target-id="checkout_*">{__("billing_shipping_address")}</a>
            {else}
                <span class="ty-step__title-txt">{__("billing_shipping_address")}</span>
            {/if}
            {/hook}
        </h3>
    {/if}

    <div id="step_two_body" class="ty-step__body{if $edit}-active{/if}{if !$edit} hidden{/if} cm-skip-save-fields">

        {if $smarty.request.profile == "new"}
            {assign var="hide_profile_name" value=false}
        {else}
            {assign var="hide_profile_name" value=true}
        {/if}

        {if $edit}
            <div class="clearfix">
                <div class="checkout__block">
                    {include file="views/profiles/components/multiple_profiles.tpl" show_text=true hide_profile_name=$hide_profile_name hide_profile_delete=true profile_id=$cart.profile_id create_href="checkout.checkout?edit_step=step_two&from_step=$edit_step&profile=new"}
                </div>
            </div>
        {/if}

        {if $settings.General.address_position == "billing_first"}
            {assign var="first_section" value="B"}
            {assign var="first_section_text" value=__("billing_address")}
            {assign var="sec_section" value="S"}
            {assign var="sec_section_text" value=__("shipping_address")}
            {assign var="ship_to_another_text" value=__("text_ship_to_billing")}
            {assign var="body_id" value="sa"}
        {else}
            {assign var="first_section" value="S"}
            {assign var="first_section_text" value=__("shipping_address")}
            {assign var="sec_section" value="B"}
            {assign var="sec_section_text" value=__("billing_address")}
            {assign var="ship_to_another_text" value=__("text_billing_same_with_shipping")}
            {assign var="body_id" value="ba"}
        {/if}

        {if $edit}
            {if $profile_fields[$first_section]}
                <div class="clearfix" data-ct-address="billing-address">
                    <div class="checkout__block">
                        {include file="views/profiles/components/profile_fields.tpl" section=$first_section body_id="" ship_to_another=true is_checkout=true}
                    </div>
                </div>
            {/if}

            {*if $profile_fields[$sec_section]}
                <div class="clearfix shipping-address__switch" data-ct-address="shipping-address">
                    {include file="views/profiles/components/profile_fields.tpl" section=$sec_section body_id=$body_id address_flag=$profile_fields|fn_compare_shipping_billing ship_to_another=$cart.ship_to_another title=$sec_section_text grid_wrap="checkout__block"}
                </div>
            {/if*}
            <input class="hidden" type="radio" name="ship_to_another" value="0" checked="checked" />
        {/if}
    </div>
<!--step_two--></div>
