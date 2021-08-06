<div id="rc_dialog">
{capture name="buttons"}
    {if $dialog_data.option && $dialog_data.option == '3'}
        <div class="ty-customization-footer" id="sd_buttons">
            <div class="ty-customization-detail-link ty-customization-detail-link-right cm-customization-switch cm-customization" {if $dialog_data.view != 'L'}style="display: none;"{/if}>
                {__("show_customization_details")}
            </div>
            <div class="ty-customization-detail-link ty-customization-detail-link-left cm-customization-switch cm-customization" {if $dialog_data.view != 'P'}style="display: none;"{/if}>
                {__("go_to_products_list")}
            </div>
        <!--sd_buttons--></div>
    {/if}
{/capture}
{capture name="info"}
        <div class="{if $dialog_data.option}ty-sd__content-mode-{$dialog_data.option}{/if}" {if $dialog_data.option}id="sd_content_mode_{$dialog_data.option}"{/if}>
        <form action="{""|fn_url}" method="post" class="cm-ajax cm-ajax-force" name="stringing_form" id="sd_form">
            <input type="hidden" name="result_ids" id="sd_result_ids" value="cart_items,cart_status*,wish_list*,checkout*,account_info*,add_to_cart_button*,{if $dialog_data.option == 3}sd_content_mode_3,sd_buttons{else}rc_dialog{/if}">
            {assign var="obj_id" value=$product_id}
            {assign var="obj_prefix" value="customization_"}
            <div class="cm-reload-{$obj_prefix}{$obj_id}">
                <input type="hidden" name="dispatch" value="racket_customization.view">
                <input type="hidden" name="sd_data" id="sd_data" value="{if $dialog_data}{foreach from=$dialog_data item="value" key="name" name="dialog_data"}{if !$smarty.foreach.dialog_data.first}&{/if}{$name}={$value}{/foreach}{/if}">
                <input type="hidden" name="product_data[{$product_id}][product_id]" value="{$product_id}">
                <input type="hidden" name="product_data[{$product_id}][amount]" value="1">
                {if $racket.product_options}
                    {foreach from=$racket.product_options key="opt_id" item="var_id"}
                        <input type="hidden" name="product_data[{$product_id}][product_options][{$opt_id}]" value="{$var_id}">
                    {/foreach}
                {/if}
            </div>
            
            {if $dialog_data.option}
                {if $dialog_data.option == '2'}
                    <div class="ty-sd_subtitle">
                        {if $racket.free_strings}
                            {__("sd_subtitle_mode_2", ["[paid_strings]" => ''])}
                        {else}
                            {__("sd_subtitle_mode_2", ["[paid_strings]" => __("paid_strings_text")])}
                        {/if}
                    </div>
                    <div class="grid-list ty-grid-list__stringing">
                        <div class="ty-sd_customization-product-title">{$product.product}{if $racket.free_strings} + {__("strings")}{/if} + {__("stringing_service")}</div>
                        <div class="ty-sd__mode-2-item">
                            {*<div class="ty-grid-list__item">
                                <div class="ty-grid-list__item-sd-bg ty-grid-list__item-sd-es-bg"></div>
                                <div class="ty-grid-list__item-sd-title">{__("expert_stringing")}</div>
                            </div>*}
                            {include file="common/product_data.tpl" product=$product but_role="big" but_text=__("add_to_cart") hide_qty_label=true show_old_price=true show_price=true show_list_discount=true show_clean_price=true show_product_options=true}
                            
                            <div class="ty-sd_customization-left-image">
                                {include file="common/image.tpl" no_ids=true image_width=$settings.Thumbnails.product_details_thumbnail_width image_height=$settings.Thumbnails.product_details_thumbnail_height images=$product.main_pair}
                            </div>
                            
                            {assign var="old_price" value="old_price_`$obj_id`"}
                            {assign var="price" value="price_`$obj_id`"}
                            {assign var="clean_price" value="clean_price_`$obj_id`"}
                            {assign var="list_discount" value="list_discount_`$obj_id`"}
                            <div class="ty-prices-container-wrap">
                                <div class="{if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}prices-container {/if}" id="prices_update_{$obj_id}">
                                    <div class="ty-prices-container-left">
                                        {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}

                                        {if $smarty.capture.$price|trim}
                                            <div class="ty-product-block__price-actual">
                                                {$smarty.capture.$price nofilter}{if !$racket.free_strings}<span class="ty-sd-plus-strings"> + {__("strings")}</span>{/if}
                                                {if $product.product_type == 'C'}
                                                    <span class="cm-reload-{$product.product_id} ty-pc-zero-price-note" id="pc_note_{$product.product_id}">
                                                        {if !$product.price|floatval}
                                                            {__("pc_zero_price_note")}
                                                        {/if}
                                                    <!--pc_note_{$product.product_id}--></span>
                                                {/if}
                                            </div>
                                        {/if}

                                        {*$smarty.capture.$clean_price nofilter}
                                        {$smarty.capture.$list_discount nofilter*}
                                    </div>
                                <!--prices_update_{$obj_id}--></div>
                            </div>
                        </div>
                        <div class="ty-sd__mode-2-textaera">
                            <div class="ty-options-avail-container-wrap">
                                {if $product.product_options}
                                    <div class="ty-options-container">
                                        {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                                        <div class="ty-product-block__option">
                                            {assign var="product_options" value="product_options_`$obj_id`"}
                                            {$smarty.capture.$product_options nofilter}
                                        </div>
                                        {if $capture_options_vs_qty}{/capture}{/if}
                                    </div>
                                {/if}
                            </div>
                            {$sd_string_service_type = $group_ref[$smarty.const.STRINGING_TENSION_GROUP_ID]}
                            <div class="cm-reload-{$obj_prefix}{$obj_id}" id="product_configuration_options_update_{$obj_prefix}{$obj_id}{$smarty.const.STRINGING_TENSION_GROUP_ID}">
                                <input type="hidden" name="product_data[{$obj_id}][configuration][{$smarty.const.STRINGING_TENSION_GROUP_ID}][product_ids][]" value="{$smarty.const.STRINGING_PRODUCT_ID}">
                                <input type="hidden" name="product_data[{$obj_id}][configuration][{$smarty.const.STRINGING_TENSION_GROUP_ID}][is_separate]" value="true">
                                {if $product.configuration.$sd_string_service_type.product_options}
                                        {include file="addons/product_configurator/views/products/components/configuration_product_options.tpl" product=$product.configuration.$sd_string_service_type id=$product.configuration.$sd_string_service_type.product_id product_options=$product.configuration.$sd_string_service_type.product_options name="product_data[`$obj_id`][configuration][`$smarty.const.STRINGING_TENSION_GROUP_ID`][options]" request_obj_prefix=$obj_prefix request_obj_id=$obj_id}
                                {/if}
                            <!--product_configuration_options_update_{$obj_prefix}{$obj_id}{$smarty.const.STRINGING_TENSION_GROUP_ID}--></div>
                        </div>
                        <div class="cm-reload-{$obj_prefix}{$obj_id} ty-sd_customization-add-to-cart ty-btn__primary cm-sd-option cm-notification-close" data-add="1" data-step="{$sd_string_service_type}">
                            {if $dialog_data.edit_configuration}{__("save")}{else}{__("add_to_cart")}{/if}
                        </div>
                    </div>
                {elseif $dialog_data.option == '3'}
                    {$sd_string_type = $group_ref[$smarty.const.STRINGING_GROUP_ID]}
                    {$sd_dampener_type = $group_ref[$smarty.const.DAMPENER_GROUP_ID]}
                    {$sd_overgrip_type = $group_ref[$smarty.const.OVERGRIP_GROUP_ID]}
                    {*<div class="ty-sd_subtitle">{__("sd_subtitle_customization")}</div>*}
                    <div class="ty-sd_customization-wrapper">
                        <div class="ty-sd_customization-products-list cm-customization" {if $dialog_data.view != 'L'}style="display: none;"{/if}>
                        
                            <div class="ty-sd_customization-products-list-steps">
                                {if !$racket.is_strung}
                                <div class="ty-column3">
                                    <div class="ty-sd_customization-products-selector {if $dialog_data.step == $smarty.const.STRINGING_GROUP_ID}ty-sd_customization-products-selector-active{else}cm-sd-option{/if}" data-step="{$smarty.const.STRINGING_GROUP_ID}">
                                        <span class="ty-sd_customization-products-selector-sign {if $product.configuration.$sd_string_type}{if $dialog_data.step == $smarty.const.STRINGING_GROUP_ID}ty-sd_customization-products-selected-active{else}ty-sd_customization-products-selected-inactive{/if}{else}{if $dialog_data.step == $smarty.const.STRINGING_GROUP_ID}ty-sd_customization-products-active{else}ty-sd_customization-products-inactive{/if}{/if}"></span>
                                        {__("strings")}
                                    </div>
                                </div>
                                {/if}
                                <div class="ty-column3">
                                    <div class="ty-sd_customization-products-selector {if $dialog_data.step == $smarty.const.DAMPENER_GROUP_ID}ty-sd_customization-products-selector-active{else}cm-sd-option{/if}" data-step="{$smarty.const.DAMPENER_GROUP_ID}">
                                        <span class="ty-sd_customization-products-selector-sign {if $product.configuration.$sd_dampener_type}{if $dialog_data.step == $smarty.const.DAMPENER_GROUP_ID}ty-sd_customization-products-selected-active{else}ty-sd_customization-products-selected-inactive{/if}{else}{if $dialog_data.step == $smarty.const.DAMPENER_GROUP_ID}ty-sd_customization-products-active{else}ty-sd_customization-products-inactive{/if}{/if}"></span>
                                        {__("dampeners")}
                                    </div>
                                </div>
                                <div class="ty-column3">
                                    <div class="ty-sd_customization-products-selector {if $dialog_data.step == $smarty.const.OVERGRIP_GROUP_ID}ty-sd_customization-products-selector-active{else}cm-sd-option{/if}" data-step="{$smarty.const.OVERGRIP_GROUP_ID}">
                                        <span class="ty-sd_customization-products-selector-sign {if $product.configuration.$sd_overgrip_type}{if $dialog_data.step == $smarty.const.OVERGRIP_GROUP_ID}ty-sd_customization-products-selected-active{else}ty-sd_customization-products-selected-inactive{/if}{else}{if $dialog_data.step == $smarty.const.OVERGRIP_GROUP_ID}ty-sd_customization-products-active{else}ty-sd_customization-products-inactive{/if}{/if}"></span>
                                        {__("overgrips")}
                                    </div>
                                </div>
                            </div>
                            {include file="addons/development/views/racket_customization/pagination.tpl" no_sorting=true}
                            
                            {if $brands}
                                <div class="ty-sd_customization-filter">
                                    <select name="brand" class="cm-dropdown cm-sd-option">
                                        <option value=""> - {__("all_brands")} - </option>
                                        {foreach from=$brands item="brand" key="brand_id"}
                                            <option value="{$brand_id}" {if $dialog_data.brand == $brand_id}selected="selected"{/if}>{$brand}</option>
                                        {/foreach}
                                    </select>
                                </div>
                            {/if}
                            
                            {include file="blocks/list_templates/grid_list.tpl"
                            products=$products
                            columns=5
                            form_prefix="block_manager"
                            no_sorting=false
                            no_pagination=true
                            no_ids="Y"
                            obj_prefix="racket_customization"
                            item_number=false
                            show_trunc_name=true
                            show_old_price=true
                            show_price=true
                            show_rating=true
                            show_clean_price=true
                            show_list_discount=true
                            show_add_to_cart=false
                            but_role="action"
                            show_discount_label=true
                            no_link=true
                            c_url=$current_url
                            ajax_pagination=true
                            save_current_page=true}
                            
                            {include file="addons/development/views/racket_customization/pagination.tpl" no_sorting=true}
                        </div>
                        <div class="ty-sd_customization-details cm-customization" {if $dialog_data.view != 'P'}style="display: none;"{/if}>
                            {include file="common/product_data.tpl" product=$product but_role="big" but_text=__("add_to_cart") hide_qty_label=true show_old_price=true show_price=true show_list_discount=true show_clean_price=true show_product_options=true}
                            
                            <div class="ty-sd_customization-details-image {*if $dialog_data.step == $smarty.const.STRINGING_GROUP_ID}ty-sd_customization-details-image-strings{elseif $dialog_data.step == $smarty.const.DAMPENER_GROUP_ID}ty-sd_customization-details-image-dampener{elseif $dialog_data.step == $smarty.const.OVERGRIP_GROUP_ID}ty-sd_customization-details-image-overgrip{/if*}">
                                {include file="common/image.tpl" no_ids=true image_width=$settings.Thumbnails.product_details_thumbnail_width image_height=$settings.Thumbnails.product_details_thumbnail_height images=$product.main_pair}
                            </div>
                            
                            {assign var="old_price" value="old_price_`$obj_id`"}
                            {assign var="price" value="price_`$obj_id`"}
                            {assign var="clean_price" value="clean_price_`$obj_id`"}
                            {assign var="list_discount" value="list_discount_`$obj_id`"}
                            <div class="ty-sd_customization-product-title">{$product.product}</div>
                            <div class="ty-options-avail-container-wrap">
                                {if $product.product_options}
                                    <div class="ty-options-container">
                                        {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                                        <div class="ty-product-block__option">
                                            {assign var="product_options" value="product_options_`$obj_id`"}
                                            {$smarty.capture.$product_options nofilter}
                                        </div>
                                        {if $capture_options_vs_qty}{/capture}{/if}
                                    </div>
                                {/if}
                            </div>
                            <div class="ty-sd_customization-steps-wrapper">
                                {if !$racket.is_strung}
                                    {if $product.configuration.$sd_string_type}
                                        <div class="ty-sd_customization-selected">
                                            {include file="addons/development/views/racket_customization/customization_product.tpl" product=$product.configuration.$sd_string_type}
                                        </div>
                                        {$sd_string_service_type = $group_ref[$smarty.const.STRINGING_TENSION_GROUP_ID]}
                                        <div class="ty-sd_customization-selected">
                                            {include file="addons/development/views/racket_customization/customization_product.tpl" product=$product.configuration.$sd_string_service_type hide_delete=true}
                                        </div>
                                    {else}
                                        <div class="ty-sd_customization-selector {if $dialog_data.step == $smarty.const.STRINGING_GROUP_ID}ty-sd_customization-selector-active{else}cm-sd-option{/if}" data-step="{$smarty.const.STRINGING_GROUP_ID}">
                                            <span class="ty-sd_customization-selector-sign">+</span>
                                            {__("select_stringing")}
                                        </div>
                                    {/if}
                                {/if}
                                
                                {if $product.configuration.$sd_dampener_type}
                                    <div class="ty-sd_customization-selected">
                                        {include file="addons/development/views/racket_customization/customization_product.tpl" product=$product.configuration.$sd_dampener_type}
                                    </div>
                                {else}
                                    <div class="ty-sd_customization-selector {if $dialog_data.step == $smarty.const.DAMPENER_GROUP_ID}ty-sd_customization-selector-active{else}cm-sd-option{/if}" data-step="{$smarty.const.DAMPENER_GROUP_ID}">
                                        <span class="ty-sd_customization-selector-sign">+</span>
                                        {__("select_dampener")}
                                    </div>
                                {/if}
                                
                                {if $product.configuration.$sd_overgrip_type}
                                    <div class="ty-sd_customization-selected">
                                        {include file="addons/development/views/racket_customization/customization_product.tpl" product=$product.configuration.$sd_overgrip_type}
                                    </div>
                                {else}
                                    <div class="ty-sd_customization-selector {if $dialog_data.step == $smarty.const.OVERGRIP_GROUP_ID}ty-sd_customization-selector-active{else}cm-sd-option{/if}" data-step="{$smarty.const.OVERGRIP_GROUP_ID}">
                                        <span class="ty-sd_customization-selector-sign">+</span>
                                        {__("select_overgrip")}
                                    </div>
                                {/if}
                            </div>
                            <div class="ty-prices-container-wrap">
                                <div class="{if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}prices-container {/if}" id="prices_update_{$obj_id}">
                                    <div class="ty-prices-container-left">
                                        {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}

                                        {if $smarty.capture.$price|trim}
                                            <div class="ty-product-block__price-actual">
                                                {$smarty.capture.$price nofilter}
                                                {if $product.product_type == 'C'}
                                                    <span class="cm-reload-{$product.product_id} ty-pc-zero-price-note" id="pc_note_{$product.product_id}">
                                                        {if !$product.price|floatval}
                                                            {__("pc_zero_price_note")}
                                                        {/if}
                                                    <!--pc_note_{$product.product_id}--></span>
                                                {/if}
                                            </div>
                                        {/if}

                                        {*$smarty.capture.$clean_price nofilter}
                                        {$smarty.capture.$list_discount nofilter*}
                                    </div>
                                <!--prices_update_{$obj_id}--></div>
                            </div>
                            <div class="ty-sd_customization-add-to-cart ty-btn__primary {if $racket.is_strung || $product.configuration.$sd_string_type}cm-sd-option{else}ty-sd_customization-add-to-cart-disabled{/if} cm-notification-close" data-add="1">
                                {if $dialog_data.edit_configuration}{__("save")}{else}{__("add_to_cart")}{/if}
                            </div>
                        </div>
                    </div>
                {/if}
            {else}
                <div class="ty-sd_subtitle">{__("sd_subtitle")}</div>
                <div class="grid-list ty-grid-list__stringing">
                    {if !$racket.is_strung}
                        <div class="ty-grid-list__item-wrapper cm-sd-option" data-add="1">
                            <div class="ty-grid-list__item">
                                <div class="ty-grid-list__item-sd-bg ty-grid-list__item-sd-u-bg"></div>
                                <div class="ty-grid-list__item-sd-title">{__("keep_unstring")}</div>
                            </div>
                        </div>
                        <div class="ty-grid-list__item-wrapper cm-sd-option" data-option="2" data-step="{$smarty.const.STRINGING_TENSION_GROUP_ID}" data-product-add="{$smarty.const.STRINGING_PRODUCT_ID}" data-reload="true">
                            <div class="ty-grid-list__item">
                                <div class="ty-grid-list__item-sd-bg ty-grid-list__item-sd-es-bg"></div>
                                <div class="ty-grid-list__item-sd-title">{__("expert_stringing")}</div>
                                <div class="ty-grid-list__item-sd-price">
                                    {if $racket.free_strings}
                                        <div>{__("string")}: <b>{__("free")}</b></div>
                                        <div>{__("stringing_service")}: <b>{__("free")}</b></div>
                                    {else}
                                        <div>{__("string_stringing_service_discount")}</div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                    {/if}
                    <div class="ty-grid-list__item-wrapper cm-sd-option cm-notification-close" data-step="{$smarty.const.STRINGING_GROUP_ID}" data-option="3" data-reload="true">
                        <div class="ty-grid-list__item">
                            <div class="ty-grid-list__item-sd-bg ty-grid-list__item-sd-m-bg"></div>
                            <div class="ty-grid-list__item-sd-title">{__("manual_stringing_selection")}</div>
                            <div class="ty-grid-list__item-sd-price">
                                {if $racket.free_strings}
                                    <div>{__("string_discount")}</div>
                                    <div>{__("stringing_service")}: <b>{__("free")}</b></div>
                                {else}
                                    <div>{__("string_stringing_service_discount")}</div>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
            <button class="hidden" id="stringing_form_submit" type="submit"></button>
        </form>
        {if $dialog_data.option}<!--sd_content_mode_{$dialog_data.option}-->{/if}</div>
{/capture}
{include file="addons/development/views/racket_customization/stringing_notification.tpl" product_buttons=$smarty.capture.buttons product_info=$smarty.capture.info}
<!--rc_dialog--></div>
