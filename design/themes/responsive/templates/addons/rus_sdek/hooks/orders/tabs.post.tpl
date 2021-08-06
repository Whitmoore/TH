{if $show_shipments}
<div id="content_sdek_information" >
<div class="ty-float-right">
{include file="buttons/button.tpl" but_meta="ty-btn__secondary cm-ajax" but_href="orders.sdek_order_status?order_id=`$order_info.order_id`" but_target_id="sdek_data_statuses" but_text=__("update_info") but_role="tool"}
</div>

{include file="addons/rus_sdek/components/data_statuses.tpl" data_status=$data_status}
<!--content_sdek_information--></div>
{/if}