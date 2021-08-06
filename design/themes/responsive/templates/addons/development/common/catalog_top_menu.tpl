{assign var="item1_url" value=$item1|fn_form_dropdown_object_link:$block.type}
{assign var="unique_elm_id" value=$item1_url|md5}
{assign var="unique_elm_id" value="topmenu_`$block.block_id`_`$unique_elm_id`"}

<li class="ty-menu__catalog ty-menu__item  ty-menu__item{$items_count}{if !$item1.$childs} ty-menu__item-nodrop{else} ty-menu__item-parent cm-menu-item-responsive{/if}">
    <div class="ty-menu__item_full cm-hover-dropdown" data-bg-shadow="true">
    <a {if $item1_url} href="{$item1_url}"{/if} class="ty-menu__item-link">
        <div class="ty-menu__catalog-link">
            {*<div class="ty-menu-container__icon"></div>*}
            {$item1.$name}
        </div>
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
                    <ul class="ty-menu__submenu-items cm-responsive-menu-submenu ty-menu__catalog-items cm-hover-dropdown-submenu" style="display: none;">
                        {foreach from=$item1.$childs item="item2" name="item2"}
                            <li class="ty-top-mine__submenu-col">
                                {assign var="item2_url" value=$item2|fn_form_dropdown_object_link:$block.type}
                                <div class="ty-menu__submenu-item-header">
                                    <a{if $item2_url} href="{$item2_url}"{/if} class="ty-menu__submenu-link">
                                        <div class="ty-menu-icon ty-category-icon-{$item2.object_id}"></div>
                                        <div class="ty-menu__submenu-item-header-text">{$item2.$name nofilter}</div>
                                    </a>
                                </div>
                                {if $item2.$childs}
                                    <div class="ty-menu__submenu">
                                        {$submenu_width = 250 * $item2.$childs|count}
                                        <div class="ty-menu__submenu-list cm-responsive-menu-submenu" style="width: {$submenu_width}px;">
                                            {hook name="blocks:topmenu_dropdown_3levels_col_elements"}
                                            {$col_with = 99 / $item2.$childs|count}
                                            {foreach from=$item2.$childs item="item3" name="item3"}
                                                {assign var="item3_url" value=$item3|fn_form_dropdown_object_link:$block.type}
                                                <div class="ty-menu__submenu-section" style="width: {$col_with}%;">
                                                    {*<div class="ty-menu__submenu-item-subheader">
                                                        <a{if $item3_url} href="{$item3_url}"{/if} class="ty-menu__submenu-link">
                                                            {$item3.$name|upper nofilter}
                                                        </a>
                                                        {include file="addons/development/common/tooltip.tpl" note_url=$item3.note_url note_text=$item3.note_text}
                                                    </div>*}
                                                    <div class="ty-menu__menu-subheader-items">
                                                    <ul>
                                                    {foreach from=$item3.$childs item="item4" name="item4"}
                                                        {assign var="item4_url" value=$item4|fn_form_dropdown_object_link:$block.type}
                                                        <li class="ty-menu__submenu-item">
                                                            <a{if $item4_url} href="{$item4_url}"{/if} class="ty-menu__submenu-link">{$item4.$name}</a>
                                                            {include file="addons/development/common/tooltip.tpl" note_url=$item4.note_url note_text=$item4.note_text}
                                                        </li>
                                                    {/foreach}
                                                    {if $item2.show_more && $item2_url}
                                                        <li class="ty-menu__submenu-item ty-menu__submenu-alt-link">
                                                            <a href="{$item2_url}" class="ty-menu__submenu-link">{__("text_topmenu_view_more")}</a>
                                                        </li>
                                                    {/if}
                                                    </ul>
                                                    </div>
                                                </div>
                                            {/foreach}
                                            {/hook}
                                        </div>
                                    </div>
                                {/if}
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
