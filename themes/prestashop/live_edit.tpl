{*
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
*  International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript">
	{if isset($ad) && isset($live_edit)}
	var ad = "{$smarty.get.ad}";
	var modules_list = new Array();
	var hooks_list = new Array();
	var hookable_list = new Array();
	{/if}
	var lastMove = '';
	var saveOK = '{l s='Module position save'}';
</script>
<div style="100%;height:20px;background-color:#D0D3D8;">
	<a href="#" id="cancelMove">{l s='Cancel'}</a>&nbsp;
	<a href="#" id="saveLiveEdit">{l s='Save'}</a>&nbsp;
	<a href="?close_live_edit" id="closeLiveEdit">{l s='Close Live edit'}</a>
</div>