<?php
/*
* 2007-2010 PrestaShop 
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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

class WebserviceCore extends ObjectModel
{
 	/** @var string Key */
	public 		$key;
	
	/** @var boolean Webservice Account statuts */
	public 		$active = true;
	
 	protected 	$fieldsRequired = array('key');
 	protected 	$fieldsSize = array('key' => 32);
 	protected 	$fieldsValidate = array('key' => 'isPasswd', 'active' => 'isBool');
		
	protected 	$table = 'webservice_account';
	protected 	$identifier = 'id_webservice_account';

	public function getFields()
	{
		parent::validateFields();
		
		$fields['key'] = pSQL($this->key);
		$fields['active'] = (int)($this->active);
		
		return $fields;
	}
	
	/**
	* Get all webservice accounts back
	*
	* @return array Webservice Accounts
	*/
	static public function getWebserviceAccounts($active = false)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'webservice_account`
		'.($active ? 'WHERE active = 1' : '').'
		ORDER BY `key` ASC');
	}
	
	static public function getResources()
	{
		$resources = array(
			'zones' => array('description' => 'The Countries zones','class' => 'Zone'),
			'countries' => array('description' => 'The countries','class' => 'Country'),
			'states' => array('description' => 'The available states of countries','class' => 'State'),
			'manufacturers' => array('description' => 'The product manufacturers','class' => 'Manufacturer'),
			'suppliers' => array('description' => 'The product suppliers','class' => 'Supplier'),
			'groups' => array('description' => 'The customer\'s groups','class' => 'Group'),
			'customers' => array('description' => 'The e-shop\'s customers','class' => 'Customer'),
			'addresses' => array('description' => 'The Customer, Manufacturer and Customer addresses','class' => 'Address'),
			'categories' => array('description' => 'The product categories','class' => 'Category'),
			'product_options' => array('description' => 'The product options','class' => 'AttributeGroup'),
			'product_option_values' => array('description' => 'The product options value','class' => 'Attribute'),
			'product_features' => array('description' => 'The product features','class' => 'Feature'),
			'product_feature_values' => array('description' => 'The product feature values','class' => 'FeatureValue'),
			'combinations' => array('description' => 'The product combinations','class' => 'Combination'),
			'products' => array('description' => 'The products','class' => 'Product'),
			'orders' => array('description' => 'The Customers orders','class' => 'Order'),
			'order_histories' => array('description' => 'The Order histories','class' => 'OrderHistory'),
			'order_states' => array('description' => 'The Order states','class' => 'OrderState'),
			'tags' => array('description' => 'The Products tags','class' => 'Tag'),
			'carriers' => array('description' => 'The Carriers','class' => 'Carrier'),
			'images' => array('description' => 'The images', 'specific_management' => true),
			'image_types' => array('description' => 'The image types', 'class' => 'ImageType'),
		);
		ksort($resources);
		return $resources;
	}
	
	public function delete()
	{
		if (!parent::delete() OR $this->deleteAssociations() === false)
			return false;
		return true;
	}
	
	public function deleteAssociations()
	{
		if (
			Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'webservice_permission`
				WHERE `id_webservice_account` = '.(int)($this->id)) === false
			||
			Db::getInstance()->Execute('
				DELETE FROM `'._DB_PREFIX_.'webservice_permission`
				WHERE `id_webservice_account` = '.(int)($this->id)) === false
			)
			return false;
		return true;
	}
	
	static public function getPermissionForAccount($auth_key)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT p.*
			FROM `'._DB_PREFIX_.'webservice_permission` p
			LEFT JOIN `'._DB_PREFIX_.'webservice_account` a ON (a.id_webservice_account = p.id_webservice_account)
			WHERE a.key = \''.pSQL($auth_key).'\'
		');
		$permissions = array();
		if ($result)
			foreach ($result as $row)
				$permissions[$row['resource']][] = $row['method'];
		return $permissions;
	}
	
	static public function getAuthenticationKeys()
	{
		$result2 = array();
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
			SELECT `key`
			FROM `'._DB_PREFIX_.'webservice_account`
		');
		if ($result)
			foreach ($result as $row)
				$result2[] = $row['key'];
		return $result2;
	}
	
	static public function setPermissionForAccount($idAccount, $permissionsToSet)
	{
		$ok = true;
		$sql = 'DELETE FROM `'._DB_PREFIX_.'webservice_permission` WHERE `id_webservice_account` = '.(int)($idAccount);
		if(!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($sql))
			$ok = false;
		if (isset($permissionsToSet))
			{
				$permissions = array();
				$resources = Webservice::getResources();
				$methods = array('GET', 'PUT', 'POST', 'DELETE', 'HEAD');
				foreach ($permissionsToSet as $resourceName => $resource_methods)
					if (in_array($resourceName, array_keys($resources)))
						foreach ($resource_methods as $methodName => $value)
							if (in_array($methodName, $methods))
								$permissions[] = array($methodName, $resourceName);
				$account = new Webservice($idAccount);
				if ($account->deleteAssociations() && $permissions)
				{
					$sql = 'INSERT INTO `'._DB_PREFIX_.'webservice_permission` (`id_webservice_permission` ,`resource` ,`method` ,`id_webservice_account`) VALUES ';
					foreach ($permissions as $permission)
						$sql .= '(NULL , \''.pSQL($permission[1]).'\', \''.pSQL($permission[0]).'\', '.(int)($idAccount).'), ';
					$sql = rtrim($sql, ', ');
					if(!Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($sql))
						$ok = false;
				}
			}
		return $ok;
	}
}


