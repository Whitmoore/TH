<!DOCTYPE html>
<html>
<head>
<title>{$title}</title>
<style>
{literal}
.form-title    {
    background-color: #ffffff;
    color: #141414;
    font-weight: bold;
}

.form-field-caption {
    font-style:italic;
}

.table-row {
    background-color: #f1f3f7;
}

.table-head {
    background-color: #bbbbbb;
}
{/literal}
</style>
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f7f7f7;">
<tbody>
    {if $body.pretitle}<tr>
        <td style="padding: 0;vertical-align: middle;text-alin: center;font-size: 0;">
            <table style="border-collapse: collapse;border-spacing: 0;vertical-align: middle;" cellspacing="0" cellpadding="0" border="0" align="center">
                <tbody><tr>
                    <td style="width: 100%;max-width: 648px;padding-bottom: 5px;padding-top: 5px;padding-left: 5px;vertical-align: middle;text-align: left;color: #777777;font-size: 11px;line-height: 15px;">{$body.pretitle}</td>
                </tr></tbody>
            </table>
        </td>
    </tr>{/if}
    <tr>
        <td>
            {if $is_modest}<a target="_blank" style="text-decoration: none;" href="{""|fn_url:'C':'http'}">{/if}
            <table cellspacing="0" cellpadding="0" style="width: 100%;max-width: 648px;margin: 0 auto;border:0;border-left:1px solid #ccc;border-right:1px solid #ccc;">
            <tbody>
                <tr>
                    <td style="background-color: #464248;padding: 20px 50px;">
                        <table cellspacing="0" cellpadding="0" style="width: 100%;">
                        <tbody>
                            <tr>
                                <td align="center">
                                    {if !$is_modest}<a target="_blank" style="text-decoration: none;" href="{""|fn_url:'C':'http'}">{/if}
                                        <img src="{$images_dir}/addons/development/dark_bg_logo_220.png" width="220" height="55" alt="TennisHouse_logo" />
                                    {if !$is_modest}</a>{/if}
                                </td>
<!--                                <td style="color: #fff;font: 21px Play,sans-serif;text-align: right;">
                                    {$settings.Company.company_phone}
                                </td>-->
                            </tr>
                        </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td><table style="border-collapse: collapse;background:#fafafa;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" border="0" align="center">
                    <tbody><tr>
                        <td style="height:37px;border-bottom: 1px solid #eeeeee;padding: 0;vertical-align: middle;text-align: left;font-size: 0;" height="37">
                            <div style="display: inline-block;vertical-align: middle;text-align: center;padding: 0;width: 12.5%;max-width: 80px;">
                                <table style="height:37px;border-collapse: collapse;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" align="center">
                                    <tbody><tr>
                                        <td style="padding: 0;vertical-align: middle;text-align: center;font: 15px Play,sans-serif;line-height: 15px;font-weight: normal;color: #777777;">
                                            {if !$is_modest}<a target="_blank" href="{"categories.view?category_id=`$smarty.const.RACKETS_CATEGORY_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display:block;color:#777777;"  data-title="{__('rackets')}">{/if}
                                                <span style="color:#777777;">{__("rackets")}</span>
                                            {if !$is_modest}</a>{/if}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </div>
                            <div style="display: inline-block;vertical-align: middle;text-align: center;padding: 0;width: 12.5%;max-width: 80px;">
                                <table style="height:37px;border-collapse: collapse;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" align="center">
                                    <tbody><tr>
                                        <td style="padding: 0;vertical-align: middle;text-align: center;font: 15px Play,sans-serif;line-height: 15px;font-weight: normal;color: #777777;">
                                            {if !$is_modest}<a target="_blank" href="{"categories.view?category_id=`$smarty.const.BALLS_CATEGORY_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display:block;color:#777777;"  data-title="{__('balls_short')}">{/if}
                                                <span style="color:#777777;">{__("balls_short")}</span>
                                            {if !$is_modest}</a>{/if}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </div>
                            <div style="display: inline-block;vertical-align: middle;text-align: center;padding: 0;width: 12.5%;max-width: 80px;">
                                <table style="height:37px;border-collapse: collapse;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" align="center">
                                    <tbody><tr>
                                        <td style="padding: 0;vertical-align: middle;text-align: center;font: 15px Play,sans-serif;line-height: 15px;font-weight: normal;color: #777777;">
                                            {if !$is_modest}<a target="_blank" href="{"categories.view?category_id=`$smarty.const.BAGS_CATEGORY_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display:block;color:#777777;"  data-title="{__('bags')}">{/if}
                                                <span style="color:#777777;">{__("bags")}</span>
                                            {if !$is_modest}</a>{/if}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </div>
                            <div style="display: inline-block;vertical-align: middle;text-align: center;padding: 0;width: 12.5%;max-width: 80px;">
                                <table style="height:37px;border-collapse: collapse;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" align="center">
                                    <tbody><tr>
                                        <td style="padding: 0;vertical-align: middle;text-align: center;font: 15px Play,sans-serif;line-height: 15px;font-weight: normal;color: #777777;">
                                            {if !$is_modest}<a target="_blank" href="{"categories.view?category_id=`$smarty.const.APPAREL_CATEGORY_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display:block;color:#777777;"  data-title="{__('apparel')}">{/if}
                                                <span style="color:#777777;">{__("apparel")}</span>
                                            {if !$is_modest}</a>{/if}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </div>
                            <div style="display: inline-block;vertical-align: middle;text-align: center;padding: 0;width: 12.5%;max-width: 80px;">
                                <table style="height:37px;border-collapse: collapse;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" align="center">
                                    <tbody><tr>
                                        <td style="padding: 0;vertical-align: middle;text-align: center;font: 15px Play,sans-serif;line-height: 15px;font-weight: normal;color: #777777;">
                                            {if !$is_modest}<a target="_blank" href="{"categories.view?category_id=`$smarty.const.SHOES_CATEGORY_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display:block;color:#777777;"  data-title="{__('shoes')}">{/if}
                                                <span style="color:#777777;">{__("shoes")}</span>
                                            {if !$is_modest}</a>{/if}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </div>
                            <div style="display: inline-block;vertical-align: middle;text-align: center;padding: 0;width: 12.5%;max-width: 81px;">
                                <table style="height:37px;border-collapse: collapse;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" align="center">
                                    <tbody><tr>
                                        <td style="padding: 0;vertical-align: middle;text-align: center;font: 15px Play,sans-serif;line-height: 15px;font-weight: normal;color: #777777;">
                                            {if !$is_modest}<a target="_blank" href="{"categories.view?category_id=`$smarty.const.STRINGS_CATEGORY_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display:block;color:#777777;"  data-title="{__('strings')}">{/if}
                                                <span style="color:#777777;">{__("strings")}</span>
                                            {if !$is_modest}</a>{/if}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </div>
                            <div style="display: inline-block;vertical-align: middle;text-align: center;padding: 0;width: 12.5%;max-width: 81px;">
                                <table style="height:37px;border-collapse: collapse;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" align="center">
                                    <tbody><tr>
                                        <td style="padding: 0;vertical-align: middle;text-align: center;font: 15px Play,sans-serif;line-height: 15px;font-weight: normal;color: #777777;">
                                            {if !$is_modest}<a target="_blank" href="{"categories.view?category_id=`$smarty.const.BALL_MACHINE_CATEGORY_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display:block;color:#777777;"  data-title="{__('ball_machines')}">{/if}
                                                <span style="color:#777777;">{__("ball_machines")}</span>
                                            {if !$is_modest}</a>{/if}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </div>
                            <div style="display: inline-block;vertical-align: middle;text-align: center;padding: 0;width: 12.5%;max-width: 81px;">
                                <table style="height:37px;border-collapse: collapse;border-spacing: 0;width: 100%;" cellspacing="0" cellpadding="0" align="center">
                                    <tbody><tr>
                                        <td style="padding: 0;vertical-align: middle;text-align: center;font: 15px Play,sans-serif;line-height: 15px;font-weight: normal;color: #777777;">
                                            {if !$is_modest}<a target="_blank" href="{"categories.view?category_id=`$smarty.const.STR_MACHINE_CATEGORY_ID`"|fn_url:'C':'http'}" style="text-decoration:none;display:block;color:#777777;"  data-title="{__('str_machines')}">{/if}
                                                <span style="color:#777777;">{__("str_machines")}</span>
                                            {if !$is_modest}</a>{/if}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </div>
                        </td>
                    </tr></tbody>
                    </table></td>
                </tr>
            </tbody>
            </table>
            {if $is_modest}</a>{/if}
            <table cellspacing="0" cellpadding="0" style="width: 100%;max-width: 648px;margin: 0 auto;border:0;border-left:1px solid #ccc;border-right:1px solid #ccc;">
            <tbody>
                <tr>
                    <td style="padding: 10px 5px 0px 5px;background-color: #fff;font: 18px Play,sans-serif;max-width: 638px;">