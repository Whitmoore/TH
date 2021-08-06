{*if $quick_view && $product.discussion_type && $product.discussion_type != 'D'}
    {include file="addons/discussion/views/discussion/components/new_post.tpl" new_post_title=__("write_review") discussion=$product.discussion post_redirect_url="products.view?product_id=`$product.product_id`&selected_section=discussion#discussion"|fn_url}
{/if}
{if $product.discussion.posts}
<div class="ty-product-reviews-block">
    <h2 class="ty-product-reviews-title">{__("product_reviews_title")}</h2>
    {include file="addons/discussion/views/discussion/view.tpl" object_id=$product.product_id object_type="P" title=__("discussion_title_product") quicklink="disussion_link" container_id="content_discussion_block" hide_scrollbar=true hide_new_post=true discussion=$product.discussion}
</div>
{/if*}