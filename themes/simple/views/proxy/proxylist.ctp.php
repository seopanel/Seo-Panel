<?php 
echo showSectionHead($spTextPanel["Proxy Manager"]);
$searchFun = "scriptDoLoadPost('proxy.php', 'listform', 'content')"; 
?>
<form name="listform" id="listform" onsubmit="<?php echo $searchFun?>">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES)?>" onblur="<?php echo $searchFun?>"></td>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="status" onchange="<?php echo $searchFun?>">
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
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="actionbut"><?php echo $spText['button']['Show Records']?></a>
		</td>
	</tr>
</table>

<div id="topnewsbox">
	<a class="bold_link" href="http://www.squidproxies.com/billing/aff.php?aff=249" target="_blank">
		<?php echo $spTextProxy['click-to-get-proxy']; ?> &gt;&gt;
	</a>
</div>

<?php echo $pagingDiv?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="leftid"><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>		
		<td><?php echo $spText['label']['Proxy']?></td>
		<td><?php echo $spText['label']['Port']?></td>
		<td><?php echo $spText['label']['Authentication']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td class="right"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 7; 
	if(count($list) > 0){
		$catCount = count($list);
		foreach($list as $i => $listInfo){
			$class = ($i % 2) ? "blue_row" : "white_row";
            if($catCount == ($i + 1)){
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            }else{
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }
            $proxyLink = scriptAJAXLinkHref('proxy.php', 'content', "sec=edit&proxyId={$listInfo['id']}", "{$listInfo['proxy']}");
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td class="td_br_right"><?php echo $listInfo['id']?></td>
				<td class="td_br_right left"><?php echo $proxyLink?></td>
				<td class="td_br_right"><?php echo $listInfo['port']?></td>
				<td class="td_br_right"><?php echo $listInfo['proxy_auth'] ? $spText['common']["Yes"] : $spText['common']["No"]; ?></td>
				<td class="td_br_right"><?php echo $listInfo['status'] ? $spText['common']["Active"] : $spText['common']["Inactive"]; ?></td>
				<td class="<?php echo $rightBotClass?>" width="100px">
					<?php
						if($listInfo['status']){
							$statLabel = $spText['common']["Inactivate"];
						}else{
							$statLabel = $spText['common']["Activate"];
						} 
					?>
					<select name="action" id="action<?php echo $listInfo['id']?>" onchange="doAction('proxy.php', 'content', 'proxyId=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?><?php echo $urlParams?>', 'action<?php echo $listInfo['id']?>')">
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
	<tr class="listBot">
		<td class="left" colspan="<?php echo ($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
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
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;" class='left'>
         	<a onclick="scriptDoLoad('proxy.php', 'content', 'sec=new')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spTextPanel['New Proxy']?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $checkFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['button']["Check Status"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['common']["Activate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $inactFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['common']["Inactivate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['common']['Delete']?>
         	</a>&nbsp;&nbsp;         	
    	</td>
	</tr>
</table>
</form>
