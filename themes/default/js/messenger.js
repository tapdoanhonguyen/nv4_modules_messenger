/**
 * @Project NUKEVIET 4.x
 * @Author mynukeviet (contact@mynukeviet.net)
 * @Copyright (C) 2016 mynukeviet. All rights reserved
 * @License: Not free read more http://nukeviet.vn/vi/store/modules/nvtools/
 * @Createdate Wed, 13 Jul 2016 00:05:30 GMT
 */

$(document).ready(function() {
	$('#frm-addtopic').submit(function(){
		for (instance in CKEDITOR.instances)
		    CKEDITOR.instances[instance].updateElement();
		$.ajax({
			type: 'POST',
			cache: false,
			url: script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=content&nocache=' + new Date().getTime(),
			data: $(this).serialize(),
			dataType: 'json',
			beforeSend: function(){
				$('#btn-submit').html('<em class="fa fa-circle-o-notch fa-spin">&nbsp;&nbsp;</em>' + LANG.waiting + '...');
				$('#frm-addtopic').find('*').prop("disabled", true);
			},
			success: function(e){
				if( e.status == 'success' ){
					window.location.href = e.redirect;
				}else{
					alert(e.mess);
					$('#btn-submit').html('<em class="fa fa-sign-in">&nbsp;&nbsp;</em>' + LANG.addtopic);
					$('#frm-addtopic').find('*').prop("disabled", false);
					$('#' + e.input).focus();
					if(e.input == 'fcode'){
						change_captcha('#fcode');
					}
				}
			}
		});
	});
	
	$('#frm-reply').submit(function(){
		var data = {
			replyid: $('#replyid').val(),
			topicid: $('#topicid').val(),
			content: '',
			fcode: $('#fcode').val()
		}		
		
		if(CFG.editor == 'ckeditor'){
			 data.content = CKEDITOR.instances[nv_module_name + '_content'].getData() 
		}else{
			data.content = $('#content').val();
		}

		if(data.content == ''){
			alert(LANG.empty_content);
		}else{
			$.ajax({
				type: 'POST',
				cache: false,
				url: nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=viewtopic&nocache=' + new Date().getTime(),
				data: $.param(data) + '&reply=1',
				dataType: 'json',
				beforeSend: function(){
					$('#btn-submit').html('<em class="fa fa-circle-o-notch fa-spin">&nbsp;&nbsp;</em>' + LANG.waiting + '...');
					$('#frm-reply').find('*').prop("disabled", true);
				},
				success: function(e){
					if( e.status == 'success' ){
						if(CFG.firebase){
							myFirebaseRef.push({
								'notifyid': e.notifyid,
								'url': e.url,
								'title': e.title,
								'poster_id': e.poster_id,
								'poster_name': e.poster_name,
								'addtime': e.addtime,
								'feedback': e.feedback
							});	
						}
						
						$('.items .item').last().after(e.data);
						$('.reply_count').text(parseInt($('#reply_count').val()) + 1);
						$('.reply_time').text(e.addtime);

						if(CFG.editor == 'ckeditor'){
							CKEDITOR.instances['messenger_content'].setData(''); 
						}
					}else{
						alert(e.mess);
						$('#' + e.input).focus();
						if(e.input == 'fcode'){
							change_captcha('#fcode');
						}
					}
					$('#btn-submit').html('<em class="fa fa-sign-in">&nbsp;&nbsp;</em>' + LANG.reply);
					$('#frm-reply').find('*').prop("disabled", false);
				}
			});
		}
	});
});

var simplemde_edit;

function nv_delete_reply(replyid, topicid, is_topic){
	if (confirm(nv_is_del_confirm[0])) {
		$.post(nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=viewtopic&nocache=' + new Date().getTime(), 'delete_reply=1&replyid=' + replyid + '&topicid=' + topicid + '&is_topic=' + is_topic, function(res) {
			var r_split = res.split('_');
			if(r_split[0] == 'OK'){
				if(!is_topic){
					$('#p' + replyid).remove();
					$('.reply_count').text(parseInt($('.reply_count').text()) - 1);
				}else{
					window.location.href = nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name;
				}
			}else{
				alert(r_split[1]);
			}
		});
	}
	return false;
}

function nv_edit_click(replyid, is_topic){
	if(!is_topic){
		$('#btn-edit-' + replyid).prop('disabled', true);
		$('#replycontent-' + replyid).hide();
		$('#replycontent-' + replyid).after('<form onsubmit="nv_edit_save($(this)); return !1;" id="frm-edit"><input type="hidden" id="replyid" value="' + replyid + '" /><input type="hidden" id="topicid" value="' + CFG.topicid + '" /><div class="form-group"><textarea class="form-control" id="replyeditor-' + replyid + '"></textarea></div><button class="btn btn-primary btn-xs">' + LANG.update + '</button> <button class="btn btn-danger btn-xs" onclick="nv_edit_drop(' + replyid + ')">' + LANG.drop + '</button></form>');
		if(CFG.editor == 'ckeditor'){
			CKEDITOR.replace('replyeditor-' + replyid, {toolbar : "Basic"}).setData($('#replycontent-' + replyid).html());	
		}else{
			$('#replyeditor-' + replyid).val($('#replycontent-' + replyid).html());
		}	
	}else{
		window.location.href = nv_base_siteurl + "index.php?" + nv_lang_variable + "=" + nv_lang_data + "&" + nv_name_variable + "=" + nv_module_name + "&" + nv_fc_variable + "=content&id=" + CFG.topicid;
	}

	return !1;
}

function nv_edit_drop(replyid){
	if (confirm(LANG.drop_confirm)) {
		$('#btn-edit-' + replyid).prop('disabled', false);
		$('#replycontent-' + replyid).show();
		$('#frm-edit').remove();	
	}
}

function nv_edit_save($this){
	var data = {
		replyid: $this.find('#replyid').val(),
		topicid: $this.find('#topicid').val(),
		content: ''
	} 
	
	if(CFG.editor == 'ckeditor'){
		data.content = CKEDITOR.instances['replyeditor-' + data['replyid']].getData(); 
	}else{
		data.content = $('#replyeditor-' + data['replyid']).val();
	}

	if(data.content == ''){
		alert(LANG.empty_content);
	}else{
		$.ajax({
			type: 'POST',
			cache: false,
			url: script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=viewtopic&nocache=' + new Date().getTime(),
			data: $.param(data) + '&reply=1',
			dataType: 'json',
			success: function(e){
				if( e.status == 'success' ){					
					$('#frm-edit').remove();
					$('#btn-edit-' + data.replyid).prop('disabled', false);
					$('#replycontent-' + data.replyid).html(data.content).show();
				}
			}
		});
	}
	return !1;
}

function fix_image_content(){
	var news = $('.content'), newsW, w, h;
	if( news.length ){
		var newsW = news.innerWidth();
		$.each($('img', news), function(){
			if( typeof $(this).data('width') == "undefined" ){
				w = $(this).innerWidth();
				h = $(this).innerHeight();
				$(this).data('width', w);
				$(this).data('height', h);
			}else{
				w = $(this).data('width');
				h = $(this).data('height');
			}

			if( w > newsW ){
				$(this).prop('width', newsW);
				$(this).prop('height', h * newsW / w);
			}
		});
	}
}

function nv_list_action( action, del_confirm_no_post )
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
                    url : script_name + '?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=ajax&nocache=' + new Date().getTime(),
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
