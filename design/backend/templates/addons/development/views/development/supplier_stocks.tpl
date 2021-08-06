{capture name="mainbox"}

{include file="addons/development/components/supplier_stocks.tpl"}
{/capture}

{include file="common/mainbox.tpl" title={__("update_stocks")} content=$smarty.capture.mainbox content_id="update_stocks" buttons=$smarty.capture.buttons}
