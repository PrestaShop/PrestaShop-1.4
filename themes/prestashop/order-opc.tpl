<script type="text/javascript">
	// <![CDATA[
	var baseDir = '{$base_dir_ssl}';
	var imgDir = '{$img_dir}';
	var orderProcess = 'order-opc';
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyRate = '{$currencyRate|floatval}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	var displayPrice = {$priceDisplay};
	var taxEnabled = {$use_taxes};
	
	var txtWithTax = "{l s='(tax incl.)'}";
	var txtWithoutTax = "{l s='(tax excl.)'}";
	var txtHasBeenSelected = "{l s='has been selected'}";
	var txtNoCarrierIsSelected = "{l s='No carrier is selected'}";
	var txtTOSIsAccepted = "{l s='Terms of service is accepted'}";
	var txtTOSIsNotAccepted = "{l s='Terms of service isn\'t accepted'}";

	var addresses = new Array();
	//]]>
</script>
{include file=$tpl_dir./thickbox.tpl}
{capture name=path}{l s='Your shopping cart'}{/capture}
{include file=$tpl_dir./breadcrumb.tpl}
{if $productNumber}
	<h2 id="cart_title">{l s='Your shopping cart'} <span id="summary_products_label" style="float:right;margin-right:10px;">{l s='contains'}<span id="summary_products_quantity">{$productNumber}</span> {if $productNumber == 1}{l s='product'}{else}{l s='products'}{/if}</span></h2>
	<p style="display:none" id="emptyCartWarning" class="warning">Votre panier est vide</p>
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
				    	<tr {if $total_wrapping == 0}style="display:none;"{/if} class="cart_total_voucher">
				    		<td colspan="6">{l s='Total gift-wrapping (tax excl.):'}</td>
				    		<td id="total_wrapping" class="price-discount">{displayPrice price=$total_wrapping_tax_exc}</td>
				    	</tr>
				    {else}
				    	<tr {if $total_wrapping == 0}style="display:none;"{/if} class="cart_total_voucher">
				    		<td colspan="6">{l s='Total gift-wrapping (tax incl.):'}</td>
				    		<td id="total_wrapping" class="price-discount">{displayPrice price=$total_wrapping}</td>
				    	</tr>
				    {/if}
				{else}
				    <tr {if $total_wrapping == 0}style="display:none;"{/if} class="cart_total_voucher">
				    	<td colspan="6">{l s='Total gift-wrapping:'}</td>
				    	<td id="total_wrapping" class="price-discount">{displayPrice price=$total_wrapping_tax_exc}</td>
				    </tr>
				{/if}
				{if $shippingCost > 0}
					{if $use_taxes}
						{if $priceDisplay}
							<tr class="cart_total_delivery">
								<td colspan="6">{l s='Total shipping (tax excl.):'}</td>
								<td id="total_shipping" class="price">{displayPrice price=$shippingCostTaxExc}</td>
							</tr>
						{else}
							<tr class="cart_total_delivery">
								<td colspan="6">{l s='Total shipping (tax incl.):'}</td>
								<td id="total_shipping" class="price">{displayPrice price=$shippingCost}</td>
							</tr>
						{/if}
					{else}
						<tr class="cart_total_delivery">
							<td colspan="6">{l s='Total shipping:'}</td>
							<td id="total_shipping" class="price">{displayPrice price=$shippingCostTaxExc}</td>
						</tr>
					{/if}
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
				{if $free_ship > 0 AND !$isVirtualCart}
				<tr class="cart_free_shipping">
					<td colspan="6" style="white-space: normal;">{l s='Remaining amount to be added to your cart in order to obtain free shipping:'}</td>
					<td class="price">{displayPrice price=$free_ship}</td>
				</tr>
				{/if}
			</tfoot>
			<tbody>
			{foreach from=$products item=product name=productLoop}
				{assign var='productId' value=$product.id_product}
				{assign var='productAttributeId' value=$product.id_product_attribute}
				{assign var='quantityDisplayed' value=0}
				{* Display the product line *}
				{include file=$tpl_dir./shopping-cart-product-line.tpl}
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
								<a class="cart_quantity_delete" id="{$product.id_product}_{$product.id_product_attribute}" href="{$base_dir_ssl}cart.php?delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" title="{l s='Delete this customization'}" class="icon" /></a>
								<input size="2" type="text" value="{$customization.quantity}"  name="quantity_{$product.id_product}_{$product.id_product_attribute}"/>
								<a class="cart_quantity_up" id="{$product.id_product}_{$product.id_product_attribute}" href="{$base_dir_ssl}cart.php?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}" title="{l s='Add'}"><img src="{$img_dir}icon/quantity_up.gif" alt="{l s='Add'}" /></a><br />
								<a class="cart_quantity_down" id="{$product.id_product}_{$product.id_product_attribute}" href="{$base_dir_ssl}cart.php?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;op=down&amp;token={$token_cart}" title="{l s='Substract'}"><img src="{$img_dir}icon/quantity_down.gif" alt="{l s='Substract'}" /></a>
							</td>
							<td class="cart_total"></td>
						</tr>
						{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
					{/foreach}
					{* If it exists also some uncustomized products *}
					{if $product.quantity-$quantityDisplayed > 0}{include file=$tpl_dir./shopping-cart-product-line.tpl}{/if}
				{/if}
			{/foreach}
			</tbody>
		{if $discounts AND $total_discounts != 0}
			<tbody>
			{foreach from=$discounts item=discount name=discountLoop}
				<tr class="cart_discount {if $smarty.foreach.discountLoop.last}last_item{elseif $smarty.foreach.discountLoop.first}first_item{else}item{/if}">
					<td class="cart_discount_name" colspan="2">{$discount.name}</td>
					<td class="cart_discount_description" colspan="3">{$discount.description}</td>
					<td class="cart_discount_delete"><a href="{$base_dir_ssl}order-opc.php?deleteDiscount={$discount.id_discount}" title="{l s='Delete'}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" class="icon" /></a></td>
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
		<form action="{$base_dir_ssl}order-opc.php" method="post" id="voucher">
			<fieldset>
				<h4>{l s='Vouchers'}</h4>
				<p>
					<label for="discount_name">{l s='Code:'}</label>
					<input type="text" id="discount_name" name="discount_name" value="{if $discount_name}{$discount_name}{/if}" />
				</p>
				<p class="submit"><input type="hidden" name="submitDiscount" /><input type="submit" name="submitAddDiscount" value="{l s='Add'}" class="button" /></p>
			{if $displayVouchers}
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
	{$HOOK_SHOPPING_CART}
	{if $isLogged}
		<p class="cart_navigation first_next_button"><a href="#" type="button" class="exclusive order-opc_next" name="order-opc_block-address">{l s='Next >'}</a></p>
		<h2 id="order-opc_address" class="order-opc_block"><img src="{$img_dir}icon/more.gif" alt="" class="order-opc_block-status" /> {l s='Addresses'}</h2>
		<div id="order-opc_block-address" class="addresses order-opc_block-content">
			<script type="text/javascript">
			 // <![CDATA[
				{foreach from=$addresses key=k item=address}
					addresses[{$address.id_address|intval}] = new Array('{$address.company|addslashes}', '{$address.firstname|addslashes}', '{$address.lastname|addslashes}', '{$address.address1|addslashes}', '{$address.address2|addslashes}', '{$address.postcode|addslashes}', '{$address.city|addslashes}', '{$address.country|addslashes}', '{$address.state|default:''|addslashes}');
				{/foreach}
			//]]>
			</script>
			<p class="address_delivery select">
				<label for="id_address_delivery">{l s='Choose a delivery address:'}</label>
				<select name="id_address_delivery" id="id_address_delivery" class="address_select" onchange="updateAddressesDisplay();">
				{foreach from=$addresses key=k item=address}
					<option value="{$address.id_address|intval}" {if $address.id_address == $cart->id_address_delivery}selected="selected"{/if}>{$address.alias|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
				</select>
			</p>
			<p class="checkbox">
				<input type="checkbox" name="same" id="addressesAreEquals" value="1" onclick="updateAddressesDisplay();" {if $cart->id_address_invoice == $cart->id_address_delivery || $addresses|@count == 1}checked="checked"{/if} />
				<label for="addressesAreEquals">{l s='Use the same address for billing.'}</label>
			</p>
			<p id="address_invoice_form" class="select" {if $cart->id_address_invoice == $cart->id_address_delivery}style="display: none;"{/if}>
			{if $addresses|@count > 1}
				<label for="id_address_invoice" class="strong">{l s='Choose a billing address:'}</label>
				<select name="id_address_invoice" id="id_address_invoice" class="address_select" onchange="updateAddressesDisplay();">
				{section loop=$addresses step=-1 name=address}
					<option value="{$addresses[address].id_address|intval}" {if $addresses[address].id_address == $cart->id_address_invoice && $cart->id_address_delivery != $cart->id_address_invoice}selected="selected"{/if}>{$addresses[address].alias|escape:'htmlall':'UTF-8'}</option>
				{/section}
				</select>
				{else}
					<a style="margin-left: 221px;" href="{$base_dir_ssl}address.php?back=order-opc.php{if $back}&mod={$back}{/if}" title="{l s='Add'}" class="button_large">{l s='Add a new address'}</a>
				{/if}
			</p>
			<div class="clear"></div>
			<ul class="address item" id="address_delivery">
				<li class="address_title">{l s='Your delivery address'}</li>
				<li class="address_company"></li>
				<li class="address_name"></li>
				<li class="address_address1"></li>
				<li class="address_address2"></li>
				<li class="address_city"></li>
				<li class="address_country"></li>
				<li class="address_update"><a href="{$base_dir_ssl}address.php?id_address={$address.id_address|intval}&amp;back=order-opc.php{if $back}&mod={$back}{/if}" title="{l s='Update'}">{l s='Update'}</a></li>
			</ul>
			<ul class="address alternate_item" id="address_invoice">
				<li class="address_title">{l s='Your billing address'}</li>
				<li class="address_company"></li>
				<li class="address_name"></li>
				<li class="address_address1"></li>
				<li class="address_address2"></li>
				<li class="address_city"></li>
				<li class="address_country"></li>
				<li class="address_update"><a href="{$base_dir_ssl}address.php?id_address={$address.id_address|intval}&amp;back=order-opc.php{if $back}&mod={$back}{/if}" title="{l s='Update'}">{l s='Update'}</a></li>
			</ul>
			<br class="clear" />
			<p class="address_add submit">
				<a href="{$base_dir_ssl}address.php?back=order-opc.php{if $back}&mod={$back}{/if}" title="{l s='Add'}" class="button_large">{l s='Add a new address'}</a>
			</p>
			<div id="ordermsg">
				<p>{l s='If you want to leave us comment about your order, please write it below.'}</p>
				<p class="textarea"><textarea cols="60" rows="3" name="message" id="message">{$oldMessage}</textarea></p>
			</div>
			<p class="cart_navigation"><a href="#" type="button" class="exclusive order-opc_next" name="order-opc_block-carrier">{l s='Next >'}</a></p>
		</div>
		<p id="order-opc_status-address" class="order-opc_status">
			{l s='Delivery address:'} "<span id="order-opc_status-address_delivery">Mon adresse</span>" {l s='has been selected'}<br />
			{l s='Invoice address:'} "<span id="order-opc_status-address_invoice">Mon adresse</span>" {l s='has been selected'}
		</p>
		
		<h2 class="order-opc_block"><img src="{$img_dir}icon/more.gif" alt="" class="order-opc_block-status" /> {l s='Choose your delivery method'}</h2>
		{$HOOK_BEFORECARRIER}
		<div id="order-opc_block-carrier" class="table_block order-opc_block-content">
		{if $isVirtualCart}
			<p class="warning">{l s='No carrier needed for this order'}</p>
		{else}
			<p class="warning" id="noCarrierWarning" {if $carriers && count($carriers)}style="display:none;"{/if}>{l s='There are no carriers available that will deliver to this address!'}</p>
			<table id="carrierTable" class="std" {if !$carriers || !count($carriers)}style="display:none;"{/if}>
				<thead>
					<tr>
						<th class="carrier_action first_item"></th>
						<th class="carrier_name item">{l s='Carrier'}</th>
						<th class="carrier_infos item">{l s='Information'}</th>
						<th class="carrier_price last_item">{l s='Price'}</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$carriers item=carrier name=myLoop}
					<tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{else}item{/if}">
						<td class="carrier_action radio">
							<input type="radio" name="id_carrier" value="{$carrier.id_carrier|intval}" id="id_carrier{$carrier.id_carrier|intval}" {if $carrier.id_carrier == $checked}checked="checked"{/if} onclick="updateCarrierSelectionAndGift();" />
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
				{$HOOK_EXTRACARRIER}
				</tbody>
			</table>
			<div style="display: none;" id="extra_carrier"></div>
		
			{if $recyclablePackAllowed}
			<p id="recyclable_block" class="checkbox" {if !$carriers || !count($carriers)}style="display:none;"{/if}>
				<input type="checkbox" name="recyclable" id="recyclable" value="1" {if $recyclable == 1}checked="checked"{/if} onclick="updateCarrierSelectionAndGift();" />
				<label for="recyclable">{l s='I agree to receive my order in recycled packaging'}.</label>
			</p>
			{/if}
		
			{if $giftAllowed}
				<script type="text/javascript" src="{$js_dir}layer.js"></script>
				<script type="text/javascript" src="{$smarty.const._PS_JS_DIR_}conditions.js"></script>
				{if !$virtual_cart && $giftAllowed && $cart->gift == 1}
				<script type="text/javascript">{literal}
				// <![CDATA[
				    $(function(){
				    	  $('#gift_div').toggle('slow');
				    });
				 //]]>
				{/literal}</script>
				{/if}
				<h3 class="gift_title">{l s='Gift'}</h3>
				<p class="checkbox">
					<input type="checkbox" name="gift" id="gift" value="1" {if $cart->gift == 1}checked="checked"{/if} onclick="$('#gift_div').toggle('slow');updateCarrierSelectionAndGift();"/>
					<label for="gift">{l s='I would like the order to be gift-wrapped.'}</label>
					<br />
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					{if $gift_wrapping_price > 0}
						({l s='Additional cost of'}
						<span class="price">
							{if $priceDisplay == 1}{convertPrice price=$total_wrapping_tax_exc}{else}{convertPrice price=$total_wrapping}{/if}
						</span>
						{if $use_taxes}{if $priceDisplay == 1} {l s='(tax excl.)'}{else} {l s='(tax incl.)'}{/if}{/if})
					{/if}
				</p>
				<p id="gift_div" class="textarea">
					<label for="gift_message">{l s='If you wish, you can add a note to the gift:'}</label>
					<textarea rows="5" cols="35" id="gift_message" name="gift_message">{$cart->gift_message|escape:'htmlall':'UTF-8'}</textarea>
				</p>
			{/if}
		{/if}
		<p class="cart_navigation"><a href="#" type="button" class="exclusive order-opc_next" name="order-opc_block-conditions">{l s='Next >'}</a></p>
		</div>
		<p id="order-opc_status-carrier" class="order-opc_status"></p>
		{if $conditions AND $cms_id}
			<h2 class="condition_title order-opc_block"><img src="{$img_dir}icon/more.gif" alt="" class="order-opc_block-status" /> {l s='Terms of service'}</h2>
			<p id="order-opc_block-conditions" class="checkbox order-opc_block-content">
				<input type="checkbox" name="cgv" id="cgv" value="1" {if $checkedTOS}checked="checked"{/if} />
				<label for="cgv">{l s='I agree with the terms of service and I adhere to them unconditionally.'}</label> <a href="{$link_conditions}" class="thickbox">{l s='(read)'}</a>
			</p>
			<p id="order-opc_status-TOS" class="order-opc_status"></p>
		{/if}
		<p id ="proceed_to_checkout" class="cart_navigation">
			<a href="#" class="exclusive_large" onclick="showPaymentModule();return false;">{l s='Proceed to Checkout'}</a>
		</p>
		
		<h2 id="payment_module_list_title">{l s='Proceed to Checkout'}</h2>
		<div id="payment_module_list" class="clear"></div>
	{else}
	<h2>{l s='LOG IN'}</h2>
	<form action="{$base_dir_ssl}authentication.php?back=order-opc.php#order-opc_address" method="post" id="create-account_form" class="std">
		<fieldset>
			<h3>{l s='Create your account'}</h3>
			<h4>{l s='Enter your e-mail address to create your account'}.</h4>
			<p class="text">
				<label for="email_create">{l s='E-mail address'}</label>
				<span><input type="text" id="email_create" name="email_create" value="{if isset($smarty.post.email_create)}{$smarty.post.email_create|escape:'htmlall'|stripslashes}{/if}" class="account_input" /></span>
			</p>
			<p class="submit">
			{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
				<input type="submit" id="SubmitCreate" name="SubmitCreate" class="button_large" value="{l s='Create your account'}" />
				<input type="hidden" class="hidden" name="SubmitCreate" value="{l s='Create your account'}" />
			</p>
		</fieldset>
	</form>
	<form action="{$base_dir_ssl}authentication.php?back=order-opc.php#order-opc_address" method="post" id="login_form" class="std">
		<fieldset>
			<h3>{l s='Already registered ?'}</h3>
			<p class="text">
				<label for="email">{l s='E-mail address'}</label>
				<span><input type="text" id="email" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall'|stripslashes}{/if}" class="account_input" /></span>
			</p>
			<p class="text">
				<label for="passwd">{l s='Password'}</label>
				<span><input type="password" id="passwd" name="passwd" value="{if isset($smarty.post.passwd)}{$smarty.post.passwd|escape:'htmlall'|stripslashes}{/if}" class="account_input" /></span>
			</p>
			<p class="submit">
				{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
				<input type="submit" id="SubmitLogin" name="SubmitLogin" class="button" value="{l s='Log in'}" />
			</p>
			<p class="lost_password"><a href="{$base_dir}password.php">{l s='Forgot your password?'}</a></p>
		</fieldset>
	</form>
	{/if}
	<div class="clear"></div>
	
{else}
	<h2>{l s='Your shopping cart'}</h2>
	<p class="warning">{l s='Your shopping cart is empty.'}</p>
{/if}
