<!-- Block permanent links module -->
<div id="permanent_links">
	<!-- Sitemap -->
	<div class="sitemap">
		<a href="{$link->getPageLink('sitemap.php')}"><img src="{$img_dir}icon/sitemap.gif" alt="{l s='sitemap' mod='blockpermanentlinks'}" title="{l s='sitemap' mod='blockpermanentlinks'}" /></a>&nbsp;
		<a href="{$link->getPageLink('sitemap.php')}">{l s='sitemap' mod='blockpermanentlinks'}</a>
	</div>
	<!-- Contact -->
	<div class="contact">
		<a href="{$link->getPageLink('contact-form.php', true)}"><img src="{$img_dir}icon/contact.gif" alt="{l s='contact' mod='blockpermanentlinks'}" title="{l s='contact' mod='blockpermanentlinks'}" /></a>&nbsp;
		<a href="{$link->getPageLink('contact-form.php', true)}">{l s='contact' mod='blockpermanentlinks'}</a>
	</div>
	<!-- Bookmark -->
	<div class="add_bookmark">
		<script type="text/javascript">
		writeBookmarkLink('{$come_from|replace:"'":''|addslashes}', '{$shop_name|addslashes|addslashes}', '{l s='bookmark this page' mod='blockpermanentlinks'}', '{$img_dir}icon/star.gif');</script>&nbsp;
	</div>
</div>
<!-- /Block permanent links module -->
