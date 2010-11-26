<?php
/////////////////////
// initializations //
/////////////////////

function psErrorHandler($errno, $errstr, $errfile, $errline)
{
	global $errors, $display_errors;
	if (!(error_reporting() & $errno)) {
		return;
	}
	if ($display_errors)
	{
		switch($errno){
			case E_ERROR:
				$errors[] = '[PHP Error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_WARNING:
				$errors[] = '[PHP Warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_PARSE:
				$errors[] = '[PHP Parse #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_NOTICE:
				$errors[] = '[PHP Notice #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_CORE_ERROR:
				$errors[] = '[PHP Core error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_CORE_WARNING:
				$errors[] = '[PHP Core warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_COMPILE_ERROR:
				$errors[] = '[PHP Compile error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_COMPILE_WARNING:
				$errors[] = '[PHP Compile warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_USER_ERROR:
				$errors[] = '[PHP User error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_USER_WARNING:
				$errors[] = '[PHP User warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_USER_NOTICE:
				$errors[] = '[PHP User notice #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_STRICT:
				$errors[] = '[PHP Strict #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			case E_RECOVERABLE_ERROR:
				$errors[] = '[PHP Recoverable error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
				break;
			default:
				$errors[] = '[PHP Unknown error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')';
		}
	}
	else
		$errors[] = 'Internal error';
	return true;
}
$errors = array();
$webservice_call = true;
$old_error_handler = set_error_handler("psErrorHandler");
include(dirname(__FILE__).'/../config/config.inc.php');
$display_errors = strtolower(ini_get('display_errors')) != 'off';
header('X-Powered-By: PrestaShop Webservice');
ini_set('html_errors', 'off');
$output = true;
$return_code = 'HTTP/1.1 200 OK';
$ws_url = Tools::getHttpHost(true).__PS_BASE_URI__.'api/';
$dtd = Tools::getHttpHost(true).__PS_BASE_URI__.'tools/webservice/psws.dtd';//A METTRE SUR LE .com ;) //non car dynamique en fonction des modules
$doc_url = 'http://prestashop.com/docs/1.4/webservice';//A METTRE SUR LE .com ;) //non car dynamique en fonction des modules
$invalid_key = false;

// http auth with a key
if (!Configuration::get('PS_WEBSERVICE'))
{
	$errors[] = 'The PrestaShop webservice is disabled. Please activate it in the PrestaShop Back Office';
	$return_code = 'HTTP/1.1 404 Not Found';
}
else
{
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
		header('WWW-Authenticate: Basic realm="Welcome to PrestaShop Webservice, please enter the authentication key as the login. No password required."');
		$return_code = 'HTTP/1.0 401 Unauthorized';
		$errors[] = 'Please enter the authentication key as the login. No password required';
	} else {
		$auth_key = trim($_SERVER['PHP_AUTH_USER']);
		if (empty($auth_key))
		{
			$errors[] = 'Authentication key is empty';
			$return_code = 'HTTP/1.0 401 Unauthorized';
		}
		elseif (strlen($auth_key) != '32')
		{
			$errors[] = 'Invalid authentication key format';
			$invalid_key = true;
			$return_code = 'HTTP/1.1 400 Bad Request';
		}
		else
		{
			$permissions = Webservice::getPermissionForAccount($auth_key);
			if (!$permissions)
			{
				$return_code = 'HTTP/1.0 401 Unauthorized';
				$errors[] = 'No permission for this authentication key';
			}
		}
	}
	if ($errors)
	{
		header('WWW-Authenticate: Basic'.($invalid_key ? '/StopAskTillNextRefresh' : '').' realm="Welcome to PrestaShop Webservice, please enter the authentication key as the login. No password required."');
		$return_code = 'HTTP/1.0 401 Unauthorized';
	}
	else
	{
		//get call informations
		$method = $_SERVER['REQUEST_METHOD'];
		$url = explode('/', $_GET['url']);
		$url_params = $_GET;
		unset($url_params['url']);

		//check method validity
		if (!in_array($method, array('GET', 'POST', 'PUT', 'DELETE')))
		{
			$errors[] = 'Method '.$method.' is not valid';
			$return_code = 'HTTP/1.1 405 Method Not Allowed';
		}
		elseif (($method == 'PUT' || $method == 'DELETE') && !array_key_exists(1, $url))
		{
			$errors[] = 'Method '.$method.' need you to specify an id';
			$return_code = 'HTTP/1.0 401 Unauthorized';
		}
		elseif (($method == 'POST') && array_key_exists(1, $url))
		{
			$errors[] = 'id is forbidden when adding a new resource';
			$return_code = 'HTTP/1.1 400 Bad Request';
		}
		else
		{

	///////////////////////////
	// resources parameters //
	///////////////////////////
			$resources = Webservice::getResources();
			// set available resource paramaters
			if ($url[0] == '')
				$resourceParameters['objectsNodeName'] = 'resources';
			elseif (in_array($url[0], array_keys($resources)))
			{
				if (in_array($url[0], array_keys($permissions)))
				{
					// instanciation because some values are dynamic in PS..
					$object = new $resources[$url[0]]['class']();
					$resourceParameters = $object->getWebserviceParameters();
				}
				else
				{
					$errors[] = 'resource of type "'.$url[0].'" is not allowed with this authentication key';
					$return_code = 'HTTP/1.1 401 Unauthorized';
				}
			}
			else
			{
				$errors[] = 'resource of type "'.$url[0].'" does not exists';
				$return_code = 'HTTP/1.1 400 Bad Request';
			}
		}
		
		if (!$errors)
			require_once('actions.php');
	}
}

require_once('return.php');
