{if $errors|@count > 0}
	{include file=$tpl_dir./errors.tpl}
{else}
	<h2>{l s='Registration completed' mod='emailverify'}</h2>
	{l s='Your account has been successfuly activated.' mod='emailverify'}<br />
	{l s='You can now log in to our shop.' mod='emailverify'}
{/if}