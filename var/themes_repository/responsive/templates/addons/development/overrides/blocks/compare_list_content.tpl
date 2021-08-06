<div class="top-compare ty-top-block__cell cm-hover-dropdown" id="compare_list_status_{$block.snapping_id}">
    {assign var="r_url" value=$config.current_url|escape:url}
    <div class="ty-top-block__cell-inner ty-dropdown-box__title ty-dropdown-box ty-top-block__compare-content cm-link {if $highlight}ty-top-block-change-state{/if}" data-href="{"product_features.compare"|fn_url}">
        <div class="ty-top-block__cell-content">
            <div class="ty-icon-compare">
                {if $smarty.session.comparison_list.products|count}
                    <div class="ty-minicart__icon-counter">{$smarty.session.comparison_list.products|count}</div>
                {/if}
            </div>
        </div>
    </div>
    <div class="cm-popup-box ty-dropdown-box__content cm-hover-dropdown-submenu" {if $is_open}style="display: block;"{/if}>
        <div class="ty-dropdown-box__content-inner">
            <div class="ty-dropdown-box__content-title">{__("comparison_list")}</div>
            <div class="ty-dropdown-box__content-items cm-cart-content {if $block.properties.products_links_type == "thumb"}cm-cart-content-thumb{/if} {if $block.properties.display_delete_icons == "Y"}cm-cart-content-delete{/if}">
                <div class="ty-cart-items">
                    {if $smarty.session.comparison_list.products}
                        <ul class="ty-cart-items__list">
                            {foreach from=$smarty.session.comparison_list.products key="key" item="p" name="comparison_list_products"}
                                {if !$p.extra.parent}
                                    <li class="ty-cart-items__list-item">
                                        {if $block.properties.products_links_type == "thumb"}
                                            <div class="ty-cart-items__list-item-image">
                                                {include file="common/image.tpl" image_width="100" image_height="100" images=$p.main_pair no_ids=true}
                                            </div>
                                        {/if}
                                        <div class="ty-cart-items__list-item-desc">
                                            <div class="ty-cart-items__list-item-category">{$p.category_name}</div>
                                            <a href="{"products.view?product_id=`$p.product_id`{if $p.ohash}&{$p.ohash}{/if}"|fn_url}">{$p.product nofilter}</a>
                                        </div>
                                        <div class="ty-cart-items__list-item-subtotal">
                                            {include file="common/price.tpl" value=$p.price span_id="w_price_`$key`_`$block.snapping_id`" class="ty-cart-items__list-item-subtotal-price"}
                                        </div>
                                        {if $block.properties.display_delete_icons == "Y"}
                                            <div class="ty-cart-items__list-item-tools-wrapper">
                                            <div class="ty-cart-items__list-item-tools cm-cart-item-delete">
                                                {if !$p.extra.exclude_from_calculate}
                                                    {include file="buttons/button.tpl" but_href="product_features.delete_product?pid=`$p.product_id`" but_meta="cm-ajax cm-ajax-full-render" but_target_id="compare_list_status*" but_role="delete" but_name="delete_cart_item"}
                                                {/if}
                                            </div>
                                            </div>
                                        {/if}
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                    {else}
                        <div class="ty-cart-items__empty">{__("compare_list_is_empty")}</div>
                    {/if}
                </div>
            </div>
            {if $smarty.session.comparison_list.products && $block.properties.display_bottom_buttons == "Y"}
                <div class="ty-cart-content__buttons-wrapper">
                    <div class="ty-cart-content__buttons">
                        <div class="cm-cart-buttons ty-cart-content__buttons-container{if $smarty.session.comparison_list.products} full-cart{else} hidden{/if}">
                            <div class="ty-float-right">
                                <a href="{"product_features.compare"|fn_url}" rel="nofollow" class="ty-btn ty-black-button">{__("go_to_compare_list")}</a>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}
        </div>
    </div>
<!--compare_list_status_{$block.snapping_id}--></div>