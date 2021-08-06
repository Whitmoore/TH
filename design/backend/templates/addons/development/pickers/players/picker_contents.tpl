{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');

    $.ceEvent('on', 'ce.formpost_add_players_form', function(frm, elm) {
        var players = {};

        if ($('input.cm-item:checked', frm).length > 0) {

            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                var item = $(this).parent().siblings();
                players[id] = {
                    player_name: item.find('.player-name').text()
                };
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), players, 'pl', {
                '{player_id}': '%id',
                '{player_name}': '%item.player_name'
            });
            {/literal}
            
            $.ceNotification('show', {
                type: 'N', 
                title: _.tr('notice'), 
                message: _.tr('text_items_added'), 
                message_state: 'I'
            });
        }

        return false;        
    });
}(Tygh, Tygh.$));
</script>
{/if}

{include file="addons/development/views/players/components/players_search_form.tpl" dispatch="players.picker" extra="<input type=\"hidden\" name=\"result_ids\" value=\"pagination_`$smarty.request.data_id`\">" put_request_vars=true form_meta="cm-ajax" in_popup=true}

<form action="{$smarty.request.extra|fn_url}" method="post" data-ca-result-id="{$smarty.request.data_id}" name="add_players_form">

{include file="common/pagination.tpl" save_current_page=true div_id="pagination_`$smarty.request.data_id`"}

{if $players}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%" class="center">
        {if $smarty.request.display == "checkbox"}
        {include file="common/check_items.tpl"}</th>
        {/if}
    <th>{__("id")}</th>
    <th>{__("player_name")}</th>
    <th>{__("gender")}</th>
    <th>{__("birthday")}</th>
    <th>{__("position")}</th>
    <th class="right">{__("active")}</th>
</tr>
</thead>
{foreach from=$players item=player}
<tr>
    <td class="left">
        {if $smarty.request.display == "checkbox"}
        <input type="checkbox" name="add_players[]" value="{$player.player_id}" class="cm-item" />
        {elseif $smarty.request.display == "radio"}
        <input type="radio" name="selected_player_id" value="{$player.player_id}" />
        {/if}
    </td>
    <td>{$player.player_id}</td>
    <td><span class="player-name">{$player.player}</span></td>
    <td><span>{if $player.gender == 'M'}{__("atp")}{else}{__("wta")}{/if}</span></td>
    <td>{$player.birthday|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</td>
    <td>{$player.position}</td>
    <td class="right">{if $player.status == "D"}{__("disable")}{else}{__("active")}{/if}</td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

<div class="buttons-container">
    {if $smarty.request.display == "radio"}
        {assign var="but_close_text" value=__("choose")}
    {else}
        {assign var="but_close_text" value=__("add_players_and_close")}
        {assign var="but_text" value=__("add_players")}
    {/if}

    {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}
</div>

</form>
