{capture name="mainbox"}
<div id="update_rrp_section">
<form action="{""|fn_url}" method="post" name="update_rrp" enctype="multipart/form-data" class="cm-ajax form-horizontal form-edit">
<input type="hidden" name="result_ids" value="update_rrp_section" />
<input type="hidden" name="step" value="{$step}" />

{if $step == 'one'}
    <div class="control-group">
        <label class="control-label">{__("type")}:</label>
        <div class="controls">
            <select class="span3" name="type">
                <option value="">{__("none")}</option>
                <option value="rrp">{__("rrp")}</option>
                <option value="ean">{__("ean")}</option>
                <option value="net">{__("net_prices")}</option>
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("select_file")}:</label>
        <div class="controls">{include file="common/fileuploader.tpl" var_name="csv_file[0]"}</div>
    </div>
    <div class="control-group">
        <label class="control-label">{__("brand")}</label>
        {$columns = "4"}
        {split data=$brands size=$columns assign="splitted_brands" skip_complete=true}
        {math equation="98 / x" x=$columns assign="cell_width"}
        <div class="controls">
        {foreach from=$splitted_brands item="_brands"}
            <div style="display: inline-block;width: 100%;">
                {foreach from=$_brands item=brand}
                    <div style="display: inline-block;width: {$cell_width}%;">
                        <div style="width: 100px;display: inline-block;">{$brand.variant}</div>
                        <input type="checkbox" name="rrp_data[brand_ids][]" value="{$brand.variant_id}" class="checkbox cm-item" {if $brand.variant_id == $smarty.const.BABOLAT_FV_ID}checked="checked"{/if}/>
                    </div>
                {/foreach}
            </div>
        {/foreach}
        </div>
    </div>
{elseif $step == 'two'}
    <div class="control-group">
        <label class="control-label">{__("add_selected_to_promotion")}</label>
        <div class="controls">
        <select class="span3" name="promotion_id">
            <option value="">{__("none")}</option>
            {foreach from=$promotions item="promotion"}
                <option value="{$promotion.promotion_id}">{$promotion.name}</option>
            {/foreach}
        </select>
        </div>
    </div>
    <table width="100%" class="table table-middle">
    {foreach from=$categories item=category}
            <tr>
                <td colspan="6" class="center ty-update-rrp-category">{$category.category}</td>
            </td>
            {$category_id = $category.category_id}
            {foreach from=$products.$category_id item=product}
            <tr>
                <td class="left">
                    <input type="checkbox" name="product_ids[]" value="{$product.product_id}" class="checkbox cm-item" />
                </td>
                <td>
                    <a class="row-status" href="{"products.update?product_id=`$product.product_id`"|fn_url}">{$product.product nofilter}</a>
                </td>
                <td>
                    {include file="common/price.tpl" value=$product.price}
                </td>
                <td>
                    {include file="common/price.tpl" value=$product.net_cost_rub}
                </td>
                <td>
                    {if $product.amount > 0}✔{/if}
                </td>
                <td>
                    {$product.updated_timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"}
                </td>
            </tr>
            {/foreach}
        {/foreach}
    </table>
{elseif $step == 'three'}
    {__('done')}
{/if}
{capture name="buttons"}
    <div class="cm-tab-tools" id="tools">
        {include file="buttons/button.tpl" but_text=__("update") but_name="dispatch[development.process_rrp]" but_role="submit-link" but_target_form="update_rrp" but_meta="cm-tab-tools"}
    <!--tools--></div>
{/capture}
</form>
<!--update_rrp_section--></div>
{/capture}

{include file="common/mainbox.tpl" title={__("update_rrp")} content=$smarty.capture.mainbox content_id="update_rrp" buttons=$smarty.capture.buttons}