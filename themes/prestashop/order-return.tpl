<h4>{l s='RE#'}<span class="color-myaccount">{$orderRet->id|string_format:"%06d"}</span> {l s='on'} {dateFormat date=$order->date_add full=0}</h4>
<div>
	<p class="bold">{l s='We have logged your return request.'}</p>
	<p>{l s='We remind you that your package must be returned to us within'} {$nbdaysreturn} {l s='days of initially receiving your order.'}</p>
	<p>{l s='Current status of your merchandise return:'} <span class="bold">{$state_name|escape:'htmlall':'UTF-8'}</span></p>
	<p>{l s='List of items marked as returned:'}</p>
</div>
<div id="order-detail-content" class="table_block">
	<table class="std">
		<thead>
			<tr>
				<th class="first_item">{l s='Reference'}</th>
				<th class="item">{l s='Product'}</th>
				<th class="last_item">{l s='Quantity'}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$products item=product name=products}
			<tr class="{if $smarty.foreach.products.first}first_item{/if} {if $smarty.foreach.products.index % 2}alternate_item{else}item{/if}">
				<td>{if $product.product_reference}{$product.product_reference|escape:'htmlall':'UTF-8'}{else}--{/if}</td>
				<td class="bold">{$product.product_name|escape:'htmlall':'UTF-8'}</td>
				<td><span class="order_qte_span editable">{$product.product_quantity|intval}</span></td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
{if $orderRet->state == 2}
<p class="bold">{l s='REMINDER:'}</p>
<div>
	- {l s='All products must be returned in their original packaging without damage or wear.'}
	<br />- {l s='Please print out the'} <a href="{$base_dir}pdf-order-return.php?id_order_return={$orderRet->id|intval}">{l s='PDF document'}</a> {l s='and slip it into your package.'}
	<br />- {l s='The package should be to the correct address'} (<a href="{$base_dir}pdf-order-return.php?id_order_return={$orderRet->id|intval}">{l s='see PDF document'}</a>)
	<br /><br />
	{l s='Upon receiving your package, we will inform you by e-mail and will then begin processing the reimbursement of your order total.'}
	<br /><br /><a href="{$base_dir_ssl}contact-form.php">{l s='Let us know if you have any questions.'}</a>
	<br />
	<p class="bold">{l s='If the conditions of return listed above are not respected, we reserve the right to refuse your package and/or reimbursement.'}</p>
</div>
{else}
	<p class="bold">{l s='You must wait for our confirmation before returning merchandise to us.'}</p>
{/if}