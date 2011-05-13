<?php
class WebserviceSpecificManagementImagesCore implements WebserviceSpecificManagementInterface
{
	protected $objOutput;
	protected $output;
	protected $wsObject;
	
	/**
	 * @var string The extension of the image to display
	 */
	protected $imgExtension = 'jpg';
	
	/**
	 * @var array The type of images (general, categories, manufacturers, suppliers, stores...)
	 */
	private $imageTypes = array(
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
//		'scenes' => array(),
		'stores' => array()
	);
	
	/**
	 * @var string The image type (product, category, general,...)
	 */
	private $imageType = NULL;
	
	/**
	 * @var int The maximum size supported when uploading images, in bytes
	 */
	private $imgMaxUploadSize = 3000000;
	
	/**
	 * @var array The list of supported mime types
	 */
	protected $acceptedImgMimeTypes = array('image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
	
	/**
	 * @var string The product image declination id
	 */
	protected $productImageDeclinationId = NULL;
	
	/**
	 * @var boolean If the current image management has to manage a "default" image (i.e. "No product available")
	 */
	protected $defaultImage = false;
	
	/**
	 * @var string The file path of the image to display. If not null, the image will be displayed, even if the XML output was not empty
	 */
	public $imgToDisplay = null;
	public $imageResource = null;
	
	// ------------------------------------------------
	// GETTERS & SETTERS
	// ------------------------------------------------
	
	
	public function setObjectOutput(WebserviceOutputBuilderCore $obj)
	{
		$this->objOutput = $obj;
		return $this;
	}
	
	public function getObjectOutput()
	{
		return $this->objOutput;
	}
	
	public function setWsObject(WebserviceRequestCore $obj)
	{
		$this->wsObject = $obj;
		return $this;
	}
	
	public function getWsObject()
	{
		return $this->wsObject;
	}
	
	public function getContent()
	{
		if ($this->output != '')
			return $this->objOutput->getObjectRender()->overrideContent($this->output);
		// display image content if needed
		else if ($this->imgToDisplay)
		{
			$imageResource = false;
			switch ($this->imgExtension)
			{
				case 'jpg':
					$imageResource = @imagecreatefromjpeg($this->imgToDisplay);
					break;
				case 'gif':
					$imageResource = @imagecreatefromgif($this->imgToDisplay);
					break;
			}
			if(!$imageResource)
				throw new WebserviceException(sprintf('Unable to load the image "%s"', str_replace(_PS_ROOT_DIR_, '[SHOP_ROOT_DIR]', $this->imgToDisplay)), array(47, 500));
			else
			{
				switch ($this->imgExtension)
				{
					case 'jpg':
						$this->objOutput->setHeaderParams('Content-Type', 'image/jpeg');
						break;
					case 'gif':
						$this->objOutput->setHeaderParams('Content-Type', 'image/gif');
						break;
				}
				return file_get_contents($this->imgToDisplay);
			}
		}
	}
	
	public function manage()
	{
		$this->manageImages();
		return $this->wsObject->getOutputEnabled();
	}
	
	/**
	 * Management of images URL segment
	 * 
	 * @return boolean
	 */
	private function manageImages()
	{
		/*
		 * available cases api/... :
		 *   
		 *   images ("types_list") (N-1)
		 *   	GET    (xml)
		 *   images/general ("general_list") (N-2)
		 *   	GET    (xml)
		 *   images/general/[header,+] ("general") (N-3)
		 *   	GET    (bin)
		 *   	PUT    (bin)
		 *   
		 *   
		 *   images/[categories,+] ("normal_list") (N-2) ([categories,+] = categories, manufacturers, ...) 
		 *   	GET    (xml)
		 *   images/[categories,+]/[1,+] ("normal") (N-3)
		 *   	GET    (bin)
		 *   	PUT    (bin)
		 *   	DELETE
		 *   	POST   (bin) (if image does not exists)
		 *   images/[categories,+]/[1,+]/[small,+] ("normal_resized") (N-4)
		 *   	GET    (bin)
		 *   images/[categories,+]/default ("display_list_of_langs") (N-3)
		 *   	GET    (xml)
		 *   images/[categories,+]/default/[en,+] ("normal_default_i18n")  (N-4)
		 *   	GET    (bin)
		 *   	POST   (bin) (if image does not exists)
		 *      PUT    (bin)
		 *      DELETE
		 *   images/[categories,+]/default/[en,+]/[small,+] ("normal_default_i18n_resized")  (N-5)
		 *   	GET    (bin)
		 *   
		 *   images/product ("product_list")  (N-2)
		 *   	GET    (xml) (list of image)
		 *   images/product/[1,+] ("product_description")  (N-3)
		 *   	GET    (xml) (legend, declinations, xlink to images/product/[1,+]/bin)
		 *   images/product/[1,+]/bin ("product_bin")  (N-4)
		 *   	GET    (bin)
		 *      POST   (bin) (if image does not exists)
		 *   images/product/[1,+]/[1,+] ("product_declination")  (N-4)
		 *   	GET    (bin)
		 *   	POST   (xml) (legend)
		 *   	PUT    (xml) (legend)
		 *      DELETE
		 *   images/product/[1,+]/[1,+]/bin ("product_declination_bin") (N-5)
		 *   	POST   (bin) (if image does not exists)
		 *   	GET    (bin)
		 *   	PUT    (bin)
		 *   images/product/[1,+]/[1,+]/[small,+] ("product_declination_resized") (N-5)
		 *   	GET    (bin)
		 *   images/product/default ("product_default") (N-3)
		 *   	GET    (bin)
		 *   images/product/default/[en,+] ("product_default_i18n") (N-4)
		 *   	GET    (bin)
		 *      POST   (bin)
		 *      PUT   (bin)
		 *      DELETE
		 *   images/product/default/[en,+]/[small,+] ("product_default_i18n_resized") (N-5)
		 * 		GET    (bin)
		 * 
		 */
		
		// Pre configuration...
		if(isset($this->wsObject->urlSegment))
		{
			if (count($this->wsObject->urlSegment) == 1)
				$this->wsObject->urlSegment[1] = '';
			if (count($this->wsObject->urlSegment) == 2)
				$this->wsObject->urlSegment[2] = '';
			if (count($this->wsObject->urlSegment) == 3)
				$this->wsObject->urlSegment[3] = '';
			if (count($this->wsObject->urlSegment) == 4)
				$this->wsObject->urlSegment[4] = '';
			if (count($this->wsObject->urlSegment) == 5)
			$this->wsObject->urlSegment[5] = '';
		}
		
		$this->imageType = $this->wsObject->urlSegment[1];
		
		switch ($this->wsObject->urlSegment[1])
		{
			// general images management : like header's logo, invoice logo, etc...
			case 'general':
				return $this->manageGeneralImages();
				break;
			// normal images management : like the most entity images (categories, manufacturers..)...
			case 'categories':
			case 'manufacturers':
			case 'suppliers':
			case 'stores':
				switch ($this->wsObject->urlSegment[1])
				{
					case 'categories':
						$directory = _PS_CAT_IMG_DIR_;
						break;
					case 'manufacturers':
						$directory = _PS_MANU_IMG_DIR_;
						break;
					case 'suppliers':
						$directory = _PS_SUPP_IMG_DIR_;
						break;
					case 'stores':
						$directory = _PS_STORE_IMG_DIR_;
						break;
				}
				return $this->manageDeclinatedImages($directory);
				break;
			
			// product image management : many image for one entity (product)
			case 'products':
				return $this->manageProductImages();
				break;
			
			// images root node management : many image for one entity (product)
			case '':
				$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('image_types', array());
				foreach ($this->imageTypes as $imageTypeName => $imageType)
				{
					$more_attr = array(
						'xlink_resource' => $this->wsObject->wsUrl.$this->wsObject->urlSegment[0].'/'.$imageTypeName,
						'get' => 'true', 'put' => 'false', 'post' => 'false', 'delete' => 'false', 'head' => 'true',
						'upload_allowed_mimetypes' => implode(', ', $this->acceptedImgMimeTypes)
					);
					$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader($imageTypeName, array(), $more_attr, false);
				}
				$this->output .= $this->objOutput->getObjectRender()->renderNodeFooter('image_types', array());
				return true;
				break;
			
			default:
				$exception = new WebserviceException(sprintf('Image of type "%s" does not exists', $this->wsObject->urlSegment[1]), array(48, 400));
				throw $exception->setDidYouMean($this->wsObject->urlSegment[1], array_keys($this->imageTypes));
		}
	}
/**
	 * Management of general images
	 * 
	 * @return boolean
	 */
	private function manageGeneralImages()
	{
		$path = '';
		$alternative_path = '';
		switch ($this->wsObject->urlSegment[2])
		{
			// Set the image path on display in relation to the header image
			case 'header':
				if (in_array($this->wsObject->method, array('GET','HEAD','PUT')))
					$path = _PS_IMG_DIR_.'logo.jpg';
				else
					throw new WebserviceException('This method is not allowed with general image resources.', array(49, 405));
				break;
			
			// Set the image path on display in relation to the mail image
			case 'mail':
				if (in_array($this->wsObject->method, array('GET','HEAD','PUT')))
				{
					$path = _PS_IMG_DIR_.'logo_mail.jpg';
					$alternative_path = _PS_IMG_DIR_.'logo.jpg';
				}
				else
					throw new WebserviceException('This method is not allowed with general image resources.', array(50, 405));
				break;
			
			// Set the image path on display in relation to the invoice image
			case 'invoice':
				if (in_array($this->wsObject->method, array('GET','HEAD','PUT')))
				{
					$path = _PS_IMG_DIR_.'logo_invoice.jpg';
					$alternative_path = _PS_IMG_DIR_.'logo.jpg';
				}
				else
					throw new WebserviceException('This method is not allowed with general image resources.', array(51, 405));
				break;
			
			// Set the image path on display in relation to the icon store image
			case 'store_icon':
				if (in_array($this->wsObject->method, array('GET','HEAD','PUT')))
				{
					$path = _PS_IMG_DIR_.'logo_stores.gif';
					$this->imgExtension = 'gif';
				}
				else
					throw new WebserviceException('This method is not allowed with general image resources.', array(52, 405));
				break;
			
			// List the general image types
			case '':
				$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('general_image_types', array());
				foreach ($this->imageTypes['general'] as $generalImageTypeName => $generalImageType)
				{
					$more_attr = array(
						'xlink_resource' => $this->wsObject->wsUrl.$this->wsObject->urlSegment[0].'/'.$this->wsObject->urlSegment[1].'/'.$generalImageTypeName,
						'get' => 'true', 'put' => 'true', 'post' => 'false', 'delete' => 'false', 'head' => 'true',
						'upload_allowed_mimetypes' => implode(', ', $this->acceptedImgMimeTypes)
					);
					$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader($generalImageTypeName, array(), $more_attr, false);
				}
				$this->output .= $this->objOutput->getObjectRender()->renderNodeFooter('general_image_types', array());
				return true;
				break;
			
			// If the image type does not exist...
			default:
				$exception = new WebserviceException(sprintf('General image of type "%s" does not exists', $this->wsObject->urlSegment[2]), array(53, 400));
				throw $exception->setDidYouMean($this->wsObject->urlSegment[2], array_keys($this->imageTypes['general']));
		}
		// The general image type is valid, now we try to do action in relation to the method
		switch($this->wsObject->method)
		{
			case 'GET':
			case 'HEAD':
				$this->imgToDisplay = ($alternative_path != '' && file_exists($alternative_path)) ? $alternative_path : $path;
				return true;
				break;
			case 'PUT':
				if ($this->writePostedImageOnDisk($path, NULL, NULL))
				{
					$this->imgToDisplay = $path;
					return true;
				}
				else
					throw new WebserviceException('Error while copying image to the directory', array(54, 400));
				break;
		}
	}
	
	private function manageDefaultDeclinatedImages($directory, $normal_image_sizes)
	{
		$this->defaultImage = true;
		// Get the language iso code list
		$langList = Language::getIsoIds(true);
		$langs = array();
		$defaultLang = Configuration::get('PS_LANG_DEFAULT');
		foreach ($langList as $lang)
		{
			if ($lang['id_lang'] == $defaultLang)
				$defaultLang = $lang['iso_code'];
			$langs[] = $lang['iso_code'];
		}
		
		// Display list of languages
		if($this->wsObject->urlSegment[3] == '' && $this->wsObject->method == 'GET')
		{
			$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('languages', array());
			foreach ($langList as $lang)
			{
				$more_attr = array(
					'xlink_resource' => $this->wsObject->wsUrl.'images/'.$this->imageType.'/default/'.$lang['iso_code'],
					'get' => 'true', 'put' => 'true', 'post' => 'true', 'delete' => 'true', 'head' => 'true',
					'upload_allowed_mimetypes' => implode(', ', $this->acceptedImgMimeTypes),
					'iso'=>$lang['iso_code']
				);
				$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('language', array(), $more_attr, false);
			}
			
			$this->output .= $this->objOutput->getObjectRender()->renderNodeFooter('languages', array());
			return true;
		}
		else
		{
			$lang_iso = $this->wsObject->urlSegment[3];
			$image_size = $this->wsObject->urlSegment[4];
			if ($image_size != '')
				$filename = $directory.$lang_iso.'-default-'.$image_size.'.jpg';
			else
				$filename = $directory.$lang_iso.'.jpg';
			$filename_exists = file_exists($filename);
			return $this->manageDeclinatedImagesCRUD($filename_exists, $filename, $normal_image_sizes, $directory);// @todo : [feature] @see todo#1
		}
	}
	
	private function manageListDeclinatedImages($directory, $normal_image_sizes)
	{
		// Check if method is allowed
		if ($this->wsObject->method != 'GET')
			throw new WebserviceException('This method is not allowed for listing category images.', array(55, 405));
		
		$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('image_types', array());
		foreach ($normal_image_sizes as $image_size)
			$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('image_type', array(), array('id' => $image_size['id_image_type'], 'name' => $image_size['name'], 'xlink_resource'=>$this->wsObject->wsUrl.'image_types/'.$image_size['id_image_type']), false);
		$this->output .= $this->objOutput->getObjectRender()->renderNodeFooter('image_types', array());
		$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('images', array());
		$nodes = scandir($directory);
		$lastId = 0;
		foreach ($nodes as $node)
			// avoid too much preg_match...
			if ($node != '.' && $node != '..' && $node != '.svn')
			{
				if ($this->imageType == 'products')
				{
					preg_match('/^(\d+)-(\d+)\.jpg*$/Ui', $node, $matches);
					if (isset($matches[1]) && $matches[1] != $lastId)
					{
						$lastId = $matches[1];
						$id = $matches[1];
						$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('image', array(), array('id' => $id, 'xlink_resource'=>$this->wsObject->wsUrl.'images/'.$this->imageType.'/'.$id), false);
					}
				}
				else
				{
					preg_match('/^(\d+)\.jpg*$/Ui', $node, $matches);
					if (isset($matches[1]))
					{
						$id = $matches[1];
						$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('image', array(), array('id' => $id, 'xlink_resource'=>$this->wsObject->wsUrl.'images/'.$this->imageType.'/'.$id), false);
					}
				}
			}
		$this->output .= $this->objOutput->getObjectRender()->renderNodeFooter('images', array());
		return true;
	}
	
	private function manageEntityDeclinatedImages($directory, $normal_image_sizes)
	{
		$normal_image_size_names = array();
		foreach ($normal_image_sizes as $normal_image_size)
			$normal_image_size_names[] = $normal_image_size['name'];
		// If id is detected
		$object_id = $this->wsObject->urlSegment[2];
		if (!Validate::isUnsignedId($object_id))
			throw new WebserviceException('The image id is invalid. Please set a valid id or the "default" value', array(60, 400));
		
		// For the product case
		if ($this->imageType == 'products')
		{
			$image_size = $this->wsObject->urlSegment[4];
			$image_id = $this->wsObject->urlSegment[3];// @todo #1: [feature] Post management for product images.
			// Get available image ids
			$available_image_ids = array();
			$nodes = scandir($directory);
			foreach ($nodes as $node)
				// avoid too much preg_match...
				if ($node != '.' && $node != '..' && $node != '.svn')
				{
					preg_match('/^'.intval($object_id).'-(\d+)\.jpg*$/Ui', $node, $matches);
					if (isset($matches[1])) {
						$available_image_ids[] = $matches[1];
					}
				}
			/*
			@todo more explanation needed
			if (!count($available_image_ids))
			{
				//$this->setError(400, 'This image id does not exist', 56);
			}*/
			
			// If an image id is specified
			if ($this->wsObject->urlSegment[3] != '')
			{
				if ($this->wsObject->urlSegment[3] == 'bin')
				{
					if ($this->wsObject->method == 'POST')
						$orig_filename = $directory.$object_id.'-'.$this->productImageDeclinationId.'-'.$available_image_ids[0].'.jpg';
					else
						$orig_filename = $directory.$object_id.'-'.$available_image_ids[0].'.jpg';// @todo get the default one
				}
				elseif (!Validate::isUnsignedId($object_id) || !in_array($this->wsObject->urlSegment[3], $available_image_ids))
				{
					throw new WebserviceException('This image id does not exist', array(57, 400));
				}
				else
				{
					$image_id = $this->wsObject->urlSegment[3];
					$orig_filename = $directory.$object_id.'-'.$image_id.'.jpg';
					$image_size = $this->wsObject->urlSegment[4];
					$filename = $directory.$object_id.'-'.$image_id.'-'.$image_size.'.jpg';
				}
			}
			// display the list of declinated images
			else
			{
				if ($available_image_ids)
				{
					$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('image', array(), array('id'=>$object_id));
					foreach ($available_image_ids as $available_image_id)
						$this->output .= $this->objOutput->getObjectRender()->renderNodeHeader('declination', array(), array('id'=>$available_image_id, 'xlink_resource'=>$this->wsObject->wsUrl.'images/'.$this->imageType.'/'.$object_id.'/'.$available_image_id), false);
					$this->output .= $this->objOutput->getObjectRender()->renderNodeFooter('image', array());
				}
				else
				{
					$this->objOutput->setStatus(404);
					$this->wsObject->setOutputEnabled(false);
				}
			}
			
		}
		// for all other cases
		else
		{
			$orig_filename = $directory.$object_id.'.jpg';
			$image_size = $this->wsObject->urlSegment[3];
			$filename = $directory.$object_id.'-'.$image_size.'.jpg';
		}
		
		// in case of declinated images list of a product is get
		if ($this->output != '')
			return true;
		// If a size was given try to display it
		elseif (isset($image_size) && $image_size != '')
		{
			// Check the given size
			if ($this->imageType == 'products' && $image_size == 'bin')
				$filename = $directory.$object_id.'-'.$image_id.'.jpg';
			elseif (!in_array($image_size, $normal_image_size_names))
			{
				$exception = new WebserviceException('This image size does not exist', array(58, 400));
				throw $exception->setDidYouMean($image_size, $normal_image_size_names);
			}
			
			if (!file_exists($filename))
				throw new WebserviceException('This image does not exist on disk', array(59, 500));
			
			// Display the resized specific image
			$this->imgToDisplay = $filename;
			return true;
		}
		// Management of the original image (GET, PUT, POST, DELETE)
		elseif (isset($orig_filename))
		{
			$orig_filename_exists = file_exists($orig_filename);
			return $this->manageDeclinatedImagesCRUD($orig_filename_exists, $orig_filename, $normal_image_sizes, $directory);
		}
	}

	/**
	 * Management of normal images (as categories, suppliers, manufacturers and stores)
	 * 
	 * @param string $directory the file path of the root of the images folder type
	 * @return boolean
	 */
	private function manageDeclinatedImages($directory)
	{
		/*
		 *ok    GET    (bin)
		 *ok images/product ("product_list")  (N-2)
		 *ok	GET    (xml) (list of image)
		 *ok images/product/[1,+] ("product_description")  (N-3)
		 *   	GET    (xml) (legend, declinations, xlink to images/product/[1,+]/bin)
		 *ok images/product/[1,+]/bin ("product_bin")  (N-4)
		 *ok 	GET    (bin)
		 *      POST   (bin) (if image does not exists)
		 *ok images/product/[1,+]/[1,+] ("product_declination")  (N-4)
		 *ok 	GET    (bin)
		 *   	POST   (xml) (legend)
		 *   	PUT    (xml) (legend)
		 *      DELETE
		 *ok images/product/[1,+]/[1,+]/bin ("product_declination_bin") (N-5)
		 *   	POST   (bin) (if image does not exists)
		 *ok 	GET    (bin)
		 *   	PUT    (bin)
		 *   images/product/[1,+]/[1,+]/[small,+] ("product_declination_resized") (N-5)
		 *ok 	GET    (bin)
		 *ok images/product/default ("product_default") (N-3)
		 *ok 	GET    (bin)
		 *ok images/product/default/[en,+] ("product_default_i18n") (N-4)
		 *ok 	GET    (bin)
		 *      POST   (bin)
		 *      PUT   (bin)
		 *      DELETE
		 *ok images/product/default/[en,+]/[small,+] ("product_default_i18n_resized") (N-5)
		 *ok	GET    (bin)
		 * 
		 * */
		
		
		// Get available image sizes for the current image type
		$normal_image_sizes = ImageType::getImagesTypes($this->imageType);
		switch ($this->wsObject->urlSegment[2])
		{
			// Match the default images
			case 'default':
				return $this->manageDefaultDeclinatedImages($directory, $normal_image_sizes);
				break;
			// Display the list of images
			case '':
				return $this->manageListDeclinatedImages($directory, $normal_image_sizes);
				break;
			default:
				return $this->manageEntityDeclinatedImages($directory, $normal_image_sizes, $qsdqsd);
				break;
		}
	}
	
	private function manageProductImages()
	{
		// add a new declinated image to the product
		$max = 0;
		foreach (scandir(_PS_PROD_IMG_DIR_) as $dir)
		{
			$matches = array();
			preg_match('/^'.intval($this->wsObject->urlSegment[2]).'-(\d+)\.jpg*$/Ui', $dir, $matches);
			if (isset($matches[1]))
				$max = max($max, (int)($matches[1]));
		}
		$this->productImageDeclinationId = $max++; 
		$this->manageDeclinatedImages(_PS_PROD_IMG_DIR_);
	}
	
	/**
	 * Management of normal images CRUD
	 * 
	 * @param boolean $filename_exists if the filename exists
	 * @param string $filename the image path
	 * @param array $imageSizes The
	 * @param string $directory
	 * @return boolean
	 */
	private function manageDeclinatedImagesCRUD($filename_exists, $filename, $imageSizes, $directory)
	{
		switch ($this->wsObject->method)
		{
			// Display the image
			case 'GET':
			case 'HEAD':
				if ($filename_exists)
					$this->imgToDisplay = $filename;
				else
					throw new WebserviceException('This image does not exist on disk', array(61, 500));
				break;
			// Modify the image
			case 'PUT':
				if ($filename_exists)
					if ($this->writePostedImageOnDisk($filename, NULL, NULL, $imageSizes, $directory))
					{
						$this->imgToDisplay = $filename;
						return true;
					}
					else
						throw new WebserviceException('Unable to save this image.', array(62, 500));
				else
					throw new WebserviceException('This image does not exist on disk', array(63, 500));
				break;
			// Delete the image
			case 'DELETE':
				if ($filename_exists)
					return $this->deleteImageOnDisk($filename, $imageSizes, $directory);
				else
					throw new WebserviceException('This image does not exist on disk', array(64, 500));
				break;
			// Add the image
			case 'POST':
				if ($filename_exists)
					throw new WebserviceException('This image already exists. To modify it, please use the PUT method', array(65, 400));
				else
				{
					if ($this->writePostedImageOnDisk($filename, NULL, NULL, $imageSizes, $directory))
					{
						$this->imgToDisplay = $filename;
						return true;
					}
					else
						throw new WebserviceException('Unable to save this image', array(66, 500));
				}
				break;
			default : 
				throw new WebserviceException('This method is not allowed', array(67, 405));
		}
	}
	
	/**
	 * 	Delete the image on disk
	 * 
	 * @param string $filePath the image file path
	 * @param array $imageTypes The differents sizes
	 * @param string $parentPath The parent path
	 * @return boolean
	 */
	private function deleteImageOnDisk($filePath, $imageTypes = NULL, $parentPath = NULL)
	{
		$this->wsObject->setOutputEnabled(false);
		if (file_exists($filePath))
		{
			// delete image on disk
			@unlink($filePath);
			
			// Delete declinated image if needed
			if ($imageTypes)
			{
				foreach ($imageTypes as $imageType)
				{
					if ($this->defaultImage) // @todo products images too !!
						$declination_path = $parentPath.$this->wsObject->urlSegment[3].'-default-'.$imageType['name'].'.jpg';
					else
						$declination_path = $parentPath.$this->wsObject->urlSegment[2].'-'.$imageType['name'].'.jpg';
					if (!@unlink($declination_path))
					{
						$this->objOutput->setStatus(204);
						return false;
					}
				}
			}
			return true;
		}
		else
		{
			$this->objOutput->setStatus(204);
			return false;
		}
	}
	
	/**
	 * Write the image on disk
	 * 
	 * @param string $basePath
	 * @param string $newPath
	 * @param int $destWidth
	 * @param int $destHeight
	 * @param array $imageTypes
	 * @param string $parentPath
	 * @return string
	 */
	private function writeImageOnDisk($basePath, $newPath, $destWidth = NULL, $destHeight = NULL, $imageTypes = NULL, $parentPath = NULL)
	{
		list($sourceWidth, $sourceHeight, $type, $attr) = getimagesize($basePath);
		if (!$sourceWidth)
			throw new WebserviceException('Image width was null', array(68, 400));
		if ($destWidth == NULL) $destWidth = $sourceWidth;
		if ($destHeight == NULL) $destHeight = $sourceHeight;
		switch ($type)
		{
			case 1:
				$sourceImage = imagecreatefromgif($basePath);
				break;
			case 3:
				$sourceImage = imagecreatefrompng($basePath);
				break;
			case 2:
			default:
				$sourceImage = imagecreatefromjpeg($basePath);
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
		
		// Build the image
		if (
			!($destImage = imagecreatetruecolor($destWidth, $destHeight)) ||
			!($white = imagecolorallocate($destImage, 255, 255, 255)) ||
			!imagefill($destImage, 0, 0, $white) ||
			!imagecopyresampled($destImage, $sourceImage, $borderWidth, $borderHeight, 0, 0, $nextWidth, $nextHeight, $sourceWidth, $sourceHeight) ||
			!imagecolortransparent($destImage, $white)
		)
			throw new WebserviceException(sprintf('Unable to build the image "%s".', str_replace(_PS_ROOT_DIR_, '[SHOP_ROOT_DIR]', $newPath)), array(69, 500));
			
		// Write it on disk
		$imaged = false;
		switch ($this->imgExtension)
		{
			case 'gif':
				$imaged = imagegif($destImage, $newPath);
				break;
			case 'png':
				$imaged = imagepng($destImage, $newPath, 7);
				break;
			case 'jpeg':
			default:
				$imaged = imagejpeg($destImage, $newPath, 90);
				break;
		}
		imagedestroy($destImage);
		if (!$imaged)
			throw new WebserviceException(sprintf('Unable to write the image "%s".', str_replace(_PS_ROOT_DIR_, '[SHOP_ROOT_DIR]', $newPath)), array(70, 500));
		
		// Write image declinations if present
		if ($imageTypes)
		{
			foreach ($imageTypes as $imageType)
			{
				if ($this->defaultImage)
					$declination_path = $parentPath.$this->wsObject->urlSegment[3].'-default-'.$imageType['name'].'.jpg';
				else
				{
					if ($this->imageType == 'products')
					{
						$declination_path = $parentPath.$this->wsObject->urlSegment[2].'-'.$this->productImageDeclinationId.'-'.$imageType['name'].'.jpg';
					}
					else
						$declination_path = $parentPath.$this->wsObject->urlSegment[2].'-'.$imageType['name'].'.jpg';
				}
				if (!$this->writeImageOnDisk($basePath, $declination_path, $imageType['width'], $imageType['height']))
					throw new WebserviceException(sprintf('Unable to save the declination "%s" of this image.', $imageType['name']), array(71, 500));
			}
		}
		return !$this->hasErrors() ? $newPath : false;
	}
	
	/**
	 * Write the posted image on disk
	 * 
	 * @param string $sreceptionPath
	 * @param int $destWidth
	 * @param int $destHeight
	 * @param array $imageTypes
	 * @param string $parentPath
	 * @return boolean
	 */
	private function writePostedImageOnDisk($receptionPath, $destWidth = NULL, $destHeight = NULL, $imageTypes = NULL, $parentPath = NULL)
	{
		if ($this->wsObject->method == 'PUT')
		{
			if (isset($_FILES['image']['tmp_name']) AND $_FILES['image']['tmp_name'])
			{
				$file = $_FILES['image'];
				if ($file['size'] > $this->imgMaxUploadSize)
					throw new WebserviceException(sprintf('The image size is too large (maximum allowed is %d KB)', ($this->imgMaxUploadSize/1000)), array(72, 400));
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
				if(!$mime_type || !in_array($mime_type, $this->acceptedImgMimeTypes))
					throw new WebserviceException('This type of image format not recognized, allowed formats are: '.implode('", "', $this->acceptedImgMimeTypes), array(73, 400));
				// Check error while uploading
				elseif ($file['error'])
					throw new WebserviceException('Error while uploading image. Please change your server\'s settings', array(74, 400));
				
				// Try to copy image file to a temporary file
				if (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($_FILES['image']['tmp_name'], $tmpName))
					throw new WebserviceException('Error while copying image to the temporary directory', array(75, 400));
				// Try to copy image file to the image directory
				else
				{
					return $this->writeImageOnDisk($tmpName, $receptionPath, $destWidth, $destHeight, $imageTypes, $parentPath);
				}
				unlink($tmpName);
			}
			else
				throw new WebserviceException('Please set an "image" parameter with image data for value', array(76, 400));
		}
		else
			throw new WebserviceException('Method '.$this->wsObject->method.' is not allowed for an image resource', array(77, 405));
	}
}