var timer;
var typeWatchOption = {
	callback: function() {calcFinalPriceAndBenefit()},
		wait: 600,
		highlight: true,
		captureLength: 0
	};

function configureTreeGrid()
{
	$('#treegrid').treegrid({
        width:905,
        height:500,
        nowrap: true,
        animate:true,
        row_numbers: false,
        singleSelect: true,
        idField:'category',
        treeField:'category',
        columns:[[  
	        {
	        	field:'category',
	        	title:'Catégories de la boutique',
	        	width:420,
	        	align:'left',
	        	formatter:function(value, elt, titi)
                	{
                    	return '<span>'+elt.name+'<\/span>';
                	}},  
	        {field:'psa_cat',title:'Catégorie assurance',width:270,align:'left'},  
	        {field:'price',title:'Prix de vente',width:90,align:'center'},  
	        {field:'action',title:'Action',width:70,align:'center'} 
		]],
        onBeforeLoad:function(row,param){
            if (row)
                $(this).treegrid('options').url = urlAjaxCategory+'?token='+token+'&getChildrenCategories=true';
            else
                $(this).treegrid('options').url = urlAjaxCategory+'?token='+token+'&getHomeCategories=true';
        },
        onLoadSuccess: function() {
        	configureMenuAction();
        }
    });
    
    $('.datagrid-body').css('background-color', '#FFF');
}


function configureMenuAction()
{
	$('.menu_action').each(function() {
	
		id = $(this).attr('id').replace('action_', '');
		
		$('#action_'+id).menubutton({
			menu:'#menu_action_'+id,
			duration: 100
		});
		$('#action_'+id).parent('td').addClass('datagrid-cell');
	});
}

function openWindowCategoryMatch(id_category)
{
	id_ps_category = id_category;
	$('#treegrid').treegrid('loading');
	
	$.ajax({
		type: 'POST',
		url: ajax_url + 'ajax.php',
		cache: false,
		dataType: "html",
		data: 'token=' + token + '&setCategory=true&id_category=' + id_category,
		success: function(htmlData) {
			openWindow('win_set_category_match',600, 630, htmlData);
			$('#treegrid').treegrid('loaded');
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
}

function openWindowPrice(id_category)
{
	id_ps_category = id_category;
	$('#treegrid').treegrid('loading');
	
	$.ajax({
		type: 'POST',
		url: ajax_url + 'ajax.php',
		cache: false,
		dataType: "html",
		data: 'token=' + token + '&setImpact=true&id_category=' + id_category,
		success: function(htmlData) {
			openWindow('win_set_price',400, 420, htmlData);
			$('#impact_value_' + id_ps_category).typeWatch(typeWatchOption);
			$('#treegrid').treegrid('loaded');
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert('TECHNICAL ERROR\nDetails:\nError thrown: ' + XMLHttpRequest + '\n' + 'Text status: ' + textStatus);
		}
	});
}


function openWindow(type, width, height, content)
{
	$('#'+type).html(content);
	$('#'+type).window({  
	    width:width,  
	    height:height,  
	    modal:true,
	    closable:true,
	    collapsible:false,
	    minimizable:false,
	    maximizable:false,
	    draggable:true,
	    resizable:false,
	    onOpen: function () {
	    
	    	if (type == 'win_set_price')
	    		configureSaveImpactButton();
	    }
	});  
}

function closeWindow()
{
	$('.window-body').window('close');
}

function getChildren()
{
	var node = $('#treegrid').treegrid('getSelected');
	if (node){
		var nodes = $('#treegrid').treegrid('getChildren', node.code);
	} else {
		var nodes = $('#treegrid').treegrid('getChildren');
	}
	var s = '';
	for(var i=0; i<nodes.length; i++){
		s += nodes[i].code + ',';
	}
	alert(s);
}
