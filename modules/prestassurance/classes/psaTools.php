<?php

class psaTools
{
	static public function checkLocalUse()
	{
		$ip = ip2long($_SERVER['REMOTE_ADDR']);
		if (!$ip)
			return true;
		$islocal = false;
		if($ip >= ip2long('192.168.0.0') && $ip <= ip2long('192.168.255.255'))
			$islocal = true;
		elseif($ip >= ip2long('10.0.0.0') && $ip <= ip2long('10.255.255.255'))
			$islocal = true;
		elseif($ip >= ip2long('172.16.0.0') && $ip <= ip2long('172.31.255.255'))
			$islocal = true;
		elseif($ip == ip2long('127.0.0.1'))
			$islocal = true;
		return $islocal;
	}
	
	static public function checkEnvironement()
	{
		if (!Configuration::get('PSA_ID_MERCHANT') or !Configuration::get('PSA_KEY') or !Configuration::get('PSA_ORDER_STATUS'))
			return false;

		if (!Configuration::get('PSA_ENVIRONMENT'))
			if (in_array(Tools::getRemoteAddr(), explode(',', Configuration::get('PSA_IP_ADDRESS'))))
				return true;
			else
				return false;
			else
				return true;
	}
	
	static public function checkLimitedCountry()
	{
		global $cart;
		
		if (!$cart->id_address_delivery)
			if (Configuration::get('PSA_PROPOSE_DISCONECT'))
				return true;
			else
				return false;
				
		$address = new Address((int)$cart->id_address_delivery);
		$country = new Country((int)$address->id_country);
		if (in_array($country->iso_code, explode('|', Configuration::get('PSA_LIMITED_COUNTRY'))))
			return true;
		return false;
	}
	
	static public function createHiddenCategoryAndProduct()
	{
		$languages = Language::getLanguages();
		$category = new Category();
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'fr')
			{
				$category->name[$language['id_lang']] = 'Assurance';
				$category->link_rewrite[$language['id_lang']] = 'Assurance';
			}
			if ($language['iso_code'] == 'en')
			{
				$category->name[$language['id_lang']] = 'Insurance';
				$category->link_rewrite[$language['id_lang']] = 'Insurance';
			}
		}
		$category->id_parent = 0;
		$category->level_depth = 0;
		$category->active = 0;
		$category->add();
		Configuration::updateValue('PSA_CAT_ID', (int)$category->id);


		$product = new Product();
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'fr')
			{
				$product->name[$language['id_lang']] = 'Assurance';
				$product->link_rewrite[$language['id_lang']] = 'Assurance';
			}
			else if ($language['iso_code'] == 'en')
			{
				$product->name[$language['id_lang']] = 'Insurance';
				$product->link_rewrite[$language['id_lang']] = 'Insurance';
			}
			else
			{
				$product->name[$language['id_lang']] = 'Insurance';
				$product->link_rewrite[$language['id_lang']] = 'Insurance';
			}
		}
		
		$product->quantity = 100;
		$product->price = 0;
		$product->id_category_default = (int)$category->id;
		$product->active = true;
		$product->id_tax = 0;
		$product->out_of_stock = 1;
		$product->add();
		
		Configuration::updateValue('PSA_ID_PRODUCT', (int)$product->id);

		$attrGroup = new AttributeGroup();
		$attrGroup->is_color_group = false;
		foreach ($languages as $language)
		{
			if ($language['iso_code'] == 'fr')
			{
				$attrGroup->name[$language['id_lang']] = 'PresatShop Assurance[Do not delete]';
				$attrGroup->public_name[$language['id_lang']] = 'Assurance';
			}
			else if ($language['iso_code'] == 'en')
			{
				$attrGroup->name[$language['id_lang']] = 'PresatShop Assurance[Do not delete]';
				$attrGroup->public_name[$language['id_lang']] = 'Insurance';
			}
			else
			{
				$attrGroup->name[$language['id_lang']] = 'PresatShop Assurance[Do not delete]';
				$attrGroup->public_name[$language['id_lang']] = 'Insurance';
			}
		}
		$attrGroup->add();
		Configuration::updateValue('PSA_ATTR_GROUP_ID', (int)$attrGroup->id);
	}
	
	static public function deleteHiddenCategoryAndProduct()
	{
		$psa_id_cat = Configuration::get('PSA_CAT_ID');
		$psa_id_product = Configuration::get('PSA_ID_PRODUCT');
		$psa_id_attr = Configuration::get('PSA_ATTR_GROUP_ID');

		if ($psa_id_cat && $psa_id_cat != 1 )
		{
			$category = new Category((int)$psa_id_cat);
			$category->delete();
			Configuration::deleteByName('PSA_CAT_ID');
		}

		if ($psa_id_cat && $psa_id_product != 1)
		{
			$product = new Product((int)$psa_id_product);
			$product->delete();
			Configuration::deleteByName('PSA_ID_PRODUCT');
		}

		if ($psa_id_attr && $psa_id_attr != 1)
		{
			$attrGroup = new AttributeGroup((int)$psa_id_attr);
			$attrGroup->delete();
			Configuration::deleteByName('PSA_ATTR_GROUP_ID');
		}
	}
	
	static function checkMinimumThreshold($product_price, $insurance_price, $minimum_threshold)
	{
		if (!$minimum_threshold)
			return true;

		$percentage = ($insurance_price / $product_price) * 100;
		
		if ($percentage > $minimum_threshold)
			return false;
		else
			return true;	
		
	}
	
	static function openPopIn()
	{
		global $cookie;
		if (isset($cookie->psa_id_cart))
		{
			if ($cookie->psa_id_cart == $cookie->id_cart)
				if ($cookie->psa_pop_in == 1)
					return false;
				else
					return true;
			else
			{
				$cookie->psa_id_cart = $cookie->id_cart;
				$cookie->psa_pop_in = 0;
				return true;
			}
		}
		else
		{
			$cookie->psa_id_cart = $cookie->id_cart;
			$cookie->psa_pop_in = 0;
			return true;
		}
	}
}
