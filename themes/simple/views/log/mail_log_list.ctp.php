<?php 
echo showSectionHead($spTextPanel["Mail Log Manager"]);
$searchFun = "scriptDoLoadPost('log.php', 'listform', 'content', '&sec=mail')";
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>;return false;">
<table class="search">
	<tr>
		<th><?php echo $spText['button']['Search']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($post['keyword'], ENT_QUOTES)?>" onblur="<?php echo $searchFun?>" class="form-control"></td>
		<th class="pl-4"><?php echo $spText['common']['Category']?>: </th>
		<td>
			<select name="cat_type" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				foreach ($catTypeList as $cInfo) {
					$selectType = ($cInfo['mail_category'] == $post['cat_type']) ? "selected" : "";
					?>
					<option value="<?php echo $cInfo['mail_category']; ?>" <?php echo $selectType; ?> ><?php echo $cInfo['mail_category']; ?></option>
					<?php
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Period']?>:</th>
    	<td>
    		<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control" style="display: inline-block; width: 45%;"/>
    		<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control" style="display: inline-block; width: 45%;"/>
			<script>
			$(function() {
				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
		  	</script>
    	</td>
		<th class="pl-4"><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="status" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				$inactCheck = $actCheck = "";
				if ($post['status'] == 'success') {
				    $actCheck = "selected";
				} elseif($post['status'] == 'fail') {
				    $inactCheck = "selected";
				}
				?>
				<option value="success" <?php echo $actCheck?> ><?php echo $spText['label']["Success"]?></option>
				<option value="fail" <?php echo $inactCheck?> ><?php echo $spText['label']["Fail"]?></option>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>

<div class="mt-3 mb-2">
	<strong><?php echo $spTextPanel["Current Time"]?>:</strong> <?php echo date("Y-m-d H:i:s <b>T(P)</b>"); ?>
</div>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['label']['Subject']?></td>
		<td>TO</td>
		<td>CC</td>
		<td><?php echo $spText['label']['From']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td><?php echo $spText['common']['Category']?></td>
		<td><?php echo $spText['label']['Updated']?></td>
	</tr>
	<?php
	$colCount = 9; 
	if (count($list) > 0) {
		foreach ($list as $i => $listInfo) {
            $logLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=mail_log_details&id=".$listInfo['id'], $listInfo['id']);
            ?>
			<tr>
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td><?php echo $logLink?></td>
				<td><?php echo stripslashes($listInfo['subject'])?></td>
				<td><?php echo $listInfo['to_address']?></td>
				<td><?php echo $listInfo['cc_address']?></td>
				<td><?php echo $listInfo['from_address']?></td>
				<td class="text-center">
					<?php echo showStatusBadge($listInfo['status'], "successfail");?>
				</td>
				<td><?php echo $listInfo['mail_category']?></td>
				<td><?php echo $listInfo['log_time']?></td>
			</tr>
			<?php
		}
	}else{	 
		echo showNoRecordsList($colCount-2);		
	} 
	?>
</table>
<?php
if (SP_DEMO) {
    $clearAllFun = $delFun = "alertDemoMsg()";
} else {
    $delFun = "confirmSubmit('log.php', 'listform', 'content', '&sec=delete_mail_log&pageno=$pageNo')";
    $clearAllFun = "confirmLoad('log.php', 'content', '&sec=clear_all_mail_log')";
}   
?>
<table class="actionSec mt-2">
	<tr>
    	<td>
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="btn btn-danger">
         		<?php echo $spText['common']['Delete']?>
         	</a>&nbsp;&nbsp;
         	<?php if (empty($fromPopUp)) {?>
	         	<a onclick="<?php echo $clearAllFun?>" href="javascript:void(0);" class="btn btn-warning">
	         		<?php echo $spTextLog['Clear All Logs']?>
	         	</a>
	         <?php }?>
    	</td>
	</tr>
</table>
</form>
