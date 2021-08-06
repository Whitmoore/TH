{script src="js/addons/development/share.js"}

<div class="ty-social-share">
    <div class="social {if $short}social-short{/if}" data-url="{$config.current_url|fn_url}" data-title="{$title nofilter}" data-description="{$description}" data-image="{$image}">
        <div class="push facebook" data-id="fb"><i class="fa fa-facebook"></i><span class="ty-push-title">{__("facebook")}</span></div>
        <div class="push twitter" data-id="tw"><i class="fa fa-twitter"></i><span class="ty-push-title">{__("twitter")}</span></div>
        <div class="push vkontakte" data-id="vk"><i class="fa fa-vk"></i><span class="ty-push-title">{__("vkontakte")}</span></div>
        <div class="push google" data-id="gp"><i class="fa fa-google-plus"></i><span class="ty-push-title">{__("google_plus")}</span></div>
        <div class="push ok" data-id="ok"><i class="fa fa-odnoklassniki"></i><span class="ty-push-title">{__("odnoklassniki")}</span></div>
    </div>
</div>