<?php

if (!isset($errors))
	die;

function closest($input, $words)
{
	$shortest = -1;
	foreach ($words as $word)
	{
		$lev = levenshtein($input, $word);
		if ($lev == 0)
		{
			$closest = $word;
			$shortest = 0;
			break;
		}
		if ($lev <= $shortest || $shortest < 0)
		{
			$closest  = $word;
			$shortest = $lev;
		}
	}
	return $closest;
}

function constructSqlFilter($sqlId, $filterValue, $tableAlias = 'main.')
{
	$ret = '';
	preg_match('/^(.*)\[(.*)\](.*)$/', $filterValue, $matches);
	if (count($matches) > 1)
	{
		if ($matches[1] == '%' || $matches[3] == '%')
			$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` LIKE "'.$matches[1].pSQL($matches[2]).$matches[3]."\"\n";// AND field LIKE %value%
		elseif ($matches[1] == '' && $matches[3] == '')
		{
			preg_match('/^(\d+)(\|(\d+))+$/', $matches[2], $matches2);
			preg_match('/^(\d+)$/', $matches[2], $matches4);
			if (count($matches2) > 0 || count($matches4) > 1)
			{
				$values = explode('|', $matches[2]);
				$ret .= ' AND (';
				$temp = '';
				foreach ($values as $value)
					$temp .= $tableAlias.'`'.pSQL($sqlId).'` = "'.pSQL($value).'" OR ';// AND (field = value3 OR field = value7 OR field = value9)
				$ret .= rtrim($temp, 'OR ').')'."\n";
			}
			else
			{
				preg_match('/^(\d+),(\d+)$/', $matches[2], $matches3);
				if (count($matches3) > 0)
				{
					$values = explode(',', $matches[2]);
					$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` BETWEEN "'.$values[0].'" AND "'.$values[1]."\"\n";// AND field BETWEEN value3 AND value4
				}
			}
		}
		elseif ($matches[1] == '>')
			$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` > "'.pSQL($matches[2])."\"\n";// AND field > value3
		elseif ($matches[1] == '<')
			$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` > "'.pSQL($matches[2])."\"\n";// AND field < value3
		elseif ($matches[1] == '!')
			$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` != "'.pSQL($matches[2])."\"\n";// AND field IS NOT value3
	}
	else
		$ret .= ' AND '.$tableAlias.'`'.pSQL($sqlId).'` = "'.pSQL($filterValue)."\"\n";
	return $ret;
}

function saveObjectFromXmlInput($xmlString, $object, $successReturnCode, $resourceParameters, $errors)
{
  $xml = new SimpleXMLElement($xmlString);
	$attributes = $xml->children()->{$resourceParameters['objectNodeName']}->children();
	
	// attributes
	foreach ($resourceParameters['fields'] as $fieldName => $fieldProperties)
	{
		$sqlId = $fieldProperties['sqlId'];
		if (isset($attributes->$fieldName) && isset($fieldProperties['sqlId']) && !$fieldProperties['i18n'])
		{
			if (isset($fieldProperties['setter']))
			{
				// if we have to use a specific setter
				if (!$fieldProperties['setter'])
				{
					// if it's forbidden to set this field
					$errors[] = 'parameter "'.$fieldName.'" not writable. Please remove this attribute of this XML';
					$return_code = 'HTTP/1.1 400 Bad Request';
				}
				else
					$object->$fieldProperties['sqlId']((string)$attributes->$fieldName);
			}
			else
				$object->$sqlId = (string)$attributes->$fieldName;
		}
		elseif (isset($fieldProperties['required']) && $fieldProperties['required'] && !$fieldProperties['i18n'])
		{
			$errors[] = 'parameter "'.$fieldName.'" required';
			$return_code = 'HTTP/1.1 400 Bad Request';
		}
		elseif (!isset($fieldProperties['required']) || !$fieldProperties['required'])
			$object->$sqlId = null;
	}
	
	if (isset($attributes->associations))
	  foreach ($attributes->associations->children() as $association)
	  {
      // translated attributes
      if ($association->getName() == 'i18n')
      {
        $i18n = true;
        $fields = $association->children();
			  foreach ($fields as $field)
			  {
				  $fieldName = $field->getName();
				  $langs = array();
				  foreach ($field->children() as $translation)
			    {
				    $langs[(string)$translation['id']] = (string)$translation;
				  }
				  $object->$fieldName = $langs;
			  }
      }
	  }
	if (!$errors)
	{
		if ($i18n && ($retValidateFieldsLang = $object->validateFieldsLang(false, true)) !== true)
		{
			$errors[] = $display_errors ? 'Validation error: "'.$retValidateFieldsLang.'"' : 'Internal error';
			$return_code = 'HTTP/1.1 400 Bad Request';
		}
		elseif (($retValidateFields = $object->validateFields(false, true)) !== true)
		{
			$errors[] = $display_errors ? 'Validation error: "'.$retValidateFields.'"' : 'Internal error';
			$return_code = 'HTTP/1.1 400 Bad Request';
		}
		else
		{
			if($object->save())
			{
				if (isset($attributes->associations))
	        foreach ($attributes->associations->children() as $association)
	        {
            // associations
            if (isset($resourceParameters['associations'][$association->getName()]))
            {
              $assocItems = $association->children();
              foreach ($assocItems as $assocItem)
              {
                $fields = $assocItem->children();
                $values = array();
                foreach ($fields as $field)
                  $values[] = (string)$field;
                $setter = $resourceParameters['associations'][$association->getName()]['setter'];
                $object->$setter($values);
              }
            }
            elseif ($association->getName() != 'i18n')
            {
              $errors[] = $display_errors ? 'The association "'.$association->getName().'" does not exists' : 'Internal error';
				      $return_code = 'HTTP/1.1 400 Bad Request';
            }
	        }
				if (!$errors)
				  $return_code = $successReturnCode;
			}
			else
				$return_code = 'HTTP/1.1 500 Internal Server Error';
		}
	}
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
			case 'HEAD':
				if ($resourceParameters['objectsNodeName'] != 'resources')
				{
					//construct SQL filter
					$sql_filter = '';
					$sql_join = '';
					$schema = null;
					if ($url_params)
					{
						// if we have to display the schema
						if (array_key_exists('schema', $url_params))
						{
							if ($url_params['schema'] == 'blank')
							{
								$schema = 'blank';
							}
							elseif ($url_params['schema'] == 'synopsis')
							{
								$schema = 'synopsis';
							}
							else
							{
								$errors[] = 'Please select a schema of type \'synopsis\' to get the whole schema informations (which fields are required, which kind of content...) or \'blank\' to get an empty schema to fill before using POST request';
								$return_code = 'HTTP/1.1 400 Bad Request';
							}
						}
						else
						{
							// if there are filters
							if (isset($url_params['filter']))
								foreach ($url_params['filter'] as $field => $url_param)
								{
									$available_filters = array_keys($resourceParameters['fields']);
									if ($field != 'sort_list' && $field != 'limit_list')
										if (!in_array($field, $available_filters))
										{
											// if there are linked tables
											if (isset($resourceParameters['linked_tables']) && isset($resourceParameters['linked_tables'][$field]))
											{
												// contruct SQL join for linked tables
												$sql_join .= 'LEFT JOIN `'._DB_PREFIX_.pSQL($resourceParameters['linked_tables'][$field]['table']).'` '.pSQL($field).' ON (main.`'.pSQL($resourceParameters['fields']['id']['sqlId']).'` = '.pSQL($field).'.`'.pSQL($resourceParameters['fields']['id']['sqlId']).'`)'."\n";
										
												// construct SQL filter for linked tables
												foreach ($url_param as $field2 => $value)
												{
													if (isset($resourceParameters['linked_tables'][$field]['fields'][$field2]))
													{
														$linked_field = $resourceParameters['linked_tables'][$field]['fields'][$field2];
														$sql_filter .= constructSqlFilter($linked_field['sqlId'], $value, $field.'.');
													}
													else
													{
														$list = array_keys($resourceParameters['linked_tables'][$field]['fields']);
														$errors[] = 'This filter does not exist for this linked table. Did you mean: "'.closest($field2, $list).'"?'.(count($list) > 1 ? ' The full list is: "'.implode('", "', $list).'"' : '');
														$return_code = 'HTTP/1.1 400 Bad Request';
													}
												}
											}
											// if there are filters on linked tables but there are no linked table
											elseif (is_array($url_param))
											{
												$error_label = '';
												if (isset($resourceParameters['linked_tables']))
												{
													$list = array_keys($resourceParameters['linked_tables']);
													$error_label .= 'This linked table does not exist, did you mean: "'.closest($field, $list).'"?'.(count($list) > 1 ? ' The full list is: "'.implode('", "', $list).'"' : '');
												}
												else
													$error_label .=  'There is no existing linked table for this resource';
												$errors[] = $error_label;
												$return_code = 'HTTP/1.1 400 Bad Request';
											}
											else
											{
												$list = $available_filters;
												$errors[] = 'This filter does not exist. Did you mean: "'.closest($field, $list).'"?'.(count($list) > 1 ? ' The full list is: "'.implode('", "', $list).'"' : '');
												$return_code = 'HTTP/1.1 400 Bad Request';
											}
										}
										elseif ($url_param == '')
										{
											$errors[] = 'The filter "'.$field.'" is malformed.';
											$return_code = 'HTTP/1.1 400 Bad Request';
										}
										else
										{
											if (isset($resourceParameters['fields'][$field]['getter']))
											{
												$errors[] = 'The field "'.$field.'" is dynamic. It is not possible to filter GET query with this field.';
												$return_code = 'HTTP/1.1 400 Bad Request';
											}
											else
											{
												if (isset($resourceParameters['retrieveData']['tableAlias']))
													$sql_filter .= constructSqlFilter($resourceParameters['fields'][$field]['sqlId'], $url_param, $resourceParameters['retrieveData']['tableAlias'].'.');
												else
													$sql_filter .= constructSqlFilter($resourceParameters['fields'][$field]['sqlId'], $url_param);
											}
										}
								}
						}
					}
					
					// set the fields to display in the list : "full", "minimum", "field_1", "field_1,field_2,field_3" //TODO manage linked_tables too
					$fieldsToDisplay = 'minimum';
					if (isset($url_params['display']))
					{
						$fieldsToDisplay = $url_params['display'];
						if ($fieldsToDisplay != 'full')
						{
							preg_match('#^\[(.*)\]$#Ui', $fieldsToDisplay, $matches);
							if (count($matches))
							{
								$fieldsToTest = explode(',', $matches[1]);
								foreach ($fieldsToTest as $fieldToDisplay)
									if (!isset($resourceParameters['fields'][$fieldToDisplay]))
									{
										$errors[] = 'Unable to display this field. However, these are available: '.implode(', ', array_keys($resourceParameters['fields']));
										$return_code = 'HTTP/1.1 400 Bad Request';
									}
									$fieldsToDisplay = !$errors ? $fieldsToTest : 'minimal';
							}
							else
							{
								$errors[] = 'The \'display\' synthax is wrong. You can set \'full\' or \'[field_1,field_2,field_3,...]\'. These are available: '.implode(', ', array_keys($resourceParameters['fields']));
								$return_code = 'HTTP/1.1 400 Bad Request';
							}
						}
					}
					
					// construct SQL Sort
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
							$sql_limit .= ' LIMIT '.(int)($limitArgs[0]).(isset($limitArgs[1]) ? ', '.(int)($limitArgs[1]) : '')."\n";// LIMIT X|X, Y
						}
					}
			
			
					$objects = array();
					if (!isset($url[1]) || !strlen($url[1]))
					{
							$resourceParameters['retrieveData']['params'][] = $sql_join;
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
				$i18n = false;
				$xmlString = $_POST['xml'];
				saveObjectFromXmlInput($xmlString, $object, 'HTTP/1.1 201 Created', $resourceParameters, $errors);
			break;

			//get the matching resource(s)
			case 'DELETE':
				$object = new $resourceParameters['retrieveData']['className']($url[1]);
				if (!$object->id  || !$object->delete())
					$return_code = 'HTTP/1.1 204 No Content';
				$output = false;
			break;

			// modify a specified entry
			case 'PUT':
					$object = new $resourceParameters['retrieveData']['className']($url[1]);
					$i18n = false;
					
					if ($object->id)
					{
						$xmlString = '';
						$putresource = fopen("php://input", "r");
						while ($putData = fread($putresource, 1024))
							$xmlString .= $putData;
						fclose($putresource);
						saveObjectFromXmlInput($xmlString, $object, 'HTTP/1.1 200 OK', $resourceParameters, $errors);
					}
					else
					{
						$return_code = 'HTTP/1.1 404 Not Found';
						$output = false;
					}
			break;
		}
}

