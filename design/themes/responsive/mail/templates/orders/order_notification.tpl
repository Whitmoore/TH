{include file="common/letter_header.tpl"}

{__("dear")} <b>{$order_info.firstname|fn_convert_case}</b>,<br />

{$order_status.email_header nofilter}
{if $order_info.destination_delivery_info}
    <p>
        {$order_info.destination_delivery_info}
    </p>
{/if}
{if $order_info.discussion && $status_settings.product_reviews == 'Y'}
    <p>
        {__("write_product_review_email_text")}:
    </p>
    {foreach from=$order_info.discussion item="prod"}
        <div><a href="{"products.view?product_id=`$prod.product_id``$ekey_sfx`"|fn_url:'C':'http'}" target="_blank">{__("write_review_about_product", ["[product_name]" => $prod.product])}</a></div>
    {/foreach}
{/if}
{if $order_info.tracking_number && $order_info.status|in_array:$smarty.const.ORDER_DELIVERY_STATUSES}
    <p>
    {__("tracking_number")}: {$order_info.tracking_number}
    </p>
{/if}
{if $order_info.office_info}
    <p>
    <table cellpadding="1" cellspacing="1" border="0" width="100%">
        <tr>
            <td colspan="2" class="form-title">{__("destination_point")}<hr size="1" noshade></td>
        </tr>
        <tr>
            <td style="font-style: italic;" nowrap>{__("city")}:&nbsp;</td>
            <td >{$order_info.office_info.City}</td>
        </tr>
        <tr>
            <td style="font-style: italic;" nowrap>{__("address")}:&nbsp;</td>
            <td >{$order_info.office_info.Address}</td>
        </tr>
        <tr>
            <td style="font-style: italic;" nowrap>{__("worktime")}:&nbsp;</td>
            <td >{$order_info.office_info.WorkTime}</td>
        </tr>
        <tr>
            <td style="font-style: italic;" nowrap>{__("short_phone")}:&nbsp;</td>
            <td >{$order_info.office_info.Phone}</td>
        </tr>
    </table>
    </p>
{/if}
{if $order_info.user_id && $order_info.status == 'A' && $order_info.shipping.0.module == 'sdek'}
    <p>
    {__("order_status_tracking_info", ["[order_page_link]" => "orders.details?order_id=`$order_info.order_id`&selected_section=sdek_information"|fn_url:"C"])}
    </p>
{/if}
<br />


{assign var="order_header" value=__("invoice")}
{if $status_settings.appearance_type == "C" && $order_info.doc_ids[$status_settings.appearance_type]}
    {assign var="order_header" value=__("credit_memo")}
{elseif $status_settings.appearance_type == "O"}
    {assign var="order_header" value=__("order_details")}
{/if}

<b>{$order_header}:</b><br />

{include file="orders/invoice.tpl"}

{include file="common/letter_footer.tpl"}
