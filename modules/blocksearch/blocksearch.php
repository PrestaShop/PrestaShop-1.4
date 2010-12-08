<?php
/*
* 2007-2010 PrestaShop 
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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockSearch extends Module
{
	function __construct()
	{
		$this->name = 'blocksearch';
		$this->tab = 'search_filter';
		$this->version = 1.0;

		parent::__construct();
		
		$this->displayName = $this->l('Quick Search block');
		$this->description = $this->l('Adds a block with a quick search field');
	}

	function install()
	{
		if (!parent::install() OR !$this->registerHook('top') OR !$this->registerHook('header'))
			return false;
		return true;
	}

	function hookLeftColumn($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', (int)(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch.tpl');
	}

	function hookRightColumn($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', (int)(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch.tpl');
	}

	function hookTop($params)
	{
		global $smarty;
		$smarty->assign('ENT_QUOTES', ENT_QUOTES);
		$smarty->assign('search_ssl', (int)(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off'));
		// check if library javascript load in header hook
		$this->_disabledSearchAjax();
		$smarty->assign('ajaxsearch', (int)(Configuration::get('PS_SEARCH_AJAX')));
		return $this->display(__FILE__, 'blocksearch-top.tpl');
	}

	function hookHeader($params)
	{
		global $smarty;
		$instantSearch = (int)(Configuration::get('PS_INSTANT_SEARCH'));
		$smarty->assign('instantsearch', $instantSearch);
		if (Configuration::get('PS_SEARCH_AJAX'))
		{
			Tools::addCSS(_PS_CSS_DIR_.'jquery.autocomplete.css');
			Tools::addJS(_PS_JS_DIR_.'jquery/jquery.autocomplete.js');
		}
		Tools::addCSS(_THEME_CSS_DIR_.'product_list.css');
		Tools::addCSS(($this->_path).'blocksearch.css', 'all');
	}
	
	private function _disabledSearchAjax()
	{
		if (!$this->isRegisteredInHook('header'))
			Configuration::updateValue('PS_SEARCH_AJAX', 0);
	}
}
