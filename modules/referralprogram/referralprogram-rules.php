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

require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

if (!Tools::getValue('width') AND !Tools::getValue('height'))
	require_once(dirname(__FILE__).'/../../header.php');

$xmlFile = _PS_MODULE_DIR_.'referralprogram/referralprogram.xml';

if (file_exists($xmlFile))
{
	if ($xml = @simplexml_load_file($xmlFile))
	{
		$smarty->assign(array(
			'xml' => $xml,
			'paragraph' => 'paragraph_'.$cookie->id_lang
		));
	}
}

echo Module::display(dirname(__FILE__).'/referralprogram', 'referralprogram-rules.tpl'); 

if (!Tools::getValue('width') AND !Tools::getValue('height'))
	require_once(dirname(__FILE__).'/../../footer.php');

