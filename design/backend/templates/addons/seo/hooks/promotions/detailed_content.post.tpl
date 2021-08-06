{if $promotion_data.promotion_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="addons/seo/common/seo_name_field.tpl" object_data=$promotion_data object_name="promotion_data" object_id=$promotion_data.promotion_id object_type="m"}
{/if}