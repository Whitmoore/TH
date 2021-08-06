{capture name="buttons"}
    {include file="buttons/button.tpl" but_meta="ty-btn__secondary" but_href="product_features.compare" but_text=__("view_compare_list")}
{/capture}
{include file="views/products/components/notification.tpl" product_buttons=$smarty.capture.buttons}