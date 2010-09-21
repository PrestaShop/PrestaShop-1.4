<?php

header($return_code);
restore_error_handler();
if ($output)
{
	header('Content-Type: text/xml');
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	echo '<!DOCTYPE prestashop PUBLIC "-//PRESTASHOP//DTD REST_WEBSERVICE '._PS_VERSION_.'//EN" "'.$dtd.'">'."\n";
	echo '<p:prestashop xmlns:p="'.$doc_url.'" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n";
	if ($errors)
	{
		echo '<p:errors>'."\n";
		foreach ($errors as $error)
			echo '<p:error><![CDATA['.$error.']]></p:error>'."\n";
		echo '</p:errors>'."\n";
	}
	else
	{
		switch ($method)
		{
			case 'GET':
				if (!isset($url[1]) || !strlen($url[1])) // list entities
				{
					if (($resourceParameters['objectsNodeName'] != 'resources' && count($objects) || $resourceParameters['objectsNodeName'] == 'resources') && count($resources))
					{
						echo '<p:'.$resourceParameters['objectsNodeName'].'>'."\n";
						if ($resourceParameters['objectsNodeName'] != 'resources')
							foreach ($objects as $object)
								echo '<p:'.$resourceParameters['objectNodeName'].(array_key_exists('id', $resourceParameters['fields']) ? ' id="'.$object->id.'" xlink:href="'.$ws_url.$resourceParameters['objectsNodeName'].'/'.$object->id.'"' : '').' />'."\n";
						else
							foreach ($resources as $resourceName => $resource)
								if (in_array($resourceName, array_keys($permissions)))
									echo '<p:'.$resourceName.' xlink:href="'.$ws_url.$resourceName.'"
										get="'.(in_array('GET', $permissions[$resourceName]) ? 'true' : 'false').'"
										put="'.(in_array('PUT', $permissions[$resourceName]) ? 'true' : 'false').'"
										post="'.(in_array('POST', $permissions[$resourceName]) ? 'true' : 'false').'"
										delete="'.(in_array('DELETE', $permissions[$resourceName]) ? 'true' : 'false').'"
									>'.$resource['description'].'</p:'.$resourceName.'>'."\n";
						echo '</p:'.$resourceParameters['objectsNodeName'].'>'."\n";
					}
					else
						echo '<p:'.$resourceParameters['objectsNodeName'].' />'."\n";
				}
				else //display entity détails
					echo getXmlStringViewOfObject($resourceParameters, $objects[0]);
			break;
		
			//add a new entry / modify existing entry
			case 'POST':
			case 'PUT':
				echo getXmlStringViewOfObject($resourceParameters, $object);
			break;
		}
	}
	echo '</p:prestashop>'."\n";
}
