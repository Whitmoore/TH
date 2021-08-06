
{if $predefined_meta.robots}
    <meta name="robots" content="{$predefined_meta.robots}" />
{elseif !$smarty.request|fn_seo_is_indexed_page}
    <meta name="robots" content="noindex{if "HTTPS"|defined},nofollow{/if}" />
{else}
    {if $predefined_meta.canonical}
        <link rel="canonical" href="{$predefined_meta.canonical}" />
    {elseif $seo_canonical.current}
        <link rel="canonical" href="{$seo_canonical.current}" />
        {if $seo_canonical.prev}
            <link rel="prev" href="{$seo_canonical.prev}" />
        {/if}

        {if $seo_canonical.next}
            <link rel="next" href="{$seo_canonical.next}" />
        {/if}
    {/if}
{/if}

{if $languages|sizeof > 1}
{foreach from=$languages item="language"}
<link title="{$language.name}" dir="rtl" type="text/html" rel="alternate" hreflang="{$language.lang_code}" href="{$config.current_url|fn_link_attach:"sl=`$language.lang_code`"|fn_url}" />
{/foreach}
{/if}

