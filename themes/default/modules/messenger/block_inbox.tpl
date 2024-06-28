<!-- BEGIN: main -->
<div class="inbox">
	<em class="fa fa-envelope-o">&nbsp;</em><a href="javascript:void(0);" id="inbox-trigger">{LANG.inbox}</a>
	<div id="inbox-content">
		<div class="inbox-title">Trò chuyện</div>
		<ul class="inbox-list">
			<!-- BEGIN: loop -->
			<li>
				<div class="avatar pull-left">
					<a href="#" title="{DATA.username}"> 
						<img src="{DATA.avata}" alt="{DATA.username}" />
					</a>
				</div>
				<h3 <!-- BEGIN: unread -->class="unread"<!-- END: unread -->><a href="{DATA.link_view}" title="{DATA.title}">{DATA.title0}</a></h3>
				<em class="fa fa-clock-o">&nbsp;</em><small>{DATA.addtime}</small>
				<em class="fa fa-user">&nbsp;</em><small>{DATA.username}</small>&nbsp;&nbsp;&nbsp; 
			</li>
			<!-- END: loop -->
		</ul>
		<div class="pull-left"><a href="{URL_VIEWALL}" title="{LANG.viewall}">{LANG.viewall}...</a></div>
		<div class="pull-right"><a href="{URL_TOPIC_ADD}" title="{LANG.addtopic}">{LANG.addtopic}</a></div>
		<div class="clearfix"></div>
	</div>
</div>
<script>
	$(document).ready(function() {
		$('#inbox-trigger').click(function() {
			$(this).next('#inbox-content').slideToggle();
			$(this).toggleClass('active');
		});
	});
</script>
<!-- END: main -->

<!-- BEGIN: config -->
<tr>
	<td>{LANG.numrow}</td>
	<td><input type="number" class="form-control w200" name="config_numrow" value="{DATA.numrow}" /></td>
</tr>
<tr>
	<td>{LANG.title_length}</td>
	<td><input type="number" class="form-control w200" name="config_title_length" value="{DATA.title_length}" /></td>
</tr>
<!-- END: config -->