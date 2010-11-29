<h2>{l s='Please login' mod='paypal'}</h2>

{assign var='current_step' value='login'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"}

<form action="{$base_dir_ssl}modules/paypal/express/submit.php" method="post" id="login_form" class="std">
	<fieldset>
		<h3>{l s='This email has already been registered, please login !' mod='paypal'}</h3>
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
