<div class="ty-reward-points-userlog-page">
{assign var="c_url" value=$config.current_url|fn_query_remove:"sort_by":"sort_order"}

<table class="ty-reward-points-userlog ty-table">
<thead>
    <tr>
        <th class="ty-reward-points-userlog__date"><a class="cm-ajax" href="{"`$c_url`&sort_by=timestamp&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("date")}</a>{if $search.sort_by == "timestamp"}{$sort_sign nofilter}{/if}</th>
        <th class="ty-reward-points-userlog__points"><a class="cm-ajax" href="{"`$c_url`&sort_by=amount&sort_order=`$search.sort_order_rev`"|fn_url}" data-ca-target-id="pagination_contents">{__("points")}</a>{if $search.sort_by == "amount"}{$sort_sign nofilter}{/if}</th>
        <th class="ty-reward-points-userlog__reason">{__("reason")}</th>
        <th class="ty-reward-points-userlog__expiration-date">{__("expiration_date")}</th>
    </tr>
</thead>
{foreach from=$userlog item="ul"}
<tr>
    <td>{$ul.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td>{include file="common/price.tpl" value=$ul.amount}</td>
    <td>
        {if $ul.action == $smarty.const.CHANGE_DUE_ORDER}
            {assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses:true:true}
            {assign var="reason" value=$ul.reason|unserialize}
            {assign var="order_exist" value=$reason.order_id|fn_get_order_name}
            {__("order")}&nbsp;{if $order_exist}<a href="{"orders.details?order_id=`$reason.order_id`"|fn_url}" class="underlined">{/if}<strong>#{$reason.order_id}</strong>{if $order_exist}</a>{/if}:&nbsp;{$statuses[$reason.from]}&nbsp;&#8212;&#8250;&nbsp;{$statuses[$reason.to]}{if $reason.text}&nbsp;({__($reason.text) nofilter}){/if}
        {elseif $ul.action == $smarty.const.CHANGE_DUE_USE}
            {assign var="order_exist" value=$ul.reason|fn_get_order_name}
            {__("text_points_used_in_order")}: {if $order_exist}<a href="{"orders.details?order_id=`$ul.reason`"|fn_url}">{/if}<strong>#{$ul.reason}</strong>{if $order_exist}</a>{/if}
        {elseif $ul.action == $smarty.const.CHANGE_DUE_ORDER_DELETE}
            {assign var="reason" value=$ul.reason|unserialize}
            {__("order")} <strong>#{$reason.order_id}</strong>: {__("deleted")}
        {elseif $ul.action == $smarty.const.CHANGE_DUE_ORDER_PLACE}
            {assign var="reason" value=$ul.reason|unserialize}
            {assign var="order_exist" value=$reason.order_id|fn_get_order_name}
            {__("order")} {if $order_exist}<a href="{"orders.details?order_id=`$reason.order_id`"|fn_url}" class="underlined">{/if}<strong>#{$reason.order_id}</strong>{if $order_exist}</a>{/if}: {__("placed")}
        {elseif $ul.action == $smarty.const.CHANGE_DUE_REVIEW}
            {assign var="reason" value=$ul.reason|unserialize}
            {__("review_reason_`$reason.type`")}
            {if $reason.type == 'P'}
                {assign var="product_exist" value=$reason.object_id|fn_get_product_name}
                {if $product_exist} <a href="{"products.view?product_id=`$reason.object_id`"|fn_url}" class="underlined"><strong>{$product_exist}</strong></a>{/if}
            {/if}
        {else}
            {hook name="reward_points:userlog"}
            {$ul.reason}
            {/hook}
        {/if}
    </td>
    <td>{if $ul.expire}{$ul.expire|date_format:"`$settings.Appearance.date_format`"}{else} - {/if}</td>
</tr>
{foreachelse}
<tr class="ty-table__no-items">
    <td colspan="3"><p class="ty-no-items">{__("no_items")}</p></td>
</tr>
{/foreach}
</table>

{capture name="mainbox_title"}
    {__("reward_points_log")}
    <div class="ty-profile-subtitle">
        <div class="ty-profile-reward-points">
            {__("balance")}:&nbsp;<a href="{"pages.view?page_id=`$smarty.const.SAVING_PROGRAM_PAGE_ID`"|fn_url}">{include file="common/price.tpl" value=$auth.points|default:"0"}</a>
        </div>
    </div>
{/capture}
</div>