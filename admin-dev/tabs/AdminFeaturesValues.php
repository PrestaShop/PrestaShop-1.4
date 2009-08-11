<?php

/**
  * Features-values tab for admin panel, AdminFeaturesValues.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminFeaturesValues extends AdminTab
{
	public function __construct()
	{
	 	$this->table = 'feature_value';
	 	$this->className = 'FeatureValue';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;

		parent::__construct();
	}

	/**
	 * Display form
	 *
	 * @global string $currentIndex Current URL in order to keep current Tab
	 */
	public function displayForm($token = NULL)
	{
		global $currentIndex;

		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$obj = $this->loadObject(true);

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.($token ? $token : $this->token).'" method="post">
		'.($obj->id ? '<input type="hidden" name="id_feature_value" value="'.$obj->id.'" />' : '').'
			<fieldset class="width3"><legend><img src="../img/t/AdminFeatures.gif" />'.$this->l('Value').'</legend>
				<label>'.$this->l('Value:').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '
					<div id="value_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="value_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'value', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
						<span class="hint" name="help_box">'.$this->l('Invalid characters:').' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, 'value', 'value');
		echo '
					<div style="clear: both;"></div>
				</div>
				<label>'.$this->l('Feature:').' </label>
				<div class="margin-form">
					<select name="id_feature">';
		$features = Feature::getFeatures($defaultLanguage);
		foreach ($features AS $feature)
			echo '<option value="'.$feature['id_feature'].'"'.($this->getFieldValue($obj, 'id_feature') == $feature['id_feature']? ' selected="selected"' : '').'>'.$feature['name'].'</option>';
		echo '
					</select><sup> *</sup>
				</div>
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}

	/**
	 * Manage page processing
	 *
	 * @global string $currentIndex Current URL in order to keep current Tab
	 */
	public function postProcess($token = NULL)
	{
		global $currentIndex;

		if(Tools::getValue('submitDel'.$this->table))
		{
		 	if ($this->tabAccess['delete'] === '1')
		 	{
			 	if (isset($_POST[$this->table.$_POST['groupid'].'Box']))
			 	{
					$object = new $this->className();
					if ($object->deleteSelection($_POST[$this->table.$_POST['groupid'].'Box']))
						Tools::redirectAdmin($currentIndex.'&conf=2'.'&token='.($token ? $token : $this->token));
					$this->_errors[] = Tools::displayError('an error occurred while deleting selection');
				}
				else
					$this->_errors[] = Tools::displayError('you must select at least one element to delete');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to delete here.');
		}
		else
			parent::postProcess();
	}
}

?>