<?php

function __autoload($className)
{
	if (stristr($className, 'smarty'))
		return;
	if (!class_exists($className, false))
	{
		require_once(dirname(__FILE__).'/../classes/'.$className.'.php');
		if (file_exists(dirname(__FILE__).'/../override/classes/'.$className.'.php'))
			require_once(dirname(__FILE__).'/../override/classes/'.$className.'.php');
		else
		{
			$coreClass = new ReflectionClass($className.'Core');
			if ($coreClass->isAbstract())
				eval('abstract class '.$className.' extends '.$className.'Core {}');
			else
				eval('class '.$className.' extends '.$className.'Core {}');
		}
	}
}

/* Use Smarty 3 API calls */
if (!_PS_FORCE_SMARTY_2_) /* PHP version > 5.1.2 */
{
	spl_autoload_register('__autoload');
	define('SMARTY_SPL_AUTOLOAD', 0);
}

?>