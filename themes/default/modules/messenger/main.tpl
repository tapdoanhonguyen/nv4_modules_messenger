<!-- BEGIN: main -->
<div class="mainpage">
	<div class="button-group m-bottom">
		<input name="check_all[]" type="checkbox" value="yes" onclick="nv_checkAll(this.form, 'idcheck[]', 'check_all[]',this.checked);">
		<a href="{URL_ADDTOPIC}" title="{LANG.addtopic}"><button class="btn btn-default"><em class="fa fa-plus-circle">&nbsp;</em>{LANG.addtopic}</button></a>
	</div>
	<hr />
	<form class="m-bottom">
		<!-- BEGIN: loop -->
		<div class="row">
			<div class="col-xs-2 col-sm-1 col-md-1">
				<input type="checkbox" onclick="nv_UncheckAll(this.form, 'idcheck[]', 'check_all[]', this.checked);" value="{DATA.id}" name="idcheck[]" class="post">
			</div>
			<div class="col-xs-22 col-sm-14 col-md-17 content">
				<div class="avatar pull-left" title="">
					<a href="#" class="pull-left" title="{DATA.username}"> <img src="{DATA.avata}" />
					</a>
				</div>
				<h3 <!-- BEGIN: unread -->class="unread"<!-- END: unread -->><a href="{DATA.link_view}" title="{DATA.title}">{DATA.title}</a></h3>
				<em class="fa fa-user">&nbsp;</em><small>{DATA.username}</small>&nbsp;&nbsp;&nbsp; 
				<em class="fa fa-clock-o">&nbsp;</em><small>{DATA.addtime}</small>
			</div>
			<div class="col-xs-24 col-sm-15 col-md-3 hidden-xs stats">
				<span class="show">{DATA.reply_count}</span> <small>{LANG.reply}</small>
			</div>
			<div class="col-xs-24 col-sm-15 col-md-3 hidden-xs stats">
				<span class="show">{DATA.view_count}</span> <small>{LANG.viewcount}</small>
			</div>
		</div>
		<!-- END: loop -->
	</form>
	
	<form class="m-bottom pull-left form-inline">
        <select class="form-control" id="action">
            <!-- BEGIN: action -->
            <option value="{ACTION.key}">{ACTION.value}</option>
            <!-- END: action -->
        </select>
        <button class="btn btn-primary" onclick="nv_list_action( $('#action').val(), '{LANG.error_empty_data}' ); return false;">{LANG.perform}</button>
    </form>
    
	<!-- BEGIN: page -->
	<div class="text-center">{PAGE}</div>
	<!-- END: page -->
</div>
<!-- END: main -->