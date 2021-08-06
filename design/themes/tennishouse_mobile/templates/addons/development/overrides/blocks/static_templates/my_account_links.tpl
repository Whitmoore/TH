<p class="ty-footer-menu__header">
    <span>{__("my_account")}</span>
</p>
<ul class="ty-footer-menu__items">
{if $auth.user_id}
    <li class="ty-footer-menu__item"><a href="{"profiles.update"|fn_url}">{__("profile_details")}</a></li>
    <li class="ty-footer-menu__item"><a href="{"orders.search"|fn_url}">{__("orders")}</a></li>
    <li class="ty-footer-menu__item"><a href="{"wishlist.view"|fn_url}">{__("wishlist")}</a></li>       
    <li class="ty-footer-menu__item"><a href="{"product_features.compare"|fn_url}">{__("comparison_list")}</a></li>
    <li class="ty-footer-menu__item"><a href="{"rma.returns"|fn_url}">{__("return_requests")}</a></li>
{else}
    <li class="ty-footer-menu__item"><a href="{"auth.login_form"|fn_url}">{__("sign_in")}</a></li>
    <li class="ty-footer-menu__item"><a href="{"profiles.add"|fn_url}">{__("create_account")}</a></li>
{/if}
<!--account_info_links_{$block.snapping_id}--></ul>