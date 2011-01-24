<?php

class shopImporter extends ImportModule
{
	private $supportedImports = array();

	public function __construct()
	{
		global $cookie;

		$this->name = 'shopImporter';
		$this->tab = 'migration_tools';
		$this->version = '1.0';

		parent::__construct ();

		$this->displayName = $this->l('Shop Importer');
		$this->description = $this->l('This module allows you to import your shop from another system into prestashop');
		$this->supportedImports = array(
									'language' => array('methodName' => 'getLangagues',
														'name' => $this->l('Language'),
														'label' => $this->l('Import Languages'),
														'table' => 'lang',
														'identifier' => 'id_lang',
														'alterTable' => array('id_lang' => 'int(10)'),
														'info' => $this->l('New languages will automatically add translations!'),
														'delete' => true,
														'defaultId' => 'PS_LANG_DEFAULT'
														),
									'currency' => array('methodName' => 'getCurrencies',
														'name' => $this->l('Currency'),
														'label' => $this->l('Import Currencies'),
														'table' => 'currency',
														'identifier' => 'id_currency',
														'alterTable' => array('id_currency' => 'int(10)'),
														'delete' => true,
														'defaultId' => 'PS_CURRENCY_DEFAULT'
														),
									'zone' => array('methodName' => 'getZones',
														'name' => $this->l('Zone'),
														'label' => $this->l('Import Zones'),
														'table' => 'zone',
														'identifier' => 'id_zone',
														'alterTable' => array('id_zone' => 'int(10)'),
														'delete' => true
														),
									'country' => array('methodName' => 'getCountries',
														'name' => $this->l('Country'),
														'label' => $this->l('Import Countries'),
														'table' => 'country',
														'identifier' => 'id_country',
														'foreign_key' => array('id_zone', 'id_currency'),
														'alterTable' => array('id_country' => 'int(10)'),
														'delete' => true,
														'defaultId' => 'PS_COUNTRY_DEFAULT'
														),
									'state' => array('methodName' => 'getStates',
														'name' => $this->l('State'),
														'label' => $this->l('Import States'),
														'table' => 'state',
														'identifier' => 'id_state',
														'foreign_key' => array('id_zone', 'id_country'),
														'alterTable' => array('id_state' => 'int(10)'),
														'delete' => true
														),
									'group' => array('methodName' => 'getGroups',
														'name' => $this->l('Group'),
														'label' => $this->l('Import Groups'),
														'table' => 'group',
														'identifier' => 'id_group',
														'alterTable' => array('id_group' => 'int(10)'),
														'delete' => true,
														),
									'customer' => array('methodName' => 'getCustomers',
														'name' => $this->l('Customer'),
														'label' => $this->l('Import Customers'),
														'table' => 'customer',
														'identifier' => 'id_customer',
														'foreign_key' => array('id_group'),
														'alterTable' => array('id_customer' => 'int(10)', 'passwd' => 'varchar(100)'),
														'delete' => true,
														'association' => array(
															array(
																'table' => 'customer_group',
																'fields' => array('id_customer', 'id_group'),
																'matchTable' => array('customer', 'group'),
																)
															)
														),
									'address' => array('methodName' => 'getAddresses',
														'name' => $this->l('Address'),
														'label' => $this->l('Import Addresses'),
														'table' => 'address',
														'identifier' => 'id_address',
														'foreign_key' => array('id_country', 'id_state', 'id_customer'),
														'alterTable' => array('id_address' => 'int(10)'),
														'delete' => true
														),
									'order' => array('methodName' => 'getOrders',
													 'name' => $this->l('Order'),
													 'label' => $this->l('Import Orders'),
													 'table' => 'order',
													 'identifier' => 'id_order',
													 'alterTable' => array('id_order' => 'int(10)'),
													 'delete' => true
													 ),
									'manufacturer' => array('methodName' => 'getManufacturers',
														'name' => $this->l('Manufacturer'),
														'label' => $this->l('Import Manufacturers'),
														'table' => 'manufacturer',
														'identifier' => 'id_manufacturer',
														'delete' => true,
														'alterTable' => array('id_manufacturer' => 'int(10)'),
														),
									'supplier' => array('methodName' => 'getSuppliers',
														'name' => $this->l('Supplier'),
														'label' => $this->l('Import Suppliers'),
														'table' => 'supplier',
														'identifier' => 'id_supplier',
														'delete' => true,
														'alterTable' => array('id_supplier' => 'int(10)'),
														),
									'category' => array('methodName' => 'getCategories',
														'name' => $this->l('Category'),
														'label' => $this->l('Import Categories'),
														'table' => 'category',
														'identifier' => 'id_category',
														'alterTable' => array('id_category' => 'int(10)'),
														'delete' => true
														),
									'attributegroup' => array('methodName' => 'getAttributesGroups',
														'name' => $this->l('AttributeGroup'),
														'label' => $this->l('Import Attributes Groups'),
														'table' => 'attribute_group',
														'identifier' => 'id_attribute_group',
														'alterTable' => array('id_attribute_group' => 'int(10)'),
														'delete' => true
														),
									'attribute' => array('methodName' => 'getAttributes',
														'name' => $this->l('Attribute'),
														'label' => $this->l('Import Attributes'),
														'table' => 'attribute',
														'identifier' => 'id_attribute',
														'alterTable' => array('id_attribute' => 'int(10)'),
														'foreign_key' => array('id_attribute_group'),
														'delete' => true
														),
									'product' => array('methodName' => 'getProducts',
														'name' => $this->l('Product'),
														'label' => $this->l('Import Products'),
														'table' => 'product',
														'identifier' => 'id_product',
														'alterTable' => array('id_product' => 'int(10)'),
														'foreign_key' => array('id_category'),
														'delete' => true,
														'association' => array(
															array(
																'table' => 'category_product',
																'fields' => array('id_category', 'id_product'),
																'matchTable' =>  array('category', 'product')
																)
															)
														),
									'combination' => array('methodName' => 'getProductsCombination',
														'name' => $this->l('Combination'),
														'label' => $this->l('Import Products Combinations'),
														'table' => 'product_attribute',
														'identifier' => 'id_product_attribute',
														'alterTable' => array('id_product_attribute' => 'int(10)', 'id_product' => 'int(10)'),
														'foreign_key' => array('id_product'),
														'delete' => true,
														'association' => array(
															array(
																'table' => 'product_attribute_combination',
																'fields' => array('id_attribute', 'id_product_attribute'),
																'matchTable' =>  array('attribute', 'product_attribute')
																)
															)
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
				<script src="'._PS_JS_DIR_.'jquery/jquery.scrollTo-1.4.2-min.js"></script> 
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
					var runImport = "'.$this->l('Run Import').'";
					var importHasErrors = "'.$this->l('Errors occurred during import. For more details click on "Show errors"').'"
					var importFinish = "'.$this->l('The import is completed').'"
					var truncateTable = "'.$this->l('Remove data').'"
					var oneThing = "'.$this->l('Please choose one thing to import').'"
				</script>
				<style>
					.margin-form{padding: 0px 0px 1em 120px;width:300px;}
					label{width: 170px;}
					.import{background-color: #CCCCCC;border: 1px solid gray;margin: 0px 0px 10px;padding: 10px 15px;line-height: 20px;}
				</style>
				<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" />'.$this->l('Import from another system').'</legend>
				<div class="warn" ><img src="../img/admin/warn2.png">
					'.$this->l('Before starting the import please backup your database. ').'
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
				<div id="db_input">
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
							<input type="password" name="password" id="password" value="">
						</div>
					<label>'.$this->l('Database').' : </label>
						<div class="margin-form" style="">
							<input type="text" name="database" id="database" value="osc">
						</div>
					<label>'.$this->l('Database prefix').' : </label>
						<div class="margin-form" style="">
							<input type="text" name="prefix" id="prefix" value="">
						</div>
					</div>
					<div class="margin-form">
						<input type="submit" name="displayOptions" id="displayOptions" class="button" value="'.$this->l('Next Step').'">
					</div>
					<hr>
					<div style="display:none" id="importOptions">
					<h2>'.$this->l('Import Options').'</h2>
					<div id="importOptionsYesNo">';
					foreach($this->supportedImports as $key => $import)
					{
						$html .= '<label>'.$import['name'].' : </label>
									<div class="margin-form">
										<label class="t"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes"></label>
										<input type="radio" id="'.$import['identifier'].'_on'.'" name="'.$import['methodName'].'" class="'.$key.'" value="1" checked="checked">
										<label class="t" for="'.$import['identifier'].'_on'.'">'.$this->l('Yes').'</label>&nbsp;&nbsp;
										<label class="t"><img src="../img/admin/disabled.gif" alt="No" title="No" style="margin-left: 10px;"></label>
										<input type="radio" id="'.$import['identifier'].'_off'.'" name="'.$import['methodName'].'" class="'.$key.'" value="0">
										<label class="t" for="'.$import['identifier'].'_off'.'">'.$this->l('No').'</label>&nbsp;&nbsp;
										'.(array_key_exists('delete', $import) ? '
										<label class="t"><img src="../img/admin/delete.gif" alt="Delete" title="Delete"></label>
										<input type="checkbox" class="truncateTable" id="'.$key.'" name="delete_'.$import['name'].'">
										<label class="t" for="'.$key.'">'.$this->l('Delete').'</label>' : '' ).
										(array_key_exists('info', $import) ? '<p>'.$import['info'].'</p>' : '').'
									</div>';
					}
					$html .= '</div><hr>
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
								<p>'.$this->l('Stop: if there are errors with the data, import will not run.').'</p>
								<p>'.$this->l('Skip: if there are errors with the data, import will skip incorrect data.').'</p>
								<p>'.$this->l('Force: if there are errors with the data, import will replace incorrect data by generic data.').'</p>
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
		$table = $this->supportedImports[strtolower($className)]['table'];

		$object = new $className();
		$rules = call_user_func(array($className, 'getValidationRules'), $className);

		if ((sizeof($rules['requiredLang']) OR sizeof($rules['sizeLang']) OR sizeof($rules['validateLang']) OR Tools::isSubmit('syncLang') OR  Tools::isSubmit('syncCurrency')))
		{
			$moduleName = Tools::getValue('moduleName');
			if (file_exists('../../modules/'.$moduleName.'/'.$moduleName.'.php'))
			{
				require_once('../../modules/'.$moduleName.'/'.$moduleName.'.php');
				$importModule = new $moduleName();
				$importModule->server = Tools::getValue('server');
				$importModule->user = Tools::getValue('user');
				$importModule->passwd = Tools::getValue('password');
				$importModule->database = Tools::getValue('database');
				$importModule->prefix = Tools::getValue('prefix');
				$defaultLanguage = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
				if (Tools::isSubmit('syncLang'))
				{
					$defaultIdLand = $importModule->getDefaultIdLang();
					$languages = $importModule->getLangagues(0);
					$defaultLanguageImport = new Language(Language::getIdByIso($languages[$defaultIdLand]['iso_code']));
					if ($defaultLanguage->iso_code != $defaultLanguageImport->iso_code)
						$errors[] = $this->l('Delault lang don\'t match : ').'<br>'.Configuration::get('PS_SHOP_NAME').' : '.$defaultLanguage->name.' ≠ '.$importModule->displayName.' : '.$defaultLanguageImport->name.'<br>'.$this->l('Please change default language in your configuration');
				}
				
				if (Tools::isSubmit('syncCurrency'))
				{
					$defaultIdCurrency = $importModule->getDefaultIdCurrency();
					$currencies = $importModule->getCurrencies(0);
					if(!empty($currencies[$defaultIdCurrency]['iso_code']))
						$defaultCurrencyImport = new Currency((int)Currency::getIdByIsoCode($currencies[$defaultIdCurrency]['iso_code']));
					else
						$defaultCurrencyImport = new Currency((int)Currency::getIdByIsoCodeNum($currencies[$defaultIdCurrency]['iso_code_num']));
					
					
					$defaultCurrency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
					if ($defaultCurrency->iso_code != $defaultCurrencyImport->iso_code)
						$errors[] = $this->l('Delault Currency don\'t match : ').'<br>'.Configuration::get('PS_SHOP_NAME').' : '.$defaultCurrency->name.' ≠ '.$importModule->displayName.' : '.$defaultCurrencyImport->name.'<br>'.$this->l('Please change default currency in your configuration');
				}
				if (!empty($errors))
					die('{"hasError" : true, "error" : '.Tools::jsonEncode($errors).'}');
				
			}
			else
				die('{"hasError" : true, "error" : ["FATAL ERROR"], "datas" : []}');
		}
		
		foreach($fields as $key => $field)
		{
			$id = $this->supportedImports[strtolower($className)]['identifier'];
			//remove wrong fields (ex : id_toto in Customer)
			foreach($field as $name => $value)
				if (!array_key_exists($name, get_object_vars($object)) AND ($name != $id))
					unset($field[$name]);
			$return = $this->validateRules($rules, $field, $className, $languages, $defaultLanguage);
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
		
		
		if ($save OR Tools::isSubmit('syncLang'))
		{
			//add language if not exist in prestashop
			if ($className == 'Language')
			{
				if (Tools::isSubmit('syncLang'))
					$add = true;
				else
					$add = false;
				$this->checkAndAddLang($fields, $add);
			}
			else
			{
				$return = $this->saveObject($className, $fields);
				$this->cleanPositions($table);
				//insert association
				if (array_key_exists('association', $this->supportedImports[strtolower($className)]))
					$this->insertAssociation(strtolower($className), $fields);
				if (!empty($return))
				{
					$json['hasError'] = true;
					$json['error'] = $return;
				}
			}
			if ($className == 'Category')
				$this->updateCat();
		}
		die(Tools::jsonEncode($json));
	}
	
	private function saveObject($className, $items)
	{
		$return = array();
		$table = $this->supportedImports[strtolower($className)]['table'];
		//creating temporary fields for identifiers matching and password
		if (array_key_exists('alterTable', $this->supportedImports[strtolower($className)]))
			$this->alterTable(strtolower($className));
		foreach($items as $item)
		{
			$object = new $className;
			$id = $item[$this->supportedImports[strtolower($className)]['identifier']];
			if (array_key_exists('foreign_key', $this->supportedImports[strtolower($className)]))			
				$this->replaceForeignKey($item, $table);
			foreach($item as $key => $val)
			{
				if ($key == 'passwd')
					$val = substr($val,0,29);
				$object->$key = $val;
			}
		if (!$object->add())
			$return[] = array($item[$this->supportedImports[strtolower($className)]['identifier']], $this->l('An error occurred when adding the object'));
		else
			$this->saveMatchId(strtolower($className), (int)$object->id, (int)$id);
		}
		return $return;
	}
	
	private function insertAssociation($table, $items)
	{
		foreach($this->supportedImports[$table]['association'] AS $association)
		{
			$associatFields = '';
			$associatFieldsName = implode('`, `', $association['fields']);
			$tableAssociation = $association['table'];
			$matchTable = $association['matchTable'];
			if (!empty($items))
			{
				$match = array();
				foreach($matchTable as $mTable)
				{
					$tmp = $this->getForeignKey($mTable, array('id_'.$mTable));
					$match['id_'.$mTable] = $tmp['id_'.$mTable];
				}
				foreach($items AS $item)
					foreach($item AS $key => $val)
						if ($key == 'association' AND !empty($key))
							foreach($val[$tableAssociation] AS $k => $v)
								$associatFields .= ' ('.(int)$match[$association['fields'][0]][$k].', '.(int)$match[$association['fields'][1]][$v].'), ';
				if ($associatFields != '')
					Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.pSQL($tableAssociation).'` (`'.$associatFieldsName.'`) VALUES '.rtrim($associatFields, ', '));
			}
		}
	}
	
	private function saveMatchId($className, $psId, $matchId)
	{
		$table = $this->supportedImports[$className]['table'];
		$moduleName = Tools::getValue('moduleName');
		$identifier = $this->supportedImports[$className]['identifier'];
		Db::getInstance()->Execute('UPDATE '._DB_PREFIX_.pSQL($table).' SET `'.pSQL($identifier).'_'.pSQL($moduleName).'` =  '.(int)$matchId.' WHERE `'.pSQL($identifier).'` = '.(int)$psId);
	}
	
	private function replaceForeignKey(&$item, $table)
	{
		if ($table == 'product_attribute')
			$table2 = 'combination';
		else
			$table2 = $table;
		
		$foreingKey = $this->supportedImports[$table2]['foreign_key'];
		$foreingKeyValue = $this->getForeignKey($table, $foreingKey);
		foreach($foreingKey as $key)
		{
			if ($table == 'product' AND $key == 'id_category')
				$key2 = 'id_category_default';
			else if ($table == 'customer' AND $key == 'id_group')
				$key2 = 'id_default_group';
			else
				$key2 = $key;
			if ($item[$key2] != 0)
				$item[$key2] = $foreingKeyValue[$key][$item[$key2]];
			elseif (array_key_exists('defaultId', $this->supportedImports[$table]))
			{
				//get default id
				(array_key_exists('defaultId', $this->supportedImports[$table]) ? $defaultId = Configuration::get($this->supportedImports[$table]['defaultId']) :  $defaultId = 0);
				$item[$key] = $defaultId;
			}
		}
	}
	
	private function alterTable($className)
	{
		$query ='';
		$queryTmp = '';
		$from = $this->supportedImports[$className]['table'];
		$result = array();		
		$result = Db::getInstance()->getRow('SELECT * FROM `'._DB_PREFIX_.pSQL($from).'`');
		if (!$result)
			$result = array();
		foreach ($this->supportedImports[$className]['alterTable'] AS $name => $type)
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
	
	private function updateCat()
	{
		$moduleName = Tools::getValue('moduleName');
		Db::getInstance()->Execute('UPDATE
									'._DB_PREFIX_.'category c
									INNER JOIN
									'._DB_PREFIX_.'category c2
									ON
									c.id_parent = c2.id_category_'.pSQL($moduleName).' 
									SET
									c.id_parent = c2.id_category
									WHERE c.id_category_'.pSQL($moduleName).' != 0');
	}
	
	private function getForeignKey($className, $foreign_key = null)
	{
		
		$moduleName = Tools::getValue('moduleName');
		if (is_null($foreign_key))
			$foreign_key = $this->supportedImports[$className]['foreign_key'];
		$match = array();
		foreach($foreign_key AS $key)
		{
			foreach($this->supportedImports AS $table => $conf)
				if ($conf['identifier'] == $key)
					$from = $this->supportedImports[$table]['table'];
			$return = Db::getInstance()->ExecuteS('SELECT `'.pSQL($key).'_'.pSQL($moduleName).'`, `'.pSQL($key).'` FROM `'._DB_PREFIX_.pSQL($from).'` WHERE `'.pSQL($key).'_'.pSQL($moduleName).'` != 0');
			foreach($return AS $name => $val)
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
		foreach($return AS $name => $val)
				$match[$val[$id.'_'.$moduleName]] = $val[$id];
		return $match;
	}
	private function getMatchIdLang()
	{
		$moduleName = Tools::getValue('moduleName');
		$return = Db::getInstance()->ExecuteS('SELECT `id_lang_'.$moduleName.'`, `id_lang` FROM `'._DB_PREFIX_.'lang'.'` WHERE `id_lang_'.$moduleName.'` != 0');
		$match = array();
		foreach($return AS $name => $val)
				$match[$val['id_lang_'.$moduleName]] = $val['id_lang'];
		return $match;
	}


	private function validateRules($rules, &$fields, $className, $languages, $defaultLanguage)
	{
		$returnErrors = array();
		$hasErrors = Tools::getValue('hasErrors');

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

	public function checkAndAddLang ($languages, $add = true)
	{
		$errors = array();
		$moduleName = Tools::getValue('moduleName');
		$this->alterTable('language');
		foreach($languages as $language)
		{
			$iso = $language['iso_code'];
			if (!Language::getIdByIso($iso))
			{
				if ($add && Validate::isLangIsoCode($iso))
				{
					if (@fsockopen('www.prestashop.com', 80))
					{
						if ($lang_pack = json_decode(@file_get_contents('http://www.prestashop.com/download/lang_packs/get_language_pack.php?version='._PS_VERSION_.'&iso_lang='.$iso)))
						{
							if ($content = file_get_contents('http://www.prestashop.com/download/lang_packs/gzip/'.$lang_pack->version.'/'.$iso.'.gzip'))
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
									$errors[] = Tools::displayError('Server does not have permissions for writing');
							}
							else
								$errors[] = Tools::displayError('language not found');
						}
						else
							$errors[] = Tools::displayError('archive cannot be downloaded from prestashop.com');
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
					if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d))
						unlink(_PS_CAT_IMG_DIR_.$d);
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
					if (preg_match('/^[0-9]+\-[0-9]+\-(.*)\.jpg$/', $d) OR preg_match('/^[0-9]+\-[0-9]+\.jpg$/', $d))
						unlink(_PS_PROD_IMG_DIR_.$d);
*/
				break;
			case 'Manufacturers' :
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'manufacturer');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'manufacturer_lang');
/*
				foreach (scandir(_PS_MANU_IMG_DIR_) AS $d)
					if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d))
						unlink(_PS_MANU_IMG_DIR_.$d);
*/
				break;
			case 'Suppliers' :
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'supplier');
				Db::getInstance()->Execute('TRUNCATE TABLE `'._DB_PREFIX_.'supplier_lang');
				foreach (scandir(_PS_SUPP_IMG_DIR_) AS $d)
/*
				foreach (scandir(_PS_SUPP_IMG_DIR_) AS $d)
					if (preg_match('/^[0-9]+\-(.*)\.jpg$/', $d))
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
			foreach($cat AS $i => $categ)
				Category::cleanPositions((int)($categ['id_category']));
		}
		if($table == 'product')
		{
			//clean products position
			$cat = Category::getCategories(1, false, false);
			foreach($cat AS $i => $categ)
				Product::cleanPositions((int)($categ['id_category']));
		}
	}
	
	public function regenerateLevelDepth() {
		
		global $cookie;
		$categories = Category::getCategories(1, false, false);
		$cat = new Category();
		foreach($categories as $category)
		{
			$cat = new Category((int)$category['id_category']);
			$subCat = $cat->getSubCategories((int)$category['id_category']);
			d($subCat);
		}
	}

	public function getDefaultIdLang ()
	{
		return;
	}
}

?>