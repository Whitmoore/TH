{$active_tabs = []}
{capture name="tabsbox"}
    {foreach from=$tabs item="tab" key="tab_id"}
        {if $tab.show_in_popup != "Y" && $tab.status == "A"}
            {assign var="tab_content_capture" value="tab_content_capture_`$tab_id`"}

            {capture name=$tab_content_capture}
                {if $tab.tab_type == 'B'}
                    {render_block block_id=$tab.block_id dispatch="products.view"}
                {elseif $tab.tab_type == 'T'}
                    {include file=$tab.template product_tab_id=$tab.html_id}
                {/if}
            {/capture}

            {if $smarty.capture.$tab_content_capture|trim}
                {if $settings.Appearance.product_details_in_tab == "N"}
                    <h3 class="tab-list-title">{$tab.name}</h3>
                {/if}
                {$html_id = $tab.html_id}
                {$active_tabs[] = $navigation.tabs.$html_id}
                {$navigation.tabs.$html_id.display = true}
                <div id="content_{$tab.html_id}" class="ty-wysiwyg-content content-{$tab.html_id}">
                    {$smarty.capture.$tab_content_capture nofilter}
                </div>
            {/if}
        {/if}
    {/foreach}
{/capture}

{capture name="tabsbox_content"}
{if $settings.Appearance.product_details_in_tab == "Y"}
    {include file="addons/development/common/product_tabsbox.tpl" content=$smarty.capture.tabsbox }
{else}
    {$smarty.capture.tabsbox nofilter}
{/if}
{/capture}