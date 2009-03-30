/**
  * Admin table Drag and Drop, admin-dnd.js
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.0
  *
  */

$(document).ready(function(){	
	$("table.tableDnD").tableDnD({
		onDragStart: function(table, row){
			originalOrder = $.tableDnD.serialize();		
			reOrder = ":even";
			if ($("#" + table.tBodies[0].rows[1].id).hasClass("alt_row"))
				reOrder = ":odd";
			$("#" + row.id).parents("tr").addClass("myDragClass");
		},
		dragHandle: "dragHandle",
		onDragClass: "myDragClass",
		onDrop: function(table, row){
			if (originalOrder != $.tableDnD.serialize())
			{	
				var dest = 'ajax.php';			
		       	var way = (originalOrder.indexOf(row.id) < $.tableDnD.serialize().indexOf(row.id))? 1 : 0;
		       	var ids = row.id.split('_');
		       	var params = 'ajaxModulesPositions=true&way=' + way + '&id_module=' + ids[1] + '&id_hook=' + ids[0]+ '&token=' + token +'&' + $.tableDnD.serialize();
		       	
		       	$.ajax({
					type: 'GET',
					url: dest,
					async: true,
					data: params,
					success: function(data){
						$("#" + table.id).find("tr").removeClass("alt_row");
			       		$("#" + table.id).find("tr" + reOrder).addClass("alt_row");
						$("#" + table.id).find("td.positions").each(function(i){
							$(this).html(i+1);
						});
						$("#" + table.id).find("td.dragHandle a:hidden").show();
						$("#" + table.id).find("td.dragHandle:first a:even").hide();
						$("#" + table.id).find("td.dragHandle:last a:odd").hide();		
					}
				});
			}
		}
	});
})