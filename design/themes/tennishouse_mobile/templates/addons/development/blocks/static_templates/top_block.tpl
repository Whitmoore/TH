<div class="ty-top-block-wrapper">
    <div class="ty-menu-container">
        <a href="{""|fn_url}" data-iconpos="notext" data-shadow="false" data-iconshadow="false" title="{$logos.theme.image.alt}" onclick="$( '#left-panel' ).panel( 'open' );return false;"><div class="ty-menu-container__icon"></div></a>
    </div>
    <div class="ty-logo-container">
        <a href="{""|fn_url}" title="{$logos.theme.image.alt}">
            <div class="ty-store-logo"></div>
        </a>
    </div>
    <div class="ty-cart-container" id="cart_status_top">
        <a href="{""|fn_url}" data-iconpos="notext" data-shadow="false" data-iconshadow="false" title="{$logos.theme.image.alt}" onclick="$( '#right-panel' ).panel( 'open' );return false;">
            <div class="ty-cart-container__icon">
                {if $smarty.session.cart.amount}
                    <div class="ty-minicart__icon-counter">{$smarty.session.cart.amount}</div>
                {/if}
            </div>
        </a>
    <!--cart_status_top--></div>
</div>
<div class="ty-top-block__search">
    {include file="common/search.tpl"}
</div>
{*<a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=5"|fn_url}">*}
<div class="ty-benefits-guarantees__top">
    <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-free-shipping"></div></div>
    <div class="ty-benefits-guarantees__text">{__("free_delivery_and_paymet_on_delivery_text")}</div>
</div>
{*</a>*}
{*
<div class="ty-top-block_top_left-wrapper">
    <div class="ty-top-block_top-wrapper">
        <div class="ty-top-block__search">
            {include file="common/search.tpl"}
        </div>
        <div class="top-my-account">
            {$class = ""|fn_get_my_account_title_class}
            {assign var="dropdown_id" value="my_account"}
            {capture name="my_account_content"}
                {include file="blocks/my_account.tpl" title={__("my_account")} block = ['snapping_id' => 'my_account']}
            {/capture}
            <div class="ty-dropdown-box $class ty-float-right">
                <div id="sw_dropdown_{$dropdown_id}" class="ty-dropdown-box__title cm-combination">
                    {$smarty.capture.title nofilter}
                </div>
                <div id="dropdown_{$dropdown_id}" class="cm-popup-box ty-dropdown-box__content hidden">
                    {$smarty.capture.my_account_content nofilter}
                </div>
            </div>
        </div>
        <div class="top-cart-content">
            {include file="blocks/cart_content.tpl" dropdown_id="cart_content" block=['snapping_id' => 'cart_content', 'properties' => ['products_links_type' => 'thumb', 'display_delete_icons' => 'Y', 'display_bottom_buttons' => 'Y']]}
        </div>
    </div>
    <div class="ty-top-block_bottom-wrapper">
        <a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=2"|fn_url}">
            <div class="ty-benefits-guarantees__top">
                <div class="ty-benefits-guarantees__icon-block"><i class="ty-benefits-low-price"></i></div>
                <div class="ty-benefits-guarantees__text ty-benefits-guarantees__text-single-line">{__("our_advantages_text")}</div>
            </div>
        </a>
        <a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=5"|fn_url}">
        <div class="ty-benefits-guarantees__top">
            <div class="ty-benefits-guarantees__icon-block"><i class="ty-benefits-free-shipping"></i></div>
            <div class="ty-benefits-guarantees__text">{__("free_delivery_text")}</div>
        </div>
        </a>
        <a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=73"|fn_url}">
        <div class="ty-benefits-guarantees__top">
            <div class="ty-benefits-guarantees__icon-block"><i class="ty-benefits-paymenton-delivery"></i></div>
            <div class="ty-benefits-guarantees__text">{__("payment_on_delivery_text")}</div>
        </div>
        </a>
    </div>
</div>
*}