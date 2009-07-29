<?php

function update_modules_sql()
{
	Configuration::loadConfiguration();
	$blocklink = Module::getInstanceByName('blocklink');
	if ($blocklink->id)
		Db::getInstance()->Execute('ALTER IGNORE TABLE `'._DB_PREFIX_.'blocklink_lang` ADD PRIMARY KEY (`id_link`, `id_lang`);');
	$productComments = Module::getInstanceByName('productcomments');
	if ($productComments->id)
	{
		Db::getInstance()->Execute('ALTER IGNORE TABLE `'._DB_PREFIX_.'product_comment_grade` ADD PRIMARY KEY (`id_product_comment`, `id_product_comment_criterion`);');
		Db::getInstance()->Execute('ALTER IGNORE TABLE `'._DB_PREFIX_.'product_comment_criterion` DROP PRIMARY KEY, ADD PRIMARY KEY (`id_product_comment_criterion`, `id_lang`);');
		Db::getInstance()->Execute('ALTER IGNORE TABLE `'._DB_PREFIX_.'product_comment_criterion_product` ADD PRIMARY KEY(`id_product`, `id_product_comment_criterion`);');
	}
}
