<?php

/**
  * Access management tab for admin panel, AdminAccess.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */
  
include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminAccess extends AdminTab
{
	public function postProcess()
	{
		if (Tools::isSubmit('submitAddaccess') AND $action = Tools::getValue('action') AND $id_tab = intval(Tools::getValue('id_tab')) AND $id_profile = intval(Tools::getValue('id_profile')))
			Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'access` SET `'.pSQL($action).'` = '.intval(Tools::getValue('perm')).' WHERE `id_tab` = '.intval($id_tab).' AND `id_profile` = '.intval($id_profile));
	}
	
	public function display()
	{
		$this->displayForm();
	}
	
	/**
	* Get the current profile id
	*
	* @return the $_GET['profile'] if valid, else 1 (the first profile id)
	*/
	function getCurrentProfileId()
	{
	 	return (isset($_GET['profile']) AND !empty($_GET['profile']) AND is_numeric($_GET['profile'])) ? intval($_GET['profile']) : 1;
	}
	
	function displayForm()
	{
		global $cookie, $currentIndex;
	 	
	 	$currentProfile = intval($this->getCurrentProfileId());
	 	$tabs = Tab::getTabs($cookie->id_lang);
		$profiles = Profile::getProfiles(intval($cookie->id_lang));
		$accesses = Profile::getProfileAccesses(intval($currentProfile));

		echo '
		<script type="text/javascript">
			setLang(Array(\''.$this->l('Profile updated').'\', \''.$this->l('Request failed!').'\', \''.$this->l('Updating in progress. Please wait.').'\', \''.$this->l('Server connection failed!').'\'));
		</script>
		<div id="ajax_confirmation"></div>
		<table class="table" cellspacing="0">
			<tr>
				<th>
					<select name="profile" onchange="redirect(\''.(Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').$currentIndex.'&token='.$this->token.'&profile=\'+this.options[this.selectedIndex].value)">';
		if ($profiles)
			foreach ($profiles AS $profile)
				echo '<option value="'.intval($profile['id_profile']).'" '.(intval($profile['id_profile']) == $currentProfile ? 'selected="selected"' : '').'>'.$profile['name'].'</option>';
		echo '
					</select>
				</th>
				<th>'.$this->l('View').'</th>
				<th>'.$this->l('Add').'</th>
				<th>'.$this->l('Edit').'</th>
				<th>'.$this->l('Delete').'</th>
			</tr>';
			
		if (!sizeof($tabs))
			echo '<tr><td colspan="5">'.$this->l('No tab').'</td></tr>';
		else
			foreach ($tabs AS $tab)
				if (!$tab['id_parent'] OR intval($tab['id_parent']) == -1)
				{
					$this->printTabAccess(intval($currentProfile), $tab, $accesses[$tab['id_tab']], false);
					foreach ($tabs AS $child)
						if ($child['id_parent'] === $tab['id_tab'])
					 		$this->printTabAccess($currentProfile, $child, $accesses[$child['id_tab']], true);
				}

		echo '</table>';
	}
	
	private function printTabAccess($currentProfile, $tab, $access, $is_child)
	{
		$perms = array('view', 'add', 'edit', 'delete');
		echo '<tr><td'.($is_child ? '' : ' class="bold"').'>'.($is_child ? ' &raquo; ' : '').$tab['name'].'</td>';
		foreach ($perms as $perm)
			echo '<td class="center"><input type="checkbox" name="1" onchange="ajax_power(this, \''.$perm.'\', '.intval($access['id_tab']).', '.intval($access['id_profile']).', \''.$this->token.'\')" '.(intval($access[$perm]) == 1 ? 'checked="checked"' : '').'/></td>';
		echo '</tr>';
	 
	}
}

?>