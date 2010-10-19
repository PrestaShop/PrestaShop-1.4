{if $comparator_max_item}
<script type="text/javascript">
// <![CDATA[
	var min_item = '{l s='Please, select at least one product.' js=1}';
	var max_item = "{l s='You can\'t add more than' js=1} {$comparator_max_item} {l s='product(s) in the product comparator' js=1}";
//]]>
</script>
	<form method="get" action="products-comparison.php" onsubmit="return checkBeforeComparison();">
		<p>
		<input type="submit" class="button" value="{l s='Compare'}" style="float:right" />
		<input type="hidden" name="compare_product_list" id="compare_product_list" value="" /> 
		</p>		
	</form>
{/if}
