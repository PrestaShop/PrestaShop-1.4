/*
* 2007-2010 PrestaShop 
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
*  @copyright  2007-2010 Prestashop SA : 6 rue lacepede, 75005 PARIS
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

function getCommentForm()
{
	if (document.forms)
		return (document.forms['comment_form']);
	else
		return (document.comment_form);
}

function acceptComment(id)
{
	var form = getCommentForm();
	if (id)
		form.elements['id_product_comment'].value = id;
	form.elements['action'].value = 'accept';
	form.submit();
}


function deleteComment(id)
{
	var form = getCommentForm();
	if (id)
		form.elements['id_product_comment'].value = id;
	form.elements['action'].value = 'delete';
	form.submit();
}

function getCriterionForm()
{
	if (document.forms)
		return (document.forms['criterion_form']);
	else
		return (document.criterion_form);
}

function editCriterion(id)
{
	var form = getCriterionForm();
	form.elements['id_product_comment_criterion'].value = id;
	form.elements['criterion_name'].value = document.getElementById('criterion_name_' + id).value;
	form.elements['criterion_action'].value = 'edit';
	form.submit();
}

function deleteCriterion(id)
{
	var form = getCriterionForm();
	form.elements['id_product_comment_criterion'].value = id;
	form.elements['criterion_action'].value = 'delete';
	form.submit();
}
