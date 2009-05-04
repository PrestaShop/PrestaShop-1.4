<script type="text/javascript">
// <![CDATA[
idSelectedCountry = {if isset($smarty.post.id_state)}{$smarty.post.id_state|intval}{else}false{/if};
countries = new Array();
{foreach from=$countries item='country'}
	{if isset($country.states)}
		countries[{$country.id_country|intval}] = new Array();
		{foreach from=$country.states item='state' name='states'}
			countries[{$country.id_country|intval}]['{$state.id_state|intval}'] = '{$state.name|escape:'htmlall':'UTF-8'}';
		{/foreach}
	{/if}
{/foreach}
//]]>
</script>

<h2>{l s='Check your informations' mod='paypalapi'}</h2>

{assign var='current_step' value='login'}
{include file=$tpl_dir./order-steps.tpl}

{include file=$tpl_dir./errors.tpl}

<form action="{$base_dir_ssl}modules/paypalapi/express/submit.php" method="post" id="account-creation_form" class="std">
	<fieldset class="account_creation">
		<h3>{l s='Your personal information' mod='paypalapi'}</h3>
		<p class="radio required">
			<span>{l s='Title'}</span>
			<input type="radio" name="id_gender" id="id_gender1" value="1" {if isset($smarty.post.id_gender) && $smarty.post.id_gender == 1}checked="checked"{/if} />
			<label for="id_gender1" class="top">{l s='Mr.' mod='paypalapi'}</label>
			<input type="radio" name="id_gender" id="id_gender2" value="2" {if isset($smarty.post.id_gender) && $smarty.post.id_gender == 2}checked="checked"{/if} />
			<label for="id_gender2" class="top">{l s='Ms.' mod='paypalapi'}</label>
		</p>
		<p class="required text">
			<label for="customer_firstname">{l s='First name' mod='paypalapi'}</label>
			<input onkeyup="$('#firstname').val(this.value);" type="text" class="text" id="customer_firstname" name="customer_firstname" value="{$firstname}" />
			<sup>*</sup>
		</p>
		<p class="required text">
			<label for="customer_lastname">{l s='Last name' mod='paypalapi'}</label>
			<input onkeyup="$('#lastname').val(this.value);" type="text" class="text" id="customer_lastname" name="customer_lastname" value="{$lastname}" />
			<sup>*</sup>
		</p>
		<p class="required text">
			<label for="email">{l s='E-mail' mod='paypalapi'}</label>
			<input type="text" class="text" id="email" name="email" value="{$email}" />
			<sup>*</sup>
		</p>
		<p class="required password">
			<label for="password">{l s='Password' mod='paypalapi'}</label>
			<input type="password" class="text" name="passwd" id="passwd" />
			<sup>*</sup>
			<span class="form_info">{l s='(5 characters min.)' mod='paypalapi'}</span>
		</p>
		<p class="select">
			<span>{l s='Birthday' mod='paypalapi'}</span>
			<select id="days" name="days">
				<option value="">-</option>
				{foreach from=$days item=day}
					<option value="{$day|escape:'htmlall':'UTF-8'}" {if ($sl_day == $day)} selected="selected"{/if}>{$day|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
				{/foreach}
			</select>
			{*
				{l s='January' mod='paypalapi'}
				{l s='February' mod='paypalapi'}
				{l s='March' mod='paypalapi'}
				{l s='April' mod='paypalapi'}
				{l s='May' mod='paypalapi'}
				{l s='June' mod='paypalapi'}
				{l s='July' mod='paypalapi'}
				{l s='August' mod='paypalapi'}
				{l s='September' mod='paypalapi'}
				{l s='October' mod='paypalapi'}
				{l s='November' mod='paypalapi'}
				{l s='December' mod='paypalapi'}
			*}
			<select id="months" name="months">
				<option value="">-</option>
				{foreach from=$months key=k item=month}
					<option value="{$k|escape:'htmlall':'UTF-8'}" {if ($sl_month == $k)} selected="selected"{/if}>{$month}&nbsp;</option>
				{/foreach}
			</select>
			<select id="years" name="years">
				<option value="">-</option>
				{foreach from=$years item=year}
					<option value="{$year|escape:'htmlall':'UTF-8'}" {if ($sl_year == $year)} selected="selected"{/if}>{$year|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
				{/foreach}
			</select>
		</p>
		<p class="checkbox" >
			<input type="checkbox" name="newsletter" id="newsletter" value="1" {if isset($smarty.post.newsletter) AND $smarty.post.newsletter == 1} checked="checked"{/if} />
			<label for="newsletter">{l s='Sign up for our newsletter' mod='paypalapi'}</label>
		</p>
		<p class="checkbox" >
			<input type="checkbox"name="optin" id="optin" value="1" {if isset($smarty.post.optin) AND $smarty.post.optin == 1} checked="checked"{/if} />
			<label for="optin">{l s='Receive special offers from our partners' mod='paypalapi'}</label>
		</p>
	</fieldset>
	<fieldset class="account_creation">
		<h3>{l s='Your address' mod='paypalapi'}</h3>
		<p class="text">
			<label for="company">{l s='Company' mod='paypalapi'}</label>
			<input type="text" class="text" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{/if}" />
		</p>
		<p class="required text">
			<label for="firstname">{l s='First name' mod='paypalapi'}</label>
			<input type="text" class="text" id="firstname" name="firstname" value="{$firstname}" />
			<sup>*</sup>
		</p>
		<p class="required text">
			<label for="lastname">{l s='Last name' mod='paypalapi'}</label>
			<input type="text" class="text" id="lastname" name="lastname" value="{$lastname}" />
			<sup>*</sup>
		</p>
		<p class="required text">
			<label for="address1">{l s='Address' mod='paypalapi'}</label>
			<input type="text" class="text" name="address1" id="address1" value="{$street}" />
			<sup>*</sup>
		</p>
		<p class="text">
			<label for="address2">{l s='Address (2)' mod='paypalapi'}</label>
			<input type="text" class="text" name="address2" id="address2" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{/if}" />
		</p>
		<p class="required text">
			<label for="postcode">{l s='Postal code / Zip code' mod='paypalapi'}</label>
			<input type="text" class="text" name="postcode" id="postcode" value="{$zip}" />
			<sup>*</sup>
		</p>
		<p class="required text">
			<label for="city">{l s='City' mod='paypalapi'}</label>
			<input type="text" class="text" name="city" id="city" value="{$city}" />
			<sup>*</sup>
		</p>
		<p class="required select">
			<label for="id_country">{l s='Country' mod='paypalapi'}</label>
			<select name="id_country" id="id_country">
				<option value="">-</option>
				{foreach from=$countries item=v}
				<option value="{$v.id_country}" {if ($sl_country == $v.id_country)} selected="selected"{/if}>{$v.name|escape:'htmlall':'UTF-8'}</option>
				{/foreach}
			</select>
			<sup>*</sup>
		</p>
		<p class="required id_state select">
			<label for="id_state">{l s='State' mod='paypalapi'}</label>
			<select name="id_state" id="id_state">
				<option value="">-</option>
			</select>
			<sup>*</sup>
		</p>
		<p class="textarea">
			<label for="other">{l s='Additional information' mod='paypalapi'}</label>
			<textarea name="other" id="other" cols="26" rows="3">{if isset($smarty.post.other)}{$smarty.post.other}{/if}</textarea>
		</p>
		<p class="text">
			<label for="phone">{l s='Home phone' mod='paypalapi'}</label>
			<input type="text" class="text" name="phone" id="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{/if}" />
		</p>
		<p class="text">
			<label for="phone_mobile">{l s='Mobile phone' mod='paypalapi'}</label>
			<input type="text" class="text" name="phone_mobile" id="phone_mobile" value="{if isset($smarty.post.phone_mobile)}{$smarty.post.phone_mobile}{/if}" />
		</p>
		<p class="required text" id="address_alias">
			<label for="alias">{l s='Assign an address title for future reference' mod='paypalapi'} !</label>
			<input type="text" class="text" name="alias" id="alias" value="{if isset($smarty.post.alias)}{$smarty.post.alias}{else}{l s='My address' mod='paypalapi'}{/if}" />
			<sup>*</sup>
		</p>
	</fieldset>
	<p class="cart_navigation required submit">
		<input type="hidden" name="token" value="{$ppToken|escape:'htmlall'|stripslashes}" />
		<input type="hidden" name="payerID" value="{$payerID|escape:'htmlall'|stripslashes}" />
		<input type="submit" name="submitAccount" id="submitAccount" value="{l s='Continue' mod='paypalapi'}" class="exclusive" />
		<span><sup>*</sup>{l s='Required field' mod='paypalapi'}</span>
	</p>
</form>