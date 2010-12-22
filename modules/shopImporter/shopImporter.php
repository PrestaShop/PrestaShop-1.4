<?php

class shopImporter extends ImportModule
{		
	
	public $match_id = array('id_language' => 'id_lang', 'id_category_default' => 'id_category');
	private $supportedImports = array();
	
		
	public function __construct()
	{
		global $cookie;
		
		$this->name = 'shopImporter';
		$this->tab = 'migration_tools';
		$this->version = '1.0';

		parent::__construct ();

		$this->displayName = $this->l('Shop Importer');
		$this->description = $this->l('This module allows to import from another system to prestashop');
		
		$this->supportedImports = array(
									'language' => array('methodName' => 'getLangagues',
														'name' => $this->l('Language'),
														'label' => $this->l('Import Languages'),
														'identifier' => 'id_lang',
														'alterTable' => array('id_lang' => 'int(10)'),
														'info' => $this->l('New languages will automatically add translations!'),
														'delete' => true,
														'defaultId' => 'PS_LANG_DEFAULT'
														),
									'currency' => array('methodName' => 'getCurrencies',
														'name' => $this->l('Currency'),
														'label' => $this->l('Import Currencies'),
														'identifier' => 'id_currency',
														'alterTable' => array('id_currency' => 'int(10)'),
														'delete' => true,
														'defaultId' => 'PS_CURRENCY_DEFAULT'
														),
									'zone' => array('methodName' => 'getZones',
														'name' => $this->l('Zone'),
														'label' => $this->l('Import Zones'),
														'identifier' => 'id_zone',
														'alterTable' => array('id_zone' => 'int(10)'),
														'delete' => true
														),
									'country' => array('methodName' => 'getCountries',
														'name' => $this->l('Country'),
														'label' => $this->l('Import Countries'),
														'identifier' => 'id_country',
														'foreign_key' => array('id_zone', 'id_currency'),
														'alterTable' => array('id_country' => 'int(10)'),
														'delete' => true,
														'defaultId' => 'PS_COUNTRY_DEFAULT'
														),
									'state' => array('methodName' => 'getStates',
														'name' => $this->l('State'),
														'label' => $this->l('Import States'),
														'identifier' => 'id_state',
														'foreign_key' => array('id_zone', 'id_country'),
														'alterTable' => array('id_state' => 'int(10)'),
														'delete' => true
														),
									'group' => array('methodName' => 'getGroups',
														'name' => $this->l('Group'),
														'label' => $this->l('Import Groups'),
														'identifier' => 'id_group',
														'alterTable' => array('id_group' => 'int(10)'),
														'delete' => true,
														),
									'customer' => array('methodName' => 'getCustomers',
														'name' => $this->l('Customer'),
														'label' => $this->l('Import Customers'),
														'identifier' => 'id_customer',
														'alterTable' => array('id_customer' => 'int(10)', 'passwd' => 'varchar(100)'),
														'delete' => true
														),
									'address' => array('methodName' => 'getAddresses',
														'name' => $this->l('Address'),
														'label' => $this->l('Import Addresses'),
														'identifier' => 'id_address',
														'foreign_key' => array('id_country', 'id_state', 'id_customer'),
														'alterTable' => array('id_address' => 'int(10)'),
														'delete' => true
														),
									'order' => array('methodName' => 'getOrders',
													 'name' => $this->l('Order'),
													 'label' => $this->l('Import Orders'),
													 'identifier' => 'id_order',
													 'alterTable' => array('id_order' => 'int(10)'),
													 'delete' => true
													 ),
									'manufacturer' => array('methodName' => 'getManufacturers',
														'name' => $this->l('Manufacturer'),
														'label' => $this->l('Import Manufacturers'),
														'identifier' => 'id_manufacturer',
														'delete' => true,
														'alterTable' => array('id_manufacturer' => 'int(10)'),
														),
									'supplier' => array('methodName' => 'getSuppliers',
														'name' => $this->l('Supplier'),
														'label' => $this->l('Import Suppliers'),
														'identifier' => 'id_supplier',
														'delete' => true,
														'alterTable' => array('id_supplier' => 'int(10)'),
														),
									'category' => array('methodName' => 'getCategories',
														'name' => $this->l('Category'),
														'label' => $this->l('Import Categories'),
														'identifier' => 'id_category',
														'alterTable' => array('id_category' => 'int(10)'),
														'delete' => true
														),
									'product' => array('methodName' => 'getProducts',
														'name' => $this->l('Product'),
														'label' => $this->l('Import Products'),
														'identifier' => 'id_product',
														'alterTable' => array('id_product' => 'int(10)'),
														'foreign_key' => array('id_category'),
														'delete' => true,
														'association' => array('table' => 'category_product',
																			   'foreign_key' => array('id_category', 'id_product')
																			   )
														),
									'productCombination' => array('methodName' => 'getProductsCombination',
														'name' => $this->l('Products Combinations'),
														'label' => $this->l('Import Products Combinations'),
														'identifier' => 'id_attribute',
														'alterTable' => array('id_attribute' => 'int(10)'),
														'delete' => true
														),
									);
	}
	
	public function install()
	{
		return parent::install(); 					
	}
	
	public function uninstall()
	{
		return parent::uninstall();
	}
	
	public function getContent()
	{
			global $cookie;
		$exportModules = parent::getImportModulesOnDisk();
		$html = '<script type="text/javascript" src="../modules/shopImporter/shopImporter.js"></script>
				 <script type="text/javascript">
					var conf = new Array();';
		$i = 0;
		foreach($this->supportedImports as $import)
		{
			$html .= 'conf['.$i.'] = new Array(\''.$import['methodName'].'\', \''.$import['label'].'\', \''.$import['name'].'\');';
			$i++;	
		}			

		$html .= '	var notExist = "'.$this->l('is not available in this module').'";
					var databaseOk = "'.$this->l('Connection to the database OK').'";
					var showErrors = "'.$this->l('Show errors').'";
					var testImport = "'.$this->l('Test import process').'";
					var import = "'.$this->l('Import').'";
					var importHasErrors = "'.$this->l('Please fixe errors before to continue').'"
					var importFinish = "'.$this->l('The import is completed').'"
					var truncateTable = "'.$this->l('Remove datas').'"
				</script>
				<style>
					.margin-form{padding: 0px 0px 1em 120px;width:300px;}
					label{width: 170px;}
					.import{background-color: #CCCCCC;border: 1px solid gray;margin: 0px 0px 10px;padding: 10px 15px;line-height: 20px;}
				</style>
				<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" />'.$this->l('Import from another system').'</legend>
				<div class="warn" ><img src="../img/admin/warn2.png">
					'.$this->l('Before starting the import thank you to backup your database. ').'
						<a href="index.php?tab=AdminBackup&token='.Tools::getAdminToken('AdminBackup'.intval(Tab::getIdFromClassName('AdminBackup')).intval($cookie->id_employee)).'"">'.$this->l(' Click here to backup').'</a>
				</div>
				<div class="warn" ><img src="../img/admin/warn2.png">
					This module is in beta version
				</div>
				<br>
				<div style="float:right;width:450px" id="steps"></div>
				<label>'.$this->l('Choose your import').' : </label>
				<div class="margin-form">
					<select name="import_module_name" id="import_module_name">
						<option value="0">---</option>';
		
		foreach($exportModules as $key => $module)
			($module->name != $this->name ? $html .= '<option value="'.$module->name.'">'.$module->displayName.'</option>' : '' );
	
		$html .= '</select><input type="submit" class="button" id="choose_module_name" value="'.$this->l('Choose').'">
				</div>
				<div id="db_config" style="display:none;width:420px;padding-right:20px">
					<label>'.$this->l('Server').' : </label>
						<div class="margin-form">
							<input type="text" name="server" id="server" value="localhost">
						</div>
					<label>'.$this->l('User').' : </label>
						<div class="margin-form">
							<input type="text" name="user" id="user" value="root">
						</div>
					<label>'.$this->l('Password').' : </label>
						<div class="margin-form">
							<input type="password" name="password" id="password" value="MA1ej2mt3">
						</div>
					<label>'.$this->l('Database').' : </label>
						<div class="margin-form" style="">
							<input type="text" name="database" id="database" value="osc">
						</div>
					<label>'.$this->l('Database prefix').' : </label>
						<div class="margin-form" style="">
							<input type="text" name="prefix" id="prefix" value="">
						</div>
					<div class="margin-form">
						<input type="submit" name="displayOptions" id="displayOptions" class="button" value="'.$this->l('Next Step').'">
					</div>
					<hr>
					<div style="display:none" id="importOptions">
					<h2>'.$this->l('Import Options').'</h2>';
					foreach($this->supportedImports as $key => $import)
					{
						$html .= '<label>'.$import['name'].' : </label>
									<div class="margin-form">
										<label class="t"><img src="../img/admin/enabled.gif" alt="Oui" title="Oui"></label>
										<input type="radio" id="'.$import['identifier'].'_on'.'" name="'.$import['methodName'].'" class="'.$key.'" value="1" checked="checked">
										<label class="t" for="'.$import['identifier'].'_on'.'"> Oui</label>&nbsp;&nbsp;
										<label class="t"><img src="../img/admin/disabled.gif" alt="Non" title="Non" style="margin-left: 10px;"></label>
										<input type="radio" id="'.$import['identifier'].'_off'.'" name="'.$import['methodName'].'" class="'.$key.'" value="0">
										<label class="t" for="'.$import['identifier'].'_off'.'"> Non</label>&nbsp;&nbsp;
										'.(array_key_exists('delete', $import) ? '
										<label class="t"><img src="../img/admin/delete.gif" alt="Delete" title="Delete"></label>
										<input type="checkbox" class="truncateTable" id="'.$key.'" name="delete_'.$import['name'].'">
										<label class="t" for="'.$key.'">'.$this->l('Delete').'</label>' : '' ).
										(array_key_exists('info', $import) ? '<p>'.$import['info'].'</p>' : '').'
									</div>';
					} 
					$html .= '<hr>
					<h2>'.$this->l('Advanced Options').'</h2>
					<div class="warn" id="warnSkip" style="display:none"><img src="../img/admin/warn2.png">
					'.$this->l('This mode is dangerous').'
					</div>
					<label>'.$this->l('If errors happen').' : </label>
							<div class="margin-form">
								<label class="t"><img src="'.$this->_path.'img/stop.png"></label>
								<input type="radio" name="hasErrors" id="hasErrors" value="0" checked="checked">
								<label class="t">'.$this->l('Stop').'</label>
								<label class="t"><img src="'.$this->_path.'img/skip.png" style="margin-left: 10px;"></label>
								<input type="radio" name="hasErrors" id="hasErrors" value="1">
								<label class="t">'.$this->l('Skip').'</label>
								<label class="t"><img src="'.$this->_path.'img/force.gif" style="margin-left: 10px;"></label>
								<input type="radio" name="hasErrors" id="hasErrors" value="2">
								<label class="t">'.$this->l('Force').'</label>
								<p>'.$this->l('Stop : if there are errors in the audit data, import will not run.').'</p>
								<p>'.$this->l('Skip : if there are errors in the audit data, import will skip incorrect data.').'</p>
								<p>'.$this->l('Force : if there are errors in the audit data, import will replace incorrect data by generic data.').'</p>
							</div>
							<hr>
							<div style="display:none" id="specificOptions">
							<h2>'.$this->l('Specific Options').'</h2>
								<div id="specificOptionsContent"></div>
							</div>
							<hr>
							<div class="margin-form">
							<input type="submit" class="button" name="checkAndSaveConfig" id="checkAndSaveConfig" value="'.$this->l('Next Step').'">						
						</div>
					</div>
				</div>
				</fieldset>';
		return $html;
	}

	
	public function generiqueImport($className, $fields, $save = false)
	{
		$return = '';
		$json = array();
		$errors = array();
		$json['hasError'] = false;
		$json['datas'] = $fields;
		$languages = array();
		$defaultLanguage = '';
		
		$object = new $className();
		$rules = call_user_func(array($className, 'getValidationRules'), $className);
		
		if ((sizeof($rules['requiredLang']) OR sizeof($rules['sizeLang']) OR sizeof($rules['validateLang'])))
		{
			$moduleName = Tools::getValue('moduleName');
			if (file_exists('../../modules/'.$moduleName.'/'.$moduleName.'.php'))
			{
				require_once('../../modules/'.$moduleName.'/'.$moduleName.'.php');
				$importModule = new $moduleName();
				$server = Tools::getValue('server');
				$user = Tools::getValue('user');
				$password = Tools::getValue('password');
				$database = Tools::getValue('database');
				$prefix = Tools::getValue('prefix');
				$importModule->prefix = $prefix;
				$importModule->initDatabaseConnection($server, $user, $password, $database);
				$languages = $importModule->getLangagues(0);
				$idDefaultLanguage = $importModule->getDefaultIdLang();
				$this->initDatabaseConnection(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
				$defaultLanguage = new Language((int)$idDefaultLanguage);
			}
			else
				die('{"hasError" : true, "error" : ["FATAL ERROR"], "datas" : []}');
		}
		
		foreach($fields as $key => $field)
		{
			//check if id name is not generic (ex: id_lang)
			if (array_key_exists('id_'.strtolower($className), $this->match_id))
				$id = $this->match_id['id_'.strtolower($className)];
			else
				$id = 'id_'.strtolower($className);

			//remove wrong fields (ex : id_toto in Customer)
			foreach($field as $name => $value)
				if (!array_key_exists($name, get_object_vars($object)) AND ($name != $id))
					unset($field[$name]);	
			$return = $this->validateRules($rules, &$field, $className, $languages, $defaultLanguage);
			if (!empty($return))
			{
				//skip mode
				if (Tools::getValue('hasErrors') == 1)
					unset($fields[$key]);

				$errors[] = $return;
				array_unshift($errors[sizeof($errors)-1], $field[$id]);
			}
		}
		if (sizeof($errors) > 0)
		{
			$json['hasError'] = true;
			$json['error'] = $errors;
		}

		if ($save)
		{
			//add language if not exist in prestashop
			if ($className == 'Language')
				$this->checkAndAddLang($fields);
			else
				$this->autoInsert(strtolower($className), $fields);
			
		}
		die(Tools::jsonEncode($json));
	
	}
	
	private function autoInsert($table, $items)
	{
		if (!sizeof($items))
			return true;
		$identifier = $this->supportedImports[$table]['identifier'];
		$this->initDatabaseConnection(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
		$query = '';
		$queryValue = '';
		$queryFields = '';
		$queryLang = '';
		$queryValueLangTab = array();
		$queryValueLang = '';
		$queryFieldsLang = array();
		$multiLangFields = array();
		$queryValueAssocTab = array();
		$queryValueAssoc = '';
		$hasLang = false;
		$hasAssociation = false;
		$foreignKey = array();
		$moduleName = Tools::getValue('moduleName');

		//get foreign key
		if (array_key_exists('foreign_key', $this->supportedImports[$table]))
		{
			//get default id
			(array_key_exists('defaultId', $this->supportedImports[$table]) ? $defaultId = Configuration::get( $this->supportedImports[$table]['defaultId']) :  $defaultId = 0);
			
			$foreign_key = $this->getForeignKey($table);
			foreach($items as $k => $val)
				foreach($foreign_key as $f_key => $match)
				{	
					if ($table == 'product' AND $f_key == 'id_category')
						$f_key = 'id_category_default';
					if($items[$k][$f_key] != 0)
						$items[$k][$f_key] = $match[$val[$f_key]];
					else
						$items[$k][$f_key] = $defaultId;
				}
		}
		//creating temporary fields for identifiers matching and password
		if (array_key_exists('alterTable', $this->supportedImports[$table]))
		{
			$this->alterTable($table);
			foreach($items as $k => $val)
					foreach($this->supportedImports[$table]['alterTable'] as $key => $value)
					{
						$items[$k][$key.'_'.$moduleName] = $items[$k][$key];
						$items[$k][$key] = 'NULL';
					}
		}
		foreach($items as $item)
		{
			$queryValue .= '(';
			if (empty($queryFields))
			{
				$queryFields = implode(',', array_keys($items[key($items)]));
				$queryFields = '`'.str_replace(',', '`,`', $queryFields).'`';
			}
			
			foreach ($item AS $key => $value)
				if (is_array($value))
				{
					if ($key != 'association')
					{
						$hasLang = true;
						$multiLangFields[$item[$identifier.'_'.$moduleName]][$key] = $value;
						if (!array_key_exists($key, $queryFieldsLang))
							$queryFieldsLang[$key] = '`'.pSQL($key).'`';
						
						//remove field name multi lang
						$queryFields = str_replace(',`'.$key.'`', '', $queryFields);
					}
					else
					{
						$hasAssociation = true;
						$queryValueAssocTab[] = $value;
						//remove field name association
						$queryFields = str_replace(',`'.$key.'`', '', $queryFields);
					}
				}
				elseif($identifier == $key)
					$queryValue .= 'NULL,' ;
				elseif (!empty($foreignKey) AND array_key_exists($key, $foreignKey))
						$queryValue .='\''.$foreignKey[$key][$value].'\',';
					else
						$queryValue .=($value == 'NULL' ? 'NULL,' : '\''.pSQL($value).'\',');
			
			$queryValue = rtrim($queryValue, ',').'),';
			$queryFields = rtrim($queryFields, ',');
		}
	
		$queryValue = rtrim($queryValue, ',');
		Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.pSQL($table).'` ('.$queryFields.') VALUES '.rtrim($queryValue, ','));
		
		//clean position
		$this->cleanPositions($table);
		
		if ($hasLang)
		{
			$queryFieldsLang = '`'.pSQL($identifier).'`, `id_lang`, '.implode(',', $queryFieldsLang);
			$tmpTab = array();
			//get foreign key for lang table
			$foreignKeyLang =  $this->getForeignKeyLang($table);
			$idsMatchLang = $this->getMatchIdLang();
			//TODO a mettre dans la methode formatInsertLang
			$tmpTab = array();
			foreach($multiLangFields as $multiLang => $val)
				foreach($val as $field)
					foreach($field as $lang => $value)
						if (array_key_exists($multiLang, $tmpTab) AND array_key_exists($lang, $tmpTab[$multiLang]))
							$tmpTab[$multiLang][$lang] .= '\''.pSQL($value).'\', ';
						else
							$tmpTab[$multiLang][$lang] = '\''.pSQL($foreignKeyLang[$multiLang]).'\', \''.pSQL($idsMatchLang[$lang]).'\', \''.pSQL($value).'\', ';
			foreach($tmpTab as $tmp)
				foreach($tmp as $t)
					$queryValueLang .= '('.rtrim($t , ', ').'), ';
		
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.pSQL($table).'_lang` ('.$queryFieldsLang.') 
										VALUES '.rtrim($queryValueLang, ', '));
		}
		if($hasAssociation)
		{
			$tmpTab = array();
			$foreignKeyId = $this->supportedImports[$table]['association']['foreign_key'];
			$foreignKey =  $this->getForeignKey($table, $this->supportedImports[$table]['association']['foreign_key']);
			foreach($queryValueAssocTab as $key => $val)
				$tmpTab[$foreignKey[$foreignKeyId[0]][key($val)]] = $foreignKey[$foreignKeyId[1]][$val[key($val)]];
			$this->autoInsertAssociation($table, $this->supportedImports[$table]['association']['table'], $tmpTab);
		}
	}
	
	private function autoInsertAssociation($table, $tableAssociation,  $fields)
	{
			$associatFields = '';
			
			foreach($fields as $key => $val)
				$associatFields .= ' ('.(int)$key.', '.(int)$val.'), ';
			$associatFields = rtrim($associatFields, ', ');
			$fieldName = implode('`, `', $this->supportedImports[$table]['association']['foreign_key']);
			if ($associatFields != '')
				Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.pSQL($tableAssociation).'` (`'.$fieldName.'`) VALUES '.$associatFields.' ');
	}
	
	/*
private function formatInsertLang($table, $fields, $identifier)
	{
		$query = '';
		return $query;
	}
*/
	
	private function alterTable($table)
	{
		$query ='';
		$queryTmp = '';
		$from = $table;
		$result = array();
		//change table name nonstandard 
		($table == 'language' ?  $from = 'lang' : ($table == 'order' ?  $from = 'orders' : '' ));
		$this->initDatabaseConnection(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
		$result = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.pSQL($from).'`');
		if (!$result)
			$result = array();
		foreach ($this->supportedImports[$table]['alterTable'] as $name => $type)
		{
			$moduleName = Tools::getValue('moduleName');
			if (!array_key_exists($name.'_'.$moduleName, $result))
				$queryTmp .= ' ADD `'.$name.'_'.$moduleName.'` '.$type.' NOT NULL,';
		}
		
		if (!empty($queryTmp))
		{
			$query = 'ALTER TABLE  `'._DB_PREFIX_.pSQL($from).'` ';
			$query .= rtrim($queryTmp, ',');
			Db::getInstance()->Execute($query);
		}

	}
	
	private function getForeignKey($table, $foreign_key = null)
	{
		$moduleName = Tools::getValue('moduleName');
		//$this->initDatabaseConnection(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
		if (is_null($foreign_key))
			$foreign_key = $this->supportedImports[$table]['foreign_key'];
		$match = array();		
		foreach($foreign_key as $key)
		{
			foreach($this->supportedImports as $table => $conf)
				if ($conf['identifier'] == $key)
					$from = $table;
			$return = Db::getInstance()->ExecuteS('SELECT `'.$key.'_'.$moduleName.'`, `'.$key.'` FROM `'._DB_PREFIX_.$from.'` WHERE `'.$key.'_'.$moduleName.'` != 0');
			foreach($return as $name => $val)
				$match[$key][$val[$key.'_'.$moduleName]] = $val[$key];
		}
		return $match;
	}
	
	private function getForeignKeyLang($table)
	{		
		$id = $this->supportedImports[$table]['identifier'];
		$moduleName = Tools::getValue('moduleName');
		$return = Db::getInstance()->ExecuteS('SELECT `'.$id.'_'.$moduleName.'`, `'.$id.'` FROM `'._DB_PREFIX_.pSQL($table).'` WHERE `'.$id.'_'.$moduleName.'` != 0');
		$match = array();
		foreach($return as $name => $val)
				$match[$val[$id.'_'.$moduleName]] = $val[$id];
		return $match;
	}
	private function getMatchIdLang()
	{
		$moduleName = Tools::getValue('moduleName');
		$return = Db::getInstance()->ExecuteS('SELECT `id_lang_'.$moduleName.'`, `id_lang` FROM `'._DB_PREFIX_.'lang'.'` WHERE `id_lang_'.$moduleName.'` != 0');
		$match = array();
		foreach($return as $name => $val)
				$match[$val['id_lang_'.$moduleName]] = $val['id_lang'];
		return $match;
	}
	
	
	private function validateRules($rules, $fields, $className, $languages, $defaultLanguage)
	{
		$returnErrors = array();
		$hasErrors = Tools::getValue('hasErrors');
		//$this->initDatabaseConnection(_DB_SERVER_, _DB_USER_, _DB_PASSWD_, _DB_NAME_);
		
		/* Checking for required fields */
		foreach ($rules['required'] AS $field)
			if (($value = $fields[$field]) == false AND (string)$value != '0')
				if ($hasErrors == 2)
					$todo;//TODO remplire avec des données par default
				else
					$returnErrors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $field, $className).'</b> '.$this->l('is required');	
		
		/* Checking for maximum fields sizes */
		foreach ($rules['size'] AS $field => $maxLength)
			if (array_key_exists($field, $fields) AND $field != 'passwd')
				if ($fields[$field] !== false AND Tools::strlen($fields[$field]) > $maxLength)
					if ($hasErrors == 2)
						$fields[$field] = substr($fields[$field], 0, $maxLength);
					else
						$returnErrors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $field, $className).'</b>'.$this->l('is too long').' ('.$maxLength.' '.$this->l('chars max').')';
		
		/* Checking for fields validity */
		foreach ($rules['validate'] AS $field => $function)
			if (array_key_exists($field, $fields))
				if (($value = $fields[$field]) !== false AND ($field != 'passwd'))
					if (!Validate::$function($value))
						if ($hasErrors == 2)
							$todo;//TODO remplire avec des données par default
						else
							$returnErrors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $field, $className).'</b> '.$this->l('is invalid');
		if ((sizeof($rules['requiredLang']) OR sizeof($rules['sizeLang']) OR sizeof($rules['validateLang'])))
		{

		/* Checking for multilingual required fields */
		foreach ($rules['requiredLang'] AS $fieldLang)
			if (($empty = $fields[$fieldLang][$defaultLanguage->id]) === false OR empty($empty))
				if ($hasErrors == 2)
					$todo;//TODO remplire avec des données par default
				else
					$returnErrors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $fieldLang, $className).'</b> '.$this->l('is required at least in').' '.$defaultLanguage->name;
		
		/* Checking for maximum multilingual fields size */
		foreach ($rules['sizeLang'] AS $fieldLang => $maxLength)
			foreach ($languages AS $language)
				if (isset($fields[$fieldLang][$language['id_lang']]) && $fields[$fieldLang] !== false AND Tools::strlen($fields[$fieldLang][$language['id_lang']]) > $maxLength)
					if ($hasErrors == 2)
						$fields[$field] = substr($fields[$field], 0, $maxLength);
					else
						$returnErrors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $fieldLang, $className).' ('.$language['name'].')</b> '.$this->l('is too long').' ('.$maxLength.' '.$this->l('chars max').')';
		/* Checking for multilingual fields validity */
		foreach ($rules['validateLang'] AS $fieldLang => $function)
			foreach ($languages AS $language)
				if (isset($fields[$fieldLang][$language['id_lang']]) && ($value = $fields[$fieldLang][$language['id_lang']]) !== false AND !empty($value))
					if (!Validate::$function($value))
						if ($hasErrors == 2)
							$todo;//TODO remplire avec des données par default
						else
							$this->_errors[] = $this->l('the field').' <b>'.call_user_func(array($className, 'displayFieldName'), $fieldLang, $className).' ('.$language['name'].')</b> '.$this->l('is invalid');

		}
		
		return $returnErrors;
	}
	
	public function checkAndAddLang ($languages)
	{
		$errors = array();
		$moduleName = Tools::getValue('moduleName');		
		$this->alterTable('language');
		foreach($languages as $language)
		{
			$iso = $language['iso_code'];
			if (!Language::getIdByIso($iso))
			{
				if (Validate::isLangIsoCode($iso))
				{
					if (@fsockopen('www.prestashop.com', 80))
					{
						if ($content = file_get_contents('http://www.prestashop.com/download/lang_packs/gzip/'.$iso.'.gzip'))
						{
							$file = _PS_TRANSLATIONS_DIR_.$iso.'.gzip';
							if (file_put_contents($file, $content))
							{
								require_once('../../tools/tar/Archive_Tar.php');
								$gz = new Archive_Tar($file, true);
								if ($gz->extract(_PS_TRANSLATIONS_DIR_.'../', false))
								{
									if (!Language::checkAndAddLanguage($iso))
										$errors[] = Tools::displayError('archive cannot be extracted');
									else
									{
										$newId = Language::getIdByIso($iso);
										Db::getInstance()->Execute('UPDATE  `'._DB_PREFIX_.'lang` 
																	SET  `id_lang_'.$moduleName.'` =  '.$language['id_lang'].' 
																	WHERE  `id_lang` = '.$newId);
										$errors[] = true;
									}
								}
								$errors[] = Tools::displayError('archive cannot be extracted');
							}
							else
								$errors[] = Tools::displayError('Server don\'t have permissions for writing');
						}
						else
							$errors[] = Tools::displayError('language not found');
					}
					else
						$errors[] = Tools::displayError('archive cannot be downloaded from prestashop.com');
				}
				else
					$errors[] = Tools::displayError('Invalid parameter');
				}
			else
			{
				$newId = Language::getIdByIso($iso);
				Db::getInstance()->Execute('UPDATE  `'._DB_PREFIX_.'lang` 
											SET  `id_lang_'.$moduleName.'` =  '.$language['id_lang'].' 
											WHERE  `id_lang` = '.$newId);
			}
		}

	}

	public function truncateTable($table)
	{
		switch ($table)
		{
			case 'language' :
				$languages = Language::getLanguages();
				$defaultIdLang = Configuration::get('PS_LANG_DEFAULT');
				$tmp = array();
				foreach($languages as $lang)
					if ($lang['id_lang'] != $defaultIdLang)
						$tmp[] = $lang['id_lang'];
				$language = new Language();
				$language->deleteSelection($tmp);
			break;
			case 'customer' :
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'customer');
				break;
			case 'address' :
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'address');
				break;
			case 'category' :
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'category` WHERE id_category != 1');
				Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'category_lang` WHERE id_category != 1');
				Db::getInstance()->Execute('ALTER TABLE `'._DB_PREFIX_.'category` AUTO_INCREMENT = 2 ');
/*
				foreach (scandir(_PS_CAT_IMG_DIR_) AS $d)
					if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d))
						@unlink(_PS_CAT_IMG_DIR_.$d);
*/
			break;
			case 'product' :
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'product');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'feature_product');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'product_lang');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'category_product');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'product_tag');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'image');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'image_lang');
/*
				foreach (scandir(_PS_PROD_IMG_DIR_) AS $d)
					if (preg_match('/^[0-9]+\-[0-9]+\-(.*)\.jpg$/', $d)
								OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d)
								OR preg_match('/^[0-9]+\-[0-9]+\.jpg$/', $d))
					{
						@unlink(_PS_PROD_IMG_DIR_.$d);
					}
*/
				break;
			case 'Manufacturers' :
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'manufacturer');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'manufacturer_lang');
/*
				foreach (scandir(_PS_MANU_IMG_DIR_) AS $d)
					if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d))
						@unlink(_PS_MANU_IMG_DIR_.$d);
*/
				break;
			case 'Suppliers' :
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'supplier');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'supplier_lang');
				foreach (scandir(_PS_SUPP_IMG_DIR_) AS $d)
/*
					if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^([[:lower:]]{2})\-default\-(.*)\.jpg$/', $d))
						unlink(_PS_SUPP_IMG_DIR_.$d);
*/
				break;
			case 'currency' :
			case 'customer' :
			case 'zone' :
			case 'state' :
			case 'group' :
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.pSQL($table));
				break;
		}
		return true;
	}
	
	public function cleanPositions($table)
	{	
		if($table == 'category')
		{
			//clean category position
			$cat = Category::getCategories(1, false, false);
			foreach($cat as $i => $categ)
			{
				Category::cleanPositions((int)($categ['id_category']));
			}
		}
		if($table == 'product')
		{
			//clean products position
			$cat = Category::getCategories(1, false, false);
			foreach($cat as $i => $categ)
			{
				Product::cleanPositions((int)($categ['id_category']));
			}
		}
	}
	
	public function getDefaultIdLang ()
	{
		return;
	}

}
/*


*/

?>
