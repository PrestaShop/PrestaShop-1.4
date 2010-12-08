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

<script type="text/javascript">
<!--
	var baseDir = '{$base_dir_ssl}';
-->
</script>

{capture name=path}{l s='Your payment method'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h1>{l s='Choose your payment method'}</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}

{if $HOOK_PAYMENT}
	<h4>{l s='Please choose the payment method you want to use to pay the amount of'}&nbsp;<span class="price">{convertPrice price=$total_price}</span> {if $taxes_enabled}{l s='(tax incl.)'}{/if}</h4><br />
	{$HOOK_PAYMENT}
{else}
	<p class="warning">{l s='No payment modules have been installed yet.'}</p>
{/if}

<p class="cart_navigation"><a href="{$link->getPageLink('order.php', true)}?step=2" title="{l s='Previous'}" class="button">&laquo; {l s='Previous'}</a></p>
