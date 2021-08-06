{*if $auth.user_id}
<li class="ty-menu__item">
    <div class="ty-menu__submenu-item-header">
        <a href="{"rma.returns"|fn_url}" rel="nofollow" class="ty-menu__item-link">{__("return_requests")}</a>
    </div>
</li>
{/if*}