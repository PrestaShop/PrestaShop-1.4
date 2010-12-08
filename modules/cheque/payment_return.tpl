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

{if $status == 'ok'}
	<p>{l s='Your order on' mod='cheque'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='cheque'}
		<br /><br />
		{l s='Please send us a cheque with:' mod='cheque'}
		<br /><br />- {l s='an amount of' mod='cheque'} <span class="price">{$total_to_pay}</span>
		<br /><br />- {l s='payable to the order of' mod='cheque'} <span class="bold">{if $chequeName}{$chequeName}{else}___________{/if}</span>
		<br /><br />- {l s='mail to' mod='cheque'} <span class="bold">{if $chequeAddress}{$chequeAddress}{else}___________{/if}</span>
		<br /><br />{l s='An e-mail has been sent to you with this information.' mod='cheque'}
		<br /><br /><span class="bold">{l s='Your order will be sent as soon as we receive your payment.' mod='cheque'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='cheque'} <a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='cheque'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='cheque'} 
		<a href="{$link->getPageLink('contact-form.php', true)}">{l s='customer support' mod='cheque'}</a>.
	</p>
{/if}
