{** block-description:discussion_homepage **}

{assign var="discussion" value=0|fn_get_discussion:"E":true:$block.properties}

{if $discussion && $discussion.type != "D" && $discussion.posts}
    <div class="ty-discussion-posts">
        {$item_width = 99 / $discussion.posts|count}
        {foreach from=$discussion.posts item=post}
            <a href="{"discussion.view?thread_id=`$discussion.thread_id`"|fn_url}">
            <div class="ty-discussion-post__item" style="width: {$item_width}%">
                <div class="ty-discussion-hp-post" id="post_{$post.post_id}">
                    {if $discussion.type == "C" || $discussion.type == "B"}
                        <div class="ty-discussion-hp-post__message"><span class="ty-quote-left">“</span>{$post.message|escape|nl2br|truncate:145:"...":true nofilter nofilter}<span class="ty-quote-right">”</span></div>
                    {/if}
                </div>
                <span class="ty-discussion-post__author">{$post.name}</span>
                {if $post.city}<span class="ty-discussion-post__city">, {$post.city}</span>{/if}
            </div>
            </a>
        {/foreach}
        <div class="ty-check-all__block-link">
            {include file="addons/development/common/form_link.tpl" form_method="post" hidden_input=["redirect_url" => "discussion.view?thread_id=`$discussion.thread_id`"] link_text=__("check_all_items")|upper link_meta="ty-button-link ty-view-all-link" link_name="dispatch[development.redirect]" link_role=""}
        </div>
    </div>
{/if}
