{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="warehouses_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />

{if $warehouses}
<table class="table table-middle">
<thead>
<tr>
    <th width="1%" class="left">
        {include file="common/check_items.tpl" class="cm-no-hide-input"}</th>
    <th width="15%"><span>{__("priority")}</span></th>
    <th>{__("warehouse")}</th>
    <th width="6%">&nbsp;</th>
</tr>
</thead>
{foreach from=$warehouses item=warehouse}
<tr>
    <td class="left">
        <input type="checkbox" name="warehouse_ids[]" value="{$warehouse.warehouse_id}" class="cm-item " {if $warehouse.warehouse_id == $smarty.const.TH_WAREHOUSE_ID}disabled="disabled"{/if} /></td>
    <td>
        <input type="text" name="warehouses_data[{$warehouse.warehouse_id}][priority]" value="{$warehouse.priority}" size="3" class="input-micro input-hidden" />
    </td>
    <td class="">
        <a class="row-status" href="{"warehouses.update?warehouse_id=`$warehouse.warehouse_id`"|fn_url}">{$warehouse.name}</a>
    </td>
    <td>
        {capture name="tools_list"}
            <li>{btn type="list" text=__("edit") href="warehouses.update?warehouse_id=`$warehouse.warehouse_id`"}</li>
            {if $warehouse.warehouse_id != $smarty.const.TH_WAREHOUSE_ID}<li>{btn type="list" class="cm-confirm" text=__("delete") href="warehouses.delete?warehouse_id=`$warehouse.warehouse_id`"}</li>{/if}
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
        {if $warehouses}
            <li>{btn type="delete_selected" dispatch="dispatch[warehouses.m_delete]" form="warehouses_form"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}
    {if $warehouses}
        {include file="buttons/save.tpl" but_name="dispatch[warehouses.m_update]" but_role="submit-link" but_target_form="warehouses_form"}
    {/if}{/capture}
{capture name="adv_buttons"}
    {include file="common/tools.tpl" tool_href="warehouses.add" prefix="top" hide_tools="true" title=__("add_warehouse") icon="icon-plus"}
{/capture}

</form>

{/capture}
{include file="common/mainbox.tpl" title=__("warehouses") sidebar=$smarty.capture.sidebar content=$smarty.capture.mainbox buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
