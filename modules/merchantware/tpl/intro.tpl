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

<form action="{$formCredential|escape:'htmlall':'UTF-8'}" method="POST">
  <fieldset>
    <div>
  <h3>{l s='To help your business save money, PrestaShop and Merchant Warehouse have partnered together to present special savings on your credit card payment solutions.' mod='merchantware'}</h3>
  <p>{l s='Merchant Warehouse enables PrestaShop clients to securely process all forms of electronic payments including credit, debit, rewards and gift cards as well as checks using any payment processing device.' mod='merchantware'}</p>
  <div style="float:right; margin-top:10px;"><iframe width="335" height="210" src="http://www.youtube.com/embed/798mpLiA9bs" frameborder="2" allowfullscreen></iframe></div><br />
  <h3>{l s='Merchant Warehouse delivers:' mod='merchantware'}</h3>
<ul>
	<li>
		{l s='Free payment gateway ($300 value)' mod='merchantware'}</li>
	<li>
		{l s='Reduced costs: no setup fees (savings up to $200) or additional transaction fees' mod='merchantware'}</li>
	<li>
		{l s='Award-winning, free, 24/7 in-house customer support' mod='merchantware'}</li>
	<li>
		{l s='Simplicity: eliminate the need of 3rd Party Gateway provider' mod='merchantware'}</li>
	<li>
		{l s='Fast, free and easy account set-ups' mod='merchantware'}</li>
	<li>
		{l s='Transparency: no contracts or hidden fees' mod='merchantware'}</li>
	<li>
		{l s='Extensive transaction and report management tools' mod='merchantware'}</li>
	<li>
		{l s='PCI-DSS certified payment processing solutions' mod='merchantware'}</li>
	<li>
		{l s='Seamless payments through your PrestaShop web store' mod='merchantware'}</li>
</ul></p></div><br />
<h3>{l s='Create your account TODAY by filling out the form below!' mod='merchantware'}</h3>
    <label for="company">{l s='Company' mod='merchantware'} <span class="required">*</span></label>
  <div class="margin-form">
    <input id="company" name="company" type="text" class="text" value="" />
  </div>
  <label for="firstname">{l s='Firstname' mod='merchantware'} <span class="required">*</span></label>
  <div class="margin-form">
    <input id="firstname" name="firstname" type="text" class="text" value="" />
  </div>
  <label for="lastname">{l s='Lastname' mod='merchantware'} <span class="required">*</span></label>
  <div class="margin-form">
    <input id="lastname" name="lastname" type="text" class="text" value="" />
  </div>
    <label for="email">{l s='Email' mod='merchantware'} <span class="required">*</span></label>
  <div class="margin-form">
    <input id="email" name="email" type="text" class="text" value="" />
  </div>
    <label for="address">{l s='Address' mod='merchantware'}</label>
    <div class="margin-form">
      <input id="address" name="address" type="text" class="text" value="" />
    </div>
    <label for="city">{l s='City' mod='merchantware'}</label>
    <div class="margin-form">
      <input id="city" name="city" type="text" class="text" value="" />
    </div>
    <label for="state">{l s='State' mod='merchantware'}</label>
    <div class="margin-form">
      <select id="state" name="state_id">
	{foreach from=$states item=state}
	<option value='{$state.id_state|intval}'>{$state.name|escape:'htmlall':'UTF-8'}</option>
	{/foreach}
      </select>
    </div>
    <label for="zipcode">{l s='Zip Code' mod='merchantware'}</label>
    <div class="margin-form">
      <input id="zipcode" name="zipcode" type="text" class="text" value="" />
    </div>
    <label for="phone">{l s='Phone Number' mod='merchantware'} </label>
    <div class="margin-form">
      <input id="phone" name="phone" type="text" class="text" value="" />
    </div>
    <label for="phone2">{l s='Home Phone Number' mod='merchantware'} </label>
    <div class="margin-form">
      <input id="phone2" name="phone2" type="text" class="text" value="" />
    </div>
    <label for="comments">{l s='Products sold' mod='merchantware'}<br />{l s='or Services provided' mod='merchantware'} </label>
    <div class="margin-form" style="height:115px;">
      <textarea name="comments" id="comments" cols="30" rows="6" class="textarea"></textarea>
    </div>
    <div class="margin-form" style="height:115px;">
      <input type="submit" id="validForm" name="validForm" value="{l s='Register' mod='merchantware'}" />
    </div>
  </fieldset>
</form>
