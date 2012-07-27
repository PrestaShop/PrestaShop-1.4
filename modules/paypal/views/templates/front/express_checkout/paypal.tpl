{*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14011 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<p class="payment_module">
	<a href="javascript:onclick($('#paypal_payment_form').submit())" title="{l s='Pay with PayPal' mod='paypal'}">
		{if isset($logos.LocalPayPalHorizontalSolutionPP) && $PayPal_payment_method == $PayPal_integral}
			<img src="{$logos.LocalPayPalHorizontalSolutionPP}" alt="{$PayPal_content.payment_choice}" height="48px" />
		{else}
			<img src="{$logos.LocalPayPalLogoMedium}" alt="{$PayPal_content.payment_choice}" />
		{/if}
		{$PayPal_content.payment_choice}
	</a>
</p>

<form id="paypal_payment_form" action="{$base_dir_ssl}modules/paypal/express_checkout/submit.php" title="{l s='Pay with PayPal' mod='paypal'}" method="post">
	<input type="hidden" name="express_checkout" value="{$PayPal_payment_type}"/>
	<input type="hidden" name="current_shop_url" value="{$PayPal_current_shop_url}" />
</form>