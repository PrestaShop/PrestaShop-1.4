<?php

/**
  * ConnectionsSource class, ConnectionsSource.php
  * Connection source management
  * @category classes
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  *
  */

class ConnectionsSource extends ObjectModel
{
	public $id_connections;
	public $http_referer;
	public $request_uri;
	public $keywords;
	public $date_add;

	// Controler les keywords
	
	protected	$fieldsRequired = array('id_connections', 'date_add');
	protected	$fieldsValidate = array('id_connections' => 'isUnsignedId', 'http_referer' => 'isAbsoluteUrl', 'request_uri' => 'isUrl', 'keywords' => 'isMessage');

	protected 	$table = 'connections_source';
	protected 	$identifier = 'id_connections_source';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_connections'] = intval($this->id_connections);
		$fields['http_referer'] = pSQL($this->http_referer);
		$fields['request_uri'] = pSQL($this->request_uri);
		$fields['keywords'] = pSQL($this->keywords);
		$fields['date_add'] = pSQL($this->date_add);
		return $fields;
	}
	
	public function add($autodate = true, $nullValues = false)
	{
		if($result = parent::add($autodate, $nullValues))
			Referrer::cacheNewSource($this->id);
		return $result;
	}
	
	public static function logHttpReferer()
	{
		global $cookie;

		if (!isset($cookie->id_connections) OR !Validate::isUnsignedId($cookie->id_connections))
			return false;
		if (!isset($_SERVER['HTTP_REFERER']) AND !Configuration::get('TRACKING_DIRECT_TRAFFIC'))
			return false;
		
		$source = new ConnectionsSource();
		if (isset($_SERVER['HTTP_REFERER']) AND Validate::isAbsoluteUrl($_SERVER['HTTP_REFERER']))
		{
			if ((preg_replace('/^www./', '', parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST)) == preg_replace('/^www./', '', $_SERVER['HTTP_HOST']))
				AND !strncmp(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH), parse_url('http://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__, PHP_URL_PATH), strlen(__PS_BASE_URI__)))
				return false;
			if (Validate::isAbsoluteUrl(strval($_SERVER['HTTP_REFERER'])))
			{
				$source->http_referer = strval($_SERVER['HTTP_REFERER']);
				$source->keywords = trim(SearchEngine::getKeywords(strval($_SERVER['HTTP_REFERER'])));
				if (!Validate::isMessage($source->keywords))
					return false;
			}
		}
		
		$source->id_connections = intval($cookie->id_connections);
		$source->request_uri = $_SERVER['HTTP_HOST'];
		if (isset($_SERVER['REDIRECT_URL']))
			$source->request_uri .= strval($_SERVER['REDIRECT_URL']);
		elseif (isset($_SERVER['REQUEST_URI']))
			$source->request_uri .= strval($_SERVER['REQUEST_URI']);
		if (!Validate::isUrl($source->request_uri))
			unset($source->request_uri);
		return $source->add();
	}
	
	public static function getOrderSources($id_order)
	{
		return Db::getInstance()->ExecuteS('
		SELECT cos.http_referer, cos.request_uri, cos.keywords, cos.date_add
		FROM '._DB_PREFIX_.'orders o
		INNER JOIN '._DB_PREFIX_.'guest g ON g.id_customer = o.id_customer
		INNER JOIN '._DB_PREFIX_.'connections co  ON co.id_guest = g.id_guest
		INNER JOIN '._DB_PREFIX_.'connections_source cos ON cos.id_connections = co.id_connections
		WHERE id_order = '.intval($id_order).'
		ORDER BY cos.date_add DESC');
	}
}

?>
