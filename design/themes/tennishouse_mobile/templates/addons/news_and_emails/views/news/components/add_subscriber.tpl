{if $page_mailing_lists}
    <div class="ty-newsletters subscription-container" id="subsciption_block">
        <input type="hidden" name="subscribe_to_store_newsletters" value="N" />
        <input type="checkbox" id="subscribe_to_store_newsletters" name="subscribe_to_store_newsletters" value="Y" class="checkbox" checked="checked"/>
        <label for="subscribe_to_store_newsletters">{__("subscribe_to_store_newsletters")}</label>
    <!--subsciption_block--></div>
{elseif $show_unsubscribe}
    <div class="ty-newsletters subscription-container" id="subsciption_block">
        <input type="hidden" name="subscribe_to_store_newsletters" value="N" />
        <input type="checkbox" id="subscribe_to_store_newsletters" name="subscribe_to_store_newsletters" value="Y" class="checkbox" />
        <label for="subscribe_to_store_newsletters">{__("subscribe_to_store_newsletters")}</label>
    <!--subsciption_block--></div>
{/if}