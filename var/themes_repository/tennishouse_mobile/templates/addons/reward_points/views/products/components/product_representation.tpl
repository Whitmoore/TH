{if $product.points_info.reward.amount}
<div class="ty-product-representation">
{*if $product.points_info.price}
    <div class="ty-control-group">
        <span class="ty-control-group__label product-list-field">{__("price_in_points")}:</span>
        <span class="ty-control-group__item" id="price_in_points_{$obj_prefix}{$obj_id}">{$product.points_info.price}</span>
    </div>
{/if*}
{if $addons.development.review_reward_P > 0}
    {$rwd_text = __('reward_points_note_P', ['[product_amount]' => $addons.development.review_reward_P])}
    {$reward_options = "`$reward_options``$rwd_text`"}
{/if}
{if $addons.development.review_reward_E > 0}
    {$rwd_text = __('reward_points_note_E', ['[store_amount]' => $addons.development.review_reward_E])}
    {$reward_options = "`$reward_options``$rwd_text`"}
{/if}
<div class="ty-product-detail__info-title">{__("loyality_program")}{*include file="addons/development/common/tooltip.tpl" note_text=__("reward_points_note", ["[amount]" => $product.points_info.reward.amount, "[reward_options]" => $reward_options]) tooltipclass="ty-reward-tooltip"*}</div>
<div class="ty-control-group product-list-field product-list-field-rp{if !$product.points_info.reward.amount} hidden{/if}">
    {capture name="points_reward"}
        {include file="common/price.tpl" value=$product.points_info.reward.amount}
    {/capture}
    {*<label class="ty-control-group__label">
        {__("reward_points")}
        {$reward_options = ''}
    </label>*}
    <span class="ty-control-group__item" id="reward_points_{$obj_prefix}{$obj_id}">{__("reward_points_label", ["[amount]" => $smarty.capture.points_reward])}</span>
</div>
</div>
{/if}