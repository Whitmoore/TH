{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="savings_groups_form" class="form-horizontal form-edit {if ""|fn_check_form_permissions} cm-hide-inputs{/if}">

    <table class="table table-middle">
        <thead>
        <tr class="cm-first-sibling">
            <th width="30%">{__("orders_total")}</th>
            <th width="15%">{__("usergroup")}</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        {foreach from=$saving_data item="group_data" key="k" name="rdf"}
            <tr>
                <td class="nowrap">
                    {__("more_than")}&nbsp;
                    {if $smarty.foreach.rdf.first}
                        <input type="hidden" name="savings_groups_data[groups][{$k}][amount]" value="0" />
                        &nbsp;{include file="common/price.tpl" value="0"}
                    {else}
                        {include file="common/price.tpl" value="{$group_data.amount}" view="input" input_name="savings_groups_data[groups][`$k`][amount]" class="input-small input-hidden"}
                    {/if}
                </td>
                <td>
                    <select class="input-medium" name="savings_groups_data[groups][{$k}][usergroup_id]">
                        <option value="0"> - {__("none")} - </option>
                        {foreach from=$usergroups item="group"}
                            <option value="{$group.usergroup_id}" {if $group_data.usergroup_id == $group.usergroup_id}selected="selected"{/if}>{$group.usergroup}</option>
                        {/foreach}
                    </select></td>
                <td class="nowrap right">
                    {capture name="tools_items"}

                        {if !$smarty.foreach.rdf.first}
                            <a class="cm-confirm cm-tooltip" href="{"development.delete_savings_group?group_id=`$k`"|fn_url}" title="{__("delete")}"><i class="icon-trash"></i></a>
                        {else}
                            <span class="icon-trash undeleted-element" {__("delete")}></span>
                        {/if}
                    {/capture}
                    <div class="hidden-tools">
                        {if $smarty.foreach.rdf.first}
                            {include file="buttons/remove_item.tpl" but_class="cm-delete-row"}
                        {else}
                            {include file="buttons/remove_item.tpl" only_delete='Y' but_class="cm-delete-row"}
                        {/if}

                    </div>
                </td>
            </tr>
        {foreachelse}
            {$k = 1}
            <tr class="no-items">
                <td>
                    {__("more_than")}&nbsp;
                    <input type="hidden" name="savings_groups_data[groups][{$k}][amount]" value="0" />&nbsp;{include file="common/price.tpl" value="0"}
                </td>
                <td>
                    <select class="input-medium" name="savings_groups_data[groups][{$k}][usergroup_id]">
                        <option value="0"> - {__("none")} - </option>
                        {foreach from=$usergroups item="group"}
                            <option value="{$group.usergroup_id}">{$group.usergroup}</option>
                        {/foreach}
                    </select></td>
                <td class="right"><div class="hidden-tools">{include file="buttons/remove_item.tpl" but_class="cm-delete-row"}</div></td>
            </tr>
        {/foreach}

        {$k = $k + 1}
        <tr id="box_add_saving_group">
            <td>
                {__("more_than")}&nbsp;{include file="common/price.tpl" value="" view="input" input_name="savings_groups_data[add_groups][`$k`][amount]" class="input-small input-hidden"}</td>
            <td>
                <select class="input-medium" name="savings_groups_data[add_groups][{$k}][usergroup_id]">
                    <option value="0"> - {__("none")} - </option>
                    {foreach from=$usergroups item="group"}
                        <option value="{$group.usergroup_id}">{$group.usergroup}</option>
                    {/foreach}
                </select></td>
            <td class="right"> <div class="hidden-tools">{include file="buttons/multiple_buttons.tpl" item_id="add_saving_group" tag_level=1}</div></td>
        </tr>

    </table>

    {capture name="buttons"}
        {include file="buttons/save.tpl" but_role="submit-link" but_target_form="savings_groups_form" but_name="dispatch[development.update_savings_groups]"}
    {/capture}
</form>

{/capture}

{include file="common/mainbox.tpl" title=__("saving_system") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
