{if !$hide_form && $addons.call_requests.buy_now_with_one_click == "Y" && !$hide_call_request}

{$id = "call_request_{$obj_prefix}{$obj_id}"}

<a id="opener_{$id}" class="cm-dialog-opener cm-dialog-auto-size ty-btn ty-btn__text" data-ca-target-id="content_{$id}">{__("call_requests.buy_now_with_one_click")}</a>

{/if}
