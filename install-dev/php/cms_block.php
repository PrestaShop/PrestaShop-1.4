<?php
function alter_cms_block()
{
	if (!Db::getInstance()->execute('SELECT `id_block_cms` FROM `'._DB_PREFIX_.'cms_block` LIMIT 1'))
		return Db::getInstance()->execute('ALTER TABLE '._DB_PREFIX_.'cms_block` ADD `display_store` TINYINT NOT NULL DEFAULT \'1\'');
	return true;
}

