{$location = $location|default:"top-left"}
<div class="ty-block-categories-wrapper cm-banner">
    <picture>
        <source srcset="{$images_dir}/addons/development/{$class_name}.jpg" {*usemap="#Map-{$class_name}"*}>
        <img src="{$images_dir}/addons/development/{$class_name}.jpg" alt="Homepage banner" {*usemap="#Map-{$class_name}"*}>
    </picture>
    {*<map name="Map-{$class_name}">
        <area shape="rect" coords="0,0,392,320" href="{$link|fn_url}" alt="">
    </map>*}
    <div class="ty-block-categories__overlay"></div>
    <div class="ty-block-categories ty-block-categories-{$location}">
        <div>
            <a href="{$link|fn_url}" class="cm-banner-link"><h2 class="ty-block-categories__item ty-block-categories__title">{$title}</h2></a>
            {if $subcategories}
                {foreach from=$subcategories item="category" name="block_categories"}
                    {if $smarty.foreach.block_categories.iteration < 5}
                        <div class="ty-block-categories__item"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"> - {$category.category}</a></div>
                    {/if}
                {/foreach}
                {if $subcategories|count > 0}<div class="ty-block-categories__item"><a href="{$link|fn_url}"> - {__("check_all_items")}</a></div>{/if}
            {/if}
        </div>
        {if ($second_link && $second_title) || $second_subcategories}
            <div class="ty-block-categories-section">
                {if $second_link && $second_title}
                    <a href="{$second_link|fn_url}"><h2 class="ty-block-categories__item ty-block-categories__title">{$second_title}</h2></a>
                {/if}
                {if $second_subcategories}
                    {foreach from=$second_subcategories item="category" name="block_categories"}
                        {if $smarty.foreach.block_categories.iteration < 4}
                            <div class="ty-block-categories__item"><a href="{"categories.view?category_id=`$category.category_id`"|fn_url}"> - {$category.category}</a></div>
                        {/if}
                    {/foreach}
                    {if $second_subcategories|count > 0}<div class="ty-block-categories__item"><a href="{$second_link|fn_url}"> - {__("check_all_items")}</a></div>{/if}
                {/if}
            </div>
        {/if}
    </div>
</div>