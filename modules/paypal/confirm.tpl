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

{capture name=path}<a href="{$link->getPageLink('order.php', true)}">{l s='Your shopping cart' mod='paypal'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='PayPal' mod='paypal'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='paypal'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3>{l s='PayPal payment' mod='paypal'}</h3>
<form action="{$this_path_ssl}{$mode}submit.php" method="post">
	<input type="hidden" name="token" value="{$ppToken|escape:'htmlall'|stripslashes}" />
	<input type="hidden" name="payerID" value="{$payerID|escape:'htmlall'|stripslashes}" />
	<p>
		<img src="{$logo}" alt="{l s='PayPal' mod='paypal'}" style="margin-bottom: 5px" />
		<br />{l s='You have chosen to pay with PayPal.' mod='paypal'}
		<br/><br />
		{l s='Here is a short summary of your order:' mod='paypal'}
	</p>
	<p style="margin-top:20px;">
		- {l s='The total amount of your order is' mod='paypal'}
			<span id="amount_{$currency->id}" class="price">{convertPriceWithCurrency price=$total currency=$currency}</span> {l s='(tax incl.)' mod='paypal'}
	</p>
	<p>
		- {l s='We accept the following currency to be sent by PayPal:' mod='paypal'}&nbsp;<b>{$currency->name}</b>
			<input type="hidden" name="currency_payement" value="{$currency->id}">
	</p>
	<p>
		<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='paypal'}.</b>
	</p>
	<p class="cart_navigation">
		<a href="{$link->getPageLink('order.php', true)}?step=3" class="button_large">{l s='Other payment methods' mod='paypal'}</a>
		<input type="submit" name="submitPayment" value="{l s='I confirm my order' mod='paypal'}" class="exclusive_large" />
	</p>
</form>
