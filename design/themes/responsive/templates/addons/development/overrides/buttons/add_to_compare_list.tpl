{if !$config.tweaks.disable_dhtml}
    {assign var="ajax_class" value="cm-ajax cm-ajax-full-render"}
{/if}

{if  !$hide_compare_list_button}
    {include file="addons/development/common/form_link.tpl" form_class="ty-wishlist-form $ajax_class" form_method="get" hidden_input=["product_id" => "{$product_id}", "result_ids" => "compare_list_status*"] link_text=__("compare") link_meta="ty-add-to-compare" link_name="dispatch[product_features.add_product]" link_id="add_to_compare_`$obj_id`" link_role=""}
{/if}
