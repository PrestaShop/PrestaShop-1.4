{capture name=path}{l s='Search'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2 {if isset($instantSearch) && $instantSearch}id="instant_search_results"{/if}>
{l s='Search'}&nbsp;{if $nbProducts > 0}"{if isset($search_query) && $search_query}{$search_query|escape:'htmlall':'UTF-8'}{elseif $search_tag}{$search_tag|escape:'htmlall':'UTF-8'}{elseif $ref}{$ref|escape:'htmlall':'UTF-8'}{/if}"{/if}
{if isset($instantSearch) && $instantSearch}<a href="#" class="close">{l s='Back to the last page'}</a>{/if}
</h2>

{include file="$tpl_dir./errors.tpl"}
{if !$nbProducts}
	<p class="warning">
		{if isset($search_query) && $search_query}
			{l s='No results found for your search'}&nbsp;"{if isset($query)}{$query|escape:'htmlall':'UTF-8'}{/if}"
		{elseif isset($search_tag) && $search_tag}
			{l s='No results found for your search'}&nbsp;"{$tag|escape:'htmlall':'UTF-8'}"
		{else}
			{l s='Please type a search keyword'}
		{/if}
	</p>
{else}
	<h3><span class="big">{$nbProducts|intval}</span>&nbsp;{if $nbProducts == 1}{l s='result has been found.'}{else}{l s='results have been found.'}{/if}</h3>
	{if !isset($instantSearch) || (isset($instantSearch) && !$instantSearch)}{include file="$tpl_dir./product-sort.tpl"}{/if}
	{include file="$tpl_dir./product-list.tpl" products=$products}
	{if !isset($instantSearch) || (isset($instantSearch) && !$instantSearch)}{include file="$tpl_dir./pagination.tpl"}{/if}
{/if}