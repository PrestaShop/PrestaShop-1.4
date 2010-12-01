<?php
  
class ControllerFactoryCore
{
	public static function getController($className, $auth = false, $ssl = false)
	{
		if (!class_exists($className, false))
		{
			require_once(dirname(__FILE__).'/../controllers/'.$className.'.php');
			if (file_exists(dirname(__FILE__).'/../override/controllers/'.$className.'.php'))
				require_once(dirname(__FILE__).'/../override/controllers/'.$className.'.php');
			else
			{
				$coreClass = new ReflectionClass($className.'Core');
				if ($coreClass->isAbstract())
					eval('abstract class '.$className.' extends '.$className.'Core {}');
				else
					eval('class '.$className.' extends '.$className.'Core {}');
			}
		}
		return new $className($auth, $ssl);
	}
}

