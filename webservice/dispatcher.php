<?php
/*
* 2007-2010 PrestaShop 
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
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/../config/config.inc.php');

require_once(dirname(__FILE__).'/WebserviceRequest.php');

$method = isset($_REQUEST['ps_method']) ? $_REQUEST['ps_method'] : $_SERVER['REQUEST_METHOD'];

if (isset($_REQUEST['xml']))
{
	// if a XML is in POST
	$input_xml = $_REQUEST['xml'];
}
else
{
	// if no XML
	$input_xml = NULL;
	
	// if a XML is in PUT
	if ($_SERVER['REQUEST_METHOD'] == 'PUT')
	{
		$putresource = fopen("php://input", "r");
		while ($putData = fread($putresource, 1024))
			$input_xml .= $putData;
		fclose($putresource);
	}
}

$params = $_GET;
unset($params['url']);

// fetch the request
$result = WebserviceRequest::getInstance()->fetch($method, $_GET['url'], $params, $input_xml);
// display result
header($result['content_type']);
header($result['status']);
header($result['x_powered_by']);
header($result['execution_time']);
if (isset($result['ps_ws_version']))
	header($result['ps_ws_version']);


if ($result['type'] == 'xml')
{
	header($result['content_sha1']);
	echo $result['content'];
}
elseif ($result['type'] == 'image')
{
	if ($result['extension'] == 'jpg')
		
		imagejpeg($result['resource']);
	elseif ($result['extension'] == 'gif')
		imagegif($result['resource']);
}
