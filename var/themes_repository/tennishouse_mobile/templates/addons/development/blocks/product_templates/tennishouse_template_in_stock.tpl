{script src="js/tygh/exceptions.js"}
<div class="ty-product-block" id="product_details">
    <div itemscope itemtype="http://schema.org/Product" class="ty-product-block__wrapper clearfix">
    {hook name="products:view_main_info"}
        {if $product}
            {assign var="obj_id" value=$product.product_id}
            {include file="common/product_data.tpl" product=$product but_role="big" but_text=__("add_to_cart") hide_qty_label=true show_qty=false}
            {if !$hide_title}
                {if $product.discussion.posts || $product.header_features}
                    <div class="ty-product-detail__before-title">
                    {hook name="products:brand"}
                        <div class="ty-product-detail__brand-image">
                            {$brand_id = $smarty.const.BRAND_FEATURE_ID}
                            {$brand_variant_id = $product.header_features.$brand_id.variant_id}
                            {include file="addons/development/common/brand_logo.tpl" brand=$product.header_features.$brand_id.variants.$brand_variant_id brand_variant_id=$brand_variant_id itemprop="brand"}
                        </div>
                    {/hook}
                    </div>
                {/if}
                <div class="ty-product-block-title-wrapper">
                    <h1 itemprop="name" class="ty-product-block-title" {live_edit name="product:product:{$product.product_id}"}>
                        {$product.product nofilter}
                    </h1>
                    {hook name="products:main_info_title"}{/hook}
                </div>
            {/if}
            <div class="ty-product-block__img-wrapper">
                {hook name="products:image_wrap"}
                    {if !$no_images}
                        <div class="ty-product-block__img cm-reload-{$product.product_id}" id="product_images_{$product.product_id}_update">

                            <div class="ty-product-tags">
                                {if $product.tags.new}
                                    <div class="ty-new-item-tag"></div>
                                {/if}
                                {assign var="discount_label" value="discount_label_`$obj_prefix``$obj_id`"}
                                {$smarty.capture.$discount_label nofilter}
                                {if $product.free_strings}
                                    <div class="ty-free-string-tag"></div>
                                {/if}
                            </div>
                            {if $smarty.const.PROMOTION_TAG|in_array:$product.tags}
                                <div class="ty-grid-list__image-promo-tag-detailed"></div>
                            {/if}
                            
                            {include file="views/products/components/product_images.tpl" product=$product show_detailed_link="Y" image_width=$settings.Thumbnails.product_details_thumbnail_width image_height=$settings.Thumbnails.product_details_thumbnail_height}
                        <!--product_images_{$product.product_id}_update--></div>
                    {/if}
                {/hook}
            </div>
            <div class="ty-product-block__left">
                <div class="ty-product-detail__buy-section">
                {assign var="form_open" value="form_open_`$obj_id`"}
                {$smarty.capture.$form_open nofilter}

                {assign var="old_price" value="old_price_`$obj_id`"}
                {assign var="price" value="price_`$obj_id`"}
                {assign var="clean_price" value="clean_price_`$obj_id`"}
                {assign var="list_discount" value="list_discount_`$obj_id`"}

                {hook name="products:promo_text"}
                {if $product.promo_text}
                <div class="ty-product-block__note">
                    {$product.promo_text nofilter}
                </div>
                {/if}
                {/hook}

                <div class="ty-prices-container-wrap">
                    <div class="ty-prices-container-left">
                        <div class="{if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}prices-container {/if}" id="prices_update_{$obj_prefix}{$obj_id}">
                            {*if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}
                                <div class="ty-product-prices">
                                    {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}
                            {/if*}

                            {if $smarty.capture.$price|trim}
                                <div class="ty-product-block__price-actual">
                                    {if $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}{$smarty.capture.$price nofilter}
                                    {if $product.product_type == 'C'}
                                        <span class="cm-reload-{$obj_prefix}{$product.product_id} ty-pc-zero-price-note" id="pc_note_{$obj_prefix}{$product.product_id}">
                                            {if !$product.price|floatval}
                                                {__("pc_zero_price_note")}
                                            {/if}
                                        <!--pc_note_{$obj_prefix}{$product.product_id}--></span>
                                    {/if}
                                </div>
                                {assign var="qty_discounts" value="qty_discounts_`$obj_id`"}
                                {if $smarty.capture.$qty_discounts|trim}
                                    <div class="ty-ti-price-wrap">
                                        {$smarty.capture.$qty_discounts nofilter}
                                    </div>
                                {/if}
                            {/if}

                            {*if $smarty.capture.$old_price|trim || $smarty.capture.$clean_price|trim || $smarty.capture.$list_discount|trim}
                                    {$smarty.capture.$clean_price nofilter}
                                    {$smarty.capture.$list_discount nofilter}
                                </div>
                            {/if*}
                        <!--prices_update_{$obj_prefix}{$obj_id}--></div>
                    </div>
                </div>

                <div class="hidden">
                    {assign var="sku" value="sku_`$obj_id`"}
                    {$smarty.capture.$sku nofilter}
                </div>

                <div class="ty-options-avail-container-wrap">
                    {hook name="products:options_avail_container"}
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
                    
                    <div class="ty-avail-container">
                        {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                        <div class="ty-product-block__field-group">
                            {assign var="product_amount" value="product_amount_`$obj_id`"}
                            {$smarty.capture.$product_amount nofilter}
                        
                            {assign var="qty" value="qty_`$obj_id`"}
                            {$smarty.capture.$qty nofilter}
                        </div>
                        {if $capture_options_vs_qty}{/capture}{/if}
                    </div>
                    {/hook}
                </div>

                {if $capture_buttons}{capture name="buttons"}{/if}
                <div class="ty-product-block__button">
                    {if $show_details_button}
                        {include file="buttons/button.tpl" but_href="products.view?product_id=`$product.product_id`" but_text=__("view_details") but_role="submit"}
                    {/if}

                    {*<div class="ty-product-qty">
                        {assign var="qty" value="qty_`$obj_id`"}
                        {$smarty.capture.$qty nofilter}

                        {assign var="min_qty" value="min_qty_`$obj_id`"}
                        {$smarty.capture.$min_qty nofilter}
                    </div>*}

                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}

                    {assign var="list_buttons" value="list_buttons_`$obj_id`"}
                    {$smarty.capture.$list_buttons nofilter}
                </div>
                {if $capture_buttons}{/capture}{/if}
                {assign var="form_close" value="form_close_`$obj_id`"}
                {$smarty.capture.$form_close nofilter}

                {hook name="products:product_detail_bottom"}
                {/hook}
                </div>
            </div>
            
            <div class="ty-product-subsection">
                {if $product.variations}
                <div class="ty-product-variations">
                    <div class="ty-product-detail__info-title">{__("product_variations")}</div>
                    <div class="ty-product-variations__items">
                        {foreach from=$product.variations item="variation"}
                            <div class="ty-product-variations__items-image">
                                <a href="{"products.view?product_id=`$variation.product_id`"|fn_url}">{include file="common/image.tpl" images=$variation.main_pair image_width="70" image_height="70" show_detailed_link=false obj_id="variation_`$variation.product_id`"}</a>
                            </div>
                        {/foreach}
                    </div>
                </div>
                {/if}
                
                {if $product.customization_type}
                    <div class="ty-customization-wrapper">
                        <div class="ty-product-detail__info-title">{__("racket_customization_dialog_title")}</div>
                        <div class="ty-customization-block cm-sd-option" data-option="{if $product.free_strings}0{else}3{/if}" data-step="{$smarty.const.STRINGING_GROUP_ID}" data-form="product_form_{$obj_id}" data-reload="1">
                            <div class="ty-customization-image"></div>
                            <div class="ty-customization-blokc-descr">{if $product.customization_type == 'S'}{__("prepare_racket_description_no_strings")}{else}{__("prepare_racket_description")}{/if}</div>
                        </div>
                    </div>
                {/if}
                    
                {*<div class="ty-price-options">
                    {if $product.review_discount || $addons.development.review_reward_P > 0}
                    <div class="ty-get-discount-tooltip">
                        {if $product.review_discount}
                            {include file="addons/development/common/tooltip.tpl" note_text=__("get_review_discount_description", ["[percent]" => $product.review_discount]) tooltip_title=__("get_review_discount_text", ["[percent]" => $product.review_discount]) tooltipclass="ty-category-tooltip"}
                        {elseif $addons.development.review_reward_P > 0}
                            {include file="addons/development/common/tooltip.tpl" note_text=__("get_product_review_reward_points", ["[amount]" => $addons.development.review_reward_P, "[limit]" => $addons.development.review_number_limit_P, "[limit_month]" => $addons.development.review_time_limit_P]) tooltip_title=__("get_discount_now") tooltipclass="ty-category-tooltip"}
                        {/if}
                    </div>
                    {/if}
                    <div class="ty-found-cheaper-tooltip">
                        {include file="addons/development/common/tooltip.tpl" tooltip_title=__("found_cheaper") note_text=__("found_cheaper_offer") tooltipclass="ty-category-tooltip"}
                    </div>
                </div>*}
            
                <div class="ty-product-detail_shipping">
                    <div class="ty-product-detail__info-title">{__("shipping")}</div>
                    {if $product.is_free_shipping}
                        <div class="ty-product-free-shipping">{__("free_shipping_product_text")}</div>
                    {/if}
                    {include file="addons/development/common/product_shipping_estimation.tpl"}
                </div>
                
                <div class="ty-product-block__advanced-option">
                    {if $capture_options_vs_qty}{capture name="product_options"}{$smarty.capture.product_options nofilter}{/if}
                    {assign var="advanced_options" value="advanced_options_`$obj_id`"}
                    {$smarty.capture.$advanced_options nofilter}
                    {if $capture_options_vs_qty}{/capture}{/if}
                </div>
                
                {*if $product.offer_help*}
                    <div class="ty-consultation">
                        <div class="ty-consultation_column">
                            <span class="ty-product-detail__info-title">{__("expert_consultation")}</span>
                        </div>
                        <div class="ty-consultation_column">
                            <div class="ty-consultation-phone"><a href="tel:{$company_phone}">{$settings.Company.company_phone}</a></div>
                            <div class="ty-consultation-email"><a href="mailto:{$settings.Company.company_users_department}">{$settings.Company.company_support_department}</a></div>
                        </div>
                        <div class="ty-consultation_column">
                            <div class="ty-product-detail_code_wrapper">
                                <span class="ty-product-detail__info-title">{__("product_code")}: </span><span class="ty-product-detail__code">{$product.product_code}</span>
                            </div>
                        </div>
                    </div>
                {*/if*}
                
                {if $product.players}
                    {if "RASBST"|strpos:$product.category_type !== false}
                        {$type = $product.category_type}
                    {else}
                        {$type = 'C'}
                    {/if}
                    {if $product.players|count == '1'}
                        {$title = __("`$product.category_type`_played_by_single")}
                    {else}
                        {$title = __("`$product.category_type`_played_by")}
                    {/if}
                    <div class="ty-product-block__players">
                        <div class="ty-product-detail__info-title">
                            {$title}
                        </div>
                        <div class="ty-product-block__players-block">
                            {foreach from=$product.players item="player" name="plrs"}
                                {if $smarty.foreach.plrs.iteration < 5}
                                    <div class="ty-product-list__player_image">
                                        <a href="{"players.view?player_id=`$player.player_id`"|fn_url}">
                                            {include file="common/image.tpl" obj_id=$obj_id_prefix images=$player.main_pair image_width="90" image_height="90"}
                                        </a>
                                        <div>{$player.player}</div>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                {/if}
                
                {if $product.feature_comparison == "Y"}
                    <div class="ty-add-to-compare-block-details">
                    {include file="buttons/add_to_compare_list.tpl" product_id=$product.product_id}
                    </div>
                {/if}
            </div>
            
            {if $show_product_tabs}
                <div class="ty-product-tabs">
                    {include file="views/tabs/components/product_popup_tabs.tpl"}
                    {$smarty.capture.popupsbox_content nofilter}
                    {include file="views/tabs/components/product_tabs.tpl"}

                    {if $blocks.$tabs_block_id.properties.wrapper}
                        {include file=$blocks.$tabs_block_id.properties.wrapper content=$smarty.capture.tabsbox_content title=$blocks.$tabs_block_id.description}
                    {else}
                        {$smarty.capture.tabsbox_content nofilter}
                    {/if}
                </div>
            {/if}
        {/if}
    {/hook}
    </div>

    {if $smarty.capture.hide_form_changed == "Y"}
        {assign var="hide_form" value=$smarty.capture.orig_val_hide_form}
    {/if}

<!--product_details--></div>
{include file="addons/development/common/share_buttons.tpl" short=true title=$product.product description=$product.full_description|strip_tags|truncate:160 image=$product.main_pair.detailed.image_path}
[-similar_products-]

{if $block_tabs.tabs}
    <div class="ty-tennishouse-container ty-products-scroller">
    {$_block_tabs = []}
    {capture name="block_tabsbox"}
        {foreach from=$block_tabs.tabs item="block_data" key="block_tab_id"}
            {if $smarty.capture.$block_tab_id}
                {$_block_tabs.tabs.$block_tab_id = $block_data}
                {$smarty.capture.$block_tab_id nofilter}
            {/if}
        {/foreach}
    {/capture}
    {include file="addons/development/common/tabsbox.tpl" content=$smarty.capture.block_tabsbox navigation=$_block_tabs}
    </div>
{/if}

{capture name="mainbox_title"}{assign var="details_page" value=true}{/capture}