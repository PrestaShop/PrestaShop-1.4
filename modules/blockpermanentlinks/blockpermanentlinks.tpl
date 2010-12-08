{*
* Copyright (C) 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*}

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
