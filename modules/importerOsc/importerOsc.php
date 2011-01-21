<?php

class importerOsc extends ImportModule
{
	public function __construct()
	{
		global $cookie;
		
		$this->name = 'importerOsc';
		$this->tab = 'migration_tools';
		$this->version = '1.0';
		$this->theImporter = 1;

		parent::__construct ();

		$this->displayName = $this->l('Importer osCommerce');
		$this->description = $this->l('This module allows you to import from osCommerce to Prestashop'); 
	}
	
	public function install()
	{
		if (!parent::install())
			return false;
		return true; 					
	}
	
	public function uninstall()
	{
		if (!$this->registerHook('beforeAuthentication') OR !parent::uninstall())
			return false;
		return true;
	}
	
	public function displaySpecificOptions()
	{
		$langagues = $this->ExecuteS('SELECT * FROM  `'.addslashes($this->prefix).'languages`');
		$curencies = $this->ExecuteS('SELECT * FROM  `'.addslashes($this->prefix).'currencies`');
		
		$html = '<label style="width:200px">'.$this->l('Default osCommerce language').' : </label>
				<div class="margin-form">
				<select name="defaultOscLang"><option value="------">------</option>';
				foreach($langagues AS $lang)
					$html .= '<option value="'.$lang['languages_id'].'">'.$lang['name'].'</option>';
		$html .= '</select></div>
				<label style="width:200px">'.$this->l('Default osCommerce currency').' : </label>
				<div class="margin-form">
				<select name="defaultOscCurrency"><option value="------">------</option>';
				foreach($curencies AS $curency)
					$html .= '<option value="'.$curency['currencies_id'].'">'.$curency['title'].'</option>';
		$html .= '</select></div>';
		return $html;
	}
	
	public function getDefaultIdLang ()
	{
		return Tools::getValue('defaultOscLang');
	}
	
	public function getDefaultIdCurrency ()
	{
		return Tools::getValue('defaultOscCurrency');
	}
	
	
	public function getLangagues($limit = 0)
	{
		
		$identifier = 'languages_id';
		$matchFields = array(
							'languages_id' => 'id_lang',
							'name' => 'name',
							'code' => 'iso_code',
							'active' => 'active'
							);
		$langagues = $this->ExecuteS('SELECT languages_id, name, code, 1 as active FROM  `'.addslashes($this->prefix).'languages` LIMIT '.(int)($limit).' , 100');
		return $this->autoFormat($langagues, $identifier, $matchFields);		
	}
	
	public function getCurrencies($limit = 0)
	{
		$identifier = 'currencies_id';
		$matchFields = array(
							'currencies_id' => 'id_currency',
							'title' => 'name',
							'code' => 'iso_code',
							'code_num' => 'iso_code_num',
							'symbol' => 'sign',
							'value' => 'conversion_rate',
							'format' => 'format',
							'decimals' => 'decimals'
							);
		$currencies = $this->ExecuteS('
										SELECT currencies_id, title, code , 0 as format, 999 as code_num, 1 as decimals, CONCAT(`symbol_left`, `symbol_right`) as symbol, value  
										FROM  `'.addslashes($this->prefix).'currencies` LIMIT '.(int)($limit).' , 100'
										);
		return $this->autoFormat($currencies, $identifier, $matchFields);		
	}
	
	public function getZones($limit = 0)
	{
		$identifier = 'geo_zone_id';
		$matchFields = array(
							'geo_zone_id' => 'id_zone',
							'geo_zone_name' => 'name',
							'active' => 'active'
							);
		$zones = $this->ExecuteS('SELECT geo_zone_id, geo_zone_name, 1 as active FROM  `'.addslashes($this->prefix).'geo_zones` LIMIT '.(int)($limit).' , 100');
		return $this->autoFormat($zones, $identifier, $matchFields);		
	}
	
	public function getCountries($limit = 0)
	{
		$multiLangFields = array('countries_name');
		$keyLanguage = 'language_id';
		$identifier = 'countries_id';
		$matchFields = array(
							'countries_id' => 'id_country',
							'countries_name' => 'name',
							'countries_iso_code_2' => 'iso_code',
							'zone_id' => 'id_zone',
							'language_id' => 'id_lang',
							'id_currency' => 'id_currency',
							'contains_states' => 'contains_states',
							'need_identification_number' => 'need_identification_number',
							'active' => 'active'
							);
		$defaultIdLang = $this->getDefaultIdLang();
		$countries = $this->ExecuteS('
										SELECT countries_id, countries_name, countries_iso_code_2, '.$defaultIdLang.' as language_id,
										1 as zone_id, 0 as id_currency, 1 as contains_states, 1 as need_identification_number, 1 as active
										FROM  `'.addslashes($this->prefix).'countries` as c  LIMIT '.(int)($limit).' , 100');
		return $this->autoFormat($countries, $identifier, $matchFields, $keyLanguage, $multiLangFields);		
	}
	
	public function getStates($limit = 0)
	{
		$identifier = 'id_state';
		$matchFields = array(
							'id_state' => 'id_state',
							'id_country' => 'id_country',
							'id_zone' => 'id_zone',
							'iso_code' => 'iso_code',
							'name' => 'name',
							'active' => 'active'
							);
		$states = array(
				0 => array(
						'id_state' => 0,
						'id_country' => 0,
						'id_zone' => 0,
						'iso_code' => 999,
						'name' => 'osc',
						'active' => 0
						)			
					);
		return $this->autoFormat($states, $identifier, $matchFields);		
	}
	
	public function getGroups()
	{
		$idLang = $this->getDefaultIdLang();
		return array( 1 => array(
								'id_group' => 1,
								'price_display_method' => 0,
								'name' => array($idLang => $this->l('Default OS Commerce Group'))  
								)
					);
	}

	public function getCustomers($limit = 0)
	{
		$matchFields = array(
							'customers_id' => 'id_customer',
							'id_default_group' => 'id_default_group',
							'customers_gender' => 'id_gender',
							'customers_firstname' => 'firstname',
							'customers_lastname' => 'lastname',
							'customers_dob' => 'birthday',
							'customers_email_address' => 'email',
							'customers_password' => 'passwd',
							'active' => 'active'		
							);
							
		$genderMatch = array('m' => 1,'f' => 2);
		$customers = $this->ExecuteS('
										SELECT customers_id, 1 as id_default_group, customers_gender, customers_firstname, customers_lastname, DATE(customers_dob), customers_email_address, customers_password, 1 as active 
										FROM  `'.addslashes($this->prefix).'customers` LIMIT '.(int)($limit).' , 100'
										);
		
		$return = array();
		$i = 0;
		foreach($customers AS $customer)
		{
			foreach($customer AS $attr => $val)
				if (array_key_exists($attr, $matchFields))
				{
					switch ($attr) 
					{
				    case 'customers_gender':
				    	(array_key_exists($val, $genderMatch) ? $val = $genderMatch[$val] : $val = 9);
				        break;
				    }
				    $return[$i][$matchFields[$attr]] = $val;
				}
			$i ++;
		}
		return $return;
	}
	
	public function getAddresses($limit = 0)
	{
		$identifier = 'address_book_id';
		$matchFields = array(
							'address_book_id' => 'id_address',
							'customers_id' => 'id_customer',
							'alias' => 'alias',
							'entry_company' => 'company',
							'entry_firstname' => 'firstname',
							'entry_lastname' => 'lastname',
							'entry_street_address' => 'address1',
							'entry_postcode' => 'postcode',
							'entry_city' => 'city',
							'entry_state' => 'id_state',
							'entry_country_id' => 'id_country',
							);
		$addresses = $this->ExecuteS('SELECT address_book_id, customers_id,CONCAT(customers_id, \'_address\') as alias, entry_company, entry_firstname, entry_lastname,
										 entry_street_address, entry_postcode, entry_city, entry_country_id , 0 as entry_state
										FROM  `'.addslashes($this->prefix).'address_book` LIMIT '.(int)($limit).' , 100');
		return $this->autoFormat($addresses, $identifier, $matchFields);
	}

	
	public function getCategories($limit = 0)
	{
		$multiLangFields = array('categories_name', 'link_rewrite');
		$keyLanguage = 'language_id';
		$identifier = 'categories_id';
		$matchFields = array(
							'categories_id' => 'id_category',
							'parent_id' => 'id_parent',
							'language_id' => 'id_lang',
							'categories_name' => 'name',
							'active' => 'active',
							'link_rewrite' => 'link_rewrite'							
							);
		$categories = $this->ExecuteS('SELECT c.categories_id as categories_id, c.parent_id as parent_id, cd.language_id, cd.categories_name , 1 as active, REPLACE(LOWER(cd.categories_name), \' \', \'_\') as link_rewrite
										  FROM `'.addslashes($this->prefix).'categories` c 
										  LEFT JOIN `'.addslashes($this->prefix).'categories_description` cd ON (c.categories_id = cd.categories_id)
										  ORDER BY c.categories_id, cd.language_id
										  LIMIT '.(int)($limit).' , 100');
		return $this->autoFormat($categories, $identifier, $matchFields, $keyLanguage, $multiLangFields);
	}
	
	public function getAttributesGroups($limit = 0)
	{
		$multiLangFields = array('products_options_name');
		$keyLanguage = 'language_id';
		$identifier = 'products_options_id';
		$matchFields = array(
							'products_options_id' => 'id_attribute_group',
							'language_id' => 'id_lang',
							'products_options_name' => 'name',
							'products_options_name_2' => 'public_name',
							'is_color_group' => 'is_color_group'
							);

		$countries = $this->ExecuteS('
									SELECT products_options_id, products_options_name, products_options_name as products_options_name_2, language_id as language_id, 0 as is_color_group
									FROM  `'.addslashes($this->prefix).'products_options` 
									LIMIT '.(int)($limit).' , 100');
		return $this->autoFormat($countries, $identifier, $matchFields, $keyLanguage, $multiLangFields);		
	}
	
	public function getAttributes($limit = 0)
	{
		$multiLangFields = array('products_options_values_name');
		$keyLanguage = 'language_id';
		$identifier = 'products_options_values_id';
		$matchFields = array(
							'products_options_values_id' => 'id_attribute',
							'language_id' => 'id_lang',
							'products_options_values_name' => 'name',
							'products_options_id' => 'id_attribute_group'
							);

		$countries = $this->ExecuteS('
									SELECT p.`products_options_values_id`, p.`products_options_values_name`, p.`language_id` as language_id , po.`products_options_id`
									FROM  `'.addslashes($this->prefix).'products_options_values` p
									LEFT JOIN `'.addslashes($this->prefix).'products_options_values_to_products_options` po ON (po.products_options_values_id = p.products_options_values_id)
									LIMIT '.(int)($limit).' , 100');
		return $this->autoFormat($countries, $identifier, $matchFields, $keyLanguage, $multiLangFields);
	}
	
	public function getProducts($limit = 0)
	{
		$multiLangFields = array('products_name', 'link_rewrite', 'products_description');
		$keyLanguage = 'language_id';
		$identifier = 'products_id';
		$matchFields = array(
							'products_id' => 'id_product',
							'products_quantity' => 'quantity',
							'products_model' => 'reference',
							'products_price' => 'price',
							'products_weight' => 'weight',
							'products_status' => 'active',
							'manufacturers_id' => 'id_manufacturer',
							'language_id' => 'id_lang',
							'products_name' => 'name',
							'products_description' => 'description',
							'link_rewrite' => 'link_rewrite',
							'categories_id' => 'id_category_default',
							'association' => 'association'
							);
		$products = $this->ExecuteS('SELECT p.`products_id`, p.`products_quantity`, p.`products_model`, p.`products_price`, p.`products_weight`, p.`products_status`, p.`manufacturers_id`,
										pd.language_id, pd.products_name, pd.products_description ,
											(SELECT ptc.categories_id FROM `'.addslashes($this->prefix).'products_to_categories` ptc WHERE ptc.`products_id` = p.`products_id`) as categories_id
										FROM	`'.addslashes($this->prefix).'products` p LEFT JOIN `'.addslashes($this->prefix).'products_description` pd ON (p.products_id = pd.products_id)
										LIMIT '.(int)($limit).' , 100');
		foreach($products as& $product)
		{
			$product['link_rewrite'] = Tools::link_rewrite($product['products_name']);
			$product['association'] = array('category_product' => array($product['categories_id'] => $product['products_id']));
		}
		return $this->autoFormat($products, $identifier, $matchFields, $keyLanguage, $multiLangFields);
	}
	
	public function getProductsCombination($limit = 0)
	{
		$identifier = 'products_attributes_id';
		$matchFields = array(
							'products_attributes_id' => 'id_product_attribute',
							'products_id' => 'id_product',
							'price' => 'price',
							'association' => 'association'
							);
		$combinations = $this->ExecuteS('
										SELECT products_attributes_id, products_id, options_values_price as price, options_id
										FROM  `'.addslashes($this->prefix).'products_attributes` LIMIT '.(int)($limit).' , 100');
		/*
$combinations = $this->ExecuteS('
										SELECT products_attributes_id, products_id, CONCAT(price_prefix, options_values_price) as price, options_id
										FROM  `'.addslashes($this->prefix).'products_attributes` LIMIT '.(int)($limit).' , 100');
*/
		foreach($combinations as& $combination)
		{
			$combination['association'] = array('product_attribute_combination' => array($combination['options_id'] => $combination['products_attributes_id']));
			unset($combination['options_id']);
		}

		return $this->autoFormat($combinations, $identifier, $matchFields);
	}
	
	public function getManufacturers($limit = 0)
	{
		$identifier = 'manufacturers_id';
		$matchFields = array(
							'manufacturers_id' => 'id_manufacturer',
							'manufacturers_name' => 'name',
							'active' => 'active'					
							);
		$customers = $this->ExecuteS('SELECT manufacturers_id, manufacturers_name, 1 as active FROM  `'.addslashes($this->prefix).'manufacturers` LIMIT '.(int)($limit).' , 100');
		return $this->autoFormat($customers, $identifier, $matchFields);
	}
	
	private function autoFormat($items, $identifier,$matchFields, $keyLanguage = NULL, $multiLangFields = array())
	{		
		$array = array();
		foreach ($items AS $item)
			if (sizeof($multiLangFields) && is_array($multiLangFields) && isset($array[$item[$identifier]][$matchFields[$multiLangFields[0]]]))
				foreach ($multiLangFields AS $key)
					$array[$item[$identifier]][$matchFields[$key]][$item[$keyLanguage]] = $item[$key];
			else
				foreach ($item AS $key => $value)
					if (sizeof($multiLangFields) AND in_array($key, $multiLangFields))
						$array[$item[$identifier]][$matchFields[$key]] = array($item[$keyLanguage] => $value);
					elseif (sizeof($multiLangFields) AND $matchFields[$key] == $matchFields[$keyLanguage])
						continue;
					else
						$array[$item[$identifier]][$matchFields[$key]] = $value;
		return $array;
	}	

	public function hookbeforeAuthentication($params)
	{
		$passwd = trim(Tools::getValue('passwd'));
		$email = trim(Tools::getValue('email'));
		$result = Db::getInstance()->GetRow('
	          SELECT *
	          FROM `'._DB_PREFIX_     .'customer`
	          WHERE `active` = 1 AND `email` = \''.pSQL($email).'\'');
		if ($result && !empty($result['passwd_'.$this->name]))
	    {	
			if (file_exists(dirname(__FILE__).'/passwordhash.php'))
			{
				include(dirname(__FILE__).'/passwordhash.php');
				$hasher = new PasswordHash(10, true);
			 	if($hasher->CheckPassword($passwd, $result['passwd_'.pSQL($this->name)]))
			 	{
					$ps_passwd =  md5(pSQL(_COOKIE_KEY_.$passwd));
					Db::getInstance()->Execute('
					UPDATE `'._DB_PREFIX_.'customer`
					SET `passwd` = \''.pSQL($ps_passwd).'\', passwd_'.pSQL($this->name).' = \'\'
					WHERE `'._DB_PREFIX_.'customer`.`id_customer` ='.(int)$result['id_customer'].' LIMIT 1');
				}
			}
		}
	}

}

?>