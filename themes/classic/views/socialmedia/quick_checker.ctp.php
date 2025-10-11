<?php
echo showSectionHead($spTextTools['Quick Checker']);
$actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('$pageScriptPath', 'search_form', 'subcontent', '&sec=doQuickChecker')";
?>
<form id='search_form' onsubmit="<?php echo $actFun; ?>;return false;">
<table class="search" style="width: 60%">
	<tr>
		<th width="30%"><?php echo $spText['label']['Type']?>: </th>
		<td>
			<select name="type" id="sm_type" class="custom-select">
				<?php foreach($serviceList as $serviceName => $serviceInfo){?>
					<option value="<?php echo $serviceName?>"><?php echo $serviceInfo['label']?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th id="sm_url_label"><?php echo $spText['common']['Link']?>: </th>
		<td>
			<input type="text" value="" name="url" class="form-control" required="required"/>
			<?php
			$serviceSelName = !empty($post['type']) ? $post['type'] : "facebook";
			if (!empty($serviceList[$serviceSelName]['example'])) {
				?>
				<p><b>Eg:</b> <span id="ex_review_link"><?php echo $serviceList[$serviceSelName]['example']?></span></p>
				<?php
			}?>
			<div style="padding: 10px 6px; display: none;" id="sm_url_note">
				<a target="_blank" href="<?php echo SP_MAIN_SITE?>/blog/2020/07/how-do-i-find-the-linkedin-company-id/">
					<?php echo $spTextSMC['Click here to get LinkedIn Company Id']; ?> &gt;&gt;
				</a>
			</div>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'></div>

<script type="text/javascript">
var typeList = [];
<?php foreach ($serviceList as $serviceName => $serviceInfo) {?>
	typeList["<?php echo $serviceName?>"] = "<?php echo $serviceInfo['example']?>";
<?php }?>

$(function() {	
	$('#sm_type').change(function() {
		smType = $(this).val();
		$('#ex_review_link').html(typeList[smType]);
		
		if (smType == 'linkedin') {
			$('#sm_url_label').html("<?php echo $spTextSMC['Company Id']?>:");
			$('#sm_url_note').show();
		} else {
			$('#sm_url_label').html("<?php echo $spText['common']['Link']?>:");
			$('#sm_url_note').hide();
		}		
	});
	
});
</script>