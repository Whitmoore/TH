{capture name="mainbox"}

<div class="cron-log-title">{$log_types[$log.type]}</div>
<div class="cron-log-time">{$log.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</div>
<div class="cron-log-status">{__("status")}: {if $log.status == 'S'}{__("successful")}{elseif $log.status == 'F'}{__("fail")}{elseif $log.status == 'P'}{__("cron_in_progress")}{/if}</div>

{if $log.results}
    <div class="cron-log-results">{$log.results|fn_print_tpl}</div>
{/if}

{/capture}

{include file="common/mainbox.tpl" title=__("cron_log_details") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
