<?php

/**
  * Categories class AdminAliases.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */
  
class AdminAliases extends AdminTab
{
	function __construct()
	{
	 	$this->table = 'alias';
	 	$this->className = 'Alias';
	 	$this->edit = true;
		$this->delete = true;
		
		$this->fieldsDisplay = array(
		'alias' => array('title' => $this->l('Aliases'), 'width' => 160),
		'search' => array('title' => $this->l('Search'), 'width' => 40),
		'active' => array('title' => $this->l('Status'), 'align' => 'center', 'active' => 'status', 'type' => 'bool', 'orderby' => false)
		);
		parent::__construct();
	}

	public function postProcess()
	{
		if (isset($_POST['submitAdd'.$this->table]))
		{
			$search = strval(Tools::getValue('search'));
			$string = strval(Tools::getValue('alias'));	
		 	$aliases = explode(',', $string);
			if (empty($search) OR empty($string))
				$this->_errors[] = $this->l('aliases and result are both required');
			if (!Validate::isValidSearch($search))
				$this->_errors[] = $search.' '.$this->l('is not a valid result');
		 	foreach ($aliases AS $alias)
				if (!Validate::isValidSearch($alias))
					$this->_errors[] = $alias.' '.$this->l('is not a valid alias');
			
			if (!sizeof($this->_errors))
			{
				Alias::deleteAliases($search);
			 	foreach ($aliases AS $alias)
			 	{
					$obj = new Alias(NULL, trim($alias), trim($search));
					$obj->save();
				}
			}
		}
		else
			parent::postProcess();
	}

	public function displayForm()
	{
		global $currentIndex;
		
		$obj = $this->loadObject(true);

		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/search.gif" />'.$this->l('Aliases').'</legend>
				<label>'.$this->l('Alias:').' </label>
				<div class="margin-form">
					<input type="text" size="40" name="alias" value="'.Tools::getValue('alias', htmlentities($obj->getAliases(), ENT_COMPAT, 'UTF-8')).'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Enter each alias separated by a comma (\',\')').' '.$this->l('(e.g., \'prestshop,preztashop,prestasohp\')').'<br />
					'.$this->l('Forbidden characters:').' <>;=#{}</p>
				</div>
				<label>'.$this->l('Result:').' </label>
				<div class="margin-form">
					<input type="text" size="15" name="search" value="'.htmlentities($this->getFieldValue($obj, 'search'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Search this word instead.').'</p>
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
