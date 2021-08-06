{if $mode == 'checkout' || $mode == 'cart'}
    <div id="mswidget" class="ms-widget-modal"></div>
    {script src="js/addons/rus_multiship/func.js"}
    <script src="https://multiship.ru/widget/loader?resource_id={$addons.rus_multiship.client_id}&sid={$addons.rus_multiship.sid}&key={$addons.rus_multiship.widget_key}"></script>
{/if}
