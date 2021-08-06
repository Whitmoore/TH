{if $pair_data.icon || $pair_data.detailed}
    <div id="{$el_id}">
        {include file="common/image.tpl" no_ids=true images=$pair_data image_width=$iw image_height=$ih}
    <!--{$el_id}--></div>
{else}
    <div class="{if $loader_class}{$loader_class}{else}cm-image-loader{/if}" data-ca-pair-id="{$pair_id}" {if $trigger_ids}data-ca-trigger-ids="{$trigger_ids}"{/if} id="{$el_id}">
    <!--{$el_id}--></div>
{/if}
