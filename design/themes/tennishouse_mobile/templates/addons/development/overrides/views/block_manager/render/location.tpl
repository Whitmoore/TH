<div data-role="page" id="mobile_page">
{if $containers.header}
<div data-role="panel" data-display="push" class="tygh-header clearfix ty-left-panel hidden" id="left-panel">
    {*$containers.header nofilter*}
    {include file="addons/development/common/catalog_panel.tpl"}
</div>
<div data-role="panel" id="right-panel" class="ty-right-panel hidden" data-display="push" data-position="right">
    {include file="addons/development/common/cart_panel.tpl"}
</div>
{/if}

{if $containers.top_panel}
<div data-role="header" class="tygh-top-panel clearfix">
    {$containers.top_panel nofilter}
</div>
{/if}

{if $containers.content}
<div data-role="content" data-position-fixed="true" class="tygh-content clearfix">
    {$containers.content|fn_check_vars|fn_render_captured_blocks:$smarty.capture nofilter}
</div>
{/if}

{if $containers.footer}
<div class="tygh-footer clearfix" id="tygh_footer" data-role="footer" role="contentinfo">
    {$containers.footer nofilter}
</div>
{/if}

{if "ULTIMATE"|fn_allowed_for}
    {* Show "Entry page" *}
    {if $show_entry_page}
        <div id="entry_page"></div>
            <script type="text/javascript">
                $('#entry_page').ceDialog('open', {$ldelim}href: fn_url('companies.entry_page'), resizable: false, title: '{__("choose_your_country")}', width: 325, height: 420, dialogClass: 'entry-page'{$rdelim});
            </script>
    {/if}
{/if}

{if $smarty.request.meta_redirect_url|fn_check_meta_redirect}
    <meta http-equiv="refresh" content="1;url={$smarty.request.meta_redirect_url|fn_check_meta_redirect|fn_url}" />
{/if}
</div>