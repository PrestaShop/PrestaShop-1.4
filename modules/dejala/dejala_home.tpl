	{if ($registered == 0)}

		<div style="float:left;width:48%;margin-left:10px;">
			<form action="{$formAction}" method="post">
				<input type="hidden" name="method" value="register">
				<fieldset >
					<legend>{l s='New to Dejala.fr ?' mod='dejala'}</legend>
					<div>
						{l s='Your shop name:' mod='dejala'} <input size="30" type="text" name="store_name" value="{$store_name}"/>
					</div>
					<div>
						{l s='Select your country:' mod='dejala'} 
						<select name="country">	
							<option value="fr">{l s='France' mod='dejala'}</option>
							<option value="es">{l s='Spain' mod='dejala'}</option>
						</select>
					</div>
					<div>
						{l s='Choose your login:' mod='dejala'} <input size="30" type="text" name="login" value="{$login}"/>
					</div>
					<div>
						{l s='Choose your password:' mod='dejala'} <input size="15" type="password" name="password" value=""/>
					</div>
					<br/>
					<input type="submit" name="btnSubmit" value="{l s='Register' mod='dejala'}" class="button" />
				</fieldset>
			</form>
		</div>

		<div style="float:left; margin-left:5px;width:47%;">
			<form action="{$formAction}" method="post">
				<input type="hidden" name="method" value="signin" />
				<fieldset>
					<legend>{l s='I already have an account for my shop:' mod='dejala'}</legend>
					<div>{l s='Login:' mod='dejala'} <input size="30" type="text" name="login" value="{$login}"/></div>
					<div>
						{l s='Select your country:' mod='dejala'} 
						<select name="country">	
							<option value="fr">{l s='France' mod='dejala'}</option>
							<option value="es">{l s='Spain' mod='dejala'}</option>
						</select>
					</div>
					<div>{l s='Password:' mod='dejala'} <input size="15" type="password" name="password" value=""/></div>
					<br/>
					<input type="submit" name="btnSubmit" value="{l s='Sign-in' mod='dejala'}" class="button" />
				</fieldset>
			</form>
		</div>
		<div class="clear"></div>

		{else}
			<fieldset>
			<div id="dejalaAutopub" style="float:right;margin:0px;margin-top:10px;">
				<iframe frameborder="no" scrolling="no" style="margin:0px; margin-top:-10px; padding: 0px; width: 310px; height: 270px;" src="http://module.pro.dejala.{$country}/tabs/home_pub.php"></iframe>
			</div>
			<div style="width:65%;">
				<legend>{l s='Dejala.fr' mod='dejala'}</legend>
				{l s='Your are running on the ' mod='dejala'}: {if ($djl_mode=='TEST')}{l s='test platform' mod='dejala'}{else}{l s='production platform' mod='dejala'}{/if}<br/>
								
				{* Switch mode button : switch between test/prod modes  *}
				{if ($djl_mode == 'PROD') }
					<form action="{$formAction}" method="post">
						<input type="hidden" name="method" value="switchMode">
						<input type="hidden" name="mode" value="TEST">
						<input type="submit" name="btnSubmit" value="{l s='Switch to test mode' mod='dejala'}" class="button" />
					</form>
				{else}
					{if ($isLiveReady=='1') }
						<form action="{$formAction}" method="post">
							<input type="hidden" name="method" value="switchMode">
							<input type="hidden" name="mode" value="PROD">
							<input type="submit" name="btnSubmit" value="{l s='Switch to production mode' mod='dejala'}" class="button" />
						</form>
					{else}
						{if ($isLiveRequested=='1') }
							{l s='Your request to go live is under process : Dejala.fr will contact you to finalize your registration.' mod='dejala'}
						{else}
							{* Demande de passage en prod *}
							<form action="{$formAction}" method="post">
								<input type="hidden" name="method" value="golive">
								<input type="submit" name="btnSubmit" value="{l s='Go live : request Dejala.fr to create my account in production.' mod='dejala'}" class="button" />
							</form>
						{/if}
					{/if}
				{/if}		
				
				<br/><br/>		
				{if ($djl_mode == 'PROD') }{l s='Your credit' mod='dejala'} :{else if ($djl_mode == 'TEST') }{l s='Your virtual credit (in order to test)' mod='dejala'} :{/if} {$account_balance} {l s='euros' mod='dejala'}<br/>
				{if ($djl_mode == 'PROD')}<a href="http://pro.dejala.{$country}" target="_blank" style="color:blue;font-weight:bold;text-decoration:underline;">{l s='Credit your account' mod='dejala'}</a><br/>{/if}
				
				<br/><br/>
				<form action="{$formAction}" method="post">
					<input type="hidden" name="method" value="switchActive">
					<input type="hidden" name="active_flag" value="{if ($is_active == 1)}0{else}1{/if}">
					{if ($is_active == 1)}{l s='Dejala IS visible in my shop' mod='dejala'}{else}{l s='Dejala IS NOT visible in my shop' mod='dejala'}{/if}<br/>
					<input type="submit" name="btnSubmit" value="{if ($is_active == 1)}{l s='Hide Dejala in my shop' mod='dejala'}{else}{l s='Show Dejala in my shop' mod='dejala'}{/if}" class="button" />
				</form>
			</div>
		
			
			</fieldset>
		{/if}
