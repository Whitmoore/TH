{* rus_build_exim_1C dbazhenov *}
{capture name="mainbox"}
<div id="orders_import">
    <form action="{""|fn_url}" method="post" name="import_orders" class="cm-ajax cm-comet" enctype="multipart/form-data">
    <input type="hidden" name="fake" value="1" />
    <input type="hidden" name="result_ids" value="orders_import" />
    <fieldset>
    {if $orders_files}
    <table class="table table-middle">
    <thead>
    <tr>
        <th width="5%" class="left">{include file="common/check_items.tpl"}</th>
        <th width="10%" class="left">{__("type")}</th>
        <th>{__("filename")}</th>
        <th>{__("filesize")}</th>
        <th>&nbsp;</th>
    </tr>
    </thead>
    {foreach from=$orders_files item=file key=name}
    <tr>
        <td>
            <input type="checkbox" name="orders_files[]" value="{$name}" class="cm-item" /></td>
        <td>[{$file.type}]</td>
        <td>
            <a href="{"1c.get_file?file=`$name`"|fn_url}"><span>{$name}</span></a></td>
        <td>
            {$file.size|number_format}&nbsp;{__("bytes")}</td>
        <td class="nowrap" width="10%">
            <div class="hidden-tools">
                {capture name="tools_list"}
                    <li>{btn type="list" text=__("download") href="1c.get_file?file=`$name`"}</li>
                    <li class="divider"></li>
                    <li>{btn type="list" text=__("delete") class="cm-confirm" href="1c.delete_file?file=`$name`"}</li>
                {/capture}
                {dropdown content=$smarty.capture.tools_list}
             </div>
        </td>
    </tr>
    {/foreach}
    </table>
    {else}
        <p class="no-items">{__("no_data")}</p>
    {/if}
    </fieldset>
    </form>
<!--orders_import--></div>
{/capture}

{capture name="buttons"}
    {capture name="tools_list"}
        {if $orders_files}
            <li>{btn type="delete_selected" class="cm-no-ajax" dispatch="dispatch[1c.delete_file]" form="import_orders"}</li>
        {/if}
    {/capture}
    {dropdown content=$smarty.capture.tools_list}

    {if $orders_files}
        {include file="buttons/button.tpl" but_text=__("import") but_name="dispatch[1c.run_import]" but_role="submit-link" but_target_form="import_orders"}
     {/if}
{/capture}

{capture name="sidebar"}
    <div class="sidebar-row">
        <h6>{__("select_file")}</h6>
        <div class="sidebar-row">
            <form action="{""|fn_url}" method="post" name="upload_orders" class="form-horizontal form-edit cm-comet" enctype="multipart/form-data">
                {include file="common/fileuploader.tpl" var_name="xml_orders[0]"}
                <br>
                {include file="buttons/button.tpl" but_meta="btn-primary" but_text=__("upload") but_name="dispatch[1c.upload]"}
            </form>
        </div>
    </div>
    <hr>
    <div class="sidebar-row">
    <h6>{__("help")}</h6>
        <div class="sidebar-field">
            <small>{__("1c_select_files")}</small>
        </div>
    </div>
{/capture}

{include file="common/mainbox.tpl" title=__("1c_orders_from") content=$smarty.capture.mainbox buttons=$smarty.capture.buttons sidebar=$smarty.capture.sidebar}
