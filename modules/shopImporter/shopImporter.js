var shopImporter = {
	
	moduleName: $('#import_module_name').val(),
	server: $('#server').val(),
	user: $('#user').val(),
	password: $('#password').val(),
	database: $('#database').val(),
	prefix: $('#prefix').val(),
	specificOptions : '',
	output : 1,
	hasErrors : 0,
	limit: 0,
	idMethod: 0,
	nbrMethod: 0,
	save : 0,
	srcError : '../modules/shopImporter/img/error.png',
	srcConf : '../modules/shopImporter/img/ok.png',
	srcImport : '../modules/shopImporter/img/ajax-loader.gif',
	srcWarn : '../modules/shopImporter/img/warn.png',
	srcDelete : '../modules/shopImporter/img/delete.gif',
	
	checkAndSaveConfig : function (save)
	{
		$.ajax({
	       type: 'GET',
	       url: '../modules/shopImporter/ajax.php',
	       async: false,
	       cache: false,
	       dataType : "json",
	       data: 'ajax=true&checkAndSaveConfig&moduleName='+this.moduleName+'&server='+this.server+'&user='+this.user+'&password='+this.password+'&database='+this.database+this.specificOptions ,
	       success: function(jsonData)
	       {
		       	if (!jsonData.hasError)
	    		{
			       	$('#checkAndSaveConfig').fadeOut('slow');
			       	$('#steps').html('<div id="database_feedback" style="display:none;" class="conf"><img src="'+shopImporter.srcConf+'">'+databaseOk+'</div>');
			    	$('#steps').html($('#steps').html()+'<input style="display:none" type="submit" name="next" id="next" class="button" value="'+testImport+'">');
			    	$('#database_feedback').fadeIn('slow', function() {
	    			if (save)
			    	{
			    		shopImporter.idMethod = 0;
			    		shopImporter.limit = 0;
			    		shopImporter.nbrMethod = conf.length;
			    		$('.truncateTable:checked').each(function (){ 
			    			shopImporter.truncatTable(this.id) 
			    		});
						
						if($('#truncat_feedback').length != 0)
							$('#truncat_feedback').removeClass('import').addClass('conf');
						
			    		shopImporter.getDatas(conf[shopImporter.idMethod]);
			    	}
			    	else
			    	{
				    	$('#next').fadeIn('slow', function () { 
					    	$('#next').unbind('click').click(function(){
								$('#next').fadeOut('fast', function() {
									shopImporter.nbrMethod = conf.length;
									shopImporter.getDatas(conf[shopImporter.idMethod]);
								});
								return false;
							});
						});
					}
		    	});		    	
		    }
		    else
		    {
		    	$('#steps').html('<div id="database_feedback" style="display:none;" class="error"><img src="'+shopImporter.srcError+'">'+jsonData.error+'</div>');
		    	$('#database_feedback').fadeIn('slow');
		    }
	       },
	      error: function(XMLHttpRequest, textStatus, errorThrown) 
	       {
	       		$('#steps').html($('#steps').html()+'<div id="technical_error_feedback" style="display:none;" class="error"><img src="'+shopImporter.srcError+'">TECHNICAL ERROR<br><br>Details: '+XMLHttpRequest.responseText+'</div>');
	       		$('#technical_error_feedback').fadeIn('slow');
	       		
	       }
	   });
	},
	
	getDatas : function (methodName)
	{		
		//check if method have to be call
		if (shopImporter.idMethod >= shopImporter.nbrMethod)
			shopImporter.displayEnd(false);
		else if ($('input[name='+methodName[0]+']:radio:checked').val() == 0)
		{
			shopImporter.idMethod ++;
			shopImporter.getDatas(conf[shopImporter.idMethod]);
			return;
		}
		if (typeof(methodName) != 'undefined' && !$('#ok_feedback_'+methodName[0]).length)
			$('#steps').html($('#steps').html()+'<div id="ok_feedback_'+methodName[0]+'" style="display:none;" class="import"><img src="'+this.srcImport+'">'+methodName[1]+'<span id="display_error_'+methodName[0]+'" style="display:none"><span><div id="feedback_'+methodName[0]+'_errors_list"></div></div>');
			$('#ok_feedback_'+methodName[0]).css('display', '');
			$('#checkAndSaveConfig').fadeIn('slow');
		
		$.ajax({
	       type: 'GET',
	       url: '../modules/shopImporter/ajax.php',
	       async: true,
	       cache: false,
	       dataType : "json",
	       data: 'ajax=true&getData&className='+methodName[2]+'&getMethod='+methodName[0]+'&moduleName='+this.moduleName+'&server='+this.server+'&user='+this.user+'&password='+this.password+'&database='+this.database+'&limit='+this.limit+'&save='+this.save+'&errors='+this.errors+'&hasErrors='+this.hasErrors+this.specificOptions ,
	       success: function(jsonData)
	       {		      
		       	var jsonError;
		       	if (jsonData.hasError)
	    		{
					jsonError = '';
					if (jsonData.error == 'not_exist')
					{
						$('#ok_feedback_'+methodName[0]).removeClass('conf').addClass('warn');
						$('#ok_feedback_'+methodName[0]).html('<img src="'+shopImporter.srcWarn+'">'+methodName[1]+' '+notExist);
					}
					else
					{
						for (i=0;i<jsonData.error.length;i++)
							jsonError = jsonError+'<li>Id : '+jsonData.error[i]+'</li>';
						$('#ok_feedback_'+methodName[0]).removeClass('import').addClass('error');
						$('#ok_feedback_'+methodName[0]+' >img:first').attr('src', shopImporter.srcError);
									
						if ($('#display_error_'+methodName[0]+'_link').length == 0)
						{
							$('#ok_feedback_'+methodName[0]).html($('#ok_feedback_'+methodName[0]).html()+'<span id="display_error_'+methodName[0]+'" style="float:right;"><a id="display_error_'+methodName[0]+'_link" class="display_error_link" rel="'+methodName[0]+'" href="#" onclick="enableShowErrors(\''+methodName[0]+'\'); return false;">'+showErrors+'(<span id="nbr_errors_'+methodName[0]+'">'+jsonData.error.length+'</span>)'+'</a></span><div style="display:none;" id="feedback_'+methodName[0]+'_errors_list"><ul>'+jsonError+'</ul></div>');
						}
						else
						{
							var nbrErrors = $('#nbr_errors_'+methodName[0]).html();
							var newNbrError = parseInt(jsonData.error.length) + parseInt(nbrErrors);
							$('#nbr_errors_'+methodName[0]).html(newNbrError);
							$('#feedback_'+methodName[0]+'_errors_list > ul').html($('#feedback_'+methodName[0]+'_errors_list > ul').html() + jsonError);
						}
					}
					if (jsonData.datas.length != 100)
					{
						shopImporter.idMethod ++;
						shopImporter.limit = 0;
					}
					else
						shopImporter.limit += 100;
						
					if ((shopImporter.idMethod < shopImporter.nbrMethod))
						shopImporter.getDatas(conf[shopImporter.idMethod]);
					else
						shopImporter.displayEnd(false);
	    		}
	    		else
	    		{
    				if (jsonData.datas.length != 100)
    				{
						$('#ok_feedback_'+methodName[0]).removeClass('import').addClass('conf');
						$('#ok_feedback_'+methodName[0]+'>img:first').attr('src', shopImporter.srcConf);
						shopImporter.idMethod ++;
						shopImporter.limit = 0;
						shopImporter.getDatas(conf[shopImporter.idMethod]);
					}
					else
					{
						shopImporter.limit += 100;
						if (shopImporter.idMethod < shopImporter.nbrMethod)
							shopImporter.getDatas(conf[shopImporter.idMethod]);
						else
							shopImporter.displayEnd(true);
					}	
				}
	       },
	       error: function(XMLHttpRequest, textStatus, errorThrown) 
	       {
	       		$('#steps').html($('#steps').html()+'<div id="technical_error_feedback" style="display:none;" class="error"><img src="'+shopImporter.srcError+'">TECHNICAL ERROR<br><br>Details: '+XMLHttpRequest.responseText+'</div>');
	       		$('#technical_error_feedback').fadeIn('slow');
	       		$('#checkAndSaveConfig').fadeIn('slow');
	       }
	   });
	},
	
	truncatTable : function (className)
	{
		if (!$('#truncat_feedback').length)
		{
			$('#steps').html($('#steps').html()+'<div id="truncat_feedback" style="display:none;" class="conf"><img src="'+this.srcConf+'">'+truncateTable+'</div>');
			$('#truncat_feedback').css('display', '');
		}
		
		$.ajax({
	       type: 'GET',
	       url: '../modules/shopImporter/ajax.php',
	       async: false,
	       cache: false,
	       dataType : "json",
	       data: 'ajax=true&truncatTable&className='+className+this.specificOptions ,
	       success: function(jsonData)
	       {		      
		       	var jsonError;
		       	if (jsonData.hasError)
	    		{
	    			jsonError = '';
	    			for (i=0;i<jsonData.error.length;i++)
							jsonError = jsonError+'<li>Table : '+jsonData.error[i]+'</li>';
					$('#truncat_feedback').removeClass('import').addClass('error');
					$('#truncat_feedback >img:first').attr('src', shopImporter.srcError);
					if ($('#display_error_truncat_feedback_link').length == 0)
					{
						$('#truncat_feedback').html($('#truncat_feedback').html()+'<span id="display_error_truncat" style="float:right;"><a id="display_error_truncat_link" class="display_error_link" rel="truncat" href="#" onclick="enableShowErrorsTruncate(); return false;">'+showErrors+'</a></span><div style="display:none;" id="feedback_truncat_errors_list"><ul>'+jsonError+'</ul></div>');
					}
					else
					{
						var nbrErrors = $('#nbr_errors_'+methodName[0]).html();
						var newNbrError = parseInt(jsonData.error.length) + parseInt(nbrErrors);
						$('#nbr_errors_'+methodName[0]).html(newNbrError);
						$('#feedback_'+methodName[0]+'_errors_list > ul').html($('#feedback_'+methodName[0]+'_errors_list > ul').html() + jsonError);
					}
	    		}
	    		else
	    		{
	    			
	    		}
	    	},
	    	error: function(XMLHttpRequest, textStatus, errorThrown) 
	       {
	       		$('#steps').html($('#steps').html()+'<div id="technical_error_feedback" style="display:none;" class="error"><img src="'+shopImporter.srcError+'">TECHNICAL ERROR<br><br>Details: '+XMLHttpRequest.responseText+'</div>');
	       		$('#technical_error_feedback').fadeIn('slow');
	       		$('#checkAndSaveConfig').fadeIn('slow');
	       }
	    	
		});
	},
	
	displayEnd : function (finish)
	{	
		if (finish)
		{
			$('#steps').html($('#steps').html()+'<div id="technical_error_feedback" style="display:none;" class="conf"><img src="'+shopImporter.srcConf+'">'+importFinish+'</div>');
			$('#technical_error_feedback').fadeIn('slow');
		}
		else if ((this.hasErrors != 0 || ($('.display_error_link').length == 0 && this.hasErrors == 0)) || (this.hasErrors == 1))
		{
			$('#steps').html($('#steps').html()+'<input style="display:none" type="submit" name="submitImport" id="submitImport" class="button" value="'+import+'">');
			$('#submitImport').fadeIn('slow', function() {
				$(this).unbind('click').click(function() {
					shopImporter.save = 1;
					shopImporter.checkAndSaveConfig(shopImporter.save);
				});
			});
		}
		else
		{
			$('#steps').html($('#steps').html()+'<div id="technical_error_feedback" style="display:none;" class="error"><img src="'+shopImporter.srcError+'">'+importHasErrors+'</div>');
			$('#technical_error_feedback').fadeIn('slow');
		}
		$('#checkAndSaveConfig').fadeIn('slow');
	},	

};


function enableShowErrors(methodName)
{
	$(document).find('#feedback_'+methodName+'_errors_list').slideToggle();
	return false;
}

function enableShowErrorsTruncate()
{
	$(document).find('#feedback_truncat_errors_list').slideToggle();
	return false;
}

function displaySpecificOptions(moduleName, server, user, password, database, prefix)
{
	$.ajax({
	       type: 'GET',
	       url: '../modules/shopImporter/ajax.php',
	       async: false,
	       cache: false,
	       dataType : "html",
	       data: 'ajax=true&displaySpecificOptions&moduleName='+moduleName+'&server='+ server+'&user='+user+'&password='+password+'&database='+database+'&prefix='+prefix ,
	       success: function(htmlData)
	       {
	       		if (htmlData != 'not_exist')
	       		{
	       			$('#specificOptionsContent').html(htmlData);
	       			$('#specificOptions').show();
	       		}
	       },
	       error: function(XMLHttpRequest, textStatus, errorThrown)
		   {
		   		alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		   }
	   });
}

$(document).ready(function(){
	
	$('input[name=hasErrors]:radio').change(function () {
		if ($(this).val() == 1)
			$('#warnSkip').fadeIn('slow');
		else
			$('#warnSkip').fadeOut('slow');
	});
	
	$('#choose_module_name').unbind('click').click(function(){
		$('#db_config').hide();
		$('#importOptions').hide();
		$('#steps').html('');
		if ($('#import_module_name').attr('value') != 0)
		{
			$('#db_config').slideDown('slow');
			$('#displayOptions').show();
			$('#checkAndSaveConfig').show();
		}
		else
		{
			$('#db_config').slideUp('slow');
			$('#checkAndSaveConfig').show();
		}
		return false;
	});
	
	$('#displayOptions').unbind('click').click(function(){
		$(this).fadeOut('slow');
		$('#importOptions').slideDown('slow');
			moduleName = $('#import_module_name').val();
			server = $('#server').val();
			user = $('#user').val();
			password = $('#password').val();
			database = $('#database').val();
			prefix = $('#prefix').val();
		displaySpecificOptions(moduleName, server, user, password, database, prefix);
		return false;
	});	
	
	$('#checkAndSaveConfig').unbind('click').click(function(){
		$('#steps').html('');
		shopImporter.specificOptions = '';
		$('#specificOptionsContent :input').each(function (){
			shopImporter.specificOptions = shopImporter.specificOptions+'&'+$(this).attr('name')+'='+$(this).attr('value');
		});
			shopImporter.idMethod = 0;
			shopImporter.limit = 0;
			shopImporter.save = 0;
			shopImporter.moduleName = $('#import_module_name').val();
			shopImporter.server = $('#server').val();
			shopImporter.user = $('#user').val();
			shopImporter.password = $('#password').val();
			shopImporter.database = $('#database').val();
			shopImporter.prefix = $('#prefix').val();
			shopImporter.hasErrors = $('input[name=hasErrors]:radio:checked').val();
			shopImporter.checkAndSaveConfig(shopImporter.save);
		return false;
		
	});	
	
	$('#importOptionsYesNo :radio').change( function () {
		$('#steps').html('');
		onThing = false;
		
		$('#importOptionsYesNo :radio:checked').each( function () {
			if ($(this).attr('value') == 1)
				onThing = true;
		});
		if (onThing)
			$('#checkAndSaveConfig').fadeIn();
		else
		{
			$('#checkAndSaveConfig').fadeOut();
			$('#steps').html('<div id="one_thing_error_feedback" style="display:none;" class="error"><img src="'+shopImporter.srcError+'">'+oneThing+'</div>');
			$('#one_thing_error_feedback').fadeIn('slow');
		}			
	});
	
	
});