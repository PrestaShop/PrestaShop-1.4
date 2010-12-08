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

function __autoload($className)
{
	if (stristr($className, 'smarty'))
		return;
	if (!class_exists($className, false))
	{
		require_once(dirname(__FILE__).'/../classes/'.$className.'.php');
		if (file_exists(dirname(__FILE__).'/../override/classes/'.$className.'.php'))
			require_once(dirname(__FILE__).'/../override/classes/'.$className.'.php');
		else
		{
			$coreClass = new ReflectionClass($className.'Core');
			if ($coreClass->isAbstract())
				eval('abstract class '.$className.' extends '.$className.'Core {}');
			else
				eval('class '.$className.' extends '.$className.'Core {}');
		}
	}
}

/* Use Smarty 3 API calls */
if (!defined('_PS_FORCE_SMARTY_2_') OR !_PS_FORCE_SMARTY_2_) /* PHP version > 5.1.2 */
{
	spl_autoload_register('__autoload');
	define('SMARTY_SPL_AUTOLOAD', 0);
}

?>