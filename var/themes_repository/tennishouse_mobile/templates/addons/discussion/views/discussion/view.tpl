{if !$discussion}
    {assign var="discussion" value=$object_id|fn_get_discussion:$object_type:true:$smarty.request}
{/if}
{if $object_type == "P"}
{$new_post_title = __("write_review")}
{else}
{$new_post_title = __("new_post")}
{/if}
{if $discussion && $discussion.type != "D"}
        <div class="discussion-block" id="{if $container_id}{$container_id}{else}content_discussion{/if}">
        {if $wrap == true}
            {capture name="content"}
            {include file="common/subheader.tpl" title=$title}
        {/if}

        {if $subheader}
            <h4>{$subheader}</h4>
        {/if}

        {if $discussion.object_type == 'E'}
            {if $discussion.thread_id = $smarty.const.REVIEWS_THREAD_ID}
            <div class="ty-rate-us-ym-reviews">
                <a href="http://clck.yandex.ru/redir/dtype=stred/pid=47/cid=2508/*http://market.yandex.ru/shop/292708/reviews" target="_blank"><img src="https://clck.yandex.ru/redir/dtype=stred/pid=47/cid=2507/*https://grade.market.yandex.ru/?id=292708&action=image&size=2" border="0" width="150" height="101" alt="Читайте отзывы покупателей и оценивайте качество магазина на Яндекс.Маркете" /></a>
            </div>
            {/if}
        {/if}
        <div id="posts_list">
            {if $discussion.posts}
                {capture name="play_level_note"}
                    <a href="{"pages.view?page_id=42"|fn_url}" target="_blank">{__("how_to_know_your_game_level")}</a>
                {/capture}
                {capture name="surface_note"}
                    <a href="{"pages.view?page_id=43"|fn_url}" target="_blank">{__("variery_court_surfaces")}</a>
                {/capture}
                
                {*include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search*}
                {foreach from=$discussion.posts item=post}
                    <div class="ty-discussion-post__content">
                        {hook name="discussion:items_list_row"}
                        <span class="ty-discussion-post__author">{$post.name}</span>
                        {if $post.age}<span>, {$post.age|fn_show_age}</span>{/if}
                        {if $post.city}<span>, {$post.city}</span>{/if}
                        <span class="ty-discussion-post__date">{$post.timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}</span>
                        <div class="ty-discussion-post {cycle values=", ty-discussion-post_even"}" id="post_{$post.post_id}">
                            <span class="ty-caret"> <span class="ty-caret-outer"></span> <span class="ty-caret-inner"></span></span>

                            {if $discussion.type == "R" || $discussion.type == "B" && $post.rating_value > 0}
                                <div class="clearfix ty-discussion-post__rating">
                                    {include file="addons/discussion/views/discussion/components/stars.tpl" stars=$post.rating_value|fn_get_discussion_rating}
                                </div>
                            {/if}

                            {if $discussion.type == "C" || $discussion.type == "B"}
                                <div class="ty-discussion-post__message">{$post.message|nl2br nofilter}</div>
                            {/if}
                    
                            {if $post.play_level || $post.surface || $post.configuration}
                                <div class="ty-post-additionl-info">
                                    {if $post.play_level}
                                        <div class="ty-inline-block">{__("play_level")}{include file="common/tooltip.tpl" tooltip=$smarty.capture.play_level_note}: {if $post.play_level == '1'}1.0{elseif $post.play_level == '2'}1.5{elseif $post.play_level == '3'}2.0{elseif $post.play_level == '4'}2.5{elseif $post.play_level == '5'}3.0{elseif $post.play_level == '6'}3.5{elseif $post.play_level == '7'}4.0{elseif $post.play_level == '8'}4.5{elseif $post.play_level == '9'}5.0{elseif $post.play_level == '10'}5.5{elseif $post.play_level == '11'}6.0 - 7.0{/if}</div>
                                    {/if}
                                    {if $post.surface}
                                        <div class="ty-inline-block">{__("favourite_surface")}{include file="common/tooltip.tpl" tooltip=$smarty.capture.surface_note}: {if $post.surface == '12'}{__("hard_surface")}{elseif $post.surface == '13'}{__("clay_surface")}{elseif $post.surface == '14'}{__("grass_surface")}{elseif $post.surface == '15'}{__("synthetic_surface")}{elseif $post.surface == '16'}{__("synthetic_grass_surface")}{elseif $post.surface == '17'}{__("parquet_surface")}{elseif $post.surface == '18'}{__("asphalt_surface")}{/if}</div>
                                    {/if}
                                    {if $post.configuration}
                                    <div>
                                        {__("configuration")}: {$post.configuration}
                                    </div>
                                    {/if}
                                </div>
                            {/if}
                    
                        </div>
                        {/hook}
                    </div>
                {/foreach}


                {*include file="common/pagination.tpl" id="pagination_contents_comments_`$object_id`" extra_url="&selected_section=discussion" search=$discussion.search*}
            {else}
                {*<p class="ty-no-items">{__("no_posts_found")}</p>*}
            {/if}
        <!--posts_list--></div>

        {if $wrap == true}
            {/capture}
            {$smarty.capture.content nofilter}
        {else}
            {capture name="mainbox_title"}{$title}{/capture}
        {/if}
        {if "CRB"|strpos:$discussion.type !== false && !$discussion.disable_adding && !$hide_new_post}
            <div class="ty-product-add-review-wrapper" id="product_review_block">
                <div class="ty-product-add-review">
                    <div class="ty-product-review-title">{if $discussion.object_type == 'P'}{__("used_this_product_`$product.category_type`")}{else}{__("q_used_our_service")}{/if}</div>
                    {include file="addons/discussion/views/discussion/components/quick_post.tpl"}
                </div>
            </div>
            <script type="text/javascript">
            (function(_, $) {
                function fn_hide_form(event) {
                    if (event.target.id != "product_review_block" && !$(event.target).parents("#product_review_block").size()) { 
                        $('#product_review_block div').removeClass('ty-product-add-review-is-focus');
                        $('#dsc_message_{$obj_prefix}{$obj_id}').focus(function(e){
                            fn_show_form(e);
                        });
                        $(this).unbind( event )
                    }
                }
                function fn_show_form(event)
                {
                    $('#product_review_block div').addClass('ty-product-add-review-is-focus');
                    $("body").click(function(e) {
                        fn_hide_form(e);
                    });
                    $(this).unbind( event )
                }
                $('#dsc_message_{$obj_prefix}{$obj_id}').focus(function(e){
                    fn_show_form(e);
                });
            }(Tygh, Tygh.$));
            </script>
        {/if}
    </div>
{/if}