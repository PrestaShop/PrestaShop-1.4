<!-- Block search module HEADER -->
<div id="search_block_top">
	<form method="get" action="{$base_dir}search.php" id="searchbox">
	<p>
		<label for="search_query"><!-- image on background --></label>
		<input type="text" id="search_query" name="search_query" value="{if isset($smarty.get.search_query)}{$smarty.get.search_query|htmlentities:$ENT_QUOTES:'utf-8'}{/if}" />
		<input type="submit" name="submit_search" value="{l s='Search' mod='blocksearch'}" class="button" />
	</p>
	</form>
</div>
<!-- /Block search module HEADER -->