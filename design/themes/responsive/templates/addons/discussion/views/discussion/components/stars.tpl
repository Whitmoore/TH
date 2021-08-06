{if $allow_rate}
    {if !$user_rate}
    <span class="ty-rate-product">{__("rate_`$object_type`")}</span>
    {/if}
    <form action="{""|fn_url}" method="post" name="rate_product_form" class="ty-rate-product-form cm-ajax" id="av_rating_form">
    <input type="hidden" name="result_ids" value="posts_list,new_post,average_rating*">
    <input type ="hidden" name="post_data[thread_id]" value="{$discussion.thread_id}" />
    <input type ="hidden" name="redirect_url" value="{$post_redirect_url|default:$config.current_url}" />
    <input type="hidden" name="selected_section" value="" />
    <input type="hidden" name="force_review" value="Y" />
    {*if $user_rate && $user_rate.post_id}
        <input type ="hidden" name="post_data[post_id]" value="{$user_rate.post_id}" />
    {/if*}
        <div class="ty-rating" id="{$rate_id}">
            {$rate_id = "av_rating_`$obj_prefix``$obj_id`"}
            {foreach from =""|fn_get_discussion_ratings item="title" key="val" name="av_rating"}
            {$item_rate_id = "`$rate_id`_`$val`"}
            <input type="radio" id="{$item_rate_id}" class="ty-rating__check" name="post_data[rating_value]" {*if $user_rate && $user_rate.rating_value == $val}checked="checked"{/if*} value="{$val}" onclick="$('#submit_{$rate_id}').click();" autocomplete="off" /><label class="ty-rating__label {if $smarty.foreach.av_rating.iteration <= $stars.empty}ty-icon-star-empty{elseif $smarty.foreach.av_rating.iteration  == $stars.empty + 1 && $stars.part}ty-icon-star-half{elseif $smarty.foreach.av_rating.total >= $smarty.foreach.av_rating.iteration}ty-icon-star{/if}" for="{$item_rate_id}" title="{$title}">{$title}</label>
            {/foreach}
        </div>
        {include file="buttons/button.tpl" but_text="" but_name="dispatch[discussion.add]" but_meta="hidden" but_id="submit_`$rate_id`" but_role=""}
    </form>
{else}
    <span class="ty-nowrap ty-stars">
        {if $is_link}{if $runtime.mode == "view" && $runtime.controller == "products"}<a class="cm-external-click" data-ca-scroll="content_discussion" data-ca-external-click-id="discussion">{else}<a href="{"products.view?product_id=`$product.product_id`"|fn_url}">{/if}{/if}
        {section name="full_star" loop=$stars.full}<i class="ty-stars__icon ty-icon-star"></i>{/section}{if $stars.part}<i class="ty-stars__icon ty-icon-star-half"></i>{/if}{section name="full_star" loop=$stars.empty}<i class="ty-stars__icon ty-icon-star-empty"></i>{/section}
        {if $is_link}</a>{/if}
    </span>
{/if}