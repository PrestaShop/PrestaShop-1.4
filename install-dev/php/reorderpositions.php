<?php

function reorderpositions()
{
	$cat = Category::getCategories(1, false, false);
	
	foreach($cat as $i => $categ)
	{
		Product::cleanPositions(intval($categ['id_category']));
	}
}
?>