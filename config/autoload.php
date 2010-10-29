<?php

function __autoload($className)
{
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