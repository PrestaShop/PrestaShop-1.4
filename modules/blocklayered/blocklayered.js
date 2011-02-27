/*
* 2007-2011 PrestaShop 
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

$(document).ready(function()
{
	cancelFilter();
	openCloseFilter();
	
	$('#layered_form input[type=checkbox]').live('click', function()
	{
		reloadContent();
	});
});

function cancelFilter()
{
	$('#enabled_filters a').live('click', function()
	{
		$('#'+$(this).attr('rel')).attr('checked', false);
		reloadContent();
	});
}

function openCloseFilter()
{
	$('#layered_form span.layered_close a').live('click', function()
	{
		if ($(this).html() == '&lt;')
		{
			$('#'+$(this).attr('rel')).show();
			$(this).html('v');
		}
		else
		{
			$('#'+$(this).attr('rel')).hide();
			$(this).html('&lt;');
		}
	});
}

function reloadContent()
{
	$('#product_list').empty().html($('#layered_ajax_loader').html());
	
	$.ajax(
	{
		type: 'GET',
		url: baseDir + 'modules/blocklayered/blocklayered-ajax.php',
		data: $('#layered_form').serialize(),
		dataType: 'json',
		success: function(data)
		{
			$('#layered_block_left').after(data.layered_block_left).remove();
			$('#product_list').html(data.product_list);
		}
	});
}