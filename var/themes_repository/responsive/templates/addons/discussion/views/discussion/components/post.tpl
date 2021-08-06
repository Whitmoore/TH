<form action="{""|fn_url}" method="post" class="{if !$post_redirect_url}cm-ajax cm-form-dialog-closer{/if} posts-form" name="add_post_form" id="add_post_form_{$obj_prefix}{$obj_id}">
<input type="hidden" name="result_ids" value="posts_list,new_post,average_rating*">
<input type ="hidden" name="post_data[thread_id]" value="{$discussion.thread_id}" />
<input type ="hidden" name="redirect_url" value="{$post_redirect_url|default:$config.current_url}" />
<input type="hidden" name="selected_section" value="" />

<div id="new_post_{$obj_prefix}{$obj_id}">

<div>
<div class="ty-control-group ty-inline-block" style="width: 78%;">
    <label for="dsc_name_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_name")}</label>
    <input type="text" id="dsc_name_{$obj_prefix}{$obj_id}" name="post_data[name]" value="{if $auth.user_id}{$user_info.firstname} {$user_info.lastname}{elseif $discussion.post_data.name}{$discussion.post_data.name}{/if}" size="45" class="ty-input-text" />
</div>

{if $discussion.type == "R" || $discussion.type == "B"}
<div class="ty-control-group ty-inline-block" style="width: 20%;">
    {$rate_id = "rating_`$obj_prefix``$obj_id`"}
    <label class="ty-control-group__title cm-required cm-multiple-radios">{__("your_rating")}</label>
    {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[rating_value]"}
</div>
{/if}
</div>

{hook name="discussion:add_post"}
{if $discussion.type == "C" || $discussion.type == "B"}
<div class="ty-control-group">
    <label for="dsc_message_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_message")}</label>
    <textarea id="dsc_message_{$obj_prefix}{$obj_id}" name="post_data[message]" class="ty-input-textarea ty-input-text-large" rows="5" cols="72">{$discussion.post_data.message}</textarea>
</div>
{/if}
{/hook}
{$user_info = $auth.user_id|fn_get_user_info:true}
{if $discussion.object_type == 'P'}
<div class="ty-new-post__additional-info">

<div class="ty-new-post__additional-info-note">{__("additional_info_note")}</div>
{$birthday_pf_id = $smarty.const.BIRTHDAY_PF_ID}
{$play_level_pf_id = $smarty.const.PLAY_LEVEL_PF_ID}
{$surface_pf_id = $smarty.const.SURFACE_PF_ID}
{$configuration_pf_id = $smarty.const.CONFIGURATION_PF_ID}
{$age = $user_info.fields.$birthday_pf_id|fn_get_age}

<div class="ty-new-post_additional_fields">
    <div class="ty-control-group ty-inline-block" style="width: 31%;">
        <label for="dsc_city_{$obj_prefix}{$obj_id}" class="ty-control-group__title" style="padding-bottom: 7px;">{__("city")}</label>
        <input type="text" id="dsc_city_{$obj_prefix}{$obj_id}" name="post_data[city]" value="{if $user_info.s_city}{$user_info.s_city}{elseif $discussion.post_data.city}{$discussion.post_data.city}{/if}" size="15" class="ty-input-text" />
    </div>
    <div class="ty-control-group ty-inline-block" style="width: 14%;">
        <label for="dsc_age_{$obj_prefix}{$obj_id}" class="ty-control-group__title" style="padding-bottom: 7px;">{__("age")}</label>
        <input type="text" id="dsc_age_{$obj_prefix}{$obj_id}" name="post_data[age]" value="{if $age}{$age}{elseif $discussion.post_data.age}{$discussion.post_data.age}{/if}" size="50" class="ty-input-text-short" />
    </div>
    
    <div class="ty-control-group ty-inline-block" style="width: 24%;">
        <label for="dsc_play_level_{$obj_prefix}{$obj_id}" class="ty-control-group__title">{__("play_level")}{include file="common/tooltip.tpl" tooltip=$smarty.capture.play_level_note}</label>
        <select id="dsc_play_level_{$obj_prefix}{$obj_id}" name="post_data[play_level]">
            <option value="" > - </option>
            <option value="1" {if $discussion.post_data.play_level == '1' || $user_info.fields.$play_level_pf_id == '1'}selected="selected"{/if}>1.0</option>
            <option value="2" {if $discussion.post_data.play_level == '2' || $user_info.fields.$play_level_pf_id == '2'}selected="selected"{/if}>1.5</option>
            <option value="3" {if $discussion.post_data.play_level == '3' || $user_info.fields.$play_level_pf_id == '3'}selected="selected"{/if}>2.0</option>
            <option value="4" {if $discussion.post_data.play_level == '4' || $user_info.fields.$play_level_pf_id == '4'}selected="selected"{/if}>2.5</option>
            <option value="5" {if $discussion.post_data.play_level == '5' || $user_info.fields.$play_level_pf_id == '5'}selected="selected"{/if}>3.0</option>
            <option value="6" {if $discussion.post_data.play_level == '6' || $user_info.fields.$play_level_pf_id == '6'}selected="selected"{/if}>3.5</option>
            <option value="7" {if $discussion.post_data.play_level == '7' || $user_info.fields.$play_level_pf_id == '7'}selected="selected"{/if}>4.0</option>
            <option value="8" {if $discussion.post_data.play_level == '8' || $user_info.fields.$play_level_pf_id == '8'}selected="selected"{/if}>4.5</option>
            <option value="9" {if $discussion.post_data.play_level == '9' || $user_info.fields.$play_level_pf_id == '9'}selected="selected"{/if}>5.0</option>
            <option value="10" {if $discussion.post_data.play_level == '10' || $user_info.fields.$play_level_pf_id == '10'}selected="selected"{/if}>5.5</option>
            <option value="11" {if $discussion.post_data.play_level == '11' || $user_info.fields.$play_level_pf_id == '11'}selected="selected"{/if}>6.0 - 7.0</option>
        </select>
    </div>
    <div class="ty-control-group ty-inline-block" style="width: 29%;">
        <label for="dsc_favourite_surface_{$obj_prefix}{$obj_id}" class="ty-control-group__title">{__("favourite_surface")}{include file="common/tooltip.tpl" tooltip=$smarty.capture.surface_note}</label>
        <select id="dsc_favourite_surface_{$obj_prefix}{$obj_id}" name="post_data[surface]">
            <option value="" > - </option>
            <option value="12" {if $discussion.post_data.surface == '12' || $user_info.fields.$surface_pf_id == '12'}selected="selected"{/if}>{__("hard_surface")}</option>
            <option value="13" {if $discussion.post_data.surface == '13' || $user_info.fields.$surface_pf_id == '13'}selected="selected"{/if}>{__("clay_surface")}</option>
            <option value="14" {if $discussion.post_data.surface == '14' || $user_info.fields.$surface_pf_id == '14'}selected="selected"{/if}>{__("grass_surface")}</option>
            <option value="15" {if $discussion.post_data.surface == '15' || $user_info.fields.$surface_pf_id == '15'}selected="selected"{/if}>{__("synthetic_surface")}</option>
            <option value="16" {if $discussion.post_data.surface == '16' || $user_info.fields.$surface_pf_id == '16'}selected="selected"{/if}>{__("synthetic_grass_surface")}</option>
            <option value="17" {if $discussion.post_data.surface == '17' || $user_info.fields.$surface_pf_id == '17'}selected="selected"{/if}>{__("parquet_surface")}</option>
            <option value="18" {if $discussion.post_data.surface == '18' || $user_info.fields.$surface_pf_id == '18'}selected="selected"{/if}>{__("asphalt_surface")}</option>
        </select>
    </div>
</div>
<div class="ty-control-group ty-inline-block">
    <label for="dsc_configuration_{$obj_prefix}{$obj_id}" class="ty-control-group__title" style="padding-bottom: 7px;">{__("configuration")}</label>
    <input type="text" id="dsc_configuration_{$obj_prefix}{$obj_id}" name="post_data[configuration]" value="{if $user_info.fields.$configuration_pf_id}{$user_info.fields.$configuration_pf_id}{elseif $discussion.post_data.age}{$discussion.post_data.configuration}{/if}" size="50" title="{__("racket_string_tension")}" class="ty-input-text-large cm-hint" />
</div>

</div>
{else}
    <div class="ty-control-group ty-inline-block" style="width: 31%;">
        <label for="dsc_city_{$obj_prefix}{$obj_id}" class="ty-control-group__title" style="padding-bottom: 7px;">{__("city")}</label>
        <input type="text" id="dsc_city_{$obj_prefix}{$obj_id}" name="post_data[city]" value="{if $user_info.s_city}{$user_info.s_city}{elseif $discussion.post_data.city}{$discussion.post_data.city}{/if}" size="15" class="ty-input-text" />
    </div>
{/if}
{include file="common/image_verification.tpl" option="discussion"}

<!--new_post_{$obj_prefix}{$obj_id}--></div>

<div class="buttons-container">
    {include file="buttons/button.tpl" but_text=__("submit") but_meta="ty-btn__secondary" but_role="submit" but_name="dispatch[discussion.add]"}
</div>

</form>