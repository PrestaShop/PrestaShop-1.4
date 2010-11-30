<?php

if (!isset($errors))
	die;

function getXmlStringViewOfObject($resourceParameters, $object = null, $schema = null) {
	
	global $ws_url;
	
	// two modes are available : 'schema', or 'display entity'
	
	//p($resourceParameters);
	
	$ret = '<'.$resourceParameters['objectNodeName'].'>'."\n";
	
	// display fields
	foreach ($resourceParameters['fields'] as $key => $field)
		if ($key != 'id')//TODO remove this condition
		{
			// get the field value with a specific getter
			if (isset($field['getter']) && $schema != 'blank')
				$object->$key = $object->$field['getter']();
			
			// display i18n fields
			if (isset($field['i18n']) && $field['i18n'])
			{
				$ret .= '<'.$field['sqlId'];
				if ($schema == 'synopsis')
				{
					if (array_key_exists('required', $field) && $field['required'])
						$ret .= ' required="true"';
					if (array_key_exists('maxSize', $field) && $field['maxSize'])
						$ret .= ' maxSize="'.$field['maxSize'].'"';
					if (array_key_exists('validateMethod', $field) && $field['validateMethod'])
						$ret .= ' format="'.implode(' ', $field['validateMethod']).'"';
				}
				$ret .= ">\n";
				if (!is_null($schema))
					$ret .= '<language id="" format="isUnsignedId"></language>'."\n";
				else
					foreach ($object->$key as $idLang => $value)
						$ret .= '<language id="'.$idLang.'" xlink:href="'.$ws_url.'languages/'.$idLang.'"><![CDATA['.$value.']]></language>'."\n";
				$ret .= '</'.$field['sqlId'].'>'."\n";
			}
			else
			{
				// display not i18n field value
				$ret .= '<'.$field['sqlId'];
				if (array_key_exists('xlink_resource', $field) && $schema != 'blank')
					$ret .= ' xlink:href="'.$ws_url.$field['xlink_resource'].'/'.($schema != 'synopsis' ? $object->$key : '').'"';
				if (isset($field['getter']) && $schema != 'blank')
					$ret .= ' not_filterable="true"';
				if ($schema == 'synopsis')
				{
					if (array_key_exists('required', $field) && $field['required'])
						$ret .= ' required="true"';
					if (array_key_exists('maxSize', $field) && $field['maxSize'])
						$ret .= ' maxSize="'.$field['maxSize'].'"';
					if (array_key_exists('validateMethod', $field) && $field['validateMethod'])
						$ret .= ' format="'.implode(' ', $field['validateMethod']).'"';
				}
				$ret .= '>';
				if (is_null($schema))
					$ret .= '<![CDATA['.$object->$key.']]>';
				$ret .= '</'.$field['sqlId'].'>'."\n";
				
				
				/*
				
				if ($schema == 'synopsis')
					$ret .= ' i18n="true" required="'.($field['required'] ? 'true' : 'false').'"';
				*/
			}
		}
		else
				// display id
				if (is_null($schema))
				$ret .= '<id><![CDATA['.$object->id.']]></id>'."\n";
	
	// display associations
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
	$ret .= '</'.$resourceParameters['objectNodeName'].'>'."\n";
	return $ret;
}


header($return_code);
restore_error_handler();


if ($output)
{
	$output_string = '';
	header('Content-Type: text/xml');
	$output_string .= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	/*$output_string .= '<?xml-stylesheet type="text/css" href="api.css"?>'."\n";*/
	//$output_string .= '<!DOCTYPE prestashop PUBLIC "-//PRESTASHOP//DTD REST_WEBSERVICE '._PS_VERSION_.'//EN"'."\n".'"'.$dtd.'">'."\n";
	$output_string .= '<prestashop xmlns="'.$doc_url.'" xmlns:xlink="http://www.w3.org/1999/xlink">'."\n";
	if ($errors)
	{
		$output_string .= '<errors>'."\n";
		foreach ($errors as $error)
			$output_string .= '<error><![CDATA['.$error.']]></error>'."\n";
		$output_string .= '</errors>'."\n";
	}
	else
	{
		switch ($method)
		{
			case 'GET':
				// list entities
				if (!isset($url[1]) || !strlen($url[1]))
				{
					if (($resourceParameters['objectsNodeName'] != 'resources' && count($objects) || $resourceParameters['objectsNodeName'] == 'resources') && count($resources))
					{
						if ($resourceParameters['objectsNodeName'] != 'resources')
						{
							if (!is_null($schema))
							{
								// display ready to use schema
								if ($schema == 'blank')
								{
									$output_string .= getXmlStringViewOfObject($resourceParameters, null, $schema);
									
								}
								// display ready to use schema
								else
								{
									$output_string .= getXmlStringViewOfObject($resourceParameters, null, $schema);
								}
							}
							// display specific resources list
							else
							{
								$output_string .= '<'.$resourceParameters['objectsNodeName'].'>'."\n";
								if ($fieldsToDisplay == 'minimum')
									foreach ($objects as $object)
										$output_string .= '<'.$resourceParameters['objectNodeName'].(array_key_exists('id', $resourceParameters['fields']) ? ' id="'.$object->id.'" xlink:href="'.$ws_url.$resourceParameters['objectsNodeName'].'/'.$object->id.'"' : '').' />'."\n";
								elseif ($fieldsToDisplay == 'full')
									foreach ($objects as $object)
										$output_string .= getXmlStringViewOfObject($resourceParameters, $object);
								$output_string .= '</'.$resourceParameters['objectsNodeName'].'>'."\n";
								/*else
								{
									die('todo : display specific fields');//TODO[id,lastname]
								}*/
							}
						}
						// display all ressources list
						else
						{
							$output_string .= '<resources shopName="'.Configuration::get('PS_SHOP_NAME').'">'."\n";
							foreach ($resources as $resourceName => $resource)
								if (in_array($resourceName, array_keys($permissions)))
									$output_string .= '<'.$resourceName.' xlink:href="'.$ws_url.$resourceName.'"
										get="'.(in_array('GET', $permissions[$resourceName]) ? 'true' : 'false').'"
										put="'.(in_array('PUT', $permissions[$resourceName]) ? 'true' : 'false').'"
										post="'.(in_array('POST', $permissions[$resourceName]) ? 'true' : 'false').'"
										delete="'.(in_array('DELETE', $permissions[$resourceName]) ? 'true' : 'false').'"
									>
									<description>'.$resource['description'].'</description>
									<schema type="blank" xlink:href="'.$ws_url.$resourceName.'?schema=blank" />
									<schema type="synopsis" xlink:href="'.$ws_url.$resourceName.'?schema=synopsis" />
									</'.$resourceName.'>'."\n";
							$output_string .= '</resources>'."\n";
						}
						
					}
					else
						$output_string .= '<'.$resourceParameters['objectsNodeName'].' />'."\n";
				}
				//display one resource
				else
					$output_string .= getXmlStringViewOfObject($resourceParameters, $objects[0]);
			break;
		
			//add a new entry / modify existing entry
			case 'POST':
			case 'PUT':
				$output_string .= getXmlStringViewOfObject($resourceParameters, $object);
			break;
		}
	}
	$output_string .= '</prestashop>'."\n";
	
	header('Content-Sha1: '.sha1($output_string));
	
	echo $output_string;
}
