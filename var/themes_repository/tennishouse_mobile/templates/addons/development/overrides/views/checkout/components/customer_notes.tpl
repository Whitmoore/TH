{hook name="checkout:notes"}
	<div class="ty-customer-notes">
	    <p class="ty-customer-notes__title">{__("type_comments_here")}</p>
	    <textarea class="ty-customer-notes__text" name="customer_notes" cols="60" placeholder="{__("order_notes_text")}" rows="3">{$cart.notes}</textarea>
	</div>
{/hook}