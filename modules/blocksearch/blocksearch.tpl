{*
* Copyright (C) 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

<!-- Block search module -->
<div id="search_block_left" class="block exclusive">
	<h4>{l s='Search' mod='blocksearch'}</h4>
	<form method="get" action="{$link->getPageLink('search.php', true)}" id="searchbox">
		<p class="block_content">
			<label for="search_query">{l s='Enter a product name' mod='blocksearch'}</label>
			<input type="hidden" name="orderby" value="position" />
			<input type="hidden" name="orderway" value="desc" />
			<input type="text" id="search_query" name="search_query" value="{if isset($smarty.get.search_query)}{$smarty.get.search_query|htmlentities:$ENT_QUOTES:'utf-8'|stripslashes}{/if}" />
			<input type="submit" id="search_button" class="button_mini" value="{l s='go' mod='blocksearch'}" />
		</p>
	</form>
</div>
{if $ajaxsearch}
	<script type="text/javascript">
	/* <![CDATA[ */
	{literal}
		$('document').ready( function() {
			$("#search_query")
				.autocomplete(
					'{/literal}{if $search_ssl == 1}{$link->getPageLink('search.php', true)}{else}{$link->getPageLink('search.php')}{/if}{literal}', {
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
		/* ]]> */
	</script>
{/if}
<!-- /Block search module -->
