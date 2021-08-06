{if $player_data}
    {assign var="id" value=$player_data.player_id}
{else}
    {assign var="id" value=0}
{/if}

{** players section **}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="players_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="player_id" value="{$id}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

{capture name="tabsbox"}
<div id="content_detailed">
    <div class="control-group">
        <label for="elm_player_name" class="control-label cm-required">{__("player_name")}</label>
        <div class="controls">
        <input type="text" name="player_data[player]" id="elm_player_name" value="{$player_data.player}" size="25" class="input-long" /></div>
    </div>

    <div class="control-group">
        <label for="elm_player_en" class="control-label cm-required">{__("player_name_en")}</label>
        <div class="controls">
        <input type="text" name="player_data[player_en]" id="elm_player_en" value="{$player_data.player_en}" size="25" class="input-long" /></div>
    </div>

    <div class="control-group" id="player_photo">
        <label class="control-label">{__("photo")}</label>
        <div class="controls">
            {include file="common/attach_images.tpl" image_name="player_main" image_object_type="player" image_pair=$player_data.main_pair image_object_id=$id no_detailed=false hide_titles=true}
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label">{__("bg_image")}:</label>
        <div class="controls">
            {include file="common/attach_images.tpl" image_name="player_bg" image_object_type="player" image_pair=$player_data.bg_image image_object_id=$id  hide_titles=true no_thumbnail=true image_type="B"}
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_gender" class="control-label cm-required">{__("gender")}</label>
        <div class="controls">
        <select name="player_data[gender]" id="elm_player_gender">
            <option {if $player_data.gender == "M"}selected="selected"{/if} value="M">{__("male")}</option>
            <option {if $player_data.gender == "F"}selected="selected"{/if} value="F">{__("female")}</option>
        </select>
        </div>
    </div>
    
    <div class="control-group">
        <label for="elm_player_ranking" class="control-label">{__("ranking")}</label>
        <div class="controls">
            <input type="text" name="player_data[ranking]" id="elm_player_height" value="{$player_data.ranking}" size="3" class="input-small"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_birthday" class="control-label">{__("birthday")}</label>
        <div class="controls">
            {include file="common/calendar.tpl" date_id="elm_player_birthday" date_name="player_data[birthday]" date_val=$player_data.birthday start_year="1950" end_year="0"}
        </div>
    </div>
    
    <div class="control-group">
        <label for="elm_player_birthplace" class="control-label">{__("birthplace")}</label>
        <div class="controls">
            <input type="text" name="player_data[birthplace]" id="elm_player_birthplace" value="{$player_data.birthplace}" size="25" class="input-long"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_residence" class="control-label">{__("residence")}</label>
        <div class="controls">
            <input type="text" name="player_data[residence]" id="elm_player_residence" value="{$player_data.residence}" size="25" class="input-long"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_height" class="control-label">{__("player_height")}</label>
        <div class="controls">
            <input type="text" name="player_data[height]" id="elm_player_height" value="{$player_data.height}" size="3" class="input-small"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_weight" class="control-label">{__("weight")}</label>
        <div class="controls">
            <input type="text" name="player_data[weight]" id="elm_player_weight" value="{$player_data.weight}" size="3" class="input-small"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_plays" class="control-label">{__("player_plays")}</label>
        <div class="controls">
            <select name="player_data[plays]" id="elm_player_plays">
                <option {if $player_data.plays == "R"}selected="selected"{/if} value="R">{__("righty")}</option>
                <option {if $player_data.plays == "L"}selected="selected"{/if} value="L">{__("lefty")}</option>
            </select>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_turned_pro" class="control-label">{__("turned_pro")}</label>
        <div class="controls">
            <input type="text" name="player_data[turned_pro]" id="elm_player_turned_pro" value="{$player_data.turned_pro}" size="5" class="input-small"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_titles" class="control-label">{__("titles")}</label>
        <div class="controls">
            <input type="text" name="player_data[titles]" id="elm_player_titles" value="{$player_data.titles}" size="5" class="input-small"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_coach" class="control-label">{__("coach")}</label>
        <div class="controls">
            <input type="text" name="player_data[coach]" id="elm_player_coach" value="{$player_data.coach}" size="25" class="input-long"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_website" class="control-label">{__("website")}</label>
        <div class="controls">
            <input type="text" name="player_data[website]" id="elm_player_website" value="{$player_data.website}" size="25" class="input-long"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_rss_link" class="control-label">{__("rss_link")}</label>
        <div class="controls">
            <input type="text" name="player_data[rss_link]" id="elm_player_rss_link" value="{$player_data.rss_link}" size="25" class="input-long"/>
        </div>
    </div>

    <div class="control-group">
        <label for="elm_player_data_link" class="control-label">{__("link")}</label>
        <div class="controls">
            <input type="text" name="player_data[data_link]" id="elm_player_data_link" value="{$player_data.data_link}" size="25" class="input-long"/>
            {if $player_data.data_link}<a href="{$player_data.data_link}" target="_blank">{$player_data.data_link}</a>{/if}
        </div>
    </div>
    
    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="elm_player_data_descr">{__("description")}:</label>
        <div class="controls">
            <textarea id="elm_player_data_descr" name="player_data[description]" cols="55" rows="8" class="cm-wysiwyg input-large">{$player_data.description}</textarea>
        </div>
    </div>

    {include file="common/select_status.tpl" input_name="player_data[status]" id="elm_player_status" obj_id=$id obj=$player_data hidden=true}
    
</div>

<div id="content_products">
    {include file="pickers/products/picker.tpl" data_id="added_products" input_name="player_data[gear]" no_item_text=__("text_no_items_defined", ["[items]" => __("products")]) type="links" placement="right" item_ids=$player_data.gear}
</div>

<div id="content_addons">
{hook name="players:detailed_content"}
{/hook}
</div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}
</form>

{capture name="buttons"}
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="players_form" but_name="dispatch[players.update]"}
    {else}
        {$view_uri = "players.view?player_id=`$id`"|fn_get_preview_url:$player_data:$auth.user_id}
        {$ui_uri = "players.update_info?player_id=`$id`"|fn_url}

        {capture name="tools_list"}
            <li>{btn type="list" target="_blank" text=__("preview") href=$view_uri}</li>
            <li>{btn type="list" class="cm-ajax" text=__("update_player_info") href=$ui_uri}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[players.update]" but_role="submit-link" but_target_form="players_form" save=$id}
    {/if}
{/capture}
    
{/capture}

{if !$id}
    {assign var="title" value=__("players.new_player")}
{else}
    {assign var="title" value="{__("players.editing_player")}: `$player_data.player`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

{** player section **}
