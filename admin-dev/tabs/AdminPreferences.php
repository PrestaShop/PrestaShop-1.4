<?php

/**
  * General preferences tab for admin panel, AdminPreferences.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminPreferences extends AdminTab
{
	public function __construct()
	{

		global $cookie;

		$this->className = 'Configuration';
		$this->table = 'configuration';

		$tmz = Tools::getTimezones();
		$txs = Tax::getTaxes(intval($cookie->id_lang));
		$timezone = array();
		foreach ($tmz as $id => $name)
			$timezone[] = array('id' => $id, 'name' => $name);
		$taxes[] = array('id' => 0, 'name' => $this->l('None'));
		foreach ($txs as $tax)
			$taxes[] = array('id' => $tax['id_tax'], 'name' => $tax['name']);

		$this->_fieldsGeneral = array(
			'PS_BASE_URI' => array('title' => $this->l('PS directory:'), 'desc' => $this->l('Name of the PrestaShop directory on your Web server, bracketed by forward slashes (e.g., /shop/)'), 'validation' => 'isGenericName', 'type' => 'text', 'size' => 20, 'default' => '/'),
			'PS_SHOP_ENABLE' => array('title' => $this->l('Enable Shop:'), 'desc' => $this->l('Activate or deactivate your shop. Deactivate your shop while you perform maintenance on it'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool'),
			'PS_MAINTENANCE_IP' => array('title' => $this->l('Maintenance IP:'), 'desc' => $this->l('IP address allowed to access the Front Office while the shop is disabled (e.g., 42.24.4.2)'), 'validation' => 'isGenericName', 'type' => 'text', 'size' => 15, 'default' => ''),
			'PS_SSL_ENABLED' => array('title' => $this->l('Enable SSL'), 'desc' => $this->l('If your hosting provider allows SSL, you can activate SSL encryption (https://) for customer account identification and order processing'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool', 'default' => '0'),
			'PS_TOKEN_ENABLE' => array('title' => $this->l('Increase Front Office security'), 'desc' => $this->l('Enable or disable token on the Front Office in order to improve PrestaShop security'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool', 'default' => '0'),
			'PS_REWRITING_SETTINGS' => array('title' => $this->l('Friendly URL:'), 'desc' => $this->l('Enable only if your server allows URL rewriting (recommended)').'<p class="hint clear" style="display: block;">'.$this->l('If you turn on this feature, you must').' <a href="?tab=AdminGenerator&token='.Tools::getAdminToken('AdminGenerator'.intval(Tab::getIdFromClassName('AdminGenerator')).intval($cookie->id_employee)).'">'.$this->l('generate a .htaccess file').'</a></p><div class="clear"></div>', 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool'),
			'PS_HELPBOX' => array('title' => $this->l('Back Office help boxes:'), 'desc' => $this->l('Enable yellow help boxes which are displayed under form fields in the Back Office'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool'),
			'PS_CONDITIONS' => array('title' => $this->l('Terms of service:'), 'desc' => $this->l('Require customers to accept or decline terms of service before processing the order'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool'),
			'PS_GIFT_WRAPPING' => array('title' => $this->l('Offer gift-wrapping:'), 'desc' => $this->l('Suggest gift-wrapping to customer and possibility of leaving a message'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool'),
			'PS_GIFT_WRAPPING_PRICE' => array('title' => $this->l('Gift-wrapping price:'), 'desc' => $this->l('Set a price for gift-wrapping'), 'validation' => 'isPrice', 'cast' => 'floatval', 'type' => 'price'),
			'PS_GIFT_WRAPPING_TAX' => array('title' => $this->l('Gift-wrapping tax:'), 'desc' => $this->l('Set a tax for gift-wrapping'), 'validation' => 'isInt', 'cast' => 'intval', 'type' => 'select', 'list' => $taxes, 'identifier' => 'id'),
			'PS_RECYCLABLE_PACK' => array('title' => $this->l('Offer recycled packaging:'), 'desc' => $this->l('Suggest recycled packaging to customer'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool'),
			'PS_CART_FOLLOWING' => array('title' => $this->l('Cart re-display at login:'), 'desc' => $this->l('After customer logs in, recall and display contents of his/her last shopping cart'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool'));
			if (function_exists('date_default_timezone_set'))
				$this->_fieldsGeneral['PS_TIMEZONE'] = array('title' => $this->l('Timezone:'), 'validation' => 'isUnsignedId', 'cast' => 'intval', 'type' => 'select', 'list' => $timezone, 'identifier' => 'id');
			$this->_fieldsGeneral['PS_THEME_V11'] = array('title' => $this->l('v1.1 theme compatibility:'), 'desc' => $this->l('My shop use a PrestaShop v1.1 theme (SSL will generate warnings in customer browser)'), 'validation' => 'isBool', 'cast' => 'intval', 'type' => 'bool');

		parent::__construct();
	}

	public function display()
	{
		$this->_displayForm('general', $this->_fieldsGeneral, $this->l('General'), 'width2', 'tab-preferences');
	}

	public function postProcess()
	{
		global $currentIndex;

		if (isset($_POST['submitGeneral'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->_postConfig($this->_fieldsGeneral);
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (isset($_POST['submitShop'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->_postConfig($this->_fieldsShop);
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (isset($_POST['submitAppearance'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
				$this->_postConfig($this->_fieldsAppearance);
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
		elseif (isset($_POST['submitThemes'.$this->table]))
		{
		 	if ($this->tabAccess['edit'] === '1')
		 	{
				if ($val = Tools::getValue('PS_THEME'))
				{
				 	rewriteSettingsFile(NULL, $val, NULL);
				 	Tools::redirectAdmin($currentIndex.'&conf=6'.'&token='.$this->token);
				}
				else
					$this->_errors[] = Tools::displayError('you must choose a graphical theme');
			}
			else
				$this->_errors[] = Tools::displayError('You do not have permission to edit anything here.');
		}
	}

	/**
	  * Update settings in database and configuration files
	  *
	  * @params array $fields Fields settings
	  *
	  * @global string $currentIndex Current URL in order to keep current Tab
	  */
	protected function _postConfig($fields)
	{
		global $currentIndex;

		$languages = Language::getLanguages();

		/* Check required fields */
		foreach ($fields AS $field => $values)
			if (isset($values['required']) AND $values['required'])
				if ($values['type'] == 'textLang')
				{
					foreach ($languages as $language)
						if (($value = Tools::getValue($field.'_'.$language['id_lang'])) == false AND (string)$value != '0')
							$this->_errors[] = Tools::displayError('field').' <b>'.$values['title'].'</b> '.Tools::displayError('is required');
				}
				elseif (($value = Tools::getValue($field)) == false AND (string)$value != '0')
					$this->_errors[] = Tools::displayError('field').' <b>'.$values['title'].'</b> '.Tools::displayError('is required');

		/* Check fields validity */
		foreach ($fields AS $field => $values)
			if ($values['type'] == 'textLang')
			{
				foreach ($languages as $language)
					if (Tools::getValue($field.'_'.$language['id_lang']) AND isset($values['validation']))
						if (!Validate::$values['validation'](Tools::getValue($field.'_'.$language['id_lang'])))
							$this->_errors[] = Tools::displayError('field').' <b>'.$values['title'].'</b> '.Tools::displayError('is invalid');
			}
			elseif (Tools::getValue($field) AND isset($values['validation']))
				if (!Validate::$values['validation'](Tools::getValue($field)))
					$this->_errors[] = Tools::displayError('field').' <b>'.$values['title'].'</b> '.Tools::displayError('is invalid');

		/* Default value if null */
		foreach ($fields AS $field => $values)
			if (!Tools::getValue($field) AND isset($values['default']))
				$_POST[$field] = $values['default'];

		/* Save process */
		if (!sizeof($this->_errors))
		{
			if (isset($_POST['submitGeneral'.$this->table]))
			{
				rewriteSettingsFile(isset($_POST['PS_BASE_URI']) ? $_POST['PS_BASE_URI'] : '', NULL, NULL);
				unset($this->_fieldsGeneral['PS_BASE_URI']);
			}
			elseif (isset($_POST['submitAppearance'.$this->table]))
			{
				if (isset($_FILES['PS_LOGO']['tmp_name']) AND $_FILES['PS_LOGO']['tmp_name'])
				{
					if ($error = checkImage($_FILES['PS_LOGO'], 300000))
						$this->_errors[] = $error;
					if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($_FILES['PS_LOGO']['tmp_name'], $tmpName))
						return false;
					elseif (!@imageResize($tmpName, _PS_IMG_DIR_.'logo.jpg'))
						$this->_errors[] = 'an error occured during logo copy';
					unlink($tmpName);
				}
				$this->uploadIco('PS_FAVICON', _PS_IMG_DIR_.'favicon.ico');
			}

			/* Update settings in database */
			if (!sizeof($this->_errors))
			{
				foreach ($fields AS $field => $values)
				{
					unset($val);
					if ($values['type'] == 'textLang')
						foreach ($languages as $language)
							$val[$language['id_lang']] = isset($values['cast']) ? $values['cast'](Tools::getValue($field.'_'.$language['id_lang'])) : Tools::getValue($field.'_'.$language['id_lang']);
					else
						$val = isset($values['cast']) ? $values['cast'](Tools::getValue($field)) : Tools::getValue($field);
					Configuration::updateValue($field, $val);
				}
				Tools::redirectAdmin($currentIndex.'&conf=6'.'&token='.$this->token);
			}
		}
	}

	private function getVal($conf, $key)
	{
		return Tools::getValue($key, (isset($conf[$key]) ? $conf[$key] : ''));
	}

	private function getConf($fields, $languages)
	{
		foreach ($fields AS $key => $field)
		{
			if ($field['type'] == 'textLang')
				foreach ($languages as $language)
					$tab[$key.'_'.$language['id_lang']] = Tools::getValue($key.'_'.$language['id_lang'], Configuration::get($key, $language['id_lang']));
			else
				$tab[$key] =  Tools::getValue($key, Configuration::get($key));
		}
		$tab['PS_BASE_URI'] = __PS_BASE_URI__;
		$tab['PS_THEME'] = _THEME_NAME_;
		$tab['db_type'] = _DB_TYPE_;
		$tab['db_server'] = _DB_SERVER_;
		$tab['db_name'] = _DB_NAME_;
		$tab['db_prefix'] = _DB_PREFIX_;
		$tab['db_user'] = _DB_USER_;
		$tab['db_passwd'] = '';
		return $tab;
	}

	private function	getDivLang($fields)
	{
		$tab = array();
		foreach ($fields AS $key => $field)
			if ($field['type'] == 'textLang')
				$tab[] = $key;
		return implode('¤', $tab);
	}

	/**
	  * Display configuration form
	  *
	  * @params string $name Form name
	  * @params array $fields Fields settings
	  *
	 * @global string $currentIndex Current URL in order to keep current Tab
	  */
	protected function _displayForm($name, $fields, $tabname, $size, $icon)
	{
		global $currentIndex;

		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
		$confValues = $this->getConf($fields, $languages);
		$divLangName = $this->getDivLang($fields);
		$required = false;

		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
		</script>
		<form action="'.$currentIndex.'&submit'.$name.$this->table.'=1&token='.$this->token.'" method="post" class="'.$size.'" enctype="multipart/form-data">
			<fieldset><legend><img src="../img/admin/'.strval($icon).'.gif" />'.$tabname.'</legend>';
		foreach ($fields AS $key => $field)
		{
			/* Specific line for e-mails settings */
			if (get_class($this) == 'Adminemails' AND $key == 'PS_MAIL_SERVER')
				echo '<div id="smtp" style="display: '.((isset($confValues['PS_MAIL_METHOD']) AND $confValues['PS_MAIL_METHOD'] == 2) ? 'block' : 'none').';">';
			if (isset($field['required']) AND $field['required'])
				$required = true;
			$val = $this->getVal($confValues, $key);
			if (!in_array($field['type'], array('image', 'radio', 'container', 'container_end')) OR isset($field['show']))
				echo '<div style="clear: both; padding-top:15px;">'.($field['title'] ? '<label >'.$field['title'].'</label>' : '').'<div class="margin-form" style="padding-top:5px;">';

			/* Display the appropriate input type for each field */
			switch ($field['type'])
			{
				case 'select':
					echo '
					<select name="'.$key.'"'.(isset($field['js']) === true ? ' onchange="'.$field['js'].'"' : '').(isset($field['id']) === true ? ' id="'.$field['id'].'"' : '').'>';
					foreach ($field['list'] AS $k => $value)
						echo '<option value="'.(isset($value['cast']) ? $value['cast']($value[$field['identifier']]) : $value[$field['identifier']]).'"'.(($val == $value[$field['identifier']]) ? ' selected="selected"' : '').'>'.$value['name'].'</option>';
					echo '
					</select>';
					break;

				case 'bool':
					echo '<label class="t" for="'.$key.'_on"><img src="../img/admin/enabled.gif" alt="'.$this->l('Yes').'" title="'.$this->l('Yes').'" /></label>
					<input type="radio" name="'.$key.'" id="'.$key.'_on" value="1"'.($val ? ' checked="checked"' : '').(isset($field['js']['on']) ? $field['js']['on'] : '').' />
					<label class="t" for="'.$key.'_on"> '.$this->l('Yes').'</label>
					<label class="t" for="'.$key.'_off"><img src="../img/admin/disabled.gif" alt="'.$this->l('No').'" title="'.$this->l('No').'" style="margin-left: 10px;" /></label>
					<input type="radio" name="'.$key.'" id="'.$key.'_off" value="0" '.(!$val ? 'checked="checked"' : '').(isset($field['js']['off']) ? $field['js']['off'] : '').'/>
					<label class="t" for="'.$key.'_off"> '.$this->l('No').'</label>';
					break;

				case 'radio':
					foreach ($field['choices'] AS $cValue => $cKey)
						echo '<input type="radio" name="'.$key.'" id="'.$key.$cValue.'_on" value="'.intval($cValue).'"'.(($cValue == $val) ? ' checked="checked"' : '').(isset($field['js'][$cValue]) ? ' '.$field['js'][$cValue] : '').' /><label class="t" for="'.$key.$cValue.'_on"> '.$cKey.'</label><br />';
					echo '<br />';
					break;

				case 'image':
					echo '
					<table cellspacing="0" cellpadding="0">
						<tr>';
					$i = 0;
					foreach ($field['list'] AS $theme)
					{
						echo '<td class="center" style="width: 180px; padding:0px 20px 20px 0px;">
						<input type="radio" name="'.$key.'" id="'.$key.'_'.$theme['name'].'_on" style="vertical-align: text-bottom;" value="'.$theme['name'].'"'.
						(_THEME_NAME_ == $theme['name'] ? 'checked="checked"' : '').' />
						<label class="t" for="'.$key.'_'.$theme['name'].'_on"> '.Tools::strtolower($theme['name']).'</label>
						<br />
						<label class="t" for="'.$key.'_'.$theme['name'].'_on">
							<img src="../themes/'.$theme['name'].'/preview.jpg" alt="'.Tools::strtolower($theme['name']).'">
						</label>
						</td>';
						if (isset($field['max']) AND ($i+1) % $field['max'] == 0)
							echo '</tr><tr>';
						$i++;
					}
					echo '</tr>
					</table>';
					break;

				case 'price':
					$default_currency = new Currency(intval(Configuration::get("PS_CURRENCY_DEFAULT")));
					echo $default_currency->getSign('left').'<input type="'.$field['type'].'" size="'.(isset($field['size']) ? intval($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.$default_currency->getSign('right');
					break;

				case 'textLang':
					foreach ($languages as $language)
						echo '
						<div id="'.$key.'_'.$language['id_lang'].'" style="margin-bottom:8px; display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left; vertical-align: top;">
							<input type="text" name="'.$key.'_'.$language['id_lang'].'" value="'.htmlentities($this->getVal($confValues, $key.'_'.$language['id_lang']), ENT_COMPAT, 'UTF-8').'" />
						</div>';
					$this->displayFlags($languages, $defaultLanguage, $divLangName, $key);
					break;

				case 'file':
					if (isset($field['thumb']) AND $field['thumb'] AND $field['thumb']['pos'] == 'before')
						echo '<img src="'.$field['thumb']['file'].'" alt="'.$field['title'].'" title="'.$field['title'].'" /><br />';
					echo '<input type="file" name="'.$key.'" />';
					break;

				case 'textarea':
					echo '<textarea name='.$key.' cols="'.$field['cols'].'" rows="'.$field['rows'].'">'.htmlentities($val, ENT_COMPAT, 'UTF-8').'</textarea>';
					break;

				case 'container':
					echo '<div id="'.$key.'">';
				break;

				case 'container_end':
					echo (isset($field['content']) === true ? $field['content'] : '').'</div>';
				break;

				case 'text':
				default:
					echo '<input type="'.$field['type'].'"'.(isset($field['id']) === true ? ' id="'.$field['id'].'"' : '').' size="'.(isset($field['size']) ? intval($field['size']) : 5).'" name="'.$key.'" value="'.($field['type'] == 'password' ? '' : htmlentities($val, ENT_COMPAT, 'UTF-8')).'" />'.(isset($field['next']) ? '&nbsp;'.strval($field['next']) : '');
			}
			echo ((isset($field['required']) AND $field['required'] AND !in_array($field['type'], array('image', 'radio')))  ? ' <sup>*</sup>' : '');
			echo (isset($field['desc']) ? '<p style="clear:both">'.((isset($field['thumb']) AND $field['thumb'] AND $field['thumb']['pos'] == 'after') ? '<img src="'.$field['thumb']['file'].'" alt="'.$field['title'].'" title="'.$field['title'].'" style="float:left;" />' : '' ).$field['desc'].'</p>' : '');
			if (!in_array($field['type'], array('image', 'radio', 'container', 'container_end')) OR isset($field['show']))
				echo '</div></div>';
		}

		/* End of specific div for e-mails settings */
		if (get_class($this) == 'Adminemails')
			echo '<script type="text/javascript">if (getE(\'PS_MAIL_METHOD2_on\').checked) getE(\'smtp\').style.display = \'block\'; else getE(\'smtp\').style.display = \'none\';</script></div>';

		echo '	<div align="center" style="margin-top: 20px;">
					<input type="submit" value="'.$this->l('   Save   ', 'AdminPreferences').'" name="submit'.ucfirst($name).$this->table.'" class="button" />
				</div>
				'.($required ? '<div class="small"><sup>*</sup> '.$this->l('Required field', 'AdminPreferences').'</div>' : '').'
			</fieldset>
		</form>';
	}
}

?>