<h2>{l s='Please login' mod='paypalapi'}</h2>

{assign var='current_step' value='login'}
{include file=$tpl_dir./order-steps.tpl}

{include file=$tpl_dir./errors.tpl}

<form action="{$base_dir_ssl}modules/paypalapi/express/submit.php" method="post" id="login_form" class="std">
	<fieldset>
		<h3>{l s='This email has already been registered, please login !' mod='paypalapi'}</h3>
		<p class="text">
			<label for="email" style="text-align:left; margin-left:10px;">{l s='E-mail address' mod='paypalapi'}</label>
			<span><input type="text" id="email" name="email" value="{$email|escape:'htmlall'|stripslashes}" class="account_input" /></span>
		</p>
		<p class="text">
			<label for="passwd" style="text-align:left; margin-left:10px;">{l s='Password' mod='paypalapi'}</label>
			<span><input type="password" id="passwd" name="passwd" value="{$passwd|escape:'htmlall'|stripslashes}" class="account_input" /></span>
		</p>
		<p class="submit" style="padding-top:15px;">
			<input type="hidden" name="token" value="{$ppToken|escape:'htmlall'|stripslashes}" />
			<input type="hidden" name="payerID" value="{$payerID|escape:'htmlall'|stripslashes}" />
			<input type="submit" id="submitLogin" name="submitLogin" class="button" value="{l s='Log in' mod='paypalapi'}" />
		</p>
		<p class="lost_password center"><a href="{$base_dir}password.php">{l s='Forgot your password?' mod='paypalapi'}</a></p>
	</fieldset>
</form>