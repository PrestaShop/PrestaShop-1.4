SET NAMES 'utf8';

UPDATE `PREFIX_web_browser` SET name = 'Firefox' where name like 'Firefox 3.x';

UPDATE `PREFIX_web_browser` SET name = 'Safari iPad' where name like 'Firefox 2.x';

UPDATE `PREFIX_web_browser` SET name = 'IE 6' where name like 'IE 6.x';

UPDATE `PREFIX_web_browser` SET name = 'IE 7' where name like 'IE 7.x';

UPDATE `PREFIX_web_browser` SET name = 'IE 8' where name like 'IE 8.x';

UPDATE `PREFIX_web_browser` SET name = 'IE 9' where name like 'Google Chrome';

INSERT INTO `PREFIX_web_browser` (`id_web_browser` , `name`) VALUES (NULL , 'IE 10');

INSERT INTO `PREFIX_web_browser` (`id_web_browser` , `name`) VALUES (NULL , 'Chrome');

UPDATE `PREFIX_operating_system` SET name = 'Windows 7' where name like 'MacOsX';

UPDATE `PREFIX_operating_system` SET name = 'Windows 8' where name like 'Linux';

INSERT INTO `PREFIX_operating_system` (`id_operating_system` , `name`) VALUES (NULL , 'MacOsX');

INSERT INTO `PREFIX_operating_system` (`id_operating_system` , `name`) VALUES (NULL , 'Linux');

INSERT INTO `PREFIX_operating_system` (`id_operating_system` , `name`) VALUES (NULL , 'Android');

/* PHP:update_web_browser(); */;