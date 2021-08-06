{if $player_data.player_id && "ULTIMATE"|fn_allowed_for || "MULTIVENDOR"|fn_allowed_for}
{include file="addons/seo/common/seo_name_field.tpl" object_data=$player_data object_name="player_data" object_id=$player_data.player_id object_type="l"}
{/if}