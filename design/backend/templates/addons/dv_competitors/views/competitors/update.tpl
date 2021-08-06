{if $competitor_data}
    {assign var="id" value=$competitor_data.competitor_id}
{else}
    {assign var="id" value=0}
{/if}

{** competitors section **}

{capture name="mainbox"}

{capture name="tabsbox"}
<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="competitors_form">
<input type="hidden" name="competitor_id" value="{$id}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

<div id="content_detailed">
    <div class="control-group">
        <label for="elm_competitor_name" class="control-label cm-required">{__("competitor_name")}</label>
        <div class="controls">
        <input type="text" name="competitor_data[name]" id="elm_competitor_name" value="{$competitor_data.name}" size="25" class="input-long" /></div>
    </div>
    <div class="control-group">
        <label for="elm_competitor_link" class="control-label cm-required">{__("competitor_link")}</label>
        <div class="controls">
        <input type="text" name="competitor_data[link]" id="elm_competitor_link" value="{$competitor_data.link}" class="input-large" /></div>
    </div>
    <div class="control-group">
        <label for="elm_last_update" class="control-label">{__("last_update")}</label>
        <div class="controls">
            {if $competitor_data.last_update}
                {$competitor_data.last_update|date_format:"`$settings.Appearance.date_format`"}, {$competitor_data.last_update|date_format:"`$settings.Appearance.time_format`"}
            {else}
                -
            {/if}
        </div>
    </div>
    {if !$competitor_data}
        {$competitor_data.status = 'D'}
    {/if}
    {include file="common/select_status.tpl" input_name="competitor_data[status]" id="elm_competitor_status" obj=$competitor_data hidden=false}

    {if $id}
    <div class="control-group">
        <label for="elm_competitor_link" class="control-label">{__("products")}</label>
        <div class="controls">
            <div class="ty-cp-cell ty-cp-input-full">
                <input type="text" size="55" value="{$product.product}" data-product-id="" data-competitor-id="{$id}" class="input-large cm-competitor-products" />
                {include file="addons/dv_competitors/views/competitors/prices_results.tpl"}
            </div>
        </div>
    </div>
    {/if}
</div>

</form>

{if $competitor_data.competitor_id}
<div id="content_parsing">
    {include file="addons/dv_competitors/common/parse_link.tpl" competitor_id=$id}
</div>
{/if}

{if $competitor_data.update_log}
    <div id="content_update_log">
        <div class="cron-log-results">{$competitor_data.update_log|fn_print_tpl}</div>
    </div>
{/if}

{capture name="buttons"}
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[competitors.update]" but_role="submit-link" but_target_form="competitors_form" but_meta="cm-save-buttons"}
    {else}

        {capture name="tools_list"}
        <li>{btn type="list" text=__("update_catalog") href="competitors.update_competitor?competitor_id=`$id`"}</li>
        <li>{btn type="list" text=__("delete") class="cm-confirm" href="competitors.delete?competitor_id=`$id`"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}

        {include file="buttons/save_cancel.tpl" but_name="dispatch[competitors.update]" but_role="submit-link" but_target_form="competitors_form" save=$id but_meta="cm-save-buttons"}
    {/if}
{/capture}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{/capture}

{if !$id}
    {assign var="title" value=__("new_competitor")}
{else}
    {assign var="title" value="{__("editing_competitor")}: `$competitor_data.name`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

{** competitor section **}
