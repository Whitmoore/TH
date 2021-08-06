{if $sms_list}

<table class="table table-middle">
<thead class="cm-first-sibling">
    <tr>
        <th width="10%">{__("phone")}</th>
        <th width="50%">{__("text")}</th>
        <th width="20%">{__("time")}</th>
        <th width="10%">{__("status")}</th>
        <th width="10%"></th>
    </tr>
</thead>
<tbody>
{foreach from=$sms_list item=sms}
    <tr>
        <td>{$sms.phone}</td>
        <td>{$sms.text}</td>
        <td>{$sms.timestamp|date_format:"`$settings.Appearance.date_format`"},{$sms.timestamp|date_format:"`$settings.Appearance.time_format`"}</td>
        <td>{$sms.sms_status}</td>
        <td>
            </td>
    </tr>
{/foreach}
</tbody>
</table>

{/if}