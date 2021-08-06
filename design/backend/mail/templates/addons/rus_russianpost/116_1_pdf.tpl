<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
{literal}
<style type="text/css" media="screen,print">

body,p,div,td {
    color: #000000;
    font: 12px Arial;
}
body {
    padding: 0;
    margin: 0;
}
a, a:link, a:visited, a:hover, a:active {
    color: #000000;
    text-decoration: underline;
}
a:hover {
    text-decoration: none;
}
</style>
{/literal}
</head>

<body style="width: 141mm; height: 200mm;">
    <div style="width: 141mm; height: 200mm; position: relative;" >
        {if $data.print_bg == 'Y'}
        <img style="width: 141mm; height: 200mm;" src="{$images_dir}/addons/rus_russianpost/116_1.png">
        {/if}
        <span style="position: absolute;height: 5.5mm;width: 86mm;top: 45mm;left: 9mm;text-align: center;font: 10pt 'Arial';">{if $data.total_cen}{$data.total_cen}{else}б/ц{/if}</span>
        <span style="position: absolute;height: 5.5mm;width: 86mm;top: 53mm;left: 9mm;text-align: center;font: 10pt 'Arial';">{$data.total_cod}</span>

        <span style="position: absolute;height: 5.5mm;width: 79mm;top: 60mm;left: 17mm;font: 10pt 'Arial';">{$data.from_whom}</span>
        <span style="position: absolute;height: 10.5mm;width: 87mm;top: 64mm;left: 9mm;font: 10pt 'Arial';text-indent: 10mm;line-height: 17pt;">{$data.sender_address}</span>
        <span style="position: absolute;height: 5.5mm;width: 86mm;top: 71mm;left: 9mm;font: 10pt 'Arial';">{$data.sender_address2}</span>
        <span style="position: absolute;height: 5.5mm;width: 36mm;top: 76mm;left: 66.5mm;font: 12pt 'Arial';letter-spacing: 6.6pt;">
                <span style="position: absolute; left: 0mm;">{$data.from_index.0}</span>
                <span style="position: absolute; left: 4.5mm;">{$data.from_index.1}</span>
                <span style="position: absolute; left: 9.5mm;">{$data.from_index.2}</span>
                <span style="position: absolute; left: 14mm;">{$data.from_index.3}</span>
                <span style="position: absolute; left: 18.5mm;">{$data.from_index.4}</span>
                <span style="position: absolute; left: 23.5mm;">{$data.from_index.5}</span>
        </span>

        <span style="position: absolute;height: 5.5mm;width: 110mm;top: 84mm;left: 20mm;font: 10pt 'Arial';">{$data.fio}</span>
        <span style="position: absolute;height: 5.5mm;width: 113mm;top: 91mm;left: 17mm;font: 10pt 'Arial';">{$data.fiz_addres}</span>
        <span style="position: absolute;height: 5.5mm;width: 91mm;top: 96mm;left: 9mm;font: 10pt 'Arial';">{$data.fiz_addres2}</span>
        <span style="position: absolute;height: 5.5mm;width: 36mm;top: 95mm;left: 101.5mm;font: 12pt 'Arial';letter-spacing: 6.6pt;">
            <span style="position: absolute; left: 0mm;">{$data.index.0}</span>
            <span style="position: absolute; left: 4.5mm;">{$data.index.1}</span>
            <span style="position: absolute; left: 9.5mm;">{$data.index.2}</span>
            <span style="position: absolute; left: 14mm;">{$data.index.3}</span>
            <span style="position: absolute; left: 18.5mm;">{$data.index.4}</span>
            <span style="position: absolute; left: 23.5mm;">{$data.index.5}</span>
        </span>

        <span style="position: absolute;height: 5.5mm;width: 22mm;top: 109mm;left: 24mm;text-align: center;font: 10pt 'Arial';">{$data.fiz_doc}</span>
        <span style="position: absolute;height: 5.5mm;width: 14mm;top: 109mm;left: 55mm;text-align: center;font: 10pt 'Arial';">{$data.fiz_doc_serial}</span>
        <span style="position: absolute;height: 5.5mm;width: 16mm;top: 109mm;left: 73mm;text-align: center;font: 10pt 'Arial';">{$data.fiz_doc_number}</span>
        <span style="position: absolute;height: 5.5mm;width: 17mm;top: 109mm;text-align: center;left: 99mm;font: 10pt 'Arial';">{$data.fiz_doc_date}</span>
        <span style="position: absolute;height: 5.5mm;width: 7mm;top: 109mm;text-align: center;left: 120mm;font: 10pt 'Arial';">{$data.fiz_doc_date2}</span>
        <span style="position: absolute;height: 5.5mm;width: 125mm;top: 114mm;left: 7mm;text-align: center;font: 10pt 'Arial';">{$data.fiz_doc_creator}</span>
    </div>
</body>
</html>