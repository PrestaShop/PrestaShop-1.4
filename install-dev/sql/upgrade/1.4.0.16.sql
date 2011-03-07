INSERT INTO `PREFIX_hook` (`id_hook`, `name`, `title`, `description`, `position`)
VALUES (NULL, 'afterCreateHtaccess', 'After htaccess creation', 'After htaccess creation', 0);

INSERT INTO `PREFIX_hook_module` (`id_module`, `id_hook`, `position`) VALUES
(
SELECT IFNULL(
(SELECT `id_module` FROM `PREFIX_module` WHERE `name` = 'blockcategories'), 0)),
(SELECT `id_hook` FROM `PREFIX_hook` WHERE `name` = 'afterCreateHtaccess')
, 0
)


/* PHP:add_module_to_hook(blockcategories, afterCreateHtaccess); */;

