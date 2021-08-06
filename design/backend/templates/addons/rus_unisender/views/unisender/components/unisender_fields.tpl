{assign var="key" value="0"}

<table class="table">
<thead class="cm-first-sibling">
<tr>
    <th>{__("addons.rus_unisender.field_name")}</th>
    <th>{__("addons.rus_unisender.field_unisender")}</th>
    <th class="center">{__("active")}</th>
    <th>&nbsp;</th>
</tr>
</thead>

<tbody>
{if $fields}
{foreach from=$fields item="field" key="key"}
<tr class="cm-row-item">
    <td>
        <select name="unisender_data[fields][{$key}][field]">
            <option value=""></option>
            <optgroup label="{__("contact_information")}">
                {foreach from=$profile_fields['C'] item="profile_field"}
                    {if $profile_field.profile_show == 'Y' || $profile_field.checkout_show == 'Y' || $profile_field.partner_required == 'Y'}
                        <option {if $field.field == $profile_field.field_name}selected="selected"{/if} value="{$profile_field.field_name}">{$profile_field.description}</option>
                    {/if}
                {/foreach}
            </optgroup>

            <optgroup label="{__("billing_address")}">
                {foreach from=$profile_fields['B'] item="profile_field"}
                    {if $profile_field.profile_show == 'Y' || $profile_field.checkout_show == 'Y' || $profile_field.partner_required == 'Y'}
                        <option {if $field.field == $profile_field.field_name}selected="selected"{/if} value="{$profile_field.field_name}">{$profile_field.description}</option>
                    {/if}
                {/foreach}
            </optgroup>

            <optgroup label="{__("shipping_address")}">
                {foreach from=$profile_fields['S'] item="profile_field"}
                    {if $profile_field.profile_show == 'Y' || $profile_field.checkout_show == 'Y' || $profile_field.partner_required == 'Y'}
                        <option {if $field.field == $profile_field.field_name}selected="selected"{/if} value="{$profile_field.field_name}">{$profile_field.description}</option>
                    {/if}
                {/foreach}
            </optgroup>
        </select>
    </td>
    <td>
        {if $unisender_fields}
            <select name="unisender_data[fields][{$key}][unisender_field]">
                <option value=""></option>
                {foreach from=$unisender_fields item="unisender_field"}
                    <option {if $field.unisender_field == $unisender_field.name}selected="selected"{/if} value="{$unisender_field.name}">{$unisender_field.name}</option>
                {/foreach}
            </select>
        {else}
            <select name="unisender_data[fields][{$key}][unisender_field]">
                <option value=""></option>
                {if $field.unisender_field}<option selected="selected" value="{$field.unisender_field}">{$field.unisender_field}</option>{/if}
            </select>
        {/if}
    </td>

    <td class="center">
        <input type="hidden" name="unisender_data[fields][{$key}][status]" value="N" />
        <input type="checkbox" name="unisender_data[fields][{$key}][status]" value="Y" {if $field.status == "Y"}checked="checked"{/if} /></td>
        <input type="hidden" name="unisender_data[fields][{$key}][field_id]" value={$field.field_id} />
    <td>{include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}</td>
</tr>
{/foreach}
{/if}

{math equation="x + 1" x=$key assign="key"}

<tr id="box_add_unisender_fields">
    <td>
        <select name="unisender_data[fields][{$key}][field]">
            <option value=""></option>
            <optgroup label="{__("contact_information")}">
                {foreach from=$profile_fields['C'] item="field"}
                    {if $field.profile_show == 'Y' || $field.checkout_show == 'Y' || $field.partner_required == 'Y'}
                    <option value="{$field.field_name}">{$field.description}</option>
                    {/if}
                {/foreach}
            </optgroup>

            <optgroup label="{__("billing_address")}">
                {foreach from=$profile_fields['B'] item="field"}
                    {if $field.profile_show == 'Y' || $field.checkout_show == 'Y' || $field.partner_required == 'Y'}
                    <option value="{$field.field_name}">{$field.description}</option>
                    {/if}
                {/foreach}
            </optgroup>

            <optgroup label="{__("shipping_address")}">
                {foreach from=$profile_fields['S'] item="field"}
                    {if $field.profile_show == 'Y' || $field.checkout_show == 'Y' || $field.partner_required == 'Y'}
                    <option value="{$field.field_name}">{$field.description}</option>
                    {/if}
                {/foreach}
            </optgroup>
        </select>
    </td>
    <td>
        {if $unisender_fields}
            <select name="unisender_data[fields][{$key}][unisender_field]">
                <option value=""></option>
                {foreach from=$unisender_fields item="field"}
                    <option value="{$field.name}">{$field.name}</option>
                {/foreach}
            </select>
        {/if}
    </td>
    <td class="center">
        <input type="hidden" name="unisender_data[fields][{$key}][status]" value="N" />
        <input type="checkbox" name="unisender_data[fields][{$key}][status]" value="Y" checked="checked" />
    </td>

    <td>
        {include file="buttons/multiple_buttons.tpl" item_id="add_unisender_fields"}
    </td>
</tr>
</tbody>
</table>