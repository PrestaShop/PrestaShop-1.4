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

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class LoyaltyStateModule extends ObjectModel
{
	public $name;
	public $id_order_state;

	protected $fieldsValidate = array('id_order_state' => 'isInt');
	protected $fieldsRequiredLang = array('name');
	protected $fieldsSizeLang = array('name' => 128);
	protected $fieldsValidateLang = array('name' => 'isGenericName');

	protected $table = 'loyalty_state';
	protected $identifier = 'id_loyalty_state';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_order_state'] = (int)($this->id_order_state);
		return $fields;
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

	static public function getDefaultId() { return 1; }
	static public function getValidationId() { return 2; }
	static public function getCancelId() { return 3; }
	static public function getConvertId() { return 4; }
	static public function getNoneAwardId() { return 5; }

	static public function insertDefaultData()
	{
		$loyaltyModule = new Loyalty();
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		
		$default = new LoyaltyStateModule(LoyaltyStateModule::getDefaultId());
		$default->name[$defaultLanguage] = $loyaltyModule->getL('Awaiting validation');
		$default->save();

		$validation = new LoyaltyStateModule(LoyaltyStateModule::getValidationId());
		$validation->id_order_state = _PS_OS_DELIVERED_;
		$validation->name[$defaultLanguage] = $loyaltyModule->getL('Available');
		$validation->save();

		$cancel = new LoyaltyStateModule(LoyaltyStateModule::getCancelId());
		$cancel->id_order_state = _PS_OS_CANCELED_;
		$cancel->name[$defaultLanguage] = $loyaltyModule->getL('Cancelled');
		$cancel->save();

		$convert = new LoyaltyStateModule(LoyaltyStateModule::getConvertId());
		$convert->name[$defaultLanguage] = $loyaltyModule->getL('Already converted');
		$convert->save();

		$noneAward = new LoyaltyStateModule(LoyaltyStateModule::getNoneAwardId());
		$noneAward->name[$defaultLanguage] = $loyaltyModule->getL('Unavailable on discounts');
		$noneAward->save();

		return true;
	}

}

