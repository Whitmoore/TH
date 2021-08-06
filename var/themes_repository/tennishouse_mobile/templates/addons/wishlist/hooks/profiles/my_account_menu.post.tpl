<li class="ty-menu__item">
    <div class="ty-menu__submenu-item-header">
        <a href="{"wishlist.view"|fn_url}" rel="nofollow" class="ty-menu__item-link">
            <div class="ty-menu-icon ty-wishlist-icon"></div>
            <div class="ty-menu__submenu-item-header-text">{__("wishlist")}{if $smarty.session.wishlist.products|count > 0} ({$smarty.session.wishlist.products|count}){/if}</div>
        </a>
    </div>
</li>