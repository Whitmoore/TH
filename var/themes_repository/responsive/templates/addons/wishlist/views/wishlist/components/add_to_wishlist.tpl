<div class="ty-add-to-wish-block" id="add_to_wishlist_{$product_id}">
    {$wishlist_id=$product_id|fn_check_wishlist}
    {$but_id="button_wishlist_`$product_id`"}
    
    {include file="addons/development/common/form_link.tpl" form_class="ty-wishlist-form cm-ajax cm-ajax-hidden" form_method="get" hidden_input=["product_id" => "{$product_id}", "result_ids" => "wishlist_status*"] link_text="" link_onclick="fn_unlike();" link_meta="{if !$wishlist_id}hidden{/if} ty-added-to-wish {if $state_changed}ty-wishlist-change-state{/if}" link_name="dispatch[wishlist.unlike]" link_id="unlike_`$but_id`" link_role=""  but_name="dispatch[wishlist.add..`$product_id`]" but_role="text"}

    {include file="addons/development/common/form_link.tpl" form_class="ty-wishlist-form cm-ajax cm-ajax-hidden" form_method="get" hidden_input=["product_id" => "{$product_id}", "result_ids" => "wishlist_status*"] link_text="" link_onclick="fn_like();" link_meta="{if $wishlist_id}hidden{/if} ty-add-to-wish {if $state_changed}ty-wishlist-change-state{/if}" link_name="dispatch[wishlist.like]" link_id="like_`$but_id`" link_role="" but_name="dispatch[wishlist.add..`$product_id`]" but_role="text"}

    <span id="likes_num_{$but_id}" class="ty-wishlist-likes {if $likes <= 0}hidden{/if}">{$likes}</span>
    <script type="text/javascript">
        function fn_unlike()
        {$ldelim}
            $('#unlike_{$but_id}').addClass('hidden');
            $('#like_{$but_id}').addClass('ty-wishlist-change-state').removeClass('hidden');
            likes = parseInt($('#likes_num_{$but_id}').html()) - 1;
            $('#likes_num_{$but_id}').html(likes);
            if (likes > 0) {
                $('#likes_num_{$but_id}').removeClass('hidden');
            } else {
                $('#likes_num_{$but_id}').addClass('hidden');
            }
        {$rdelim}
        function fn_like()
        {$ldelim}
            $('#like_{$but_id}').addClass('hidden');
            $('#unlike_{$but_id}').addClass('ty-wishlist-change-state').removeClass('hidden');
            likes = parseInt($('#likes_num_{$but_id}').html()) + 1;
            $('#likes_num_{$but_id}').html(likes);
            if (likes > 0) {
                $('#likes_num_{$but_id}').removeClass('hidden');
            } else {
                $('#likes_num_{$but_id}').addClass('hidden');
            }
        {$rdelim}
    </script>
<!--add_to_wishlist_{$product_id}--></div>
