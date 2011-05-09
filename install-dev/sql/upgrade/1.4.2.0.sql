SET NAMES 'utf8';

ALTER TABLE `PREFIX_tab_lang` MODIFY `id_lang` int(10) unsigned NOT NULL AFTER `id_tab`;
ALTER TABLE `PREFIX_carrier` ADD `is_free` tinyint(1) unsigned NOT NULL DEFAULT '0' AFTER `is_module`;



/* PHP:alter_blocklink(); */;
/* PHP:update_module_loyalty(); */;
/* PHP:remove_module_from_hook(blockcategories, afterCreateHtaccess); */;

