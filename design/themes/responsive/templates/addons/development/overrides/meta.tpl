{hook name="index:meta"}
{if $display_base_href}
<base href="{$config.current_location}/" />
{/if}
<meta http-equiv="Content-Type" content="text/html; charset={$smarty.const.CHARSET}" data-ca-mode="{$store_trigger}" />
{hook name="index:meta_description"}
{if $runtime.controller == 'products' && $runtime.mode == 'view'}
    {$meta_descr = __('product_meta_description', ['[category]' => $product.category_main_title|fn_strtolower, '[product]' => $product.product, '[price]' => $product.price|fn_format_price, '[date]' => $smarty.const.TIME|date_format:$settings.Appearance.date_format, '[product_code]' => $product.product_code])}
    <meta name="description" content="{$meta_descr}" />
    <meta property="og:title" content="{$product.product}" />
    <meta property="og:type" content="product" />
    <meta property="og:site_name" content="{$settings.Company.company_name}" />
    {if $product.main_pair.detailed.http_image_path}
    <meta property="og:image" content="{$product.main_pair.detailed.http_image_path}" />
    {/if}
    <meta property="og:description" content="{$meta_descr}" />
    <meta property="og:url" content="{"products.view?product_id=`$product.product_id`"|fn_url}"/>
{elseif $runtime.controller == 'players' && $runtime.mode == 'list'}
    {$meta_descr = __('players_meta_description', ['[players]' => $meta_players])}
    <meta name="description" content="{$meta_descr}" />
{elseif $runtime.controller == 'players' && $runtime.mode == 'view'}
    {$meta_descr = __('player_share_buttons_description', ['[player]' => "`$player_data.player`. `$player_data.player_en`", '[birthplace]' => {$player_data.birthplace}, '[racket]' => {$racket}])}
    <meta property="og:type"   content="profile" />
    <meta property="og:url"    content="{"players.view?player_id=`$player_data.player_id`"|fn_url}" /> 
    <meta property="og:title"  content="{$player_data.player}" /> 
    <meta property="og:image"  content="{$player_data.main_pair.detailed.http_image_path}" /> 
    <meta property="og:description" content="{$meta_descr}" />
    <meta name="description" content="{$meta_descr}" />
{else}
    <meta name="description" content="{$meta_description|html_entity_decode:$smarty.const.ENT_COMPAT:"UTF-8"|default:$location_data.meta_description}" />
{/if}
{/hook}
{if $meta_keywords}
    <meta name="keywords" content="{$meta_keywords|default:$location_data.meta_keywords}" />
{/if}
{/hook}
{$location_data.custom_html nofilter}
<meta name="yandex-verification" content="597256756fd89e2a" />
<meta name="google-site-verification" content="nQeXw-29ma0tSQyhHhH3XWpTGUS9cQ1lI7o-RYkyiQk" />