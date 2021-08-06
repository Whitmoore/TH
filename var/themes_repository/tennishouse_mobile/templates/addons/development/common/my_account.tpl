<div id="my_account_block">
{hook name="profiles:my_account_menu"}
{if !$auth.user_id}
    {assign var="return_current_url" value=$config.current_url|escape:url}
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header">
            <a href="{"auth.login_form?return_url=`$return_current_url`"|fn_url}" rel="nofollow" class="ty-menu__item-link">
                <div class="ty-menu-icon ty-login-icon"></div>
                <div class="ty-menu__submenu-item-header-text">{__("sign_in")}</div>
            </a>
        </div>
    </li>
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header">
            <a href="{"profiles.add?return_url=`$return_current_url`"|fn_url}" rel="nofollow" class="ty-menu__item-link">
                <div class="ty-menu-icon ty-register-icon"></div>
                <div class="ty-menu__submenu-item-header-text">{__("register")}</div>
            </a>
        </div>
    </li>
{else}
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header">
            <a href="{"profiles.update"|fn_url}" rel="nofollow" class="ty-menu__item-link">
                <div class="ty-menu-icon ty-profile-icon"></div>
                <div class="ty-menu__submenu-item-header-text">{__("profile_details")}</div>
            </a>
        </div>
    </li>
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header">
            <a href="{"orders.search"|fn_url}" rel="nofollow" class="ty-menu__item-link">
                <div class="ty-menu-icon ty-orders-icon"></div>
                <div class="ty-menu__submenu-item-header-text">{__("orders")}</div>
            </a>
        </div>
    </li>
{/if}
<li class="ty-menu__item">
    <div class="ty-menu__submenu-item-header">
        <a href="{"product_features.compare"|fn_url}" rel="nofollow" class="ty-menu__item-link">
            <div class="ty-menu-icon ty-compare-icon"></div>
            <div class="ty-menu__submenu-item-header-text">{__("comparison_list")}{if $smarty.session.comparison_list.products|count > 0} ({$smarty.session.comparison_list.products|count}){/if}</div>
        </a>
    </div>
</li>
{/hook}
{if $auth.user_id}
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header">
            <a href="{"auth.logout?redirect_url=`$return_current_url`"|fn_url}" rel="nofollow" class="ty-menu__item-link">
                <div class="ty-menu-icon ty-logout-icon"></div>
                <div class="ty-menu__submenu-item-header-text">{__("sign_out")}</div>
            </a>
        </div>
    </li>
{/if}
{if $settings.Appearance.display_track_orders == 'Y'}
    <li class="ty-menu__item">
        <div class="ty-menu__submenu-item-header">
            <div class="ty-account-info__orders updates-wrapper track-orders" id="track_orders_block_{$block.snapping_id}">
                <div class="ty-menu-icon ty-order-tracking-icon"></div>
                <div class="ty-menu__submenu-item-header-form">
                    <form action="{""|fn_url}" method="get" class="cm-ajax cm-ajax-full-render" name="track_order_quick">
                        <input type="hidden" name="result_ids" value="track_orders_block_*" />
                        <input type="hidden" name="return_url" value="{$smarty.request.return_url|default:$config.current_url}" />

                        <div class="ty-account-info__orders-txt">{__("track_my_order")}</div>

                        <div class="ty-account-info__orders-input ty-control-group ty-input-append ty-input-order-tracking">
                            <label for="track_order_item{$block.snapping_id}" class="cm-required hidden">{__("track_my_order")}</label>
                            <input type="text" size="20" class="ty-input-text cm-hint" id="track_order_item{$block.snapping_id}" name="track_data" value="{__("order_id")}{if !$auth.user_id}/{__("email")}{/if}" />
                            {include file="buttons/go.tpl" but_name="orders.track_request" alt=__("go")}
                            {include file="common/image_verification.tpl" option="track_orders" align="left" sidebox=true}
                        </div>
                    </form>
                </div>
        <!--track_orders_block_{$block.snapping_id}--></div>
        </div>
    </li>
{/if}
<!--my_account_block--></div>