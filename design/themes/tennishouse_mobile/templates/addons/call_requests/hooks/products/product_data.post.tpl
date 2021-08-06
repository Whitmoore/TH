{if $details_page && $addons.call_requests.buy_now_with_one_click == "Y"}

{$id = "call_request_{$obj_prefix}{$obj_id}"}

<div class="hidden" id="content_{$id}" title="{__("call_requests.buy_now_with_one_click_`$product.type`")}" data-title-tag="h2">
    {include file="addons/call_requests/views/call_requests/components/call_requests_content.tpl" product=$product id=$id}
</div>

{/if}