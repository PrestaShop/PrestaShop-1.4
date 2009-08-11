<?php

/**
  * Currency class, Currency.php
  * Currencies management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class		Currency extends ObjectModel
{
	public 		$id;

	/** @var string Name */
	public 		$name;

	/** @var string Iso code */
	public 		$iso_code;

	/** @var string Symbol for short display */
	public 		$sign;
	
	/** @var int bool used for displaying blank between sign and price */
	public		$blank;

	/** @var string Conversion rate from euros */
	public 		$conversion_rate;

	/** @var boolean True if currency has been deleted (staying in database as deleted) */
	public 		$deleted = 0;

	/** @var int ID used for displaying prices */
	public		$format;

	/** @var int bool Display decimals on prices */
	public		$decimals;

 	protected 	$fieldsRequired = array('name', 'iso_code', 'sign', 'conversion_rate', 'format', 'decimals');
 	protected 	$fieldsSize = array('name' => 32, 'iso_code' => 3, 'sign' => 8);
 	protected 	$fieldsValidate = array('name' => 'isGenericName', 'sign' => 'isGenericName',
		'format' => 'isUnsignedId', 'decimals' => 'isBool', 'conversion_rate' => 'isFloat', 'deleted' => 'isBool');

	protected 	$table = 'currency';
	protected 	$identifier = 'id_currency';

	public function getFields()
	{
		parent::validateFields();

		$fields['name'] = pSQL($this->name);
		$fields['iso_code'] = pSQL($this->iso_code);
		$fields['sign'] = pSQL($this->sign);
		$fields['format'] = intval($this->format);
		$fields['decimals'] = intval($this->decimals);
		$fields['blank'] = intval($this->blank);
		$fields['conversion_rate'] = floatval($this->conversion_rate);
		$fields['deleted'] = intval($this->deleted);

		return $fields;
	}

	public function deleteSelection($selection)
	{
		if (!is_array($selection) OR !Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
			die(Tools::displayError());
		$result = true;
		foreach ($selection AS $id)
		{
			$obj = new Currency(intval($id));
			$res[$id] = $obj->delete();
		}
		foreach ($res AS $value)
			if (!$value)
				return false;
		return true;
	}

	public function delete()
	{
		if ($this->id == Configuration::get('PS_CURRENCY_DEFAULT'))
		{
			$result = Db::getInstance()->getRow('SELECT `id_currency` FROM '._DB_PREFIX_.'currency WHERE `id_currency` != '.intval($this->id).' AND `deleted` = 0');
			if (!$result['id_currency'])
				return false;
			Configuration::updateValue('PS_CURRENCY_DEFAULT', $result['id_currency']);
		}
		$this->deleted = 1;
		return $this->update();
	}

	/**
	  * Return formated sign
	  *
	  * @param string $side left or right
	  * @return string formated sign
	  */
	public function getSign($side=NULL)
	{
		if (!$side)
			return $this->sign;
		$formated_strings = array(
			'left' => $this->sign.' ',
			'right' => ' '.$this->sign
		);
		$formats = array(
			1 => array('left' => &$formated_strings['left'], 'right' => ''),
			2 => array('left' => '', 'right' => &$formated_strings['right']),
			3 => array('left' => &$formated_strings['left'], 'right' => ''),
			4 => array('left' => '', 'right' => &$formated_strings['right']),
		);
		return ($formats[$this->format][$side]);
	}

	/**
	  * Return available currencies
	  *
	  * @return array Currencies
	  */
	static public function getCurrencies($object = false)
	{
		$tab = Db::getInstance()->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'currency`
		WHERE `deleted` = 0
		ORDER BY `name` ASC');
		if ($object)
			foreach ($tab as $key => $currency)
				$tab[$key] = new Currency($currency['id_currency']);
		return $tab;
	}
	
	static public function getPaymentCurrenciesSpecial($id_module)
	{
		return Db::getInstance()->getRow('
		SELECT mc.*
		FROM `'._DB_PREFIX_.'module_currency` mc
		WHERE mc.`id_module` = '.intval($id_module));
	}
	
	static public function getPaymentCurrencies($id_module)
	{
		return Db::getInstance()->ExecuteS('
		SELECT c.*
		FROM `'._DB_PREFIX_.'module_currency` mc
		LEFT JOIN `'._DB_PREFIX_.'currency` c ON c.`id_currency` = mc.`id_currency`
		WHERE c.`deleted` = 0
		AND mc.`id_module` = '.intval($id_module).'
		ORDER BY c.`name` ASC');
	}
	
	static public function checkPaymentCurrencies($id_module)
	{
		return Db::getInstance()->ExecuteS('
		SELECT mc.*
		FROM `'._DB_PREFIX_.'module_currency` mc
		WHERE mc.`id_module` = '.intval($id_module));
	}

	static public function getCurrency($id_currency)
	{
		return Db::getInstance()->getRow('
		SELECT *
		FROM `'._DB_PREFIX_.'currency`
		WHERE `deleted` = 0
		AND `id_currency` = '.intval($id_currency));
	}
	
	static public function getIdByIsoCode($iso_code)
	{
		$result = Db::getInstance()->getRow('
		SELECT `id_currency`
		FROM `'._DB_PREFIX_.'currency`
		WHERE `deleted` = 0
		AND `iso_code` = \''.pSQL($iso_code).'\'');
		return $result['id_currency'];
	}

	public function refreshCurrency($data, $isoCodeSource, $defaultCurrency)
	{
		if ($this->iso_code != $isoCodeSource)
		{
			/* Seeking for rate in feed */
			foreach ($data->currency AS $obj)
				if ($this->iso_code == strval($obj['iso_code']))
					$this->conversion_rate = round(floatval($obj['rate']) /  $defaultCurrency->conversion_rate, 6);
		}
		else
		{
			/* If currency is like isoCodeSource, setting it to default conversion rate */
			$this->conversion_rate = round(1 / floatval($defaultCurrency->conversion_rate), 6);
		}
		$this->update();
	}

	static public function refreshCurrenciesGetDefault($data, $isoCodeSource, $idCurrency)
	{
		$defaultCurrency = new Currency($idCurrency);

		/* Change defaultCurrency rate if not as currency of feed source */
		if ($defaultCurrency->iso_code != $isoCodeSource)
			foreach ($data->currency AS $obj)
				if ($defaultCurrency->iso_code == strval($obj['iso_code']))
					$defaultCurrency->conversion_rate = round(floatval($obj['rate']), 6);
		return $defaultCurrency;
	}

	static public function refreshCurrencies()
	{
		if (!$feed = @simplexml_load_file('http://www.prestashop.com/xml/currencies.xml'))
			return Tools::displayError('Cannot parse feed!');
		if (!$defaultCurrency = intval(Configuration::get('PS_CURRENCY_DEFAULT')))
			return Tools::displayError('No default currency!');
		$isoCodeSource = strval($feed->source['iso_code']);
		$currencies = self::getCurrencies(true);
		$defaultCurrency = self::refreshCurrenciesGetDefault($feed->list, $isoCodeSource, $defaultCurrency);
		foreach ($currencies as $currency)
			if ($currency->iso_code != $defaultCurrency->iso_code)
				$currency->refreshCurrency($feed->list, $isoCodeSource, $defaultCurrency);
	}
}

?>