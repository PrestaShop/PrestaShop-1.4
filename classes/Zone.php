<?php
/*
* Copyright (C) 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

class ZoneCore extends ObjectModel
{
 	/** @var string Name */
	public 		$name;
	
	/** @var boolean Zone status */
	public 		$active = true;
	public 		$eu_zone = false; /* Obsolete; to remove */
	
 	protected 	$fieldsRequired = array('name');
 	protected 	$fieldsSize = array('name' => 64);
 	protected 	$fieldsValidate = array('name' => 'isGenericName', 'active' => 'isBool');
		
	protected 	$table = 'zone';
	protected 	$identifier = 'id_zone';
	
	protected	$webserviceParameters = array(
	);

	public function getFields()
	{
		parent::validateFields();
		
		$fields['name'] = pSQL($this->name);
		$fields['active'] = (int)($this->active);
		
		return $fields;
	}
	
	/**
	* Get all available geographical zones
	*
	* @return array Zones
	*/
	static public function getZones($active = false)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'zone`
		'.($active ? 'WHERE active = 1' : '').'
		ORDER BY `name` ASC');
	}

	/**
	* Get a zone ID from its default language name
	*
	* @return integer id_zone
	*/
	static public function getIdByName($name)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `id_zone`
			FROM `'._DB_PREFIX_.'zone`
			WHERE `name` = \''.pSQL($name).'\''
		);
	}
}


