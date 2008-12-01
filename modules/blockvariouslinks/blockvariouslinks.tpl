<!-- MODULE Block various links -->
<ul class="block_various_links" id="block_various_links_footer">
	<li class="first_item"><a href="{$base_dir}prices-drop.php" title="">{l s='Specials' mod='blockvariouslinks'}</a></li>
	<li class="item"><a href="{$base_dir}new-products.php" title="">{l s='New products' mod='blockvariouslinks'}</a></li>
	<li class="item"><a href="{$base_dir}best-sales.php" title="">{l s='Top sellers' mod='blockvariouslinks'}</a></li>
	<li class="item"><a href="{$base_dir_ssl}contact-form.php" title="">{l s='Contact us' mod='blockvariouslinks'}</a></li>
	{foreach from=$cmslinks item=cmslink}
		<li class="item"><a href="{$cmslink.link}" title="{$cmslink.meta_title}">{$cmslink.meta_title}</a></li>
	{/foreach}
	<li class="last_item">{l s='Powered by' mod='blockvariouslinks'} <a href="http://www.prestashop.com">PrestaShop</a>&trade;</li>
</ul>
<!-- /MODULE Block various links -->
