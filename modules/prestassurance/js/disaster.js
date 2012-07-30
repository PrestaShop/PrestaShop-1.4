function submitDisaster()
{
	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/prestassurance/ajax_fo.php',
		async: true,
		cache: false,
		dataType : "json",
		data: 'ajax=true&token='+token_psa+'&submitDisaster=1&'+ $('#disaster_form').serialize(),
		success: function(jsonData)
		{
			if ($('#phone').val() == '')
				alert(phone_need);
			else
			{
				if (jsonData.hasError)
				{
					var errors = '';
					for(error in jsonData.errors)
						//IE6 bug fix
						if(error != 'indexOf')
							errors += jsonData.errors[error] + "<br/>";
					$('#alert_message').removeClass().addClass('psa_error');
					$('#alert_message').html(errors);
				}
				else
				{
					getOrderDisasterDetails($('#order_select').val());
					$('#alert_message').removeClass().addClass('psa_confirm');
					$('#alert_message').html(disaster_saved);		
				}
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert("TECHNICAL ERROR: unable to submit disaster.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
		}
	});
	return false;
}

function getOrderDisasterDetails(id_order)
{
	clearNewCommentForm();
	$('#disaster_comment').val('');
	$('#alert_message').html('');
	$('#alert_message').removeClass();
	if (!id_order || id_order == 0)
	{
		$('#contact_form_link, #reason_detail_container, #disaster_form_detail, #documents_list_container, #comment_container, #submit_disaster_button').fadeOut();
		return;
	}
	else
	{
		$('#contact_form_link, #reason_detail_container, #documents_list_container, #comment_container, #submit_disaster_button').fadeOut();
		$.ajax({
			type: 'GET',
			url: baseDir + 'modules/prestassurance/ajax_fo.php',
			async: true,
			cache: false,
			dataType : "json",
			data: 'ajax=true&token='+token_psa+'&getOrderDisasterDetails=1&id_order='+id_order,
			success: function(jsonData)
			{
				if (jsonData.hasError)
				{
					var errors = '';
					for(error in jsonData.errors)
						//IE6 bug fix
						if(error != 'indexOf')
							errors += jsonData.errors[error] + "<br/>";
					$('#alert_message').removeClass().addClass('psa_error');
					$('#alert_message').html(errors);
				}
				else
				{
					$('#disaster_follow_content table tbody').html('');
					$('#id_product').html('');
					var option = '';
					$.each(jsonData.details.product, function() 
					{
						if (this.product_id != id_psa_product)
						{
							option += '<option value="'+this.product_id+'">'+this.product_name+'</option>';
						}
					});
					$('#id_product').html(option);
					if (!jsonData.details.disaster.length)
					{
						tr  = document.createElement('tr');
						td = document.createElement('td');
						$(td).css('text-align','center');
						$(td).attr('colspan', 5);
						$(td).html(no_disaster);
						tr.appendChild(td);	
						$('#disaster_follow_content table tbody').append(tr);
					}	
					else
					{
						$.each(jsonData.details.disaster, function() 
						{
							tr  = document.createElement('tr');
							var id_disaster = this.id_disaster;
							
							if (typeof(this.id_disaster) != 'undefined')
								$('#id_disaster_new_comment').val(this.id_disaster);
							
							if (typeof(this.id_psa_disaster) != 'undefined')
								$('#id_psa_disaster_new_comment').val(this.id_psa_disaster);
							
							/*
td = document.createElement('td');
							$(td).html(this.id_psa_disaster);
							tr.appendChild(td);
*/
							
							td = document.createElement('td');
							$(td).html(this.name);
							tr.appendChild(td);
							
							td = document.createElement('td');
							
							switch (this.reason) 
							{ 
							case 'product_purchased_broken': 
								$(td).html(product_purchased_broken); 
							break; 
							case 'product_purchased_stolen': 
								$(td).html(product_purchased_stolen); 
							break; 
							case 'product_purchased_not_delivered': 
								$(td).html(product_purchased_not_delivered); 
							break; 
							case 'internet': 
								$(td).html(internet); 
							break; 
							}							
							
							tr.appendChild(td);
							
							td = document.createElement('td');
							img = document.createElement('img');
							$(img).attr('src', base_dir_ssl+'modules/prestassurance/img/'+this.status+'.png');
							$(td).css({'width':'30px','text-align':'center'});
							$(td).html(img);
							tr.appendChild(td);
							
							td = document.createElement('td');
							img = document.createElement('img');
							$(td).css({'width':'30px','text-align':'center'});
							a_details = document.createElement('a');
							$(a_details).bind('click', function () {
								$('#detail_'+id_disaster).toggle();
								return false;
							});
							$(a_details).html(img);
							
							$(img).attr('src', base_dir_ssl+'modules/prestassurance/img/details.gif');
							$(img).attr('title', 'Voir les commentaires');
							
							
							img = document.createElement('img');
							a_comment = document.createElement('a');
							$(td).css({'width':'30px','text-align':'center'});
							$(a_comment).bind('click', function () {
								$('#add_new_comment').fadeIn();
								return false;
							});
							$(a_comment).html(img);
							
							$(img).attr('src', base_dir_ssl+'modules/prestassurance/img/add_comment.png');
							$(img).attr('title', 'Ajouter un commentaires');
							td.appendChild(a_details);
							td.appendChild(a_comment);
							
							tr.appendChild(td);
							
							$('#disaster_follow_content table tbody').append(tr);
							
							tr  = document.createElement('tr');
							$(tr).css('display', 'none');
							$(tr).attr('id', 'detail_'+id_disaster);
							
							td = document.createElement('td');
							$(td).css('text-align','center');
							$(td).attr('colspan', 4);
							
							if (!this.comments.length)
							{
								td = document.createElement('td');
								$(td).css('text-align','center');
								$(td).attr('colspan', 4);
								$(td).html(no_coment);
							}
							else
							{	
								td = document.createElement('td');
								$(td).css('text-align','left');
								$(td).attr('colspan', 5);
								comments = '';
								$.each(this.comments, function() 
								{
									comments += '<p><b>'+this.date_add+'</b> '+(this.way == 1 ? your_comment : insurer_comment)+' : '+this.comment+'</p>';
								});
								$(td).html(comments);
								tr.appendChild(td);
								$('#disaster_follow_content table tbody').append(tr);
							}
							$('#disaster_follow_content table tbody').append(tr);
						});
					}
					$('#disaster_form_detail, #disaster_follow_content').fadeIn();		
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert("TECHNICAL ERROR: unable to get details.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
			}
		});
	}
	return false;
}


function submitAddNewComment()
{
	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/prestassurance/ajax_fo.php',
		async: true,
		cache: false,
		dataType : "json",
		data: 'ajax=true&token='+token_psa+'&addDisasterComment=1&id_psa_disaster='+ $('#id_psa_disaster_new_comment').val()+'&id_disaster='+ $('#id_disaster_new_comment').val()+'&comment='+ $('#disaster_new_comment').val(),
		success: function(jsonData)
		{
			if (jsonData.hasError)
			{
				var errors = '';
				for(error in jsonData.errors)
					//IE6 bug fix
					if(error != 'indexOf')
						errors += jsonData.errors[error] + "<br/>";
				$('#alert_message').removeClass().addClass('psa_error');
				$('#alert_message').html(errors);
			}
			else
			{
				getOrderDisasterDetails($('#order_select').val());
				$('#alert_message').removeClass().addClass('psa_confirm');
				$('#alert_message').html(comment_added);		
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert("TECHNICAL ERROR: unable to submit disaster.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
		}
	});
	return false;
}

function clearNewCommentForm()
{
	$('#add_new_comment').fadeOut();
	$('#disaster_new_comment').val('');
}

function showStep3(step_2)
{
	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/prestassurance/ajax_fo.php',
		async: true,
		cache: false,
		dataType : "json",
		data: 'ajax=true&token='+token_psa+'&getStep3Details=1&step_2='+step_2,
		success: function(jsonData)
		{
			$('#contact_form_link').hide();
			$('#comment_container, #submit_disaster_button').hide();
			//$('#reason_detail_container, #documents_list_container, #comment_container, #submit_disaster_button').hide();

			if (!jsonData)
			{
				$('#contact_form_link').show();
			}
			else
			{
				if (typeof(jsonData) == 'object')
				{
					$('#reason_detail').html('');
					var option = '<option value="0">-------------</option>';
					$.each(jsonData, function(key, value) 
					{
						option += '<option value="'+key+'">'+value+'</option>';
					});
					$('#reason_detail').html(option);
					$('#reason_detail_container').show();
					$('#reason_detail_container').show();
				}
				else
				{
					$('#reason_detail_container').hide();
					$('#comment_container, #submit_disaster_button').show();
				}
					
			}
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert("TECHNICAL ERROR.\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
		}
	});
}

function showFinalStep(key)
{
	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/prestassurance/ajax_fo.php',
		async: true,
		cache: false,
		dataType : "json",
		data: 'ajax=true&token='+token_psa+'&getFinalStepDetail=1&key='+key,
		success: function(jsonData)
		{
			$('#contact_form_link').hide();
			$('#documents_list_container, #documents_list_container, #comment_container, #submit_disaster_button').hide();
			if (!jsonData)
			{
				$('#contact_form_link').show();
			}
			else
			{
				$('ul#documents_list').html('');
				var list = '';
				$.each(jsonData, function(key, value) 
				{
					list += '<li>'+value+'</li>';
				});
				
				//$('ul##documents_list').html(list);
				//$('#documents_list_container, #documents_list_container, #comment_container, #submit_disaster_button').show();
				$('#comment_container, #submit_disaster_button').show();
				
			}
		
			
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert("TECHNICAL ERROR.\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
		}
	});
}