<?php
echo showSectionHead($spTextPanel["Crawl Log Manager"]);
$searchFun = "scriptDoLoadPost('log.php', 'listform', 'content')";
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>">
<table class="search">
	<tr>
		<th><?php echo $spText['button']['Search']?>: </th>
		<td>
			<input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES)?>" onblur="<?php echo $searchFun?>" class="form-control">
		</td>
		<th class="pl-4"><?php echo $spText['label']['Report Type']?>: </th>
		<td>
			<select name="crawl_type" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				foreach ($crawlTypeList as $cInfo) {
					$selectType = ($cInfo['crawl_type'] == $crawlType) ? "selected" : "";
					?>
					<option value="<?php echo $cInfo['crawl_type']; ?>" <?php echo $selectType; ?> ><?php echo $cInfo['crawl_type']; ?></option>
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
		<th class="pl-4"><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<?php
			$this->data['onChange'] = $searchFun;
			$this->data['seNull'] = true;
			echo $this->render('searchengine/seselectbox', 'ajax');
			?>
		</td>
	</tr>
	<tr>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="status" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				$inactCheck = $actCheck = "";
				if ($statVal == 'success') {
				    $actCheck = "selected";
				} elseif($statVal == 'fail') {
				    $inactCheck = "selected";
				}
				?>
				<option value="success" <?php echo $actCheck?> ><?php echo $spText['label']["Success"]?></option>
				<option value="fail" <?php echo $inactCheck?> ><?php echo $spText['label']["Fail"]?></option>
			</select>
		</td>
		<th class="pl-4"><?php echo $spText['label']['Proxy']; ?>: </th>
		<td>
			<select name="proxy_id" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				foreach ($proxyList as $proxyInfo) {
					$selectType = ($proxyInfo['proxy_id'] == $proxyId) ? "selected" : "";
					?>
					<option value="<?php echo $proxyInfo['proxy_id']; ?>" <?php echo $selectType; ?> ><?php echo $proxyInfo['proxy'].":".$proxyInfo['port']; ?></option>
					<?php
				}
				?>
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
		<td><?php echo $spText['label']['Report Type']?></td>
		<td><?php echo $spText['label']['Reference']?></td>
		<td><?php echo $spText['label']['Subject']?></td>
		<td><?php echo $spText['common']['Details']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td><?php echo $spText['label']['Updated']?></td>
		<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 9;
	if (count($list) > 0) {
		foreach ($list as $i => $listInfo) {

            // if from popup
			if ($fromPopUp) {
            	$logLink = scriptAJAXLinkHref('log.php', 'content', "sec=crawl_log_details&id=".$listInfo['id'], $listInfo['id']);
            } else {
				$logLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log_details&id=".$listInfo['id'], $listInfo['id']);
			}

            // crawl log is for keyword
            if ($listInfo['crawl_type'] == 'keyword') {

				// if ref is is integer find keyword name
				if (!empty($listInfo['keyword'])) {
					$listInfo['ref_id'] = $listInfo['keyword'];
				}

				// find search engine info
				if (preg_match("/^\d+$/", $listInfo['subject'])) {
					$seCtrler = new SearchEngineController();
					$seInfo = $seCtrler->__getsearchEngineInfo($listInfo['subject']);
					$listInfo['subject'] = $seInfo['domain'];
				}

			}

			?>
			<tr>
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td><?php echo $logLink?></td>
				<td><?php echo $listInfo['crawl_type']?></td>
				<td><?php echo $listInfo['ref_id']?></td>
				<td><?php echo $listInfo['subject']?></td>
				<td><?php echo stripslashes($listInfo['log_message'])?></td>
				<td class="text-center">
					<?php
					if ($listInfo['crawl_status']) {
						echo "<span class='badge badge-success py-2 px-3 text-light'>{$spText['label']['Success']}</span>";
					} else {
						echo "<span class='badge badge-danger py-2 px-3 text-light'>{$spText['label']['Fail']}</span>";
					}
					?>
				</td>
				<td><?php echo $listInfo['crawl_time']?></td>
				<td>
					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select"
						onchange="doAction('log.php', 'content', 'id=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?><?php echo $urlParams?>', 'action<?php echo $listInfo['id']?>')" style="width: 180px;">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="delete_crawl_log"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
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
    $delFun = "confirmSubmit('log.php', 'listform', 'content', '&sec=delete_all_crawl_log&pageno=$pageNo')";
    $clearAllFun = "confirmLoad('log.php', 'content', '&sec=clear_all_log')";
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
