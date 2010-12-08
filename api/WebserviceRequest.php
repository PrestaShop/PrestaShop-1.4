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

class WebserviceRequest
{
	
	private $_errors = array();
	private $_status = 'HTTP/1.1 200 OK';
	private $_output = true;
	private $_specificManagement = false;
	private $_wsUrl;
	private $_docUrl = 'http://prestashop.com/docs/1.4/webservice';
	private $_authenticated = false;
	private $_method;
	private $_realMethod;
	private $_urlFolders = array();
	private $_urlParams = array();
	private $_startTime = 0;
	private $_resources;
	private $_resourceConfiguration;
	private $_permissions;
	private $_xmlContent = '';
	private $_objects;
	private $_object;
	private $_schema;
	private $_fieldsToDisplay = 'minimum';
	private $_imageTypes = array(
		'general' => array(
			'header' => array(),
			'mail' => array(),
			'invoice' => array(),
			'store_icon' => array(),
		),
		'products' => array(),
		'categories' => array(),
		'manufacturers' => array(),
		'suppliers' => array(),
		'scenes' => array(),
		'stores' => array()
	);
	private $_imgToDisplay;
	private $_imgType = 'jpg';
	private $_imgMaxSize = 3000000;
	private $_imgTypes = array('image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
	
	private static $_instance;
	
	private function __construct ()
	{
		// time logger
		$this->_startTime = microtime(true);
		
		// two global vars, for compatibility with the PS core...
		global $webservice_call, $display_errors;
		$webservice_call = true;
		$display_errors = strtolower(ini_get('display_errors')) != 'off';
		// error handler
		set_error_handler(array('WebserviceRequest', 'psErrorHandler'));
		ini_set('html_errors', 'off');
		
		$this->_wsUrl = Tools::getHttpHost(true).__PS_BASE_URI__.'api/';
	}
	
	public static function getInstance()
	{
		if(!isset(self::$_instance))
			self::$_instance = new WebserviceRequest();
		return self::$_instance;
	}
	
	public function begin()
	{
		// check webservice activation and request authentication
		if ($this->isActivated() && $this->authenticate())
		{
			//parse request url
			$this->_method = isset($_REQUEST['ps_method']) ? $_REQUEST['ps_method'] : $_SERVER['REQUEST_METHOD'];
			$this->_realMethod = $_SERVER['REQUEST_METHOD'];
			$this->_urlFolders = explode('/', $_GET['url']);
			$this->_urlParams = $_GET;
			unset($this->_urlParams['url']);
			
			// check method and resource
			if ($this->checkResource() && $this->checkMethod())
			{
				// if the resource is a core entity...
				if (!isset($this->_resources[$this->_urlFolders[0]]['specific_management']) || !$this->_resources[$this->_urlFolders[0]]['specific_management'])
				{
					// load resource configuration
					if ($this->_urlFolders[0] != '')
					{
						$object = new $this->_resources[$this->_urlFolders[0]]['class']();
						$this->_resourceConfiguration = $object->getWebserviceParameters();
					}
					
					// execute the action
					switch ($this->_method)
					{
						case 'GET':
						case 'HEAD':
							if ($this->executeGet())
								$this->constructXMLOutputAfterGet();
							break;
						case 'POST':
							if (array_key_exists(1, $this->_urlFolders))
								$this->setError(400, 'id is forbidden when adding a new resource');
							elseif ($this->executePost())
								$this->constructXMLOutputAfterModification();
							break;
						case 'PUT':
							if ($this->executePut())
								$this->constructXMLOutputAfterModification();
							break;
						case 'DELETE':
							$this->executeDelete();
							break;
					}
				}
				// if the management is specific
				else
				{
					$this->_specificManagement = $this->_urlFolders[0];
					switch($this->_specificManagement)
					{
						case 'images':
							$this->manageImages();
							break;
					}
				}
			}
		}
		if ($this->_output)
			$this->display();
	}
	
	public function setStatus($num)
	{
		switch ($num)
		{
			case 200 :
				$this->_status = 'HTTP/1.1 200 OK';
				break;
			case 201 :
				$this->_status = 'HTTP/1.1 201 Created';
				break;
			case 204 :
				$this->_status = 'HTTP/1.1 204 No Content';
				break;
			case 400 :
				$this->_status = 'HTTP/1.1 400 Bad Request';
				break;
			case 401 :
				$this->_status = 'HTTP/1.0 401 Unauthorized';
				break;
			case 404 :
				$this->_status = 'HTTP/1.1 404 Not Found';
				break;
			case 405 :
				$this->_status = 'HTTP/1.1 405 Method Not Allowed';
				break;
			case 500 :
				$this->_status = 'HTTP/1.1 500 Internal Server Error';
				break;
		}
	}
	
	private function writeImageOnDisk($receptionPath, $destWidth = NULL, $destHeight = NULL)
	{
		if ($this->_realMethod == 'PUT')
		{
			$this->setError(405, 'Method PUT is currently not implemented with image resource. Please use the POST method and use the method simulator by adding a "ps_method" parameter with the value "PUT"');//TODO
			return false;
		}
		elseif ($this->_realMethod == 'POST')
		{
			if (isset($_FILES['image']['tmp_name']) AND $_FILES['image']['tmp_name'])
			{
				$file = $_FILES['image'];
				if ($file['size'] > $this->_imgMaxSize)
				{
					$this->setError(400, 'The image size is too large (maximum allowed is '.($this->_imgMaxSize/1000).' KB)');
					return false;
				}
				// Get mime content type
				$mime_type = false;
				if (Tools::isCallable('finfo_open'))
				{
					$const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
					$finfo = finfo_open($const);
					$mime_type = finfo_file($finfo, $file['tmp_name']);
					finfo_close($finfo);
				}
				elseif (Tools::isCallable('mime_content_type'))
					$mime_type = mime_content_type($file['tmp_name']);
				elseif (Tools::isCallable('exec'))
					$mime_type = trim(exec('file -b --mime-type '.escapeshellarg($file['tmp_name'])));
				if (empty($mime_type) || $mime_type == 'regular file')
					$mime_type = $file['type'];
				if (($pos = strpos($mime_type, ';')) !== false)
					$mime_type = substr($mime_type, 0, $pos);
				
				// Check mime content type
				if(!$mime_type || !in_array($mime_type, $this->_imgTypes))
				{
					$this->setError(400, 'This type of image format not recognized, allowed formats are: '.implode('", "', $this->_imgTypes));
					return false;
				}
				// Check error while uploading
				elseif ($file['error'])
				{
					$this->setError(400, 'Error while uploading image. Please change your server\'s settings');
					return false;
				}
				
				// Try to copy image file to a temporary file
				if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($_FILES['image']['tmp_name'], $tmpName))
				{
					$this->setError(400, 'Error while copying image to the temporary directory');
					return false;
				}
				// Try to copy image file to the image folder
				else
				{
					list($sourceWidth, $sourceHeight, $type, $attr) = getimagesize($tmpName);
					if (!$sourceWidth)
					{
						$this->setError(400, 'Image width was null');
						return false;
					}
					if ($destWidth == NULL) $destWidth = $sourceWidth;
					if ($destHeight == NULL) $destHeight = $sourceHeight;
					switch ($type)
					{
						case 1:
							$sourceImage = imagecreatefromgif($tmpName);
							break;
						case 3:
							$sourceImage = imagecreatefrompng($tmpName);
							break;
						case 2:
						default:
							$sourceImage = imagecreatefromjpeg($tmpName);
							break;
					}
				
					$widthDiff = $destWidth / $sourceWidth;
					$heightDiff = $destHeight / $sourceHeight;
					
					if ($widthDiff > 1 AND $heightDiff > 1)
					{
						$nextWidth = $sourceWidth;
						$nextHeight = $sourceHeight;
					}
					else
					{
						if ((int)(Configuration::get('PS_IMAGE_GENERATION_METHOD')) == 2 OR ((int)(Configuration::get('PS_IMAGE_GENERATION_METHOD')) == 0 AND $widthDiff > $heightDiff))
						{
							$nextHeight = $destHeight;
							$nextWidth = (int)(($sourceWidth * $nextHeight) / $sourceHeight);
							$destWidth = ((int)(Configuration::get('PS_IMAGE_GENERATION_METHOD')) == 0 ? $destWidth : $nextWidth);
						}
						else
						{
							$nextWidth = $destWidth;
							$nextHeight = (int)($sourceHeight * $destWidth / $sourceWidth);
							$destHeight = ((int)(Configuration::get('PS_IMAGE_GENERATION_METHOD')) == 0 ? $destHeight : $nextHeight);
						}
					}
					
					$borderWidth = (int)(($destWidth - $nextWidth) / 2);
					$borderHeight = (int)(($destHeight - $nextHeight) / 2);
					
					$destImage = imagecreatetruecolor($destWidth, $destHeight);
				
					$white = imagecolorallocate($destImage, 255, 255, 255);
					imagefill($destImage, 0, 0, $white);
				
					imagecopyresampled($destImage, $sourceImage, $borderWidth, $borderHeight, 0, 0, $nextWidth, $nextHeight, $sourceWidth, $sourceHeight);
					imagecolortransparent($destImage, $white);
					$flag = false;
					switch ($this->_imgType)
					{
						case 'gif':
							$flag = imagegif($destImage, $receptionPath);
							break;
						case 'png':
							$flag = imagepng($destImage, $receptionPath, 7);
							break;
						case 'jpeg':
						default:
							$flag = imagejpeg($destImage, $receptionPath, 90);
							break;
					}
					imagedestroy($destImage);
					return $receptionPath;
				}
				unlink($tmpName);
			}
			else
			{
				$this->setError(400, 'Please set an "image" parameter with image data for value');
				return false;
			}
		}
		else
		{
			$this->setError(405, 'Method '.$this->_method.' is not allowed for an image resource');
			return false;
		}
	}
	private function manageImages()
	{
		if (count($this->_urlFolders) == 1)
			$this->_urlFolders[1] = '';
		switch ($this->_urlFolders[1])
		{
			case '':
				$this->_xmlContent .= '<image_types>'."\n";
				foreach ($this->_imageTypes as $imageTypeName => $imageType)
					$this->_xmlContent .= '<'.$imageTypeName.' xlink:href="'.$this->_wsUrl.$this->_urlFolders[0].'/'.$imageTypeName.'" get="true" put="false" post="false" delete="false" head="true" upload_allowed_mimetypes="'.implode(', ', $this->_imgTypes).'" />'."\n";
				$this->_xmlContent .= '</image_types>'."\n";
				break;
			
			case 'general':
				// Check if method is allowed
				if ($this->_method != 'GET' && $this->_method != 'HEAD' && $this->_method != 'PUT')
				{
					$this->setError(405, 'This method is not allowed with general image resources.');
					return false;
				}
				
				// Execute action
				if (count($this->_urlFolders) == 2)
					$this->_urlFolders[2] = '';
				$path = '';
				$alternative_path = '';
				switch ($this->_urlFolders[2])
				{
					case '':
						$this->_xmlContent .= '<general_image_types>'."\n";
						foreach ($this->_imageTypes['general'] as $generalImageTypeName => $generalImageType)
							$this->_xmlContent .= '<'.$generalImageTypeName.' xlink:href="'.$this->_wsUrl.$this->_urlFolders[0].'/'.$this->_urlFolders[1].'/'.$generalImageTypeName.'" get="true" put="true" post="false" delete="false" head="true" upload_allowed_mimetypes="'.implode(', ', $this->_imgTypes).'" />'."\n";
						$this->_xmlContent .= '</general_image_types>'."\n";
						break;
					
					case 'header':
						$path = _PS_IMG_DIR_.'logo.jpg';
						break;
					case 'mail':
						$path = _PS_IMG_DIR_.'logo_mail.jpg';
						$alternative_path = _PS_IMG_DIR_.'logo.jpg';
						break;
					case 'invoice':
						$path = _PS_IMG_DIR_.'logo_invoice.jpg';
						$alternative_path = _PS_IMG_DIR_.'logo.jpg';
						break;
					case 'store_icon':
						$path = _PS_IMG_DIR_.'logo_stores.gif';
						$this->_imgType = 'gif';
						break;
					default:
						$this->setError(400, 'General image of type "'.$this->_urlFolders[2].'" does not exists. Did you mean: "'.$this->closest($this->_urlFolders[2], array_keys($this->_imageTypes['general'])).'"? The full list is: "'.implode('", "', array_keys($this->_imageTypes['general'])).'"');
						return false;
				}
				if ($path != '')
				{
					if ($this->_method == 'GET' || $this->_method == 'HEAD')
					{
						$this->_imgToDisplay = ($alternative_path != '' && file_exists($alternative_path)) ? $alternative_path : $path;
					}
					elseif ($this->_method == 'PUT')
					{
						if ($this->writeImageOnDisk($path, NULL, NULL))
							$this->_imgToDisplay = $path;
						else
						{
							$this->setError(400, 'Error while copying image to the directory');
							return false;
						}
					}
				}
				break;
			case 'categories':
				// Execute action
				if (count($this->_urlFolders) == 2)
					$this->_urlFolders[2] = '';
				$path = '';
				$categoryImageTypes = ImageType::getImagesTypes('categories');
				switch ($this->_urlFolders[2])
				{
					case '':
						// Check if method is allowed
						if ($this->_method != 'GET')
						{
							$this->setError(405, 'This method is not allowed for listing category images.');
							return false;
						}
						
						// Display the list of category images present on disk 
						$this->_xmlContent .= '<image_types>'."\n";
						foreach ($categoryImageTypes as $imageType)
							$this->_xmlContent .= '<image_type id="'.$imageType['id_image_type'].'" name="'.$imageType['name'].'" xlink:href="'.$this->_wsUrl.'image_types/'.$imageType['id_image_type'].'" />'."\n";
						$this->_xmlContent .= '</image_types>'."\n";
						$this->_xmlContent .= '<category_images>'."\n";
						$nodes = scandir(_PS_CAT_IMG_DIR_);
						foreach ($nodes as $node)
							// avoid too much preg_match...
							if ($node != '.' && $node != '..' && $node != '.svn')
							{
								preg_match('/^(\d)\.jpg*$/Ui', $node, $matches);
								if (isset($matches[1]))
								{
									$id = $matches[1];
									$this->_xmlContent .= '<category_image id="'.$id.'" xlink:href="'.$this->_wsUrl.$this->_urlFolders[0].'/'.$this->_urlFolders[1].'/'.$id.'" />'."\n";
								}
							}
						$this->_xmlContent .= '</category_images>'."\n";
						break;
					
					case 'default':
						// "Default" category image management
						
						d('default categ image case TODO');//TODO
						break;
					
					default:
						// Specific category image management
						$orig_filename = _PS_CAT_IMG_DIR_.$this->_urlFolders[2].'.jpg';
						if (file_exists($orig_filename))
						{
							// If no size was given...
							if (count($this->_urlFolders) == 3)
								$this->_urlFolders[3] = '';
							//d($this->_urlFolders);
							if ($this->_urlFolders[3] == '')
							{
								// Management of the original image (GET, PUT, POST, DELETE)
								switch ($this->_method)
								{
									case 'GET':
									case 'HEAD':
										
										// Display the resized specific image
										$this->_imgToDisplay = $orig_filename;
										break;
									case 'PUT':
										d('TODO');//TODO
										break;
									case 'POST':
										d('TODO');//TODO
										break;
									case 'DELETE':
										d('TODO');//TODO
										break;
									default : 
										$this->setError(405, 'This method is not allowed for listing category images.');
										return false;
								}
							}
							else
							{
								// If a size was given...
								
								// Check if method is allowed
								if ($this->_method != 'GET')
								{
									$this->setError(405, 'This method is not allowed for listing category images.');
									return false;
								}
								
								// Check the given size
								$categoryImageTypeNames = array();
								foreach ($categoryImageTypes as $categoryImageType)
									$categoryImageTypeNames[] = $categoryImageType['name'];
								if (!in_array($this->_urlFolders[3], $categoryImageTypeNames))
								{
									$this->setError(400, 'This image type does not exist for the category images. Did you mean: "'.$this->closest($this->_urlFolders[3], $categoryImageTypeNames).'"? The full list is: "'.implode('", "', $categoryImageTypeNames).'"');
									return false;
								}
								$filename = _PS_CAT_IMG_DIR_.$this->_urlFolders[2].'-'.$this->_urlFolders[3].'.jpg';
								
								// Display the resized specific image
								if (file_exists($filename))
									$this->_imgToDisplay = $filename;
								else
								{
									$this->setError(500, 'This image does not exist on disk');
									return false;
								}
								/*
								d($categoryImageTypeNames);
								d($this->_urlFolders[3]);
								*/
							}
						}
						else
						{
							$this->setStatus(404);
							return false;
						}
				}
				if ($path != '')
				{
					if ($this->_method == 'GET' || $this->_method == 'HEAD')
					{
						$this->_imgToDisplay = ($alternative_path != '' && file_exists($alternative_path)) ? $alternative_path : $path;
					}
					elseif ($this->_method == 'PUT')
					{
						if ($this->writeImageOnDisk($path, NULL, NULL))
							$this->_imgToDisplay = $path;
						else
						{
							$this->setError(400, 'Error while copying image to the directory');
							return false;
						}
					}
				}
				break;
			case 'products':
				$this->_xmlContent = 'case "images/products"';
				break;
			case 'suppliers':
				$this->_xmlContent = 'case "images/suppliers"';
				break;
			case 'manufacturers':
				$this->_xmlContent = 'case "images/manufacturers"';
				break;
			case 'scenes':
				$this->_xmlContent = 'case "images/scenes"';
				break;
			case 'stores':
				$this->_xmlContent = 'case "images/stores"';
				break;
			default:
				$this->setError(400, 'Image of type "'.$this->_urlFolders[1].'" does not exists. Did you mean: "'.$this->closest($this->_urlFolders[1], array_keys($this->_imageTypes)).'"? The full list is: "'.implode('", "', array_keys($this->_imageTypes)).'"');
				return false;
		}
	}
	
	public function setError($num, $label)
	{
		global $display_errors;
		$this->setStatus($num);
		$this->_errors[] = $display_errors ? $label : 'Internal error';
	}
	
	private function hasErrors()
	{
		return (int)$this->_errors;
	}
	
	// check request authentication
	private function authenticate()
	{
		if (!$this->hasErrors())
		{
			if (!isset($_SERVER['PHP_AUTH_USER']))
			{
				header('WWW-Authenticate: Basic realm="Welcome to PrestaShop Webservice, please enter the authentication key as the login. No password required."');
				$this->setError(401, 'Please enter the authentication key as the login. No password required');
				return false;
			}
			else
			{
				$auth_key = trim($_SERVER['PHP_AUTH_USER']);
				if (empty($auth_key))
				{
					$this->setError(401, 'Authentication key is empty');
					return false;
				}
				elseif (strlen($auth_key) != '32')
				{
					$this->setError(400, 'Invalid authentication key format');
					return false;
				}
				else
				{
					if (!Webservice::isKeyActive($auth_key))
					{
						$this->setError(400, 'Authentification is not active');
						return false;
					}
					$this->_permissions = Webservice::getPermissionForAccount($auth_key);
					if (!$this->_permissions)
					{
						$this->setError(401, 'No permission for this authentication key');
						return false;
					}
				}
			}
			if ($this->hasErrors())
			{
				header('WWW-Authenticate: Basic realm="Welcome to PrestaShop Webservice, please enter the authentication key as the login. No password required."');
				$this->setStatus(401);
				return false;
			}
			else
			{
				// only now we can say the access is authenticated
				$this->_authenticated = true;
				return true;
			}
		}
	}
	
	
	// check webservice activation
	private function isActivated()
	{
		if (!Configuration::get('PS_WEBSERVICE'))
		{
			$this->setError(404, 'The PrestaShop webservice is disabled. Please activate it in the PrestaShop Back Office');
			return false;
		}
		return true;
	}
	
	// check method
	private function checkMethod()
	{
		if (!in_array($this->_method, array('GET', 'POST', 'PUT', 'DELETE', 'HEAD')))
			$this->setError(405, 'Method '.$this->_method.' is not valid');
		elseif (($this->_method == 'PUT' || $this->_method == 'DELETE') && !array_key_exists(1, $this->_urlFolders))
			$this->setError(401, 'Method '.$this->_method.' need you to specify an id');
		elseif ($this->_urlFolders[0] && !in_array($this->_method, $this->_permissions[$this->_urlFolders[0]]))
			$this->setError(405, 'Method '.$this->_method.' is not allowed for the ressource '.$this->_urlFolders[0].' with this authentication key');
		else
			return true;
		return false;
	}
	
	// check resource
	private function checkResource()
	{
		$this->_resources = Webservice::getResources();
		$resourceNames = array_keys($this->_resources);
		if ($this->_urlFolders[0] == '')
			$this->_resourceConfiguration['objectsNodeName'] = 'resources';
		elseif (in_array($this->_urlFolders[0], $resourceNames))
		{
			if (!in_array($this->_urlFolders[0], array_keys($this->_permissions)))
			{
				$this->setError(401, 'Resource of type "'.$this->_urlFolders[0].'" is not allowed with this authentication key');
				return false;
			}
		}
		else
		{
			$this->setError(400, 'Resource of type "'.$this->_urlFolders[0].'" does not exists. Did you mean: "'.$this->closest($this->_urlFolders[0], $resourceNames).'"?'.(count($resourceNames) > 1 ? ' The full list is: "'.implode('", "', $resourceNames).'"' : ''));
			return false;
		}
		return true;
	}
	
	private function executeGet()
	{
		if ($this->_resourceConfiguration['objectsNodeName'] != 'resources')
		{
			//construct SQL filter
			$sql_filter = '';
			$sql_join = '';
			if ($this->_urlParams)
			{
				// if we have to display the schema
				if (array_key_exists('schema', $this->_urlParams))
				{
					if ($this->_urlParams['schema'] == 'blank')
					{
						$this->_schema = 'blank';
					}
					elseif ($this->_urlParams['schema'] == 'synopsis')
					{
						$this->_schema = 'synopsis';
					}
					else
					{
						$this->setError(400, 'Please select a schema of type \'synopsis\' to get the whole schema informations (which fields are required, which kind of content...) or \'blank\' to get an empty schema to fill before using POST request');
						return false;
					}
				}
				else
				{
					// if there are filters
					if (isset($this->_urlParams['filter']))
						foreach ($this->_urlParams['filter'] as $field => $url_param)
						{
							$available_filters = array_keys($this->_resourceConfiguration['fields']);
							if ($field != 'sort' && $field != 'limit')
								if (!in_array($field, $available_filters))
								{
									// if there are linked tables
									if (isset($this->_resourceConfiguration['linked_tables']) && isset($this->_resourceConfiguration['linked_tables'][$field]))
									{
										// contruct SQL join for linked tables
										$sql_join .= 'LEFT JOIN `'._DB_PREFIX_.pSQL($this->_resourceConfiguration['linked_tables'][$field]['table']).'` '.pSQL($field).' ON (main.`'.pSQL($this->_resourceConfiguration['fields']['id']['sqlId']).'` = '.pSQL($field).'.`'.pSQL($this->_resourceConfiguration['fields']['id']['sqlId']).'`)'."\n";
						
										// construct SQL filter for linked tables
										foreach ($url_param as $field2 => $value)
										{
											if (isset($this->_resourceConfiguration['linked_tables'][$field]['fields'][$field2]))
											{
												$linked_field = $this->_resourceConfiguration['linked_tables'][$field]['fields'][$field2];
												$sql_filter .= $this->constructSqlFilter($linked_field['sqlId'], $value, $field.'.');
											}
											else
											{
												$list = array_keys($this->_resourceConfiguration['linked_tables'][$field]['fields']);
												$this->setError(400, 'This filter does not exist for this linked table. Did you mean: "'.$this->closest($field2, $list).'"?'.(count($list) > 1 ? ' The full list is: "'.implode('", "', $list).'"' : ''));
												return false;
											}
										}
									}
									// if there are filters on linked tables but there are no linked table
									elseif (is_array($url_param))
									{
										$error_label = '';
										if (isset($this->_resourceConfiguration['linked_tables']))
										{
											$list = array_keys($this->_resourceConfiguration['linked_tables']);
											$error_label .= 'This linked table does not exist, did you mean: "'.$this->closest($field, $list).'"?'.(count($list) > 1 ? ' The full list is: "'.implode('", "', $list).'"' : '');
										}
										else
											$error_label .=	'There is no existing linked table for this resource';
										$this->setError(400, 'HTTP/1.1 400 Bad Request');
										return false;
									}
									else
									{
										$list = $available_filters;
										$this->setError(400, 'This filter does not exist. Did you mean: "'.$this->closest($field, $list).'"?'.(count($list) > 1 ? ' The full list is: "'.implode('", "', $list).'"' : ''));
										return false;
									}
								}
								elseif ($url_param == '')
								{
									$this->setError(400, 'The filter "'.$field.'" is malformed.');
									return false;
								}
								else
								{
									if (isset($this->_resourceConfiguration['fields'][$field]['getter']))
									{
										$this->setError(400, 'The field "'.$field.'" is dynamic. It is not possible to filter GET query with this field.');
										return false;
									}
									else
									{
										if (isset($this->_resourceConfiguration['retrieveData']['tableAlias']))
											$sql_filter .= $this->constructSqlFilter($this->_resourceConfiguration['fields'][$field]['sqlId'], $url_param, $this->_resourceConfiguration['retrieveData']['tableAlias'].'.');
										else
											$sql_filter .= $this->constructSqlFilter($this->_resourceConfiguration['fields'][$field]['sqlId'], $url_param);
									}
								}
						}
				}
			}
	
			// set the fields to display in the list : "full", "minimum", "field_1", "field_1,field_2,field_3" //TODO manage linked_tables too
			if (isset($this->_urlParams['display']))
			{
				$this->_fieldsToDisplay = $this->_urlParams['display'];
				if ($this->_fieldsToDisplay != 'full')
				{
					preg_match('#^\[(.*)\]$#Ui', $this->_fieldsToDisplay, $matches);
					if (count($matches))
					{
						$fieldsToTest = explode(',', $matches[1]);
						foreach ($fieldsToTest as $fieldToDisplay)
							if (!isset($this->_resourceConfiguration['fields'][$fieldToDisplay]))
							{
								$this->setError(400,'Unable to display this field. However, these are available: '.implode(', ', array_keys($this->_resourceConfiguration['fields'])));
								return false;
							}
							$this->_fieldsToDisplay = !$this->hasErrors() ? $fieldsToTest : 'minimal';
					}
					else
					{
						$this->setError(400, 'The \'display\' synthax is wrong. You can set \'full\' or \'[field_1,field_2,field_3,...]\'. These are available: '.implode(', ', array_keys($this->_resourceConfiguration['fields'])));
						return false;
					}
				}
			}
	
			// construct SQL Sort
			$sql_sort = '';
			$available_filters = array_keys($this->_resourceConfiguration['fields']);
			if (isset($this->_urlParams['sort']))
			{
				preg_match('#^\[(.*)\]$#Ui', $this->_urlParams['sort'], $matches);
				if (count($matches) > 1)
					$sorts = explode(',', $matches[1]);
				else
					$sorts = array($this->_urlParams['sort']);
		
				$sql_sort .= ' ORDER BY ';
		
				foreach ($sorts as $sort)
				{
					$sortArgs = explode('_', $sort);
					if (count($sortArgs) != 2 || (strtoupper($sortArgs[1]) != 'ASC' && strtoupper($sortArgs[1]) != 'DESC'))
					{
						$this->setError(400, 'The "sort" value has to be formed as this example: "field_ASC" ("field" has to be an available field)');
						return false;
					}
					elseif (!in_array($sortArgs[0], $available_filters))
					{
						$this->setError(400, 'Unable to filter by this field. However, these are available: '.implode(', ', $available_filters));
						return false;
					}
					else
					{
						$sql_sort .= (isset($this->_resourceConfiguration['retrieveData']['tableAlias']) ? $this->_resourceConfiguration['retrieveData']['tableAlias'].'.' : '').'`'.pSQL($this->_resourceConfiguration['fields'][$sortArgs[0]]['sqlId']).'` '.strtoupper($sortArgs[1]).', ';// ORDER BY `field` ASC|DESC
					}
				}
				$sql_sort = rtrim($sql_sort, ', ')."\n";
			}

			//construct SQL Limit
			$sql_limit = '';
			if (isset($this->_urlParams['limit']))
			{
				$limitArgs = explode(',', $this->_urlParams['limit']);
				if (count($limitArgs) > 2)
				{
					$this->setError(400, 'The "limit" value has to be formed as this example: "5,25" or "10"');
					return false;
				}
				else
				{
					$sql_limit .= ' LIMIT '.(int)($limitArgs[0]).(isset($limitArgs[1]) ? ', '.(int)($limitArgs[1]) : '')."\n";// LIMIT X|X, Y
				}
			}


			$this->_objects = array();
			if (!isset($this->_urlFolders[1]) || !strlen($this->_urlFolders[1]))
			{
					$this->_resourceConfiguration['retrieveData']['params'][] = $sql_join;
					$this->_resourceConfiguration['retrieveData']['params'][] = $sql_filter;
					$this->_resourceConfiguration['retrieveData']['params'][] = $sql_sort;
					$this->_resourceConfiguration['retrieveData']['params'][] = $sql_limit;
				//list entities
				$tmp = new $this->_resourceConfiguration['retrieveData']['className']();
				$sqlObjects = call_user_func_array(array($tmp, $this->_resourceConfiguration['retrieveData']['retrieveMethod']), $this->_resourceConfiguration['retrieveData']['params']);
				if ($sqlObjects)
					foreach ($sqlObjects as $sqlObject)
					{
						$this->_objects[] = new $this->_resourceConfiguration['retrieveData']['className']($sqlObject[$this->_resourceConfiguration['fields']['id']['sqlId']]);
					}
			}
			else
			{
				//get entity details
				$object = new $this->_resourceConfiguration['retrieveData']['className']($this->_urlFolders[1]);
				if ($object->id)
					$this->_objects[] = $object;
				else
				{
					$this->setStatus(404);
					$this->_output = false;
					return false;
				}
			}

		}
		return true;
	}
	
	private function executePost()
	{
		$this->_object = new $this->_resourceConfiguration['retrieveData']['className']();
		// we prefer use $_REQUEST as $_POST because of simulated methods
		return $this->saveObjectFromXmlInput($_REQUEST['xml'], 201);
	}
	
	private function executePut()
	{
		$this->_object = new $this->_resourceConfiguration['retrieveData']['className']($this->_urlFolders[1]);

		if ($this->_object->id)
		{
			$xmlString = '';
			if ($this->_realMethod == 'PUT')
			{
				$putresource = fopen("php://input", "r");
				while ($putData = fread($putresource, 1024))
					$xmlString .= $putData;
				fclose($putresource);
			}
			else
			{
				// we prefer use $_REQUEST as $_POST or $_GET because of simulated methods
				$xmlString .= $_REQUEST['xml'];
			}
			return $this->saveObjectFromXmlInput($xmlString, 200);
		}
		else
		{
			$this->setStatus(404);
			$this->_output = false;
			return false;
		}
	}
	
	private function executeDelete()
	{
		$object = new $resourceParameters['retrieveData']['className']($url[1]);
		if (!$object->id	|| !$object->delete())
			$return_code = 'HTTP/1.1 204 No Content';
		$output = false;
	}
	
	private function constructXMLOutputAfterGet()
	{
		// list entities
		if (!isset($this->_urlFolders[1]) || !strlen($this->_urlFolders[1]))
		{
			if (($this->_resourceConfiguration['objectsNodeName'] != 'resources' && count($this->_objects) || $this->_resourceConfiguration['objectsNodeName'] == 'resources') && count($this->_resources))
			{
				if ($this->_resourceConfiguration['objectsNodeName'] != 'resources')
				{
					if (!is_null($this->_schema))
					{
						// display ready to use schema
						if ($this->_schema == 'blank')
						{
							$this->_fieldsToDisplay = 'full';
							$this->_xmlContent .= $this->getXmlStringViewOfObject();
						
						}
						// display ready to use schema
						else
						{
							$this->_fieldsToDisplay = 'full';
							$this->_xmlContent .= $this->getXmlStringViewOfObject();
						}
					}
					// display specific resources list
					else
					{
						$this->_xmlContent .= '<'.$this->_resourceConfiguration['objectsNodeName'].'>'."\n";
						if ($this->_fieldsToDisplay == 'minimum')
							foreach ($this->_objects as $object)
								$this->_xmlContent .= '<'.$this->_resourceConfiguration['objectNodeName'].(array_key_exists('id', $this->_resourceConfiguration['fields']) ? ' id="'.$object->id.'" xlink:href="'.$this->_wsUrl.$this->_resourceConfiguration['objectsNodeName'].'/'.$object->id.'"' : '').' />'."\n";
						else
							foreach ($this->_objects as $object)
								$this->_xmlContent .= $this->getXmlStringViewOfObject($object);
						$this->_xmlContent .= '</'.$this->_resourceConfiguration['objectsNodeName'].'>'."\n";
					}
				}
				// display all ressources list
				else
				{
					$this->_xmlContent .= '<api shop_name="'.Configuration::get('PS_SHOP_NAME').'" get="true" put="false" post="false" delete="false" head="true">'."\n";
					foreach ($this->_resources as $resourceName => $resource)
						if (in_array($resourceName, array_keys($this->_permissions)))
						{
							$this->_xmlContent .= '<'.$resourceName.' xlink:href="'.$this->_wsUrl.$resourceName.'"
								get="'.(in_array('GET', $this->_permissions[$resourceName]) ? 'true' : 'false').'"
								put="'.(in_array('PUT', $this->_permissions[$resourceName]) ? 'true' : 'false').'"
								post="'.(in_array('POST', $this->_permissions[$resourceName]) ? 'true' : 'false').'"
								delete="'.(in_array('DELETE', $this->_permissions[$resourceName]) ? 'true' : 'false').'"
								head="'.(in_array('HEAD', $this->_permissions[$resourceName]) ? 'true' : 'false').'"
							>
							<description>'.$resource['description'].'</description>';
							if (!isset($resource['specific_management']) || !$resource['specific_management'])
							$this->_xmlContent .= '
							<schema type="blank" xlink:href="'.$this->_wsUrl.$resourceName.'?schema=blank" />
							<schema type="synopsis" xlink:href="'.$this->_wsUrl.$resourceName.'?schema=synopsis" />';
							$this->_xmlContent .= '
							</'.$resourceName.'>'."\n";
						}
					$this->_xmlContent .= '</api>'."\n";
				}
			
			}
			else
				$this->_xmlContent .= '<'.$this->_resourceConfiguration['objectsNodeName'].' />'."\n";
		}
		//display one resource
		else
		{
			$this->_fieldsToDisplay = 'full';
			$this->_xmlContent .= $this->getXmlStringViewOfObject($this->_objects[0]);
		}
	}
	
	private function constructXMLOutputAfterModification()
	{
		$this->_fieldsToDisplay = 'full';
		$this->_xmlContent .= $this->getXmlStringViewOfObject($this->_object);
	}
	
	private function display()
	{
		// write headers
		header($this->_status);
		header('X-Powered-By: PrestaShop Webservice');
		// write this header only now (avoid hackers happiness...)
		if ($this->_authenticated)
			header('PSWS-Version: '._PS_VERSION_);
		header('Execution-Time: '.round(microtime(true) - $this->_startTime,3));
		
		// display image content if needed
		if ($this->_imgToDisplay)
		{
			switch ($this->_imgType)
			{
				case 'jpg':
					$im = @imagecreatefromjpeg($this->_imgToDisplay);
					break;
				case 'gif':
					$im = @imagecreatefromgif($this->_imgToDisplay);
					break;
			}
			if(!$im)
				$this->setError(500, 'Unable to load the image "'.str_replace(_PS_ROOT_DIR_, '[SHOP_ROOT_DIR]', $this->_imgToDisplay).'"');
			else
			{
				switch ($this->_imgType)
				{
					case 'jpg':
						header('Content-Type: image/jpeg');
						imagejpeg($im);
						break;
					case 'gif':
						header('Content-Type: image/gif');
						imagegif($im);
						break;
				}
				imagedestroy($im);
			}
		}
		
		// if errors appends when creating return xml, we replace the usual xml content by the nice error handler content
		if ($this->hasErrors())
		{
			$this->_xmlContent = '<errors>'."\n";
			foreach ($this->_errors as $error)
				$this->_xmlContent .= '<error><![CDATA['.$error.']]></error>'."\n";
			$this->_xmlContent .= '</errors>'."\n";
			restore_error_handler();
		}
		
		// display xml content if needed
		if (strlen($this->_xmlContent) > 0)
		{
			header('Content-Type: text/xml');
			header('Content-Sha1: '.sha1($this->_xmlContent));
			$xml_start = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$xml_start .= '<prestashop xmlns="'.$this->_docUrl.'" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n";
			$xml_end = '</prestashop>'."\n";
			echo $xml_start.$this->_xmlContent.$xml_end;
		}
		die;
	}
	
	private function saveObjectFromXmlInput($xmlString, $successReturnCode)
	{
		$xml = new SimpleXMLElement($xmlString);
		$attributes = $xml->children()->{$this->_resourceConfiguration['objectNodeName']}->children();
		$i18n = false;
		// attributes
		foreach ($this->_resourceConfiguration['fields'] as $fieldName => $fieldProperties)
		{
			$sqlId = $fieldProperties['sqlId'];
			if (isset($attributes->$fieldName) && isset($fieldProperties['sqlId']) && (!isset($fieldProperties['i18n']) || !$fieldProperties['i18n']))
			{
				if (isset($fieldProperties['setter']))
				{
					// if we have to use a specific setter
					if (!$fieldProperties['setter'])
					{
						// if it's forbidden to set this field
						$this->setError(400, 'parameter "'.$fieldName.'" not writable. Please remove this attribute of this XML');
						return false;
					}
					else
						$this->_object->$fieldProperties['sqlId']((string)$attributes->$fieldName);
				}
				else
					$this->_object->$sqlId = (string)$attributes->$fieldName;
			}
			elseif (isset($fieldProperties['required']) && $fieldProperties['required'] && !$fieldProperties['i18n'])
			{
				$this->setError(400, 'parameter "'.$fieldName.'" required');
				return false;
			}
			elseif (!isset($fieldProperties['required']) || !$fieldProperties['required'])
				$this->_object->$sqlId = null;
		}
	
		if (isset($attributes->associations))
			foreach ($attributes->associations->children() as $association)
			{
				// translated attributes
				if ($association->getName() == 'i18n')
				{
					$i18n = true;
					$fields = $association->children();
					foreach ($fields as $field)
					{
						$fieldName = $field->getName();
						$langs = array();
						foreach ($field->children() as $translation)
						{
							$langs[(string)$translation['id']] = (string)$translation;
						}
						$this->_object->$fieldName = $langs;
					}
				}
			}
		if (!$this->hasErrors())
		{
			if ($i18n && ($retValidateFieldsLang = $this->_object->validateFieldsLang(false, true)) !== true)
			{
				$this->setError(400, 'Validation error: "'.$retValidateFieldsLang.'"');
				return false;
			}
			elseif (($retValidateFields = $this->_object->validateFields(false, true)) !== true)
			{
				$this->setError(400, 'Validation error: "'.$retValidateFields.'"');
				return false;
			}
			else
			{
				//d($this->_object);
				if($this->_object->save())
				{
					if (isset($attributes->associations))
						foreach ($attributes->associations->children() as $association)
						{
							// associations
							if (isset($this->_resourceConfiguration['associations'][$association->getName()]))
							{
								$assocItems = $association->children();
								foreach ($assocItems as $assocItem)
								{
									$fields = $assocItem->children();
									$values = array();
									foreach ($fields as $field)
										$values[] = (string)$field;
									$setter = $this->_resourceConfiguration['associations'][$association->getName()]['setter'];
									if (!$this->_object->$setter($values))
									{
										$this->setError(500, 'Error occured while setting the '.$association->getName().' value');
										return false;
									}
								}
							}
							elseif ($association->getName() != 'i18n')
							{
								$this->setError(400, 'The association "'.$association->getName().'" does not exists');
								return false;
							}
						}
					if (!$this->hasErrors())
					{
						$this->setStatus($successReturnCode);
						return true;
					}
				}
				else
					$this->setError(500, 'Unable to save resource');
			}
		}
	}
	
	private function constructSqlFilter($sqlId, $filterValue, $tableAlias = 'main.')
	{
		$ret = '';
		preg_match('/^(.*)\[(.*)\](.*)$/', $filterValue, $matches);
		if (count($matches) > 1)
		{
			if ($matches[1] == '%' || $matches[3] == '%')
				$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` LIKE "'.$matches[1].pSQL($matches[2]).$matches[3]."\"\n";// AND field LIKE %value%
			elseif ($matches[1] == '' && $matches[3] == '')
			{
				preg_match('/^(\d+)(\|(\d+))+$/', $matches[2], $matches2);
				preg_match('/^(\d+)$/', $matches[2], $matches4);
				if (count($matches2) > 0 || count($matches4) > 1)
				{
					$values = explode('|', $matches[2]);
					$ret .= ' AND (';
					$temp = '';
					foreach ($values as $value)
						$temp .= $tableAlias.'`'.pSQL($sqlId).'` = "'.pSQL($value).'" OR ';// AND (field = value3 OR field = value7 OR field = value9)
					$ret .= rtrim($temp, 'OR ').')'."\n";
				}
				else
				{
					preg_match('/^(\d+),(\d+)$/', $matches[2], $matches3);
					if (count($matches3) > 0)
					{
						$values = explode(',', $matches[2]);
						$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` BETWEEN "'.$values[0].'" AND "'.$values[1]."\"\n";// AND field BETWEEN value3 AND value4
					}
				}
			}
			elseif ($matches[1] == '>')
				$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` > "'.pSQL($matches[2])."\"\n";// AND field > value3
			elseif ($matches[1] == '<')
				$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` > "'.pSQL($matches[2])."\"\n";// AND field < value3
			elseif ($matches[1] == '!')
				$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` != "'.pSQL($matches[2])."\"\n";// AND field IS NOT value3
		}
		else
			$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` = "'.pSQL($filterValue)."\"\n";
		return $ret;
	}
	
	private function psErrorHandler($errno, $errstr, $errfile, $errline)
	{
		if (!(error_reporting() & $errno))
		{
			return;
		}
		switch($errno){
			case E_ERROR:
				$this->setError(500, '[PHP Error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_WARNING:
				$this->setError(500, '[PHP Warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_PARSE:
				$this->setError(500, '[PHP Parse #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_NOTICE:
				$this->setError(500, '[PHP Notice #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_CORE_ERROR:
				$this->setError(500, '[PHP Core #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_CORE_WARNING:
				$this->setError(500, '[PHP Core warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_COMPILE_ERROR:
				$this->setError(500, '[PHP Compile #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_COMPILE_WARNING:
				$this->setError(500, '[PHP Compile warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_USER_ERROR:
				$this->setError(500, '[PHP Error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_USER_WARNING:
				$this->setError(500, '[PHP User warning #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_USER_NOTICE:
				$this->setError(500, '[PHP User notice #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_STRICT:
				$this->setError(500, '[PHP Strict #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			case E_RECOVERABLE_ERROR:
				$this->setError(500, '[PHP Recoverable error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
				break;
			default:
				$this->setError(500, '[PHP Unknown error #'.$errno.'] '.$errstr.' ('.$errfile.', line '.$errline.')');
		}
		return true;
	}
	
	private function getXmlStringViewOfObject($object = null)
	{
	
		// two modes are available : 'schema', or 'display entity'
	
		$ret = '<'.$this->_resourceConfiguration['objectNodeName'].'>'."\n";
		// display fields
		foreach ($this->_resourceConfiguration['fields'] as $key => $field)
		{
			if ($this->_fieldsToDisplay == 'full' || in_array($key, $this->_fieldsToDisplay))
			{
				if ($key != 'id')//TODO remove this condition
				{
					// get the field value with a specific getter
					if (isset($field['getter']) && $this->_schema != 'blank')
						$object->$key = $object->$field['getter']();
			
					// display i18n fields
					if (isset($field['i18n']) && $field['i18n'])
					{
						$ret .= '<'.$field['sqlId'];
						if ($this->_schema == 'synopsis')
						{
							if (array_key_exists('required', $field) && $field['required'])
								$ret .= ' required="true"';
							if (array_key_exists('maxSize', $field) && $field['maxSize'])
								$ret .= ' maxSize="'.$field['maxSize'].'"';
							if (array_key_exists('validateMethod', $field) && $field['validateMethod'])
								$ret .= ' format="'.implode(' ', $field['validateMethod']).'"';
						}
						$ret .= ">\n";
						if (!is_null($this->_schema))
							$ret .= '<language id="" '.($this->_schema == 'synopsis' ? 'format="isUnsignedId"' : '').'></language>'."\n";
						else
							foreach ($object->$key as $idLang => $value)
								$ret .= '<language id="'.$idLang.'" xlink:href="'.$this->_wsUrl.'languages/'.$idLang.'"><![CDATA['.$value.']]></language>'."\n";
						$ret .= '</'.$field['sqlId'].'>'."\n";
					}
					else
					{
						// display not i18n field value
						$ret .= '<'.$field['sqlId'];
						if (array_key_exists('xlink_resource', $field) && $this->_schema != 'blank')
							$ret .= ' xlink:href="'.$this->_wsUrl.$field['xlink_resource'].'/'.($this->_schema != 'synopsis' ? $object->$key : '').'"';
						if (isset($field['getter']) && $this->_schema != 'blank')
							$ret .= ' not_filterable="true"';
						if ($this->_schema == 'synopsis')
						{
							if (array_key_exists('required', $field) && $field['required'])
								$ret .= ' required="true"';
							if (array_key_exists('maxSize', $field) && $field['maxSize'])
								$ret .= ' maxSize="'.$field['maxSize'].'"';
							if (array_key_exists('validateMethod', $field) && $field['validateMethod'])
								$ret .= ' format="'.implode(' ', $field['validateMethod']).'"';
						}
						$ret .= '>';
						if (is_null($this->_schema))
							$ret .= '<![CDATA['.$object->$key.']]>';
						$ret .= '</'.$field['sqlId'].'>'."\n";
					}
				}
				else
						// display id
						if (is_null($this->_schema))
						$ret .= '<id><![CDATA['.$object->id.']]></id>'."\n";
			}
		}
	
		// display associations
		if (isset($this->_resourceConfiguration['associations']))
		{
			$ret .= '<associations>'."\n";
			foreach ($this->_resourceConfiguration['associations'] as $assocName => $association)
			{
				$ret .= '<'.$assocName.'>'."\n";
				$getter = $this->_resourceConfiguration['associations'][$assocName]['getter'];
				if (method_exists($object, $getter))
				{
					$associationRessources = $object->$getter();
					if (is_array($associationRessources))
						foreach ($associationRessources as $associationRessource)
						{
							$ret .= '<'.$this->_resourceConfiguration['associations'][$assocName]['resource'].' xlink:href="'.$this->_wsUrl.$assocName.'/'.$associationRessource['id'].'">'."\n";
							foreach ($associationRessource as $fieldName => $fieldValue)
							{
								if ($fieldName == 'id')
									$ret .= '<'.$fieldName.'><![CDATA['.$fieldValue.']]></'.$fieldName.'>'."\n";
							}
							$ret .= '</'.$this->_resourceConfiguration['associations'][$assocName]['resource'].'>'."\n";
						}
				}
				$ret .= '</'.$assocName.'>'."\n";
			}
			$ret .= '</associations>'."\n";
		}
		$ret .= '</'.$this->_resourceConfiguration['objectNodeName'].'>'."\n";
		return $ret;
	}
	
	private function closest($input, $words)
	{
		$shortest = -1;
		foreach ($words as $word)
		{
			$lev = levenshtein($input, $word);
			if ($lev == 0)
			{
				$closest = $word;
				$shortest = 0;
				break;
			}
			if ($lev <= $shortest || $shortest < 0)
			{
				$closest = $word;
				$shortest = $lev;
			}
		}
		return $closest;
	}
}
