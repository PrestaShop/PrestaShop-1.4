{capture name=path}<a href="{$link->getPageLink('order.php', true)}">{l s='Your shopping cart' mod='paypal'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='PayPal ExpressCheckout' mod='paypal'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{$message}</h2>
{if isset($logs) && $logs}
	<div class="error">
		<p><b>{l s='Please refer to logs:' mod='paypal'}</b></p>
		<ol>
		{foreach from=$logs key=k item=log}
			<li>{$log}</li>
		{/foreach}
		</ol>
		<p><a href="{$smarty.server.HTTP_REFERER|secureReferrer}" class="button_small" title="{l s='Back' mod='paypalapi'}">&laquo; {l s='Back' mod='paypal'}</a></p>
	</div>
{/if}
