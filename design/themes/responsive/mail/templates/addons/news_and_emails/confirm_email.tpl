{include file="common/letter_header.tpl"}

{__("dear")},<br><br>

{__("text_new_subscriber")}<br /><br />

{if $promo}
{__("text_new_subscriber_promo", ["[percent]" => $promo.discount, "[promo]" => $promo.promo_code, "[days]" => $promo.promo_expiration])}

<div style="background-image: url('{$images_dir}/addons/development/subscriber_promo.jpg'); background-size: contain; background-repeat: no-repeat; text-align: center; width: 600px; height: 500px; display: inline-block;">
    <div style="color: rgb(255, 255, 255); display: block; margin: 15px 0px; font-size: 23px;">{__("subscription_your_present")}</div>
    <div style="color: rgb(255, 255, 255); display: block; font-size: 110px;">{$promo.discount}%</div>
    <div style="margin: 30px 0px 0px;">
        <div style="color: rgb(255, 255, 255); border: 1px solid rgb(255, 255, 255); width: 260px; height: 40px; line-height: 40px; display: inline-block;">{$promo.promo_code}</div>
        <div style="background-color: rgb(255, 255, 255); border: 1px solid rgb(255, 255, 255); display: inline-block; width: 260px; vertical-align: top;"><a href="{""|fn_url:'C':'http'}" style="color: rgb(0, 0, 0); text-transform: uppercase; font-size: 15px; display: block; height: 40px; line-height: 40px;">{__("choose_and_buy")}</a></div>
    </div>
</div><br /><br /><br />
{/if}

<div>
{capture name="link"}
    <a href="{"newsletters.decline_email?ekey=`$ekey`"|fn_url:$zone:'http'}">{__("not_my_account")}</a>
{/capture}
{__("text_decline_subscription_email", ["[link]" => $smarty.capture.link])} 
</div>

{include file="common/letter_footer.tpl"}