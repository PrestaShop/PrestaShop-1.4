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
*  @copyright  2007-2010 Prestashop SA
*  @version  Release: $Revision: 1.4 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registred Trademark & Property of PrestaShop SA
*/

function checkLangPack(){
	$('p#resultCheckLangPack').hide();
	if ($('#iso_code').val().length == 2)
	{
		$.ajax(
		{
		   url: "ajax_lang_packs.php",
		   cache: false,
		   data: {iso_lang:$('#iso_code').val(), ps_version:'1.3.0.0'},
		   dataType : 'json',
		   success: function(ret)
		   {
		   		console.log(ret);
			   	if(typeof ret == 'object')
					$('p#resultCheckLangPack').html(langPackOk+'<br />'+langPackVersion+''+ret.version+' <a href="http://www.prestashop.com/download/lang_packs/gzip/'+$('#iso_code').val()+'.gzip" target="_blank">'+download+'</a><br />'+langPackInfo).show("slow");
				else if (ret == "offline")
					$('p#resultCheckLangPack').show('slow');
		   }
		 }
		 );
	 }
}

$(document).ready(function() {
	$('p#resultCheckLangPack').hide();
});
