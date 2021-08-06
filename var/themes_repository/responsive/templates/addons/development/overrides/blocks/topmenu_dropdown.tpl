{hook name="blocks:topmenu_dropdown"}

{if $items}
    <div class="ty-menu__items">
    <ul class="cm-responsive-menu">
        {hook name="blocks:topmenu_dropdown_top_menu"}
            <li class="ty-menu__item ty-menu__menu-btn visible-phone">
                <a class="ty-menu__item-link">
                    <i class="ty-icon-short-list"></i>
                    <span>{__("menu")}</span>
                </a>
            </li>

        {$items_count = $items|count}
        {foreach from=$items item="item1" name="item1"}
            {if $item1.type == 'C'}
                {include file="addons/development/common/catalog_top_menu.tpl"}
            {else}
                {assign var="item1_url" value=$item1|fn_form_dropdown_object_link:$block.type}
                {assign var="unique_elm_id" value=$item1_url|md5}
                {assign var="unique_elm_id" value="topmenu_`$block.block_id`_`$unique_elm_id`"}

                <li class="ty-menu__item ty-menu__item{$items_count}{if !$item1.$childs} ty-menu__item-nodrop{else} ty-menu__item-parent cm-menu-item-responsive{/if}">
                    <div class="ty-menu__item_full  cm-hover-dropdown" data-bg-shadow="true">
                    <a {if $item1_url} href="{$item1_url}"{/if} class="ty-menu__item-link {if $item1.href == 'products.sale'}ty-menu__item-link-sale{/if}">
                        {if $item1.href == 'index.php'}<div class="ty-menu__homepage-link">{/if}{if $item1.href != 'index.php'}{$item1.$name}{else}{/if}{if $item1.href == 'index.php'}</div>{/if}
                    </a>
                    {if $item1.$childs}

                        {if !$item1.$childs|fn_check_second_level_child_array:$childs}
                        {* Only two levels. Vertical output *}
                            <div class="ty-menu__submenu">
                                <ul class="ty-menu__submenu-items ty-menu__submenu-items-simple cm-responsive-menu-submenu cm-hover-dropdown-submenu">
                                    {hook name="blocks:topmenu_dropdown_2levels_elements"}

                                    {foreach from=$item1.$childs item="item2" name="item2"}
                                        {assign var="item_url2" value=$item2|fn_form_dropdown_object_link:$block.type}
                                        <li class="ty-menu__submenu-item">
                                            <a class="ty-menu__submenu-link" {if $item_url2} href="{$item_url2}"{/if}>{$item2.$name}</a>
                                        </li>
                                    {/foreach}
                                    {if $item1.show_more && $item1_url}
                                        <li class="ty-menu__submenu-item ty-menu__submenu-alt-link">
                                            <a href="{$item1_url}"
                                            class="ty-menu__submenu-alt-link">{__("text_topmenu_view_more")}</a>
                                        </li>
                                    {/if}

                                    {/hook}
                                </ul>
                            </div>
                        {else}
                            <div class="ty-menu__submenu" id="{$unique_elm_id}">
                                {hook name="blocks:topmenu_dropdown_3levels_cols"}
                                    {$submenu_width = 250 * $item1.$childs|count}
                                    {$col_with = 99 / $item1.$childs|count}
                                    <ul class="ty-menu__submenu-items cm-responsive-menu-submenu ty-menu__page-items cm-hover-dropdown-submenu" style="display: none;width: {$submenu_width}px;">
                                        {foreach from=$item1.$childs item="item2" name="item2"}
                                            <li class="ty-top-mine__submenu-col" style="width: {$col_with}%;">
                                                {assign var="item2_url" value=$item2|fn_form_dropdown_object_link:$block.type}
                                                <div class="ty-menu__submenu-item-header {if $item2.object_id == $smarty.const.SPORTS_NUTRITION_CATEGORY_ID}ty-menu__sports-nutrition{/if}">
                                                    <a{if $item2_url} href="{$item2_url}"{/if} class="ty-menu__submenu-link">{$item2.$name|upper nofilter}</a>
                                                </div>
                                                <div class="ty-menu__submenu">
                                                    <div class="ty-menu__submenu-list cm-responsive-menu-submenu">
                                                        {if $item2.$childs}
                                                            {hook name="blocks:topmenu_dropdown_3levels_col_elements"}
                                                            <ul>
                                                            {foreach from=$item2.$childs item="item3" name="item3"}
                                                                {assign var="item3_url" value=$item3|fn_form_dropdown_object_link:$block.type}
                                                                <li class="ty-menu__submenu-item">
                                                                    <a{if $item3_url} href="{$item3_url}"{/if} class="ty-menu__submenu-link">{$item3.$name}</a>
                                                                </li>
                                                            {/foreach}
                                                            {if $item2.show_more && $item2_url}
                                                                <li class="ty-menu__submenu-item ty-menu__submenu-alt-link">
                                                                    <a href="{$item2_url}" class="ty-menu__submenu-link">{__("text_topmenu_view_more")}</a>
                                                                </li>
                                                            {/if}
                                                            </ul>
                                                            {/hook}
                                                        {/if}
                                                    </div>
                                                </div>
                                            </li>
                                        {/foreach}
                                        {if $item1.show_more && $item1_url}
                                            <li class="ty-menu__submenu-dropdown-bottom"><a href="{$item1_url}">
                                                <div class="ty-menu__submenu-dropdown-bottom-block">{if $item1.show_more_text}{$item1.show_more_text}{else}{__("text_topmenu_more", ["[item]" => $item1.$name])}{/if}</div>
                                            </a></li>
                                        {/if}
                                    </ul>
                                {/hook}
                            </div>
                        {/if}
                    {/if}
                    </div>
                </li>
            {/if}
        {/foreach}
        {/hook}
    </ul>
    </div>
{/if}
{/hook}
