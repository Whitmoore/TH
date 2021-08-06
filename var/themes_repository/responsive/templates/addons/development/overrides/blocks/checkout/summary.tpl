<div class="ty-checkout-summary" id="checkout_info_summary_{$block.snapping_id}">
    <table class="ty-checkout-summary__block">
        <tbody>
            <tr>
                <td colspan="2">
                    <ul class="ty-order-products__list order-product-list">
                    {hook name="block_checkout:cart_items"}
                        {foreach from=$cart_products key="key" item="product" name="cart_products"}
                            {hook name="block_checkout:cart_products"}
                                {if !$cart.products.$key.extra.parent}
                                    <li class="ty-order-products__item">
                                        <a href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&{$product.ohash}{/if}"|fn_url}" class="ty-order-products__a">{$product.product nofilter}</a>
                                        {if !$product.exclude_from_calculate}
                                            {include file="buttons/button.tpl" but_href="checkout.delete?cart_id=`$key`&redirect_mode=`$runtime.mode`" but_meta="ty-order-products__item-delete delete" but_target_id="cart_status*" but_role="delete" but_name="delete_cart_item"}
                                        {/if}
                                        <div class="ty-order-products__price">
                                            {$product.amount}&nbsp;x&nbsp;{include file="common/price.tpl" value=$product.display_price}
                                        </div>
                                        {include file="common/options_info.tpl" product_options=$product.product_options no_block=true}
                                        {hook name="block_checkout:product_extra"}{/hook}
                                    </li>
                                {/if}
                            {/hook}
                        {/foreach}
                    {/hook}
                    </ul>
                </td>
            </tr>

            {if !$cart.shipping_failed && $cart.chosen_shipping && $cart.shipping_required}
            <tr>
                <td class="ty-checkout-summary__item">{__("shipping")}</td>
                <td class="ty-checkout-summary__item ty-right" data-ct-checkout-summary="shipping">
                    {if !$cart.display_shipping_cost}
                        <span>{__("free_shipping")}</span>
                    {else}
                        <span>{include file="common/price.tpl" value=$cart.display_shipping_cost}</span>
                    {/if}
                </td>
            </tr>
            {/if}

            {if ($cart.discount|floatval)}
            <tr class="ty-checkout-summary__order_discount">
                <td class="ty-checkout-summary__item">
                    {__("including_discount")}
                </td>
                <td class="ty-checkout-summary__item ty-right discount-price" data-ct-checkout-summary="order-discount">
                    -{include file="common/price.tpl" value=$cart.discount}
                </td>
            </tr>
            {/if}

            {if $cart.subtotal_discount}
            <tr class="ty-checkout-summary__order_discount">
                <td class="ty-checkout-summary__item">
                    {__("order_discount")}
                </td>
                <td class="ty-checkout-summary__item ty-right discount-price" data-ct-checkout-summary="order-discount">
                    <span>-{include file="common/price.tpl" value=$cart.subtotal_discount}</span>
                </td>
            </tr>
            {/if}
            {if $cart.user_data.possible_user.allowed_points}
                <tr><td colspan="2">
                    {include file="addons/development/common/tooltip.tpl" note_text=__("authorize_apply_reward_points", ["[amount]" => $cart.user_data.possible_user.allowed_points, "[email]" => $cart.user_data.email]) tooltip_title=__("authorize_apply_reward_points_title", ["[amount]" => $cart.user_data.possible_user.allowed_points]) tooltipclass="ty-category-tooltip"}
                </td></tr>
            {/if}
            {if $cart.review_discount}
                <tr><td colspan="2">
                    {include file="addons/development/common/tooltip.tpl" note_text=__("get_review_discount_description", ["[percent]" => $cart.review_discount]) tooltip_title=__("get_review_discount_text", ["[percent]" => $cart.review_discount]) tooltipclass="ty-category-tooltip"}
                </td></tr>
            {elseif !$cart.subtotal_discount && $cart.points_info.max_allowed > 0 && $cart.points_info.total_price > 0 && (!$user_info || ($user_info && $user_info.points == 0)) && $addons.development.review_reward_P > 0}
                <tr><td colspan="2">
                    {include file="addons/development/common/tooltip.tpl" note_text=__("get_product_review_reward_points", ["[amount]" => $addons.development.review_reward_P, "[limit]" => $addons.development.review_number_limit_P, "[limit_month]" => $addons.development.review_time_limit_P]) tooltip_title=__("get_discount_now") tooltipclass="ty-category-tooltip"}
                </td></tr>
            {/if}
            {hook name="checkout:discount_summary"}
            {/hook}


            {if $cart.payment_surcharge && !$take_surcharge_from_vendor}
                <tr>
                    <td class="ty-checkout-summary__item">{$cart.payment_surcharge_title|default:__("payment_surcharge")}</td>
                    <td class="ty-checkout-summary__item ty-right" data-ct-checkout-summary="payment-surcharge">
                        <span>{include file="common/price.tpl" value=$cart.payment_surcharge}</span>
                    </td>
                </tr>
                {math equation="x+y" x=$cart.total y=$cart.payment_surcharge assign="_total"}
            {/if}

            {if $cart.taxes}
                <tr>
                    <td class="ty-checkout-summary__item ty-checkout-summary__taxes">{__("taxes")}</td>
                    <td class="ty-checkout-summary__item"></td>
                </tr>
                {foreach from=$cart.taxes item="tax"}
                    <tr>
                        <td class="ty-checkout-summary__item" data-ct-checkout-summary="tax-name {$tax.description}">
                            <div class="ty-checkout-summary__taxes-name">{$tax.description} ({include file="common/modifier.tpl" mod_value=$tax.rate_value mod_type=$tax.rate_type}{if $tax.price_includes_tax == "Y" && ($settings.Appearance.cart_prices_w_taxes != "Y" || $settings.General.tax_calculation == "subtotal")} {__("included")}{/if})</div>
                        </td>
                        <td class="ty-checkout-summary__item ty-right" data-ct-checkout-summary="taxes">
                            <span class="ty-checkout-summary__taxes-amount">{include file="common/price.tpl" value=$tax.tax_subtotal}</span>
                        </td>
                    </tr>
                {/foreach}
            {/if}

            {hook name="checkout:summary"}
            {/hook}
            <tr>
                <td colspan="2" class="ty-checkout-summary__item">
                    {include file="views/checkout/components/promotion_coupon.tpl"}
                </td>
            </tr>
        </tbody>
        <tbody>
            <tr>
                <th class="ty-checkout-summary__total" colspan="2" data-ct-checkout-summary="order-total">
                    <div>
                        {__("order_total")}
                        <span class="ty-checkout-summary__total-sum">{include file="common/price.tpl" value=$_total|default:$cart.total}</span>
                    </div>
                </th>
            </tr>
        </tbody>
    </table>
<!--checkout_info_summary_{$block.snapping_id}--></div>
