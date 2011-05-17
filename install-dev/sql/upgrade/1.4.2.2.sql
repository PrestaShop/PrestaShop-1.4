SET NAMES 'utf8';

UPDATE `PREFIX_country` SET `display_tax_label` = '1' WHERE `id_country` = 21;

/* PHP:check_webservice_account_table(); */;
/* PHP:add_module_to_hook(blockcms, leftColumn); */;
/* PHP:add_module_to_hook(blockcms, rightColumn); */;
/* PHP:add_module_to_hook(blockcms, footer); */;

UPDATE `PREFIX_cart` ca SET `secure_key` = (SELECT `secure_key` from `PREFIX_customer` `cu` WHERE `cu`.`id_customer` = `ca`.`id_customer`) WHERE `ca`.`secure_key` = -1;

