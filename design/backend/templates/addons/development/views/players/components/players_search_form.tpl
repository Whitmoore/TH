{if $in_popup}
    <div class="adv-search">
    <div class="group">
{else}
    <div class="sidebar-row">
    <h6>{__("search")}</h6>
{/if}
<form name="player_search_form" action="{""|fn_url}" method="get" class="{$form_meta}">

{if $smarty.request.redirect_url}
<input type="hidden" name="redirect_url" value="{$smarty.request.redirect_url}" />
{/if}

{if $selected_section != ""}
<input type="hidden" id="selected_section" name="selected_section" value="{$selected_section}" />
{/if}

{if $put_request_vars}
{foreach from=$smarty.request key="k" item="v"}
{if $v && $k != "callback"}
<input type="hidden" name="{$k}" value="{$v}" />
{/if}
{/foreach}
{/if}

{capture name="simple_search"}
{$extra nofilter}
<div class="sidebar-field">
    <label for="elm_name">{__("player_name")}</label>
    <div class="break">
        <input type="text" name="player" id="elm_name" value="{$search.player}" />
    </div>
</div>
<div class="sidebar-field">
    <label for="elm_gender">{__("gender")}</label>
    <div class="break">
        <select name="gender" id="elm_gender">
            <option value="">--</option>
            <option value="M" {if $search.gender == "M"}selected="selected"{/if}>{__("male")}</option>
            <option value="F" {if $search.gender == "F"}selected="selected"{/if}>{__("female")}</option>
        </select>
    </div>
</div>
<div class="sidebar-field">
    <label for="elm_ranking">{__("atp_ranking")}</label>
    <div class="break">
        <input type="text" name="ranking" id="elm_ranking" value="{$search.ranking}" />
    </div>
</div>
{/capture}

{include file="common/advanced_search.tpl" simple_search=$smarty.capture.simple_search dispatch=$dispatch view_type="players" in_popup=$in_popup no_adv_link=true}

</form>

{if $in_popup}
</div></div>
{else}
</div><hr>
{/if}