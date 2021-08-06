                        <p>
                        {if $user_type == 'P' || $user_data.user_type == 'P'}
                            {__("affiliate_text_letter_footer")}
                        {else}
                            {__("customer_text_letter_footer", ["[company_name]" => $settings.Company.company_name])}
                        {/if}
                        </p>
                    </td>
                </tr>
                <tr style="background-color: #f5f5f5;"> 
                    <td style="padding: 0px 20px;">
                        <div style="width: 100%;margin: 0 auto;max-width: 618px;padding: 20px 0;">
                            <div style="display: inline-block; width: 100%; padding-bottom: 25px;">
                                <div style="width: 35%;margin-right: 2%;padding-top: 7px;float: left;min-width: 250px;">
                                    <div style="display: inline-block; vertical-align: bottom; padding: 3px 0px;"><img src="{$images_dir}/addons/development/copyright.png" /></div>
                                    <div style="font: 16px Play,sans-serif; display: inline-block; color: #343434; vertical-align: bottom; padding: 10px 0px;">{if $smarty.const.TIME|date_format:"%Y" != $settings.Company.company_start_year}{$settings.Company.company_start_year}-{/if}{$smarty.const.TIME|date_format:"%Y"}
                                    </div>
                                    <div style="display: inline-block; vertical-align: bottom;"><a href="{""|fn_url}"><img src="{$images_dir}/addons/development/logo_bw.png" /></a></div>
                                </div>
                                <div style="width: 33%;float: left;margin-right: 2%;min-width: 240px;padding-top: 12px;">
                                    <p style="margin: 5px 0;line-height: 20px;font-weight: 300;color: #929292;font: 18px Play, sans-serif;">{$settings.Company.company_phone}</p>
                                </div>
                                
                                <div style="width: 10%;float: left;min-width: 100px;padding-top: 3px;">
                                    <span style="float: left;margin-right: 10px;">
                                        <a target="_blank" style="text-decoration: none;" href="http://vk.com/tennishouse">
                                            <img width="100%" vspace="0" hspace="0" border="0" alt="TennisHouse_VK" style="max-width:40px;display:block;" src="{$images_dir}/addons/development/vk_bw.png">
                                        </a>
                                    </span>
                                    <span style="float: left;">
                                        <a target="_blank" style="text-decoration: none;" href="http://www.facebook.com/TennisHouse.ru">
                                            <img width="100%" vspace="0" hspace="0" border="0" alt="TennisHouse_FB" style="max-width:40px;display:block;" src="{$images_dir}/addons/development/fb_bw.png">
                                        </a>
                                    </span>
                                </div>
                            </div>
                            <div style="width: 100%;display: inline-block;">
                                <span style="float: left;margin-right: 30px;">
                                    <a target="_blank" style="font: 15px Play, sans-serif;text-decoration: underline;color: #565a5c;" href="{"categories.view?category_id=`$smarty.const.RACKETS_CATEGORY_ID`"|fn_url:'C':'http'}">{__("catalog")}</a>
                                </span>
                                <span style="float: left;margin-right: 30px;">
                                    <a target="_blank" style="font: 15px Play, sans-serif;text-decoration: underline;color: #565a5c;" href="{"orders.search"|fn_url:'C':'http'}">{__("my_orders")}</a>
                                </span>
                                <span style="float: left;margin-right: 30px;">
                                    <a target="_blank" style="font: 15px Play, sans-serif;text-decoration: underline;color: #565a5c;" href="{"pages.view?page_id=`$smarty.const.LOYALITY_PROGRAM_PAGE_ID`"|fn_url:'C':'http'}">{__("loyality_program")}</a>
                                </span>
                            </div>
                            <div style="width: 100%;display: inline-block;color: #a0a0a0;font: 11px Play, sans-serif;padding-bottom: 20px;line-height: 12px;padding-top: 20px;">
                                {__("email_footer_text")}<br />
                                {__("phone_working_hours_text", ["[hours]" => __("phone_working_hours")])}<br />
                                {__("orders_working_hours_text", ["[hours]" => __("orders_working_hours")])}<br />
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            </table>
        </td>
    </tr>
</tbody>
</table>

</body>
</html>