{capture name="mainbox_title"}{__("successfully_confirmed")}{/capture}

<span class="ty-success-registration__text">
    {__("success_registration_text")}
    {if $mail_server}
        <div class="ty-success-add__confirm-email">
            <a href="{$mail_server}" target="_blank" class="ty-btn ty-btn__primary">{__("check_email")}</a>
        </div>
    {/if}
</span>
<ul class="success-registration__list">
    {hook name="profiles:success_registration"}
        <a href="{"profiles.update"|fn_url}" class="success-registration__a"><li class="ty-success-registration__item">
            {__("edit_profile")}
            <span class="ty-success-registration__info">{__("edit_profile_note")}</span>
        </li></a>
        <a href="{"orders.search"|fn_url}" class="success-registration__a"><li class="ty-success-registration__item">
            {__("orders")}
            <span class="ty-success-registration__info">{__("track_orders")}</span>
        </li></a>
        {*<li class="ty-success-registration__item">
            <a href="{"product_features.compare"|fn_url}" class="success-registration__a">{__("compare_list")}</a>
            <span class="ty-success-registration__info">{__("compare_list_note")}</span>
        </li>*}
    {/hook}
</ul>
