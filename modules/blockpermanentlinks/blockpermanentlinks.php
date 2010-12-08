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

class BlockPermanentLinks extends Module
{
	function __construct()
	{
		$this->name = 'blockpermanentlinks';
		$this->tab = 'front_office_features';
		$this->version = 0.1;

		parent::__construct();
		
		$this->displayName = $this->l('Permanent links block');
		$this->description = $this->l('Adds a block that displays permanent links such as sitemap, contact, etc.');
	}

	function install()
	{
			return (parent::install() AND $this->registerHook('top') AND $this->registerHook('header'));
	}

	/**
	* Returns module content for header
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookTop($params)
	{
		return $this->display(__FILE__, 'blockpermanentlinks-header.tpl');
	}

	/**
	* Returns module content for left column
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookLeftColumn($params)
	{
		return $this->display(__FILE__, 'blockpermanentlinks.tpl');
	}

	function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
	
	function hookHeader($params)
	{
		Tools::addCSS(($this->_path).'blockpermanentlinks.css', 'all');
	}
}


