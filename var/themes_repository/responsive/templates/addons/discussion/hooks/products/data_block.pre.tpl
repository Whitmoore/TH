{if $show_rating}

{if $product.average_rating}
    {$average_rating = $product.average_rating}
{elseif $product.discussion.average_rating}
    {$average_rating = $product.discussion.average_rating}
{/if}

{if $rate_not_allowed}
    {if $average_rating}
        {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$average_rating|fn_get_discussion_rating is_link=false discussion=$product.discussion}
    {/if}
{elseif $product.discussion && $product.discussion.type != "D" && "CRB"|strpos:$product.discussion.type !== false && !$product.discussion.disable_adding}
    {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$average_rating|fn_get_discussion_rating is_link=false discussion=$product.discussion user_rate=$product.discussion|fn_user_rated object_type="product_`$product.category_type`" allow_rate=true}
{/if}

{/if}