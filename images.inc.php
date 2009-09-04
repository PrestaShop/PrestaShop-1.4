<?php

/**
  * Generate a cached thumbnail for object lists (eg. carrier, order states...etc)
  *
  * @param string $image Real image filename
  * @param string $cacheImage Cached filename
  * @param integer $size Desired size
  */
function cacheImage($image, $cacheImage, $size, $imageType = 'jpg')
{
	if (file_exists($image))
	{
		if (!file_exists(_PS_TMP_IMG_DIR_.$cacheImage))
		{
			$imageGd = ($imageType == 'gif' ? imagecreatefromgif($image) : imagecreatefromjpeg($image));
			$x = imagesx($imageGd);
			$y = imagesy($imageGd);
			
			/* Size is already ok */
			if ($y < $size) 
				copy($image, _PS_TMP_IMG_DIR_.$cacheImage);

			/* We need to resize */
			else
			{
				$ratioX = $x / ($y / $size);
				$newImage = ($imageType == 'gif' ? imagecreate($ratioX, $size) : imagecreatetruecolor($ratioX, $size));
				
				/* Allow to keep nice look even if resized */
				$white = imagecolorallocate($newImage, 255, 255, 255);
				imagefill($newImage, 0, 0, $white);
				imagecopyresampled($newImage, $imageGd, 0, 0, 0, 0, $ratioX, $size, $x, $y);
				imagecolortransparent($newImage, $white);

				/* Quality alteration and image creation */
				if ($imageType == 'gif')
					imagegif($newImage, _PS_TMP_IMG_DIR_.$cacheImage);
				else
					imagejpeg($newImage, _PS_TMP_IMG_DIR_.$cacheImage, 86);
			}
		}
		return '<img src="../img/tmp/'.$cacheImage.'" alt="" class="imgm" />';
	}
	return '';
}

/**
  * Check image upload
  *
  * @param array $file Upload $_FILE value
  * @param integer $maxFileSize Maximum upload size
  */
function	checkImage($file, $maxFileSize)
{
	if ($file['size'] > $maxFileSize)
		return Tools::displayError('image is too large').' ('.($file['size'] / 1000).Tools::displayError('KB').'). '.Tools::displayError('Maximum allowed:').' '.($maxFileSize / 1000).Tools::displayError('KB');
	if (!isPicture($file))
		return Tools::displayError('image format not recognized, allowed formats are: .gif, .jpg, .png');
	if ($file['error'])
		return Tools::displayError('error while uploading image; change your server\'s settings');
	return false;
}

function isPicture($file)
{
    /* Detect mime content type */
    $mime_type = false;
    $types = array('image/gif', 'image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');

    if (function_exists('finfo_open'))
    {
        $finfo = finfo_open(FILEINFO_MIME);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    }
    elseif (function_exists('mime_content_type'))
        $mime_type = mime_content_type($file['tmp_name']);
    elseif (function_exists('exec'))
        $mime_type = trim(exec('file -b --mime-type '.escapeshellarg($file['tmp_name'])));
	if (empty($mime_type) || $mime_type == 'regular file')
		$mime_type = $file['type'];
	if (($pos = strpos($mime_type, ';')) !== false)
		$mime_type = substr($mime_type, 0, $pos);
    // is it a picture ?
    return $mime_type && in_array($mime_type, $types);
}

/**
  * Check icon upload
  *
  * @param array $file Upload $_FILE value
  * @param integer $maxFileSize Maximum upload size
  */
function	checkIco($file, $maxFileSize)
{
	if ($file['size'] > $maxFileSize)
		return Tools::displayError('image is too large').' ('.($file['size'] / 1000).'ko). '.Tools::displayError('Maximum allowed:').' '.($maxFileSize / 1000).'ko';
	if (substr($file['name'], -4) != '.ico')
		return Tools::displayError('image format not recognized, allowed formats are: .ico');
	if ($file['error'])
		return Tools::displayError('error while uploading image; change your server\'s settings');
	return false;
}

/**
  * Resize, cut and optimize image
  *
  * @param array $sourceFile Image object from $_FILE
  * @param string $destFile Destination filename
  * @param integer $destWidth Desired width (optional)
  * @param integer $destHeight Desired height (optional)
  *
  * @return boolean Operation result
  */
function imageResize($sourceFile, $destFile, $destWidth = NULL, $destHeight = NULL, $fileType = 'jpg')
{
	list($sourceWidth, $sourceHeight, $type, $attr) = getimagesize($sourceFile);
	if (!$sourceWidth)
		return false;
	if ($destWidth == NULL) $destWidth = $sourceWidth;
	if ($destHeight == NULL) $destHeight = $sourceHeight;

	$sourceImage = createSrcImage($type, $sourceFile);

	$widthDiff = $destWidth / $sourceWidth;
	$heightDiff = $destHeight / $sourceHeight;
	
	if ($widthDiff > 1 AND $heightDiff > 1)
	{
		$nextWidth = $sourceWidth;
		$nextHeight = $sourceHeight;
	}
	else
	{
		if (intval(Configuration::get('PS_IMAGE_GENERATION_METHOD')) == 2 OR (intval(Configuration::get('PS_IMAGE_GENERATION_METHOD')) == 0 AND $widthDiff > $heightDiff))
		{
			$nextHeight = $destHeight;
			$nextWidth = intval(($sourceWidth * $nextHeight) / $sourceHeight);
			$destWidth = (intval(Configuration::get('PS_IMAGE_GENERATION_METHOD')) == 0 ? $destWidth : $nextWidth);
		}
		else
		{
			$nextWidth = $destWidth;
			$nextHeight = intval($sourceHeight * $destWidth / $sourceWidth);
			$destHeight = (intval(Configuration::get('PS_IMAGE_GENERATION_METHOD')) == 0 ? $destHeight : $nextHeight);
		}
	}
	
	$borderWidth = intval(($destWidth - $nextWidth) / 2);
	$borderHeight = intval(($destHeight - $nextHeight) / 2);
	
	$destImage = imagecreatetruecolor($destWidth, $destHeight);

	$white = imagecolorallocate($destImage, 255, 255, 255);
	imagefill($destImage, 0, 0, $white);

	imagecopyresampled($destImage, $sourceImage, $borderWidth, $borderHeight, 0, 0, $nextWidth, $nextHeight, $sourceWidth, $sourceHeight);
	imagecolortransparent($destImage, $white);
	return (returnDestImage($fileType, $destImage, $destFile));
}

/**
  * Cut image
  *
  * @param array $srcFile Image object from $_FILE
  * @param string $destFile Destination filename
  * @param integer $destWidth Desired width (optional)
  * @param integer $destHeight Desired height (optional)
  *
  * @return boolean Operation result
  */
function	imageCut($srcFile, $destFile, $destWidth = NULL, $destHeight = NULL, $fileType = 'jpg', $destX = 0, $destY = 0)
{
	if (!isset($srcFile['tmp_name']) OR !file_exists($srcFile['tmp_name']))
		return false;

	// Source infos
	$srcInfos = getimagesize($srcFile['tmp_name']);
	$src['width'] = $srcInfos[0];
	$src['height'] = $srcInfos[1];
	$src['ressource'] = createSrcImage($srcInfos[2], $srcFile['tmp_name']);
	
	// Destination infos
	$dest['x'] = $destX;
	$dest['y'] = $destY;
	$dest['width'] = $destWidth != NULL ? $destWidth : $src['width'];
	$dest['height'] = $destHeight != NULL ? $destHeight : $src['height'];
	$dest['ressource'] = createDestImage($dest['width'], $dest['height']);
	
	$white = imagecolorallocate($dest['ressource'], 255, 255, 255);
	imagecopyresampled($dest['ressource'], $src['ressource'], 0, 0, $dest['x'], $dest['y'], $dest['width'], $dest['height'], $dest['width'], $dest['height']);
	imagecolortransparent($dest['ressource'], $white);
	$return = returnDestImage($fileType, $dest['ressource'], $destFile);
	return	($return);
}

function	createSrcImage($type, $filename)
{
	switch ($type)
	{
		case 1:
			return imagecreatefromgif($filename);
			break;
		case 3:
			return imagecreatefrompng($filename);
			break;
		case 2:
		default:
			return imagecreatefromjpeg($filename);
			break;
	}
}

function	createDestImage($width, $height)
{
	$image = imagecreatetruecolor($width, $height);
	$white = imagecolorallocate($image, 255, 255, 255);
	imagefill($image, 0, 0, $white);
	return $image;
}

function returnDestImage($type, $ressource, $filename)
{
	$flag = false;
	switch ($type)
	{
		case 'gif':
			$flag = imagegif($ressource, $filename);
			break;
		case 'png':
			$flag = imagepng($ressource, $filename, 7);
			break;
		case 'jpeg':
		default:
			$flag = imagejpeg($ressource, $filename, 90);
			break;
	}
	imagedestroy($ressource);
	return $flag;
}

/**
  * Delete product or category image
  *
  * @param integer $id_item Product or category id
  * @param integer $id_image Image id
  */
function deleteImage($id_item, $id_image = NULL)
{
	$path = ($id_image) ? _PS_PROD_IMG_DIR_ : _PS_CAT_IMG_DIR_;
	$table = ($id_image) ? 'product' : 'category';
	
	if (file_exists(_PS_TMP_IMG_DIR_.$table.'_'.$id_item.'.jpg'))
		unlink(_PS_TMP_IMG_DIR_.$table.'_'.$id_item.'.jpg');
	
	if ($id_image AND file_exists($path.$id_item.'-'.$id_image.'.jpg'))
		unlink($path.$id_item.'-'.$id_image.'.jpg');
	elseif (!$id_image AND file_exists($path.$id_item.'.jpg'))
		unlink($path.$id_item.'.jpg');
	/* Auto-generated images */
	$imagesTypes = ImageType::getImagesTypes();
	foreach ($imagesTypes AS $k => $imagesType)
		if ($id_image AND file_exists($path.$id_item.'-'.$id_image.'-'.$imagesType['name'].'.jpg'))
			unlink($path.$id_item.'-'.$id_image.'-'.$imagesType['name'].'.jpg');
		elseif (!$id_image AND file_exists($path.$id_item.'-'.$imagesType['name'].'.jpg'))
			unlink($path.$id_item.'-'.$imagesType['name'].'.jpg');
	/* BO "mini" image */
	if (file_exists(_PS_TMP_IMG_DIR_.$table.'_mini_'.$id_item.'.jpg'))
		unlink(_PS_TMP_IMG_DIR_.$table.'_mini_'.$id_item.'.jpg');
	return true;
}

?>