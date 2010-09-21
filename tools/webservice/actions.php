<?php

function getXmlStringViewOfObject($resourceParameters, $object) {
	global $ws_url;
	$ret = '';
	$ret .= '<p:'.$resourceParameters['objectNodeName'].'>'."\n";
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


if (!$errors)
{
	// check this method is allowed for this auth key
	if ($url[0] && !in_array($method, $permissions[$url[0]]))
	{
		$errors[] = 'Method '.$method.' is not allowed for the ressource '.$url[0].' with this authentication key';
		$return_code = 'HTTP/1.1 405 Method Not Allowed';
	}
	else
		switch ($method)
		{
			//get the matching resource(s)
			case 'GET':
				if ($resourceParameters['objectsNodeName'] != 'resources')
				{
					//construct SQL filter
					$sql_filter = '';
					if ($url_params)
						foreach ($url_params as $field => $url_param)
						{
							$available_filters = array_keys($resourceParameters['fields']);
							if ($field != 'sort_list' && $field != 'limit_list')
								if (!in_array($field, $available_filters))
								{
									$errors[] = 'This filter does not exist. However, these are available: '.implode(', ', $available_filters);
									$return_code = 'HTTP/1.1 400 Bad Request';
								}
								elseif ($url_param == '')
								{
									$errors[] = 'The filter "'.$field.'" is malformed.';
									$return_code = 'HTTP/1.1 400 Bad Request';
								}
								else
								{
									$field = $resourceParameters['fields'][$field]['sqlId'];
									preg_match('/^(.*)\[(.*)\](.*)$/', $url_param, $matches);
									if (count($matches) > 1)
									{
										if ($matches[1] == '%' || $matches[3] == '%')
											$sql_filter .= ' AND `'.(isset($resourceParameters['retrieveData']['tableAlias']) ? $resourceParameters['retrieveData']['tableAlias'].'.' : '').''.pSQL($field).'` LIKE "'.$matches[1].pSQL($matches[2]).$matches[3]."\"\n";// AND field LIKE %value%
										elseif ($matches[1] == '' && $matches[3] == '')
										{
											preg_match('/^(\d+)(\|(\d+))+$/', $matches[2], $matches2);
											if (count($matches2) > 0)
											{
												$values = explode('|', $matches[2]);
												$sql_filter .= ' AND (';
												$temp = '';
												foreach ($values as $value)
													$temp .= ''.(isset($resourceParameters['retrieveData']['tableAlias']) ? $resourceParameters['retrieveData']['tableAlias'].'.' : '').'`'.pSQL($field).'` = "'.pSQL($value).'" OR ';// AND (field = value3 OR field = value7 OR field = value9)
												$sql_filter .= rtrim($temp, 'OR ').')'."\n";
											}
											else
											{
												preg_match('/^(\d+),(\d+)$/', $matches[2], $matches3);
												if (count($matches3) > 0)
												{
													$values = explode(',', $matches[2]);
													$sql_filter .= ' AND '.(isset($resourceParameters['retrieveData']['tableAlias']) ? $resourceParameters['retrieveData']['tableAlias'].'.' : '').'`'.pSQL($field).'` BETWEEN "'.$values[0].'" AND "'.$values[1]."\"\n";// AND field BETWEEN value3 AND value4
												}
											}
										}
										elseif ($matches[1] == '>')
											$sql_filter .= ' AND '.(isset($resourceParameters['retrieveData']['tableAlias']) ? $resourceParameters['retrieveData']['tableAlias'].'.' : '').'`'.pSQL($field).'` > "'.pSQL($matches[2])."\"\n";// AND field > value3
										elseif ($matches[1] == '<')
											$sql_filter .= ' AND '.(isset($resourceParameters['retrieveData']['tableAlias']) ? $resourceParameters['retrieveData']['tableAlias'].'.' : '').'`'.pSQL($field).'` > "'.pSQL($matches[2])."\"\n";// AND field < value3
										elseif ($matches[1] == '!')
											$sql_filter .= ' AND '.(isset($resourceParameters['retrieveData']['tableAlias']) ? $resourceParameters['retrieveData']['tableAlias'].'.' : '').'`'.pSQL($field).'` != "'.pSQL($matches[2])."\"\n";// AND field IS NOT value3
									}
									else
										$sql_filter .= ' AND '.(isset($resourceParameters['retrieveData']['tableAlias']) ? $resourceParameters['retrieveData']['tableAlias'].'.' : '').'`'.pSQL($field).'` = "'.pSQL($url_param)."\"\n";
								}
						}
			
					//construct SQL Sort
					$sql_sort = '';
					$available_filters = array_keys($resourceParameters['fields']);
					if (isset($url_params['sort_list']))
					{
						$sortArgs = explode('_', $url_params['sort_list']);
						if (count($sortArgs) != 2 || (strtoupper($sortArgs[1]) != 'ASC' && strtoupper($sortArgs[1]) != 'DESC'))
						{
							$errors[] = 'The "sort_list" value has to be formed as this example: "field_ASC" ("field" has to be an available field)';
							$return_code = 'HTTP/1.1 400 Bad Request';
						}
						elseif (!in_array($sortArgs[0], $available_filters))
						{
							$errors[] = 'Unable to filter by this field. However, these are available: '.implode(', ', $available_filters);
							$return_code = 'HTTP/1.1 400 Bad Request';
						}
						else
						{
							$sql_sort .= ' ORDER BY '.(isset($resourceParameters['retrieveData']['tableAlias']) ? $resourceParameters['retrieveData']['tableAlias'].'.' : '').'`'.pSQL($resourceParameters['fields'][$sortArgs[0]]['sqlId']).'` '.strtoupper($sortArgs[1])."\n";// ORDER BY `field` ASC|DESC
						}
					}
			
					//construct SQL Limit
					$sql_limit = '';
					if (isset($url_params['limit_list']))
					{
						$limitArgs = explode(',', $url_params['limit_list']);
						if (count($limitArgs) > 2)
						{
							$errors[] = 'The "limit_list" value has to be formed as this example: "5,25" or "10"';
							$return_code = 'HTTP/1.1 400 Bad Request';
						}
						else
						{
							$sql_limit .= ' LIMIT '.intval($limitArgs[0]).(isset($limitArgs[1]) ? ', '.intval($limitArgs[1]) : '')."\n";// LIMIT X|X, Y
						}
					}
			
			
					$objects = array();
					if (!isset($url[1]) || !strlen($url[1]))
					{
							$resourceParameters['retrieveData']['params'][] = $sql_filter;
							$resourceParameters['retrieveData']['params'][] = $sql_sort;
							$resourceParameters['retrieveData']['params'][] = $sql_limit;
						//list entities
						$tmp = new $resourceParameters['retrieveData']['className']();
						$sqlObjects = call_user_func_array(array($tmp, $resourceParameters['retrieveData']['retrieveMethod']), $resourceParameters['retrieveData']['params']);
						if ($sqlObjects)
							foreach ($sqlObjects as $sqlObject)
							{
								$objects[] = new $resourceParameters['retrieveData']['className']($sqlObject[$resourceParameters['fields']['id']['sqlId']]);
							}
					}
					else
					{
						//get entity details
						$object = new $resourceParameters['retrieveData']['className']($url[1]);
						if ($object->id)
							$objects[] = $object;
						else
						{
							$return_code = 'HTTP/1.1 404 Not Found';
							$output = false;
						}
					}

				}
			break;

			//add a new entry
			case 'POST':
				$object = new $resourceParameters['retrieveData']['className']();
				//attributes
				foreach ($resourceParameters['fields'] as $fieldName => $fieldProperties)
				{
					$sqlId = $fieldProperties['sqlId'];
					if (isset($_POST['attributes'][$fieldName]) && isset($fieldProperties['sqlId']))
						$object->$sqlId = $_POST['attributes'][$fieldName];
					elseif (isset($fieldProperties['required']) && $fieldProperties['required'] && ( $fieldName == 'id' ? !isset($object->id) : !isset($object->$sqlId) ))
					{
						$errors[] = 'parameter "'.$fieldName.'" required';
						$return_code = 'HTTP/1.1 400 Bad Request';
					}
				}
				if ($errors)
					$return_code = 'HTTP/1.1 400 Bad Request';
				elseif (!$object->save())
					$return_code = 'HTTP/1.1 400 Bad Request';
				
				//associations
				if (isset($_POST['associations']) && is_array($_POST['associations']))
					foreach ($_POST['associations'] as $assocName => $values)
					{
						if (!$errors && in_array($assocName, array_keys($resourceParameters['associations'])))
						{
							$setter = $resourceParameters['associations'][$assocName]['setter'];
							if (!method_exists($object, $setter))
							{
								$errors[] = 'No association implemented for the resources of type "'.$assocName.'"';
								$return_code = 'HTTP/1.1 400 Bad Request';
							}
							
							elseif (!$object->$setter($values))
							{
								$errors[] = 'error occured for association "'.$assocName.'"';
								$return_code = 'HTTP/1.1 400 Bad Request';
							}
						}
					}
				
				if ($errors)
					$return_code = 'HTTP/1.1 400 Bad Request';
				elseif (!$object->save())
					$return_code = 'HTTP/1.1 400 Bad Request';
				else
					$return_code = 'HTTP/1.1 201 Created';
			break;

			//get the matching resource(s)
			case 'DELETE':
				$object = new $resourceParameters['retrieveData']['className']($url[1]);
				if (!$object->id  || !$object->delete())
					$return_code = 'HTTP/1.1 204 No Content';
				$output = false;
			break;

			//add a new entry
			case 'PUT':
					$object = new $resourceParameters['retrieveData']['className']($url[1]);
			
					if ($object->id)
					{
						$xmlstring = '';
						$putresource = fopen("php://input", "r");
						while ($putData = fread($putresource, 1024))
							$xmlstring .= $putData;
						fclose($putresource);
						$xml = new SimpleXMLElement($xmlstring);
						$attributes = $xml->attributes;
						foreach ($resourceParameters['fields'] as $fieldName => $fieldProperties)
						{
							$sqlId = $fieldProperties['sqlId'];
							if (isset($attributes->$fieldName) && isset($fieldProperties['sqlId']))
							{
								if (isset($attributes->$fieldName->language))
								{
									$langs = array();
									foreach ($attributes->$fieldName->language as $language)
									{
										if (isset($language['id']))
											$langs[(string)$language['id']] = (string)$language[0];
									}
									$object->$sqlId = $langs;
								}
								else
									$object->$sqlId = (string)$attributes->$fieldName;
							}
							elseif (isset($fieldProperties['required']) && $fieldProperties['required'])
							{
								$errors[] = 'parameter "'.$fieldName.'" required';
								$return_code = 'HTTP/1.1 400 Bad Request';
							}
							elseif (!isset($fieldProperties['required']) || !$fieldProperties['required'])
								$object->$sqlId = null;
						}
						if (!$errors)
						{
							$object->save();
							$return_code = 'HTTP/1.1 200 OK';
						}
						else
							$return_code = 'HTTP/1.1 400 Bad Request';
					}
					else
					{
						$return_code = 'HTTP/1.1 404 Not Found';
						$output = false;
					}
			break;
		}
}

