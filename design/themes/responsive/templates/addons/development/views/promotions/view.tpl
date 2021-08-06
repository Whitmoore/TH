<div class="ty-promotion-image-title">{include file="common/image.tpl" obj_id=$obj_id_prefix images=$promotion_data.main_pair}</div>
<div class="ty-wysiwyg-content">
    {hook name="pages:page_content"}
    <div class="ty-page-description" {live_edit name="page:description:{$page.page_id}"}>
        {$promotion_data.detailed_description nofilter}
    </div>
    {/hook}
</div>
{if $products}
<div class="ty-promotion-products-title">{__("promotion_products")}</div>
<div class="ty-promotion-products">
    {include file="blocks/list_templates/grid_list.tpl"
    products=$products
    columns=5
    form_prefix="promotion_products"
    no_sorting="Y"
    no_pagination="Y"
    no_ids="Y"
    obj_prefix="promotion_products"
    item_number=false
    show_trunc_name=true
    show_old_price=true
    show_price=true
    show_rating=true
    show_clean_price=true
    show_list_discount=true
    show_add_to_cart=false
    but_role="action"
    show_discount_label=true
    category_grid=true}
</div>
{/if}

{capture name="mainbox_title"}{if !$image_title_text}<span class="ty-promotion-title" {live_edit name="page:page:{$page.page_id}"}>{$promotion_data.name}</span>{/if}{/capture}
    
{hook name="pages:page_extra"}
{/hook}

{include file="addons/development/common/share_buttons.tpl" title=$promotion_data.name}