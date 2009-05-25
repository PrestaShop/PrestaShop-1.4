<!-- Block suppliers module -->
<div id="suppliers_block_left" class="block blocksupplier">
	<h4><a href="{$base_dir}supplier.php" title="{l s='Suppliers' mod='blocksupplier'}">{l s='Suppliers' mod='blocksupplier'}</a></h4>
	<div class="block_content">
{if $suppliers}
	{if $text_list}
	<ul class="bullet">
	{foreach from=$suppliers item=supplier name=supplier_list}
		{if $smarty.foreach.supplier_list.iteration <= $text_list_nb}
		<li class="{if $smarty.foreach.supplier_list.last}last_item{elseif $smarty.foreach.supplier_list.first}first_item{else}item{/if}">
			<a href="{$link->getsupplierLink($supplier.id_supplier, $supplier.link_rewrite)}" title="{l s='More about' mod='blocksupplier'} {$supplier.name}">{$supplier.name}</a>
		</li>
		{/if}
	{/foreach}
	</ul>
	{/if}
	{if $form_list}
		<form action="{$smarty.server.SCRIPT_NAME}" method="get">
			<p>
				<select id="supplier_list" onchange="autoUrl('supplier_list', '');">
					<option value="0">{l s='All suppliers' mod='blocksupplier'}</option>
				{foreach from=$suppliers item=supplier}
					<option value="{$link->getsupplierLink($supplier.id_supplier, $supplier.link_rewrite)}">{$supplier.name}</option>
				{/foreach}
				</select>
			</p>
		</form>
	{/if}
{else}
	<p>{l s='No supplier' mod='blocksupplier'}</p>
{/if}
	</div>
</div>
<!-- /Block suppliers module -->
