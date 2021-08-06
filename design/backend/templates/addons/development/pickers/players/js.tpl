{if $view_mode == "list" || $view_mode == "mixed"}
    <tr {if !$clone}id="{$holder}_{$player_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
        <td><a href="{"players.update?player_id=`$player_id`"|fn_url}"><span>{$player_name}</span></a></td>
        <td class="nowrap">
            {if !$view_only}
                {capture name="tools_list"}
                    <li>{btn type="list" text=__("edit") href="players.update?player_id=`$player_id`"}</li>
                    <li>{btn type="list" text=__("remove") onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$player_id}', 'pl'); return false;"}</li>
                {/capture}
                <div class="hidden-tools">
                    {dropdown content=$smarty.capture.tools_list}
                </div>
            {/if}
        </td>
    </tr>
{else}
    <{if $single_line}span{else}p{/if} {if !$clone}id="{$holder}_{$player_id}" {/if}class="cm-js-item{if $clone} cm-clone hidden{/if}">
    {if !$first_item && $single_line}
        <span class="cm-comma{if $clone} hidden{/if}">,&nbsp;&nbsp;</span>
    {/if}
    <a href="{"players.update?player_id=`$player_id`"|fn_url}" class="underlined"><span>{$player_name}</span></a>
    {if !$view_only}
        {capture name="tools_list"}
            <li>{btn type="list" text=__("remove") onclick="Tygh.$.cePicker('delete_js_item', '{$holder}', '{$player_id}', 'pl'); return false;"}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
    </{if $single_line}span{else}p{/if}>
{/if}