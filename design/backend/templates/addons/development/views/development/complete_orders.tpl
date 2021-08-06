{capture name="mainbox"}
<div id="co_section">
<form action="{""|fn_url}" method="post" name="complete_orders" enctype="multipart/form-data" class="cm-ajax form-horizontal form-edit">
<input type="hidden" name="result_ids" value="co_section" />

{if $step == 'one'}
    <div class="control-group">
        <label class="control-label">{__("select_file")}:</label>
        <div class="controls">{include file="common/fileuploader.tpl" var_name="csv_file[0]"}</div>
    </div>
{elseif $step == 'two'}

    <form action="{""|fn_url}" method="post" target="_self" name="orders_list_form">

    <div id="complete_orders_list">
    {assign var="order_status_descr" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses:false:true}
    {assign var="extra_status" value="development.complete_orders"}
    {$statuses = []}
    {assign var="order_statuses" value=$smarty.const.STATUSES_ORDER|fn_get_statuses:$statuses:false:true}

    {if $orders}
    <table width="100%" class="table table-middle">
    <thead>
    <tr>
        <th width="5%" class="left">
        {include file="common/check_items.tpl" check_statuses=$order_status_descr}
        </th>
        <th width="9%">{__("id")}</th>
        <th width="14%">{__("status")}</th>
        <th width="9%">{__("date")}</th>
        <th width="17%">{__("delivery_status")}</th>
        <th width="15%">{__("destination")}</th>
        <th width="7%">&nbsp;</th>
        <th width="10%" class="right">{__("total")}</th>

    </tr>
    </thead>
    {foreach from=$orders item="o"}
    <tr>
        <td class="left">
            <input type="checkbox" name="order_ids[]" value="{$o.order_id}" class="cm-item cm-item-status-{$o.status|lower}" /></td>
        <td>
            <a href="{"orders.details?order_id=`$o.order_id`"|fn_url}" class="underlined">#{$o.order_id}</a>{if $o.order_number}</br>№{$o.order_number|fn_short_order_number}{/if}
            {if $order_statuses_data[$o.status].params.appearance_type == "I" && $o.invoice_id}
                <p class="small-note">{__("invoice")} #{$o.invoice_id}</p>
            {elseif $order_statuses_data[$o.status].params.appearance_type == "C" && $o.credit_memo_id}
                <p class="small-note">{__("credit_memo")} #{$o.credit_memo_id}</p>
            {/if}
            {include file="views/companies/components/company_name.tpl" object=$o}
        </td>
        <td>
            {if "MULTIVENDOR"|fn_allowed_for}
                {assign var="notify_vendor" value=true}
            {else}
                {assign var="notify_vendor" value=false}
            {/if}

            {include file="common/select_popup.tpl" suffix="o" order_info=$o id=$o.order_id status=$o.status items_status=$order_status_descr update_controller="orders" notify=true notify_sms=true notify_department=true notify_vendor=$notify_vendor status_target_id="complete_orders_list" extra="&return_url=`$extra_status`" statuses=$order_statuses btn_meta="btn btn-info o-status-`$o.status` btn-small"|lower}
        </td>
        <td>{$o.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
        <td>
            {if $order_d_statuses[$o.order_id] == 'E'}{__('delivered')}{else}{__('not_delivered')}{/if}
        </td>
        <td>{if $o.s_city}{$o.s_city}{else} - {/if}{if $o.s_country}{if $o.s_city}, {/if}{$o.s_country|fn_get_country_name}{else} - {/if}</td>

        {hook name="orders:manage_data"}{/hook}

        <td width="5%" class="center">
            {capture name="tools_items"}
                <li>{btn type="list" href="orders.details?order_id=`$o.order_id`" text={__("view")}}</li>
                {hook name="orders:list_extra_links"}
                    <li>{btn type="list" href="order_management.edit?order_id=`$o.order_id`" text={__("edit")}}</li>
                    {assign var="current_redirect_url" value=$config.current_url|escape:url}
                    <li>{btn type="list" href="orders.delete?order_id=`$o.order_id`&redirect_url=`$current_redirect_url`" class="cm-confirm" text={__("delete")}}</li>
                {/hook}
            {/capture}
            <div class="hidden-tools">
                {dropdown content=$smarty.capture.tools_items}
            </div>
        </td>
        <td class="right">
            {include file="common/price.tpl" value=$o.total}
        </td>
    </tr>
    {/foreach}
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}

    <!--complete_orders_list--></div>
    </form>

{elseif $step == 'three'}
    {__('done')}
{/if}
{capture name="buttons"}
    {capture name="tools_list"}
        <li>{btn type="list" text={__("finish")} dispatch="dispatch[development.complete_orders.finish]" form="complete_orders"}</li>
        {hook name="orders:list_tools"}
        {/hook}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
    {include file="buttons/button.tpl" but_text=__("search") but_name="dispatch[development.complete_orders.search]" but_role="submit-link" but_target_form="complete_orders" but_meta="cm-tab-tools"}
    {include file="buttons/button.tpl" but_text=__("clear") but_name="dispatch[development.complete_orders.clear]" but_role="submit-link" but_target_form="complete_orders" but_meta="cm-tab-tools"}
{/capture}
</form>
<!--co_section--></div>
{/capture}

{include file="common/mainbox.tpl" title={__("complete_orders")} content=$smarty.capture.mainbox content_id="complete_orders" buttons=$smarty.capture.buttons}