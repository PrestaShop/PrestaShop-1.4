<?php

if (!isset($errors))
	die;

function getXmlStringViewOfObject($resourceParameters, $object) {
	global $ws_url;
	$ret = '<p:'.$resourceParameters['objectNodeName'].'>'."\n";
	foreach ($resourceParameters['fields'] as $key => $field)
		if ($key != 'id')
		{
			if (is_array($object->$key))
			{
				$ret .= '<'.$field['sqlId'].'>'."\n";
				foreach ($object->$key as $idLang => $value)
					$ret .= '<language id="'.$idLang.'" xlink:href="'.$ws_url.'languages/'.$idLang.'"><![CDATA['.$value.']]></language>'."\n";
				$ret .= '</'.$field['sqlId'].'>'."\n";
			}
			else
			{
				$ret .= '<'.$field['sqlId'].(array_key_exists('xlink_resource', $field) ? ' xlink:href="'.$ws_url.$field['xlink_resource'].'/'.$object->$key.'"' : '').'><![CDATA['.$object->$key.']]></'.$field['sqlId'].'>'."\n";
			}
		}
		else
				$ret .= '<id><![CDATA['.$object->id.']]></id>'."\n";
	if (isset($resourceParameters['associations']))
	{
		$ret .= '<associations>'."\n";
		foreach ($resourceParameters['associations'] as $assocName => $association)
		{
			$ret .= '<'.$assocName.'>'."\n";
			$getter = $resourceParameters['associations'][$assocName]['getter'];
			if (method_exists($object, $getter))
			{
				$associationRessources = $object->$getter();
				if (is_array($associationRessources))
					foreach ($associationRessources as $associationRessource)
					{
						$ret .= '<'.$resourceParameters['associations'][$assocName]['resource'].' xlink:href="'.$ws_url.$assocName.'/'.$associationRessource['id'].'">'."\n";
						foreach ($associationRessource as $fieldName => $fieldValue)
						{
							if ($fieldName == 'id')
								$ret .= '<'.$fieldName.'><![CDATA['.$fieldValue.']]></'.$fieldName.'>'."\n";
						}
						$ret .= '</'.$resourceParameters['associations'][$assocName]['resource'].'>'."\n";
					}
			}
			$ret .= '</'.$assocName.'>'."\n";
		}
		$ret .= '</associations>'."\n";
	}
	$ret .= '</p:'.$resourceParameters['objectNodeName'].'>'."\n";
	return $ret;
}


header($return_code);
restore_error_handler();
if ($output)
{
	header('Content-Type: text/xml');
	echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	echo '<!DOCTYPE prestashop PUBLIC "-//PRESTASHOP//DTD REST_WEBSERVICE '._PS_VERSION_.'//EN"'."\n".'"'.$dtd.'">'."\n";
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
						{
							if ($fieldsToDisplay == 'minimum')
								foreach ($objects as $object)
									echo '<p:'.$resourceParameters['objectNodeName'].(array_key_exists('id', $resourceParameters['fields']) ? ' id="'.$object->id.'" xlink:href="'.$ws_url.$resourceParameters['objectsNodeName'].'/'.$object->id.'"' : '').' />'."\n";
							elseif ($fieldsToDisplay == 'full')
								foreach ($objects as $object)
									echo getXmlStringViewOfObject($resourceParameters, $object);
							/*else
							{
								die('todo : display specific fields');//TODO[id,lastname]
							}*/
						}
						else
						{
							foreach ($resources as $resourceName => $resource)
								if (in_array($resourceName, array_keys($permissions)))
									echo '<p:'.$resourceName.' xlink:href="'.$ws_url.$resourceName.'"
										get="'.(in_array('GET', $permissions[$resourceName]) ? 'true' : 'false').'"
										put="'.(in_array('PUT', $permissions[$resourceName]) ? 'true' : 'false').'"
										post="'.(in_array('POST', $permissions[$resourceName]) ? 'true' : 'false').'"
										delete="'.(in_array('DELETE', $permissions[$resourceName]) ? 'true' : 'false').'"
									>'.$resource['description'].'</p:'.$resourceName.'>'."\n";
						}
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
