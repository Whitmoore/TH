<div {if $microdata} itemprop="itemListElement" itemscope itemtype="http://schema.org/Product"{/if} class="ty-grid-list__item-wrapper">
{hook name="products:product_multicolumns_list"}
{/hook}
<a {if $microdata} itemprop="url"{/if} {if !$no_link}href="{"products.view?product_id=`$product.product_id`{if $product.ohash}&`$product.ohash`{/if}"|fn_url}"{else}data-product-add="{$product.product_id}"{/if} class="ty-grid-list__item-link {if $no_link}cm-sd-option{/if}">
<div class="ty-grid-list__item">
    {if $product.obj_prefix}
        {assign var="obj_id" value="`$product.obj_prefix`_`$product.product_id`"}
    {else}
        {assign var="obj_id" value=$product.product_id}
    {/if}
    {include file="common/product_data.tpl" product=$product}

    {assign var="form_open" value="form_open_`$obj_id`"}
    {$smarty.capture.$form_open nofilter}
    {if $product.match_p}
        <div class="ty-grid-list__match">{__("match")} {$product.match_p}%</div>
    {/if}
    {if $product.is_selected}
        <div class="ty-grid-list__item-selected"></div>
    {/if}
    <div class="ty-grid-list__image">
        {if $product.option_images && ($mode == 'R' || $mode  == 'S') && !$product.hide_icons && $product.option_images|count > 1}
            <div class="ty-list-options">
            {foreach from=$product.option_images item="opt_icon"}
                {if $product.full_option_images}
                    <div class="ty-list-options_box">
                        {if $opt_icon.detailed}{include file="common/image.tpl" show_detailed_link=false images=$opt_icon.detailed no_ids=true image_height="30"}{else}{include file="common/image.tpl" show_detailed_link=false images=$product.main_pair.detailed no_ids=true image_height="30"}{/if}
                    </div>
                {else}
                    <div class="ty-list-options_box ty-color_option_images">
                        {if $opt_icon.icon}{include file="common/image.tpl" show_detailed_link=false images=$opt_icon.icon no_ids=true image_height="15"}{/if}
                    </div>
                {/if}
            {/foreach}
            </div>
        {/if}
        <div class="ty-grid-list__image-product" {if $mode == 'R'}style="max-width: {$settings.Thumbnails.product_lists_thumbnail_width}px;max-height: {$settings.Thumbnails.product_lists_thumbnail_height}px;"{/if}>
            {if $mode == 'R'}
                {$show_gallery = true}
            {else}
                {$show_gallery = false}
            {/if}
            {if $microdata}
                {include file="views/products/components/product_icon.tpl" product=$product show_gallery=$show_gallery itemprop="image"}
            {else}
                {include file="views/products/components/product_icon.tpl" product=$product show_gallery=$show_gallery}
            {/if}
        </div>

        <div class="ty-grid-list__brand-image">
            {if $microdata}
                {include file="addons/development/common/brand_logo.tpl"  brand=$product.brand brand_variant_id=$product.brand.variant_id itemprop="brand"}
            {else}
                {include file="addons/development/common/brand_logo.tpl"  brand=$product.brand brand_variant_id=$product.brand.variant_id}
            {/if}
        </div>
        
        {if $mode == 'R'}
            {*assign var="rating" value="rating_`$obj_id`"}
            {if $smarty.capture.$rating}
                <div class="grid-list__rating">
                    {$smarty.capture.$rating nofilter}
                </div>
            {/if*}
            {if $product.likes > 0}
                <div class="ty-grid-list__likes">
                    <div class="{if $product.is_liked}ty-grid-list__likes-heart-full{else}ty-grid-list__likes-heart{/if}"></div><span class="ty-grid-list__likes-num">{$product.likes}</span>
                </div>
            {/if}
        {/if}
        {if $mode == 'R'}
            <div class="ty-product-tags">
                {if $product.tags.new}
                    <div class="ty-new-item-tag"></div>
                {/if}
                {assign var="discount_label" value="discount_label_`$obj_prefix``$obj_id`"}
                {$smarty.capture.$discount_label nofilter}
                {if $product.free_strings}
                    <div class="ty-free-string-tag"></div>
                {/if}
            </div>
            <div class="ty-grid-list__item-icon-features">
                {foreach from=$product.description_features item="feature"}
                    {if $feature.feature_id|fn_is_icon_feature}
                        <div class="ty-grid-list__item-feature ty-grid-list__item-feature-icon">
                            <div class="ty-feature-icon ty-feature-icon-{$feature.feature_id}"></div><span class="ty-feature-icon-value">{$feature.variants}</span>
                        </div>
                    {/if}
                {/foreach}
            </div>
            {if $smarty.const.PROMOTION_TAG|in_array:$product.tags}
                <div class="ty-grid-list__image-promo-tag"></div>
            {/if}
        {/if}
    </div>
    
    <div class="ty-grid-list__item-info">
        {hook name="products:product_multicolumns_list_item_info"}
        <div class="ty-grid-list__item-name">
            {if $mode == 'R' && !$extended}
                <div class="ty-product-series">{$product.subtitle}</div>
            {/if}
            <div {if $microdata} itemprop="name"{/if} class="ty-grid-list__item-title">
                {assign var="name" value="name_`$obj_id`"}
                {*if $category_grid*}<div class="ty-grid-list__item-title-header">{*/if*}{$smarty.capture.$name nofilter}{*if $category_grid*}</div>{*/if*}
            </div>
        </div>
        {if $extended}
        <div itemprop="description" class="ty-grid-list__item-description">
            {assign var="name" value="prod_descr_`$obj_id`"}
            {$smarty.capture.$name nofilter}
        </div>
        <div class="ty-grid-list__item-features">
            {foreach from=$product.description_features item="feature"}
                {if !$feature.feature_id|fn_is_icon_feature}
                    <div class="ty-grid-list__item-feature">
                        {$feature.variants}
                    </div>
                {/if}
            {/foreach}
        </div>
        {else}
            <meta itemprop="description" content="{$product.product nofilter}" />
        {/if}

        {hook name="products:product_multicolumns_list_item_price"}
        <div {if $microdata} itemprop="offers"{/if} itemscope itemtype="http://schema.org/Offer" class="ty-grid-list__price {if $product.price == 0}ty-grid-list__no-price{/if}">
            {assign var="old_price" value="old_price_`$obj_id`"}
            {if $mode == 'R' && $smarty.capture.$old_price|trim}{$smarty.capture.$old_price nofilter}{/if}

            {assign var="price" value="price_`$obj_id`"}
            {$smarty.capture.$price nofilter}
            {if $microdata}
                <link itemprop="availability" href="http://schema.org/InStock" />
                <meta itemprop="price" content="{$product.price|fn_format_price:$primary_currency}" />
                <meta itemprop="priceCurrency" content="{$currencies[$smarty.const.CART_PRIMARY_CURRENCY].currency_code}" />
            {/if}
            
            {assign var="clean_price" value="clean_price_`$obj_id`"}
            {$smarty.capture.$clean_price nofilter}

            {assign var="list_discount" value="list_discount_`$obj_id`"}
            {$smarty.capture.$list_discount nofilter}
        </div>
        {/hook}
   
        {*<div class="ty-grid-list__control">
            {if $settings.Appearance.enable_quick_view == 'Y'}
                {include file="views/products/components/quick_view_link.tpl" quick_nav_ids=$quick_nav_ids}
            {/if}

            {if $show_add_to_cart}
                <div class="button-container">
                    {assign var="add_to_cart" value="add_to_cart_`$obj_id`"}
                    {$smarty.capture.$add_to_cart nofilter}
                </div>
            {/if}
        </div>*}
        {/hook}
        {if $category_grid}
        <div class="ty-grid-list__item-additional-wrapper">
            <div class="ty-grid-list__item-additional">
                {if $product.sizes}
                    <div class="ty-grid-list__item-additional-sizes">
                        <span class="ty-grid-list__item-additional-sizes-title">{$product.sizes.name}:</span>
                        {foreach from=$product.sizes.variants item="v_name" name="av_sizes"}{if !$smarty.foreach.av_sizes.first}, {/if}{$v_name}{/foreach}
                    </div>
                {/if}
            </div>
        </div>
        {/if}
    </div>
    
    {assign var="form_close" value="form_close_`$obj_id`"}
    {$smarty.capture.$form_close nofilter}
    
</div>
</a>
</div>