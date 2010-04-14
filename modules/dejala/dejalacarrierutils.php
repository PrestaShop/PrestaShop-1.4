<?php

/**
 * Utility class to manipulate dejala Carrier data
 **/
class DejalaCarrierUtils
{
	/**
		* creates of a dejala carrier corresponding to $dejalaProduct
	*/
	public static function createDejalaCarrier($dejalaConfig, $dejalaProduct)
	{
		// MFR091130 - get id zone from the country used in the module (if the store zones were customized) - default is 1 (Europe)
		$id_zone = 1;
		$moduleCountryIsoCode = strtoupper($dejalaConfig->country);
		$countryID = Country::getByIso($moduleCountryIsoCode);
		if (intval($countryID))
			$id_zone = Country::getIdZone($countryID);
		
		$vatRate = floatval($dejalaProduct['vat']);
		// MFR091130 - get or create the tax & attach it to our zone if needed
		$id_tax = Tax::getTaxIdByRate($vatRate);
		if (!$id_tax) 
		{
			$tax = new Tax();
			$tax->rate = $vatRate;
			$defaultLanguage = Configuration::get('PS_LANG_DEFAULT');
			$tax->name[$defaultLanguage] = $tax->rate . '%';
			$tax->add();
			$id_tax = Tax::getTaxIdByRate($vatRate);
		}
		if (!Tax::zoneHasTax($id_tax, $id_zone))
		{
			// MFR : direct call because $tax->addZone($id_zone) causes errors when called
			Db::getInstance()->Execute('INSERT INTO `'._DB_PREFIX_.'tax_zone` (`id_tax` , `id_zone`) VALUES ('.intval($id_tax).', '.intval($id_zone).')');			
		}
				
		$carrier = new Carrier();
		$carrier->name = 'dejala';
		$carrier->id_tax = $id_tax;
		$carrier->url = 'http://tracking.dejala.' . $dejalaConfig->country . '/tracker/@';
		$carrier->active = true;
		$carrier->deleted = 0;
		$carrier->shipping_handling = false;
		$carrier->range_behavior = 0;
		$carrier->is_module = 1;
		$languages = Language::getLanguages(true);
		foreach ($languages as $language) {
			if ($language['iso_code'] == 'fr')
				$carrier->delay[$language['id_lang']] = utf8_encode('Quand vous voulez... Par coursier, '.$dejalaProduct['timelimit'].'H');
			if ($language['iso_code'] == 'en')
				$carrier->delay[$language['id_lang']] = utf8_encode('When you want... Dispatch rider, '.$dejalaProduct['timelimit'].'H range');
			if ($language['iso_code'] == 'es')
				$carrier->delay[$language['id_lang']] = utf8_encode('Cuando quiera... Por mensajero, '.$dejalaProduct['timelimit'].'H');	
		}
		$carrier->add();
		
		$sql = 'INSERT INTO `'._DB_PREFIX_.'carrier_zone` (`id_carrier` , `id_zone`) VALUES ('.intval($carrier->id).', ' . intval($id_zone) . ')';
		Db::getInstance()->Execute($sql);

		$rangeW = new RangeWeight();
		$rangeW->id_carrier = $carrier->id;
		$rangeW->delimiter1 = 0;
		$rangeW->delimiter2 = $dejalaProduct['max_weight'];
		$rangeW->add();
		$vat_factor = (1+ ($dejalaProduct['vat'] / 100));
		$priceTTC = round(($dejalaProduct['price']*$vat_factor) + $dejalaProduct['margin'], 2);
		$priceHT = round($priceTTC/$vat_factor, 2);
		$priceList = '(NULL'.','.$rangeW->id.','.$carrier->id.','.$id_zone.','.$priceHT.')';
		$carrier->addDeliveryPrice($priceList);
		return (new Carrier($carrier->id));
	}

	/**
	 * Gets a dejala carrier corresponding to $dejalaProduct
	 */
	public static function getDejalaCarrier($dejalaConfig, $dejalaProduct)
	{
		global $cookie;
		
		$electedCarrier = NULL;
		$totalCartWeight = floatval($dejalaProduct['max_weight']);
		if ($totalCartWeight <= 0)
			$totalCartWeight = 3.99;
		else
			$totalCartWeight -= 0.01;
		
		/** MFR090828 - compare to HT price (since DejalaCarrier has a tax_id) */	
		$vat_factor = (1+ ($dejalaProduct['vat'] / 100));
		$priceTTC = round(($dejalaProduct['price']*$vat_factor) + $dejalaProduct['margin'], 2);
		$priceHT = round($priceTTC/$vat_factor, 2);			
		$productPrice = $priceHT;
		
		// MFR091130 - get id zone from the country used in the module (if the store zones were customized)
		// default (Europe)
		$id_zone = 1;
		$moduleCountryIsoCode = strtoupper($dejalaConfig->country);
		$countryID = Country::getByIso($moduleCountryIsoCode);
		if (intval($countryID))
			$id_zone = Country::getIdZone($countryID);
		
		$allCarriers = DejalaCarrierUtils::getCarriers(intval($cookie->id_lang), true, false, $id_zone, true);
		$electedCarrier = NULL;
		foreach ($allCarriers as $carrier) {
			if (($carrier['name'] == 'dejala')
//			&& ($carrier['range_behavior'])
//			&& (Configuration::get('PS_SHIPPING_METHOD'))
//			&& (Carrier::checkDeliveryPriceByWeight($carrier['id_carrier'], $totalCartWeight, $id_zone))
			) {
				$mCarrier = new DejalaCarrier($carrier['id_carrier']);
				$mCarrier->setDeliveryPrice($productPrice) ;
//				if ($productPrice == $mCarrier->getDeliveryPriceByWeight($totalCartWeight, $id_zone))
//				{
					if ($electedCarrier == NULL)
						$electedCarrier = $mCarrier;
					else if ($mCarrier->id < $electedCarrier->id)
						$electedCarrier = $mCarrier;
//				}
			}
		}
		return $electedCarrier;
	}

	/**
	 * Hack : Should be replacement in 1.2 finale for function Carrier::getCarriers in /classes/Carrier.php
	 * Get all carriers in a given language
	 *
	 * @param integer $id_lang Language id
	 * @param boolean $active Returns only active carriers when true
	 * @return array Carriers
	 */
	public static function getCarriers($id_lang, $active = false, $delete = false, $id_zone = false, $all = false)
	{
	 	if (!Validate::isBool($active))
	 		die(Tools::displayError());
	 	// MFR090202 - Fix SQL injection possibility following R�mi Gaillard remarks
		$sql = '
			SELECT c.*, cl.delay
			FROM `'._DB_PREFIX_.'carrier` c
			LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.intval($id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz  ON (cz.`id_carrier` = c.`id_carrier`)'.
			($id_zone ? 'LEFT JOIN `'._DB_PREFIX_.'zone` z  ON (z.`id_zone` = '.intval($id_zone).')' : '').'
			WHERE c.`deleted` '.($delete ? '= 1' : ' = 0').
			($active ? ' AND c.`active` = 1' : '').
			($id_zone ? ' AND cz.`id_zone` = '.intval($id_zone).'
			AND z.`active` = 1' : '').'
			'.($all ? NULL : 'AND c.`is_module` = 0').'
			GROUP BY c.`id_carrier`';
		$carriers = Db::getInstance()->ExecuteS($sql);
		
		if (is_array($carriers) AND count($carriers))
		{
			foreach ($carriers as $key => $carrier)
				if ($carrier['name'] == '0')
					$carriers[$key]['name'] = Configuration::get('PS_SHOP_NAME');
		}
		else
			$carriers = array();

		return $carriers;
	}
	
	/**
	 * Checks if a Dejala carrier already exists
	 */
	public static function carrierExists($dejalaConfig)
	{
		global $cookie;
		$id_zone = 1;
		$moduleCountryIsoCode = strtoupper($dejalaConfig->country);
		$countryID = Country::getByIso($moduleCountryIsoCode);
		if (intval($countryID))
			$id_zone = Country::getIdZone($countryID);
		$allCarriers = DejalaCarrierUtils::getCarriers(intval($cookie->id_lang), true, false, $id_zone, true);	
		foreach ($allCarriers as $carrier) {
			if (($carrier['name'] == 'dejala') && ($carrier['is_module'] == true)) return true ;
		}
		return false ;
	}
}

?>
