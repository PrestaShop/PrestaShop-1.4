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

<h2>{l s='Please log in' mod='paypal'}</h2>

{assign var='current_step' value='login'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}

<form action="{$base_dir_ssl}modules/paypal/express/submit.php" method="post" id="login_form" class="std">
	<fieldset>
		<h3>{l s='This email has already been registered, please log in !' mod='paypal'}</h3>
		<p class="text">
			<label for="email" style="text-align:left; margin-left:10px;">{l s='E-mail address' mod='paypal'}</label>
			<span><input type="text" id="email" name="email" value="{$email|escape:'htmlall'|stripslashes}" class="account_input" /></span>
		</p>
		<p class="text">
			<label for="passwd" style="text-align:left; margin-left:10px;">{l s='Password' mod='paypal'}</label>
			<span><input type="password" id="passwd" name="passwd" value="{$passwd|escape:'htmlall'|stripslashes}" class="account_input" /></span>
		</p>
		<p class="submit" style="padding-top:15px;">
			<input type="hidden" name="token" value="{$ppToken|escape:'htmlall'|stripslashes}" />
			<input type="hidden" name="payerID" value="{$payerID|escape:'htmlall'|stripslashes}" />
			<input type="submit" id="submitLogin" name="submitLogin" class="button" value="{l s='Log in' mod='paypal'}" />
		</p>
		<p class="lost_password center"><a href="{$link->getPageLink('password.php')}">{l s='Forgot your password?' mod='paypal'}</a></p>
	</fieldset>
</form>
