<div id="breadcrumbs_{$block.block_id}">

{if $breadcrumbs && $breadcrumbs|@sizeof > 1}
    <div itemscope itemtype="http://schema.org/BreadcrumbList" class="ty-breadcrumbs {if $image_title}ty-image-title-breadcrumbs{/if} clearfix">
        {strip}
            {foreach from=$breadcrumbs item="bc" name="bcn" key="key"}
                <span itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                {if $key != "0"}
                    <span class="ty-breadcrumbs__slash">/</span>
                {/if}
                {if $bc.link}
                    <a itemprop="item" href="{$bc.link|fn_url}" class="ty-breadcrumbs__a{if $additional_class} {$additional_class}{/if}"{if $bc.nofollow} rel="nofollow"{/if}><span itemprop="name">{$bc.title|strip_tags|escape:"html" nofilter}</span></a>
                {else}
                    <span itemprop="item" class="ty-breadcrumbs__current"><span itemprop="name">{$bc.title|strip_tags|escape:"html" nofilter}</span></span>
                {/if}
                <meta itemprop="position" content="{$smarty.foreach.bcn.iteration}" />
                </span>
            {/foreach}
            {*include file="common/view_tools.tpl"*}
        {/strip}
    </div>
{/if}

<!--breadcrumbs_{$block.block_id}--></div>
