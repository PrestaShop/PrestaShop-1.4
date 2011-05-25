<?php
/*
* 2007-2011 PrestaShop 
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

function __autoload($className)
{
	if (!class_exists($className, false))
	{
		if (function_exists('smartyAutoload') AND smartyAutoload($className)) 
			return true;
		
		$className = str_replace(chr(0), '', $className);
		$file_in_override = file_exists(dirname(__FILE__).'/../override/classes/'.$className.'.php');
		$file_in_classes = file_exists(dirname(__FILE__).'/../classes/'.$className.'.php');
		// It's a Core class and his name is the same as his declared name
		if (preg_match('/([a-z]+)Core$/', $className, $matches_class))
		{
			require_once(dirname(__FILE__).'/../classes/'.$matches_class[1].'.php');
		}
		else
		{
			if ($file_in_override && $file_in_classes)
			{
				require_once(dirname(__FILE__).'/../classes/'.str_replace(chr(0), '', $className).'.php');
				require_once(dirname(__FILE__).'/../override/classes/'.$className.'.php');
			}
			else if (!$file_in_override && $file_in_classes)
			{
				require_once(dirname(__FILE__).'/../classes/'.str_replace(chr(0), '', $className).'.php');
				$classInfos = new ReflectionClass($className.((interface_exists($className, false) or class_exists($className, false)) ? '' : 'Core'));
				if (!$classInfos->isInterface())
					eval(($classInfos->isAbstract() ? 'abstract ' : '').'class '.$className.' extends '.$className.'Core {}');
			}
			else if ($file_in_override && !$file_in_classes)
			{
				require_once(dirname(__FILE__).'/../override/classes/'.$className.'.php');
			}
		}
	}
}
