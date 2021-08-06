{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="anouncements_form" class="form-horizontal form-edit {if ""|fn_check_form_permissions} cm-hide-inputs{/if}">

    <table class="table table-middle">
        <thead>
        <tr class="cm-first-sibling">
            <th width="7%">{__("priority")}</th>
            <th width="55%">{__("text")}</th>
            <th width="10%">{__("class")}</th>
            <th width="11%">{__("start_date")}</th>
            <th width="11%">{__("end_date")}</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        {foreach from=$anouncements item="anouncement" key="k" name="rdf"}
            <tr>
                <td>
                    <input type="text" name="anouncements_data[{$k}][priority]" size="3" value="{$anouncement.priority}" class="input-micro" />
                </td>
                <td>
                    <textarea name="anouncements_data[{$k}][text]" cols="55" rows="2" class="cm-wysiwyg input-large">{$anouncement.text}</textarea>
                </td>
                <td>
                    <input type="text" name="anouncements_data[{$k}][class]" size="6" value="{$anouncement.class}" class="input-small" />
                </td>
                <td>
                    {include file="common/calendar.tpl" date_id="elm_anouncement_start_`$k`" date_name="anouncements_data[{$k}][start_timestamp]" date_val=$anouncement.start_timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                </td>
                <td>
                    {include file="common/calendar.tpl" date_id="elm_anouncement_end_`$k`" date_name="anouncements_data[{$k}][end_timestamp]" date_val=$anouncement.end_timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
                </td>
                <td class="nowrap right">
                    <div class="hidden-tools">
                        {include file="buttons/remove_item.tpl" only_delete='Y' but_class="cm-delete-row"}
                    </div>
                </td>
            </tr>
        {/foreach}

        {$k = $max_key + 1}
        <tr id="box_add_saving_group">
            <td>
                <input type="text" name="anouncements_data[{$k}][priority]" size="3" value="0" class="input-micro" />
            </td>
            <td>
                <textarea name="anouncements_data[{$k}][text]" cols="55" rows="2" class="cm-wysiwyg input-large"></textarea>
            </td>
            <td>
                <input type="text" name="anouncements_data[{$k}][class]" size="6" value="" class="input-small" />
            </td>
            <td>
                {include file="common/calendar.tpl" date_id="elm_anouncement_start_`$k`" date_name="anouncements_data[{$k}][start_timestamp]" date_val=$smarty.const.TIME start_year=$settings.Company.company_start_year}
            </td>
            <td>
                {include file="common/calendar.tpl" date_id="elm_anouncement_end_`$k`" date_name="anouncements_data[{$k}][end_timestamp]" date_val=$smarty.const.TIME start_year=$settings.Company.company_start_year}
            </td>
            <td class="right"> <div class="hidden-tools">{include file="buttons/multiple_buttons.tpl" item_id="add_saving_group" tag_level=1}</div></td>
        </tr>

    </table>

    {capture name="buttons"}
        {include file="buttons/save.tpl" but_role="submit-link" but_target_form="anouncements_form" but_name="dispatch[anouncements.update]"}
    {/capture}
</form>

{/capture}

{include file="common/mainbox.tpl" title=__("anouncements") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
