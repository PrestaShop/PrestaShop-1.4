<?php

function moduleReinstaller($moduleName, $force = false)
{
	$module = Module::getInstanceByName($moduleName);
	if (!is_object($module))
		die(Tools::displayError());
	if ($module->uninstall() OR $force)
		return $module->install();
	return false;
}

?>