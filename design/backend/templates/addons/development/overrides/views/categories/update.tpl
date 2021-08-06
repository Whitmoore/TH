{if $category_data.category_id}
    {assign var="id" value=$category_data.category_id}
{else}
    {assign var="id" value=0}
{/if}

{capture name="mainbox"}
<form action="{""|fn_url}" method="post" name="category_update_form" class="form-horizontal form-edit {if ""|fn_check_form_permissions} cm-hide-inputs{/if}" enctype="multipart/form-data">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="category_id" value="{$id}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

{capture name="tabsbox"}

<div id="content_detailed">

    {include file="common/subheader.tpl" title=__("information") target="#acc_information"}
    <div id="acc_information" class="collapsed in">
    <div class="control-group">
        <label for="elm_category_name" class="control-label cm-required">{__("name")}:</label>
        <div class="controls">
            <input type="text" name="category_data[category]" id="elm_category_name" size="55" value="{$category_data.category}" class="input-large" />
        </div>
    </div>
    <div class="control-group">
        {if "categories"|fn_show_picker:$smarty.const.CATEGORY_THRESHOLD}
            <label class="control-label cm-required" for="elm_category_parent_id">{__("location")}:</label>
            <div class="controls">
                {include file="pickers/categories/picker.tpl" data_id="location_category" input_name="category_data[parent_id]" item_ids=$category_data.parent_id|default:"0" hide_link=true hide_delete_button=true default_name=__("root_level") display_input_id="elm_category_parent_id" except_id=$id}
            </div>
        {else}
            <label class="control-label" for="elm_category_parent_id">{__("location")}:</label>

            <div class="controls">
            <select name="category_data[parent_id]" id="elm_category_parent_id">
                <option value="0" {if $category_data.parent_id == "0"}selected="selected"{/if}>- {__("root_level")} -</option>
                {foreach from=0|fn_get_plain_categories_tree:false item="cat" name="categories"}
                {if !"ULTIMATE"|fn_allowed_for}
                    {if $cat.id_path|strpos:"`$category_data.id_path`/" === false && $cat.category_id != $id || !$id}
                        <option value="{$cat.category_id}" {if $cat.disabled}disabled="disabled"{/if} {if $category_data.parent_id == $cat.category_id}selected="selected"{/if}>{$cat.category|escape|indent:$cat.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
                    {/if}
                {/if}
                {if "ULTIMATE"|fn_allowed_for}
                    {if $cat.store}
                        {if !$smarty.foreach.categories.first}
                            </optgroup>
                        {/if}
                        <optgroup label="{$cat.category}">
                    {else}
                        {if $cat.id_path|strpos:"`$category_data.id_path`/" === false && $cat.category_id != $id || !$id}
                            <option value="{$cat.category_id}" {if $cat.disabled}disabled="disabled"{/if} {if $category_data.parent_id == $cat.category_id}selected="selected"{/if}>{$cat.category|escape|indent:$cat.level:"&#166;&nbsp;&nbsp;&nbsp;&nbsp;":"&#166;--&nbsp;" nofilter}</option>
                        {/if}
                    {/if}
                {/if}
                {/foreach}
            </select>
            </div>
        {/if}
    </div>

    {if $auth.is_root == 'Y'}
        <div class="control-group">
            <label class="control-label" for="elm_category_net_currency_code">{__("net_currency_code")}:</label>
            <div class="controls">
                <select class="span3" name="category_data[net_currency_code]">
                    <option value="">{__("default")}</option>
                    {foreach from=$currencies item="cur"}
                        <option value="{$cur.currency_code}" {if $category_data.net_currency_code == $cur.currency_code}selected="selected"{/if}>{$cur.description}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    {/if}

    <div class="control-group">
        <label class="control-label" for="elm_category_descr">{__("description")}:</label>
        <div class="controls">
            <textarea id="elm_category_descr" name="category_data[description]" cols="55" rows="8" class="input-large cm-wysiwyg input-textarea-long">{$category_data.description}</textarea>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_descr">{__("full_description")}:</label>
        <div class="controls">
            <textarea id="elm_category_descr" name="category_data[full_description]" cols="55" rows="8" class="input-large cm-wysiwyg input-textarea-long">{$category_data.full_description}</textarea>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_brand_id">{__("brand")}:</label>

        <div class="controls">
        <select name="category_data[brand_id]" id="elm_category_brand_id">
            <option value="0" {if $category_data.brand_id == "0"}selected="selected"{/if}> - </option>
            {foreach from=""|fn_get_brands item="brand"}
                <option value="{$brand.variant_id}" {if $category_data.brand_id == $brand.variant_id}selected="selected"{/if}>{$brand.variant}</option>
            {/foreach}
        </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_tabs_categorization">{__("tabs_categorization")}:</label>

        <div class="controls">
        <select name="category_data[tabs_categorization]" id="elm_category_tabs_categorization" {*onchange="$('#tabs_options').toggle($(this).val() > 0);"*}>
            <option value="" {if $category_data.tabs_categorization == "0"}selected="selected"{/if}> - {__("none")} - </option>
            {foreach from=$filter_features item=feature}
                <option value="{$feature.feature_id}" {if $category_data.tabs_categorization == $feature.feature_id}selected="selected"{/if}>{if $feature.feature_group}{$feature.feature_group}: {/if}{$feature.filter}</option>
            {/foreach}
        </select>
        </div>
    </div>
    {*
    <div id="tabs_options" {if !$category_data.tabs_categorization}style="display: none;"{/if}>
        <div class="control-group">
            <label class="control-label" for="elm_category_extended_tabs_categorization">{__("extended_tabs_categorization")}:</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="hidden" name="category_data[extended_tabs_categorization]" value="0" />
                    <input type="checkbox" name="category_data[extended_tabs_categorization]" id="elm_category_extended_tabs_categorization" value="Y" {if $category_data.extended_tabs_categorization == 'Y'} checked="checked"{/if} />
                </label>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_category_all_items_tab">{__("all_items_tab")}:</label>
            <div class="controls">
                <label class="checkbox">
                    <input type="hidden" name="category_data[all_items_tab]" value="0" />
                    <input type="checkbox" name="category_data[all_items_tab]" id="elm_category_all_items_tab" value="Y" {if $category_data.all_items_tab == 'Y'} checked="checked"{/if} />
                </label>
            </div>
        </div>
    </div>
    *}

    <div class="control-group">
        <label class="control-label" for="elm_category_subtabs_categorization">{__("subtabs_categorization")}:</label>

        <div class="controls">
        <select name="category_data[subtabs_categorization]" id="elm_category_subtabs_categorization">
            <option value="" {if $category_data.subtabs_categorization == "0"}selected="selected"{/if}> - {__("none")} - </option>
            {foreach from=$filter_features item=feature}
                <option value="{$feature.feature_id}" {if $category_data.subtabs_categorization == $feature.feature_id}selected="selected"{/if}>{if $feature.feature_group}{$feature.feature_group}: {/if}{$feature.filter}</option>
            {/foreach}
        </select>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_sections_categorization">{__("sections_categorization")}:</label>

        <div class="controls">
            {include file="common/double_selectboxes.tpl"
            first_name="category_data[sections_categorization]"
            first_data=$category_data.sections_categorization
            second_name="sections_categorization"
            second_data=$section_features}
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_ajax_pagination">{__("ajax_pagination")}:</label>
        <div class="controls">
            <label class="checkbox">
                <input type="hidden" name="category_data[ajax_pagination]" value="N" />
                <input type="checkbox" name="category_data[ajax_pagination]" id="elm_category_ajax_pagination" value="Y" {if $category_data.ajax_pagination == 'Y'} checked="checked"{/if} />
            </label>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_category_pagination_type">{__("pagination_type")}:</label>

        <div class="controls">
        <select name="category_data[pagination_type]" id="elm_category_pagination_type" onchange="$('#pagination_options').toggle($(this).val() != 'N');">
            <option value="N" {if $category_data.pagination_type == "N"}selected="selected"{/if}> - {__("none")} - </option>
            <option value="R" {if $category_data.pagination_type == "R"}selected="selected"{/if}>{__("regular_pagination")}</option>
        </select>
        </div>
    </div>

    <div id="pagination_options" {if $category_data.pagination_type == "N"}class="hidden"{/if}>
        <div class="control-group">
            <label class="control-label" for="elm_category_products_per_page">{__("products_per_page")}:</label>

            <div class="controls">
                <input type="text" name="category_data[products_per_page]" id="elm_category_products_per_page" size="55" value="{$category_data.products_per_page}" class="input-text-short" />
            </div>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_note_url">{__("note_url")}:</label>
        <div class="controls">
            <input type="text" name="category_data[note_url]" id="elm_category_note_url" size="55" value="{$category_data.note_url}" class="input-text-short" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_category_note_text">{__("note_text")}:</label>
        <div class="controls">
            <input type="text" name="category_data[note_text]" id="elm_category_note_text" size="55" value="{$category_data.note_text}" class="input-text-short" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_category_code">{__("code")}:</label>
        <div class="controls">
            <input type="text" name="category_data[code]" id="elm_category_code" size="55" value="{$category_data.code}" class="input-text-short" />
        </div>
    </div>
    {*
    <div class="control-group">
        <label class="control-label" for="elm_category_is_virtual">{__("is_virtual")}:</label>
        <div class="controls">
            <label class="checkbox">
                <input type="hidden" name="category_data[is_virtual]" value="N" />
                <input type="checkbox" name="category_data[is_virtual]" id="elm_category_is_virtual" value="Y" {if $category_data.is_virtual == 'Y'} checked="checked"{/if} />
            </label>
        </div>
    </div>
    *}
    <div class="control-group">
        <label class="control-label" for="elm_category_product_pretitle">{__("product_pretitle")}:</label>
        <div class="controls">
            <input type="text" name="category_data[product_pretitle]" id="elm_category_product_pretitle" size="55" value="{$category_data.product_pretitle}" class="input-text-medium" />
        </div>
    </div>

    {include file="common/select_status.tpl" input_name="category_data[status]" id="elm_category_status" obj=$category_data hidden=true}

    {if "ULTIMATE"|fn_allowed_for}
    {include file="views/companies/components/company_field.tpl"
        name="category_data[company_id]"
        id="category_data_company_id"
        selected=$category_data.company_id
    }
    {/if}

    <div class="control-group">
        <label class="control-label">{__("images")}:</label>
        <div class="controls">
            {include file="common/attach_images.tpl" image_name="category_main" image_object_type="category" image_pair=$category_data.main_pair image_object_id=$id icon_text=__("text_category_icon") detailed_text=__("text_category_detailed_image") no_thumbnail=true}
        </div>
    </div>

    </div>
    <hr />

    {include file="common/subheader.tpl" title=__("seo_meta_data") target="#acc_seo"}

    <div id="acc_seo" class="collapsed in">
    <div class="control-group">
        <label class="control-label" for="elm_category_page_title">{__("page_title")}:</label>
        <div class="controls">
            <input type="text" name="category_data[page_title]" id="elm_category_page_title" size="55" value="{$category_data.page_title}" class="input-large" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_meta_description">{__("meta_description")}:</label>
        <div class="controls">
            <textarea name="category_data[meta_description]" id="elm_category_meta_description" cols="55" rows="4" class="input-large">{$category_data.meta_description}</textarea>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_meta_keywords">{__("meta_keywords")}:</label>
        <div class="controls">
            <textarea name="category_data[meta_keywords]" id="elm_category_meta_keywords" cols="55" rows="4" class="input-large">{$category_data.meta_keywords}</textarea>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_noindex">{__("tag_noindex")}:</label>
        <div class="controls">
        <input type="hidden" value="N" name="category_data[is_noindex]"/>
        <input type="checkbox" class="cm-switch-availability" value="Y" name="category_data[is_noindex]" id="sw_category_meta" {if $category_data.is_noindex == 'Y'} checked="checked"{/if} />
        </div>
    </div>

    <div id="category_meta">
        <div class="control-group">
            <label class="control-label" for="elm_category_nofollow">{__("tag_nofollow")}:</label>
            <div class="controls">
            <input type="hidden" value="N" name="category_data[is_nofollow]"/>
            <input type="checkbox" class="" value="Y" name="category_data[is_nofollow]" id="elm_category_nofollow"{if $category_data.is_nofollow == 'Y'} checked="checked"{/if} {if $category_data.is_noindex != 'Y'}disabled="disabled"{/if} />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_category_canonical">{__("tag_canonical")}:</label>
            <div class="controls">
                <input type="text" name="category_data[canonical]" id="elm_category_canonical" size="55" value="{$category_data.canonical}" class="input-text-short cm-switch-inverse" {if $category_data.is_noindex == 'Y'}disabled="disabled"{/if} />
            </div>
        </div>
    </div>

    </div>
    <hr />
    {if !"ULTIMATE:FREE"|fn_allowed_for}
    {include file="common/subheader.tpl" title=__("availability") target="#acc_availability"}
    <div id="acc_availability">
    <div class="control-group">
        <label class="control-label">{__("usergroups")}:</label>
            <div class="controls">
                {include file="common/select_usergroups.tpl" id="ug_id" name="category_data[usergroup_ids]" usergroups="C"|fn_get_usergroups:$smarty.const.DESCR_SL usergroup_ids=$category_data.usergroup_ids input_extra="" list_mode=false}
                <label class="checkbox" for="usergroup_to_subcats">{__("to_all_subcats")}
                    <input id="usergroup_to_subcats" type="checkbox" name="category_data[usergroup_to_subcats]" value="Y" />
                </label>
            </div>
    </div>
    {/if}

    <div class="control-group">
        <label class="control-label" for="elm_category_position">{__("position")}:</label>
        <div class="controls">
            <input type="text" name="category_data[position]" id="elm_category_position" size="10" value="{$category_data.position}" class="input-text-short" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_category_creation_date">{__("creation_date")}:</label>
        <div class="controls">
            {include file="common/calendar.tpl" date_id="elm_category_creation_date" date_name="category_data[timestamp]" date_val=$category_data.timestamp|default:$smarty.const.TIME start_year=$settings.Company.company_start_year}
        </div>
    </div>

    {include file="views/localizations/components/select.tpl" data_from=$category_data.localization data_name="category_data[localization]"}
</div>
</div>

<div id="content_views">
    <div id="extra">
        <div class="control-group">
            <label class="control-label" for="elm_category_product_layout">{__("product_details_view")}:</label>
            <div class="controls">
            <select id="elm_category_product_layout" name="category_data[product_details_layout]">
                {foreach from="category"|fn_get_product_details_views key="layout" item="item"}
                    <option {if $category_data.product_details_layout == $layout}selected="selected"{/if} value="{$layout}">{$item}</option>
                {/foreach}
            </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_category_products_sorting">{__("products_sorting")}:</label>
            <div class="controls">
            <select id="elm_category_products_sorting" name="category_data[products_sorting]">
                <option {if $category_data.products_sorting == ''}selected="selected"{/if} value="">{__("default")}</option>
                {foreach from=$category_data.sortings key="key" item="name"}
                    <option {if $category_data.products_sorting == $key}selected="selected"{/if} value="{$key}">{$name}</option>
                {/foreach}
            </select>
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_category_use_custom_templates">{__("use_custom_view")}:</label>
            <div class="controls">
            <input type="hidden" value="N" name="category_data[use_custom_templates]"/>
            <input type="checkbox" class="cm-toggle-checkbox" value="Y" name="category_data[use_custom_templates]" id="elm_category_use_custom_templates"{if $category_data.selected_layouts} checked="checked"{/if} />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_category_product_columns">{__("product_columns")}:</label>
            <div class="controls">
            <input type="text" name="category_data[product_columns]" id="elm_category_product_columns" size="10" value="{$category_data.product_columns}" class="cm-toggle-element" {if !$category_data.selected_layouts}disabled="disabled"{/if} />
            </div>
        </div>

        {assign var="layouts" value=""|fn_get_products_views:false:false}
        <div class="control-group">
            <label class="control-label">{__("available_views")}:</label>
            <div class="controls">
                {foreach from=$layouts key="layout" item="item"}
                    <label class="checkbox" for="elm_category_layout_{$layout}"><input type="checkbox" class="cm-combo-checkbox cm-toggle-element" name="category_data[selected_layouts][{$layout}]" id="elm_category_layout_{$layout}" value="{$layout}" {if ($category_data.selected_layouts.$layout) || (!$category_data.selected_layouts && $item.active)}checked="checked"{/if} {if !$category_data.selected_layouts}disabled="disabled"{/if} />{$item.title}</label>
                {/foreach}
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_category_default_layout">{__("default_category_view")}:</label>
            <div class="controls">
            <select id="elm_category_default_layout" class="cm-combo-select cm-toggle-element" name="category_data[default_layout]" {if !$category_data.selected_layouts}disabled="disabled"{/if}>
                {foreach from=$layouts key="layout" item="item"}
                    {if ($category_data.selected_layouts.$layout) || (!$category_data.selected_layouts && $item.active)}
                        <option {if $category_data.default_layout == $layout}selected="selected"{/if} value="{$layout}">{$item.title}</option>
                    {/if}
                {/foreach}
            </select>
            </div>
        </div>
    </div>
</div>

<div id="content_addons">
{hook name="categories:detailed_content"}
{/hook}
</div>

{hook name="categories:tabs_content"}
{/hook}

{capture name="buttons"}
    {if $id}
        {include file="common/view_tools.tpl" url="categories.update?category_id="}

        {$view_uri = "categories.view?category_id=`$id`"|fn_get_preview_url:$category_data:$auth.user_id}

        {capture name="tools_list"}
            {hook name="categories:update_tools_list"}
                <li>{btn type="list" href="categories.add?parent_id=$id" text=__("add_subcategory")}</li>
                <li>{btn type="list" href="products.add?category_id=$id" text=__("add_product")}</li>
                <li>{btn type="list" target="_blank" text=__("preview") href=$view_uri}</li>
                <li class="divider"></li>
                <li>{btn type="list" href="products.manage?cid=$id" text=__("view_products")}</li>
                <li>{btn type="list" class="cm-confirm" text=__("delete_this_category") data=["data-ca-confirm-text" => "{__("category_deletion_side_effects")}"] href="categories.delete?category_id=`$id`"}</li>
            {/hook}
        {/capture}
        {dropdown content=$smarty.capture.tools_list}
    {/if}
    {include file="buttons/save_cancel.tpl" but_role="submit-link" but_target_form="category_update_form" but_name="dispatch[categories.update]" save=$id}
{/capture}

{if $id}
    {hook name="categories:tabs_extra"}
    {/hook}
{/if}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox group_name=$runtime.controller active_tab=$smarty.request.selected_section track=true}
</form>
{/capture}

{capture name="sidebar"}
{if $categories_tree}
    <div class="sidebar-row">
        <h6>{__("categories")}</h6>
        <div class="nested-tree">
            {include file="views/categories/components/categories_links_tree.tpl" show_all=false categories_tree=$categories_tree}
        </div>
    </div>
{/if}
{/capture}

{if !$id}
    {include file="common/mainbox.tpl" title=__("new_category") sidebar=$smarty.capture.sidebar sidebar_position="left" content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}
{else}
    {include file="common/mainbox.tpl" sidebar=$smarty.capture.sidebar sidebar_position="left" title="{__("editing_category")}: `$category_data.category`" content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons}
{/if}
