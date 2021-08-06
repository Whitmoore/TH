{assign var="current_redirect_url" value=$config.current_url|fn_link_attach:"selected_section=discussion"|escape:url}
{if $post.message}
<div class="summary">
    <input type="text" name="posts[{$post.post_id}][name]" value="{$post.name}" size="40" class="input-hidden ty-dm-name">
    <input type="text" name="posts[{$post.post_id}][city]" value="{$post.city}" size="20" class="input-hidden ty-dm-city">
    {if $discussion.object_type == 'P' || $discussion_object_type == 'P'}
    <input type="text" name="posts[{$post.post_id}][age]" value="{$post.age}" size="10" class="input-hidden ty-dm-age">
    <select name="posts[{$post.post_id}][play_level]" class="ty-dm-play-level">
        <option value="" > - </option>
        <option value="1" {if $post.play_level == '1'}selected="selected"{/if}>1.0</option>
        <option value="2" {if $post.play_level == '2'}selected="selected"{/if}>1.5</option>
        <option value="3" {if $post.play_level == '3'}selected="selected"{/if}>2.0</option>
        <option value="4" {if $post.play_level == '4'}selected="selected"{/if}>2.5</option>
        <option value="5" {if $post.play_level == '5'}selected="selected"{/if}>3.0</option>
        <option value="6" {if $post.play_level == '6'}selected="selected"{/if}>3.5</option>
        <option value="7" {if $post.play_level == '7'}selected="selected"{/if}>4.0</option>
        <option value="8" {if $post.play_level == '8'}selected="selected"{/if}>4.5</option>
        <option value="9" {if $post.play_level == '9'}selected="selected"{/if}>5.0</option>
        <option value="10" {if $post.play_level == '10'}selected="selected"{/if}>5.5</option>
        <option value="11" {if $post.play_level == '11'}selected="selected"{/if}>6.0 - 7.0</option>
    </select>
    <select name="posts[{$post.post_id}][surface]" class="ty-dm-surface">
        <option value="" > - </option>
        <option value="12" {if $post.surface == '12'}selected="selected"{/if}>{__("hard_surface")}</option>
        <option value="13" {if $post.surface == '13'}selected="selected"{/if}>{__("clay_surface")}</option>
        <option value="14" {if $post.surface == '14'}selected="selected"{/if}>{__("grass_surface")}</option>
        <option value="15" {if $post.surface == '15'}selected="selected"{/if}>{__("synthetic_surface")}</option>
        <option value="16" {if $post.surface == '16'}selected="selected"{/if}>{__("synthetic_grass_surface")}</option>
        <option value="17" {if $post.surface == '17'}selected="selected"{/if}>{__("parquet_surface")}</option>
        <option value="18" {if $post.surface == '18'}selected="selected"{/if}>{__("asphalt_surface")}</option>
    </select>
    {/if}

    {hook name="discussion:update_post"}
        {if $type == "C" || $type == "B"}
            <textarea name="posts[{$post.post_id}][message]" cols="80" rows="5" class="input-hidden cm-wysiwyg">{$post.message}</textarea>
        {/if}
    {/hook}
    {if $discussion.object_type == 'P' || $discussion_object_type == 'P'}
    <input type="text" name="posts[{$post.post_id}][configuration]" value="{$post.configuration}" size="40" class="input-hidden ty-dm-configuration">
    {/if}
</div>
{/if}
<div class="tools">
    <div class="pull-left">
        {if "discussion.m_delete"|fn_check_view_permissions}
            <input type="checkbox" name="delete_posts[{$post.post_id}]" id="delete_checkbox_{$post.post_id}"  class="pull-left cm-item" value="Y">
        {/if}
        <div class="hidden-tools pull-left cm-statuses">
            {if "discussion.update"|fn_check_view_permissions}
                <span class="cm-status-a {if $post.status == "D"}hidden{/if}">
                    <span class="label label-success">{__("approved")}</span>
                    <a class="cm-status-switch icon-thumbs-down cm-tooltip" title="{__("disapprove")}" data-ca-status="D" data-ca-post-id="{$post.post_id}"></a>
                </span>
                <span class="cm-status-d {if $post.status == "A"}hidden{/if}">
                    <span class="label label-important">{__("not_approved")}</span>
                    <a class="cm-status-switch icon-thumbs-up cm-tooltip" title="{__("approve")}" data-ca-status="A" data-ca-post-id="{$post.post_id}"></a>
                </span>
            {else}
                <span class="cm-status-{$post.status|lower}">
                    {if $post.status == "A"}
                        <span class="label label-success">{__("approved")}</span>
                    {else}
                        <span class="label label-important">{__("not_approved")}</span>
                    {/if}
                </span>
            {/if}
            {if "discussion.delete"|fn_check_view_permissions}
                <a class="icon-trash cm-tooltip cm-confirm" href="{"discussion.delete?post_id=`$post.post_id`&redirect_url=`$current_redirect_url`"|fn_url}" title="{__("delete")}"></a>
            {/if}
        </div>
    </div>


    <div class="pull-right">
        {if $post.user_id}
            <a href="{"profiles.update?user_id=`$post.user_id`"|fn_url}" style="float: left;margin-right: 10px;">{if $post.user_name|trim}{$post.user_name}{else}{$post.user_id}{/if}</a>
        {/if}
        <span class="muted">{$post.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"} / {__("ip_address")}:&nbsp;{$post.ip_address}</span>

        {if ($type == "R" || $type == "B") && $post.rating_value > 0}
            {if $allow_save}
                {include file="addons/discussion/views/discussion_manager/components/rate.tpl" rate_id="rating_`$post.post_id`" rate_value=$post.rating_value rate_name="posts[`$post.post_id`][rating_value]"}
            {else}
                {include file="addons/discussion/views/discussion_manager/components/stars.tpl" stars=$post.rating_value}
            {/if}
        {/if}
    </div>

    {if $show_object_link}
        <a href="{$post.object_data.url|fn_url}" class="post-object" title="{$post.object_data.description}">{$post.object_data.description}</a>
    {/if}
</div>
