<div style="display: inline-block" id="{$obj_id}">
    {if $feature_id && $feature_variants}
        <select name="{$data_name}[{$key}]">
            <option value=""> - {__("choose_feature_variant")} - </option>
            {foreach from=$feature_variants item=variant}
                <option value="{$variant.variant_id}" {if $variant_id == $variant.variant_id}selected="selected"{/if}>{$variant.variant}</option>
            {/foreach}
        </select>
    {/if}
<!--{$obj_id}--></div>
