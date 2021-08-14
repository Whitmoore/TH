{assign var="callback_url" value="paykeeper.return?pid=$payment_id"|fn_url:'C':'http'}

{if ($payment_id)}
{__("paykeeper_callback_notice")}
<br>
{$callback_url}
{else}
{__("paykeeper_callback_notice_new")}
{/if}

<div class="control-group">
    <label class="control-label" for="account">{__("paykeeper_secret")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][secret]" id="account" value="{$processor_params.secret}" >
    </div>
</div>

<div class="control-group">
    <label class="control-label" for="secret">{__("paykeeper_url")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][url]" id="secret"
               {if ($processor_params.url == '')} value="http://demo.paykeeper.ru/create/"  {else} value="{$processor_params.url}" {/if}  >
    </div>
</div>

{assign var="statuses" value=$smarty.const.STATUSES_ORDER|fn_get_simple_statuses}
<div class="control-group">
    <label class="control-label" for="order_status">{__("paykeeper_order_status")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][order_status]" id="order_status">
            {foreach from=$statuses item="s" key="k"}
                <option value="{$k}" {if ($processor_params.order_status == $k)}selected="selected"{/if}>{$s}</option>
            {/foreach}
        </select>
    </div>
</div>
