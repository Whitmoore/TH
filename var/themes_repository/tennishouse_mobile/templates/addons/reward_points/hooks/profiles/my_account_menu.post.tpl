{if $auth.user_id}
<li class="ty-menu__item">
    <div class="ty-menu__submenu-item-header">
        <a href="{"reward_points.userlog"|fn_url}" rel="nofollow" class="ty-menu__item-link">
            <div class="ty-menu-icon ty-reward-icon"></div>
            <div class="ty-menu__submenu-item-header-text">{__("reward_points_log")}</div>
        </a>
    </div>
</li>
{/if}