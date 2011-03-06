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
	{/if}
	var lastMove = '';
	var saveOK = '{l s='Module position save'}';
</script>

<div style="width:100%;height:30px;padding-top:10px;background-color:#D0D3D8;border:solid 1px gray;position:fixed;bottom:0;opacity:0.7" onmouseover="$(this).css('opacity', 1);" onmouseout="$(this).css('opacity', 0.7);">
	<!--<input type="submit" value="{l s='Undo'}" id="cancelMove" class="button" style="float:left"> -->
	<input type="submit" value="{l s='Save'}" id="saveLiveEdit" class="exclusive" style="float:left">
	<input type="submit" value="{l s='Close Live edit'}" id="closeLiveEdit" class="button" style="float:left" onclick="window.close();">
	<div style="float:right;margin-right:20px;" id="live_edit_feed_back"></div>
</div>
<a href="#" style="display:none;" id="fancy"></a>
<div id="live_edit_feedback" style="display:none;"> 
	<p> 
		<!-- <a href="javascript:;" onclick="$.fancybox.close();">{l s='Close'}</a> --> 
	</p> 
</div>	
