<?php

	define('PS_ADMIN_DIR', getcwd());
	include_once('tabs/AdminCatalog.php');
	include_once('tabs/AdminProducts.php');
	include_once('../config/config.inc.php');
	include_once('init.php');
	
	$catalog = new AdminCatalog();
	$adminProducts = new AdminProducts();

	global $cookie;
	
	/*TEST*/
/*	echo '<table><tr>';*/
	/*!TEST*/
	echo '			<tr>
						<td class="col-left"><label for="id_category_default" class="t">'.$adminProducts->getL('Default category:').'</label></td>
						<td>
							<select id="id_category_default" name="id_category_default" onchange="checkDefaultCategory(this.value);">';
		$categories = Category::getCategories(intval($cookie->id_lang), false);
		Category::recurseCategory($categories, $categories[0][1], 1, intval(Tools::getValue('id_category_default')));
		echo '			</select>
						</td>
					</tr>
					<tr>
						<td class="col-left">'.$adminProducts->getL('Catalog:').'</td>
						<td>
							<div style="overflow: auto; min-height: 300px; padding-top: 0.6em;" id="categoryList">
								<script type="text/javascript">
								$(document).ready(function() {
									$(\'div#categoryList input.categoryBox\').click(function (){
										if ($(this).is(\':not(:checked)\') && $(\'div#categoryList input.id_category_default\').val() == $(this).val())
											alert(\''.utf8_encode(html_entity_decode($adminProducts->getL('Consider changing the default category.'))).'\');
									});
								});
								</script>
								<table cellspacing="0" cellpadding="0" class="table">
									<tr>
										<th><input type="checkbox" name="checkme" class="noborder" onclick="checkDelBoxes(this.form, \'categoryBox[]\', this.checked)" /></th>
										<th>'.$adminProducts->getL('ID').'</th>
										<th style="width: 600px">'.$adminProducts->getL('Name').'</th>
									</tr>';
			$done = array();
			$index = array();
			if (Tools::isSubmit('categoryBox'))
				foreach (Tools::getValue('categoryBox') AS $k => $row)
					$index[] = $row;
			elseif (intval(Tools::getValue('id_product')))
				foreach (Product::getIndexedCategories(intval(Tools::getValue('id_product'))) AS $k => $row)
					$index[] = $row['id_category'];
			$adminProducts->recurseCategoryForInclude(intval(Tools::getValue('id_product')), $index, $categories, $categories[0][1], 1, intval(Tools::getValue('id_category_default')));
			echo '				</table>
								<p style="padding:0px; margin:0px 0px 10px 0px;">'.$adminProducts->getL('Mark all checkbox(es) of categories in which product is to appear').'<sup> *</sup></p>
							</div>
					</tr>';
	/*TEST*/
/*	echo '</tr></table>';*/
	/*!TEST*/
?>
