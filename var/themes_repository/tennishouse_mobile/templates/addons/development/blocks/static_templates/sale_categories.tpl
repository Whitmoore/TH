{if $categories}
<ul class="ty-text-links ty-sale-categories">
    {foreach from=$categories item="category"}
    <li class="ty-text-links__item {if $search.cd == $category.category_id}ty-text-links__item-active{/if}"><a class="ty-text-links__a" href="{"products.sale?cd=`$category.category_id`"|fn_url}">{$category.category}</a></li>
    {/foreach}
</ul>
{/if}