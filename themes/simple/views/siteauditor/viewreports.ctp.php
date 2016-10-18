<?php echo showSectionHead($spTextTools['Auditor Reports']); ?>
<?php
if(empty($projectId)) {
	showErrorMsg($spTextSA['No active projects found'].'!');
} 
$submitJsFunc = "scriptDoLoadPost('siteauditor.php', 'search_form', 'subcontent', '&sec=showreport')";
?>
<form id='search_form' onsubmit="<?php echo $submitJsFunc; ?>; return false;">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>				
		<th><?php echo $spText['label']['Project']?>: </th>
		<td>
			<select id="project_id" name="project_id" onchange="<?php echo $submitJsFunc?>" style="width: 180px;">
				<?php foreach($projectList as $list) {?>
					<?php if($list['id'] == $projectId) {?>
						<option value="<?php echo $list['id']?>" selected="selected"><?php echo $list['name']?></option>
					<?php } else {?>
						<option value="<?php echo $list['id']?>"><?php echo $list['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>						
		<th><?php echo $spText['label']['Report Type']?>: </th>
		<td width="200px;">
			<select name="report_type" id="report_type" onchange="<?php echo $submitJsFunc?>">
				<?php foreach($reportTypes as $type => $label) {
				    $selected = ($type == $repType) ? "selected" : "";
				    ?>
					<option value="<?php echo $type?>" <?php echo $selected?>><?php echo $label?></option>
				<?php }?>
			</select>
		</td>						
		<th><?php echo $spTextSA['Crawled']?>: </th>
		<td>			
			<select name="crawled" id="crawled" onchange="<?php echo $submitJsFunc?>">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<option value="0"><?php echo $spText['common']['No']?></option>
				<option value="1" selected><?php echo $spText['common']['Yes']?></option>
			</select>
		</td>
	</tr>
	<tr>						
		<th><?php echo $spTextSA['Page Link']?>: </th>
		<td style="width: 50px;">
			<input type="text" name="page_url" value="" onblur="<?php echo $submitJsFunc?>" style="width: 180px;">
		</td>			
		<th><?php echo $spText['common']['MOZ Rank']?>: </th>
		<td>
			<select name="pagerank" onchange="<?php echo $submitJsFunc?>">
				<option value="-1">-- <?php echo $spText['common']['Select']?> --</option>
				<?php for($i=0;$i<=10;$i++) {?>
					<option value="<?php echo $i?>">PR<?php echo $i?></option>
				<?php }?>
			</select>			
		</td>
		<td colspan="2">
			<a href="javascript:void(0);" onclick="<?php echo $submitJsFunc?>" class="actionbut"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<script><?php echo $submitJsFunc?></script>
</div>