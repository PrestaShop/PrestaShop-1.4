<?php
/*
* 2007-2012 PrestaShop
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision$
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/* Charset */
if (!defined('_PS_MB_STRING_'))
	define('_PS_MB_STRING_', function_exists('mb_strlen'));
if (!defined('_PS_ICONV_'))
	define('_PS_ICONV_', function_exists('iconv'));

class ToolsInstall
{
	/**
	 * checkDB will call to the 
	 * 
	 * @param string $srv 
	 * @param string $login 
	 * @param string $password 
	 * @param string $name 
	 * @param string $posted 
	 * @return void
	 */
	public static function checkDB($srv, $login, $password, $name, $posted = true)
	{
		// Don't include theses files if classes are already defined
		if (!class_exists('Validate', false))
		{
			include_once(INSTALL_PATH.'/../classes/Validate.php');
			eval('class Validate extends ValidateCore{}');
		}

		if (!class_exists('Db', false))
		{
			include_once(INSTALL_PATH.'/../classes/Db.php');
			eval('abstract class Db extends DbCore{}');
		}

		if (!class_exists('MySQL', false))
		{
			include_once(INSTALL_PATH.'/../classes/MySQL.php');
			eval('class MySQL extends MySQLCore{}');
		}
				
		if ($posted)
		{
			// Check POST data...
			$data_check = array(
				!isset($_GET['server']) OR empty($_GET['server']) OR !Validate::isUrl($_GET['server']),
				!isset($_GET['engine']) OR empty($_GET['engine']) OR !Validate::isMySQLEngine($_GET['engine']),
				!isset($_GET['name']) OR empty($_GET['name']) OR !Validate::isUnixName($_GET['name']),
				!isset($_GET['login']) OR empty($_GET['login']) OR !Validate::isUnixName($_GET['login']),
				!isset($_GET['password']),
				(!isset($_GET['tablePrefix']) OR !Validate::isTablePrefix($_GET['tablePrefix'])) && !empty($_GET['tablePrefix']),
			);

			foreach ($data_check AS $data)
				if ($data)
					return 8;
		}

		switch(MySQL::tryToConnect(trim($srv), trim($login), trim($password), trim($name)))
		{
			case 0:
				if (MySQL::tryUTF8(trim($srv), trim($login), trim($password)))
					return true;
				return 49;
			break;
			case 1:
				return 25;
			break;
			case 2:
				return 24;
			break;
			case 3:
				return 50;
			break;
		}
	}
	
	public static function getHttpHost($http = false, $entities = false, $ignore_port = false)
	{
		$host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);
		if ($ignore_port && $pos = strpos($host, ':'))
			$host = substr($host, 0, $pos);
		if ($entities)
			$host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
		if ($http)
			$host = 'http://'.$host;
		return $host;
	}
	
	public static function sendMail($smtpChecked, $smtpServer, $content, $subject, $type, $to, $from, $smtpLogin, $smtpPassword, $smtpPort = 25, $smtpEncryption)
	{
		require_once(INSTALL_PATH.'/../tools/swift/Swift.php');
		require_once(INSTALL_PATH.'/../tools/swift/Swift/Connection/SMTP.php');
		require_once(INSTALL_PATH.'/../tools/swift/Swift/Connection/NativeMail.php');
		
		$swift = NULL;
		$result = NULL;
		try
		{
			if ($smtpChecked)
			{
				
				$smtp = new Swift_Connection_SMTP($smtpServer, $smtpPort, ($smtpEncryption == "off") ? Swift_Connection_SMTP::ENC_OFF : (($smtpEncryption == "tls") ? Swift_Connection_SMTP::ENC_TLS : Swift_Connection_SMTP::ENC_SSL));
				$smtp->setUsername($smtpLogin);
				$smtp->setpassword($smtpPassword);
				$smtp->setTimeout(5);
				$swift = new Swift($smtp);
			}
			else
				$swift = new Swift(new Swift_Connection_NativeMail());
			
			$message = new Swift_Message($subject, $content, $type);
			
			if ($swift->send($message, $to, $from))
				$result = true;
			else
				$result = 999;

			$swift->disconnect();
		}
		catch (Swift_Connection_Exception $e)
		{
			$result = $e->getCode();
		}
		catch (Swift_Message_MimeException $e)
		{
			$result = $e->getCode();
		}
		return $result;	
	}
	
	public static function getNotificationMail($shopName, $shopUrl, $shopLogo, $firstname, $lastname, $password, $email)
	{
		$iso_code = $_GET['isoCodeLocalLanguage'];
		$pathTpl = INSTALL_PATH.'/../mails/en/employee_password.html';
		$pathTplLocal = INSTALL_PATH.'/../mails/'.$iso_code.'/employee_password.html';
		
		$content = (file_exists($pathTplLocal)) ? file_get_contents($pathTplLocal) : file_get_contents($pathTpl);
		$content = str_replace('{shop_name}', $shopName, $content);
		$content = str_replace('{shop_url}', $shopUrl, $content);
		$content = str_replace('{shop_logo}', $shopLogo, $content);
		$content = str_replace('{firstname}', $firstname, $content);
		$content = str_replace('{lastname}', $lastname, $content);
		$content = str_replace('{passwd}', $password, $content);
		$content = str_replace('{email}', $email, $content);
		return $content;
	}
	
	public static function getLangString($idLang)
	{
		switch ($idLang)
		{
			case 'en' : return 'English (English)';
			case 'fr' : return 'Français (French)';
		}
	}

	static function strtolower($str)
	{
		if (function_exists('mb_strtolower'))
			return mb_strtolower($str, 'utf-8');
		return strtolower($str);
	}

	static function strtoupper($str)
	{
		if (function_exists('mb_strtoupper'))
			return mb_strtoupper($str, 'utf-8');
		return strtoupper($str);
	}
	
	static function ucfirst($str)
	{
		return self::strtoupper(self::substr($str, 0, 1)).self::substr($str, 1);
	}
	
	static function substr($str, $start, $length = false, $encoding = 'utf-8')
	{
		if (function_exists('mb_substr'))
			return mb_substr($str, $start, ($length === false ? self::strlen($str) : $length), $encoding);
		return substr($str, $start, $length);
	}
	
	static function strlen($str)
	{
		if (function_exists('mb_strlen'))
			return mb_strlen($str, 'utf-8');
		return strlen($str);
	}

/**
     * Converts a simpleXML element into an array. Preserves attributes and everything.
     * You can choose to get your elements either flattened, or stored in a custom index that
     * you define.
     * For example, for a given element
     * <field name="someName" type="someType"/>
     * if you choose to flatten attributes, you would get:
     * $array['field']['name'] = 'someName';
     * $array['field']['type'] = 'someType';
     * If you choose not to flatten, you get:
     * $array['field']['@attributes']['name'] = 'someName';
     * _____________________________________
     * Repeating fields are stored in indexed arrays. so for a markup such as:
     * <parent>
     * <child>a</child>
     * <child>b</child>
     * <child>c</child>
     * </parent>
     * you array would be:
     * $array['parent']['child'][0] = 'a';
     * $array['parent']['child'][1] = 'b';
     * ...And so on.
     * _____________________________________
     * @param simpleXMLElement $xml the XML to convert
     * @param boolean $flattenValues    Choose wether to flatten values
     *                                    or to set them under a particular index.
     *                                    defaults to true;
     * @param boolean $flattenAttributes Choose wether to flatten attributes
     *                                    or to set them under a particular index.
     *                                    Defaults to true;
     * @param boolean $flattenChildren    Choose wether to flatten children
     *                                    or to set them under a particular index.
     *                                    Defaults to true;
     * @param string $valueKey            index for values, in case $flattenValues was set to
            *                            false. Defaults to "@value"
     * @param string $attributesKey        index for attributes, in case $flattenAttributes was set to
            *                            false. Defaults to "@attributes"
     * @param string $childrenKey        index for children, in case $flattenChildren was set to
            *                            false. Defaults to "@children"
     * @return array the resulting array.
     */
		public static function simpleXMLToArray ($xml, $flattenValues = true, $flattenAttributes = true, $flattenChildren = true, $valueKey = '@value', $attributesKey = '@attributes', $childrenKey = '@children')
		{
		$return = array();
		if (!($xml instanceof SimpleXMLElement))
			return $return;

		$name = $xml->getName();
		$_value = trim((string)$xml);
		if (strlen($_value) == 0)
			$_value = null;

		if ($_value !== null)
		{
			if (!$flattenValues)
				$return[$valueKey] = $_value;
			else
				$return = $_value;
		}

		$children = array();
		$first = true;
		foreach($xml->children() as $elementName => $child)
		{
			$value = ToolsInstall::simpleXMLToArray($child, $flattenValues, $flattenAttributes, $flattenChildren, $valueKey, $attributesKey, $childrenKey);
			if (isset($children[$elementName]))
			{
				if ($first)
				{
					$temp = $children[$elementName];
					unset($children[$elementName]);
					$children[$elementName][] = $temp;
					$first=false;
				}
				$children[$elementName][] = $value;
			}
			else
				$children[$elementName] = $value;
		}

		if (count($children) > 0 )
		{
			if (!$flattenChildren)
				$return[$childrenKey] = $children;
			else
				$return = array_merge($return, $children);
		}

		$attributes = array();
		foreach($xml->attributes() as $name => $value)
			$attributes[$name] = trim($value);

		if (count($attributes) > 0)
		{
			if (!$flattenAttributes)
				$return[$attributesKey] = $attributes;
			else
				$return = array_merge($return, $attributes);
		}

		return $return;
	}
}
