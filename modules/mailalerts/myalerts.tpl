<script type="text/javascript">
<!--
	var baseDir = '{$base_dir_ssl}';
-->
</script>

<div id="myalerts">
	{capture name=path}<a href="{$base_dir_ssl}my-account.php">{l s='My account' mod='mailalerts'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='My alerts' mod='mailalerts'}{/capture}
	{include file=$tpl_dir./breadcrumb.tpl}

	<h2>{l s='My alerts' mod='mailalerts'}</h2>

	{include file=$tpl_dir./errors.tpl}

	{if $id_customer|intval neq 0}
		{if $alerts}
		<div id="block-history" class="block-center">
			<table class="std">
				<thead>
					<tr>
						<th class="first_item">{l s='Product' mod='mailalerts'}</th>
						<th class="last_item" style="width:20px;">{l s='Delete' mod='mailalerts'}</th>
					</tr>
				</thead>
				<tbody>
				{foreach from=$alerts item=product name=i}
				<tr>
					<td class="first_item">
					<span style="float:left;"><a href="{$product.link|escape:'htmlall':'UTF-8'}"><img src="{$img_prod_dir}{$product.cover}-small.jpg" alt="{$product.name|escape:'htmlall':'UTF-8'}" /></a></span>
					<span style="float:left;"><a href="{$product.link|escape:'htmlall':'UTF-8'}">{$product.name|truncate:40:'...'|escape:'htmlall':'UTF-8'}</a>
					{if isset($product.attributes_small)}
						<br /><i>{$product.attributes_small|escape:'htmlall':'UTF-8'}</i>
					{/if}</span>
					</td>
					<td class="align_center">
						<a href="{$base_dir_ssl}modules/mailalerts/myalerts.php?action=delete&id_product={$product.id_product}&id_product_attribute={$product.id_product_attribute}"><img src="{$content_dir}modules/mailalerts/img/delete.gif" alt="{l s='Delete' mod='mailalerts'}" /></a>
					</td>
				</tr>
				</tbody>
			{/foreach}
			</table>
		</div>
		<div id="block-order-detail">&nbsp;</div>
		{else}
			<p class="warning">{l s='You are not subscribed to any alerts.' mod='mailalerts'}</p>
		{/if}
	{/if}

	<ul class="footer_links">
		<li><a href="{$base_dir_ssl}my-account.php"><img src="{$img_dir}icon/my-account.gif" alt="" class="icon" /></a><a href="{$base_dir_ssl}my-account.php">{l s='Back to Your Account' mod='mailalerts'}</a></li>
		<li><a href="{$base_dir}"><img src="{$img_dir}icon/home.gif" alt="" class="icon" /></a><a href="{$base_dir}">{l s='Home' mod='mailalerts'}</a></li>
	</ul>
</div>
