<?php
echo showSectionHead($spTextTools['Quick Checker']);
$actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('$pageScriptPath', 'search_form', 'subcontent', '&sec=doQuickChecker')";
?>
<form id='search_form' onsubmit="<?php echo $actFun; ?>;return false;">
<table class="search" style="width: 60%">
	<tr>
		<th width="30%"><?php echo $spText['label']['Type']?>: </th>
		<td>
			<select name="type" id="review_link_type" class="custom-select">
				<?php foreach($serviceList as $serviceName => $serviceInfo){?>
					<option value="<?php echo $serviceName?>"><?php echo $serviceInfo['label']?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Link']?>: </th>
		<td>
			<input type="url" value="" name="url" class="form-control" required="required"/>
			<?php
			$serviceSelName = !empty($post['type']) ? $post['type'] : "google";
			if (!empty($serviceList[$serviceSelName]['example'])) {
				?>
				<p><b>Eg:</b> <span id="ex_review_link"><?php echo implode(', ', $serviceList[$serviceSelName]['example'])?></span></p>
				<?php
			}?>
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
	typeList["<?php echo $serviceName?>"] = "<?php echo $serviceInfo['example'][0]?>";
<?php }?>

(function () {
	$('#review_link_type').change(function() {
		service = $(this).val();
		$('#ex_review_link').html(typeList[service]);
	});
})();
</script>