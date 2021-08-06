{** block-description:discussion_title_home_page **}

{assign var="discussion" value=0|fn_get_discussion:"E":true:$block.properties}

{if $discussion && $discussion.type != "D" && $discussion.posts}

{foreach from=$discussion.posts item=post}

<div class="discussion-testimonial-post">
    <a href="{"discussion.view?thread_id=`$discussion.thread_id`&post_id=`$post.post_id`"|fn_url}#post_{$post.post_id}">
    <div>
        <span class="ty-block-discussion-post__author">{$post.name}</span>
        {if $discussion.type == "R" || $discussion.type == "B"}
            <div style="float: right;">{include file="addons/discussion/views/discussion/components/stars.tpl" stars=$post.rating_value|fn_get_discussion_rating}</div>
        {/if}
    </div>
    {if $post.city}<div class="ty-discussion-post__city">{$post.city}</div>{/if}
    {if $discussion.type == "C" || $discussion.type == "B"}
        <div class="ty-block-discussion-hp-post__message">
            "{$post.message|escape|nl2br|truncate:300:"...":true nofilter}"
        </div>
    {/if}
    </a>
</div>

{/foreach}

<a href="{"discussion.view?thread_id=`$discussion.thread_id`"|fn_url}">
    <div class="ty-block-discussion_view-all">{__("view_all")}</div>
</a>
{/if}
