{* rus_build_exim_1c dbazhenov *}
{capture name="mainbox"}
<div id="create_prices">
    <form action="{""|fn_url}" method="post" name="create_prices" class="cm-ajax cm-comet" enctype="multipart/form-data">
    <input type="hidden" name="fake" value="1" />
    <fieldset>
    {include file="common/subheader.tpl" title=__("taxes") target="#taxes"}
    <div id="taxes" class="collapse in">
        {if $addons.rus_exim_1c.exim_1c_add_tax == "Y"}
            <table class="table table-middle" width="100%">
                <thead class="cm-first-sibling">
                    <tr>
                        <th width="15%">{__("tax")}&nbsp;CS-Cart</th>
                        <th width="70%">{__("tax")}&nbsp;1C</th> 
                        <th width="15%"></th>  
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$taxes_data item="tax_data" key="_key" name="tax_1c"}
                        <tr class="cm-row-item">
                            <td width="15%">
                                <select id="tax_id" name="taxes_1c[{$_key}][tax_id]" class="span3">
                                    {foreach from=$taxes item="tax"}
                                        {if $tax_data.tax_id != $tax.tax_id}
                                            <option value="{$tax.tax_id}">{$tax.tax}</option>
                                        {/if}
                                    {/foreach}
                                    <option value="{$tax_data.tax_id}" selected="selected">{$tax_data.tax_id|fn_get_tax_name}</option>
                                </select>
                            </td>
                            <td width="70%"><input type="text" name="taxes_1c[{$_key}][tax_1c]" value="{$tax_data.tax_1c}" class="span8" /></td>
                            <td width="15%">{include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}</td>
                        </tr>
                    {/foreach}
                    {math equation="x+1" x=$_key|default:0 assign="new_key"}
                    <tr class="cm-row-item" id="box_add_tax">
                        <td width="15%">
                            <select id="tax_id" name="taxes_1c[{$new_key}][tax_id]" class="span3">
                                {foreach from=$taxes item="tax"}
                                    <option value="{$tax.tax_id}">{$tax.tax}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td width="70%"><input type="text" name="taxes_1c[{$new_key}][tax_1c]" class="span8" /></td>
                        <td width="15%">{include file="buttons/multiple_buttons.tpl" item_id="add_tax"}</td>
                    </tr>
                </tbody>
            </table>
        {else}
            <p class="no-items">{__("off_function_tax_1c")}</p>    
        {/if}
    </div>
    <hr>
    {include file="common/subheader.tpl" title=__("prices") target="#prices"}
    <div id="prices" class="collapse in">
        {if $addons.rus_exim_1c.exim_1c_export_add_prices == "Y"}
            {assign var="usergroups" value="C"|fn_get_usergroups}
            
            <table class="table" width="100%">
                <tr>
                    <td width="20%">{__("base_price")} ({$currencies.$primary_currency.symbol nofilter}) :</td>
                    <td width="80%"><input type="text" name="base_price_1c" value="{$base_price_1c}" class="span9" /></td>
                </tr>
                <tr>
                    <td width="20%">{__("list_price")} ({$currencies.$primary_currency.symbol nofilter}) :</td>
                    <td width="80%"><input type="text" name="list_price_1c" value="{$list_price_1c}" class="span9" /></td>
                </tr>
            </table>
        
            <table class="table table-middle" width="100%">
                <thead class="cm-first-sibling">
                    <tr>
                        <th width="15%">{__("usergroup")}&nbsp;CS-Cart</th>
                        <th width="70%">{__("price")}&nbsp;1C</th> 
                        <th width="15%"></th>  
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$prices_data item="price" key="_key" name="price_1c"}
					    {assign var="default_usergroup_name" value=""}
                        <tr class="cm-row-item">
                            <td width="15%">
                                <select id="usergroup_id" name="prices_1c[{$_key}][usergroup_id]" class="span3">
                                    {foreach from=fn_get_default_usergroups() item="usergroup"}
                                        {if $usergroup.usergroup_id != '0'}
                                            {if $price.usergroup_id != $usergroup.usergroup_id}
                                                <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                                            {else}
                                                {assign var="default_usergroup_name" value=$usergroup.usergroup}
                                            {/if}
                                        {/if}
                                    {/foreach}
                                    {foreach from=$usergroups item="usergroup"}
                                        {if $price.usergroup_id != $usergroup.usergroup_id}
                                            <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                                        {/if}
                                    {/foreach}
                                    <option value="{$price.usergroup_id}" selected="selected">{if $default_usergroup_name}{$default_usergroup_name}{else}{$price.usergroup_id|fn_get_usergroup_name}{/if}</option>
                                </select>
                            </td>
                            <td width="70%"><input type="text" name="prices_1c[{$_key}][price_1c]" value="{$price.price_1c}" class="span8" /></td>
                            <td width="15%">{include file="buttons/clone_delete.tpl" microformats="cm-delete-row" no_confirm=true}</td>
                        </tr>
                    {/foreach}
                    {math equation="x+1" x=$_key|default:0 assign="new_key"}
                    <tr class="cm-row-item" id="box_add_price">
                        <td width="15%">
                            <select id="usergroup_id" name="prices_1c[{$new_key}][usergroup_id]" class="span3">
                                {foreach from=fn_get_default_usergroups() item="usergroup"}
                                    {if $usergroup.usergroup_id != '0'}
                                        <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                                    {/if}
                                {/foreach}
                                {foreach from=$usergroups item="usergroup"}
                                    <option value="{$usergroup.usergroup_id}">{$usergroup.usergroup}</option>
                                {/foreach}
                            </select>
                        </td>
                        <td width="70%"><input type="text" name="prices_1c[{$new_key}][price_1c]" class="span8" /></td>
                        <td width="15%">{include file="buttons/multiple_buttons.tpl" item_id="add_price"}</td>
                    </tr>
                </tbody>
            </table>
            <br />
            <br />
            <br />
            {if $addons.rus_exim_1c.exim_1c_export_check_prices == "Y"}
                <h2>{__("test")}</h2>
                <table class="table table-middle">
                    {foreach from=$resul_test item="price" key="_key"}
                        <tr>
                            <td>{$price.price_1c}&nbsp;{if $price.price_1c == "base"}({__("base_price")}){/if}&nbsp;</td>
                            <td>{if $price.valid == "1"}{__("correct_1c_price")}{else}{__("incorrect_1c_price")}{/if}</td>
                        </tr>
                    {/foreach}
                </table>
            {/if}
        {else}
            <p class="no-items">{__("off_function_import_prices")}</p>
        {/if}
    </div>
    </fieldset>
    </form>    
<!--create_prices--></div>
{/capture}

{capture name="buttons"}
    {if $addons.rus_exim_1c.exim_1c_export_add_prices}
        {include file="buttons/button.tpl" but_text=__("save") but_name="dispatch[1c.save_prices]" but_role="submit-link" but_target_form="create_prices"}
    {/if}
{/capture}

{include file="common/mainbox.tpl" title=__("1c_prices") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons}




