{if !empty($addons.rus_multiship.client_id) && !empty($addons.rus_multiship.sid) && !empty($addons.rus_multiship.widget_key)}
    <div id="mswidget" class="ms-widget-modal"></div>

    <script type="text/javascript" src="https://api-maps.yandex.ru/2.0/?load=package.standard,package.geoQuery&lang=ru-RU"></script>
    <script src="https://multiship.ru/widget/loader?resource_id={$addons.rus_multiship.client_id}&sid={$addons.rus_multiship.sid}&key={$addons.rus_multiship.widget_key}"></script>
    {script src="js/addons/rus_multiship/func.js"}
{/if}
