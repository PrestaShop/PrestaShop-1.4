<?php

define('PAYPAL_API_VERSION', '53.0');

class PaypalLib extends PaypalAPI
{
	private $_logs = array();

	public function getLogs()
	{
		return $this->_logs;
	}

	public function makeCall($host, $script, $methodName, $string)
	{
		// Making request string
		$request = 'METHOD='.urlencode($methodName).'&VERSION='.urlencode(PAYPAL_API_VERSION);
		$request .= '&PWD='.urlencode($this->_apiPassword).'&USER='.urlencode($this->_apiUser);
		$request .= '&SIGNATURE='.urlencode($this->_apiSignature).$string;

		// Making connection
		include(_PS_MODULE_DIR_.'paypalapi/api/PayPalConnect.php');
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
		$this->_logs[] = '<b>'.$this->l('PayPal response:').'</b>';
		foreach ($response as $k => $res)
			$this->_logs[] = $k.' -> '.$res;
		return $response;
	}

	public function makeSimpleCall($host, $script, $request)
	{
		// Making connection
		include(_PS_MODULE_DIR_.'paypalapi/api/PayPalConnect.php');
		$ppConnect = new PaypalConnect();
		$result = $ppConnect->makeConnection($host, $script, $request);
		$this->_logs = $ppConnect->getLogs();
		return $result;
	}
}

