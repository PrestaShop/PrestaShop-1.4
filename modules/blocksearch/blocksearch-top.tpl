<!-- Block search module TOP -->
<div id="search_block_top">
	<form method="get" action="{$base_dir_ssl}search.php" id="searchbox">
	<p>
		<label for="search_query"><!-- image on background --></label>
		<input type="hidden" name="orderby" value="position" />
		<input type="hidden" name="orderway" value="desc" />
		<input type="text" id="search_query" name="search_query" value="{if isset($smarty.get.search_query)}{$smarty.get.search_query|htmlentities:$ENT_QUOTES:'utf-8'|stripslashes}{/if}" />
		<input type="submit" name="submit_search" value="{l s='Search' mod='blocksearch'}" class="button" />
	</p>
	</form>
</div>
{if $ajaxsearch}
	<script type="text/javascript">{literal}
		$('document').ready( function() {
			$("#search_query")
				.autocomplete(
					'{/literal}{if $search_ssl == 1}{$base_dir_ssl}{else}{$base_dir}{/if}{literal}search.php', {
						minChars: 3,
						max: 10,
						width: 500,
						selectFirst: false,
						scroll: false,
						dataType: "json",
						formatItem: function(data, i, max, value, term) {
							return value;
						},
						parse: function(data) {
							var mytab = new Array();
							for (var i = 0; i < data.length; i++) {
								mytab[mytab.length] = { 
									data: data[i], 
									value: data[i].cname + ' > ' + data[i].pname 
								};
							}
							return mytab;
						},
						extraParams: {
							ajaxSearch: 1,
							id_lang: {/literal}{$cookie->id_lang}{literal}
						}
					}
				)
				.result(function(event, data, formatted) {
					$('#search_query').val(data.pname);
					document.location.href = data.product_link;
				})
		});{/literal}
	</script>
{/if}
<!-- /Block search module TOP -->
