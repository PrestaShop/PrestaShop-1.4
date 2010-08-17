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

$(document).ready(function() {
	$('table.tableDnD').tableDnD({
		onDragStart: function(table, row) {
			originalOrder = $.tableDnD.serialize();
			reOrder = ':even';
			if ($('#' + table.tBodies[0].rows[1].id).hasClass('alt_row'))
				reOrder = ':odd';
			$('#' + row.id).parent('tr').addClass('myDragClass');
		},
		dragHandle: 'dragHandle',
		onDragClass: 'myDragClass',
		onDrop: function(table, row) {
			if (originalOrder != $.tableDnD.serialize()) {
				var way = (originalOrder.indexOf(row.id) < $.tableDnD.serialize().indexOf(row.id))? 1 : 0;

				var ids = row.id.split('_');
				var tableDrag = $('#' + table.id);
				var params = '';
				
				if (come_from == 'AdminModulesPositions')
					params = {
						ajaxModulesPositions: true,
						id_hook: ids[0],
						id_module: ids[1],
						way: way,
						token: token
					};
				else if (come_from == 'product') {
					params = {
						ajaxProductsPositions: true,
						id_category: ids[1],
						id_product: ids[2],
						way: way,
						token: token
					};
				}

				$.ajax({
					type: 'POST',
					async: false,
					url: 'ajax.php?' + $.tableDnD.serialize(),
					data: params,
					success: function(data) {
						if (come_from == 'AdminModulesPositions') {
							tableDrag.find('tr').removeClass('alt_row');
							tableDrag.find('tr' + reOrder).addClass('alt_row');
							tableDrag.find('td.positions').each(function(i) {
								$(this).html(i+1);
							});
							tableDrag.find('td.dragHandle a:hidden').show();
							tableDrag.find('td.dragHandle:first a:even').hide();
							tableDrag.find('td.dragHandle:last a:odd').hide();
						}
						else if (come_from == 'product') {
							var reg = /_[0-9]$/g;
							tableDrag.find('tbody tr').each(function(i) {
								$(this).attr('id', $(this).attr('id').replace(reg, '_' + i));
								
								// Update link position
								var up_reg  = new RegExp('position=[-]?[0-9]+&');
								
								// Up links
								$(this).find('td.dragHandle a:odd').attr('href', $(this).find('td.dragHandle a:odd').attr('href').replace(up_reg, 'position='+ (i - 1) +'&'));
								
								// Down links
								$(this).find('td.dragHandle a:even').attr('href', $(this).find('td.dragHandle a:even').attr('href').replace(up_reg, 'position='+ (i + 1) +'&'));
								
							});
							
							tableDrag.find('tr').not('.nodrag').removeClass('alt_row');
							tableDrag.find('tr:not(".nodrag"):odd').addClass('alt_row');
							tableDrag.find('tr td.dragHandle a:hidden').show();
							
							if (alternate) {
								tableDrag.find('tr td.dragHandle:first a:odd').hide();
								tableDrag.find('tr td.dragHandle:last a:even').hide();
							}
							else {
								tableDrag.find('tr td.dragHandle:first a:even').hide();
								tableDrag.find('tr td.dragHandle:last a:odd').hide();
							}
						}
					}
				});
			}
		}
	});
})
