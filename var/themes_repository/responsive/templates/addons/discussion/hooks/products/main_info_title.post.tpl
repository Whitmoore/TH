{*if $product.discussion_type && $product.discussion_type != 'D'}
    <div class="ty-discussion__rating-wrapper" id="average_rating_product">
        {assign var="rating" value="rating_`$obj_id`"}{$smarty.capture.$rating nofilter}

        {if $product.discussion.posts}
        <a class="ty-discussion__review-a cm-external-click" data-ca-scroll="content_discussion" data-ca-external-click-id="discussion">({$product.discussion.posts|count})</a>
        {/if}
    <!--average_rating_product--></div>
{/if*}
