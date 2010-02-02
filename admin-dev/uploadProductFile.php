<?php

define('PS_ADMIN_DIR', getcwd());

include(PS_ADMIN_DIR.'/../config/config.inc.php');

if (!class_exists('Cookie'))
	exit();

$cookie = new Cookie('psAdmin', substr($_SERVER['SCRIPT_NAME'], strlen(__PS_BASE_URI__), -10));
if (!$cookie->isLoggedBack())
	die;

if (isset($_FILES['virtual_product_file']) AND is_uploaded_file($_FILES['virtual_product_file']['tmp_name']) AND 
(isset($_FILES['virtual_product_file']['error']) AND !$_FILES['virtual_product_file']['error'])	OR 
(!empty($_FILES['virtual_product_file']['tmp_name']) AND $_FILES['virtual_product_file']['tmp_name'] != 'none'))
{
	$filename = $_FILES['virtual_product_file']['name'];
	$file = $_FILES['virtual_product_file']['tmp_name'];
	$newfilename = ProductDownload::getNewFilename();

	if (!copy($file, _PS_DOWNLOAD_DIR_.$newfilename))
	{
		header('HTTP/1.1 500 Error');
		echo '<return result="error" msg="no rights" filename="'.$filename.'" />';
	}
	@unlink($file);

	header('HTTP/1.1 200 OK');
	echo '<return result="success" msg="'.$newfilename.'" filename="'.$filename.'" />';
}
else
{
	header('HTTP/1.1 500 Error');
	echo '<return result="error" msg="big error" filename="'.ProductDownload::getNewFilename().'" />';
}

?>
