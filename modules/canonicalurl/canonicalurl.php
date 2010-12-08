<?php
/*
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
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class canonicalUrl extends Module
{
	public function __construct()
	{
		$this->name = 'canonicalurl';
		$this->tab = 'seo';
		$this->version = 1.3;

		parent::__construct();

		$this->displayName = $this->l('Canonical URL');
		$this->description = $this->l('Improve SEO by avoiding the "duplicate content" status for your Website.');
		
		if ($this->id AND strlen(Configuration::get('CANONICAL_URL')) == 0)
			$this->warning = $this->l('You must set the canonical URL to avoid "duplicate content" status for your Website.');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('header') OR !Configuration::updateValue('CANONICAL_URL', ''))
			return false;
		return true;
	}

	public function uninstall()
	{
		/* Delete configuration */
		Configuration::deleteByName('CANONICAL_URL');
		return parent::uninstall();
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitCanonicalUrl'))
		{
			$canonicalUrl = pSQL(Tools::getValue('canonicalUrl'));
			if (strlen($canonicalUrl) == 0)
				$output .= '<div class="alert error">'.$this->l('Canonical URL : invalid URL.').'</div>';
			else
			{
				Configuration::updateValue('CANONICAL_URL', $canonicalUrl);
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
			}
		}
		return $output.$this->displayForm();
	}

	public function displayForm()
	{
		return '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Canonical URL').'</label>
				<div class="margin-form">
					http(s)://<input type="text" name="canonicalUrl" value="'.Configuration::get('CANONICAL_URL').'" />/some/directories/a_prestashop_webpage.php
					<p class="clear">'.$this->l('Choose the primary domain name for your referencing (e.g., www.myshop.com or myshop.com or mywebsite.com). Note: Do not include the last slash ("/"), the "/index.php" suffix, or the "http(s)://" prefix.').'</p>
				</div>
				<center><input type="submit" name="submitCanonicalUrl" value="'.$this->l('Save').'" class="button" /></center>
			</fieldset>
		</form>';
	}

	public function hookHeader($params)
	{
		global $smarty, $protocol_link;
		
		$canonicalUrl = Configuration::get('CANONICAL_URL');
		$ps_request = str_replace(__PS_BASE_URI__, '', $_SERVER['REQUEST_URI']);
		if (strlen(Configuration::get('CANONICAL_URL')) > 0)
			$smarty->assign('canonical_url', $protocol_link.$canonicalUrl.Tools::htmlentitiesUTF8(rawurldecode($_SERVER['REQUEST_URI'])));
		return $this->display(__FILE__, 'canonicalurl.tpl');
	}
}


