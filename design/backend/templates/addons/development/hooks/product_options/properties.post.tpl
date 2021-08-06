{if $option_data.inventory == 'Y' && "SR"|strpos:$option_data.option_type !== false}
    {if $filter_features}
        <div class="control-group">
            <label class="control-label" for="elm_filter_filter_by_{$id}">{__("feature")}</label>
            <div class="controls">
                <select name="option_data[feature_id]">
                    <option value="" > - {__("none")} - </option>
                    {foreach from=$filter_features item=feature}
                        <option value="{$feature.feature_id}" {if $option_data.feature_id == $feature.feature_id}selected="selected"{/if}>{if $feature.group_description}{$feature.group_description}: {/if}{$feature.description}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    {/if}
    <div class="control-group">
        <label class="control-label" for="elm_show_on_catalog_{$id}">{__("show_on_catalog")}</label>
        <div class="controls">
        <label class="checkbox">
        <input type="hidden" name="option_data[show_on_catalog]" value="N" /><input type="checkbox" id="elm_show_on_catalog_{$id}" name="option_data[show_on_catalog]" value="Y" {if $option_data.show_on_catalog == "Y"}checked="checked"{/if}  />
        </label>
        </div>
    </div>
{/if}
