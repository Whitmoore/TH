<div class="sidebar-row">
    <h6>{__("run_script")}</h6>
    <form action="{""|fn_url}" name="cron_run_form" method="get" class="cm-ajax" id="run_script_form">
        <input type="hidden" name="result_ids" value="cron_logs" />
        <div class="sidebar-field">
            <label class="control-label">{__("type")}:</label>
            <div class="controls">
                <select id="type" name="cron_type">
                    <option value="" selected="selected">{__("all")}</option>
                    {foreach from=$log_types key="type" item="name"}
                        <option value="{$type}" >{$name}</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="sidebar-field">
            <div class="controls">
                <label class="checkbox">
                    <input type="checkbox" value="{$log.log_id}" checked="checked" onchange="$('#run_script_form').toggleClass('cm-ajax')"/>{__("Ajax")}
                </label>
            </div>
        </div>
        {include file="buttons/button.tpl" but_text=__("run") but_role="submit" method="GET" but_name="dispatch[cron.run]"}
    </form>
</div>
