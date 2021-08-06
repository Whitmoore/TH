{if $warehouse_data}
    {assign var="id" value=$warehouse_data.warehouse_id}
{else}
    {assign var="id" value=0}
{/if}

{** warehouses section **}

{capture name="mainbox"}

{capture name="tabsbox"}
<form action="{""|fn_url}" method="post" class="form-horizontal form-edit" name="warehouses_form">
<input type="hidden" name="fake" value="1" />
<input type="hidden" name="warehouse_id" value="{$id}" />
<input type="hidden" name="selected_section" value="{$smarty.request.selected_section}" />

<div id="content_detailed">
    <div class="control-group">
        <label for="elm_warehouse_name" class="control-label cm-required">{__("warehouse_name")}</label>
        <div class="controls">
        <input type="text" name="warehouse_data[name]" id="elm_warehouse_name" value="{$warehouse_data.name}" size="25" class="input-long" /></div>
    </div>
    {if $id != $smarty.const.TH_WAREHOUSE_ID}
    <div class="control-group">
        <label for="elm_warehouse_brand_id" class="control-label">{__("brand")}</label>
        {$columns = "4"}
        {split data=$brands size=$columns assign="splitted_brands" skip_complete=true}
        {math equation="98 / x" x=$columns assign="cell_width"}
        <div class="controls">
        {foreach from=$splitted_brands item="_brands"}
            <div style="display: inline-block;width: 100%;">
                {foreach from=$_brands item=brand}
                    <div style="display: inline-block;width: {$cell_width}%;">
                        <div style="width: 100px;display: inline-block;">{$brand.variant}</div>
                        <input type="checkbox" name="warehouse_data[brand_ids][]" value="{$brand.variant_id}" class="checkbox cm-item" {if $brand.variant_id|in_array:$warehouse_data.brand_ids}checked="checked"{/if}/>
                    </div>
                {/foreach}
            </div>
        {/foreach}
        </div>
    </div>
    {/if}
    <div class="control-group">
        <label class="control-label" for="elm_warehouse_priority">{__("priority")}:</label>
        <div class="controls">
            <input type="text" name="warehouse_data[priority]" id="elm_warehouse_priority" size="10" value="{$warehouse_data.priority}" class="input-text-short" />
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_net_total">{__("wh_net_total")}:</label>
        <div class="controls" style="padding-top: 5px;">
            {include file="common/price.tpl" value=$total.net}
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="elm_net_total">{__("wh_rrp_total")}:</label>
        <div class="controls" style="padding-top: 5px;">
            {include file="common/price.tpl" value=$total.rrp}
        </div>
    </div>
    {if $id}
    <div class="control-group">
        <a href="{"development.check_inventory?warehouse_id=`$id`"|fn_url}" target="_blank">{__("check_inventory")}</a>
    </div>
    {/if}

</div>

<div id="content_addons">
{hook name="warehouses:detailed_content"}
{/hook}
</div>

</form>

<div class="cm-hide-save-button hidden" id="content_import_inventory">
    {include file="addons/development/components/supplier_stocks.tpl" warehouse_id=$warehouse_data.warehouse_id dispatch="development.update_warehouse_stocks"}
</div>

{capture name="buttons"}
    {if !$id}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[warehouses.update]" but_role="submit-link" but_target_form="warehouses_form" but_meta="cm-save-buttons"}
    {else}
        {include file="buttons/save_cancel.tpl" but_name="dispatch[warehouses.update]" but_role="submit-link" but_target_form="warehouses_form" save=$id but_meta="cm-save-buttons"}
    {/if}
{/capture}

{/capture}
{include file="common/tabsbox.tpl" content=$smarty.capture.tabsbox active_tab=$smarty.request.selected_section track=true}

{/capture}

{if !$id}
    {assign var="title" value=__("warehouses.new_warehouse")}
{else}
    {assign var="title" value="{__("warehouses.editing_warehouse")}: `$warehouse_data.name`"}
{/if}
{include file="common/mainbox.tpl" title=$title content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}

{** warehouse section **}
