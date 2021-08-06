{foreach from=$items_tree item="item"}
    {if $item.is_virtual != 'Y'}
        <li class="ty-menu__item {if $item.subitems}dropdown-vertical__dir{/if} menu-level-{$level}">
            {assign var="item_url" value=$item|fn_form_dropdown_object_link:"menu"}
            <div class="ty-menu__submenu-item-header">
                <a{if $item_url} href="{$item_url}"{/if} {if $item.new_window}target="_blank"{/if} class="ty-menu__item-link {if $item.object_id === 'sale'}ty-sale-products{/if}">
                    <div class="ty-menu-icon {if $type == 'C'}{if $item.is_feature == 'Y'}ty-feature-icon-{$item.object_id}{else}ty-category-icon-{$item.object_id}{/if}{elseif $type == 'A'}ty-menu-icon-{$item.param_id}{/if}"></div>
                    <div class="ty-menu__submenu-item-header-text" {if $type == 'C' && $item.object_id == $smarty.const.STR_MACHINE_CATEGORY_ID}style="margin-right: -20px;"{/if}>{$item.item}</div>
                </a>
                {if $item.subitems}
                    <div class="ty-menu__item-toggle">
                        <i class="ty-icon-right-open"></i>
                        <i class="ty-icon-down-open"></i>
                    </div>
                {/if}
            </div>

            {if $item.subitems}
                <ul class="ty-menu__submenu-items">
                    {include file="addons/development/common/catalog_panel_menu.tpl" items_tree=$item.subitems level=$level+1}
                </ul>
            {/if}
        </li>
    {else}
        {include file="addons/development/common/catalog_panel_menu.tpl" items_tree=$item.subitems level=$level+1}
    {/if}
{/foreach}