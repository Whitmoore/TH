<div class="control-group">
    <label for="block_{$html_id}_ajax_content" class="control-label">{__("ajax_block_content")}</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="block_data[properties][ajax_content]" value="N" />
            <input type="checkbox" name="block_data[properties][ajax_content]" id="block_{$html_id}_ajax_content" value="Y" {if $block.properties.ajax_content == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>
<div class="control-group">
    <label for="block_{$html_id}_capture_content" class="control-label">{__("capture_block_content")}</label>
    <div class="controls">
        <label class="checkbox">
            <input type="hidden" name="block_data[properties][capture_content]" value="N" />
            <input type="checkbox" name="block_data[properties][capture_content]" id="block_{$html_id}_capture_content" value="Y" {if $block.properties.capture_content == "Y"}checked="checked"{/if}/>
        </label>
    </div>
</div>
<div class="control-group">
    <label for="block_{$html_id}_title_header" class="control-label">{__("title_header")}</label>
    <div class="controls">
        <select id="block_{$html_id}_template" name="block_data[properties][title_header]"  class="cm-reload-form">
            <option value="" {if $block.properties.title_header == ''}selected="selected"{/if}>{__("no_header")}</option>
            <option value="1" {if $block.properties.title_header == '1'}selected="selected"{/if}>{__("header_1_tag")}</option>
            <option value="2" {if $block.properties.title_header == '2'}selected="selected"{/if}>{__("header_2_tag")}</option>
            <option value="3" {if $block.properties.title_header == '3'}selected="selected"{/if}>{__("header_3_tag")}</option>
            <option value="4" {if $block.properties.title_header == '4'}selected="selected"{/if}>{__("header_4_tag")}</option>
            <option value="5" {if $block.properties.title_header == '5'}selected="selected"{/if}>{__("header_5_tag")}</option>
            <option value="6" {if $block.properties.title_header == '6'}selected="selected"{/if}>{__("header_6_tag")}</option>
        </select>
    </div>
</div>
