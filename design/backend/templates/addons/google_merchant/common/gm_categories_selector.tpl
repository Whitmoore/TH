{script src="js/addons/google_merchant/gm_categories.js"}

{$obj_id = $obj_id|default:"gm_categories"}

<div class="control-group cm-no-hide-input">
    <label for="product_type_prefix" class="control-label">{__("google_product_category")}:</label>
    <div class="controls" id="{$obj_id}_box">
        <input type="text" name="{$name}" size="200" value="{$value}" class="input-large cm-gm-categories" /></br>
        {include file="common/popupbox.tpl" id="{$obj_id}_popup" href="gm_categories.picker?obj_id={$obj_id}" link_text=__("gml_market_category_link") text=__("gml_market_category_list_title") act="link"}
    </div>
</div>  

