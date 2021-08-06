
{include file="common/subheader.tpl" title=__("google_merchant") target="#google_merchant_addon"}

<div id="google_merchant_addon" class="in collapse">

    <div class="control-group">
        <label class="control-label" for="yml_export">{__("gml_disable_product")}:</label>
        <div class="controls">
            <input type="hidden" value="N" name="product_data[gml_disable_product]"/>
            <input type="checkbox" class="cm-toggle-checkbox" value="Y" name="product_data[gml_disable_product]" id="gml_export" {if $product_data.gml_disable_product == "Y"} checked="checked"{/if} />
        </div>
    </div>
    
    {include file="addons/google_merchant/common/gm_categories_selector.tpl" name="product_data[gml_product_category]" value=$product_data.gml_product_category}            
</div>
