{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="competitors_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />

{if $competitors}
<table class="table table-middle">
<thead>
<tr>
    <th width="5%" class="left">
        {include file="common/check_items.tpl" class="cm-no-hide-input"}</th>
    <th>{__("name")}</th>
    <th>{__("link")}</th>
    <th>{__("last_update")}</th>
    <th width="6%">&nbsp;</th>
    <th width="10%" class="right">{__("status")}</th>
</tr>
</thead>
{foreach from=$competitors item=competitor}
<tr class="cm-row-status-{$competitor.status|lower}">
    <td class="left">
        <input type="checkbox" name="competitor_ids[]" value="{$competitor.competitor_id}" class="cm-item " {if $competitor.competitor_id == $smarty.const.TH_competitor_ID}disabled="disabled"{/if} /></td>
    <td class="">
        <a class="row-status" href="{"competitors.update?competitor_id=`$competitor.competitor_id`"|fn_url}">{$competitor.name}</a>
    </td>
    <td>
        <a class="row-status" target="_blank" href="{"`$competitor.link`"}">{$competitor.link}</a>
    </td>
    <td>
        {if $competitor.last_update}
            {$competitor.last_update|date_format:"`$settings.Appearance.date_format`"}, {$competitor.last_update|date_format:"`$settings.Appearance.time_format`"}
        {/if}
    </td>
    <td>
        {capture name="tools_list"}
            <li>{btn type="list" text=__("edit") href="competitors.update?competitor_id=`$competitor.competitor_id`"}</li>
            {if $competitor.competitor_id != $smarty.const.TH_competitor_ID}<li>{btn type="list" class="cm-confirm" text=__("delete") href="competitors.delete?competitor_id=`$competitor.competitor_id`"}</li>{/if}
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
    <td class="right nowrap">
    {include file="common/select_popup.tpl" popup_additional_class="dropleft" id=$competitor.competitor_id status=$competitor.status hidden=false object_id_name="competitor_id" table="competitors"}
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $competitors}
            <li>{btn type="delete_selected" dispatch="dispatch[competitors.m_delete]" form="competitors_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
    {if $competitors}
        {include file="buttons/save.tpl" but_name="dispatch[competitors.m_update]" but_role="submit-link" but_target_form="competitors_form"}
    {/if}{/capture}
{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="competitors.add" prefix="top" hide_tools="true" title=__("add_competitor") icon="icon-plus"}
{/capture}

</form>

{/capture}
{include file="common/mainbox.tpl" title=__("competitors") sidebar=$smarty.capture.sidebar content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
