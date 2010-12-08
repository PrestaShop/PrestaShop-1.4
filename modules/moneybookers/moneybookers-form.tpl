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

<html>
	<body style="margin: 0; padding: 0;">
		<form action="https://www.moneybookers.com/app/payment.pl" method="post">
			<input type="hidden" name="pay_to_email" value="{$pay_to_email}" />
			<input type="hidden" name="recipient_description" value="{$recipient_description}" />
			<input type="hidden" name="transaction_id" value="{$transaction_id}" />
			<input type="hidden" name="return_url" value="{$return_url}" />
			<input type="hidden" name="return_url_text" value="{$return_url}" />
			<input type="hidden" name="cancel_url" value="{$return_url}" />
			<input type="hidden" name="status_url" value="{$status_url}" />
			<input type="hidden" name="status_url2" value="{$pay_to_email}" />
			<input type="hidden" name="language" value="{$language}" />
			<input type="hidden" name="hide_login" value="{$hide_login}" />
			<input type="hidden" name="pay_from_email" value="{$pay_from_email}" />
			<input type="hidden" name="firstname" value="{$firstname}" />
			<input type="hidden" name="lastname" value="{$lastname}" />
			{if (!empty($date_of_birth))}<input type="hidden" name="date_of_birth" value="{$date_of_birth}" />{/if}
			<input type="hidden" name="address" value="{$address}" />
			{if (!empty($address2))}<input type="hidden" name="address2" value="{$address2}" />{/if}
			{if (!empty($phone_number))}<input type="hidden" name="phone_number" value="{$phone_number}" />{/if}
			<input type="hidden" name="postal_code" value="{$postal_code}" />
			<input type="hidden" name="city" value="{$city}" />
			{if isset($state) && !empty($state)}<input type="hidden" name="state" value="{$state}" />{/if}
			<input type="hidden" name="country" value="{$country}" />
			<input type="hidden" name="amount" value="{$amount}" />
			<input type="hidden" name="currency" value="{$currency}" />
			<input type="hidden" name="amount2_description" value="{if isset($amount2_description)}{$amount2_description}{/if}}" />
			<input type="hidden" name="amount2" value="{$amount2}" />
			<input type="hidden" name="amount3_description" value="{$amount3_description}" />
			<input type="hidden" name="amount3" value="{$amount3}" />
			<input type="hidden" name="amount4_description" value="{$amount4_description}" />
			<input type="hidden" name="amount4" value="{$amount4}" />
			<input type="hidden" name="payment_methods" value="ACC">
			<input type="hidden" name="return_url_target" value="2">  
			<input type="hidden" name="cancel_url_target" value="2">  
			<input type="hidden" name="merchant_fields" value="platform"> 
			<input type="hidden" name="platform" value="prestashop">
			<input type="image" src="{$modules_dir}moneybookers/logo-cc-{$id_logo}.gif" name="Submit" style="float: left; margin-right: 20px;" />
			<h3 style="float: left; font-size: 11px; font-family: Verdana; color: #5D717E; font-weight: normal; margin-top: 35px;">{l s='Pay by credit card' mod='moneybookers'}</h3>
			<div style="clear: both;"></div>
		</form>
		<form action="https://www.moneybookers.com/app/payment.pl" method="post">
			<input type="hidden" name="pay_to_email" value="{$pay_to_email}" />
			<input type="hidden" name="recipient_description" value="{$recipient_description}" />
			<input type="hidden" name="transaction_id" value="{$transaction_id}" />
			<input type="hidden" name="return_url" value="{$return_url}" />
			<input type="hidden" name="return_url_text" value="{$return_url}" />
			<input type="hidden" name="cancel_url" value="{$return_url}" />
			<input type="hidden" name="status_url" value="{$status_url}" />
			<input type="hidden" name="status_url2" value="{$pay_to_email}" />
			<input type="hidden" name="language" value="{$language}" />
			<input type="hidden" name="hide_login" value="{$hide_login}" />
			{if (!empty($logo_url))}<input type="hidden" name="logo_url" value="{$logo_url}" />{/if}
			<input type="hidden" name="pay_from_email" value="{$pay_from_email}" />
			<input type="hidden" name="firstname" value="{$firstname}" />
			<input type="hidden" name="lastname" value="{$lastname}" />
			{if (!empty($date_of_birth))}<input type="hidden" name="date_of_birth" value="{$date_of_birth}" />{/if}
			<input type="hidden" name="address" value="{$address}" />
			{if (!empty($address2))}<input type="hidden" name="address2" value="{$address2}" />{/if}
			{if (!empty($phone_number))}<input type="hidden" name="phone_number" value="{$phone_number}" />{/if}
			<input type="hidden" name="postal_code" value="{$postal_code}" />
			<input type="hidden" name="city" value="{$city}" />
			{if isset($state) && !empty($state)}<input type="hidden" name="state" value="{$state}" />{/if}
			<input type="hidden" name="country" value="{$country}" />
			<input type="hidden" name="amount" value="{$amount}" />
			<input type="hidden" name="currency" value="{$currency}" />
			<input type="hidden" name="amount2_description" value="{if isset($amount2_description)}{$amount2_description}{/if}}" />
			<input type="hidden" name="amount2" value="{$amount2}" />
			<input type="hidden" name="amount3_description" value="{$amount3_description}" />
			<input type="hidden" name="amount3" value="{$amount3}" />
			<input type="hidden" name="amount4_description" value="{$amount4_description}" />
			<input type="hidden" name="amount4" value="{$amount4}" />
			<input type="hidden" name="payment_methods" value="WLT">
			<input type="hidden" name="return_url_target" value="2">  
			<input type="hidden" name="cancel_url_target" value="2">  
			<input type="hidden" name="merchant_fields" value="platform"> 
			<input type="hidden" name="platform" value="prestashop">
			<input type="image" style="float: left; margin-right: 20px;" src="{$modules_dir}moneybookers/logo-mb-{$id_logo_wallet}.gif" name="Submit">
			<h3 style="float: left; font-size: 11px; font-family: Verdana; color: #5D717E; font-weight: normal; margin-top: 35px;">{l s='Pay by Moneybookers eWallet' mod='moneybookers'}</h3>
			<div style="clear: both;"></div>
		</form>
	</body>
</html>
