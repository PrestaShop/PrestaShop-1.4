<?php

/**
  * Tools class, Tools.php
  * Various tools
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.1
  *
  */

class Tools
{
	/**
	* Random password generator
	*
	* @param integer $length Desired length (optional)
	* @return string Password
	*/
	static public function passwdGen($length = 8)
	{
		$str = 'abcdefghijkmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		for ($i = 0, $passwd = ''; $i < $length; $i++)
			$passwd .= Tools::substr($str, mt_rand(0, Tools::strlen($str) - 1), 1);
		return $passwd;
	}

	/**
	* Redirect user to another page
	*
	* @param string $url Desired URL
	* @param string $baseUri Base URI (optional)
	*/
	static public function redirect($url, $baseUri = __PS_BASE_URI__)
	{
		if (isset($_SERVER['HTTP_REFERER']) AND ($url == $_SERVER['HTTP_REFERER']))
			header('Location: '.$_SERVER['HTTP_REFERER']);
		else
			header('Location: '.$baseUri.$url);
		exit;
	}

	/**
	* Redirect url wich allready PS_BASE_URI
	*
	* @param string $url Desired URL
	*/
	static public function redirectLink($url)
	{
		header('Location: '.$url);
		exit;
	}

	/**
	* Redirect user to another admin page
	*
	* @param string $url Desired URL
	*/
	static public function redirectAdmin($url)
	{
		header('Location: '.$url);
		exit;
	}

	/**
	* Get a value from $_POST / $_GET
	* if unavailable, take a default value
	*
	* @param string $key Value key
	* @param mixed $defaultValue (optional)
	* @return mixed Value
	*/
	static public function getValue($key, $defaultValue = false)
	{
		global $cookie;

	 	if (!isset($key) OR empty($key) OR !is_string($key))
			return false;
		$ret = (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $defaultValue));

		if (is_string($ret) === true)
			$ret = urldecode(preg_replace('/((\%5C0+)|(\%00+))/i', '', urlencode($ret)));

		return is_array($ret) ? $ret : stripslashes($ret);
	}

	static public function getIsset($key)
	{
	 	if (!isset($key) OR empty($key) OR !is_string($key))
			return false;
	 	return isset($_POST[$key]) ? true : (isset($_GET[$key]) ? true : false);
	}

	/**
	* Change language in cookie while clicking on a flag
	*/
	static public function setCookieLanguage()
	{
		global $cookie;

		/* If language does not exist or is disabled, erase it */
		if ($cookie->id_lang)
		{
			$lang = new Language(intval($cookie->id_lang));
			if (!Validate::isLoadedObject($lang) OR !$lang->active)
				$cookie->id_lang = NULL;
		}
		
		/* Automatically detect language if not already defined */
		if (!$cookie->id_lang AND isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$array = split(',', Tools::strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));
			if (Validate::isLanguageIsoCode($array[0]))
			{
				$lang = new Language(intval(Language::getIdByIso(strval($array[0]))));
				if (Validate::isLoadedObject($lang) AND $lang->active)
					$cookie->id_lang = intval($lang->id);
			}
		}
		
		/* If language file not present, you must use default language file */
		if (!$cookie->id_lang OR !Validate::isUnsignedId($cookie->id_lang))
			$cookie->id_lang = Configuration::get('PS_LANG_DEFAULT');
		
		$iso = Language::getIsoById($cookie->id_lang);
		@include_once(_PS_TRANSLATIONS_DIR_.$iso.'/fields.php');
		@include_once(_PS_TRANSLATIONS_DIR_.$iso.'/errors.php');
		@include_once(_PS_THEME_DIR_.'lang/'.$iso.'.php');
		return $iso;
	}
	
	static public function switchLanguage()
	{
		global $cookie;
		
		/* Language switching */
		if ($id_lang = intval(Tools::getValue('id_lang')) AND Validate::isUnsignedId($id_lang))
		{
			$cookie->id_lang = $id_lang;
			$cookie->update();
			$link = new Link();
			if ($id_product = intval(Tools::getValue('id_product')) AND !isset($_GET['adminlang']))
				$url = $link->getProductLink(new Product($id_product, false, $id_lang));
			elseif ($id_category = intval(Tools::getValue('id_category')) AND !isset($_GET['adminlang']))
				$url = $link->getCategoryLink(new Category($id_category, $id_lang));
			elseif ($id_cms = intval(Tools::getValue('id_cms')) AND !isset($_GET['adminlang']))
				$url = $link->getCMSLink(new CMS($id_cms, $id_lang));
			else
			{
				$n = 0;
				$url = $_SERVER['PHP_SELF'];
				unset($_GET['id_lang'], $_GET['adminlang']);
				foreach ($_GET AS $k => $value)
					$url .= ((!$n++) ? '?' : '&').urlencode(stripslashes($k)).'='.urlencode(stripslashes($value));
			}
			Tools::redirectLink($url);
		}
	}

	static public function setCurrency()
	{
		global $cookie;

		if (self::isSubmit('SubmitCurrency'))
			if (isset($_POST['id_currency']) AND is_numeric($_POST['id_currency']))
			{
				$currency = new Currency(intval($_POST['id_currency']));
				if (is_object($currency) AND $currency->id AND !$currency->deleted)
					$cookie->id_currency = intval($currency->id);
			}

		if ($cookie->id_currency)
		{
			$currency = new Currency(intval($cookie->id_currency));
			if (is_object($currency) AND $currency->id AND intval($currency->deleted) != 1)
				return $currency;
		}
		$currency = new Currency(intval(Configuration::get('PS_CURRENCY_DEFAULT')));
		if (is_object($currency) AND $currency->id)
			$cookie->id_currency = intval($currency->id);
		return $currency;
	}

	/**
	* Return price with currency sign for a given product
	*
	* @param float $price Product price
	* @param object $currency Current currency object
	* @param boolean $convert Need to convert currency sign to UTF8 (for FPDF), (optional)
	* @return string Price with currency sign
	*/
	static public function displayPrice($price, $currency, $no_utf8 = false, $convert = true)
	{
		/* if you modified this function, don't forget to modify the Javascript function formatCurrency (in tools.js) */
		if ($convert)
			$price = self::convertPrice($price, $currency);
		if (is_int($currency))
			$currency = new Currency(intval($currency));
		$c_char = (is_array($currency) ? $currency['sign'] : $currency->sign);
		$c_format = (is_array($currency) ? $currency['format'] : $currency->format);
		$c_decimals = (is_array($currency) ? intval($currency['decimals']) : intval($currency->decimals)) * _PS_PRICE_DISPLAY_PRECISION_;
		$c_blank = (is_array($currency) ? $currency['blank'] : $currency->blank);
		$blank = ($c_blank ? ' ' : '');
		$ret = 0;
		if (($isNegative = ($price < 0)))
			$price *= -1;
		switch ($c_format)
	 	{
	 	 	/* X 0,000.00 */
	 	 	case 1:
				$ret = $c_char.$blank.number_format($price, $c_decimals, '.', ',');
				break;
			/* 0 000,00 X*/
			case 2:
				$ret = number_format($price, $c_decimals, ',', ' ').$blank.$c_char;
				break;
			/* X 0.000,00 */
			case 3:
				$ret = $c_char.$blank.number_format($price, $c_decimals, ',', '.');
				break;
			/* 0,000.00 X */
			case 4:
				$ret = number_format($price, $c_decimals, '.', ',').$blank.$c_char;
				break;
		}
		if ($isNegative)
			$ret = '-'.$ret;
		if ($no_utf8)
			return str_replace('€', chr(128), $ret);
		return $ret;
	}
	
	static public function displayPriceSmarty($params, &$smarty)
	{
		$currency = new Currency(intval($params['currency']));
		if (Validate::isLoadedObject($currency))
			return self::displayPrice($params['price'], $currency, false, false);
		return 0;
	}

	/**
	* Return price converted
	*
	* @param float $price Product price
	* @param object $currency Current currency object
	*/
	static public function convertPrice($price, $currency)
	{
		$c_id = (is_array($currency) ? $currency['id_currency'] : $currency->id);
		$c_rate = (is_array($currency) ? $currency['conversion_rate'] : $currency->conversion_rate);
		if ($c_id != intval(Configuration::get('PS_CURRENCY_DEFAULT')))
			$price *= $c_rate;
		return $price;
	}

	/**
	* Display date regarding to language preferences
	*
	* @param array $params Date, format...
	* @param object $smarty Smarty object for language preferences
	* @return string Date
	*/
	static public function dateFormat($params, &$smarty)
	{
		return Tools::displayDate($params['date'], $smarty->ps_language->id, $params['full']);
	}

	/**
	* Display date regarding to language preferences
	*
	* @param string $date Date to display format UNIX
	* @param integer $id_lang Language id
	* @param boolean $full With time or not (optional)
	* @return string Date
	*/
	static public function displayDate($date, $id_lang, $full = false, $separator='-')
	{
	 	if (!$date OR !strtotime($date))
	 		return $date;
		if (!Validate::isDate($date) OR !Validate::isBool($full))
			die (Tools::displayError('Invalid date'));
	 	$tmpTab = explode($separator, substr($date, 0, 10));
	 	$hour = ' '.substr($date, -8);

		$language = Language::getLanguage(intval($id_lang));
	 	if ($language AND strtolower($language['iso_code']) == 'fr')
	 		return ($tmpTab[2].'-'.$tmpTab[1].'-'.$tmpTab[0].($full ? $hour : ''));
	 	else
	 		return ($tmpTab[0].'-'.$tmpTab[1].'-'.$tmpTab[2].($full ? $hour : ''));
	}

	/**
	* Sanitize a string
	*
	* @param string $string String to sanitize
	* @param boolean $full String contains HTML or not (optional)
	* @return string Sanitized string
	*/
	static public function safeOutput($string, $html = false)
	{
	 	if (!$html)
			$string = @htmlentities(strip_tags($string), ENT_QUOTES, 'utf-8');
		return $string;
	}

	static public function htmlentitiesUTF8($string)
	{
		if (is_array($string))
			return array_map(array('Tools', 'htmlentitiesUTF8'), $string);
		return htmlentities($string, ENT_QUOTES, 'utf-8'); 
	}

	static public function safePostVars()
	{
		$_POST = array_map(array('Tools', 'htmlentitiesUTF8'), $_POST);
	}

	/**
	* Delete directory and subdirectories
	*
	* @param string $dirname Directory name
	*/
	static public function deleteDirectory($dirname)
	{
		$files = scandir($dirname);
		foreach ($files as $file)
			if ($file != '.' AND $file != '..')
			{
				if (is_dir($file))
					self::deleteDirectory($file);
				elseif (file_exists($file))
					unlink($file);
				else
					echo 'Unable to delete '.$file;
			}
		rmdir($dirname);
	}

	/**
	* Display an error according to an error code
	*
	* @param integer $code Error code
	*/
	static public function displayError($string = 'Hack attempt', $htmlentities = true)
	{
		global $_ERRORS;

		if (!is_array($_ERRORS))
			return str_replace('"', '&quot;', $string);
		$key = md5(str_replace('\'', '\\\'', $string));
		$str = (isset($_ERRORS) AND is_array($_ERRORS) AND key_exists($key, $_ERRORS)) ? ($htmlentities ? htmlentities($_ERRORS[$key], ENT_COMPAT, 'UTF-8') : $_ERRORS[$key]) : $string;
		return str_replace('"', '&quot;', stripslashes($str));
	}

	/**
	* Display an error with detailed object
	*
	* @param object $object Object to display
	*/
	static public function dieObject($object, $kill = true)
	{
		echo '<pre style="text-align: left;">';
		print_r($object);
		echo '</pre><br />';
		if ($kill)
			die('END');
	}
	
	/**
	* ALIAS OF dieObject() - Display an error with detailed object
	*
	* @param object $object Object to display
	*/
	static public function d($object, $kill = true)
	{
		self::dieObject($object, $kill = true);
	}
	
	/**
	* ALIAS OF dieObject() - Display an error with detailed object but don't stop the execution
	*
	* @param object $object Object to display
	*/
	static public function p($object)
	{
		self::dieObject($object, false);
	}

	/**
	* Check if submit has been posted
	*
	* @param string $submit submit name
	*/
	static public function isSubmit($submit)
	{
		return (
			isset($_POST[$submit]) OR isset($_POST[$submit.'_x']) OR isset($_POST[$submit.'_y'])
			OR isset($_GET[$submit]) OR isset($_GET[$submit.'_x']) OR isset($_GET[$submit.'_y'])
		);
	}

	/**
	* Get meta tages for a given page
	*
	* @param integer $id_lang Language id
	* @return array Meta tags
	*/
	static public function getMetaTags($id_lang)
	{
		global $maintenance;

		if (!$maintenance)
		{
		 	/* Products specifics meta tags */
			if ($id_product = Tools::getValue('id_product'))
			{
				$row = Db::getInstance()->getRow('
				SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`, `description_short`
				FROM `'._DB_PREFIX_.'product_lang`
				WHERE id_lang = '.intval($id_lang).' AND id_product = '.intval($id_product));
				if ($row)
				{
					if (empty($row['meta_description']))
						$row['meta_description'] = strip_tags($row['description_short']);
					return self::completeMetaTags($row, $row['name']);
				}
			}

			/* Categories specifics meta tags */
			elseif ($id_category = Tools::getValue('id_category'))
			{
				$row = Db::getInstance()->getRow('
				SELECT `name`, `meta_title`, `meta_description`, `meta_keywords`, `description`
				FROM `'._DB_PREFIX_.'category_lang`
				WHERE id_lang = '.intval($id_lang).' AND id_category = '.intval($id_category));
				if ($row)
				{
					if (empty($row['meta_description']))
						$row['meta_description'] = strip_tags($row['description']);
					return self::completeMetaTags($row, Category::hideCategoryPosition($row['name']));
				}
			}

			/* CMS specifics meta tags */
			elseif ($id_cms = Tools::getValue('id_cms'))
			{
				$row = Db::getInstance()->getRow('
				SELECT `meta_title`, `meta_description`, `meta_keywords`
				FROM `'._DB_PREFIX_.'cms_lang`
				WHERE id_lang = '.intval($id_lang).' AND id_cms = '.intval($id_cms));
				if ($row)
				{
					$row['meta_title'] = Configuration::get('PS_SHOP_NAME').' - '.$row['meta_title'];
					return self::completeMetaTags($row, $row['meta_title']);
				}
			}
		}
		/* Default meta tags */
		return Tools::getHomeMetaTags($id_lang);
	}

	/**
	* Get meta tags for a given page
	*
	* @param integer $id_lang Language id
	* @return array Meta tags
	*/
	static public function getHomeMetaTags($id_lang)
	{
		global $cookie, $page_name;

		/* Metas-tags */
		$metas = Meta::getMetaByPage($page_name, $id_lang);
		$ret['meta_title'] = (isset($metas['title']) AND $metas['title']) ? Configuration::get('PS_SHOP_NAME').' - '.$metas['title'] : Configuration::get('PS_SHOP_NAME');
		$ret['meta_description'] = (isset($metas['description']) AND $metas['description']) ? $metas['description'] : '';
		$ret['meta_keywords'] = (isset($metas['keywords']) AND $metas['keywords']) ? $metas['keywords'] :  '';
		return $ret;
	}


	static public function completeMetaTags($metaTags, $defaultValue)
	{
		global $cookie;

		if ($metaTags['meta_title'] == NULL)
			$metaTags['meta_title'] = Configuration::get('PS_SHOP_NAME').' - '.$defaultValue;
		if ($metaTags['meta_description'] == NULL)
			$metaTags['meta_description'] = Configuration::get('PS_META_DESCRIPTION', intval($cookie->id_lang)) ? Configuration::get('PS_META_DESCRIPTION', intval($cookie->id_lang)) : '';
		if ($metaTags['meta_keywords'] == NULL)
			$metaTags['meta_keywords'] = Configuration::get('PS_META_KEYWORDS', intval($cookie->id_lang)) ? Configuration::get('PS_META_KEYWORDS', intval($cookie->id_lang)) : '';
		return $metaTags;
	}

	/**
	* Encrypt password
	*
	* @param object $object Object to display
	*/
	static public function encrypt($passwd)
	{
		return md5(pSQL(_COOKIE_KEY_.$passwd));
	}

	/**
	* Get token to prevent CSRF
	*
	* @param string $token token to encrypt
	*/
	static public function getToken($page = true)
	{
		global $cookie;
		if ($page === true)
			return (Tools::encrypt($cookie->id_customer.$cookie->passwd.$_SERVER['SCRIPT_NAME']));
		else
			return (Tools::encrypt($cookie->id_customer.$cookie->passwd.$page));
	}
	
	/**
	* Encrypt password
	*
	* @param object $object Object to display
	*/
	static public function getAdminToken($string)
	{
		return !empty($string) ? Tools::encrypt($string) : false;
	}

	/**
	* Get the user's journey
	*
	* @param integer category id
	* @param string finish of the path
	*/
	static public function getPath($id_category, $path = '')
	{
		global $link, $cookie;
		$category = new Category(intval($id_category), intval($cookie->id_lang));
		if (!Validate::isLoadedObject($category))
			die (Tools::displayError());
		if ($category->id == 1)
			return '<span class="navigation_end">'.$path.'</span>';
		$pipe = (Configuration::get('PS_NAVIGATION_PIPE') ? Configuration::get('PS_NAVIGATION_PIPE') : '>');
		$category_name = Category::hideCategoryPosition($category->name);
		if ($path != $category_name)
			$path = '<a href="'.Tools::safeOutput($link->getCategoryLink($category)).'">'.$category_name.'</a> '.$pipe.' '.$path;
		return Tools::getPath(intval($category->id_parent), $path);
	}

	/**
	* Stats for admin panel
	*
	* @return integer Categories total
	*/

	static public function getCategoriesTotal()
	{
		$row = Db::getInstance()->getRow('SELECT COUNT(`id_category`) AS total FROM `'._DB_PREFIX_.'category`');
		return intval($row['total']);
	}

	/**
	* Stats for admin panel
	*
	* @return integer Products total
	*/

	static public function getProductsTotal()
	{
		$row = Db::getInstance()->getRow('SELECT COUNT(`id_product`) AS total FROM `'._DB_PREFIX_.'product`');
		return intval($row['total']);
	}

	/**
	* Stats for admin panel
	*
	* @return integer Customers total
	*/

	static public function getCustomersTotal()
	{
		$row = Db::getInstance()->getRow('SELECT COUNT(`id_customer`) AS total FROM `'._DB_PREFIX_.'customer`');
		return intval($row['total']);
	}

	/**
	* Stats for admin panel
	*
	* @return integer Orders total
	*/

	static public function getOrdersTotal()
	{
		$row = Db::getInstance()->getRow('SELECT COUNT(`id_order`) AS total FROM `'._DB_PREFIX_.'orders`');
		return intval($row['total']);
	}

	/*
	** Historyc translation function kept for compatibility
	** Removing soon
	*/
	static public function historyc_l($key, $translations)
	{
		global $cookie;
		if (!$translations OR !is_array($translations))
			die(Tools::displayError());
		$iso = strtoupper(Language::getIsoById($cookie->id_lang));
		$lang = key_exists($iso, $translations) ? $translations[$iso] : false;
		return (($lang AND is_array($lang) AND key_exists($key, $lang)) ? stripslashes($lang[$key]) : $key);
	}

	static public function link_rewrite($str, $utf8_decode = false)
	{
		$purified = '';
		$length = Tools::strlen($str);
		if ($utf8_decode)
			$str = utf8_decode($str);
		for ($i = 0; $i < $length; $i++)
		{
			$char = Tools::substr($str, $i, 1);
			if (Tools::strlen(htmlentities($char)) > 1)
			{
				$entity = htmlentities($char, ENT_COMPAT, 'UTF-8');
				$purified .= $entity{1};
			}
			elseif (preg_match('|[[:alpha:]]{1}|u', $char))
				$purified .= $char;
			elseif (preg_match('<[[:digit:]]|-{1}>', $char))
				$purified .= $char;
			elseif ($char == ' ')
				$purified .= '-';
		}
		return $purified;
	}

	/**
	* Truncate strings
	*
	* @param string $str
	* @param integer $maxLen Max length
	* @param string $suffix Suffix optional
	* @return string $str truncated
	*/
	/* CAUTION : Use it only on module hookEvents.
	** For other purposes use the smarty function instead */
	static public function truncate($str, $maxLen, $suffix = '...')
	{
	 	if (Tools::strlen($str) <= $maxLen)
	 		return $str;
	 	$str = utf8_decode($str);
	 	return (utf8_encode(substr($str, 0, $maxLen - Tools::strlen($suffix)).$suffix));
	}

	/**
	* Generate date form
	*
	* @param integer $year Year to select
	* @param integer $month Month to select
	* @param integer $day Day to select
	* @return array $tab html data with 3 cells :['days'], ['months'], ['years']
	*
	*/
	static public function dateYears()
	{
		for ($i = date('Y') - 10; $i >= 1900; $i--)
			$tab[] = $i;
		return $tab;
	}

	static public function dateDays()
	{
		for ($i = 1; $i != 32; $i++)
			$tab[] = $i;
		return $tab;
	}

	static public function dateMonths()
	{
		for ($i = 1; $i != 13; $i++)
			$tab[$i] = date('F', mktime(0, 0, 0, $i, date('m'), date('Y')));
		return $tab;
	}

	static public function hourGenerate($hours, $minutes, $seconds)
	{
	    return implode(':', array($hours, $minutes, $seconds));
	}

	static public function dateFrom($date)
	{
		$tab = explode(' ', $date);
		if (!isset($tab[1]))
		    $date .= ' ' . Tools::hourGenerate(0, 0, 0);
		return $date;
	}

	static public function dateTo($date)
	{
		$tab = explode(' ', $date);
		if (!isset($tab[1]))
		    $date .= ' ' . Tools::hourGenerate(23, 59, 59);
		return $date;
	}

	static public function getExactTime()
	{
		return time()+microtime();
	}

	static function strtolower($str)
	{
		if (function_exists('mb_strtolower'))
			return mb_strtolower($str, 'utf-8');
		return strtolower($str);
	}

	static function strlen($str)
	{
		if (function_exists('mb_strlen'))
			return mb_strlen($str, 'utf-8');
		return strlen($str);
	}

	static function strtoupper($str)
	{
		if (function_exists('mb_strtoupper'))
			return mb_strtoupper($str, 'utf-8');
		return strtoupper($str);
	}

	static function substr($str, $start, $length = false, $encoding = 'utf-8')
	{
		if (function_exists('mb_substr'))
			return mb_substr($str, $start, ($length === false ? Tools::strlen($str) : $length), $encoding);
		return substr($str, $start, $length);
	}

	static function ucfirst($str)
	{
		return self::strtoupper(Tools::substr($str, 0, 1)).Tools::substr($str, 1);
	}
	
	
	static public function orderbyPrice(&$array, $orderWay)
	{
		foreach($array as &$row)
			$row['price_tmp'] =  Product::getPriceStatic($row['id_product'], true, ((isset($row['id_product_attribute']) AND !empty($row['id_product_attribute'])) ? intval($row['id_product_attribute']) : NULL), 2);
		if(strtolower($orderWay) == 'desc')
			uasort($array,"Tools::cmpPriceDesc");
		else
			uasort($array,"Tools::cmpPriceAsc");
		foreach($array as &$row)
			unset($row['price_tmp']);
	}
	
		/**
	 * Compare 2 price to sort Categories by price
	 *
	 * @param integer $a
	 * @param integer $b 
	 * @return bool 
	 */

	static public function cmpPriceDesc($a,$b)
	{
	    if ($a['price_tmp'] < $b['price_tmp'])
			return (1);
		elseif ($a['price_tmp'] > $b['price_tmp'])
			return (-1);
		return (0);
	}

	static public function cmpPriceAsc($a,$b)
	{
	    if ($a['price_tmp'] < $b['price_tmp'])
			return (-1);
		elseif ($a['price_tmp'] > $b['price_tmp'])
			return (1);
		return (0);
	}

	static public function iconv($from, $to, $string)
	{
	    $converted = htmlentities($string, ENT_NOQUOTES, $from);
	    $converted = html_entity_decode($converted, ENT_NOQUOTES, $to);
	    return $converted;
	}

}

?>
