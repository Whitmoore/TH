<div class="ty-top-block-wrapper">
    <div class="ty-logo-container ty-top-block__cell">
        <div class="ty-top-block__cell-inner ty-top-block__logo-content">
            <div class="ty-top-block__cell-content">
                <a href="{""|fn_url}" title="{$logos.theme.image.alt}">
                    <div class="ty-logo-wrapper"><div class="ty-store-logo"></div></div>
                </a>
            </div>
        </div>
    </div>
    {*
    <div class="ty-store-phone-block ty-top-block__cell">
        <div class="ty-top-block__cell-inner">
            <div class="ty-top-block__cell-content">
                <div class="ty-store-phone"><a href="tel:{$company_phone}">{$settings.Company.company_phone}</a></div>
                <div class="ty-store-phone__call_back"><a id="opener_store_call" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__text" data-ca-target-id="content_store_call">{__("call_back")}</a></div>
                <div class="hidden" id="content_store_call" title="{__("order_call_back")}">
                    {include file="addons/call_requests/views/call_requests/components/call_requests_content.tpl" id="store_call"}
                </div>
            </div>
        </div>
    </div>
    *}
    <div class="ty-top-block__search ty-top-block__cell cm-ajax-search-block">
        <div class="ty-top-block__cell-inner ty-top-block__search-content">
            {include file="common/search.tpl"}
        </div>
    </div>
    <div class="top-my-account ty-top-block__cell cm-hover-dropdown">
        {capture name="my_account_content"}
            {include file="blocks/my_account.tpl" title={__("my_account")} block = ['snapping_id' => 'my_account']}
        {/capture}
        <div class="ty-top-block__cell-inner ty-dropdown-box__title ty-top-block__my-account-content cm-link" data-href="{"profiles.update"|fn_url}">
            {$smarty.capture.title nofilter}
        </div>
        <div class="cm-popup-box ty-dropdown-box__content cm-hover-dropdown-submenu">
            {$smarty.capture.my_account_content nofilter}
        </div>
    </div>
    {include file="blocks/wishlist_content.tpl" dropdown_id="wishlist_content" block=['snapping_id' => 'wishlist_content', 'properties' => ['products_links_type' => 'thumb', 'display_delete_icons' => 'Y', 'display_bottom_buttons' => 'Y']]}
    {include file="blocks/compare_list_content.tpl" dropdown_id="compare_list_content" block=['snapping_id' => 'compare_list_content', 'properties' => ['products_links_type' => 'thumb', 'display_delete_icons' => 'Y', 'display_bottom_buttons' => 'Y']]}
    {include file="blocks/cart_content.tpl" dropdown_id="cart_content" block=['snapping_id' => 'cart_content', 'properties' => ['products_links_type' => 'thumb', 'display_delete_icons' => 'Y', 'display_bottom_buttons' => 'Y']]}
    {*
    <div class="ty-top-block_bottom-wrapper">
        <a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=2"|fn_url}">
            <div class="ty-benefits-guarantees__top">
                <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-low-price"></div></div>
                <div class="ty-benefits-guarantees__text ty-benefits-guarantees__text-single-line">{__("only_original_goods")}</div>
            </div>
        </a>
        <a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=5"|fn_url}">
        <div class="ty-benefits-guarantees__top">
            <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-free-shipping"></div></div>
            <div class="ty-benefits-guarantees__text">{__("free_delivery_text")}</div>
        </div>
        </a>
        <a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=4"|fn_url}">
        <div class="ty-benefits-guarantees__top">
            <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-free-returns"></div></div>
            <div class="ty-benefits-guarantees__text ty-benefits-guarantees__text-single-line">{__("free_returns_text")}</div>
        </div>
        </a>
        <a class="ty-benefits-guarantees__a" href="{"pages.view?page_id=73"|fn_url}">
        <div class="ty-benefits-guarantees__top">
            <div class="ty-benefits-guarantees__icon-block"><div class="ty-benefits-paymenton-delivery"></div></div>
            <div class="ty-benefits-guarantees__text">{__("payment_on_delivery_text")}</div>
        </div>
        </a>
    </div>
    *}
</div>