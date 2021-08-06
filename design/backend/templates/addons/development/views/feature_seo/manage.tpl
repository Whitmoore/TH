{capture name="add_new_picker"}
    {include file="addons/development/views/feature_seo/update.tpl" category_id=$category_id}
{/capture}
<div style="float: right; margin-bottom: 20px;">
    {include file="common/popupbox.tpl" id="add_new_option" text=__("new_combination") link_text=__("add_combination") act="general" content=$smarty.capture.add_new_picker meta=$position icon="icon-plus"}
</div>

<div class="items-container" id="feature_seo_table">
    {if $feature_seos.data}
        <table class="table table-middle">
            <tbody>
            {foreach from=$feature_seos.data item="fs"}
            
                {capture name="feature_seo_name"}
                    {foreach from=$fs.features item="variant_id" key="feature_id" name="pairs"}
                        {if $smarty.foreach.pairs.index > 0} + {/if}{$feature_seos.description.features.$feature_id}: {$feature_seos.description.variants.$variant_id}
                    {/foreach}
                {/capture}
                
                {include file="common/object_group.tpl"
                no_table=true
                id=$fs.item_id
                id_prefix="feature_seo_"
                href_desc=" "
                text=$smarty.capture.feature_seo_name
                hide_for_vendor=false
                status=$fs.status
                table="category_feature_seo"
                object_id_name="item_id"
                href="feature_seo.update?item_id=`$fs.item_id`"
                href_delete="feature_seo.delete?item_id=`$fs.item_id`&category_id=`$category_id`"
                delete_target_id="feature_seo_table"
                header_text="{__("edit")}: `$smarty.capture.feature_seo_name`"
                skip_delete=false
                additional_class="cm-no-hide-input"
                prefix="feature_seo"
                link_text="{__("edit")}"
                non_editable=false
                company_object=$fs}
            {/foreach}
            </tbody>
        </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
<!--feature_seo_table--></div>