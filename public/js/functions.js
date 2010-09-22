//
// DLL Specific javascript
//

function callService( action, data, method)
{
	$.ajax({
		type: method || 'POST',
		url: 'service.php?action='+action,
		data: 'data='+JSON.stringify(data)
	});
}

function changePageOrder( e, ui )
{
	var item = ui.item[0];
	var data = [];
	var dll_id = 0;
	
	$('li:not(:last)',item.parentNode).each(function(c){
		dll_id = parseInt($('a',this).attr('href').substring(10));
		data.push(dll_id);
	});
	callService('dll_update_page',data);
}

// function selectPage( page_id )
// {
// 	if(!page_id) page = dll_page_data;
// 	else {
// 		
// 	}
// 	$('#edit_planes div').hide();
// 	if(!dll_page_data.id) return;
// 	
// 	$('#dll_update_page input[name=title]').val(dll_page_data.title);
// 	$('#dll_update_page input[name=time]').val(dll_page_data.time);
// 	$('#edit_'+dll_page_data.type+'_plane').show();
// }

$(document).ready(function(){
	
	// Make pages sortable
	$('#page_select_box ul').sortable({
		containment: 'parent',
		items: $('li:not(:last)',this),
		stop: changePageOrder
	}).disableSelection();
	
	// Select screen
// 	updateScreens();
});
