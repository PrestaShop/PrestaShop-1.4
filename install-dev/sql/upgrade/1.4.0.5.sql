SET NAMES 'utf8';

@alias = (SELECT id_tab FROM PREFIX_tab WHERE class_name = "AdminAlias" LIMIT 1);
UPDATE PREFIX_tab SET id_parent = 8 WHERE id_tab = @alias LIMIT 1;
@stores = (SELECT id_tab FROM PREFIX_tab WHERE class_name = "AdminStores" LIMIT 1);
UPDATE PREFIX_tab SET id_parent = 9 WHERE id_tab = @stores LIMIT 1;
