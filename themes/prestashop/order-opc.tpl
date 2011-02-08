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
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

{if $PS_CATALOG_MODE}
	<h2 id="cart_title">{l s='Your shopping cart'}</h2>
	<p class="warning">{l s='This store has not accepted your new order.'}</p>
{else}
<script type="text/javascript">
	// <![CDATA[
	var baseDir = '{$base_dir_ssl}';
	var imgDir = '{$img_dir}';
	var authenticationUrl = '{$link->getPageLink("authentication.php", true)}';
	var orderOpcUrl = '{$link->getPageLink("order-opc.php", true)}';
	var historyUrl = '{$link->getPageLink("history.php", true)}';
	var guestTrackingUrl = '{$link->getPageLink("guest-tracking.php", true)}';
	var addressUrl = '{$link->getPageLink("address.php", true)}';
	var orderProcess = 'order-opc';
	var guestCheckoutEnabled = {$PS_GUEST_CHECKOUT_ENABLED|intval};
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyRate = '{$currencyRate|floatval}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	var displayPrice = {$priceDisplay};
	var taxEnabled = {$use_taxes};
	var conditionEnabled = {$conditions|intval};
	var countries = new Array();
	var countriesNeedIDNumber = new Array();
	var countriesNeedZipCode = new Array();
	var vat_management = {$vat_management|intval};
	
	var txtWithTax = "{l s='(tax incl.)'}";
	var txtWithoutTax = "{l s='(tax excl.)'}";
	var txtHasBeenSelected = "{l s='has been selected'}";
	var txtNoCarrierIsSelected = "{l s='No carrier has been selected'}";
	var txtNoCarrierIsNeeded = "{l s='No carrier is needed for this order'}";
	var txtConditionsIsNotNeeded = "{l s='No terms of service must be accepted'}";
	var txtTOSIsAccepted = "{l s='Terms of service is accepted'}";
	var txtTOSIsNotAccepted = "{l s='Terms of service have not been accepted'}";
	var txtThereis = "{l s='There is'}";
	var txtErrors = "{l s='error(s)'}";
	var txtDeliveryAddress = "{l s='Delivery address'}";
	var txtInvoiceAddress = "{l s='Invoice address'}";
	var txtModifyMyAddress = "{l s='Modify my address'}";
	var txtInstantCheckout = "{l s='Instant checkout'}";
	var errorCarrier = "{$errorCarrier}";
	var errorTOS = "{$errorTOS}";
	var checkedCarrier = "{if isset($checked)}{$checked}{else}0{/if}";

	var addresses = new Array();
	var isLogged = {$isLogged|intval};
	var isGuest = {$isGuest|intval};
	var isVirtualCart = {$isVirtualCart|intval};
	var isPaymentStep = {$isPaymentStep|intval};
	//]]>
</script>

{capture name=path}{l s='Your shopping cart'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}
{if $productNumber}
	<!-- Shopping cart -->
	<h2 id="cart_title">{l s='Your shopping cart'} <span id="summary_products_label" style="float:right;margin-right:10px;">{l s='contains'}<span id="summary_products_quantity">{$productNumber}</span> {if $productNumber == 1}{l s='product'}{else}{l s='products'}{/if}</span></h2>
	<p style="display:none" id="emptyCartWarning" class="warning">{l s='Your cart is empty'}</p>
	<div id="order-detail-content" class="table_block">
		<table id="cart_summary" class="std">
			<thead>
				<tr>
					<th class="cart_product first_item">{l s='Product'}</th>
					<th class="cart_description item">{l s='Description'}</th>
					<th class="cart_ref item">{l s='Ref.'}</th>
					<th class="cart_availability item">{l s='Avail.'}</th>
					<th class="cart_unit item">{l s='Unit price'}</th>
					<th class="cart_quantity item">{l s='Qty'}</th>
					<th class="cart_total last_item">{l s='Total'}</th>
				</tr>
			</thead>
			<tfoot>
				{if $use_taxes}
					{if $priceDisplay}
						<tr class="cart_total_price">
							<td colspan="6">{l s='Total products (tax excl.):'}</td>
							<td class="price" id="total_product">{displayPrice price=$total_products}</td>
						</tr>
					{else}
						<tr class="cart_total_price">
							<td colspan="6">{l s='Total products (tax incl.):'}</td>
							<td class="price" id="total_product">{displayPrice price=$total_products_wt}</td>
						</tr>
					{/if}
				{else}
					<tr class="cart_total_price">
						<td colspan="6">{l s='Total products:'}</td>
						<td class="price" id="total_product">{displayPrice price=$total_products}</td>
					</tr>
				{/if}
				{if $total_discounts != 0}
					{if $use_taxes}
						{if $priceDisplay}
							<tr class="cart_total_voucher">
								<td colspan="6">{l s='Total vouchers (tax excl.):'}</td>
								<td class="price-discount" id="total_discount">{displayPrice price=$total_discounts_tax_exc}</td>
							</tr>
						{else}
							<tr class="cart_total_voucher">
								<td colspan="6">{l s='Total vouchers (tax incl.):'}</td>
								<td class="price-discount" id="total_discount">{displayPrice price=$total_discounts}</td>
							</tr>
						{/if}
					{else}
						<tr class="cart_total_voucher">
							<td colspan="6">{l s='Total vouchers:'}</td>
							<td class="price-discount" id="total_discount">{displayPrice price=$total_discounts_tax_exc}</td>
						</tr>
					{/if}
				{/if}
				{if $use_taxes}
				    {if $priceDisplay}
				    	<tr {if $total_wrapping == 0}style="display:none;"{/if} class="cart_total_wrapping">
				    		<td colspan="6">{l s='Total gift-wrapping (tax excl.):'}</td>
				    		<td id="total_wrapping" class="price-discount">{displayPrice price=$total_wrapping_tax_exc}</td>
				    	</tr>
				    {else}
				    	<tr {if $total_wrapping == 0}style="display:none;"{/if} class="cart_total_wrapping">
				    		<td colspan="6">{l s='Total gift-wrapping (tax incl.):'}</td>
				    		<td id="total_wrapping" class="price-discount">{displayPrice price=$total_wrapping}</td>
				    	</tr>
				    {/if}
				{else}
				    <tr {if $total_wrapping == 0}style="display:none;"{/if} class="cart_total_wrapping">
				    	<td colspan="6">{l s='Total gift-wrapping:'}</td>
				    	<td id="total_wrapping" class="price-discount">{displayPrice price=$total_wrapping_tax_exc}</td>
				    </tr>
				{/if}
				{if $use_taxes}
					{if $priceDisplay}
						<tr class="cart_total_delivery" {if $shippingCost <= 0} style="display:none;"{/if}>
							<td colspan="6">{l s='Total shipping (tax excl.):'}</td>
							<td class="price" id="total_shipping">{displayPrice price=$shippingCostTaxExc}</td>
						</tr>
					{else}
						<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none;"{/if}>
							<td colspan="6">{l s='Total shipping (tax incl.):'}</td>
							<td class="price" id="total_shipping" >{displayPrice price=$shippingCost}</td>
						</tr>
					{/if}
				{else}
					<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none;"{/if}>
						<td colspan="6">{l s='Total shipping:'}</td>
						<td class="price" id="total_shipping" >{displayPrice price=$shippingCostTaxExc}</td>
					</tr>
				{/if}			
				{if $use_taxes}
				<tr class="cart_total_price">
					<td colspan="6">{l s='Total (tax excl.):'}</td>
					<td id="total_price_without_tax" class="price">{displayPrice price=$total_price_without_tax}</td>
				</tr>
				<tr class="cart_total_voucher">
					<td colspan="6">{l s='Total tax:'}</td>
					<td id="total_tax" class="price">{displayPrice price=$total_tax}</td>
				</tr>
				<tr class="cart_total_price">
					<td colspan="6">{l s='Total (tax incl.):'}</td>
					<td id="total_price" class="price">{displayPrice price=$total_price}</td>
				</tr>
				{else}
				<tr class="cart_total_price">
					<td colspan="6">{l s='Total:'}</td>
					<td id="total_price" class="price">{displayPrice price=$total_price_without_tax}</td>
				</tr>
				{/if}
				<tr class="cart_free_shipping" {if $free_ship <= 0 || $isVirtualCart} style="display: none;" {/if}>
					<td colspan="6" style="white-space: normal;">{l s='Remaining amount to be added to your cart in order to obtain free shipping:'}</td>
					<td id="free_shipping" class="price">{displayPrice price=$free_ship}</td>
				</tr>
			</tfoot>
			<tbody>
			{foreach from=$products item=product name=productLoop}
				{assign var='productId' value=$product.id_product}
				{assign var='productAttributeId' value=$product.id_product_attribute}
				{assign var='quantityDisplayed' value=0}
				{* Display the product line *}
				{include file="$tpl_dir./shopping-cart-product-line.tpl"}
				{* Then the customized datas ones*}
				{if isset($customizedDatas.$productId.$productAttributeId)}
					{foreach from=$customizedDatas.$productId.$productAttributeId key='id_customization' item='customization'}
						<tr class="alternate_item cart_item">
							<td colspan="5">
								{foreach from=$customization.datas key='type' item='datas'}
									{if $type == $CUSTOMIZE_FILE}
										<div class="customizationUploaded">
											<ul class="customizationUploaded">
												{foreach from=$datas item='picture'}<li><img src="{$pic_dir}{$picture.value}_small" alt="" class="customizationUploaded" /></li>{/foreach}
											</ul>
										</div>
									{elseif $type == $CUSTOMIZE_TEXTFIELD}
										<ul class="typedText">
											{foreach from=$datas item='textField' name='typedText'}<li>{if $textField.name}{$textField.name}{else}{l s='Text #'}{$smarty.foreach.typedText.index+1}{/if}{l s=':'} {$textField.value}</li>{/foreach}
										</ul>
									{/if}
								{/foreach}
							</td>
							<td class="cart_quantity">
								<div style="float:right">
								<a rel="nofollow" class="cart_quantity_delete" id="{$product.id_product}_{$product.id_product_attribute}" href="{$link->getPageLink('cart.php', true)}?delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" title="{l s='Delete this customization'}" width="11" height="13" class="icon" /></a>
								</div>
								<div id="cart_quantity_button" style="float:left">
									<a rel="nofollow" class="cart_quantity_up" id="{$product.id_product}_{$product.id_product_attribute}" href="{$link->getPageLink('cart.php', true)}?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}" title="{l s='Add'}"><img src="{$img_dir}icon/quantity_up.gif" alt="{l s='Add'}" width="14" height="9" /></a><br />
									<a rel="nofollow" class="cart_quantity_down" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}" href="{$link->getPageLink('cart.php', true)}?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;op=down&amp;token={$token_cart}" title="{l s='Subtract'}"><img src="{$img_dir}icon/quantity_down.gif" width="14" height="9" alt="{l s='Subtract'}" /></a>
								</div>
								<input type="hidden" value="{$customization.quantity}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_hidden"/>
								<input size="2" type="text" value="{$customization.quantity}" class="cart_quantity_input" name="quantity_{$product.id_product}_{$product.id_product_attribute}"/>
							</td>
							<td class="cart_total"></td>
						</tr>
						{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
					{/foreach}
					{* If it exists also some uncustomized products *}
					{if $product.quantity-$quantityDisplayed > 0}{include file="$tpl_dir./shopping-cart-product-line.tpl"}{/if}
				{/if}
			{/foreach}
			</tbody>
		{if $discounts AND $total_discounts != 0}
			<tbody>
			{foreach from=$discounts item=discount name=discountLoop}
				<tr class="cart_discount {if $smarty.foreach.discountLoop.last}last_item{elseif $smarty.foreach.discountLoop.first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
					<td class="cart_discount_name" colspan="2">{$discount.name}</td>
					<td class="cart_discount_description" colspan="3">{$discount.description}</td>
					<td class="cart_discount_delete"><a href="{$link->getPageLink('order-opc.php', true)}?deleteDiscount={$discount.id_discount}" title="{l s='Delete'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" width="11" height="13" class="icon" /></a></td>
					<td class="cart_discount_price"><span id="discount_price_{$discount.id_discount}" class="price-discount">
						{if $discount.value_real > 0}
							{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
						{/if}
					</span></td>
				</tr>
			{/foreach}
			</tbody>
		{/if}
		</table>
	</div>
	
	{if $voucherAllowed}
	<div id="cart_voucher" class="table_block">
		<form action="{$link->getPageLink('order-opc.php', true)}" method="post" id="voucher">
			<fieldset>
				<h4>{l s='Vouchers'}</h4>
				<p>
					<label for="discount_name">{l s='Code:'}</label>
					<input type="text" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
				</p>
				<p class="submit"><input type="hidden" name="submitDiscount" /><input type="submit" name="submitAddDiscount" value="{l s='Add'}" class="button" /></p>
			{if isset($displayVouchers) && $displayVouchers}
				<h4>{l s='Take advantage of our offers:'}</h4>
				<div id="display_cart_vouchers">
				{foreach from=$displayVouchers item=voucher}
					<span onclick="$('#discount_name').val('{$voucher.name}');return false;" class="voucher_name">{$voucher.name}</span> - {$voucher.description} <br />
				{/foreach}
				</div>
			{/if}
			</fieldset>
		</form>
	</div>
	{/if}
	<div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>
	<p>
		<span id="HOOK_SHOPPING_CART_EXTRA">{$HOOK_SHOPPING_CART_EXTRA}</span>
	</p>
	<!-- END Shopping cart -->
	{if $isLogged AND !$isGuest}
		{include file="$tpl_dir./order-opc-address.tpl"}
	{else}
		<!-- Create account / Guest account / Login block -->
		<div id="opc_dynamic_block_1">
			<h2>1. {l s='Account'}</h2>
			<div id="opc_block_1" class="opc_block_content">
				<form action="{$link->getPageLink('authentication.php', true)}?back=order-opc.php" method="post" id="login_form" class="std">
					<fieldset>
						<h3>{l s='Already registered?'} <a href="#" id="openLoginFormBlock">{l s='Click here'}</a></h3>
						<div id="login_form_content" style="display:none;">
							<!-- Error return block -->
							<div id="opc_login_errors" class="error" style="display:none;"></div>
							<!-- END Error return block -->
							<div style="margin-left:40px;margin-bottom:5px;float:left;width:40%;">
								<label for="login_email">{l s='E-mail address'}</label>
								<span><input type="text" id="login_email" name="email" /></span>
							</div>
							<div style="margin-left:40px;margin-bottom:5px;float:left;width:40%;">
								<label for="passwd">{l s='Password'}</label>
								<span><input type="password" id="passwd" name="passwd" /></span>
							</div>
							<p class="submit">
								{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
								<input type="submit" id="SubmitLogin" name="SubmitLogin" class="button" value="{l s='Log in'}" />
							</p>
							<p class="lost_password"><a href="{$base_dir}password.php">{l s='Forgot your password?'}</a></p>
						</div>
					</fieldset>
				</form>
				<form action="#" method="post" id="new_account_form" class="std">
					<fieldset>
						<h3 id="new_account_title">{l s='New Customer'}</h3>
						<div id="opc_account_choice">
							<div class="opc_float">
								<h4>{l s='Instant Checkout'}</h4>
								<p>
									<input type="button" class="exclusive_large" id="opc_guestCheckout" value="{l s='Checkout as guest'}" />
								</p>
							</div>
							
							<div class="opc_float">
								<h4>{l s='Create your account today and enjoy:'}</h4>
								<ul class="bullet">
									<li>{l s='Personalized and secure access'}</li>
									<li>{l s='Fast and easy check out'}</li>
								</ul>
								<p>
									<input type="button" class="button_large" id="opc_createAccount" value="{l s='Create an account'}" />
								</p>
							</div>
							<div class="clear"></div>
						</div>
						<div id="opc_account_form">
							<script type="text/javascript">
							// <![CDATA[
							idSelectedCountry = {if isset($guestInformations) && $guestInformations.id_state}{$guestInformations.id_state|intval}{else}false{/if};
							{if isset($countries)}
								{foreach from=$countries item='country'}
									{if isset($country.states) && $country.contains_states}
										countries[{$country.id_country|intval}] = new Array();
										{foreach from=$country.states item='state' name='states'}
											countries[{$country.id_country|intval}].push({ldelim}'id' : '{$state.id_state}', 'name' : '{$state.name|escape:'htmlall':'UTF-8'}'{rdelim});
										{/foreach}
									{/if}
									{if $country.need_identification_number}
										countriesNeedIDNumber.push({$country.id_country|intval});
									{/if}	
									{if isset($country.need_zip_code)}
										countriesNeedZipCode[{$country.id_country|intval}] = {$country.need_zip_code};
									{/if}
								{/foreach}
							{/if}
							//]]>
							{if $vat_management}
								{literal}
								function vat_number()
								{
									if ($('#company').val() != '')
										$('#vat_number_block').show();
									else
										$('#vat_number_block').hide();
								}
								function vat_number_invoice()
								{
									if ($('#company_invoice').val() != '')
										$('#vat_number_block_invoice').show();
									else
										$('#vat_number_block_invoice').hide();
								}
								
								$(document).ready(function() {
									$('#company').blur(function(){
										vat_number();
									});
									$('#company_invoice').blur(function(){
										vat_number_invoice();
									});
									vat_number();
									vat_number_invoice();
								});
								{/literal}
							{/if}
							</script>
							<!-- Error return block -->
							<div id="opc_account_errors" class="error" style="display:none;"></div>
							<!-- END Error return block -->
							<!-- Account -->
							<input type="hidden" id="is_new_customer" name="is_new_customer" value="0" />
							<input type="hidden" id="opc_id_customer" name="opc_id_customer" value="{if isset($guestInformations) && $guestInformations.id_customer}{$guestInformations.id_customer}{else}0{/if}" />
							<input type="hidden" id="opc_id_address_delivery" name="opc_id_address_delivery" value="{if isset($guestInformations) && $guestInformations.id_address_delivery}{$guestInformations.id_address_delivery}{else}0{/if}" />
							<input type="hidden" id="opc_id_address_invoice" name="opc_id_address_invoice" value="{if isset($guestInformations) && $guestInformations.id_address_delivery}{$guestInformations.id_address_delivery}{else}0{/if}" />
							<p class="required text">
								<label for="email">{l s='E-mail'}</label>
								<input type="text" class="text" id="email" name="email" value="{if isset($guestInformations) && $guestInformations.email}{$guestInformations.email}{/if}" />
								<sup>*</sup>
							</p>
							<p class="required password is_customer_param">
								<label for="passwd">{l s='Password'}</label>
								<input type="password" class="text" name="passwd" id="passwd" />
								<sup>*</sup>
								<span class="form_info">{l s='(5 characters min.)'}</span>
							</p>
							<p class="radio required">
								<span>{l s='Title'}</span>
								<input type="radio" name="id_gender" id="id_gender1" value="1" {if isset($guestInformations) && $guestInformations.id_gender == 1}checked="checked"{/if} />
								<label for="id_gender1" class="top">{l s='Mr.'}</label>
								<input type="radio" name="id_gender" id="id_gender2" value="2" {if isset($guestInformations) && $guestInformations.id_gender == 2}checked="checked"{/if} />
								<label for="id_gender2" class="top">{l s='Ms.'}</label>
							</p>
							<p class="required text">
								<label for="firstname">{l s='First name'}</label>
								<input type="text" class="text" id="firstname" name="firstname" onblur="$('#customer_firstname').val($(this).val());" value="{if isset($guestInformations) && $guestInformations.firstname}{$guestInformations.firstname}{/if}" />
								<input type="hidden" class="text" id="customer_firstname" name="customer_firstname" value="{if isset($guestInformations) && $guestInformations.firstname}{$guestInformations.firstname}{/if}" />
								<sup>*</sup>
							</p>
							<p class="required text">
								<label for="lastname">{l s='Last name'}</label>
								<input type="text" class="text" id="lastname" name="lastname" onblur="$('#customer_lastname').val($(this).val());" value="{if isset($guestInformations) && $guestInformations.lastname}{$guestInformations.lastname}{/if}" />
								<input type="hidden" class="text" id="customer_lastname" name="customer_lastname" value="{if isset($guestInformations) && $guestInformations.lastname}{$guestInformations.lastname}{/if}" />
								<sup>*</sup>
							</p>
							<p class="select">
								<span>{l s='Date of Birth'}</span>
								<select id="days" name="days">
									<option value="">-</option>
									{foreach from=$days item=day}
										<option value="{$day|escape:'htmlall':'UTF-8'}" {if isset($guestInformations) && ($guestInformations.sl_day == $day)} selected="selected"{/if}>{$day|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
									{/foreach}
								</select>
								{*
									{l s='January'}
									{l s='February'}
									{l s='March'}
									{l s='April'}
									{l s='May'}
									{l s='June'}
									{l s='July'}
									{l s='August'}
									{l s='September'}
									{l s='October'}
									{l s='November'}
									{l s='December'}
								*}
								<select id="months" name="months">
									<option value="">-</option>
									{foreach from=$months key=k item=month}
										<option value="{$k|escape:'htmlall':'UTF-8'}" {if isset($guestInformations) && ($guestInformations.sl_month == $k)} selected="selected"{/if}>{l s="$month"}&nbsp;</option>
									{/foreach}
								</select>
								<select id="years" name="years">
									<option value="">-</option>
									{foreach from=$years item=year}
										<option value="{$year|escape:'htmlall':'UTF-8'}" {if isset($guestInformations) && ($guestInformations.sl_year == $year)} selected="selected"{/if}>{$year|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
									{/foreach}
								</select>
							</p>
							<p class="checkbox">
								<input type="checkbox" name="newsletter" id="newsletter" value="1" {if isset($guestInformations) && $guestInformations.newsletter}checked="checked"{/if} />
								<label for="newsletter">{l s='Sign up for our newsletter'}</label>
							</p>
							<p class="checkbox" >
								<input type="checkbox"name="optin" id="optin" value="1" {if isset($guestInformations) && $guestInformations.optin}checked="checked"{/if} />
								<label for="optin">{l s='Receive special offers from our partners'}</label>
							</p>
							<h3>{l s='Delivery address'}</h3>
							<p class="text">
								<label for="company">{l s='Company'}</label>
								<input type="text" class="text" id="company" name="company" value="{if isset($guestInformations) && $guestInformations.company}{$guestInformations.company}{/if}" />
							</p>
							<div id="vat_number_block" style="display:none;">
								<p class="text">
									<label for="vat_number">{l s='VAT number'}</label>
									<input type="text" class="text" name="vat_number" id="vat_number" value="{if isset($guestInformations) && $guestInformations.vat_number}{$guestInformations.vat_number}{/if}" />
								</p>
							</div>
							<p class="required text dni">
								<label for="dni">{l s='Identification number'}</label>
								<input type="text" class="text" name="dni" id="dni" value="{if isset($guestInformations) && $guestInformations.dni}{$guestInformations.dni}{/if}" />
								<span class="form_info">{l s='DNI / NIF / NIE'}</span>
							</p>
							<p class="required text">
								<label for="address1">{l s='Address'}</label>
								<input type="text" class="text" name="address1" id="address1" value="{if isset($guestInformations) && $guestInformations.address1}{$guestInformations.address1}{/if}" />
								<sup>*</sup>
							</p>
							<p class="text is_customer_param">
								<label for="address2">{l s='Address (Line 2)'}</label>
								<input type="text" class="text" name="address2" id="address2" value="" />
							</p>
							<p class="required postcode text">
								<label for="postcode">{l s='Zip / Postal code'}</label>
								<input type="text" class="text" name="postcode" id="postcode" value="{if isset($guestInformations) && $guestInformations.postcode}{$guestInformations.postcode}{/if}" onkeyup="$('#postcode').val($('#postcode').val().toUpperCase());" />
								<sup>*</sup>
							</p>
							<p class="required text">
								<label for="city">{l s='City'}</label>
								<input type="text" class="text" name="city" id="city" value="{if isset($guestInformations) && $guestInformations.city}{$guestInformations.city}{/if}" />
								<sup>*</sup>
							</p>
							<p class="required select">
								<label for="id_country">{l s='Country'}</label>
								<select name="id_country" id="id_country">
									<option value="">-</option>
									{foreach from=$countries item=v}
									<option value="{$v.id_country}" {if (isset($guestInformations) AND $guestInformations.id_country == $v.id_country) OR (!isset($guestInformations) && $sl_country == $v.id_country)} selected="selected"{/if}>{$v.name|escape:'htmlall':'UTF-8'}</option>
									{/foreach}
								</select>
								<sup>*</sup>
							</p>
							<p class="required id_state select">
								<label for="id_state">{l s='State'}</label>
								<select name="id_state" id="id_state">
									<option value="">-</option>
								</select>
								<sup>*</sup>
							</p>
							<p class="textarea is_customer_param">
								<label for="other">{l s='Additional information'}</label>
								<textarea name="other" id="other" cols="26" rows="3"></textarea>
							</p>
							<p class="text">
								<label for="phone">{l s='Home phone'}</label>
								<input type="text" class="text" name="phone" id="phone" value="{if isset($guestInformations) && $guestInformations.phone}{$guestInformations.phone}{/if}" /> <sup>*</sup>
							</p>
							<p class="text is_customer_param">
								<label for="phone_mobile">{l s='Mobile phone'}</label>
								<input type="text" class="text" name="phone_mobile" id="phone_mobile" value="" />
							</p>
							<input type="hidden" name="alias" id="alias" value="{l s='My address'}" />
							
							<p class="checkbox is_customer_param">
								<input type="checkbox" name="invoice_address" id="invoice_address" />
								<label for="invoice_address"><b>{l s='Please use another address for invoice'}</b></label>
							</p>
							
							<div id="opc_invoice_address" class="is_customer_param">
								<h3>{l s='Invoice address'}</h3>
								<p class="text is_customer_param">
									<label for="company_invoice">{l s='Company'}</label>
									<input type="text" class="text" id="company_invoice" name="company_invoice" value="" />
								</p>
								<div id="vat_number_block_invoice" class="is_customer_param" style="display:none;">
									<p class="text">
										<label for="vat_number_invoice">{l s='VAT number'}</label>
										<input type="text" class="text" id="vat_number_invoice" name="vat_number_invoice" value="" />
									</p>
								</div>
								<p class="required text dni_invoice">
									<label for="dni">{l s='Identification number'}</label>
									<input type="text" class="text" name="dni" id="dni" value="{if isset($guestInformations) && $guestInformations.dni}{$guestInformations.dni}{/if}" />
									<span class="form_info">{l s='DNI / NIF / NIE'}</span>
								</p>
								<p class="required text">
									<label for="address1_invoice">{l s='Address'}</label>
									<input type="text" class="text" name="address1_invoice" id="address1_invoice" value="" />
									<sup>*</sup>
								</p>
								<p class="text is_customer_param">
									<label for="address2_invoice">{l s='Address (2)'}</label>
									<input type="text" class="text" name="address2_invoice" id="address2_invoice" value="" />
								</p>
								<p class="required postcode text">
									<label for="postcode_invoice">{l s='Zip / Postal Code'}</label>
									<input type="text" class="text" name="postcode_invoice" id="postcode_invoice" value="" onkeyup="$('#postcode').val($('#postcode').val().toUpperCase());" />
									<sup>*</sup>
								</p>
								<p class="required text">
									<label for="city_invoice">{l s='City'}</label>
									<input type="text" class="text" name="city_invoice" id="city_invoice" value="" />
									<sup>*</sup>
								</p>
								<p class="required select">
									<label for="id_country_invoice">{l s='Country'}</label>
									<select name="id_country_invoice" id="id_country_invoice">
										<option value="">-</option>
										{foreach from=$countries item=v}
										<option value="{$v.id_country}" {if ($sl_country == $v.id_country)} selected="selected"{/if}>{$v.name|escape:'htmlall':'UTF-8'}</option>
										{/foreach}
									</select>
									<sup>*</sup>
								</p>
								<p class="required id_state_invoice select" style="display:none;">
									<label for="id_state_invoice">{l s='State'}</label>
									<select name="id_state_invoice" id="id_state_invoice">
										<option value="">-</option>
									</select>
									<sup>*</sup>
								</p>
								<p class="textarea is_customer_param">
									<label for="other_invoice">{l s='Additional information'}</label>
									<textarea name="other_invoice" id="other_invoice" cols="26" rows="3"></textarea>
								</p>
								<p class="text">
									<label for="phone_invoice">{l s='Home phone'}</label>
									<input type="text" class="text" name="phone_invoice" id="phone_invoice" value="" /> <sup>*</sup>
								</p>
								<p class="text is_customer_param">
									<label for="phone_mobile_invoice">{l s='Mobile phone'}</label>
									<input type="text" class="text" name="phone_mobile_invoice" id="phone_mobile_invoice" value="" />
								</p>
								<input type="hidden" name="alias_invoice" id="alias_invoice" value="{l s='My Invoice address'}" />
							</div>
							<p style="float: right;"><input type="submit" class="exclusive button" name="submitAccount" id="submitAccount" value="{l s='Continue'}" /></p>
							<p style="clear: both;">
								<sup>*</sup>{l s='Required field'}
							</p>
							<!-- END Account -->
						</div>
					</fieldset>
				</form>
				<div class="clear"></div>
			</div>
			<div id="opc_block_1_status" class="opc_status"></div>
		</div>
		<!-- END Create account / Guest account / Login block -->
	{/if}
	<!-- Delivery method block -->
	<h2>2. {l s='Delivery methods'}</h2>
	<div id="opc_block_2" class="opc_block_content">
		<div id="HOOK_BEFORECARRIER">{if isset($carriers)}{$HOOK_BEFORECARRIER}{/if}</div>
		{if isset($virtualCart) && $isVirtualCart}
		<p class="warning">{l s='No carrier needed for this order'}</p>
		{else}
		<p class="warning" id="noCarrierWarning" {if isset($carriers) && $carriers && count($carriers)}style="display:none;"{/if}>{l s='There are no carriers available that deliver to this address.'}</p>
		<table id="carrierTable" class="std" {if !isset($carriers) || !$carriers || !count($carriers)}style="display:none;"{/if}>
			<thead>
				<tr>
					<th class="carrier_action first_item"></th>
					<th class="carrier_name item">{l s='Carrier'}</th>
					<th class="carrier_infos item">{l s='Information'}</th>
					<th class="carrier_price last_item">{l s='Price'}</th>
				</tr>
			</thead>
			<tbody>
			{if isset($carriers)}
				{foreach from=$carriers item=carrier name=myLoop}
					<tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{else}item{/if}">
						<td class="carrier_action radio">
							<input type="radio" name="id_carrier" value="{$carrier.id_carrier|intval}" id="id_carrier{$carrier.id_carrier|intval}" {if $carrier.id_carrier == $checked || $carriers|@count == 1}checked="checked"{/if} />
						</td>
						<td class="carrier_name">
							<label for="id_carrier{$carrier.id_carrier|intval}">
								{if $carrier.img}<img src="{$carrier.img|escape:'htmlall':'UTF-8'}" alt="{$carrier.name|escape:'htmlall':'UTF-8'}" />{else}{$carrier.name|escape:'htmlall':'UTF-8'}{/if}
							</label>
						</td>
						<td class="carrier_infos">{$carrier.delay|escape:'htmlall':'UTF-8'}</td>
						<td class="carrier_price">
							{if $carrier.price}
								<span class="price">
									{if $priceDisplay == 1}{convertPrice price=$carrier.price_tax_exc}{else}{convertPrice price=$carrier.price}{/if}
								</span>
								{if $use_taxes}{if $priceDisplay == 1} {l s='(tax excl.)'}{else} {l s='(tax incl.)'}{/if}{/if}
							{else}
								{l s='Free!'}
							{/if}
						</td>
					</tr>
				{/foreach}
				<tr id="HOOK_EXTRACARRIER">{$HOOK_EXTRACARRIER}</tr>
			{/if}
			</tbody>
		</table>
		<div style="display: none;" id="extra_carrier"></div>
	
		{if $recyclablePackAllowed}
		<p id="recyclable_block" class="checkbox" {if !isset($carriers) || !$carriers || !count($carriers)}style="display:none;"{/if}>
			<input type="checkbox" name="recyclable" id="recyclable" value="1" {if $recyclable == 1}checked="checked"{/if} onclick="updateCarrierSelectionAndGift();" />
			<label for="recyclable">{l s='I agree to receive my order in recycled packaging'}.</label>
		</p>
		{/if}
		
		<h3>{l s='Leave a message'}</h3>
		<div>
			<p>{l s='If you would like to comment on your order, please write it below.'}</p>
			<p><textarea cols="120" rows="3" name="message" id="message">{if isset($oldMessage)}{$oldMessage}{/if}</textarea></p>
		</div>
		
		{if $giftAllowed}
			<h3 class="gift_title">{l s='Gift'}</h3>
			<p class="checkbox">
				<input type="checkbox" name="gift" id="gift" value="1" {if $cart->gift == 1}checked="checked"{/if} onclick="$('#gift_div').toggle('slow');updateCarrierSelectionAndGift();"/>
				<label for="gift">{l s='I would like the order to be gift-wrapped.'}</label>
				<br />
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				{if $gift_wrapping_price > 0}
					({l s='Additional cost of'}
					<span class="price">
						{if $priceDisplay == 1}{convertPrice price=$total_wrapping_tax_exc_cost}{else}{convertPrice price=$total_wrapping_cost}{/if}
					</span>
					{if $use_taxes}{if $priceDisplay == 1} {l s='(tax excl.)'}{else} {l s='(tax incl.)'}{/if}{/if})
				{/if}
			</p>
			<p id="gift_div" {if $cart->gift == 0}style="display:none;"{/if} class="textarea">
				<label for="gift_message">{l s='If you wish, you can add a note to the gift:'}</label>
				<textarea rows="5" cols="35" id="gift_message" name="gift_message">{$cart->gift_message|escape:'htmlall':'UTF-8'}</textarea>
			</p>
			{/if}
		{/if}
	
		<div>
			<div style="float:left;"><a href="#opc_block_1" class="button opc_button">{l s='Back'}</a></div>
			<div style="float:right;"><a href="#opc_block_3" class="exclusive opc_button">{l s='Continue'}</a></div>
		</div>
		<div class="clear"></div>
	</div>
	<div id="opc_block_2_status" class="opc_status"></div>
	<!-- END Delivery method block -->
	
	<!-- Terms of service block -->
	<h2>3. {l s='Terms of service'}</h2>
	<div id="opc_block_3" class="opc_block_content">
		{if $conditions AND $cms_id}
		<p class="checkbox">
			<input type="checkbox" name="cgv" id="cgv" value="1" {if $checkedTOS}checked="checked"{/if} />
			<label for="cgv">{l s='I agree to the terms of service and adhere to them unconditionally.'}</label> <a href="{$link_conditions}" class="iframe">{l s='(read)'}</a>
		</p>
		<script type="text/javascript">$('a.iframe').fancybox();</script>
		{/if}
		
		<div>
			<div style="float:left;"><a href="{if $isVirtualCart}#opc_block_1{else}#opc_block_2{/if}" class="button opc_button">{l s='Back'}</a></div>
			<div style="float:right;"><a href="#opc_block_4" class="exclusive opc_button">{l s='Continue'}</a></div>
		</div>
		<div class="clear"></div>
	</div>
	<div id="opc_block_3_status" class="opc_status"></div>
	<!-- END Terms of service block -->
	
	<!-- Payment methods block -->
	<h2>4. {l s='Payment methods'}</h2>
	<div id="opc_block_4" class="opc_block_content">
		<div id="opc_payment_list">{if $isVirtualCart}<p class="warning">{l s='Please wait... we are going to validate your order'}</p>{/if}</div>
		
		{if !$isVirtualCart}
		<div>
			<div style="float:left;"><a href="#opc_block_3" class="button opc_button">{l s='Back'}</a></div>
		</div>
		{/if}
		<div class="clear"></div>
	</div>
	<!-- END Terms of service block -->
	<div class="clear"></div>	
{else}
	<h2>{l s='Your shopping cart'}</h2>
	<p class="warning">{l s='Your shopping cart is empty.'}</p>
{/if}
{/if}