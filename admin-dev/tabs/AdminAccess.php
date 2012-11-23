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
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminAccess extends AdminTab
{
	public function postProcess()
	{
		/* PrestaShop demo mode */
		if (_PS_MODE_DEMO_)
		{
			$this->_errors[] = Tools::displayError('This functionnality has been disabled.');
			return;
		}
		
		if (Tools::isSubmit('submitAddPermissions') && $this->tabAccess['edit'] == 1)
		{
			$id_profile = (int)Tools::getValue('id_profile');
			if ($id_profile && $id_profile != 1)
			{
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'access` WHERE `id_profile` = '.(int)$id_profile);
				
				$tabs = Tab::getTabs(_PS_LANG_DEFAULT_);
				if (count($tabs))
				{
					$insert = 'INSERT INTO '._DB_PREFIX_.'access (`id_tab`, `id_profile`, `view`, `add`, `edit`, `delete`) VALUES ';
					foreach ($tabs as $tab)
						$insert .= '('.(int)$tab['id_tab'].', '.(int)$id_profile.', '.(int)isset($_POST['perm_view_'.(int)$tab['id_tab']]).', '.
						(int)isset($_POST['perm_add_'.(int)$tab['id_tab']]).', '.(int)isset($_POST['perm_edit_'.(int)$tab['id_tab']]).', '.
						(int)isset($_POST['perm_delete_'.(int)$tab['id_tab']]).'),';
					$insert = rtrim($insert, ',');

					if (!Db::getInstance()->Execute($insert))
						$this->_errors[] = Tools::displayError('An error occurred while updating permissions');
					else
					{
						global $currentIndex;
						Tools::redirectAdmin($currentIndex.'&id_profile='.(int)$id_profile.'&conf=4'.'&token='.$this->token);
					}
				}
			}
		}
	}
	
	public function display()
	{
		$this->displayForm();
	}
	
	public function displayForm($isMainTab = true)
	{
		global $cookie, $currentIndex;
		parent::displayForm();
	 	
	 	$currentProfile = (int)Tools::getValue('id_profile');
		if (!$currentProfile)
			$currentProfile = 1;
	 	$tabs = Tab::getTabs((int)$cookie->id_lang);
		$profiles = Profile::getProfiles((int)$cookie->id_lang);
		$permissions = Profile::getProfileAccesses((int)$currentProfile);
		
		echo '
		<form action="'.$currentIndex.'&submitAddPermissions=1&token='.$this->token.'" id="form_permissions" method="post">
			<input type="hidden" name="id_profile" value="'.(int)$currentProfile.'" />
			<table class="table" cellspacing="0">
				<tr>
					<th '.($currentProfile == (int)_PS_ADMIN_PROFILE_ ? 'colspan="6"' : '').'>'.$this->l('Profile').'&nbsp;
						<select name="id_profile" onchange="window.location = \''.Tools::getHttpHost(true, true).$currentIndex.'&token='.$this->token.'&id_profile=\'+this.options[this.selectedIndex].value;">';
		if ($profiles)
			foreach ($profiles as $profile)
				echo '<option value="'.(int)$profile['id_profile'].'" '.((int)$profile['id_profile'] == $currentProfile ? 'selected="selected"' : '').'>'.Tools::safeOutput($profile['name']).'</option>';

		echo '
					</select>
				</th>';
		
		if ($currentProfile != (int)_PS_ADMIN_PROFILE_)
			echo '
				<th class="center">'.$this->l('View').'<br /><input type="checkbox" name="1" id="viewall" /></th>
				<th class="center">'.$this->l('Add').'<br /><input type="checkbox" name="1" id="addall" /></th>
				<th class="center">'.$this->l('Edit').'<br /><input type="checkbox" name="1" id="editall" /></th>
				<th class="center">'.$this->l('Delete').'<br /><input type="checkbox" name="1" id="deleteall" /></th>
				<th class="center">'.$this->l('All').'<br /><input type="checkbox" name="1" id="allall" /></th>
			</tr>';

		if (!count($tabs))
			echo '<tr><td colspan="5">'.$this->l('No tab').'</td></tr>';
		elseif ($currentProfile == (int)_PS_ADMIN_PROFILE_)
			echo '<tr><td colspan="5">'.$this->l('Administrator permissions cannot be modified.').'</td></tr>';
		else 
			foreach ($tabs as $tab)
				if (!$tab['id_parent'] || (int)$tab['id_parent'] == -1)
				{
					echo $this->printTabAccess((int)$currentProfile, $tab, isset($permissions[(int)$tab['id_tab']]) ? $permissions[(int)$tab['id_tab']] : 0, false);
					foreach ($tabs as $child)
						if ($child['id_parent'] === $tab['id_tab'])
							echo $this->printTabAccess($currentProfile, $child, isset($permissions[(int)$child['id_tab']]) ? $permissions[(int)$child['id_tab']] : 0, true);
				}
		echo '</table>
			<p><input type="submit" value="'.$this->l('   Save   ').'" name="submitAddPermissions" class="button" /></p>
		</form>
		<script type="text/javascript">managePermissions();</script>';
	}
	
	private function printTabAccess($currentProfile, $tab, $access, $is_child)
	{
		$output = '
		<tr>
			<td'.($is_child ? '' : ' class="bold"').'>'.($is_child ? ' &raquo; ' : '').Tools::safeOutput($tab['name']).'</td>';
		foreach (array('view', 'add', 'edit', 'delete') as $perm)
		{
			if ($this->tabAccess['edit'] == 1)
				$output .= '<td class="center"><input type="checkbox" class="'.$perm.'" name="perm_'.$perm.'_'.(int)$tab['id_tab'].'" '.((int)$access[$perm] == 1 ? 'checked="checked"' : '').' /></td>';
			else
				$output .= '<td class="center"><input type="checkbox" name="1" disabled="disabled" '.((int)$access[$perm] == 1 ? 'checked="checked"' : '').' /></td>';
		}
		$output .= '<td class="center"><input type="checkbox" class="all" /></td>
		</tr>';
		
		return $output;
	}
}