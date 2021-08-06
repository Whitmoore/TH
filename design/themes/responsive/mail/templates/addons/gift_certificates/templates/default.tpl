<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <title>{__("gift_certificate")}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="en" />
            
    <style type="text/css">
    {literal}
    body,th,td,tt,div,span,p {
        font-family: Arial, sans-serif;
        font-size: 13px;
        padding: 0px;
        margin: 0px;
    }
    @font-face {
        font-family: 'THF400';
        font-weight: 400;
        font-style: normal;
        {/literal}
        src:url("{$fonts_path}/THF400/THF400.eot?") format('eot');
        src:url("{$fonts_path}/THF400/THF400.eot?#iefix") format("embedded-opentype"),
        url("{$fonts_path}/THF400/THF400.woff") format("woff"),
        url("{$fonts_path}/THF400/THF400.ttf") format("truetype");
        {literal}
    }
    @font-face {
        font-family: 'THF700';
        font-weight: 400;
        font-style: normal;
        {/literal}
        src:url("{$fonts_path}/THF700/THF700.eot?") format('eot');
        src:url("{$fonts_path}/THF700/THF700.eot?#iefix") format("embedded-opentype"),
        url("{$fonts_path}/THF700/THF700.woff") format("woff"),
        url("{$fonts_path}/THF700/THF700.ttf") format("truetype");
        {literal}
    }
    .ty-gc-image {
        display: block;
        height: 806px;
        width: 620px;
        position: relative;
    }
    .gc-amount-text {
        font-size: 6.5rem;
        font-family: 'THF700';
        font-weight: normal;
        position: absolute;
        top: 25rem;
        color: #fff;
        left: 10.4%;
        width: 80%;
        text-align: center;
    }
    .ty-rub {
        font-family: 'THF700';
        font-size: 6.5rem;
    }
    .gc-comment-text {
        font-size: 1.6rem;
        font-family: 'THF400';
        font-weight: normal;
        position: absolute;
        top: 37rem;
        color: #000;
        left: 4rem;
        width: 80%;
        text-align: left;
    }
    {/literal}
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="main-table">
<tr>
    <td align="center" style="width: 100%; height: 100%;">
    <div class="ty-gc-image">
        <img src="{$images_dir}/addons/gift_certificates/gc_image.jpg" width="620px" height="806px" />
        <div class="gc-amount-text">{if $gift_cert_data}{$gift_cert_data.amount|fn_format_rate_value:"":$currencies.$secondary_currency.decimals:".":"":$currencies.$secondary_currency.coefficient}{$currencies.$secondary_currency.symbol nofilter}{/if}</div>
        <div class="gc-comment-text">{if $gift_cert_data.message}{$gift_cert_data.message nofilter}{/if}</div>
    </div>
    </td>
</tr>
</table>
</body>
</html>