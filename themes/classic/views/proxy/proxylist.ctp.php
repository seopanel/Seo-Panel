<?php
echo showSectionHead($spTextPanel["Proxy Manager"]);
$searchFun = "scriptDoLoadPost('proxy.php', 'listform', 'content')";
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>">
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES)?>" onblur="<?php echo $searchFun?>" class="form-control"></td>
		<th class="pl-4"><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="status" onchange="<?php echo $searchFun?>" class="custom-select">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php
				$inactCheck = $actCheck = "";
				if ($statVal == 'active') {
				    $actCheck = "selected";
				} elseif($statVal == 'inactive') {
				    $inactCheck = "selected";
				}
				?>
				<option value="active" <?php echo $actCheck?> ><?php echo $spText['common']["Active"]?></option>
				<option value="inactive" <?php echo $inactCheck?> ><?php echo $spText['common']["Inactive"]?></option>
			</select>
		</td>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="btn btn-secondary"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>

<div class="alert alert-info mb-3">
	<a class="btn btn-link" href="http://www.squidproxies.com/billing/aff.php?aff=249" target="_blank">
		<?php echo $spTextProxy['click-to-get-proxy']; ?> &gt;&gt;
	</a>
</div>

<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['label']['Proxy']?></td>
		<td><?php echo $spText['label']['Port']?></td>
		<td><?php echo $spText['label']['Authentication']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 7;
	if(count($list) > 0){
		foreach($list as $i => $listInfo){
            $proxyLink = scriptAJAXLinkHref('proxy.php', 'content', "sec=edit&proxyId={$listInfo['id']}", "{$listInfo['proxy']}");
			?>
			<tr>
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td><?php echo $listInfo['id']?></td>
				<td><?php echo $proxyLink?></td>
				<td><?php echo $listInfo['port']?></td>
				<td class="text-center">
					<?php echo $listInfo['proxy_auth'] ?
						"<span class='badge badge-success py-2 px-3 text-light'>{$spText['common']['Yes']}</span>" :
						"<span class='badge badge-secondary py-2 px-3 text-light'>{$spText['common']['No']}</span>"; ?>
				</td>
				<td class="text-center">
					<?php echo $listInfo['status'] ?
						"<span class='badge badge-success py-2 px-3 text-light'>{$spText['common']['Active']}</span>" :
						"<span class='badge badge-secondary py-2 px-3 text-light'>{$spText['common']['Inactive']}</span>"; ?>
				</td>
				<td>
					<?php
						if($listInfo['status']){
							$statLabel = $spText['common']["Inactivate"];
						}else{
							$statLabel = $spText['common']["Activate"];
						}
					?>
					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select" style="width: 180px;"
						onchange="doAction('proxy.php', 'content', 'proxyId=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?><?php echo $urlParams?>', 'action<?php echo $listInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="checkstatus"><?php echo $spText['button']['Check Status']?></option>
						<option value="<?php echo $statLabel?>"><?php echo $statLabel?></option>
						<option value="edit"><?php echo $spText['common']['Edit']?></option>
						<option value="delete"><?php echo $spText['common']['Delete']?></option>
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
    $actFun = $inactFun = $delFun = "alertDemoMsg()";
} else {
    $actFun = "confirmSubmit('proxy.php', 'listform', 'content', '&sec=activateall&pageno=$pageNo')";
    $inactFun = "confirmSubmit('proxy.php', 'listform', 'content', '&sec=inactivateall&pageno=$pageNo')";
    $delFun = "confirmSubmit('proxy.php', 'listform', 'content', '&sec=deleteall&pageno=$pageNo')";
    $checkFun = "confirmSubmit('proxy.php', 'listform', 'content', '&sec=checkall&pageno=$pageNo')";
}
?>
<table class="actionSec mt-2">
	<tr>
    	<td>
         	<a onclick="scriptDoLoad('proxy.php', 'content', 'sec=new')" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextPanel['New Proxy']?>
         	</a>
         	<a onclick="<?php echo $checkFun?>" href="javascript:void(0);" class="btn btn-info">
         		<?php echo $spText['button']["Check Status"]?>
         	</a>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-success">
         		<?php echo $spText['common']["Activate"]?>
         	</a>
         	<a onclick="<?php echo $inactFun?>" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['common']["Inactivate"]?>
         	</a>
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="btn btn-danger">
         		<?php echo $spText['common']['Delete']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
