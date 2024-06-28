<!-- BEGIN: main -->
<div class="panel panel-default item" id="p{DATA.id}">
	<div class="panel-body">
		<div class="row">
			<div class="col-xs-24 col-sm-4 col-md-4 user-info text-center">
				<img src="{DATA.avata}" class="img-thumbnail m-bottom" /> <strong>{DATA.poster}</strong>
			</div>
			<div class="col-xs-24 col-sm-20 col-md-20 content">
				<div class="row">
					<div class="col-xs-24 col-sm-12 col-md-12">
						<span class="list-info"><a href="#p{DATA.id}"><em class="fa fa-clock-o">&nbsp;</em>{DATA.addtime}</a></span>
					</div>
					<div class="col-xs-24 col-sm-12 col-md-12 text-right btn-control">
						<!-- BEGIN: edit -->
						<button class="btn btn-default btn-xs" onclick="nv_edit_click({DATA.id}, {DATA.is_topic});" id="btn-edit-{DATA.id}">
							<em class="fa fa-edit">&nbsp;</em>{GLANG.edit}
						</button>
						<!-- END: edit -->
						<!-- BEGIN: delete -->
						<button class="btn btn-default btn-xs" onclick="nv_delete_reply({DATA.id}, {DATA.topicid}, {DATA.is_topic})">
							<em class="fa fa-trash-o">&nbsp;</em>{GLANG.delete}
						</button>
						<!-- END: delete -->
					</div>
				</div>
				<hr />
				<div id="replycontent-{DATA.id}">{DATA.content}</div>
			</div>
		</div>
	</div>
	<!-- BEGIN: files -->
	<div class="panel-footer">
		<!-- BEGIN: download -->
		<div class="files">
			<!-- BEGIN: loop -->
			<!-- BEGIN: image -->
			<div class="col-xs-6 col-sm-2 col-md-2 file">
				<img src="{FILES.thumb}" data-path="{FILES.path}" class="img-thumbnail img-responsive" />
			</div>
			<!-- END: image -->
			<!-- BEGIN: file -->
			<div class="col-xs-24 col-sm-12 col-md-12 file">
				<a href="" title="{FILES.filename}" onclick="nv_files_download({FILES.id})">
					<div class="image pull-left">
						<img src="{FILES.thumb}" alt="{FILES.filename}">
					</div> <span class="filename">{FILES.filename}</span> <em class="help-block"><small><strong>{LANG.files_size}: </strong>{FILES.size}&nbsp;&nbsp;&nbsp;<strong>{LANG.files_down}: </strong>{FILES.download_count}</small></em> <!-- BEGIN: description --> <em class="help-block"><small>{FILES.description}</small></em> <!-- END: description -->
				</a>
			</div>
			<!-- END: file -->
			<!-- END: loop -->
			<div class="clearfix"></div>
		</div>
		<!-- END: download -->
		<!-- BEGIN: login -->
		<div class="text-center pointer" onclick="loginForm(''); return false;">{LANG.files_note}</div>
		<!-- END: login -->
	</div>
	<!-- END: files -->
</div>
<!-- END: main -->