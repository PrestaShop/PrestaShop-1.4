<?php
define('INSTALL_PATH', dirname(__FILE__));

	$error = "";
	$msg = "";
	$fileElementName = 'fileToUpload';
	
	if(!empty($_FILES[$fileElementName]['error']))
	{
		switch($_FILES[$fileElementName]['error'])
		{

			case '1':
				$error = '38';
				break;
			case '2':
				$error = '39';
				break;
			case '3':
				$error = '40';
				break;
			case '4':
				$error = '41';
				break;

			case '6':
				$error = '42';
				break;
			case '7':
				$error = '43';
				break;
			case '8':
				$error = '44';
				break;
			case '999':
			default:
				$error = '999';
		}
	}
	else
	{
		if(empty($_FILES[$fileElementName]['tmp_name']) OR $_FILES[$fileElementName]['tmp_name'] == 'none')
		{
			$error = '41';
		}
		else
		{				
			list($width, $height, $type, $attr) = getimagesize($_FILES[$fileElementName]['tmp_name']);
			
			if($height == 0)
			{
			$error = '16';
			}
			else
			{
				$newheight = $height > 500 ? 500 : $height;
				$percent = $newheight / $height;
				$newwidth = $width * $percent;
				$newheight = $height * $percent;
				$thumb = imagecreatetruecolor($newwidth, $newheight);
				switch ($type) {
					case 1:
						$sourceImage = imagecreatefromgif($_FILES[$fileElementName]['tmp_name']);
						break;
					case 2:
						$sourceImage = imagecreatefromjpeg($_FILES[$fileElementName]['tmp_name']);
						break;
					case 3:
						$sourceImage = imagecreatefrompng($_FILES[$fileElementName]['tmp_name']);
						break;
					default:
						return false;
				}
				
				imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
				
				if(!is_writable(realpath(INSTALL_PATH.'/../../img').'/logo.jpg')){
						$error = '37';
				}
				else
				{
					if(!imagejpeg($thumb, realpath(INSTALL_PATH.'/../../img').'/logo.jpg', 90))
					{
						$error = '7';
					}
				}
			}
		}
	}		
	echo "{";
	echo "	error: '" . $error . "',\n";
	echo "}";
?>