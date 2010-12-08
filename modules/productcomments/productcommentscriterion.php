<?php
/*
* Copyright (C) 2007-2010 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author Prestashop SA <contact@prestashop.com>
*  @copyright  Copyright (c) 2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/../../classes/Validate.php');
require_once(dirname(__FILE__).'/../../classes/Db.php');
require_once(dirname(__FILE__).'/../../classes/Tools.php');
require_once(dirname(__FILE__).'/ProductCommentCriterion.php');

if (empty($_GET['id_lang']) === false &&
	isset($_GET['id_product']) === true)
{
	$criterions = ProductCommentCriterion::get($_GET['id_lang']);
	if ((int)($_GET['id_product']))
		$selects = ProductCommentCriterion::getByProduct($_GET['id_product'], $_GET['id_lang']);
	echo '<select name="id_product_comment_criterion[]" id="id_product_comment_criterion" multiple="true" style="height:100px;width:360px;">';
	foreach ($criterions as $criterion)
	{
		echo '<option value="'.(int)($criterion['id_product_comment_criterion']).'"';
		if (isset($selects) === true && sizeof($selects))
		{
			foreach ($selects as $select)
				if ($select['id_product_comment_criterion'] == $criterion['id_product_comment_criterion'])
					echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($criterion['name'], ENT_COMPAT, 'UTF-8').'</option>';
	}
	echo '</select>';
}