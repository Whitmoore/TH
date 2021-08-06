<div class="sidebar-row">
    <h6>{__("search")}</h6>
    <form action="{""|fn_url}" name="logs_search_form" method="get">
        {capture name="simple_search"}
            {include file="common/period_selector.tpl" period=$search.period extra="" display="form" button="false"}
            <div class="control-group">
                <label class="control-label">{__("type")}:</label>
                <div class="controls">
                    <select id="type" name="type">
                        <option value=""{if !$search.type} selected="selected"{/if}>{__("all")}</option>
                        {foreach from=$log_types key="type" item="name"}
                            <option value="{$type}" {if $search.type == $type}selected="selected"{/if}>{$name}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        {/capture}

        {include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search dispatch="cron.manage" view_type="cron_logs"}
    </form>
</div>
