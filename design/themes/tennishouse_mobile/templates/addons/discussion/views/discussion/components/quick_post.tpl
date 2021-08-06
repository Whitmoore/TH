<form action="{""|fn_url}" method="post" class="{if !$post_redirect_url}cm-ajax cm-form-dialog-closer{/if} posts-form" name="add_post_form" id="add_post_form_{$obj_prefix}{$obj_id}">
    <input type="hidden" name="result_ids" value="posts_list,new_post_*,average_rating*,prices_update_*">
    <input type ="hidden" name="post_data[thread_id]" value="{$discussion.thread_id}" />
    <input type ="hidden" name="redirect_url" value="{$post_redirect_url|default:$config.current_url}" />
    <input type="hidden" name="selected_section" value="" />
    {if $discussion.post_data.post_id}
        <input type="hidden" name="post_data[post_id]" value="{$discussion.post_data.post_id}" />
    {/if}

    <div id="new_post_{$obj_prefix}{$obj_id}">

    {$user_info = $auth.user_id|fn_get_user_info:true}

    {if $discussion.type == "C" || $discussion.type == "B"}
        <div class="ty-control-group">
            <label for="dsc_message_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("your_message")}</label>
            <textarea id="dsc_message_{$obj_prefix}{$obj_id}" name="post_data[message]" class="ty-input-textarea cm-show-form" placeholder="{__("write_product_review")}" autocomplete="off">{$discussion.post_data.message}</textarea>
        </div>
    {/if}
    <div class="ty-flicker-input">
        <div class="ty-control-group ty-inline-block" style="width: 49%;margin-right: 1%;">
            <label for="dsc_name_{$obj_prefix}{$obj_id}" class="ty-control-group__title cm-required">{__("reviewer_name")}</label>
            <input type="text" id="dsc_name_{$obj_prefix}{$obj_id}" name="post_data[name]" value="{if $auth.user_id}{$user_info.firstname} {$user_info.lastname}{elseif $discussion.post_data.name}{$discussion.post_data.name}{/if}" size="25" class="ty-input-text" placeholder="{__("reviewer_name")}" autocomplete="off" />
        </div><div class="ty-control-group ty-inline-block" style="width: 50%;">
            <label for="dsc_city_{$obj_prefix}{$obj_id}" class="ty-control-group__title" style="padding-bottom: 7px;">{__("city")}</label>
            <input type="text" id="dsc_city_{$obj_prefix}{$obj_id}" name="post_data[city]" value="{if $user_info.s_city}{$user_info.s_city}{elseif $discussion.post_data.city}{$discussion.post_data.city}{/if}" size="25" class="ty-input-text" placeholder="{__("city")}" autocomplete="off" />
        </div>
    </div>
    {if $discussion.thread_id == $smarty.const.REVIEWS_THREAD_ID}
        {include file="common/image_verification.tpl" option="discussion"}
    {/if}
    <div class="ty-flicker-input">
        {if $discussion.type == "R" || $discussion.type == "B"}
            <div class="ty-control-group ty-inline-block ty-product-review-rate">
                {$rate_id = "rating_`$obj_prefix``$obj_id`"}
                <label class="ty-control-group__title cm-required cm-multiple-radios">{__("your_rating")}</label>
                {include file="addons/discussion/views/discussion/components/rate.tpl" rate_id=$rate_id rate_name="post_data[rating_value]" rating_value=$discussion.post_data.rating_value}
            </div>
        {/if}
        <div class="ty-product-review-submit">
            {include file="buttons/button.tpl" but_text=__("submit") but_meta="ty-btn__review-product" but_role="submit" but_name="dispatch[discussion.add]"}
        </div>
    </div>

    <!--new_post_{$obj_prefix}{$obj_id}--></div>

</form>
