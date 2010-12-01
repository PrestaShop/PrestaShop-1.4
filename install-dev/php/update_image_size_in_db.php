<?php

function update_image_size_in_db()
{
	if (file_exists(realpath(INSTALL_PATH.'/../img').'/logo.jpg'))
	{
		list($width, $height, $type, $attr) = getimagesize(realpath(INSTALL_PATH.'/../img').'/logo.jpg');
		Configuration::updateValue('SHOP_LOGO_WIDTH', (int)round($width));
		Configuration::updateValue('SHOP_LOGO_HEIGHT', (int)round($height));
	}
	if (file_exists(realpath(INSTALL_PATH.'/../modules/editorial').'/homepage_logo.jpg'))
	{
		list($width, $height, $type, $attr) = getimagesize(realpath(INSTALL_PATH.'/../modules/editorial').'/homepage_logo.jpg');
		Configuration::updateValue('EDITORIAL_IMAGE_WIDTH', (int)round($width));
		Configuration::updateValue('EDITORIAL_IMAGE_HEIGHT', (int)round($height));
	}
}
