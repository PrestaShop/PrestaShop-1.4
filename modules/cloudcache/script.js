$(function(){
	$('.tabbed-form.content li').not('#tab1_content').hide();
	$('.tabbed-form.menu li').click(function(){
		$('.tabbed-form.menu li').removeClass('active');
		$(this).addClass('active');
		$('.tabbed-form.content li').hide();
		$('.tabbed-form.content li:eq(' + $(this).index() + ')').show();
	});
	$('.tabbed-form.menu li:eq(0)').addClass('active');
	$('.tabbed-form.content li:eq(0)').show();
	
	/* Make table first column bolded (on table with 2 cols) */
	$('.bold-first-column td:even').addClass('bold');
	
	/* Make sure all .tree.closed have (+) inside, and the content is not shown */
	$('span.tree-button').live('click', function(){
		$(this).parent().find('.details').toggle("fast");
		$(this).toggleClass('opened');
		
		if ($(this).hasClass('opened'))
			$(this).html('&ndash;');
		else
			$(this).html('+');
	});
	
	/* jExcerpt v1.0 */
	$('.jexcerpt-short').mouseover(function(){
		$('.jexcerpt-long').hide();
		$(this).parent().attr('width', $(this).parent().width());
		$(this).parent().find('.jexcerpt-long')
			.css('left', $(this).parent().offset().left)
			.css('top', $(this).parent().offset().top)
			.show();
	});
	
	$('.jexcerpt-long').mouseout(function(){
		$(this).parent().find('.jexcerpt-short').show();
		$(this).hide();
	});
	
	/* Add span.tree for those .tree who have .details */
	$('<span class="tree-button">+</span>').prependTo($('.tree').has('.details'));
	
	if ($('#cloudcache_edit_zone_form').size())
		window.scroll(0, $('#cloudcache_edit_zone_form').offset().top-100);
	
	/* CRUD Zones*/
	$('#cloudcache_add_zone_form').hide();
	$('#cloudcache_add_zone').click(function(event){
		event.preventDefault();
		indexActiveTab = $('.tabbed-form.menu li.active').index();
		$('#cloudcache_add_zone_form:eq(' + indexActiveTab + ') #type').attr('value', 'pullzone');
		$('#cloudcache_add_zone_form:eq(' + indexActiveTab + ')').toggle('fast');
	});
	$('.SubmitCloudcacheEditZone').click(function(event){
		$('.CloudcacheZone_action').val('edit');
		$(this).parent().submit();
	});
	$('.SubmitCloudcacheClearZoneCache').click(function(event){
		$('.CloudcacheZone_action').val('clear_zone_cache');
		$(this).parent().submit();
	});
	
	/* Validate the required fields */
	$('#SubmitCloudcacheAdd_zone').click(function(){
		if ($('#name').val().length < 1 || $('#origin').val().length < 1)
		{
			alert($('#requiredFieldsTranslation').text());
			return false;
		}
		else
			$(this).parent('form').submit();
	});
});
