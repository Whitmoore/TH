{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="technologies_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />

{if $technologies}
<table class="table table-middle">
<thead>
<tr>
    <th width="1%" class="left">
        {include file="common/check_items.tpl" class="cm-no-hide-input"}</th>
    <th width="15%"><span>{__("image")}</span></th>
    <th>{__("technology")}</th>
    <th width="6%">&nbsp;</th>
</tr>
</thead>
{foreach from=$technologies item=technology}
<tr>
    <td class="left">
        <input type="checkbox" name="technology_ids[]" value="{$technology.technology_id}" class="cm-item " /></td>
    <td>
        {include file="common/image.tpl" image=$technology.main_pair.icon|default:$technology.main_pair.detailed image_id=$technology.main_pair.image_id image_width=50 href="technologies.update?technology_id=`$technology.technology_id`"|fn_url}
    </td>
    <td class="">
        <a class="row-status" href="{"technologies.update?technology_id=`$technology.technology_id`"|fn_url}">{$technology.name}</a>
    </td>
    <td>
        {capture name="tools_list"}
            <li>{btn type="list" text=__("edit") href="technologies.update?technology_id=`$technology.technology_id`"}</li>
            <li>{btn type="list" class="cm-confirm" text=__("delete") href="technologies.delete?technology_id=`$technology.technology_id`"}</li>
        {/capture}
        <div class="hidden-tools">
            {dropdown content=$smarty.capture.tools_list}
        </div>
    </td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $technologies}
            <li>{btn type="delete_selected" dispatch="dispatch[technologies.m_delete]" form="technologies_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
    {if $technologies}
        {include file="buttons/save.tpl" but_name="dispatch[technologies.m_update]" but_role="submit-link" but_target_form="technologies_form"}
    {/if}{/capture}
{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="technologies.add" prefix="top" hide_tools="true" title=__("add_technology") icon="icon-plus"}
{/capture}

</form>

{/capture}
{include file="common/mainbox.tpl" title=__("technologies") sidebar=$smarty.capture.sidebar content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
