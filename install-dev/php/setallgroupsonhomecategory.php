<?php

function setAllGroupsOnHomeCategory()
{
	$results = Group::getGroups(Configuration::get('PS_LANG_DEFAULT'));
	$groups = array();
	foreach ($results AS $result)
		$groups[] = $result['id_group'];
	if (is_array($groups) && sizeof($groups))
	{
		$category = new Category(1);
		$category->cleanGroups();
		$category->addGroups($groups);
	}
}
