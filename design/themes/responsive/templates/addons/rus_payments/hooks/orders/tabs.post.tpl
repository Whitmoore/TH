{if $order_info.payment_method.processor_params}
    {if $order_info.payment_method.processor_params.sbrf_enabled}
        {assign var="sbrf_settings" value=$order_info.payment_method.processor_params}
        {if $sbrf_settings.sbrf_enabled=="Y"}
            <div id="content_payment_information" class="{if $selected_section != "payment_information"}hidden{/if}">
                    <div class="sbrf">
                        <table class="ty-table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_recepient")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_recepient_name|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_inn")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_inn|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_kpp")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_kpp|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_okato_code")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_okato_code|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_settlement_account")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_settlement_account|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_bank")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_bank|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_bik")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_bik|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_cor_account")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_cor_account|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_kbk")}</td>
                                        <td class="ty-left">{$sbrf_settings.sbrf_kbk|unescape}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left">{__("sbrf_payment")}</td>
                                        <td class="ty-left">{__("sbrf_order_payment")} №{$order_info.order_id}</td>
                                    </tr>
                                    <tr>
                                        <td class="ty-left"><img src="{"orders.get_qr?order_id=`$order_info.order_id`"|fn_url}" alt="QrCode" width="{$sbrf_settings.sbrf_qr_print_size}" height="{$sbrf_settings.sbrf_qr_print_size}" /></td>
                                        <td class="ty-left">{__("sbrf_qr_info")}</td>
                                    </tr>
                                </tbody>
                        </table>
                    </div>
            </div>
        {else}
            <div id="content_payment_information" class="{if $selected_section != "payment_information"}hidden{/if}">
                <p class="ty-no-items">{__("sbrf_information_not_found")}</p>
            </div>
        {/if}
    {/if}
{/if}