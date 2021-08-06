
{include file="common/subheader.tpl" title=__("google_merchant") target="#google_merchant_addon"}

<div id="google_merchant_addon" class="in collapse">
    
    <div class="control-group">
        <label class="control-label" for="yml_export">{__("gml_disable_category")}:</label>
        <div class="controls">
            <input type="hidden" value="N" name="category_data[gml_disable_cat]"/>
            <input type="checkbox" class="cm-toggle-checkbox" value="Y" name="category_data[gml_disable_cat]" id="gml_export" {if $category_data.gml_disable_cat == "Y"} checked="checked"{/if} />
        </div>
    </div>

    {include file="addons/google_merchant/common/gm_categories_selector.tpl" name="category_data[gml_product_category]" value=$category_data.gml_product_category}            

</div>