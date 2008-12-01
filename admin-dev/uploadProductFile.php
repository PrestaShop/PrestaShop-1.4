<?php

define('PS_ADMIN_DIR', getcwd());

include(PS_ADMIN_DIR.'/../config/config.inc.php');

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