{$location = $location|default:"top-left"}
<div class="{$class_name} ty-block-categories-wrapper">
    <div class="ty-block-categories__overlay"></div>
    <div class="ty-block-categories-{$location}">
        <a href="{"categories.view?category_id=$category_id"|fn_url}"><h2 class="ty-block-categories__item ty-block-categories__title">{$title}</h2></a>
        {if !$no_subcategories}
            {$categories = $category_id|fn_get_block_categories}
            {foreach from=$categories item="category" name="block_categories"}
                {if $smarty.foreach.block_categories.iteration < 5}
                    <div class="ty-block-categories__item"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"> - {$category.category}</a></div>
                {/if}
            {/foreach}
            <div class="ty-block-categories__item"><a href="{"categories.view?category_id=$category_id"|fn_url}"> - {__("check_all_items")}</a></div>
        {/if}
    </div>
</div>
<a href="{"categories.view?category_id=$category_id"|fn_url}"  class="{$class_name}-link"></a>
<script type="text/javascript">
    Tygh.$(document).ready(function() {$ldelim}
        $('.{$class_name}').click(function(){$ldelim}
            $('.{$class_name}-link').click();
        {$rdelim});
    {$rdelim});
</script>