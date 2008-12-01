<?php

function moduleReinstaller($moduleName, $force = false)
{
	$module = Module::getInstanceByName($moduleName);
	if (!Validate::isLoadedObject($module))
		die(Tools::displayError());
	if ($module->uninstall() OR $force)
		return $module->install();
	return false;
}

?>