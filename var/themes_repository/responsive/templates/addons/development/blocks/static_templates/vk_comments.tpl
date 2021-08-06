{script src="js/addons/development/open_api.js"}
<div id="vk_comments"></div>
<script type="text/javascript">
var app_id = "{$addons.development.application_id}";
{literal}
VK.init({
    apiId: app_id,
    onlyWidgets: true
});
VK.Widgets.Comments('vk_comments');
{/literal}
</script>
