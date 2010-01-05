function checkLangPack(){
	$('p#resultCheckLangPack').hide();
	if ($('#iso_code').val().length == 2)
	{
		$.ajax(
		{
		   url: "ajax_lang_packs.php",
		   cache: false,
		   data: 
				"iso="+$('#iso_code').val()
		   ,
		   success: function(ret)
		   {
				if (ret == "ok")
					$('p#resultCheckLangPack').html(langPackOk+' <a href="http://www.prestashop.com/download/lang_packs/gzip/'+$('#iso_code').val()+'.gzip" target="_blank">'+download+'</a><br />'+langPackInfo).show("slow");
				else if (ret == "offline")
					$('p#resultCheckLangPack').show('slow');
				else
					$('p#resultCheckLangPack').html(noLangPack).show("slow");
		   }
		 }
		 );
	 }
}

$(document).ready(function() {
	$('p#resultCheckLangPack').hide();
});
