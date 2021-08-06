<div class="{$class_name} ty-block-categories-wrapper">
    <div class="ty-block-categories__overlay"></div>
    <div class="ty-block-categories-first-wrapper" id="link_{$first_category_id}">
        <div class="ty-block-categories-top-left">
            <a href="{"categories.view?category_id=$first_category_id"|fn_url}"><h2 class="ty-block-categories__item ty-block-categories__title">{$first_title}</h2></a>
            {$categories = $first_category_id|fn_get_block_categories}
            {foreach from=$categories item="category" name="block_categories"}
                {if $smarty.foreach.block_categories.iteration < 4}
                    <div class="ty-block-categories__item"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"> - {$category.category}</a></div>
                {/if}
            {/foreach}
            <div class="ty-block-categories__item"><a href="{"categories.view?category_id=$first_category_id"|fn_url}"> - {__("check_all_items")}</a></div>
        </div>
    </div>
    <div class="ty-block-categories-second-wrapper" id="link_{$second_category_id}">
        <div class="ty-block-categories-top-left">
            <a href="{"categories.view?category_id=$second_category_id"|fn_url}"><h2 class="ty-block-categories__item ty-block-categories__title">{$second_title}</h2></a>
            {$categories = $second_category_id|fn_get_block_categories}
            {foreach from=$categories item="category" name="block_categories"}
                {if $smarty.foreach.block_categories.iteration < 4}
                    <div class="ty-block-categories__item"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"> - {$category.category}</a></div>
                {/if}
            {/foreach}
            <div class="ty-block-categories__item"><a href="{"categories.view?category_id=$second_category_id"|fn_url}"> - {__("check_all_items")}</a></div>
        </div>
    </div>
</div>
<a href="{"categories.view?category_id=$first_category_id"|fn_url}"  class="{$class_name}-link-first"></a>
<a href="{"categories.view?category_id=$second_category_id"|fn_url}"  class="{$class_name}-link-second"></a>
<script type="text/javascript">
    Tygh.$(document).ready(function() {$ldelim}
        $('#link_' + '{$first_category_id}').click(function(){$ldelim}
            $('.{$class_name}-link-first').click();
        {$rdelim});
        $('#link_' + '{$second_category_id}').click(function(){$ldelim}
            $('.{$class_name}-link-second').click();
        {$rdelim});
    {$rdelim});
</script>