<?php echo showSectionHead($spTextTools['Auditor Reports']); ?>
<?php
if(empty($projectId)) {
	showErrorMsg($spTextSA['No active projects found'].'!');
}
$submitJsFunc = "scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport')";
?>
<!-- Report Type Tabs -->
<style>
.nav-tabs-sm .nav-link {
	padding: 10px 20px !important;
	font-size: 14px !important;
}
</style>

<form id='search_form' onsubmit="<?php echo $submitJsFunc; ?>; return false;">
<input type="hidden" name="report_type" value="<?php echo $repType?>">
<ul class="nav nav-tabs nav-tabs-sm" role="tablist" style="margin-bottom: 15px;">
	<?php foreach($reportTypes as $type => $label){?>
		<li role="presentation" class="nav-item">
			<a href="javascript:void(0);"
			   class="nav-link <?php echo ($repType == $type) ? 'active' : ''?>"
			   onclick="$('.nav-tabs .nav-link').removeClass('active'); $(this).addClass('active'); $('input[name=report_type]').val('<?php echo $type?>'); <?php echo $submitJsFunc?>">
				<?php echo $label?>
			</a>
		</li>
	<?php }?>
</ul>

<table class="search" style="width: 90%;">
	<tr>
		<th><?php echo $spText['label']['Project']?>: </th>
		<td>
			<select id="project_id" name="project_id" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<?php foreach($projectList as $list) {?>
					<?php if($list['id'] == $projectId) {?>
						<option value="<?php echo $list['id']?>" selected="selected"><?php echo $list['name']?></option>
					<?php } else {?>
						<option value="<?php echo $list['id']?>"><?php echo $list['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<th class="pl-4"><?php echo $spTextSA['Crawled']?>: </th>
		<td>
			<select name="crawled" id="crawled" onchange="<?php echo $submitJsFunc?>" class="custom-select">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" selected><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
		<th class="pl-4"><?php echo $spTextSA['Page Link']?>: </th>
		<td>
			<input type="text" name="page_url" value="" onblur="<?php echo $submitJsFunc?>" class="form-control">
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitJsFunc?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<script><?php echo $submitJsFunc?></script>
</div>