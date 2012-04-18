{if count($errors) == 0}
	<p>{l s='Votre commande a été enregistrée.' mod='kwixo'}</p>
	<p>{l s='Pour toute question merci de contacter notre ' mod='kwixo'} <a href="{$base_dir}contact-form.php">{l s='service client' mod='kwixo'}</a>.</p>
{else}
{include file="$tpl_dir./errors.tpl"}
	<p>{l s='Un probème a été rencontré. Si vous pensez qu\'il s\'agit d\'une erreur merci de contacter notre ' mod='kwixo'} <a href="{$base_dir}contact-form.php">{l s='service client.' mod='kwixo'}</a>.</p>
  <p><a href="{$base_dir}order.php"><img src="{$modules_dir}kwixo/img/cart.gif">{l s='Retour au panier'}</img></a></p>
{/if}
  <p><a href="{$base_dir}history.php"><img src="{$modules_dir}kwixo/img/order.gif">{l s='Retour aux commandes'}</img></a></p>