{capture name=path}<a href="{$base_dir_ssl}order.php">{l s='Your shopping cart' mod='paypalapi'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='PayPal' mod='paypalapi'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}

<h2>{l s='Order summary' mod='paypalapi'}</h2>

{assign var='current_step' value='payment'}
{include file=$tpl_dir./order-steps.tpl}

<h3>{l s='PayPal payment' mod='paypalapi'}</h3>
<form action="{$this_path_ssl}{$mode}submit.php" method="post">
	<input type="hidden" name="token" value="{$ppToken|escape:'htmlall'|stripslashes}" />
	<input type="hidden" name="payerID" value="{$payerID|escape:'htmlall'|stripslashes}" />
	<p>
		<img src="{$content_dir}modules/paypalapi/paypalapi.gif" alt="{l s='PayPal' mod='paypalapi'}" style="float:left; margin: 0px 10px 5px 0px;" />
		{l s='You have chosen to pay with PayPal.' mod='paypalapi'}
		<br/><br />
		{l s='Here is a short summary of your order:' mod='paypalapi'}
	</p>
	<p style="margin-top:20px;">
		- {l s='The total amount of your order is' mod='paypalapi'}
			<span id="amount_{$currency->id}" class="price">{convertPriceWithCurrency price=$total currency=$currency}</span> {l s='(tax incl.)' mod='paypalapi'}
	</p>
	<p>
		- {l s='We accept the following currency to be sent by PayPal:' mod='paypalapi'}&nbsp;<b>{$currency->name}</b>
			<input type="hidden" name="currency_payement" value="{$currency->id}">
	</p>
	<p>
		<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='paypalapi'}.</b>
	</p>
	<p class="cart_navigation">
		<a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='paypalapi'}</a>
		<input type="submit" name="submitPayment" value="{l s='I confirm my order' mod='paypalapi'}" class="exclusive_large" />
	</p>
</form>