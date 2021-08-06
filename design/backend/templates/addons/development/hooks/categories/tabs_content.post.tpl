<div id="content_cross_categories">
    {include file="pickers/categories/picker.tpl" data_id="added_products" input_name="category_data[cross_categories]" no_item_text=__("text_no_items_defined", ["[items]" => __("categories")]) type="links" placement="right" item_ids=$category_data.cross_categories multiple=true}
</div>
<div id="content_qty_discounts">
    {include file="addons/development/components/category_qty_discounts.tpl"}
    <div class="control-group">
        <label class="control-label" for="elm_category_apply_qty_discounts">{__("apply_qty_discounts")}:</label>
        <div class="controls">
            <label class="checkbox">
                <input type="hidden" name="category_data[apply_qty_discounts]" value="N" />
                <input type="checkbox" name="category_data[apply_qty_discounts]" id="elm_category_apply_qty_discounts" value="Y" />
            </label>
        </div>
    </div>
</div>
<div id="content_feature_seo">
    {include file="addons/development/views/feature_seo/manage.tpl" category_id=$id feature_seos=$category_data.feature_seos}
</div>
<div id="content_update_products">
    {if $auth.is_root == 'Y'}
        <div class="control-group">
            <label class="control-label" for="elm_shipping_weight">{__("shipping_weight")}:</label>
            <div class="controls">
                <input type="text" name="category_data[shipping_weight]" id="elm_category_shipping_weight" size="10" value="{$category_data.shipping_weight}" class="input-long" />
                <div class="checkbox ty-update-products-checkmark">
                    {__("apply_to_products")} <input type="checkbox" name="category_data[apply_to_products][shipping_weight]" value="Y" />
                </div>
            </div>
        </div>

        <hr>

        <div class="control-group">
            <label class="control-label" for="elm_items_in_box">{__("items_in_box")}:</label>
            <div class="controls">
                <input type="text" name="category_data[min_items_in_box]" id="product_items_in_box" size="5" value="{$category_data.min_items_in_box|default:"0"}" class="input-micro" onkeyup="fn_product_shipping_settings(this);" />
                &nbsp;-&nbsp;
                <input type="text" name="category_data[max_items_in_box]" size="5" value="{$category_data.max_items_in_box|default:"0"}" class="input-micro" onkeyup="fn_product_shipping_settings(this);" />
                <div class="checkbox ty-update-products-checkmark">
                    {__("apply_to_products")} <input type="checkbox" name="category_data[apply_to_products][shipping_params]" value="Y" />
                </div>
            </div>

            {if $category_data.min_items_in_box > 0 || $category_data.max_items_in_box}
                {assign var="box_settings" value=true}
            {/if}
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_box_length">{__("box_length")}:</label>
            <div class="controls">
                <input type="text" name="category_data[box_length]" id="product_box_length" size="10" value="{$category_data.box_length|default:"0"}" class="input-long shipping-dependence" {if !$box_settings}disabled="disabled"{/if} />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_box_width">{__("box_width")}:</label>
            <div class="controls">
                <input type="text" name="category_data[box_width]" id="product_box_width" size="10" value="{$category_data.box_width|default:"0"}" class="input-long shipping-dependence" {if !$box_settings}disabled="disabled"{/if} />
            </div>
        </div>

        <div class="control-group">
            <label class="control-label" for="elm_box_height">{__("box_height")}:</label>
            <div class="controls">
                <input type="text" name="category_data[box_height]" id="product_box_height" size="10" value="{$category_data.box_height|default:"0"}" class="input-long shipping-dependence" {if !$box_settings}disabled="disabled"{/if} />
            </div>
        </div>

        <hr>

        <div class="control-group">
            <label class="control-label" for="elm_category_margin">{__("price_margin")}(%):</label>
            <div class="controls">
                <input type="text" name="category_data[margin]" id="elm_category_margin" size="10" value="{$category_data.margin}" class="input-long" />
                <div class="checkbox ty-update-products-checkmark">
                    {__("apply_to_products")} <input type="checkbox" name="category_data[apply_to_products][margin]" value="Y" />
                </div>
            </div>
        </div>

        <hr>

        <div class="control-group">
            <label class="control-label" for="elm_category_update_products_price_mode">{__("price_mode")}:</label>
            <div class="controls">
                <select class="span3" name="category_data[products_price_mode]">
                    <option value=""> - </option>
                    <option value="S">{__("static_price")}</option>
                    <option value="D">{__("dynamic_price")}</option>
                    <option value="M">{__("competitor_price")}</option>
                </select>
                <div class="checkbox ty-update-products-checkmark">
                    {__("apply_to_products")} <input type="checkbox" name="category_data[apply_to_products][products_price_mode]" value="Y" />
                </div>
            </div>
        </div>
    {/if}
</div>
