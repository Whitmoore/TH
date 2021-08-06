<form action="{""|fn_url}" method="{$form_method|default:'post'}" name="form_link" {if $form_class}class="{$form_class}"{/if}>
    {foreach from=$hidden_input key="input_name" item="input_value"}
        <input type="hidden" name="{$input_name}" value="{$input_value}">
    {/foreach}
    {include file="buttons/button.tpl" but_text=$link_text but_name=$link_name but_meta=$link_meta but_onclick=$link_onclick but_id=$link_id but_role=$link_role}
</form>
