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
{if $ajaxsearch}
	<link rel="stylesheet" type="text/css" href="{$base_uri}css/jquery.autocomplete.css" />
	<script type="text/javascript" src="js/jquery/jquery.autocomplete.js"></script>
	<script type="text/javascript">
		{literal}
		
		function formatSearch(row) {
			return row[2] + ' > ' + row[1];
		}

		function redirectSearch(event, data, formatted) {
			$('#search_query').val('');
			document.location.href = data[3];
		}
		
		$('document').ready( function() {
			$("#search_query").autocomplete(
				'search.php', {
				minChars: 3,
				max:10,
				width:500,
				scroll: false,
				formatItem:formatSearch,
				extraParams:{ajaxSearch:1,id_lang:{/literal}{$cookie->id_lang}{literal}}
			}).result(redirectSearch)
		});
		{/literal}
	</script>
{/if}
<!-- /Block search module HEADER -->