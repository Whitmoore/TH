{capture name="mainbox"}
<div id="cron_logs">
<form action="{""|fn_url}" method="post" name="logs_form">

{include file="common/pagination.tpl"}

{if $logs}
<table class="table">
<thead>
    <tr>
        <th width="1%" class="left">
            {include file="common/check_items.tpl" class="cm-no-hide-input"}</th>
        <th width="10%">{__("time")}</th>
        <th width="40%">{__("type")}</th>
        <th width="15%">{__("status")}</th>
        <th width="10%">{__("details")}</th>
        <th width="5%">&nbsp;</th>
    </tr>
</thead>
<tbody>
{foreach from=$logs item="log"}
<tr>
    <td class="left">
        <input type="checkbox" name="log_ids[]" value="{$log.log_id}" class="cm-item " /></td>
    <td>
        <span class="nowrap">{$log.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}{if $log.timestamp_finish && $log.timestamp|date_format:"`$settings.Appearance.time_format`" != $log.timestamp_finish|date_format:"`$settings.Appearance.time_format`"} - {$log.timestamp_finish|date_format:"`$settings.Appearance.time_format`"}{/if}</span>
    </td>
    <td>
        {$log_types[$log.type]}
    </td>
    <td>
        {if $log.status == 'S'}{__("successful")}{elseif $log.status == 'F'}{__("fail")}{elseif $log.status == 'P'}{__("cron_in_progress")}{/if}
    </td>
    <td>
        {if $log.results}
            <a href="{"cron.view?log_id=`$log.log_id`"|fn_url}" class="underlined">{__("details")}</a>
        {else}
            &nbsp;
        {/if}
    </td>
    <td class="nowrap">
        <div class="hidden-tools">
            {capture name="tools_list"}
                <li>{btn type="list" text=__("delete") class="cm-confirm" href="cron.delete?log_id=`$log.log_id`"}</li>
            {/capture}
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>
{/foreach}
</tbody>
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
{include file="common/pagination.tpl"}
</form>
<!--cron_logs--></div>
{capture name="buttons"}
    {capture name="tools_list"}
        {if $logs}
            <li>{btn type="delete_selected" dispatch="dispatch[cron.m_delete]" form="logs_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
{/capture}

{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="cron.manage" view_type="cron_logs"}
    {include file="addons/development/views/cron/components/logs_search_form.tpl"}
    <hr/>
    {include file="addons/development/views/cron/components/run.tpl"}
{/capture}

{include file="common/mainbox.tpl" title=__("cron_logs") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
