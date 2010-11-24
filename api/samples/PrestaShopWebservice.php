<?php

class PrestaShopWebservice
{
	protected $url;
	protected $key;
	protected $debug;
	
	function __construct($url, $key, $debug = false) {
		if (!extension_loaded('curl'))
		  throw new PrestaShopWebserviceException('Please activate the PHP extension \'curl\' to allow use of PrestaShop webservice library');
		$this->url = $url;
		$this->key = $key;
		$this->debug = $debug;
	}
	
	protected static function checkStatusCode($status_code)
	{
		$error_label = 'This call to PrestaShop Web Services failed and returned an HTTP status of %d. That means: %s.';
		switch($status_code)
		{
			case 200:case 201:break;
			case 204: throw new PrestaShopWebserviceException(sprintf($error_label, $status_code, 'No content'));break;
			case 400: throw new PrestaShopWebserviceException(sprintf($error_label, $status_code, 'Bad Request'));break;
			case 401: throw new PrestaShopWebserviceException(sprintf($error_label, $status_code, 'Unauthorized'));break;
			case 404: throw new PrestaShopWebserviceException(sprintf($error_label, $status_code, 'Not Found'));break;
			case 405: throw new PrestaShopWebserviceException(sprintf($error_label, $status_code, 'Method Not Allowed'));break;
			case 500: throw new PrestaShopWebserviceException(sprintf($error_label, $status_code, 'Internal Server Error'));break;
			default: throw new PrestaShopWebserviceException('This call to PrestaShop Web Services returned an unexpected HTTP status of:' . $status_code);
		}
	}
	
	protected static function executeRequest($ws_url, $key, $resource, $params = array(), $id = null, $debug = false, $full_url = null, $url_params = null)
	{
		$ressource_url = isset($full_url) ? $full_url : $ws_url.'/api/'.$resource.($id ? '/'.$id : '').(isset($url_params) ? '?'.http_build_query($url_params) : '');
		$defaultParams = array(
			CURLOPT_HEADER => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLINFO_HEADER_OUT => TRUE,
			/*CURLOPT_TIMEOUT => 4,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_FRESH_CONNECT => 1,*/
			CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $key.':',
		);
		
		$session = curl_init($ressource_url);
		curl_setopt_array($session, $defaultParams + $params);
		$response = curl_exec($session);
		$response = explode("\r\n\r\n", $response);
		
		$header = $response[0];
		$response = $response[1];

		if ($debug)
		{
			echo '<div style="display:table;background:#CCC;font-size:8pt;padding:7px"><h6 style="font-size:9pt;margin:0">REQUEST</h6><pre>'.curl_getinfo($session, CURLINFO_HEADER_OUT).'</pre></div>';
			echo '<div style="display:table;background:#CCC;font-size:8pt;padding:7px"><h6 style="font-size:9pt;margin:0">RETURN</h6><pre>'.$header.'</pre></div>';
		}
		$status_code = curl_getinfo($session, CURLINFO_HTTP_CODE);
		if ($status_code === 0)
			throw new PrestaShopWebserviceException('CURL Error: '.curl_error($session));
		curl_close($session);
		if ($debug)
		{
			if ($params[CURLOPT_CUSTOMREQUEST] == 'PUT' || $params[CURLOPT_CUSTOMREQUEST] == 'POST')
				echo '<div style="display:table;background:#CCC;font-size:8pt;padding:7px"><h6 style="font-size:9pt;margin:0">XML SENT</h6><pre>'.htmlentities($params[CURLOPT_POSTFIELDS]).'</pre></div>';
			if ($params[CURLOPT_CUSTOMREQUEST] != 'DELETE')
				echo '<div style="display:table;background:#CCC;font-size:8pt;padding:7px"><h6 style="font-size:9pt;margin:0">HTTP RETURN</h6><pre style="max-height:500px;overflow:auto;">'.htmlentities($response).'</pre></div>';
		}
		return array('status_code' => $status_code, 'response' => $response);
	}
	
	protected static function parseXML($response)
	{
		if ($response != '')
		{
			libxml_use_internal_errors(true);
			$xml = simplexml_load_string($response);
			if (libxml_get_errors())
				throw new PrestaShopWebserviceException('HTTP XML response is not parsable : '.var_export(libxml_get_errors(), true));
			return $xml;
		}
		else
			throw new PrestaShopWebserviceException('HTTP response is empty');
	}
	
	public function add($params)
	{
		if (isset($params['url']))
			$request = self::executeRequest(null, $this->key, null,  array(CURLOPT_CUSTOMREQUEST => 'POST', CURLOPT_POSTFIELDS => 'xml='.$params['postXml']), null, $this->debug, $params['url']);
		elseif (isset($params['resource']) && isset($params['postXml']))
			$request = self::executeRequest($this->url, $this->key, $params['resource'], array(CURLOPT_CUSTOMREQUEST => 'POST', CURLOPT_POSTFIELDS => 'xml='.$params['postXml']), null, $this->debug);
		else
			throw new PrestaShopWebserviceException('Bad parameters given');
		self::checkStatusCode($request['status_code']);
		return self::parseXML($request['response']);
	}

	public function get($params)
	{
		if (isset($params['url']))
			$request = self::executeRequest(null, $this->key, null, array(CURLOPT_CUSTOMREQUEST => 'GET'), null, $this->debug, $params['url']);
		elseif (isset($params['resource']))
			$request = self::executeRequest($this->url, $this->key, $params['resource'], array(CURLOPT_CUSTOMREQUEST => 'GET'), (isset($params['id']) ? $params['id'] : null), $this->debug, null, (isset($params['filter']) ? $params['filter'] : null));
		else
			throw new PrestaShopWebserviceException('Bad parameters given');
		self::checkStatusCode($request['status_code']);// check the response validity
		return self::parseXML($request['response']);
	}

	public function edit($params)
	{
		if (isset($params['url']))
			$request = self::executeRequest(null, $this->key, null,  array(CURLOPT_CUSTOMREQUEST => 'PUT',CURLOPT_POSTFIELDS => $params['putXml']), null, $this->debug, $params['url']);
		elseif (isset($params['resource']) && isset($params['putXml']) && isset($params['id']))
			$request = self::executeRequest($this->url, $this->key, $params['resource'], array(CURLOPT_CUSTOMREQUEST => 'PUT',CURLOPT_POSTFIELDS => $params['putXml']), $params['id'], $this->debug);
		else
			throw new PrestaShopWebserviceException('Bad parameters given');
		self::checkStatusCode($request['status_code']);// check the response validity
		return self::parseXML($request['response']);
	}

	public function delete($params)
	{
		if (isset($params['url']))
			$request = self::executeRequest(null, $this->key, null, array(CURLOPT_CUSTOMREQUEST => 'DELETE'), $id, $this->debug, $params['url']);
		elseif (isset($params['resource']) && isset($params['id']))
			$request = self::executeRequest($this->url, $this->key, $params['resource'], array(CURLOPT_CUSTOMREQUEST => 'DELETE'), $params['id'], $this->debug);
		self::checkStatusCode($request['status_code']);// check the response validity
		return true;
	}
}

class PrestaShopWebserviceException extends Exception { }

