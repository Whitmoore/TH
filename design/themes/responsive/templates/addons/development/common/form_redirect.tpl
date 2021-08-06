<form action="{""|fn_url}" method="post" name="form_redirect">
    <input type="hidden" name="redirect_url" value="{$form_redirect}">
    {include file="buttons/button.tpl" but_text=$form_text but_name="dispatch[development.redirect]" but_meta="ty-button-link `$form_class`"}
</form>
