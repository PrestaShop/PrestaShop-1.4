<?php

/**
  * Modules positions tab for admin panel, AdminModulesPositions.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminModulesPositions extends AdminTab
{
	private $displayKey = 0;

	public function postProcess()
	{
		global	$currentIndex;

		// Getting key value for display
		if (Tools::getValue('show_modules') AND strval(Tools::getValue('show_modules')) != 'all')
			$this->displayKey = intval(Tools::getValue('show_modules'));

		// Change position in hook
		if (array_key_exists('changePosition', $_GET))
		{
			if ($this->tabAccess['edit'] === '1')
		 	{
				$id_module = intval(Tools::getValue('id_module'));
				$id_hook = intval(Tools::getValue('id_hook'));
				$module = Module::getInstanceById($id_module);
				if (Validate::isLoadedObject($module))
				{
					$module->updatePosition($id_hook, intval(Tools::getValue('direction')));
					Tools::redirectAdmin($currentIndex.($this->displayKey ? '&show_modules='.$this->displayKey : '').'&token='.$this->token);
				}
				else
					$this->_errors[] = Tools::displayError('module cannot be loaded');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}

		// Add new module in hook
		elseif (Tools::isSubmit('submitAddToHook'))
		{
		 	if ($this->tabAccess['add'] === '1')
			{
				// Getting vars...
				$id_module = intval(Tools::getValue('id_module'));
				$module = Module::getInstanceById($id_module);
				$id_hook = intval(Tools::getValue('id_hook'));
				$hook = new Hook($id_hook);
				$excepts = explode(',', str_replace(' ', '', Tools::getValue('exceptions')));

				// Checking vars...
				foreach ($excepts AS $except)
					if (!Validate::isFileName($except))
						$this->_errors[] = Tools::displayError('no valid value for field exceptions');
				if (!$id_module OR !Validate::isLoadedObject($module))
					$this->_errors[] = Tools::displayError('module cannot be loaded');
				elseif (!$id_hook OR !Validate::isLoadedObject($hook))
					$this->_errors[] = Tools::displayError('hook cannot be loaded');
				elseif (Hook::getModuleFromHook($id_hook, $id_module))
					$this->_errors[] = Tools::displayError('this module is already transplanted to this hook');

				// Adding vars...
				elseif (!$module->registerHook($hook->name))
					$this->_errors[] = Tools::displayError('an error occurred while transplanting module to hook');
				elseif (!$module->registerExceptions($id_hook, $excepts))
					$this->_errors[] = Tools::displayError('an error occurred while transplanting module to hook');
				else
					Tools::redirectAdmin($currentIndex.'&conf=16'.($this->displayKey ? '&show_modules='.$this->displayKey : '').'&token='.$this->token);
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}
		
		// Edit module from hook
		elseif (Tools::isSubmit('submitEditGraft'))
		{
		 	if ($this->tabAccess['add'] === '1')
			{
				// Getting vars...
				$id_module = intval(Tools::getValue('id_module'));
				$module = Module::getInstanceById($id_module);
				$id_hook = intval(Tools::getValue('id_hook'));
				$hook = new Hook($id_hook);
				$excepts = explode(',', str_replace(' ', '', Tools::getValue('exceptions')));

				// Checking vars...
				foreach ($excepts AS $except)
					if (!Validate::isFileName($except))
						$this->_errors[] = Tools::displayError('no valid value for field exceptions');
				if (!$id_module OR !Validate::isLoadedObject($module))
					$this->_errors[] = Tools::displayError('module cannot be loaded');
				elseif (!$id_hook OR !Validate::isLoadedObject($hook))
					$this->_errors[] = Tools::displayError('hook cannot be loaded');

				// Adding vars...
				if (!$module->editExceptions($id_hook, $excepts))
					$this->_errors[] = Tools::displayError('an error occurred while transplanting module to hook');
				else
					Tools::redirectAdmin($currentIndex.'&conf=16'.($this->displayKey ? '&show_modules='.$this->displayKey : '').'&token='.$this->token);
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
		}

		// Delete module from hook
		elseif (array_key_exists('deleteGraft', $_GET))
		{
		 	if ($this->tabAccess['delete'] === '1')
		 	{
				$id_module = intval(Tools::getValue('id_module'));
				$module = Module::getInstanceById($id_module);
				$id_hook = intval(Tools::getValue('id_hook'));
				$hook = new Hook($id_hook);
				if (!Validate::isLoadedObject($module))
					$this->_errors[] = Tools::displayError('module cannot be loaded');
				elseif (!$id_hook OR !Validate::isLoadedObject($hook))
					$this->_errors[] = Tools::displayError('hook cannot be loaded');
				else
				{
					if (!$module->unregisterHook($id_hook) OR !$module->unregisterExceptions($id_hook))
						$this->_errors[] = Tools::displayError('an error occurred while deleting module from hook');
					else
						Tools::redirectAdmin($currentIndex.'&conf=17'.($this->displayKey ? '&show_modules='.$this->displayKey : '').'&token='.$this->token);
				}
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
	}

	public function display()
	{
		if (array_key_exists('addToHook', $_GET) OR array_key_exists('editGraft', $_GET) OR (Tools::isSubmit('submitAddToHook') AND $this->_errors))
			$this->displayForm();
		else
			$this->displayList();
	}

	public function displayList()
	{
		global $currentIndex;
		echo '
		<script type="text/javascript" src="../js/jquery/jquery.tablednd_0_5.js"></script>
		<script type="text/javascript">
			var token = \''.$this->token.'\';
			var come_from = \'AdminModulesPositions\';
		</script>
		<script type="text/javascript" src="../js/admin-dnd.js"></script>
		';
		echo '<a href="'.$currentIndex.'&addToHook'.($this->displayKey ? '&show_modules='.$this->displayKey : '').'&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> <b>'.$this->l('Transplant a module').'</b></a><br /><br />';

		// Print select list
		echo '
		<form>
			'.$this->l('Show').' :
			<select id="show_modules" onChange="autoUrl(\'show_modules\', \''.$currentIndex.'&token='.$this->token.'&show_modules=\')">
				<option value="all">'.$this->l('All modules').'&nbsp;</option>
				<option>---------------</option>';
				$modules = Module::getModulesInstalled();

				foreach ($modules AS $module)
					if ($tmpInstance = Module::getInstanceById(intval($module['id_module'])))
						$cm[$tmpInstance->displayName] = $tmpInstance;
				ksort($cm);
				foreach ($cm AS $module)
					echo '
					<option value="'.intval($module->id).'" '.($this->displayKey == $module->id ? 'selected="selected" ' : '').'>'.$module->displayName.'</option>';
			echo '
			</select><br /><br />
			<input type="checkbox" id="hook_position" onclick="autoUrlNoList(\'hook_position\', \''.$currentIndex.'&token='.$this->token.'&show_modules='.intval(Tools::getValue('show_modules')).'&hook_position=\')" '.(Tools::getValue('hook_position') ? 'checked="checked" ' : '').' />&nbsp;'.$this->l('Display non-positionnable hook').'
		</form>';

		// Print hook list
		$irow = 0;
		$hooks = Hook::getHooks(!intval(Tools::getValue('hook_position')));
		foreach ($hooks AS $hook)
		{
			$modules = array();
			if (!$this->displayKey)
				$modules = Hook::getModulesFromHook($hook['id_hook']);
			elseif ($res = Hook::getModuleFromHook($hook['id_hook'], $this->displayKey))
					$modules[0] = $res;
			$nbModules = sizeof($modules);
			echo '
			<a name="'.$hook['name'].'"/>
			<table cellpadding="0" cellspacing="0" class="table width3 space'.($nbModules >= 2? ' tableDnD' : '' ).'" id="'.$hook['id_hook'].'">
			<tr class="nodrag nodrop"><th colspan="4">'.$hook['title'].' - <span style="color: red">'.$nbModules.'</span> '.(($nbModules > 1) ? $this->l('modules') : $this->l('module'));
			if (!empty($hook['description']))
				echo '&nbsp;<span style="font-size:0.8em; font-weight: normal">['.$hook['description'].']</span>';
			echo '</th></tr>';

			// Print modules list
			if ($nbModules)
			{
				$instances = array();
				foreach ($modules AS $module)
					if ($tmpInstance = Module::getInstanceById(intval($module['id_module'])))
						$instances[$tmpInstance->getPosition($hook['id_hook'])] = $tmpInstance;
				ksort($instances);
				foreach ($instances AS $position => $instance)
				{
					echo '
					<tr id="'.$hook['id_hook'].'_'.$instance->id.'"'.($irow++ % 2 ? ' class="alt_row"' : '').' style="height: 42px;">';
					if (!$this->displayKey)
					{
						echo '
						<td class="positions" width="40">'.intval($position).'</td>
						<td'.($nbModules >= 2? ' class="dragHandle"' : '').' id="td_'.$hook['id_hook'].'_'.$instance->id.'" width="40">
						<a'.($position == 1 ? ' style="display: none;"' : '' ).' href="'.$currentIndex.'&id_module='.$instance->id.'&id_hook='.$hook['id_hook'].'&direction=0&token='.$this->token.'&changePosition='.rand().'#'.$hook['name'].'"><img src="../img/admin/up.gif" alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a><br />
							<a '.($position == sizeof($instances) ? ' style="display: none;"' : '').'href="'.$currentIndex.'&id_module='.$instance->id.'&id_hook='.$hook['id_hook'].'&direction=1&token='.$this->token.'&changePosition='.rand().'#'.$hook['name'].'"><img src="../img/admin/down.gif" alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>
						</td>
						<td style="padding-left: 10px;">
						';
					}
					else
						echo '<td style="padding-left: 10px;" colspan="3">';
					echo '
					<img src="../modules/'.$instance->name.'/logo.gif" alt="'.stripslashes($instance->name).'" /> <strong>'.stripslashes($instance->displayName).'</strong>
						'.($instance->version ? ' v'.(intval($instance->version) == $instance->version? sprintf('%.1f', $instance->version) : floatval($instance->version)) : '').'<br />'.$instance->description.'
					</td>
						<td width="40">
							<a href="'.$currentIndex.'&id_module='.$instance->id.'&id_hook='.$hook['id_hook'].'&editGraft'.($this->displayKey ? '&show_modules='.$this->displayKey : '').'&token='.$this->token.'"><img src="../img/admin/edit.gif" border="0" alt="'.$this->l('Edit').'" title="'.$this->l('Edit').'" /></a>
							<a href="'.$currentIndex.'&id_module='.$instance->id.'&id_hook='.$hook['id_hook'].'&deleteGraft'.($this->displayKey ? '&show_modules='.$this->displayKey : '').'&token='.$this->token.'"><img src="../img/admin/delete.gif" border="0" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" /></a>
						</td>
					</tr>';
				}
			} else
				echo '<tr><td colspan="4">'.$this->l('No module for this hook').'</td></tr>';
			echo '</table>';
		}
	}

	public function displayForm()
	{
		global $currentIndex;

		$id_module = intval(Tools::getValue('id_module'));
		$id_hook = intval(Tools::getValue('id_hook'));
		if ($id_module AND $id_hook AND Tools::isSubmit('editGraft'))
		{
			$slModule = Module::getInstanceById($id_module);
			$exceptsList = $slModule->getExceptions($id_hook);
			$excepts = '';
			foreach ($exceptsList as $key => $except)
				$excepts .= ($key ? ',' : '').$except['file_name'];
		}
		$excepts = strval(Tools::getValue('exceptions', ((isset($slModule) AND Validate::isLoadedObject($slModule)) ? $excepts : '')));
		$modules = Module::getModulesInstalled(0);

		$instances = array();
		foreach ($modules AS $module)
			if ($tmpInstance = Module::getInstanceById($module['id_module']))
				$instances[$tmpInstance->displayName] = $tmpInstance;
		ksort($instances);
		$modules = $instances;
		$hooks = Hook::getHooks(0);
		echo '
		<form action="'.$currentIndex.'&token='.$this->token.'" method="post">';
		if ($this->displayKey)
			echo '<input type="hidden" name="show_modules" value="'.$this->displayKey.'" />';
		echo '<fieldset class="width3" style="width:700px;"><legend><img src="../img/t/AdminModulesPositions.gif" />'.$this->l('Transplant a module').'</legend>
				<label>'.$this->l('Module').' :</label>
				<div class="margin-form">
					<select name="id_module"'.(Tools::isSubmit('editGraft') ? ' disabled="disabled"' : '').'>';
					foreach ($modules AS $module)
						echo '
						<option value="'.$module->id.'" '.($id_module == $module->id ? 'selected="selected" ' : '').'>'.stripslashes($module->displayName).'</option>';
					echo '
					</select><sup> *</sup>
				</div>
				<label>'.$this->l('Hook into').' :</label>
				<div class="margin-form">
					<select name="id_hook"'.(Tools::isSubmit('editGraft') ? ' disabled="disabled"' : '').'>';
					foreach ($hooks AS $hook)
						echo '
						<option value="'.$hook['id_hook'].'" '.($id_hook == $hook['id_hook'] ? 'selected="selected" ' : '').'>'.$hook['title'].'</option>';
					echo '
					</select><sup> *</sup>
				</div>
				<label>'.$this->l('Exceptions').' :</label>
				<div class="margin-form">
					<input type="text" name="exceptions" size="40" '.(!empty($excepts) ? 'value="'.$excepts.'"' : '').'><br />Ex: identity.php, history.php, order.php, product.php<br /><br />
					'.$this->l('Please specify those files in which you do not want the module to be displayed').'.<br />
					'.$this->l('These files are located in your base directory').', '.$this->l('e.g., ').' <b>identity.php</b>.<br />
					'.$this->l('Please type each filename separated by a comma').'.
					<br /><br />
				</div>
				<div class="margin-form">
				';
				if (Tools::isSubmit('editGraft'))
				{
					echo '
					<input type="hidden" name="id_module" value="'.$id_module.'" />
					<input type="hidden" name="id_hook" value="'.$id_hook.'" />';
				}
				echo '
					<input type="submit" value="'.$this->l('Save').'" name="'.(Tools::isSubmit('editGraft') ? 'submitEditGraft' : 'submitAddToHook').'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>
