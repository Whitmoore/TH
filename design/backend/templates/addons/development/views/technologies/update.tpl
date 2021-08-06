{if $technology_data}
    {assign var="id" value=$technology_data.technology_id}
{else}
    {assign var="id" value=0}
{/if}

{** technologies section **}

{capture name="mainbox"}

<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="technologies_form" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="technology_id" value="{$id}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

{capture name="tabsbox"}
<div id="content_detailed">
    <div class="control-group">
        <label for="elm_technology_name" class="control-label cm-required">{__("technology_name")}</label>
        <div class="controls">
        <input type="text" name="technology_data[name]" id="elm_technology_name" value="{$technology_data.name}" size="25" class="input-long" /></div>
    </div>

    <div class="control-group" id="technology_photo">
        <label class="control-label">{__("photo")}</label>
        <div class="controls">
            {include file="common/attach_images.tpl" image_name="technology_main" image_object_type="technology" image_pair=$technology_data.main_pair image_object_id=$id no_detailed=true hide_titles=true}
        </div>
    </div>
    
    <div class="control-group cm-no-hide-input">
        <label class="control-label" for="elm_technology_description">{__("description")}:</label>
        <div class="controls">
            <textarea id="elm_technology_description" name="technology_data[description]" cols="55" rows="8" class="cm-wysiwyg input-large">{$technology_data.description}</textarea>
        </div>
    </div>

</div>

<div id="content_products">
    {include file="pickers/products/picker.tpl" data_id="added_products" input_name="technology_data[products]" no_item_text=__("text_no_items_defined", ["[items]" => __("products")]) type="links" placement="right" item_ids=$technology_data.products}
</div>

<div id="content_addons">
{hook name="technologies:detailed_content"}
{/hook}
</div>

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}
</form>

{capture name="buttons"}
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="technologies_form" but_name="dispatch[technologies.update]"}
    {else}
        {$view_uri = "technologies.view?technology_id=`$id`"|fn_get_preview_url:$technology_data:$auth.user_id}
        {$ui_uri = "technologies.update_info?technology_id=`$id`"|fn_url}

        {capture name="tools_list"}
            <li>{btn type="list" target="_blank" text=__("preview") href=$view_uri}</li>
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[technologies.update]" but_role="submit-link" but_target_form="technologies_form" save=$id}
    {/if}
{/capture}
    
{/capture}

{if !$id}
    {assign var="title" value=__("technologies.new_technology")}
{else}
    {assign var="title" value="{__("technologies.editing_technology")}: `$technology_data.name`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

{** technology section **}
