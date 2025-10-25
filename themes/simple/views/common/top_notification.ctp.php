<?php if(empty($_COOKIE['hidenews']) && !SP_HOSTED_VERSION && empty($custSiteInfo['disable_news'])){ ?>
	<div class="row-fluid mt-2" style="width: 100%;">
		<div class="alert alert-warning alert-dismissible fade show"
			role="alert" id="myAlert" style="margin: 4px;">
			<button type="button" class="close" data-dismiss="alert"
				aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
			<span id="newsalert"></span>
		</div>
	</div>
	<script>
	    scriptDoLoad('<?php echo SP_WEBPATH?>/index.php?sec=news', 'newsalert');
	    $('#myAlert').on('closed.bs.alert', function () {
	    	hideNewsBox('newsalert', 'hidenews', '1')
	    });
	</script>
<?php }?>