/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @Createdate Wed, 13 Jul 2016 00:05:30 GMT
 */

$(document).ready(function() {
	$('.loading').click(function() {
		if($.validator){
			var valid = $(this).closest('form').valid();
			if(valid){
				$('body').append('<div class="ajax-load-qa"></div>');
			}
		}else{
			var valid = $(this).closest('form').find('input:invalid').length;
			if(valid == 0){
				$('body').append('<div class="ajax-load-qa"></div>');
			}
		}
	});
});

function nv_list_action( action, url_action, del_confirm_no_post )
{
	var listall = [];
	$('input.post:checked').each(function() {
		listall.push($(this).val());
	});
	if (listall.length < 1) {
		alert( del_confirm_no_post );
		return false;
	}
	if( action == 'delete_list_id' )
	{
		if (confirm(nv_is_del_confirm[0])) {
			$.ajax({
				type : 'POST',
				url : url_action,
				data : 'delete_list=1&listall=' + listall,
				success : function(data) {
					var r_split = data.split('_');
					if( r_split[0] == 'OK' ){
						window.location.href = window.location.href;
					}
					else{
						alert( nv_is_del_confirm[2] );
					}
				}
			});
		}
	}
	return false;
}