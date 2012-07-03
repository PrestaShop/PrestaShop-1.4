<?php

include_once(_PS_MODULE_DIR_ . 'paypal/api/paypal_connect.php');
define('PAYPAL_API_VERSION', '60.0');

class PaypalLib extends Paypal
{
	private $_logs = array();

	public function getLogs()
	{
		return $this->_logs;
	}

	public function makeCall($host, $script, $methodName, $data, $method_version = '')
	{
		// Making request string
		$method_version = (!empty($method_version)) ? $method_version : PAYPAL_API_VERSION;

		$request = array(
			'METHOD' => $methodName,
			'VERSION' => $method_version,
			'PWD' => Configuration::get('PAYPAL_API_PASSWORD'),
			'USER' => Configuration::get('PAYPAL_API_USER'),
			'SIGNATURE' => Configuration::get('PAYPAL_API_SIGNATURE')
		);

		$request = http_build_query($request, '', '&');
		$request .= '&'.(!is_array($data) ? $data : http_build_query($data, '', '&'));

		// Making connection
		$ppConnect = new PaypalConnect();
		$result = $ppConnect->makeConnection($host, $script, $request, true);
		$this->_logs = $ppConnect->getLogs();

		// Formating response value
		$response = explode('&', $result);
		foreach ($response as $k => $res)
		{
			$tmp = explode('=', $res);
			if (!isset($tmp[1]))
				$response[$tmp[0]] = urldecode($tmp[0]);
			else
			{
				$response[$tmp[0]] = urldecode($tmp[1]);
				unset($response[$k]);
			}
		}
		if (!Configuration::get('PAYPAL_DEBUG_MODE'))
			$this->_logs = array();

		$toExclude = array('TOKEN', 'SUCCESSPAGEREDIRECTREQUESTED', 'VERSION', 'BUILD', 'ACK', 'CORRELATIONID');
		$this->_logs[] = '<b>'.$this->l('PayPal response:').'</b>';

		foreach ($response as $k => $res)
		{
			if (!Configuration::get('PAYPAL_DEBUG_MODE') && in_array($k, $toExclude))
				continue;
			$this->_logs[] = $k.' -> '.$res;
		}

		return $response;
	}

	public function makeSimpleCall($host, $script, $request)
	{
		// Making connection
		$ppConnect = new PaypalConnect();
		$result = $ppConnect->makeConnection($host, $script, $request);
		$this->_logs = $ppConnect->getLogs();
		return $result;
	}
}

