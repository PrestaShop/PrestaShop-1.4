<?php

/**
  * Search engine tab for admin panel, AdminSearchEngines.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class AdminSearchEngines extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'search_engine';
	 	$this->className = 'SearchEngine';
	 	$this->edit = true;
		$this->delete = true;
		
		$this->fieldsDisplay = array(
			'id_search_engine' => array('title' => $this->l('ID'), 'width' => 25),
			'server' => array('title' => $this->l('Server'), 'width' => 200),
			'getvar' => array('title' => $this->l('GET variable'), 'width' => 40));
			
		parent::__construct();
	}
	
	public function displayForm()
	{
		global $currentIndex;
		
		$obj = $this->loadObject(true);

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend>'.$this->l('Referrer').'</legend>
				<label>'.$this->l('Server').' </label>
				<div class="margin-form">
					<input type="text" size="20" name="server" value="'.htmlentities($this->getFieldValue($obj, 'server'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
				</div>
				<label>'.$this->l('$_GET variable').' </label>
				<div class="margin-form">
					<input type="text" size="40" name="getvar" value="'.htmlentities($this->getFieldValue($obj, 'getvar'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
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
