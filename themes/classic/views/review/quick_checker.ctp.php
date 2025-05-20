<?php 
echo showSectionHead($spTextTools['Quick Checker']);
$actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('$pageScriptPath', 'search_form', 'subcontent', '&sec=doQuickChecker')";
?>
<form id='search_form' onsubmit="<?php echo $actFun; ?>;return false;">
<table id="cust_tab">
	<tr class="form_head">
		<th width="30%"><?php echo $spTextTools['Quick Checker'];?></th>
		<th>&nbsp;</th>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['label']['Type']?>: </td>
		<td>
			<select name="type" id="review_link_type">
				<?php foreach($serviceList as $serviceName => $serviceInfo){?>
					<option value="<?php echo $serviceName?>"><?php echo $serviceInfo['label']?></option>
				<?php }?>
			</select>
		</td>
	</tr>
	<tr class="form_data">
		<td><?php echo $spText['common']['Link']?>: </td>		
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
	<tr class="form_data">
		<td style="border-right: 0px;">&nbsp;</td>
		<td style="border-left: 0px;">
			<br>
			<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="actionbut"><?php echo $spText['button']['Proceed']?></a>
			<br><br>
		</td>
	</tr>
</table>
</form>
<br><br>
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