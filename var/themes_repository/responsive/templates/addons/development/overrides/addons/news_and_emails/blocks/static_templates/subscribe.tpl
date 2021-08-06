{** block-description:tmpl_subscription **}
{if $addons.news_and_emails}
<div class="ty-footer-form-block">
    <div class="ty-footer-form-block__title">{__("newsletter_subscribe_title")}</div>
    {capture name="newsletter_subscription_form"}
    <div class="ty-subscribe-form" id="newsletters_subscribe_form">
        {if $confirmation_text}
            <div class="ty-subscribe-confirmation-sent">{$confirmation_text}</div>
        {else}
            <form action="{""|fn_url}" method="get" class="cm-ajax cm-ajax-full-render" name="subscribe_form">
                <input type="hidden" name="result_ids" value="newsletters_subscribe_form" />
                <input type="hidden" name="redirect_url" value="{$config.current_url}" />
                <input type="hidden" name="newsletter_format" value="2" />
                <div class="ty-control-group">
                    <label class="cm-required cm-email ty-control-group__title" for="subscr_email{$block.block_id}">{__("email")}</label>
                    <input type="text" name="subscribe_email" id="subscr_email{$block.block_id}" size="20" placeholder="{__("enter_email")}" class="ty-input-text-large" />
                </div>
                {include file="common/image_verification.tpl" option="newsletters" align="left"}
                {include file="buttons/button.tpl" but_text=__("subscribe") but_meta="ty-btn__secondary" but_name="dispatch[newsletters.add_subscriber]"}
            </form>
        {/if}
    <!--newsletters_subscribe_form--></div>
    {/capture}

    {$id = "subscribe"}
    {include file="common/popupbox.tpl"
        content=$smarty.capture.newsletter_subscription_form
        link_text=__("subscribe")
        text=__("newsletter_subscribe_text")
        id=$id
        link_meta="ty-subscribe-button"
    }
</div>
{/if}