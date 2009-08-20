<?php

include(dirname(__FILE__).'/config/config.inc.php');

$css_files = array();
$js_files = array(_THEME_JS_DIR_.'tools/treeManagement.js');

include(dirname(__FILE__).'/header.php');

/* Depth choice (number of levels displayed)  */
$depth = 0;

/* Construct categories tree */
$categTree = Category::getRootCategory()->recurseLiteCategTree($depth);

/*  ONLY FOR THEME OLDER THAN v1.0 */
function constructTreeNode($node){
	$ret = '<li>'."\n";
	$ret .= '<a href="'.$node['link'].'" title="'.strip_tags($node['desc']).'">'.$node['name'].'</a>'."\n";
	if(!empty($node['children']))
	{
		$ret .= '<ul>'."\n";
		foreach ($node['children'] AS $child)
			$ret .= constructTreeNode($child);
		$ret .= '</ul>'."\n";
	}
	$ret .= '</li>'."\n";
	return $ret;
}

$ulTree = '<div class="tree-top">' . $categTree['name'] . '</div>'."\n";
$ulTree .=  '<ul class="tree">'."\n";
foreach ($categTree['children'] AS $child)
	$ulTree .= constructTreeNode($child);
$ulTree .=  '</ul>'."\n";
$smarty->assign('categoryTree', $ulTree);
/* ELSE */
$smarty->assign('categoriesTree', $categTree);
/* /ONLY FOR THEME OLDER THAN v1.0 */

$cms = CMS::listCms(intval($cookie->id_lang));
$id_cms = array();
foreach($cms AS $row)
	$id_cms[] = intval($row['id_cms']);
$smarty->assign('cmslinks', CMS::getLinks(intval($cookie->id_lang), $id_cms ? $id_cms : NULL));	

$smarty->assign('voucherAllowed', intval(Configuration::get('PS_VOUCHERS')));
$smarty->display(_PS_THEME_DIR_.'sitemap.tpl');
include(dirname(__FILE__).'/footer.php');

?>