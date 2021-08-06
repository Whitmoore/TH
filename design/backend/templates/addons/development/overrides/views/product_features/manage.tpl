{script src="js/tygh/tabs.js"}
{script src="js/tygh/product_features.js"}

{capture name="mainbox"}

{assign var="r_url" value=$config.current_url|escape:url}

<div class="items-container{if ""|fn_check_form_permissions} cm-hide-inputs{/if}" id="update_features_list">

<div class="cm-j-tabs cm-track tabs tabs-with-conf">
    <ul class="nav nav-tabs">
    <li id="group_0" class="{if 0 == $active_tab} active extra-tab{/if}">
        <a href="{"product_features.manage?group_id=`0`"|fn_url}">{__("common_features")}</a>
    </li>
    {foreach from=$group_features item=tab key=key name=tabs}
        <li id="group_{$key}" class="{if $key == $active_tab} active extra-tab{/if}">
            {if $key == $active_tab}{btn type="dialog" class="cm-ajax-force hand icon-cog" href="product_features.update?feature_id=`$key`&return_url=$r_url" id="feature_group_`$key`" title="`$tab.description`"}{/if}
            <a href="{"product_features.manage?group_id=`$key`"|fn_url}">{$tab.description}</a>
        </li>
    {/foreach}
    </ul>
</div>

{if $features}
    <table width="100%" class="table table-middle table-objects">
        <tbody>
        {foreach from=$features item="subfeature"}

            {if !$subfeature|fn_allow_save_object:"product_features"}
                {include file="common/object_group.tpl" id=$subfeature.feature_id details=$subfeature.feature_description text=$subfeature.description status=$subfeature.status hidden=true href="product_features.update?feature_id=`$subfeature.feature_id`&return_url=$r_url" object_id_name="feature_id" table="product_features" additional_class="cm-hide-inputs" header_text="{__("viewing_feature")}: `$subfeature.description`"
                    update_controller="product_features" no_table=true non_editable=true link_text=__("view") is_view_link=true company_object=$subfeature}
            {else}
                {include file="common/object_group.tpl" id=$subfeature.feature_id details=$subfeature.feature_description text=$subfeature.description status=$subfeature.status hidden=true href="product_features.update?feature_id=`$subfeature.feature_id`&return_url=$r_url" object_id_name="feature_id" table="product_features" href_delete="product_features.delete?feature_id=`$subfeature.feature_id`&group_id=`$subfeature.parent_id`" delete_target_id="update_features_list" header_text="{__("editing_product_feature")}: `$subfeature.description`" update_controller="product_features" no_table=true company_object=$subfeature}
            {/if}

        {/foreach}
        </tbody>
    </table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}
<!--update_features_list--></div>

{capture name="adv_buttons"}
    {capture name="tools_list"}
        {capture name="add_new_picker"}
            {include file="views/product_features/update.tpl" feature=[] is_group=true}
        {/capture}
        <li>{include file="common/popupbox.tpl" id="add_new_group" text=__("new_group") content=$smarty.capture.add_new_picker link_text=__("add_group") act="link" icon="true"}</li>

        {capture name="add_new_picker_2"}
            {include file="views/product_features/update.tpl" feature=[]}
        {/capture}
        <li>{include file="common/popupbox.tpl" id="add_new_feature" text=__("new_feature") content=$smarty.capture.add_new_picker_2 link_text=__("add_feature") act="link" icon="true"}</li>
    {/capture}
    {dropdown content=$smarty.capture.tools_list icon="icon-plus" no_caret=true placement="right"}
{/capture}

{capture name="sidebar"}
    {include file="common/saved_search.tpl" dispatch="product_features.manage" view_type="product_features"}
    {include file="views/product_features/components/product_features_search_form.tpl" dispatch="product_features.manage"}
{/capture}

{/capture}
{include file="common/mainbox.tpl" title=__("features") content=$smarty.capture.mainbox select_languages=true buttons=$smarty.capture.buttons adv_buttons=$smarty.capture.adv_buttons sidebar=$smarty.capture.sidebar}
