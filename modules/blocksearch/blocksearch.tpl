<!-- Block search module -->
<div id="search_block_left" class="block exclusive">
	<h4>{l s='Search' mod='blocksearch'}</h4>
	<form method="get" action="{$base_dir}search.php" id="searchbox">
		<p class="block_content">
			<label for="search_query">{l s='Enter a product name' mod='blocksearch'}</label>
			<input type="text" id="search_query" name="search_query" value="{if isset($smarty.get.search_query)}{$smarty.get.search_query|htmlentities:$ENT_QUOTES:'utf-8'}{/if}" />
			<input type="submit" id="search_button" class="button_mini" value="{l s='go' mod='blocksearch'}" />
		</p>
	</form>
</div>
<!-- /Block search module -->