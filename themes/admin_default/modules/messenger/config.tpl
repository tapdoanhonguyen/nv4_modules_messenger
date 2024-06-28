<!-- BEGIN: main -->
<form method="post" class="form-horizontal">
	<div class="panel panel-default">
		<div class="panel-heading">{LANG.config_system}</div>
		<div class="panel-body">
			<div class="form-group">
				<label class="col-sm-3 control-label"><strong>{LANG.config_per_page}</strong></label>
				<div class="col-sm-21">
					<input type="number" name="per_page" class="form-control" value="{DATA.per_page}" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label"><strong>{LANG.config_per_topic}</strong></label>
				<div class="col-sm-21">
					<input type="number" name="per_topic" class="form-control" value="{DATA.per_topic}" />
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 text-right"><strong>{LANG.config_groups_topic}</strong></label>
				<div class="col-sm-21">
					<div style="border: 1px solid #ddd; padding: 10px; height: 200px; overflow: scroll;">
						<!-- BEGIN: groups_topic -->
						<label class="show"><input title="{GROUP_TOPIC.title}" type="checkbox" name="groups_topic[]" value="{GROUP_TOPIC.value}" {GROUP_TOPIC.checked} />{GROUP_TOPIC.title}</label>
						<!-- END: groups_topic -->
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 text-right"><strong>{LANG.config_infoemail}</strong></label>
				<div class="col-sm-21">
					<label><input type="checkbox" value="1" name="infoemail" {DATA.ck_infoemail}>{LANG.config_infoemail_note}</label>
				</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default">
		<div class="panel-heading">{LANG.config_user}</div>
		<div class="panel-body">
			<div class="form-group">
				<label class="col-sm-3 text-right"><strong>{LANG.config_editor}</strong></label>
				<div class="col-sm-21">
					<!-- BEGIN: editor -->
					<label><input type="radio" name="editor" value="{EDITOR.index}" {EDITOR.checked} />{EDITOR.value}</label>&nbsp;&nbsp;&nbsp;&nbsp;
					<!-- END: editor -->
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 text-right"><strong>{LANG.config_groups_post}</strong></label>
				<div class="col-sm-21">
					<div style="border: 1px solid #ddd; padding: 10px; height: 200px; overflow: scroll;">
						<!-- BEGIN: groups_post -->
						<label class="show"><input title="{GROUPPOST.title}" type="checkbox" name="groups_post[]" value="{GROUPPOST.value}" {GROUPPOST.checked} />{GROUPPOST.title}</label>
						<!-- END: groups_post -->
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 text-right"><strong>{LANG.config_groups_topic_group}</strong> <em class="fa fa-question-circle fa-pointer text-info" data-toggle="tooltip" data-original-title="{LANG.config_groups_topic_group_note}">&nbsp;</em></label>
				<div class="col-sm-21">
					<div style="border: 1px solid #ddd; padding: 10px; height: 200px; overflow: scroll;">
						<!-- BEGIN: groups_topic_group -->
						<label class="show"><input title="{GROUP_TOPIC.title}" type="checkbox" name="groups_topic_group[]" value="{GROUP_TOPIC_GR.value}" {GROUP_TOPIC_GR.checked} />{GROUP_TOPIC_GR.title}</label>
						<!-- END: groups_topic_group -->
					</div>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-3 control-label"><strong>{LANG.config_captcha}</strong></label>
				<div class="col-sm-21">
					<select class="form-control" name="captcha">
						<!-- BEGIN: captcha -->
						<option value="{CAPTCHA.index}"{CAPTCHA.selected}>{CAPTCHA.value}</option>
						<!-- END: captcha -->
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="text-center">
		<input type="submit" class="btn btn-primary loading" value="{LANG.save}" name="savesetting" />
	</div>
</form>
<!-- BEGIN: main -->