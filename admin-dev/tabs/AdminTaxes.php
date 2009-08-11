<?php

/**
  * Taxes tab for admin panel, AdminTaxes.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminTaxes extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'tax';
	 	$this->className = 'Tax';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		
		$this->fieldsDisplay = array(
		'id_tax' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name'), 'width' => 140),
		'rate' => array('title' => $this->l('Rate'), 'align' => 'center', 'suffix' => '%', 'width' => 50));
	
		$this->optionTitle = $this->l('Tax options');
		$this->_fieldsOptions = array(
		'PS_TAX' => array('title' => $this->l('Enable tax:'), 'desc' => $this->l('Select whether or not to include tax on purchases'), 'cast' => 'intval', 'type' => 'bool'),
		);
		
		parent::__construct();
	}
	
	public function displayForm()
	{
		global $currentIndex, $cookie;
		
		$obj = $this->loadObject(true);
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$tax_zones = $obj->getZones();
		$zones = Zone::getZones(true);
		$tax_states = $obj->getStates();
		$states = State::getStates(intval($cookie->id_lang));

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/admin/dollar.gif" />'.$this->l('Taxes').'</legend>
				<label>'.$this->l('Name:').' </label>
				<div class="margin-form">';
				foreach ($languages as $language)
					echo '
					<div id="name_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
				$this->displayFlags($languages, $defaultLanguage, 'name', 'name');
		echo '	<p style="clear: both;">'.$this->l('Tax name to display in cart and on invoice, e.g., VAT').'</p>
				</div>
				<label>'.$this->l('Rate:').' </label>
				<div class="margin-form">
					<input type="text" size="4" maxlength="5" name="rate" value="'.htmlentities($this->getFieldValue($obj, 'rate'), ENT_COMPAT, 'UTF-8').'" /> <sup>*</sup>
					<p style="clear: both;">'.$this->l('Format: XX.XX (e.g., 19.60)').'</p>
				</div>
				<label>'.$this->l('Zone:').'</label>
				<div class="margin-form">';
		foreach ($zones AS $zone)
			echo '<input type="checkbox" id="zone_'.$zone['id_zone'].'" name="zone_'.$zone['id_zone'].'" value="true" '.(Tools::getValue('zone_'.$zone['id_zone'], (is_array($tax_zones) AND in_array(array('id_tax' => $obj->id, 'id_zone' => $zone['id_zone']), $tax_zones))) ? ' checked="checked"' : '').'><label class="t" for="zone_'.$zone['id_zone'].'">&nbsp;<b>'.$zone['name'].'</b></label><br />';
		echo '	<p>'.$this->l('Zone in which this tax is activated').'</p>
				</div>
				<label>'.$this->l('States:').'</label>
				<div class="margin-form">';
		if ($states)
			foreach ($states AS $state)
				echo '<input type="checkbox" id="state_'.$state['id_state'].'" name="state_'.$state['id_state'].'" value="true" '.(Tools::getValue('state_'.$state['id_state'], (is_array($tax_states) AND in_array(array('id_tax' => $obj->id, 'id_state' => $state['id_state']), $tax_states))) ? ' checked="checked"' : '').'><label class="t" for="state_'.$state['id_state'].'">&nbsp;<b>'.$state['name'].'</b></label><br />';
		echo 	'<p>'.$this->l('State in which this tax is activated').'</p>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
	
	public function postProcess()
	{
		global $currentIndex;
		
		if(Tools::getValue('submitAdd'.$this->table))
		{			
		 	/* Checking fields validity */
			$this->validateRules();
			if (!sizeof($this->_errors))
			{
				$id = intval(Tools::getValue('id_'.$this->table));

				/* Object update */
				if (isset($id) AND !empty($id))
				{
					if ($this->tabAccess['edit'] === '1')
					{
						$object = new $this->className($id);
						if (Validate::isLoadedObject($object))
						{
							$this->copyFromPost($object, $this->table);
							$result = $object->update(false, false);
							
							if (!$result)
								$this->_errors[] = Tools::displayError('an error occurred while updating object').' <b>'.$this->table.'</b>';
							elseif ($this->postImage($object->id))
								{
									$this->changeZones($object->id);
									$this->changeStates($object->id);
									Tools::redirectAdmin($currentIndex.'&id_'.$this->table.'='.$object->id.'&conf=4'.'&token='.$this->token);
								}
						}
						else
							$this->_errors[] = Tools::displayError('an error occurred while updating object').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
				}
				
				/* Object creation */
				else
				{
					if ($this->tabAccess['add'] === '1')
					{
						$object = new $this->className();
						$this->copyFromPost($object, $this->table);
						if (!$object->add())
							$this->_errors[] = Tools::displayError('an error occurred while creating object').' <b>'.$this->table.'</b>';
						elseif (($_POST['id_'.$this->table] = $object->id /* voluntary */) AND $this->postImage($object->id) AND $this->_redirect)
						{
							$this->changeZones($object->id);
							$this->changeStates($object->id);
							Tools::redirectAdmin($currentIndex.'&id_'.$this->table.'='.$object->id.'&conf=3'.'&token='.$this->token);
						}
					}
					else
						$this->_errors[] = Tools::displayError('You do not have permission to add anything here.');
				}
			}
		}
		else
			parent::postProcess();
	}
	
	function changeZones($id)
	{
		$tax = new $this->className($id);
		if (!Validate::isLoadedObject($tax))
			die (Tools::displayError('object cannot be loaded'));
		$zones = Zone::getZones(true);
		foreach ($zones as $zone)
			if (sizeof($tax->getZone($zone['id_zone'])))
			{
				if (!isset($_POST['zone_'.$zone['id_zone']]) OR !$_POST['zone_'.$zone['id_zone']])
					$tax->deleteZone($zone['id_zone']);
			}
			elseif (isset($_POST['zone_'.$zone['id_zone']]) AND $_POST['zone_'.$zone['id_zone']])
				$tax->addZone($zone['id_zone']);
	}

	function changeStates($id)
	{
		global $cookie;

		$tax = new $this->className($id);
		if (!Validate::isLoadedObject($tax))
			die (Tools::displayError('object cannot be loaded'));
		$states = State::getStates(intval($cookie->id_lang), true);
		foreach ($states as $state)
			if ($tax->getState($state['id_state']))
			{
				if (!isset($_POST['state_'.$state['id_state']]) OR !$_POST['state_'.$state['id_state']])
					$tax->deleteState($state['id_state']);
			}
			elseif (isset($_POST['state_'.$state['id_state']]) AND $_POST['state_'.$state['id_state']])
				$tax->addState($state['id_state']);
	}
}

?>
