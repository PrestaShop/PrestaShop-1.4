<?php

/**
  * Sub domains tab for admin panel, AdminSubDomains.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminSubDomains extends AdminTab
{
	public function __construct()
	{
		$this->table = 'subdomain';
		$this->className = 'SubDomain';
		$this->edit = true;
		$this->delete = true;

		$this->fieldsDisplay = array(
			'id_subdomain' => array('title' => $this->l('ID'), 'width' => 25),
			'name' => array('title' => $this->l('Subdomain'), 'width' => 200)
		);
		parent::__construct();
	}
	
	public function displayList()
	{
		echo '<fieldset>'.$this->l('Cookies are different on each subdomain of your Website. If you want to use the same cookie, please add here the subdomains used by your shop. The most common is "www".').'</fieldset>';
		return parent::displayList();
	}

	public function displayForm()
	{
		global $currentIndex;
		
		$obj = $this->loadObject(true);

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/subdomain.gif" /> '.$this->l('Subdomains').'</legend>
				<label>'.$this->l('Subdomain:').' </label>
				<div class="margin-form">
					<input type="text" size="15" name="name" value="'.htmlentities($this->getFieldValue($obj, 'name'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Additionnal subdomain').'</p>
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