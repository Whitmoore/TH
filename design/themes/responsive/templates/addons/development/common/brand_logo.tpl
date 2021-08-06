{if $brand.image_pair}
{if !$img_height}
    {if $brand_variant_id == $smarty.const.KIRSCHBAUM_BRAND_ID || $brand_variant_id == $smarty.const.SLAZENGER_BRAND_ID}
        {$img_height = "30"}
    {elseif $brand_variant_id == $smarty.const.DFC_BRAND_ID || $brand_variant_id == $smarty.const.SIS_BRAND_ID}
        {$img_height = "40"}
    {else}
        {$img_height = "19"}
    {/if}
{/if}
{if $itemprop}
<meta itemprop="{$itemprop}" content="{$brand.variant}" />
{$itemprop = ''}
{/if}
{include file="common/image.tpl"
show_detailed_link=false
images=$brand.image_pair
no_ids=true
image_height=$img_height
keep_transparent=true}
{/if}