{capture name="mainbox"}

<form action="{""|fn_url}" method="post" name="regenerate_cities" enctype="multipart/form-data" class="cm-ajax form-horizontal form-edit">

<div class="control-group">
    <label class="control-label">{__("select_file")}:</label>
    <div class="controls">{include file="common/fileuploader.tpl" var_name="csv_file[0]"}</div>
</div>
{capture name="buttons"}
    <div class="cm-tab-tools" id="tools">
        {include file="buttons/button.tpl" but_text=__("update") but_name="dispatch[rus_sdek.regenerate_cities]" but_role="submit-link" but_target_form="regenerate_cities" but_meta="cm-tab-tools"}
    <!--tools--></div>
{/capture}
</form>
{/capture}

{include file="common/mainbox.tpl" title={__("regenerate_cities")} content=$smarty.capture.mainbox content_id="regenerate_cities" buttons=$smarty.capture.buttons}