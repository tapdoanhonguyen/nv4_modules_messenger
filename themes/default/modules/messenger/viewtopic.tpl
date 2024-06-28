<!-- BEGIN: main -->
<div class="viewtopic">
	<div class="header m-bottom">
		<h1>{TOPIC.title}</h1>
	</div>
	
	<div class="row">
		<div class="col-xs-24 col-sm-17 col-md-17">
			<div class="items">
				{TOPIC_ITEM}
				
				<!-- BEGIN: loop -->
				{REPLY_ITEM}
				<!-- END: loop -->
			</div>
			
			<!-- BEGIN: page -->
			<div class="text-center">{PAGE}</div>
			<!-- END: page -->
			
			<!-- BEGIN: frm_reply -->
			<form id="frm-reply" class="form-horizontal" onsubmit="return false;">
				<input type="hidden" id="replyid" value="{REPLY_CONTENT.id}" />
				<input type="hidden" id="topicid" value="{TOPIC.id}" />
				<input type="hidden" id="reply_count" value="{TOPIC.reply_count}" />
				<div class="m-bottom">
					{REPLY_CONTENT.content}
				</div>
				<!-- BEGIN: captcha -->
				<div class="form-group text-center">
					<input type="text" placeholder="{LANG.captcha}" maxlength="{NV_GFX_NUM}" value="" name="fcode" id="fcode" class="fcode required form-control display-inline-block" style="width:100px;" data-pattern="/^(.){{NV_GFX_NUM},{NV_GFX_NUM}}$/" onkeypress="nv_validErrorHidden(this);" data-mess="{LANG.error_captcha}"/>
					<img width="{GFX_WIDTH}" height="{GFX_HEIGHT}" title="{LANG.captcha}" alt="{LANG.captcha}" src="{NV_BASE_SITEURL}index.php?scaptcha=captcha&t={NV_CURRENTTIME}" class="captchaImg display-inline-block">
					<em onclick="change_captcha('.fcode');" title="{GLANG.captcharefresh}" class="fa fa-pointer fa-refresh margin-left margin-right"></em>
				</div>
				<!-- END: captcha -->
				<div class="text-center m-bottom">
					<button class="btn btn-primary" name="submit" type="submit" id="btn-submit"><em class="fa fa-sign-in">&nbsp;&nbsp;</em>{LANG.reply}</button>
				</div>
			</form>
			<!-- END: frm_reply -->
		</div>
		<div class="col-xs-24 col-sm-7 col-md-7">
			<div class="panel panel-primary">
				<div class="panel-heading">{LANG.topic_info}</div>
				<div class="panel-body">
					<ul class="topic_info">
						<li><label>{LANG.list_users_count}</label>: {TOPIC.user_count}</li>
						<li><label>{LANG.reply_count}</label>: <span class="reply_count">{TOPIC.reply_count}</span></li>
						<li><label>{LANG.last_reply_time}</label>: <span class="reply_time">{TOPIC.reply_time}</span></li>
					</ul>
				</div>
			</div>
			<div class="panel panel-primary">
				<div class="panel-heading">{LANG.list_users}</div>
				<div class="panel-body">
					<ul class="list_users">
						<!-- BEGIN: user -->
						<li class="clearfix">
							<div class="avatar pull-left">
								<img src="{USER.photo}">
							</div>
							{USER.fullname} <!-- BEGIN: isyou -->({LANG.you})<!-- END: isyou -->
						</li>
						<!-- END: user -->
					</ul>
				</div>
			</div>
			<!-- BEGIN: groups -->
			<div class="panel panel-primary">
				<div class="panel-heading">{LANG.list_groups}</div>
				<div class="panel-body">
					<ul class="list_users">
						<!-- BEGIN: loop -->
						<li class="clearfix">
							{GROUP.number}. {GROUP.title}
						</li>
						<!-- END: loop -->
					</ul>
				</div>
			</div>
			<!-- END: groups -->
		</div>
	</div>
</div>
<script>
	var LANG = [];
	LANG['empty_content'] = '{LANG.empty_content}';
	LANG['update'] = '{LANG.update}';
	LANG['drop'] = '{LANG.drop}';
	LANG['drop_confirm'] = '{LANG.drop_confirm}';
	LANG['waiting'] = '{LANG.waiting}';
	LANG['reply'] = '{LANG.reply}';
	
	var CFG = [];
	CFG['editor'] = '{EDITOR}';
	CFG['firebase'] = '{FIREBASE}';
	CFG['topicid'] = '{TOPIC.id}';
	
	$(window).on('load', function() {
		fix_image_content();
	});

	$(window).on("resize", function() {
		fix_image_content();
	});
		
	$('.pagination').addClass('pagination-sm');
</script>
<!-- END: main -->