<!-- BEGIN: main -->
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.css" />
<link rel="stylesheet" href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2-bootstrap.min.css" />

<div class="content">
	<h1>{LANG.addtopic}</h1>
	
	<!-- BEGIN: error -->
	<div class="alert alert-warning">{ERROR}</div>
	<!-- END: error -->
	
	<form class="form-horizontal" onsubmit="return false;" id="frm-addtopic">
		<input type="hidden" name="submit" value="1" />
		<div class="row">
			<div class="col-xs-24 col-sm-18 col-md-18">
				<div class="panel panel-default">
					<div class="panel-body">
						<input type="hidden" name="id" value="{ROW.id}" />
						<div class="form-group">
							<input class="form-control required" type="text" name="title" id="title" value="{ROW.title}" required="required" oninvalid="setCustomValidity( nv_required )" oninput="setCustomValidity('')" placeholder="{LANG.title}" />
						</div>
						<div class="form-group">{ROW.content}</div>
						<!-- BEGIN: captcha -->
						<div class="form-group">
							<label class="col-sm-5 col-md-4 control-label"><strong>{LANG.captcha}</strong></label>
							<div class="col-sm-19 col-md-20">
								<input type="text" placeholder="{LANG.captcha}" maxlength="{NV_GFX_NUM}" value="" name="fcode" id="fcode" class="fcode required form-control display-inline-block" style="width: 100px;" data-pattern="/^(.){{NV_GFX_NUM},{NV_GFX_NUM}}$/" onkeypress="nv_validErrorHidden(this);" data-mess="{LANG.error_captcha}" /> <img width="{GFX_WIDTH}" height="{GFX_HEIGHT}" title="{LANG.captcha}" alt="{LANG.captcha}" src="{NV_BASE_SITEURL}index.php?scaptcha=captcha&t={NV_CURRENTTIME}" class="captchaImg display-inline-block"> <em onclick="change_captcha('.fcode');" title="{GLANG.captcharefresh}" class="fa fa-pointer fa-refresh margin-left margin-right"></em>
							</div>
						</div>
						<!-- END: captcha -->
					</div>
				</div>
				<div class="form-group text-center">
					<button class="btn btn-primary" name="submit" type="submit" id="btn-submit">
						<em class="fa fa-sign-in">&nbsp;&nbsp;</em>{LANG.send}
					</button>
				</div>
			</div>
			<div class="col-xs-24 col-sm-6 col-md-6">
				<div class="panel panel-primary">
					<div class="panel-heading">{LANG.list_users}</div>
					<div class="panel-body">
						<select class="form-control" name="list_users[]" id="list_users" multiple="multiple">
							<!-- BEGIN: user -->
							<option value="{USER.userid}" selected="selected">{USER.username}</option>
							<!-- END: user -->
						</select>
					</div>
				</div>
				<!-- BEGIN: groups_topic -->
				<div class="panel panel-primary list_groups">
					<div class="panel-heading">{LANG.list_groups}</div>
					<div class="panel-body">
						<div style="max-height: 200px; overflow: scroll; padding: 10px;">
							<!-- BEGIN: loop -->
							<label class="show"><input type="checkbox" name="list_groups[]" value="{GROUPS_TOPIC.index}"{GROUPS_TOPIC.checked}>{GROUPS_TOPIC.value}</label>
							<!-- END: loop -->
						</div>
					</div>
				</div>
				<!-- END: groups_topic -->
			</div>
		</div>
	</form>
</div>
<script type="text/javascript" src="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.js"></script>
<link href="{NV_BASE_SITEURL}{NV_ASSETS_DIR}/js/select2/select2.min.css" type="text/css" rel="stylesheet" />

<script>
	$("#list_users").select2({
		language : "{NV_LANG_INTERFACE}",
	
		ajax : {
			url : nv_base_siteurl + 'index.php?' + nv_lang_variable + '=' + nv_lang_data + '&' + nv_name_variable + '=' + nv_module_name + '&' + nv_fc_variable + '=ajax&get_user_json=1&topicid={ROW.id}',
			dataType : 'json',
			delay : 250,
			data : function(params) {
				return {
					q : params.term, // search term
					page : params.page
				};
			},
			processResults : function(data, params) {
				params.page = params.page || 1;
				return {
					results : data,
					pagination : {
						more : (params.page * 30) < data.total_count
					}
				};
			},
			cache : true
		},
		escapeMarkup : function(markup) {
			return markup;
		}, // let our custom formatter work
		minimumInputLength : 3,
		templateResult : formatRepo, // omitted for brevity, see the source of this page
		templateSelection : formatRepoSelection
	// omitted for brevity, see the source of this page
	});

	function formatRepo(repo) {
		if (repo.loading)
			return repo.text;
		var markup = '<div class="clearfix">' + '<div class="col-xs-12 col-sm-12 col-md-12">' + repo.username + '</div>' + '<div class="col-xs-12 col-sm-12 col-md-12 text-right">' + repo.fullname + '</div>' + '</div>';
		markup += '</div></div>';
		return markup;
	}

	function formatRepoSelection(repo) {
		$('#username').val(repo.username);
		return repo.username || repo.text;
	}

	var LANG = [];
	LANG['empty_title'] = '{LANG.empty_title}';
	LANG['empty_content'] = '{LANG.empty_content}';
	LANG['empty_list_users'] = '{LANG.empty_content}';
	LANG['waiting'] = '{LANG.waiting}';
	LANG['addtopic'] = '{LANG.addtopic}';

	var CFG = [];
	CFG['editor'] = '{EDITOR}';
</script>
<!-- END: main -->