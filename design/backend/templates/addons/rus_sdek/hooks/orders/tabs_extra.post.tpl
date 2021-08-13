{if !empty($data_shipments)}
    <div id="content_sdek_orders">
        {foreach from=$data_shipments item=shipment key="shipment_id"}
            <form action="{""|fn_url}" method="post" name="sdek_form_{$shipment_id}" class="cm-processed-form cm-check-changes">
                <input type="hidden" name="order_id" value="{$order_id}" />
                <input type="hidden" name="add_sdek_info[{$shipment_id}][order][tariff_code]" value="{$shipment.tariff_id}" />
                {*if $shipment.shipment_point}
                    <input type="hidden" name="add_sdek_info[{$shipment_id}][order][shipment_point]" value="{$shipment.shipment_point}" />
                {else*}
                    <input type="hidden" name="add_sdek_info[{$shipment_id}][order][from_location][code]" value="{$shipment.from_location.code}" />
                    <input type="hidden" name="add_sdek_info[{$shipment_id}][order][from_location][address]" value="{$shipment.from_location.address}" />
                {*/if*}
                <div class="control-group">
                    <div class="control">
                        <h4>{__("shipment")}: <a class="underlined" href="{"shipments.details?shipment_id=`$shipment_id`"|fn_url}" target="_blank"><span>#{$shipment_id} ({__("details")})</span></a></h4>
                    </div>
                    <table width="100%" class="table table-middle">
                    <thead>
                    <tr>
                        <th width="30%" class="shift-left">{__("sdek.sdek_address_shipping")}</th>
                        {*<th width="10%">{__("sdek.sdek_tariff")}</th>*}
                        <th width="25%">
                            {if !empty($shipment.register_id)}
                                {if !empty($shipment.notes)}
                                    {__("sdek.sdek_comment")}
                                {/if}
                            {else}
                                {__("sdek.sdek_comment")}
                            {/if}
                        </th>
                        <th width="10%">{__("try_on")}</th>
                        <th width="10%">{__("is_partial")}</th>
                        <th width="5%">{if !$shipment.register_id}{__("shipping_cost")}{/if}</th>
                        <th width="10%">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr class="cm-row-status" valign="top" >
                        <td class="{$no_hide_input}">
                            {if !empty($shipment.register_id)}
                                {$shipment.address}
                            {else}
                                {if !empty($shipment.rec_address)}
                                    <input type="hidden" name="add_sdek_info[{$shipment_id}][order][to_location][code]" value="{$rec_city_code}" />
                                    <input type="hidden" name="add_sdek_info[{$shipment_id}][order][to_location][address]" value="{$shipment.rec_address}" />
                                    {$shipment.rec_address}
                                {else}
                                    <select name="add_sdek_info[{$shipment_id}][order][delivery_point]" class="input-medium" id="item_modifier_type">
                                        <option value=""></option>
                                        {foreach from=$shipment.offices item=address_shipping}
                                            <option value="{$address_shipping.Code}" {if $address_shipping.Code == $sdek_pvz}selected="selected"{/if}>{$address_shipping.Address}</option>
                                        {/foreach}
                                    </select>
                                {/if}
                            {/if}
                        </td>
                        <td class="left nowrap">
                            {if !empty($shipment.register_id)}
                                {$shipment.notes}
                            {else}
                                <textarea class="input-textarea checkout-textarea" name="add_sdek_info[{$shipment_id}][order][comment]" cols="60" rows="3" value="">{$shipment.comments}</textarea>
                            {/if}
                        </td>
                        <td class="left nowrap">
                            {if !empty($shipment.register_id)}
                                {if $shipment.try_on == 'Y'}{__("yes")}{else}{__("no")}{/if}
                            {else}
                                <input type="hidden" name="add_sdek_info[{$shipment_id}][try_on]" value="N" />
                                <input type="checkbox" name="add_sdek_info[{$shipment_id}][try_on]" value="Y" {if $sdek_shipments.$shipment_id.try_on}checked="checked"{/if} />
                            {/if}
                        </td>
                        <td class="left nowrap">
                            {if !empty($shipment.register_id)}
                                {if $shipment.is_partial == 'Y'}{__("yes")}{else}{__("no")}{/if}
                            {else}
                                <input type="hidden" name="add_sdek_info[{$shipment_id}][is_partial]" value="N" />
                                <input type="checkbox" name="add_sdek_info[{$shipment_id}][is_partial]" value="Y" {if $sdek_shipments.$shipment_id.is_partial}checked="checked"{/if} />
                            {/if}
                        </td>
                        <td class="right nowrap">
                            {if $shipment.register_id}
                                <div class="pull-right">
                                    {capture name="tools_list"}
                                            <li>{btn type="list" text=__("sdek.update_status") dispatch="dispatch[orders.sdek_order_status]" form="sdek_form_`$shipment_id`"}</li>
                                            <li>{btn type="list" text=__("delete") dispatch="dispatch[orders.sdek_order_delete]" form="sdek_form_`$shipment_id`"}</li>
                                    {/capture}
                                    {dropdown content=$smarty.capture.tools_list}
                                </div>
                            {else}
                                <input type="text" name="add_sdek_info[{$shipment_id}][order][delivery_recipient_cost][value]" value="{if $order_info.status != $smarty.const.ORDER_STATUS_PAID}{$order_info.shipping_cost}{/if}" class="input-mini" size="6"/>
                            {/if}
                        </td>
                        <td class="right nowrap">
                            {if !$shipment.register_id}
                                {include file="buttons/button.tpl" but_role="submit" but_name="dispatch[orders.sdek_order_delivery]" but_text=__("send") but_target_form="sdek_form_`$shipment_id`"}
                            {else}
                                {$ticket_href = "{"orders.sdek_get_ticket?order_id=`$order_info.order_id`&shipment_id=`$shipment_id`"|fn_url}"}

                                {include file="buttons/button.tpl" but_role="submit-link" but_href=$ticket_href but_text=__("sdek.receipt_order") but_meta="cm-no-ajax"}
                            {/if}
                        </td>
                    </tr>
                    </tbody>
                    </table>
                </div>
                <div class="control-group">
                    <table class="table table-middle sdek-packages-table" width="100%">
                    <thead class="cm-first-sibling">
                    <tr>
                        <th width="10%">{__("weight_sdek")}</th>
                        <th width="10%">{__("length")}</th>
                        <th width="10%">{__("width")}</th>
                        <th width="10%">{__("height")}</th>
                        <th width="50%">{__("products")}</th>
                        <th width="10%">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    {if !empty($shipment.register_id)}
                        {foreach from=$shipment.packages item="package"}
                            <tr class="{cycle values="table-row , " reset=1}" id="box_add_package">
                                <td width="7%">
                                    {$package.weight}</td>
                                <td width="7%">
                                    {$package.length}</td>
                                <td width="7%">
                                    {$package.width}</td>
                                <td width="7%">
                                    {$package.height}</td>
                                <td width="50%">
                                    {foreach from=$package.products key="product_id" item="it_data"}
                                        {$order_info.products.$product_id.product} X {$it_data.amount} {__("items")} {if $it_data.is_paid == 'Y'}{__("is_paid")}{/if}</br>
                                    {/foreach}
                                <td width="15%" class="right">

                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        {math equation="x+1" x=$_key|default:0 assign="new_key"}
                        <tr class="{cycle values="table-row , " reset=1}" id="box_add_package">
                            <td width="7%">
                                <input type="text" name="add_sdek_info[{$shipment_id}][packages][{$new_key}][weight]" value="" class="input-mini" size="6" /></td>
                            <td width="7%">
                                <input type="text" name="add_sdek_info[{$shipment_id}][packages][{$new_key}][length]" value="" class="input-mini" size="6" /></td>
                            <td width="7%">
                                <input type="text" name="add_sdek_info[{$shipment_id}][packages][{$new_key}][width]" value="" class="input-mini" size="6" /></td>
                            <td width="7%">
                                <input type="text" name="add_sdek_info[{$shipment_id}][packages][{$new_key}][height]" value="" class="input-mini" size="6" /></td>
                            <td width="50%">
                                <table width="100%" class="table table-middle">
                                <thead class="cm-first-sibling">
                                <tr>
                                    <th width="50%">{__("name")}</th>
                                    <th width="20%">{__("quantity")}</th>
                                    <th width="20%">{__("is_paid")}</th>
                                </tr>
                                </thead>
                                {foreach from=$sdek_shipments.$shipment_id.products item="nth" key="product_id"}
                                    <tr>
                                        <td>{$order_info.products.$product_id.product}</td>
                                        <td><input type="text" name="add_sdek_info[{$shipment_id}][packages][{$new_key}][products][{$product_id}][amount]" value="{$order_info.products.$product_id.amount}" class="input-mini" size="6" /></td>
                                        <td><input type="text" name="add_sdek_info[{$shipment_id}][packages][{$new_key}][products][{$product_id}][is_paid]" value="{if $order_info.status == $smarty.const.ORDER_STATUS_PAID}{$order_info.products.$product_id.subtotal}{/if}" class="input-mini" size="6" /></td>
                                    <tr>
                                {/foreach}
                                </table>
                            <td width="15%" class="right">
                                {include file="buttons/multiple_buttons.tpl" item_id="add_package" tag_level="3"}
                            </td>
                        </tr>
                    {/if}
                    </tbody>
                    </table>
                </div>
                <div class="control-group">
                    {if !empty($shipment.sdek_status)}
                        {include file="common/subheader.tpl" title=__("shippings.sdek.status_title") target="#status_information_{$shipment_id}"}
                        <div id="status_information_{$shipment_id}" class="in collapse">
                            <table width="100%" class="table table-middle" >
                            <tr>
                                {*<td>
                                    {__("sdek.lang_status_code")}
                                </td>*}
                                <td>
                                    {__("sdek.date")}
                                </td>
                                <td>
                                    {__("sdek.lang_status_order")}
                                </td>
                                <td>
                                    {__("sdek.lang_city")}
                                </td>
                            </tr>
                            {foreach from=$shipment.sdek_status item=d_status}
                                <tr>
                                    {*<td>
                                        {$d_status.id}
                                    </td>*}
                                    <td>
                                        {$d_status.date}
                                    </td>
                                    <td>
                                        {$d_status.status}
                                    </td>
                                    <td>
                                        {$d_status.city}
                                    </td>
                                </tr>
                            {/foreach}
                            </table>
                        </div>
                    {/if}
                </div>
            </form>
            <hr />
        {/foreach}
    </div>
{/if}
