<?php

/**
  * Statistics
  * @category stats
  *
  * @author Damien Metzger / Epitech
  * @copyright Epitech / PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.2
  */
  
class Connection extends ObjectModel
{	
	/** @var integer */
	public $id_guest;
	
	/** @var integer */
	public $id_page;

	/** @var string */
	public $ip_address;

	/** @var string */
	public $http_referer;

	/** @var string */	
	public $date_add;

	protected	$fieldsRequired = array ('id_guest', 'id_page');	
	protected	$fieldsValidate = array ('id_guest' => 'isUnsignedId', 'id_page' => 'isUnsignedId',
										 'ip_address' => 'isInt', 'http_referer' => 'isAbsoluteUrl');

	/* MySQL does not allow 'connection' for a table name */ 
	protected 	$table = 'connections';
	protected 	$identifier = 'id_connections';
	
	public function getFields()
	{
		parent::validateFields();
		$fields['id_guest'] = intval($this->id_guest);
		$fields['id_page'] = intval($this->id_page);
		$fields['ip_address'] = intval($this->ip_address);
		if (Validate::isAbsoluteUrl($this->http_referer))
			$fields['http_referer'] = pSQL($this->http_referer);
		$fields['date_add'] = pSQL($this->date_add);
		return $fields;
	}
	
	public static function setPageConnection($cookie)
	{
		// The connection is created if it does not exist yet and we get the current page id
		if (!isset($cookie->id_connections) OR !strstr(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '', $_SERVER['HTTP_HOST']))	
			$id_page = Connection::setNewConnection($cookie);
		if (!isset($id_page) OR !$id_page)
			$id_page = Page::getCurrentId();
		
		// The ending time will be updated by an ajax request when the guest will close the page
		$time_start = date('Y-m-d H:i:s');
		Db::getInstance()->AutoExecute(_DB_PREFIX_.'connections_page', array('id_connections' => intval($cookie->id_connections), 'id_page' => intval($id_page), 'time_start' => $time_start), 'INSERT');
		
		// This array is serialized and used by the ajax request to identify the page
		return array(
			'id_connections' => intval($cookie->id_connections),
			'id_page' => intval($id_page),
			'time_start' => $time_start);
	}
	
	public static function setNewConnection($cookie)
	{
		// The old connections details are removed from the database in order to spare some memory
		Connection::cleanConnectionsPages();
		
		// A new connection is created if the guest made no actions during 30 minutes
		$result = Db::getInstance()->getRow('
		SELECT c.`id_guest`
		FROM `'._DB_PREFIX_.'connections` c
		LEFT JOIN `'._DB_PREFIX_.'connections_page` cp ON c.`id_connections` = cp.`id_connections`
		WHERE c.`id_guest` = '.intval($cookie->id_guest).'
		AND DATE_ADD(cp.`time_start`, INTERVAL 30 MINUTE) > \''.pSQL(date('Y-m-d H:i:s')).'\'
		ORDER BY cp.`time_start` DESC');
		if (!$result['id_guest'] AND intval($cookie->id_guest))
		{
			$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
			if (preg_replace('/^www./', '', parse_url($referer, PHP_URL_HOST)) == preg_replace('/^www./', '', $_SERVER['HTTP_HOST']))
				$referer = '';
			$connection = new Connection();
			$connection->id_guest = intval($cookie->id_guest);
			$connection->id_page = Page::getCurrentId();
			$connection->ip_address = isset($_SERVER['REMOTE_ADDR']) ? ip2long($_SERVER['REMOTE_ADDR']) : '';
			if (Validate::isAbsoluteUrl($referer))
				$connection->http_referer = $referer;
			$connection->add();
			$cookie->id_connections = $connection->id;
			return $connection->id_page;
		}
	}
	
	public static function setPageTime($id_connections, $id_page, $time_start, $time)
	{
		if (!Validate::isUnsignedId($id_connections)
			OR !Validate::isUnsignedId($id_page)
			OR !Validate::isDate($time_start))
			return;
	
		// Limited to 5 minutes because more than 5 minutes is considered as an error
		if ($time > 300000)
			$time = 300000;
		Db::getInstance()->Execute('
		UPDATE `'._DB_PREFIX_.'connections_page`
		SET `time_end` = `time_start` + INTERVAL '.intval($time / 1000).' SECOND
		WHERE `id_connections` = '.intval($id_connections).'
		AND `id_page` = '.intval($id_page).'
		AND `time_start` = \''.pSQL($time_start).'\'');
	}
	
	public static function cleanConnectionsPages()
	{
		$period = Configuration::get('PS_STATS_OLD_CONNECT_AUTO_CLEAN');

		if ($period === 'week')
			$interval = '1 WEEK';
		else if ($period === 'month')
			$interval = '1 MONTH';
		else if ($period === 'year')
			$interval = '1 YEAR';
		else
			return;
			
		if ($interval != null)
		{
			// Records of connections details older than the beginning of the  specified interval are deleted
			Db::getInstance()->Execute('
			DELETE FROM `'._DB_PREFIX_.'connections_page`
			WHERE id_connections IN (
				SELECT `id_connections`
				FROM `'._DB_PREFIX_.'connections`
				WHERE date_add < LAST_DAY(DATE_SUB(NOW(), INTERVAL '.$interval.'))
			)');
		}
	}
}

?>
