{if !$smarty.request.extra}
<script type="text/javascript">
(function(_, $) {
    _.tr('text_items_added', '{__("text_items_added")|escape:"javascript"}');

    $.ceEvent('on', 'ce.formpost_add_warehouses_form', function(frm, elm) {
        var warehouses = {};

        if ($('input.cm-item:checked', frm).length > 0) {

            $('input.cm-item:checked', frm).each( function() {
                var id = $(this).val();
                var item = $(this).parent().siblings();
                warehouses[id] = {
                    warehouse_name: item.find('.warehouse-name').text()
                };
            });

            {literal}
            $.cePicker('add_js_item', frm.data('caResultId'), warehouses, 'pl', {
                '{warehouse_id}': '%id',
                '{warehouse_name}': '%item.warehouse_name'
            });
            {/literal}
            
            $.ceNotification('show', {
                type: 'N', 
                title: _.tr('notice'), 
                message: _.tr('text_items_added'), 
                message_state: 'I'
            });
        }

        return false;        
    });
}(Tygh, Tygh.$));
</script>
{/if}

{include file="addons/development/views/warehouses/components/warehouses_search_form.tpl" dispatch="warehouses.picker" extra="<input type=\"hidden\" name=\"result_ids\" value=\"pagination_`$smarty.request.data_id`\">" put_request_vars=true form_meta="cm-ajax" in_popup=true}

<form action="{$smarty.request.extra|fn_url}" method="post" data-ca-result-id="{$smarty.request.data_id}" name="add_warehouses_form">

{include file="common/pagination.tpl" save_current_page=true div_id="pagination_`$smarty.request.data_id`"}

{if $warehouses}
<table width="100%" class="table table-middle">
<thead>
<tr>
    <th width="1%" class="center">
        {if $smarty.request.display == "checkbox"}
        {include file="common/check_items.tpl"}</th>
        {/if}
    <th>{__("id")}</th>
    <th>{__("warehouse_name")}</th>
</tr>
</thead>
{foreach from=$warehouses item=warehouse}
<tr>
    <td class="left">
        {if $smarty.request.display == "checkbox"}
        <input type="checkbox" name="add_warehouses[]" value="{$warehouse.warehouse_id}" class="cm-item" />
        {elseif $smarty.request.display == "radio"}
        <input type="radio" name="selected_warehouse_id" value="{$warehouse.warehouse_id}" />
        {/if}
    </td>
    <td>{$warehouse.warehouse_id}</td>
    <td><span class="warehouse-name">{$warehouse.name}</span></td>
</tr>
{/foreach}
</table>
{else}
    <p class="no-items">{__("no_data")}</p>
{/if}

{include file="common/pagination.tpl" div_id="pagination_`$smarty.request.data_id`"}

<div class="buttons-container">
    {if $smarty.request.display == "radio"}
        {assign var="but_close_text" value=__("choose")}
    {else}
        {assign var="but_close_text" value=__("add_warehouses_and_close")}
        {assign var="but_text" value=__("add_warehouses")}
    {/if}

    {include file="buttons/add_close.tpl" is_js=$smarty.request.extra|fn_is_empty}
</div>

</form>
