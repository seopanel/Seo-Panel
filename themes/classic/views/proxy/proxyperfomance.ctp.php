<?php
echo showSectionHead($spTextPanel["Proxy Perfomance"]);
$searchFun = "scriptDoLoadPost('proxy.php', 'listform', 'content')";
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>">
<input type="hidden" name="sec" value="perfomance">
<table class="search">
	<tr>
		<th><?php echo $spText['button']['Search']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES)?>" onblur="<?php echo $searchFun?>" class="form-control"></td>
		<th class="pl-4"><?php echo $spText['common']['Period']?>:</th>
    	<td>
    		<input type="text" value="<?php echo $fromTime?>" name="from_time" class="form-control"/>
    		<input type="text" value="<?php echo $toTime?>" name="to_time" class="form-control mt-1"/>
			<script>
			$(function() {
				$( "input[name='from_time'], input[name='to_time']").datepicker({dateFormat: "yy-mm-dd"});
			});
		  	</script>
    	</td>
		<th class="pl-4"><?php echo $spText['label']['Order By']?>: </th>
		<td>
			<select name="order_by" onchange="<?php echo $searchFun?>" class="custom-select">
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
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="btn btn-secondary"><?php echo $spText['button']['Search']?></a>
		</td>
	</tr>
</table>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['label']['Proxy']?></td>
		<td><?php echo $spTextProxy['Request Count']?></td>
		<td><?php echo $spText['label']['Success']?></td>
		<td><?php echo $spText['label']['Fail']?></td>
	</tr>
	<?php
	$colCount = 6;
	if (count($list) > 0) {
		foreach ($list as $i => $listInfo) {

            $logLink = scriptAJAXLinkHrefDialog('proxy.php', 'content', "sec=edit&proxyId=".$listInfo['proxy_id'], $listInfo['proxy'].":".$listInfo['port']);
            $countLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=&proxy_id=".$listInfo['proxy_id'], $listInfo['count'], '', 'OnClick', 1000);
			$successLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=success&proxy_id=".$listInfo['proxy_id'], $listInfo['success'], '', 'OnClick', 1000);
			$failLink = scriptAJAXLinkHrefDialog('log.php', 'content', "sec=crawl_log"."$urlParams&status=fail&proxy_id=".$listInfo['proxy_id'], $listInfo['fail'], '', 'OnClick', 1000);
            ?>
			<tr>
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td><?php echo $listInfo['proxy_id']?></td>
				<td><?php echo $logLink?></td>
				<td><?php echo $countLink?></td>
				<td><?php echo $successLink?></td>
				<td><?php echo $failLink?></td>
			</tr>
			<?php
		}
	} else {
		echo showNoRecordsList($colCount-2);
	}
	?>
</table>
</form>
