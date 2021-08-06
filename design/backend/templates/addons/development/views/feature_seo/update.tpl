{if $feature_seo.item_id}
    {assign var="id" value=$feature_seo.item_id}
{else}
    {assign var="id" value=0}
{/if}

<div id="content_feature_seo{$id}">
<form action="{""|fn_url}" method="post" name="update_feature_seo_form_{$id}" class="form-horizontal form-edit ">
<input type="hidden" name="item_id" value="{$id}" />
<input type="hidden" name="result_ids" value="feature_seo_table" />
<input type="hidden" name="feature_seo_data[category_id]" value="{$category_id}" />

    <div class="control-group">
        <label class="control-label" for="elm_feature_seo_feature_id">{__("feature")}:</label>

        <div class="controls">
            <table class="table table-middle" width="100%">
            <thead class="cm-first-sibling">
            <tr>
                <th width="5%">{__("feature")}</th>
                <th width="20%">{__("variant")}</th>
                <th width="15%">&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            {$_key = 0}
            {foreach from=$feature_seo.features item="variant_id" key="feature_id" name="feature_pairs"}
            <tr class="cm-row-item">
                <td width="80%">
                    {include file="addons/development/components/select_feature_variant.tpl" data_name="feature_seo_data[features]" id=$id key=$_key variant_id=$variant_id params=['seo_variants' => true]}
                </td>
                <td width="15%" class="nowrap right">
                    {include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}
                </td>
            </tr>
            {$_key = $_key + 1}
            {/foreach}
            {math equation="x+1" x=$_key|default:0 assign="new_key"}
            <tr class="{cycle values="table-row , " reset=1}" id="box_add_feature_pair_{$id}">
                <td width="80%">
                    {include file="addons/development/components/select_feature_variant.tpl" data_name="feature_seo_data[features]" id=$id key=$new_key variant_id=0 params=['seo_variants' => true]}
                </td>
                <td width="15%" class="right">
                    {include file="buttons/multiple_buttons.tpl" item_id="add_feature_pair_`$id`"}
                </td>
            </tr>
            </tbody>
            </table>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_feature_seo_full_description_{$id}">{__("full_description")}:</label>
        <div class="controls">
            <textarea id="elm_feature_seo_full_description_{$id}" name="feature_seo_data[full_description]" cols="55" rows="8" class="input-large cm-wysiwyg input-textarea-long">{$feature_seo.full_description}</textarea>
        </div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="elm_feature_seo_page_title_{$id}">{__("page_title")}:</label>
        <div class="controls">
            <input type="text" name="feature_seo_data[page_title]" id="elm_feature_seo_page_title_{$id}" size="55" value="{$feature_seo.page_title}" class="input-large" />
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_feature_seo_meta_description_{$id}">{__("meta_description")}:</label>
        <div class="controls">
            <textarea name="feature_seo_data[meta_description]" id="elm_feature_seo_meta_description_{$id}" cols="55" rows="4" class="input-large">{$feature_seo.meta_description}</textarea>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_feature_seo_meta_keywords_{$id}">{__("meta_keywords")}:</label>
        <div class="controls">
            <textarea name="feature_seo_data[meta_keywords]" id="elm_feature_seo_meta_keywords_{$id}" cols="55" rows="4" class="input-large">{$feature_seo.meta_keywords}</textarea>
        </div>
    </div>

    <div class="control-group">
        <label class="control-label" for="elm_feature_seo_position">{__("position")}:</label>
        <div class="controls">
            <input type="text" name="feature_seo_data[position]" id="elm_feature_seo_position" size="10" value="{$feature_seo.position}" class="input-text-short" />
        </div>
    </div>
    
    <div class="buttons-container">
        {include file="buttons/save_cancel.tpl" but_name="dispatch[feature_seo.update]" cancel_action="close" save=$id but_meta="cm-dialog-closer cm-ajax"}
    </div>

</form>
<!--content_group{$id}--></div>