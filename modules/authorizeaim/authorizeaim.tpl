{*
* 2007-2011 PrestaShop
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
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<link rel="shortcut icon" type="image/x-icon" href="{$module_dir}secure.png" />
<p class="payment_module" >

	{if $isFailed == 1}
		<p style="color: red;">{l s='Error, please verify the card information' mod='authorizeaim'}</p>
	{/if}

	<form name="authorizeaim_form" id="authorizeaim_form" action="{$module_dir}validation.php" method="post">
		<span style="border: 1px solid #595A5E;display: block;padding: 0.6em;text-decoration: none;margin-left: 0.7em;">
			<a id="click_authorizeaim" href="#" title="{l s='Pay with authorizeaim' mod='authorizeaim'}" style="display: block;text-decoration: none; font-weight: bold;">
				{if $cards.visa == 1}<img src="{$module_dir}cards/visa.gif" alt="{l s='visa logo' mod='authorizeaim'}" style="vertical-align: middle;" />{/if}
				{if $cards.mastercard == 1}<img src="{$module_dir}cards/mastercard.gif" alt="{l s='mastercard logo' mod='authorizeaim'}" style="vertical-align: middle;" />{/if}
				{if $cards.discover == 1}<img src="{$module_dir}cards/discover.gif" alt="{l s='discover logo' mod='authorizeaim'}" style="vertical-align: middle;" />{/if}
				{if $cards.ax == 1}<img src="{$module_dir}cards/ax.gif" alt="{l s='american express logo' mod='authorizeaim'}" style="vertical-align: middle;" />{/if}
				&nbsp;&nbsp;{l s='Secured credit card payment with Authorize.net' mod='authorizeaim'}
			</a>

				<div id="aut2">

				<br /><br />			

				<div style="width: 136px; height: 165px; float: left; padding-top:40px; padding-right: 20px; border-right: 1px solid #DDD;">
					<img src="{$module_dir}logoa.gif" alt="secure payment" />
				</div>
				{foreach from=$p key=k item=v}
					<input type="hidden" name="{$k}" value="{$v}" />
				{/foreach}

				<label style="margin-top: 4px; margin-left: 40px;display: block;width: 90px;float: left;">{l s='Full name' mod='authorizeaim'}</label> <input type="text" name="name" id="fullname" size="27" maxlength="25S" /><img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;" /><br /><br />
				
				<label style="margin-top: 4px; margin-left: 40px;display: block;width: 90px;float: left;">{l s='Card Type' mod='authorizeaim'}</label>
				<select id="cardType">
					{if $cards.ax == 1}<option value="AmEx">American Express</option>{/if}
					{if $cards.visa == 1}<option value="Visa">Visa</option>{/if}
					{if $cards.mastercard == 1}<option value="MasterCard">MasterCard</option>{/if}
					{if $cards.discover == 1}<option value="Discover">Discover</option>{/if}
				</select>
				<img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;" /><br /><br />
				
				<label style="margin-top: 4px; margin-left: 40px;display: block;width: 90px;float: left;">{l s='Card number' mod='authorizeaim'}</label> <input type="text" name="x_card_num" id="cardnum" size="27" maxlength="16" autocomplete="Off" /><img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;" /><br /><br />
				<label style="margin-top: 4px; margin-left: 40px;display: block;width: 90px;float: left;">{l s='Expiration date' mod='authorizeaim'}</label> 
				<select name="x_exp_date_m" style="width: 40px;">{section name=date_m start=01 loop=13}
					<option value="{$smarty.section.date_m.index}">{$smarty.section.date_m.index}</option>{/section}
				</select>
				 / 
				<select name="x_exp_date_y">{section name=date_y start=11 loop=20}
					<option value="{$smarty.section.date_y.index}">20{$smarty.section.date_y.index}</option>{/section}
				</select>
				<img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;" /><br /><br />
				<label style="margin-top: 4px; margin-left: 40px;display: block;width: 90px;float: left;">{l s='CVV' mod='authorizeaim'}</label> <input type="text" name="x_card_code" id="x_card_code" size="4" maxlength="4" /><img src="{$module_dir}secure.png" alt="" style="margin-left: 5px;"/> <img src="{$module_dir}help.png" id="cvv_help" title="{l s='the 3 last digits on the back of your credit card' mod='authorizeaim'}" alt="" /><br /><br />
				<img src="{$module_dir}cvv.png" id="cvv_help_img" alt=""style="display: none;margin-left: 211px;" />
				<p style="text-align: center; width: 200px; float: left; margin: 10px 0 15px 102px;"><input type="submit" id="asubmit" value="{l s='Make payment' mod='authorizeaim'}" onclick="return checkAimForm();" style="" class="button exclusive_large" /></p>
				<div class="clear"></div>
			</div>
		</span>
	</form>
</p>
<script type="text/javascript">
	var mess_error = "{l s='Please check your credit card information (Credit card type, number and expiration date)' mod='authorizeaim' js=1}";
	var mess_error2 = "{l s='Please specify your Full Name' mod='authorizeaim' js=1}";
	{literal}
		function checkAimForm()
		{
			if ($('#fullname').val() == '')
			{
				alert(mess_error2);
				return false;
			}
			else if (!validateCC($('#cardnum').val(), $('#cardType').val()) || $('#x_card_code').val() == '')
			{
				alert(mess_error);
				return false;
			}
			else
			{
				return $('#authorizeaim_form').submit();
			}
		}
			
		$(document).ready(function(){
			$('#cvv_help').click(function(){
				$('#cvv_help_img').show();
				$('#cvv_help').unbind();
			});
		});

	{/literal}
</script>