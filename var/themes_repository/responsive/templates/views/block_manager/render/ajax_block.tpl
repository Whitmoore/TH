<div id="ajax_block_content_{$block.block_id}">
    <form action="{""|fn_url}" method="post" class="hidden" id="ajax_block_{$block.block_id}">
        <input type="hidden" name="result_ids" value="ajax_block_content_{$block.block_id}" />
        <input type="hidden" name="b_id" value="{$block.block_id}" />
        <input type="hidden" name="s_id" value="{$block.snapping_id}" />
        {foreach from=$block.extra_properties item="ep_val" key="ep_key"}
            <input type="hidden" name="extra_properties[{$ep_key}]" value="{$ep_val}" />
        {/foreach}
        {foreach from=$dynamic_object item="do_val" key="do_key"}
            <input type="hidden" name="dynamic_object[{$do_key}]" value="{$do_val}" />
        {/foreach}
        {foreach from=$smarty.request item="r_val" key="r_key"}
            <input type="hidden" name="request_data[{$r_key}]" value="{$r_val}" />
        {/foreach}
        <input type="hidden" name="request_data[current_url]" value="{$config.current_url}" />
        {include file="buttons/button.tpl" but_text=__("submit") but_name="dispatch[block_manager.get_block]" but_id="ajax_block_`$block.block_id`_submit"}
    </form>
    <script type="text/javascript">
        inputs = $('input', $('#ajax_block_{$block.block_id}'));
        params = [];
        result_id = 'ajax_block_content_{$block.block_id}';
        {literal}
            $.each(inputs, function(id, elm) {
                params.push({name: elm.name, value: elm.value});
            });
            $.ceAjax('request', fn_url('block_manager.get_block'), {
                result_ids: result_id,
                data: params,
                hidden: true,
                force_exec: true,
                method: 'post'
            });
        {/literal}
    </script>
<!--ajax_block_content_{$block.block_id}--></div>