{capture name=path}<a href="{$base_dir_ssl}order.php">{l s='Your shopping cart' mod='paypalapi'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='PayPal ExpressCheckout' mod='paypalapi'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}

<h2>{$message}</h2>
{if isset($logs) && $logs}
	<div class="error">
		<p><b>{l s='Please refer to logs:' mod='paypalapi'}</b></p>
		<ol>
		{foreach from=$logs key=k item=log}
			<li>{$log}</li>
		{/foreach}
		</ol>
		<p><a href="{$smarty.server.HTTP_REFERER}" class="button_small" title="{l s='Back' mod='paypalapi'}">&laquo; {l s='Back' mod='paypalapi'}</a></p>
	</div>
{/if}