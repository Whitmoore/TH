{assign var="avisio_url" value="payment_notification.payment_aviso?payment=yandex_money"|fn_url:'C':'https'}
{assign var="check_url" value="payment_notification.check_order?payment=yandex_money"|fn_url:'C':'https'}
{assign var="fail_url" value="payment_notification.error?payment=yandex_money"|fn_url:'C':'https'}
{assign var="success_url" value="payment_notification.ok?payment=yandex_money"|fn_url:'C':'https'}

{hook name="rus_payments:yandex_money_payment_instructions"}
{include file="common/subheader.tpl" title=__("information") target="#yandex_money_payment_instruction_`$smarty.request.payment_id`"}
<div id="yandex_money_payment_instruction_{$smarty.request.payment_id}" class="in collapse">
{__("text_yandex_money_urls", ["[avisio_url]" => $avisio_url, "[check_url]" => $check_url, "[fail_url]" => $fail_url, "[success_url]" => $success_url])}
{/hook}

{hook name="rus_payments:yandex_market_processor_https_text"}
{assign var="check_https" value="HTTPS"|defined}

{if !$check_https}
{__("text_yandex_money_https")}
{/if}
{/hook}
</div>


{include file="common/subheader.tpl" title=__("settings") target="#yandex_money_payment_settings_`$smarty.request.payment_id`"}
<div id="yandex_money_payment_settings_{$smarty.request.payment_id}" class="in collapse">

<div class="control-group">
    <label class="control-label" for="mode">{__("test_live_mode")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][mode]" id="mode">
            <option value="test" {if $processor_params.mode == "test"}selected="selected"{/if}>{__("test")}</option>
            <option value="live" {if $processor_params.mode == "live"}selected="selected"{/if}>{__("live")}</option>
        </select>
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="shop_id">{__("shop_id")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][shop_id]" id="shop_id" value="{$processor_params.shop_id}" class="input-text-large"  size="60" />
    </div>
</div>
<div class="control-group">
    <label class="control-label" for="scid">{__("scid")}:</label>
    <div class="controls">
        <input type="text" name="payment_data[processor_params][scid]" id="scid" value="{$processor_params.scid}" class="input-text-large"  size="60" />
    </div>
</div>

<div class="control-group" id="yandex_md5_div_{$smarty.request.payment_id}">
    <label class="control-label" for="md5_shoppassword_{$smarty.request.payment_id}">{__("md5_shoppassword")}:</label>
    <div class="controls">
        <input type="text" maxlength="20" size="21" name="payment_data[processor_params][md5_shoppassword]" id="md5_shoppassword_{$smarty.request.payment_id}" value="{if $ya_md5}{$ya_md5}{else}{$processor_params.md5_shoppassword}{/if}" class="input-text-large span4"  size="60" />
        <br />
        <a href="#" id="md5_generate_link_{$smarty.request.payment_id}">{__("generate")}</a>
    </div>
    
    <script type="text/javascript" class="cm-ajax-force">
    //<![CDATA[
        (function(_, $) {
        $(document).ready(function() {
          $('#md5_generate_link_{$smarty.request.payment_id}').on('click', fn_get_md5_password_{$smarty.request.payment_id});
        });

        function fn_get_md5_password_{$smarty.request.payment_id}() {
          var md5_shoppassword = $('#md5_shoppassword_{$smarty.request.payment_id}').val();
          $.ceAjax('request', '{fn_url("payments.yandex_get_md5_password")}', {
          data: {
              payment: 'yandex_money',
              md5_shoppassword: md5_shoppassword,
              result_ids: 'yandex_md5_div_' + {$smarty.request.payment_id},
              payment_id: {$smarty.request.payment_id},
          },
          });
        }
        }(Tygh, Tygh.$));
    //]]>
    </script>
<!--yandex_md5_div_{$smarty.request.payment_id}--></div>

</div>

<div class="control-group">
    <label class="control-label" for="logging">{__("addons.rus_payments.logging")}:</label>
    <div class="controls">
        <input type="checkbox" name="payment_data[processor_params][logging]" id="logging" value="Y" {if $processor_params.logging == 'Y'} checked="checked"{/if} class="input-text-large"  size="60" />
    </div>
</div>

{include file="common/subheader.tpl" title=__("yandex_payment_types") target="#yandex_payment_types_`$smarty.request.payment_id`"}
<div id="yandex_payment_types_{$smarty.request.payment_id}" class="in collapse">

    <fieldset>
    
    <div class="control-group">
        <label class="control-label" for="yandex_money_payment_yandex">{__("yandex_payment_yandex")}:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][payments][pc]" id="yandex_money_payment_yandex" value="PC"{if $processor_params.payments && $processor_params.payments.pc} checked="checked"{/if}></div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="yandex_money_payment_card">{__("yandex_payment_card")}:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][payments][ac]" id="yandex_money_payment_card" value="AC"{if $processor_params.payments && $processor_params.payments.ac} checked="checked"{/if}></div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="yandex_money_payment_terminal">{__("yandex_payment_terminal")}:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][payments][gp]" id="yandex_money_payment_terminal" value="GP"{if $processor_params.payments && $processor_params.payments.gp} checked="checked"{/if}></div>
    </div>
    
    <div class="control-group">
        <label class="control-label" for="yandex_money_payment_phone">{__("yandex_payment_phone")}:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][payments][mc]" id="yandex_money_payment_phone" value="MC"{if $processor_params.payments && $processor_params.payments.mc} checked="checked"{/if}></div>
    </div>

    <div class="control-group">
        <label class="control-label" for="yandex_money_payment_webmoney">{__("yandex_payment_webmoney")}:</label>
        <div class="controls"><input type="checkbox" name="payment_data[processor_params][payments][nv]" id="yandex_money_payment_webmoney" value="NV"{if $processor_params.payments && $processor_params.payments.nv} checked="checked"{/if}></div>
    </div>
    
    </fieldset>

</div>

<div class="control-group">
    <label class="control-label" for="currency_{$payment_id}">{__("currency")}:</label>
    <div class="controls">
        <select name="payment_data[processor_params][currency]" id="currency_{$payment_id}">
            <option value="RUB"{if $processor_params.currency == "RUB"} selected="selected"{/if}>{__("currency_code_rur")}</option>
            <option value="USD"{if $processor_params.currency == "USD"} selected="selected"{/if}>{__("currency_code_usd")}</option>
            <option value="EUR"{if $processor_params.currency == "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
            <option value="UAH"{if $processor_params.currency == "UAH"} selected="selected"{/if}>{__("currency_code_uah")}</option>
            <option value="KZT"{if $processor_params.currency == "KZT"} selected="selected"{/if}>{__("currency_code_kzt")}</option>
        </select>
    </div>
</div>
