<?php

/**
  * Tab class, Tabs.php
  * Tab management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class Tab extends ObjectModel
{
	/** @var string Displayed name*/
	public		$name;

	/** @var string Class and file name*/
	public		$class_name;
	
	public		$module;

	/** @var integer parent ID */
	public		$id_parent;

	/** @var integer position */
	public		$position;

	protected	$fieldsRequired = array('class_name', 'position');
	protected	$fieldsSize = array('class_name' => 64, 'module' => 64);
	protected	$fieldsValidate = array('id_parent' => 'isInt', 'position' => 'isUnsignedInt', 'module' => 'isTabName');

	protected	$fieldsRequiredLang = array('name');
	protected	$fieldsSizeLang = array('name' => 32);
	protected	$fieldsValidateLang = array('name' => 'isGenericName');

	protected 	$table = 'tab';
	protected 	$identifier = 'id_tab';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_parent'] = intval($this->id_parent);
		$fields['class_name'] = pSQL($this->class_name);
		$fields['module'] = pSQL($this->module);
		$fields['position'] = intval($this->position);
		return $fields;
	}

	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name'));
	}

	public function add($autodate = true, $nullValues = false)
	{
		$this->position = self::getNbTabs($this->id_parent) + 1;
		if (parent::add($autodate, $nullValues))
			return self::initAccess($this->id);
		return false;
	}
	
	static public function initAccess($id_tab)
	{
	 	/* Cookie's loading */
	 	global $cookie;
	 	if (!is_object($cookie) OR !$cookie->profile)
	 		return false;
	 	/* Profile selection */
	 	$profiles = Db::getInstance()->ExecuteS('SELECT `id_profile` FROM '._DB_PREFIX_.'profile');
	 	if (!$profiles OR empty($profiles))
	 		return false;
	 	/* Query definition */
	 	$query = 'INSERT INTO `'._DB_PREFIX_.'access` VALUES ';
	 	foreach ($profiles AS $profile)
	 	{
	 	 	$rights = ((intval($profile['id_profile']) == 1 OR intval($profile['id_profile']) == $cookie->profile) ? 1 : 0);
	 	 	$query .= ($profile === $profiles[0] ? '' : ', ').'('.$profile['id_profile'].', '.$id_tab.', '.$rights.', '.$rights.', '.$rights.', '.$rights.')';
	 	}
	 	return Db::getInstance()->Execute($query);
	}

	public function delete()
	{
	 	if (Db::getInstance()->Execute('DELETE FROM '._DB_PREFIX_.'access WHERE `id_tab` = '.intval($this->id)) AND parent::delete())
			return $this->cleanPositions($this->id_parent);
		return false;
	}

	/**
	 * Get tab id
	 *
	 * @return integer tab id
	 */
	static public function getCurrentTabId()
	{
	 	if ($result = Db::getInstance()->getRow('SELECT `id_tab` FROM `'._DB_PREFIX_.'tab` WHERE LOWER(class_name)=\''.pSQL(Tools::strtolower(Tools::getValue('tab'))).'\''))
		 	return $result['id_tab'];
 		return -1;
	}

	/**
	 * Get tab parent id
	 *
	 * @return integer tab parent id
	 */
	static public function getCurrentParentId()
	{
	 	if ($result = Db::getInstance()->getRow('SELECT `id_parent` FROM `'._DB_PREFIX_.'tab` WHERE LOWER(class_name)=\''.pSQL(Tools::strtolower(Tools::getValue('tab'))).'\''))
		 	return $result['id_parent'];
 		return -1;
	}

	/**
	 * Get tabs
	 *
	 * @return array tabs
	 */
	static public function getTabs($id_lang = false, $id_parent = NULL)
	{
		/* Tabs selection */
		$sql = ('
		SELECT *
		FROM `'._DB_PREFIX_.'tab` t
		'.($id_lang ? 'LEFT JOIN `'._DB_PREFIX_.'tab_lang` tl ON (t.`id_tab` = tl.`id_tab` AND tl.`id_lang` = '.intval($id_lang).')' : '').
		($id_parent !== NULL ? ('WHERE t.`id_parent` = '.intval($id_parent)) : '').'
		ORDER BY t.`position` ASC');
		return Db::getInstance()->ExecuteS($sql);
	}

	/**
	 * Get tab
	 *
	 * @return array tab
	 */
	static public function getTab($id_lang, $id_tab)
	{
		/* Tabs selection */
		return Db::getInstance()->getRow('
		SELECT *
		FROM `'._DB_PREFIX_.'tab` t
		LEFT JOIN `'._DB_PREFIX_.'tab_lang` tl ON (t.`id_tab` = tl.`id_tab` AND tl.`id_lang` = '.intval($id_lang).')
		WHERE t.`id_tab` = '.intval($id_tab));
	}

	/**
	 * Get tab id from name
	 *
	 * @param string class_name
	 * @return int id_tab
	 */
	static public function getIdFromClassName($class_name)
	{
		$sql = 'SELECT id_tab AS id FROM `'._DB_PREFIX_.'tab` t WHERE t.`class_name` = \''.pSQL($class_name).'\'';
		$result = Db::getInstance()->getRow($sql);
		return intval($result['id']);
	}

	static public function getClassNameFromID($id_tab)
	{
		$sql = 'SELECT class_name AS name FROM `'._DB_PREFIX_.'tab` t WHERE t.`id_tab` = \''.intval($id_tab).'\'';
		$result = Db::getInstance()->getRow($sql);
		return strval($result['name']);
	}

	static public function getNbTabs($id_parent = NULL)
	{
		/* Tabs selection */
		$result = Db::getInstance()->getRow('
		SELECT COUNT(id_tab) AS nb
		FROM `'._DB_PREFIX_.'tab` t
		'.($id_parent !== NULL ? 'WHERE t.`id_parent` = '.intval($id_parent) : ''));
		return intval($result['nb']);
	}

	public function move($direction)
	{
		$nbTabs = self::getNbTabs($this->id_parent);
		if ($direction != 'l' AND $direction != 'r')
			return false;
		if ($nbTabs <= 1)
			return false;
		if ($direction == 'l' AND $this->position <= 1)
			return false;
		if ($direction == 'r' AND $this->position >= $nbTabs)
			return false;

		$newPosition = ($direction == 'l') ? $this->position - 1 : $this->position + 1;
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'tab` t SET position = '.intval($this->position).' WHERE id_parent = '.intval($this->id_parent).' AND position = '.intval($newPosition));
		$this->position = $newPosition;
		return $this->update();
	}

	public function cleanPositions($id_parent)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_tab`
		FROM `'._DB_PREFIX_.'tab`
		WHERE `id_parent` = '.intval($id_parent).'
		ORDER BY `position`');
		$sizeof = sizeof($result);
		for ($i = 0; $i < $sizeof; ++$i)
			Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'tab`
			SET `position` = '.($i + 1).'
			WHERE `id_tab` = '.intval($result[$i]['id_tab']));
		return true;
	}
}

?>