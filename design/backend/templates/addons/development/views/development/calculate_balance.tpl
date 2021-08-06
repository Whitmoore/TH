{capture name="mainbox"}

<form action="{""|fn_url}" method="get" target="_self" name="balance_form">

<div class="sidebar-field">
    <label for="length">{__("length")}</label>
    <input type="text" name="length" id="length" value="{$params.length}" size="30"/>
</div>
<div class="sidebar-field">
    <label for="points">{__("points")}</label>
    <input type="text" name="points" id="points" value="{$params.points}" size="30"/>
</div>
<div class="sidebar-field">
    <label for="relation">{__("relation")}</label>
    <input type="text" name="relation" id="relation" value="{$params.relation}" size="30"/>
</div>
<div class="sidebar-field">
    <label for="result">{__("result")}</label>
    <input type="text" name="result" readonly="readonly" id="result" value="{$params.result}" size="30"/>
</div>
<div class="btn-group btn-hover dropleft">
    {include file="buttons/button.tpl" but_role="submit" but_text=__("calculate") but_name="dispatch[development.calculate_balance]"}
</div>
</form>
{/capture}

{include file="common/mainbox.tpl" title={__("calculate_balance")} content=$smarty.capture.mainbox content_id="calculate_balance"}
