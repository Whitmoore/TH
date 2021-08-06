{if $show_tooltip}
    <span class="ty-tooltip-block">
        {capture name="category_note"}
            <div class="tooltip-content">{__('ssl_seal_text')}</div>
        {/capture}
        <a class="cm-tooltip" data-ceTooltipPosition="ssl" title="{$smarty.capture.category_note}">
{else}
    {*<a href="https://www.rapidssl.com" target="_blank" style="outline: none;">*}
{/if}
<div class="ty-ssl-cert" id="ssl-cert"></div>
{if $show_tooltip}
        </a>
    </span>
{else}
    {*</a>*}
{/if}

{if $smarty.session.display_ssl_tooltip == 'Y'}
<script type="text/javascript">
    Tygh.$(document).ready(function() {$ldelim}
        setTimeout(function() {$ldelim}
            $('#ssl-cert').trigger('mouseover');
        {$rdelim}, 300);
        setTimeout(function() {$ldelim}
            $('#ssl-cert').trigger('mouseleave');
        {$rdelim}, 5000);
    {$rdelim});
</script>
{/if}