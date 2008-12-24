<?php

function blocknewsletter()
{
	// No one will know if the table does not exist :]
	DB::getInstance()->Execute('ALTER TABLE '._DB_PREFIX_.'newsletter ADD `http_referer` VARCHAR(255) NULL');
}

?>