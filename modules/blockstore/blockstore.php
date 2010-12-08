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
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockStore extends Module
{
    function __construct()
    {
        $this->name = 'blockstore';
        $this->tab = 'front_office_features';
        $this->version = 1.0;

        parent::__construct();

		$this->displayName = $this->l('Stores block');
        $this->description = $this->l('Displays a block with a link to the store locator');
    }

    function install()
    {
        return (parent::install() AND $this->registerHook('rightColumn') AND $this->registerHook('header'));
    }
   
    function hookLeftColumn($params)
    {
		return $this->hookRightColumn($params);
	}
	
	function hookRightColumn($params)
	{
		return $this->display(__FILE__, 'blockstore.tpl');
	}
	
	function hookHeader($params)
	{
		Tools::addCSS(_THEME_CSS_DIR_.'modules/'.$this->name.'/blockstore.css', 'all');
	}
}

