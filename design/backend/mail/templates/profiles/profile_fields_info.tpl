<tr>
    <td colspan="2" class="form-title">{$title|default:"&nbsp;"}<hr size="1" noshade="noshade" /></td>
</tr>
{foreach from=$fields item=field}
{assign var="value" value=$user_data|fn_get_profile_field_value:$field}
{if $value}
<tr>
    <td style="font-style: italic;" width="300px" nowrap="nowrap">{if $field.description|strpos:__("city") !== false}{__("city")}{else}{$field.description}{/if}:&nbsp;</td>
    <td>
        {$value|default:"-"}
    </td>
</tr>
{/if}
{/foreach}