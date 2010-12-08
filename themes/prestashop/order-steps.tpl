{*
* 2007-2010 PrestaShop 
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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

{* Assign a value to 'current_step' to display current style *}
<!-- Steps -->
<ul class="step" id="order_step">
	<li class="{if $current_step=='summary'}step_current{else}{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address' || $current_step=='login'}step_done{else}step_todo{/if}{/if}">
		{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address' || $current_step=='login'}
		<a href="{$link->getPageLink('order.php', true)}{if isset($back) && $back}?back={$back}{/if}">
			{l s='Summary'}
		</a>
		{else}
		{l s='Summary'}
		{/if}
	</li>
	<li class="{if $current_step=='login'}step_current{else}{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address'}step_done{else}step_todo{/if}{/if}">
		{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address'}
		<a href="{$link->getPageLink('order.php', true)}?step=1{if isset($back) && $back}&amp;back={$back}{/if}">
			{l s='Login'}
		</a>
		{else}
		{l s='Login'}
		{/if}
	</li>
	<li class="{if $current_step=='address'}step_current{else}{if $current_step=='payment' || $current_step=='shipping'}step_done{else}step_todo{/if}{/if}">
		{if $current_step=='payment' || $current_step=='shipping'}
		<a href="{$link->getPageLink('order.php', true)}?step=1{if isset($back) && $back}&amp;back={$back}{/if}">
			{l s='Address'}
		</a>
		{else}
		{l s='Address'}
		{/if}
	</li>
	<li class="{if $current_step=='shipping'}step_current{else}{if $current_step=='payment'}step_done{else}step_todo{/if}{/if}">
		{if $current_step=='payment'}
		<a href="{$link->getPageLink('order.php', true)}?step=2{if isset($back) && $back}&amp;back={$back}{/if}">
			{l s='Shipping'}
		</a>
		{else}
		{l s='Shipping'}
		{/if}
	</li>
	<li id="step_end" class="{if $current_step=='payment'}step_current{else}step_todo{/if}">
		{l s='Payment'}
	</li>
</ul>
<!-- /Steps -->
