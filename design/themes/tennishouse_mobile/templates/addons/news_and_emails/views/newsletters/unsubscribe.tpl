<div class="ty-unsubscribe" id="unsubscribe_block">
    {if $subscriber_found}
        <form name="unsubscribe_form" action="{""|fn_url}" method="post" class="cm-ajax">
            <input type="hidden" name="result_ids" value="unsubscribe_block">
            <input type="hidden" name="unsubscribe_data[key]" value="{$key}">
            <input type="hidden" name="unsubscribe_data[list_id]" value="{$list_id}">
            <input type="hidden" name="unsubscribe_data[s_id]" value="{$s_id}">
            
            <div class="ty-unsubscribe-email">{__("unsubscribe_email_text", ["[email]" => $email])}</div>
            <div class="ty-control-group">
                <label class="ty-control-group__title cm-trim" for="unsubscribe_notes">{__("unsubscribe_reason_text")}</label>
                <textarea id="unsubscribe_notes" name="unsubscribe_data[notes]" cols="55" rows="5" class="ty-input-textarea-long"></textarea>
            </div>
            <div class="buttons-container login-recovery">
                {include file="buttons/button.tpl" but_text=__("unsubscribe") but_name="dispatch[newsletters.unsubscribe]" but_meta="ty-btn__login ty-btn__secondary"}
            </div>
        </form>
    {elseif $unsubscribed}
        {__("subscriber_removed_text", ["[email]" => $email])}
        {if $list_id && $s_id}
        <form name="subscribe_form" action="{""|fn_url}" method="post" class="cm-ajax">
            <input type="hidden" name="result_ids" value="unsubscribe_block">
            <input type="hidden" name="subscribe_data[list_id]" value="{$list_id}">
            <input type="hidden" name="subscribe_data[s_id]" value="{$s_id}">
            <div class="buttons-container login-recovery">
                {include file="buttons/button.tpl" but_text=__("subscribe_again") but_name="dispatch[newsletters.unsubscribe]" but_meta="ty-btn__login ty-btn__secondary"}
            </div>
        </form>
        {/if}
    {elseif $subscribed}
        {__("subscription_confirmed")}
    {else}
        {__("subscription_not_found")}
    {/if}
<!--unsubscribe_block--></div>