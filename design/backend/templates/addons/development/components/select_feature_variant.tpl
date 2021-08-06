{if !$params}{$params=[]}{/if}
{$features = $variant_id|fn_get_features_by_variant:$params}
{$feature_id = 0}
<select data-ca-target-id="{$key}" data-ca-data-name="{$data_name}" onchange="fn_get_feature_variants($(this), {$id});">
    <option value=""> - {__("choose_feature")} - </option>
    {foreach from=$features item=feature}
        {if $feature.selected}{$feature_id = $feature.feature_id}{/if}
        <option value="{$feature.feature_id}" {if $feature.selected}selected="selected"{/if}>{if $feature.group_description}{$feature.group_description}: {/if}{$feature.description}</option>
    {/foreach}
</select>
{include file="addons/development/components/select_feature_variant_id.tpl" key=$key feature_id=$feature_id variant_id=$variant_id feature_variants=$features.$feature_id.variants obj_id="feature_variants_`$id`_`$key`" variant_id_name=$variant_id_name}
