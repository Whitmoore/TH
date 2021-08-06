<div class="ty-cp-search-results cm-ajax-force" id="cp_variants_{$product_id}">
    {if $results}
        {foreach from=$results item="result"}
            <div data-cp-value="{$result.item_id}" class="ty-cp-search-results-item">
                <div class="ty-cp-search-results-item-select cm-competitor-products-result {if $result.in_stock == 'N'}ty-cp-search-results-item-sold-out{/if}" data-item-id="{$result.item_id}" data-product-id="{$product_id}">{$result.name nofilter} - {include file="common/price.tpl" value=$result.price} - </div>
                <a href="{$result.link}" target="_blank" class="ty-cp-search-results-item-link">></a>
            </div>
        {/foreach}
    {elseif $ac_done}
        <div class="ty-cp-search-no-results">{__("no_match")}</div>
    {/if}

<script type="text/javascript">
{literal}
    (function (_, $) {
        $('.cm-competitor-products-result').click(function(){
            $('#cp_item_id_' + $(this).data('productId')).val($(this).data('itemId'));
            if (typeof($('#cp_item_id_' + $(this).data('productId')).data('originalId')) == 'undefined' || $('#cp_item_id_' + $(this).data('productId')).data('originalId') != $(this).data('itemId')) {
                $('#cp_action_' + $(this).data('productId')).val('T');
            } else {
                $('#cp_action_' + $(this).data('productId')).val('U');
            }
            $('#cp_action_' + $(this).data('productId')).prop('disabled', false);
            $('#cp_variants_' + $(this).data('productId')).hide();
        });
    }(Tygh, Tygh.$));
{/literal}
</script>
<!--cp_variants_{$product_id}--></div>
