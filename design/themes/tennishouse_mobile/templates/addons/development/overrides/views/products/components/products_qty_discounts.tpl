<div class="ty-qty-discount">
    <div class="ty-qty-discount__label"><icon class="ty-qty-disc-logo"></icon>{__("scale_of_discount")}</div>
    <table class="ty-table ty-product-tabs__table">
        <tbody>
            <tr>
                <td class="ty-qty-discount__td">{__("quantity")}</td>
                <td class="ty-qty-discount__td ty-center">1</td>
                {foreach from=$product.prices item="price"}
                    <td class="ty-qty-discount__td ty-center">{$price.lower_limit}+</td>
                {/foreach}
            </tr>
            <tr>
                <td class="ty-qty-discount__td">{__("price")}</td>
                <td class="ty-qty-discount__td ty-center">{include file="common/price.tpl" value=$product.price}</td>
                {foreach from=$product.prices item="price"}
                    <td class="ty-qty-discount__td ty-center">{include file="common/price.tpl" value=$price.price}</td>
                {/foreach}
            </tr>
        </tbody>
    </table>
</div>