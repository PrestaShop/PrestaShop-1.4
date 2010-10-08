<?php

/**
  * Profiles tab for admin panel, AdminProfiles.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminProfiles extends AdminTab
{
	public function __construct()
	{
		global $cookie;
	
	 	$this->table = 'profile';
	 	$this->className = 'Profile';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		
		$this->fieldsDisplay = array(
		'id_profile' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 200));
		$this->identifier = 'id_profile';
		
		$list_profile = array();
		foreach(Profile::getProfiles($cookie->id_lang) as $profil)
			$list_profile[] = array('value' => $profil['id_profile'], 'name' => $profil['name']);
		
		parent::__construct();
	}
	
	public function postProcess()
	{
	 	if (isset($_GET['delete'.$this->table]) AND $_GET[$this->identifier] == intval(_PS_ADMIN_PROFILE_))
			$this->_errors[] = $this->l('For security reasons, you cannot delete the Administrator profile');
		else
			parent::postProcess();
	}
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex;
		parent::displayForm();
		
		$obj = $this->loadObject(true);
		
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
			'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/profiles.png" />'.$this->l('Profiles').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, 'name', 'name');
		echo '		<div class="clear"></div>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>
