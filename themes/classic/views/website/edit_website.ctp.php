<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$headText = ($editAction == 'update') ? $spTextPanel['New Website'] : $spTextWeb['Edit Website'];
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($headText); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#webEditHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="webEditHelpModal" tabindex="-1" role="dialog" aria-labelledby="webEditHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-globe"></i></div>
				<h5 class="modal-title" id="webEditHelpModalLabel">About <?php echo htmlspecialchars($headText)?></h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Add a new website (or edit an existing one) with its name, URL and ownership details. This is the base record every other tool - keywords, backlinks, Site Auditor, AI Visibility - attaches to.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php
if (!empty($warningMsg)) {
    showWarningMsg($warningMsg, FALSE);
}

if(!empty($msg)){
	?>
	<p class="dirmsg">
		<font class="success"><?php echo $msg?></font>
	</p>
	<?php 
}

if(!empty($validationMsg)){
	?>
	<p class="dirmsg">
		<font class="error"><?php echo $validationMsg?></font>
	</p>
	<?php 
}

$post['url'] = empty($post['url']) ? "https://" : $post['url'];
?>
<form id="edit_website">
<input type="hidden" name="sec" value="<?php echo $editAction?>"/>
<?php if ($editAction == 'update') {?>	
	<input type="hidden" name="id" value="<?php echo $post['id']?>"/>
<?php }?>
<table class="list">
	<tr class="listHead">
		<td class="left" width='30%'><?php echo $spTextPanel['New Website']?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<?php if(!empty($isAdmin)){ ?>
		<tr class="blue_row">
			<td class="td_left_col"><label for="webuserid"><?php echo $spText['common']['User']?>:</label></td>
			<td class="td_right_col">
				<select name="userid" id="webuserid" class="custom-select">
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] == $userSelected){?>
							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
						<?php }else{?>
							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
						<?php }?>
					<?php }?>
				</select>
			</td>
		</tr>
	<?php }?>
	<tr class="white_row">
		<td class="td_left_col"><label for="webname"><?php echo $spText['common']['Name']?>:</label></td>
		<td class="td_right_col">
			<input type="text" id="webname" name="name" value="<?php echo htmlspecialchars($post['name'])?>" class="form-control"><?php echo $errMsg['name']?>
		</td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="weburl"><?php echo $spText['common']['Url']?>:</label></td>
		<td class="td_right_col">
			<div class="row">
				<div class="col-sm-9">
					<input type="text" id='weburl' name="url" value="<?php echo htmlspecialchars($post['url'])?>" class="form-control">
				</div>
				<div class="col-sm-3">
    				<a  class="btn btn-info" href="javascript:void(0);" onclick="crawlMetaData('websites.php?sec=crawlmeta', 'crawlstats')">
    					<?php echo $spText['common']['Crawl Meta Data']?>
    				</a>
				</div>
			</div>
			<?php echo $errMsg['url']?>
			<div id="crawlstats" style="padding-right:40px;" class="mt-2 float-right mt-2"></div>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="webtitle"><?php echo $spText['label']['Title']?>:</label></td>
		<td class="td_right_col"><input type="text" id="webtitle" name="title" value="<?php echo $post['title']?>" class="form-control"></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col"><label for="webdescription"><?php echo $spText['label']['Description']?>:</label></td>
		<td class="td_right_col"><textarea name="description" id="webdescription" class="form-control"><?php echo $post['description']?></textarea><?php echo $errMsg['description']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="webkeywords"><?php echo $spText['label']['Keywords']?>:</label></td>
		<td class="td_right_col"><textarea name="keywords" id="webkeywords" class="form-control"><?php echo $post['keywords']?></textarea><?php echo $errMsg['keywords']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col"><label for="analytics_view_id"><?php echo $spTextWeb['Google Analytics Property']?>:</label></td>
		<td class="td_right_col">
			<div class="row">
            	<div class="col-sm-8">    					
					<?php echo createSelectBoxFromList($propertyList, "analytics_view_id", $post['analytics_view_id'], $spText['common']['Select']);?>
    	        </div>
    	        <div class="col-sm-4">
    	        	<a href="javascript:void(0);" class="btn btn-info" id="connection_refresh">
    	        		<?php echo $spTextWeb['Sync Google Analytics Properties']?>  
	        		</a>
    	        </div>
	        </div>
	        <div id="connection_refresh_loading" class="float-right mt-2" style="display: none; width: 50%;">
				<div id="loading_longthin"></div>
	        </div>
        	<div id="connection_refresh_content" style="margin: 16px 6px;display: none;" class="fw-bold float-right"></div>
        	<div class="clearfix"></div>
        	<div id="connection_refresh_debug_toggle" style="display:none;text-align:right;margin:4px 6px;">
        		<a href="javascript:void(0);" class="text-muted" style="font-size:12px;text-decoration:underline;">
        			<span id="connection_refresh_debug_toggle_label"><?php echo $spTextWeb['Show details'] ?? 'Show details'?></span>
        		</a>
        	</div>
        	<pre id="connection_refresh_debug" style="display:none;background:#1e1e1e;color:#d4d4d4;font-family:Consolas,Monaco,'Courier New',monospace;font-size:12px;line-height:1.5;padding:12px 14px;margin:6px;border-radius:6px;max-height:280px;overflow-y:auto;white-space:pre-wrap;word-break:break-word;"></pre>
		</td>
	</tr>
	<?php if (!empty($post['id'])) {?>
	<tr class="blue_row">
		<td class="td_left_col"><label><?php echo $spTextWeb['Client Report Link']?>:</label></td>
		<td class="td_right_col">
			<div id="report_share_link_display" style="<?php echo empty($reportShareLink) ? 'display:none;' : ''?>">
				<div class="input-group">
					<input type="text" id="report_share_link_url" class="form-control" readonly value="<?php echo htmlspecialchars($reportShareLink)?>">
					<div class="input-group-append">
						<a href="javascript:void(0);" id="report_share_link_copy" class="btn btn-info"><?php echo $spTextWeb['Copy']?></a>
						<a href="javascript:void(0);" id="report_share_link_revoke" class="btn btn-warning"><?php echo $spTextWeb['Revoke']?></a>
					</div>
				</div>
			</div>
			<a href="javascript:void(0);" id="report_share_link_generate" class="btn btn-info" style="<?php echo empty($reportShareLink) ? '' : 'display:none;'?>">
				<?php echo $spTextWeb['Generate link']?>
			</a>
			<div id="report_share_link_msg" class="mt-2"></div>
			<small class="text-muted d-block mt-1"><?php echo $spTextWeb['Client Report Link note']?></small>
		</td>
	</tr>
	<?php }?>
</table>
<table class="actionSec float-right mt-2">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('websites.php', 'content')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>&nbsp;
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('websites.php', 'edit_website', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>

<script type="text/javascript">
$(function() {

	function spRenderSyncDebug(lines) {
		if (!lines || !lines.length) {
			$("#connection_refresh_debug_toggle").hide();
			$("#connection_refresh_debug").hide().text('');
			return;
		}
		var $console = $("#connection_refresh_debug");
		$console.text(lines.join("\n")).show();
		$console.scrollTop($console[0].scrollHeight);
		$("#connection_refresh_debug_toggle_label").text('<?php echo $spTextWeb['Hide details'] ?? 'Hide details'?>');
		$("#connection_refresh_debug_toggle").show();
	}

	$("#connection_refresh_debug_toggle").on('click', 'a', function() {
		var $console = $("#connection_refresh_debug");
		var isHidden = $console.is(':hidden');
		$console.toggle(isHidden);
		$console.scrollTop($console[0] ? $console[0].scrollHeight : 0);
		$("#connection_refresh_debug_toggle_label").text(
			isHidden ? '<?php echo $spTextWeb['Hide details'] ?? 'Hide details'?>' : '<?php echo $spTextWeb['Show details'] ?? 'Show details'?>'
		);
	});

    $("#connection_refresh").click(function() {
    	$("#connection_refresh_content").show();
    	spRenderSyncDebug([]);
        $.ajax({
            url: '<?php echo SP_WEBPATH?>/websites.php?sec=fetchgoogleanalytics',
            type: "GET",
  			dataType: "json",
            success: function(response) {
            	$("#connection_refresh_content").show();
            	spRenderSyncDebug(response.debug);
                if(response.status) {
                	var connectionList = response.data;
                	$('#analytics_view_id').empty();
					$('#analytics_view_id').append($('<option>', {
                        value: '',
                        text: '-- <?php echo $spText['common']['Select']?> --',
                    }));
                	$.each(connectionList, function(propertyId, propertyName) {
                      $('#analytics_view_id').append($('<option>', {
                        value: propertyId,
                        text: propertyName,
                      }));
                    });

                	$("#connection_refresh_content").html('<span class="text-success form-success"><i class="ri-checkbox-circle-line"></i>Google Analytics Properties Synced.</span>');
                } else {
                	$("#connection_refresh_content").html('<span class="text-danger form-error"><i class="ri-error-warning-line"></i>'+ response.msg +'</span>');
                }
            },
            beforeSend: function() {
				$('#connection_refresh_loading').show();
				$("#connection_refresh_content").html('');
			},
            error: function(jqXHR, textStatus, errorThrown) {
            	$("#connection_refresh_content").show();
                var errMsg = "API Error: " + errorThrown;
				$("#connection_refresh_content").html('<span class="text-danger form-error"><i class="ri-error-warning-line"></i>' + errMsg + '</span>');

				// Best-effort: an uncaught server error still often carries a JSON
				// body (jQuery just refuses to route it to success() over a non-2xx
				// status). Fall back to the raw response text so a real 500 is not
				// a dead end.
				var debugLines = [];
				try {
					var parsed = jqXHR.responseJSON || JSON.parse(jqXHR.responseText);
					debugLines = parsed && parsed.debug ? parsed.debug : [];
				} catch (e) {
					if (jqXHR.responseText) {
						debugLines = [jqXHR.responseText.substring(0, 4000)];
					}
				}
				spRenderSyncDebug(debugLines);
            },
            complete: function() {
				$('#connection_refresh_loading').hide();
			}
        });
    });

	<?php if (!empty($post['id'])) {?>
	var reportShareWebsiteId = <?php echo intval($post['id'])?>;

	function spShowReportShareMsg(text, isError) {
		$("#report_share_link_msg").html(
			'<span class="' + (isError ? 'text-danger' : 'text-success') + '">' + text + '</span>'
		);
	}

	$("#report_share_link_generate").click(function() {
		$.ajax({
			url: '<?php echo SP_WEBPATH?>/websites.php',
			type: "POST",
			dataType: "json",
			data: {sec: 'generatereportsharelink', websiteId: reportShareWebsiteId},
			success: function(response) {
				if (response.status) {
					$("#report_share_link_url").val(response.link);
					$("#report_share_link_display").show();
					$("#report_share_link_generate").hide();
				} else {
					spShowReportShareMsg(response.msg, true);
				}
			}
		});
	});

	$("#report_share_link_revoke").click(function() {
		if (!confirm('<?php echo $spTextWeb['Revoke this link? Anyone using it will lose access immediately.']?>')) {
			return;
		}
		$.ajax({
			url: '<?php echo SP_WEBPATH?>/websites.php',
			type: "POST",
			dataType: "json",
			data: {sec: 'revokereportsharelink', websiteId: reportShareWebsiteId},
			success: function(response) {
				if (response.status) {
					$("#report_share_link_display").hide();
					$("#report_share_link_generate").show();
				} else {
					spShowReportShareMsg(response.msg, true);
				}
			}
		});
	});

	$("#report_share_link_copy").click(function() {
		var $input = $("#report_share_link_url");
		$input.get(0).select();
		try {
			document.execCommand('copy');
			spShowReportShareMsg('<?php echo $spTextWeb['Link copied']?>', false);
		} catch (e) {}
	});
	<?php }?>
});
</script>