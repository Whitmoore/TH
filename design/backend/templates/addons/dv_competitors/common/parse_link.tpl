<div id="parse_link">
    <form action="{""|fn_url}" method="post" class="cm-ajax form-horizontal form-edit" name="parsing_form">
        <input type="hidden" name="result_ids" value="parse_link" />
        <input type="hidden" name="competitor_id" value={$competitor_id} />

    <div class="control-group">
        <label for="elm_link" class="control-label">{__("link")}</label>
        <div class="controls">
            <input type="text" name="link" value="{if $link}{$link}{/if}" size="25" class="input-large" />
        </div>
    </div>

    <div class="btn-group btn-hover dropleft">
        {include file="buttons/button.tpl" but_role="submit" but_text=__("parse") but_name="dispatch[competitors.parse]"}
    </div>

    {if $result}
        <div class="cron-log-results">{$result|fn_print_tpl}</div>
    {/if}

    </form>
<!--parse_link--></div>
