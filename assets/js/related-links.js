jQuery(document).ready(function($){

	// add new elements
	if($('select#related-services-meta').children().length >= 1)
	{
		$('ul#inactive-related-services').before('<input type="text" id="search-related-services" placeholder="Search" />');
	}
	$('select#related-services-meta').hide();

	$('#related-services-meta option').each(function(){
		if($(this).attr('selected') == 'selected')
		{
			$('#active-related-services').append('<li id="'+$(this).attr('value')+'"><span>'+$(this).text()+'<a id="remove" href="#">X</a></span></li>');
		}else{
			$('#inactive-related-services').append('<li id="'+$(this).attr('value')+'"><span>'+$(this).text()+'<a id="add" href="#">Add</a></span></li>');
		}
	});

	// add selection to select box
	$('#add').live('click', function(){
		id = $(this).parents('li').attr('id');
		$('#related-services-meta option[value="'+id+'"]').attr('selected', 'selected');
		$('#active-related-services').append('<li id="'+id+'"><span>'+$('#related-services-meta option[value="'+id+'"]').text()+'<a id="remove" href="#">X</a></span></li>');
		$(this).parents('li').remove();
		return false;
	});

	// remove selection from select box
	$('#remove').live('click', function(){
		id = $(this).parents('li').attr('id');
		$('#related-services-meta option[value="'+id+'"]').attr('selected', false);
		$('#inactive-related-services').append('<li id="'+id+'"><span>'+$('#related-services-meta option[value="'+id+'"]').text()+'<a id="add" href="#">add</a></span></li>');
		$(this).parent().parent('li').remove();
		return false;
	});

	// search on keypress
	$('#search-related-services').keyup(function(){
		var filter = $(this).val(), count = 0;
		$("#inactive-related-services li").each(function(){
			// If the list item does not contain the text phrase fade it out
			if ($(this).text().search(new RegExp(filter, "i")) < 0) {
				$(this).fadeOut();

			// Show the list item if the phrase matches and increase the count by 1
			} else {
				$(this).show();
				count++;
			}
		});
	});

	// hide all unchecked
	$('.settings_page_related-links-settings input[id$="_visible"]').each(function(){
		if($(this).attr('checked') != 'checked')
		{
			$(this).parents('tr').nextAll().hide();
		}
	});

	// hide when unchecked
	$('.settings_page_related-links-settings input[id$="_visible"]').click(function(){
		if($(this).attr('checked') != 'checked')
		{
			$(this).parents('tr').nextAll().hide();
		}else{
			$(this).parents('tr').nextAll().show();

			tbody = $(this).parents('tbody');
			if(tbody.find('input[id$="|page"]').attr('checked') != 'checked')
			{
				tbody.find('select[id$="_page"]').parents('tr').hide();
			}
		}
	});

	// hide all unchecked page selects
	$('.settings_page_related-links-settings input[id$="|page"]').each(function(){
		if($(this).attr('checked') != 'checked')
		{
			$(this).parents('tr').nextUntil('tr select[id$="_page"]').hide();
		}
	});

	// hide when unchecked page selects
	$('.settings_page_related-links-settings input[id$="|page"]').click(function(){
		if($(this).attr('checked') != 'checked')
		{
			$(this).parents('tr').nextUntil('tr select[id$="_page"]').hide();
		}else{
			$(this).parents('tr').nextUntil('tr select[id$="_page"]').show();
		}
	});
});