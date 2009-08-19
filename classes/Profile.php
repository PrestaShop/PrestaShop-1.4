<?php

/**
  * Profiles class, Profile.php
  * Profiles management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class Profile extends ObjectModel
{
 	/** @var string Name */
	public 		$name;
	
 	protected 	$fieldsRequiredLang = array('name');
 	protected 	$fieldsSizeLang = array('name' => 32);
 	protected 	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'profile';
	protected 	$identifier = 'id_profile';
		
	public function getFields()
	{
		return array('id_profile' => $this->id);
	}
	
	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name'));
	}
	
	/**
	* Get all available profiles
	*
	* @return array Profiles
	*/
	static public function getProfiles($id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT p.`id_profile`, `name`
		FROM `'._DB_PREFIX_.'profile` p
		LEFT JOIN `'._DB_PREFIX_.'profile_lang` pl ON (p.`id_profile` = pl.`id_profile` AND `id_lang` = '.intval($id_lang).')
		ORDER BY `name` ASC');
	}

	/**
	* Get the current profile name
	*
	* @return string Profile
	*/
	static public function getProfile($id_profile)
	{
		return Db::getInstance()->getRow('SELECT `name` FROM `'._DB_PREFIX_.'profile` WHERE `id_profile` = '.intval($id_profile));
	}

	
	public function add($autodate = true, $nullValues = false)
	{
	 	if (parent::add($autodate, true))
			return Db::getInstance()->Execute('INSERT INTO '._DB_PREFIX_.'access (SELECT '.intval($this->id).', id_tab, 0, 0, 0, 0 FROM '._DB_PREFIX_.'tab)');
		return false;
	}
	
	public function delete()
	{
	 	if (parent::delete())
	 	 	return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'access` WHERE `id_profile` = '.intval($this->id));
		return false;
	}

	public static function getProfileAccess($id_profile, $id_tab)
	{
	 	/* Accesses selection */
	 	return Db::getInstance()->getRow('
		SELECT `view`, `add`, `edit`, `delete`
		FROM `'._DB_PREFIX_.'access`
		WHERE `id_profile` = '.intval($id_profile).' AND `id_tab` = '.intval($id_tab));
	}

	public static function getProfileAccesses($id_profile)
	{
	 	/* Accesses selection */
	 	$accesses = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'access`
		WHERE `id_profile` = '.intval($id_profile));

	 	$result = array();
		foreach($accesses AS $access) {
		 	/* If it is the first time we meet this tab we prepare it */
		 	if (!isset($result[$access['id_tab']]))
		 		$result[$access['id_tab']] = array();
			$result[$access['id_tab']] = $access;
		}
		return $result;
	}
}

?>
