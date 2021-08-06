{if $addons.call_requests.buy_now_with_one_click == "Y" && !$hide_call_request}
    {$id = "call_request_{$obj_prefix}{$obj_id}"}
    <div class="ty-call-request-button"><a id="opener_{$id}_{$obj_prefix}{$obj_id}" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__text" data-ca-target-id="content_{$id}">{__("call_requests.quick_order")}</a></div>
    <div class="hidden" id="content_{$id}" title="{__("call_requests.buy_now_with_one_click_`$product.type`")}" data-title-tag="h2">
        {include file="addons/call_requests/views/call_requests/components/call_requests_content.tpl" product=$product id=$id is_product=true}
    </div>
{/if}
