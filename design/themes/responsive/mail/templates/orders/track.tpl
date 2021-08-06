{include file="common/letter_header.tpl"}

{__("hello")},<br /><br />

{__("text_track_request")}<br /><br />

{if $o_id}
{__("text_track_view_order", ["[order]" => $o_id])}<br /><br />

<div style="display: inline-block; text-align: center; width: 100%;">
<a href="{"orders.track?ekey=`$access_key`&o_id=`$o_id`"|fn_url:'C':'http'}" style="-moz-user-select: none; border: 1px solid transparent; cursor: pointer; display: inline-block; font-family: Play,sans-serif; font-style: normal; font-weight: normal; line-height: 1.42857; margin-bottom: 0px; outline: 0px none; text-align: center; text-decoration: none; vertical-align: middle; background: rgb(255, 191, 0) none repeat scroll 0px 0px; color: rgb(255, 255, 255); width: 300px; padding: 13px 14px; font-size: 20px;">{__("order_details")}</a>
</div>
<br />
<br />
<br />
{/if}

{__("text_track_view_all_orders")}<br />
<a href="{"orders.track?ekey=`$access_key`"|fn_url:'C':'http'}">{__("view_orders")}</a><br />

{include file="common/letter_footer.tpl"}