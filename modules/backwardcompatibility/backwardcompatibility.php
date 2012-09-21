<?php
/*
* 2007-2012 PrestaShop
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
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 7723 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

class BackwardCompatibility extends Module
{
	public function __construct()
	{
		$this->name = 'backwardcompatibility';
		$this->tab = 'compatibility_tools';
		$this->version = 0.1;
		$this->author = 'PrestaShop';
		$this->need_instance = 1;

		parent::__construct();

		$this->displayName = $this->l('Backward compatibility');
		$this->description = $this->l('Add compatibility tools.');

		/**
		 * Backward function compatibility
		 * Need to be called for each module in 1.4
		 */
		include_once(dirname(__FILE__).'/backward.php');
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('header') || !$this->registerHook('backOfficeHeader') || !$this->registerHook('processCarrier'))
			return false;

		/* Move module to top */
		if (_PS_VERSION_ < '1.5')
			$hooks = array((int)Hook::get('header'), (int)Hook::get('backOfficeHeader'), (int)Hook::get('processCarrier'));
		else
			$hooks = array((int)Hook::getIdByName('header'), (int)Hook::getIdByName('backOfficeHeader'), (int)Hook::getIdByName('processCarrier'));

		$module = Module::getInstanceByName($this->name);

		foreach ($hooks as $hook)
		{
			if (_PS_VERSION_ < '1.5')
				$moduleInfo = Hook::getModuleFromHook((int)$hook, $module->id);
			else
				$moduleInfo = Hook::getModulesFromHook((int)$hook, $module->id);

			if ((isset($moduleInfo['position']) && (int)$moduleInfo['position'] > 0) ||
				(isset($moduleInfo['m.position']) && (int)$moduleInfo['m.position'] > 0))
				$module->updatePosition((int)$hook, 0, 1);
		}
		return true;
	}
}


