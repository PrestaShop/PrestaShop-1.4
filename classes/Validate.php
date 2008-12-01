<?php

/**
  * Validation class, Validate.php
  * Check fields and data validity
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.0
  *
  */

class Validate
{
 	/**
	* Check for e-mail validity
	*
	* @param string $email e-mail address to validate
	* @return boolean Validity is ok or not
	*/
	static public function isEmail($email)
    {
    	return eregi('^[a-z0-9]+[._a-z0-9-]*@[a-z0-9]+[._a-z0-9-]*\.[a-z0-9]+$', $email);
    }

    /**
	* Check for module URL validity
	*
	* @param string $url module URL to validate
	* @param array $errors Reference array for catching errors
	* @return boolean Validity is ok or not
	*/
	static public function isModuleUrl($url, &$errors)
	{
		if (!$url OR $url == 'http://')
			$errors[] = Tools::displayError('please specify module URL');
		elseif (substr($url, -4) != '.tar')
			$errors[] = Tools::displayError('this is not a valid module URL (must end with .tar)');
		else
		{
			if ((strpos($url, 'http')) === false)
				$url = 'http://'.$url;
			if (!is_array(@get_headers($url)))
				$errors[] = Tools::displayError('invalid URL');
		}
		if (!sizeof($errors))
			return true;
		return false;

	}

	/**
	* Check for MD5 string validity
	*
	* @param string $md5 MD5 string to validate
	* @return boolean Validity is ok or not
	*/
	static public function isMd5($md5)
	{
		return eregi('^[a-z0-9]{32}$', $md5);
	}

	/**
	* Check for SHA1 string validity
	*
	* @param string $sha1 SHA1 string to validate
	* @return boolean Validity is ok or not
	*/
	static public function isSha1($sha1)
	{
		return eregi('^[a-z0-9]{40}$', $sha1);
	}

	/**
	* Check for a float number validity
	*
	* @param float $float Float number to validate
	* @return boolean Validity is ok or not
	*/
    static public function isFloat($float)
    {
		$f = floatval($float);
		return strval($f) == strval($float);
	}
	
    static public function isUnsignedFloat($float)
    {
		$f = floatval($float);
		return strval($f) == strval($float) AND $f >= 0;
	}

	/**
	* Check for a float number validity
	*
	* @param float $float Float number to validate
	* @return boolean Validity is ok or not
	*/
    static public function isOptFloat($float)
    {
		return empty($float) OR self::isFloat($float);
	}

	/**
	* Check for a carrier name validity
	*
	* @param string $name Carrier name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isCarrierName($name)
	{
		return empty($name) OR eregi('^[^<>;=#{}]*$', $name);
	}

	/**
	* Check for an image size validity
	*
	* @param string $size Image size to validate
	* @return boolean Validity is ok or not
	*/
	static public function isImageSize($size)
	{
		return ereg('^[0-9]{1,4}$', $size);
	}

	static public function isOptId($id)
	{
		return empty($id) OR self::isUnsignedId($id);
	}

	/**
	* Check for name validity
	*
	* @param string $name Name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isName($name)
	{
		return preg_match('/^[^0-9!<>,;?=+()@#"°{}_$%:]*$/u', stripslashes($name));
	}

	/**
	* Check for hook name validity
	*
	* @param string $hook Hook name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isHookName($hook)
	{
		return eregi('^[a-z0-9_-]+$', $hook);
	}

	/**
	* Check for sender name validity
	*
	* @param string $mailName Sender name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isMailName($mailName)
	{
		return eregi('^[^<>;=#{}]*$', $mailName);
	}

	/**
	* Check for e-mail subject validity
	*
	* @param string $mailSubject e-mail subject to validate
	* @return boolean Validity is ok or not
	*/
	static public function isMailSubject($mailSubject)
	{
		return eregi('^[^<>;{}]*$', $mailSubject);
	}

	/**
	* Check for module name validity
	*
	* @param string $moduleName Module name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isModuleName($moduleName)
	{
		return eregi('^[a-z0-9_-]+$', $moduleName);
	}

	/**
	* Check for template name validity
	*
	* @param string $tplName Template name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isTplName($tplName)
	{
		return eregi('^[a-z0-9_-]+$', $tplName);
	}

	static public function isTplFileName($tplFileName)
	{
		return preg_match('/^[a-zA-Z0-9\/_.-]+/', $tplFileName);
	}

	/**
	* Check for icon file validity
	*
	* @param string $icon Icon filename to validate
	* @return boolean Validity is ok or not
	*/
	static public function isIconFile($icon)
	{
		return eregi('^[a-z0-9_-]+\.[gif|jpg|jpeg|png]$', $icon);
	}

	/**
	* Check for ico file validity
	*
	* @param string $icon Icon filename to validate
	* @return boolean Validity is ok or not
	*/
	static public function isIcoFile($icon)
	{
		return eregi('^[a-z0-9_-]+\.ico$', $icon);
	}

	/**
	* Check for image type name validity
	*
	* @param string $type Image type name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isImageTypeName($type)
	{
		return eregi('^[a-z0-9_ -]+$', $type);
	}

	/**
	* Check for price validity
	*
	* @param string $price Price to validate
	* @return boolean Validity is ok or not
	*/
	static public function isPrice($price)
	{
		return ereg('^[0-9]{1,10}(\.[0-9]{1,9})?$', $price);
	}

	/**
	* Check for language code (ISO) validity
	*
	* @param string $isoCode Language code (ISO) to validate
	* @return boolean Validity is ok or not
	*/
	static public function isLanguageIsoCode($isoCode)
	{
		return eregi('^[a-z]{2,3}$', $isoCode);
	}

	/**
	* Check for gender code (ISO) validity
	*
	* @param string $isoCode Gender code (ISO) to validate
	* @return boolean Validity is ok or not
	*/
	static public function isGenderIsoCode($isoCode)
	{
		return ereg('^[0|1|2|9]$', $isoCode);
	}

	/**
	* Check for gender code (ISO) validity
	*
	* @param string $isoCode Gender code (ISO) to validate
	* @return boolean Validity is ok or not
	*/
	static public function isGenderName($genderName)
	{
		return eregi('^[a-z.]+$', $genderName);
	}

	/**
	* Check for discount coupon name validity
	*
	* @param string $discountName Discount coupon name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isDiscountName($discountName)
	{
		return eregi('^[^!<>,;?=+()@"°{}_$%:]{3,32}$', $discountName);
	}

	/**
	* Check for product or category name validity
	*
	* @param string $name Product or category name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isCatalogName($name)
	{
		return eregi('^[^<>;=#{}]*$', $name);
	}

	/**
	* Check for a message validity
	*
	* @param string $message Message to validate
	* @return boolean Validity is ok or not
	*/
	static public function isMessage($message)
	{
		return eregi('^([^<>#{}]|<br />)*$', $message);
	}

	/**
	* Check for a country name validity
	*
	* @param string $name Country name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isCountryName($name)
	{
		return eregi('^[a-z -]+$', $name);
	}

	/**
	* Check for a link (url-rewriting only) validity
	*
	* @param string $link Link to validate
	* @return boolean Validity is ok or not
	*/
	static public function isLinkRewrite($link)
	{
		return empty($link) OR eregi('^[_a-z0-9-]+$', $link);
	}

	/**
	* Check for zone name validity
	*
	* @param string $name Zone name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isZoneName($name)
	{
		return eregi('^[a-z -()]+$', $name);
	}

	/**
	* Check for a postal address validity
	*
	* @param string $address Address to validate
	* @return boolean Validity is ok or not
	*/
	static public function isAddress($address)
	{
		return empty($address) OR eregi('^[^!<>?=+@{}_$%]*$', $address);
	}

	/**
	* Check for city name validity
	*
	* @param string $city City name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isCityName($city)
	{
		return eregi('^[^!<>;?=+@#"°{}_$%0-9]*$', $city);
	}

	/**
	* Check for search query validity
	*
	* @param string $search Query to validate
	* @return boolean Validity is ok or not
	*/
	static public function isValidSearch($search)
	{
		return eregi('^[^<>;=#{}]{0,64}$', $search);
	}

	/**
	* Check for standard name validity
	*
	* @param string $name Name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isGenericName($name)
	{
		return empty($name) OR eregi('^[^<>;=#{}]*$', $name);
	}

	/**
	* Check for HTML field validity (no XSS please !)
	*
	* @param string $html HTML field to validate
	* @return boolean Validity is ok or not
	*/
	static public function isCleanHtml($html)
	{
		return !eregi('<[ \t\n]*script', $html);
	}

	/**
	* Check for product reference validity
	*
	* @param string $reference Product reference to validate
	* @return boolean Validity is ok or not
	*/
	static public function isReference($reference)
	{
		return eregi('^[^<>;={}]*$', $reference);
	}

	/**
	* Check for password validity
	*
	* @param string $passwd Password to validate
	* @return boolean Validity is ok or not
	*/
	static public function isPasswd($passwd, $size = 5)
	{
		return eregi('^[.a-z_0-9-]{'.$size.',32}$', $passwd);
	}

	static public function isPasswdAdmin($passwd)
	{
		return self::isPasswd($passwd, 8);
	}

	/**
	* Check for configuration key validity
	*
	* @param string $configName Configuration key to validate
	* @return boolean Validity is ok or not
	*/
	static public function isConfigName($configName)
	{
		return eregi('^[a-z_0-9-]+$', $configName);
	}

	/**
	* Check for date validity
	*
	* @param string $date Date to validate
	* @return boolean Validity is ok or not
	*/
	static public function isDate($date)
	{
		if (!preg_match('/^([0-9]{4})-((0?[1-9])|(1[0-2]))-((0?[1-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $matches))
			return false;
		return checkdate(intval($matches[2]), intval($matches[5]), intval($matches[0]));
	}

	/**
	* Check for birthDate validity
	*
	* @param string $date birthdate to validate
	* @return boolean Validity is ok or not
	*/
	static public function isBirthDate($date)
	{
	 	if (empty($date))
	 		return true;
	 	if (preg_match('/^([0-9]{4})-((0?[1-9])|(1[0-2]))-((0?[1-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $birthDate)) {
			 if ($birthDate[1] >= date('Y') - 9)
	 			return false;
	 		return true;
	 	}
		return false;
	}

	/**
	* Check for boolean validity
	*
	* @param boolean $bool Boolean to validate
	* @return boolean Validity is ok or not
	*/
	static public function isBool($bool)
	{
		return is_null($bool) OR is_bool($bool) OR ereg('^[0|1]{1}$', $bool);
	}

	/**
	* Check for phone number validity
	*
	* @param string $phoneNumber Phone number to validate
	* @return boolean Validity is ok or not
	*/
	static public function isPhoneNumber($phoneNumber)
	{
		return ereg('^[+0-9. ()-]*$', $phoneNumber);
	}

	/**
	* Check for barcode validity (EAN-13)
	*
	* @param string $ean13 Barcode to validate
	* @return boolean Validity is ok or not
	*/
	static public function isEan13($ean13)
	{
		return !$ean13 OR ereg('[0-9]{0,13}', $ean13);
	}

	/**
	* Check for postal code validity
	*
	* @param string $postcode Postal code to validate
	* @return boolean Validity is ok or not
	*/
	static public function isPostCode($postcode)
	{
		return eregi('^[a-z 0-9-]+$', $postcode);
	}

	/**
	* Check for table or identifier validity
	* Mostly used in database for ordering : ASC / DESC
	*
	* @param string $orderWay Keyword to validate
	* @return boolean Validity is ok or not
	*/
	static public function isOrderWay($orderWay)
	{
		return eregi('^ASC|DESC$', $orderWay);
	}

	/**
	* Check for table or identifier validity
	* Mostly used in database for ordering : ORDER BY field
	*
	* @param string $orderBy Field to validate
	* @return boolean Validity is ok or not
	*/
	static public function isOrderBy($orderBy)
	{
		return eregi('^[a-z0-9_-]+$', $orderBy);
	}

	/**
	* Check for table or identifier validity
	* Mostly used in database for table names and id_table
	*
	* @param string $table Table/identifier to validate
	* @return boolean Validity is ok or not
	*/
	static public function isTableOrIdentifier($table)
	{
		return eregi('^[a-z0-9_-]+$', $table);
	}

	/**
	* Check for values list validity
	* Mostly used in database for insertions (A,B,C),(A,B,C)...
	*
	* @param string $list List to validate
	* @return boolean Validity is ok or not
	*/
	static public function isValuesList($list)
	{
		return true;
		return eregi('^[0-9,\'(). NULL]+$', $list);
	}

	/**
	* Check for tags list validity
	*
	* @param string $list List to validate
	* @return boolean Validity is ok or not
	*/
	static public function isTagsList($list)
	{
		return preg_match('/^[^!<>;?=+#"°{}_$%]*$/u', $list);
	}

	/**
	* Check for an integer validity
	*
	* @param integer $id Integer to validate
	* @return boolean Validity is ok or not
	*/
	static public function isInt($int)
	{
		return (int) ((string) $int) OR $int == 0;
	}

	/**
	* Check for an integer validity (unsigned)
	*
	* @param integer $id Integer to validate
	* @return boolean Validity is ok or not
	*/
	static public function isUnsignedInt($int)
	{
		return (int) ((string) $int) AND $int < 4294967296  AND $int >= 0;
	}

	/**
	* Check for an integer validity (unsigned)
	* Mostly used in database for auto-increment
	*
	* @param integer $id Integer to validate
	* @return boolean Validity is ok or not
	*/
	static public function isUnsignedId($id)
	{
		return is_numeric($id) AND intval($id) > 0 AND intval($id) < 4294967296;
	}

	static public function isNullOrUnsignedId($id)
	{
		return is_null($id) OR self::isUnsignedId($id);
	}

	/**
	* Check object validity
	*
	* @param integer $object Object to validate
	* @return boolean Validity is ok or not
	*/
	static public function isLoadedObject($object)
	{
		return is_object($object) AND $object->id;
	}

	/**
	* Check object validity
	*
	* @param integer $object Object to validate
	* @return boolean Validity is ok or not
	*/
	static public function isColor($color)
	{
		return eregi('^(#[0-9A-Fa-f]{6}|[[:alnum:]]*)$', $color);
	}

	/**
	* Check object validity
	*
	* @param integer $object Object to validate
	* @return boolean Validity is ok or not
	*/
	static public function isUrl($url)
	{
		return eregi('^[[:alnum:]:#%&()_=./? +-]*$', $url);
	}

	/**
	* Check object validity
	*
	* @param integer $object Object to validate
	* @return boolean Validity is ok or not
	*/
	static public function isAbsoluteUrl($url)
	{
		return eregi('^(http://)[[:alnum:]]|[#%&()_=.? +-@]$', $url);
	}

	/**
	* Check for standard name file validity
	*
	* @param string $name Name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isFileName($name)
	{
		return eregi('^[a-z0-9_.-]*$', $name);
	}

	/**
	* Check for admin panel tab name validity
	*
	* @param string $name Name to validate
	* @return boolean Validity is ok or not
	*/
	static public function isTabName($name)
	{
		return eregi('^[a-z0-9_-]*$', $name);
	}

	static public function isWeightUnit($unit)
	{
		return eregi('^[[:alpha:]]{1,3}$', $unit);
	}

	static public function isProtocol($protocol)
	{
		return eregi('^http(s?)://$', $protocol);
	}


	static public function isSubDomainName($subDomainName)
	{
		return preg_match('/^[[:alnum:]]*$/', $subDomainName);
	}

	static public function isVoucherDescription($text)
	{
		return eregi('^([^<>{}]|<br />)*$', $text);
	}
	
	/**
	* Check if the char values is a granularity value
	*
	* @param char $value
	* @return boolean Validity is ok or not
	*/
	static public function isGranularityValue($value)
	{
		return (!is_null($value) AND ($value === "d" OR $value === "m" OR $value === "y"));
	}
	
	/**
	* Check if the value is a sort direction value (DESC/ASC)
	*
	* @param char $value
	* @return boolean Validity is ok or not
	*/
	static public function IsSortDirection($value)
	{
		return (!is_null($value) AND ($value === 'ASC' OR $value === 'DESC'));
	}

	/**
	* Customization fields' label validity
	*
	* @param integer $object Object to validate
	* @return boolean Validity is ok or not
	*/
	static public function isLabel($label)
	{
		return (preg_match('/^[^{}<>]*$/', $label));
	}
}

?>
