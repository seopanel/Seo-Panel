<?php
echo showSectionHead($spTextTools['Keywords Manager']);
$searchFun = "scriptDoLoadPost('keywords.php', 'listform', 'content')";
?>
<form name="listform" id="listform">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td><input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES)?>" onblur="<?php echo $searchFun?>"></td>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<select name="website_id" id="website_id" onchange="<?php echo $searchFun?>">
				<option value="">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($websiteList as $websiteInfo){?>
					<?php if($websiteInfo['id'] == $websiteId){?>
						<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
					<?php }else{?>
						<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
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
			<a href="javascript:void(0);" onclick="<?php echo $searchFun?>" class="actionbut"><?php echo $spText['button']['Search']?></a>
		</td>
	</tr>
</table>
<?php echo $pagingDiv?>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">		
		<td class="leftid"><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Name']?></td>
		<td><?php echo $spText['common']['Website']?></td>
		<td><?php echo $spText['common']['Country']?></td>
		<td><?php echo $spText['common']['lang']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td class="right"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 8; 
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
            $keywordLink = scriptAJAXLinkHref('keywords.php', 'content', "sec=edit&keywordId={$listInfo['id']}", "{$listInfo['name']}")
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td class="td_br_right"><?php echo $listInfo['id']?></td>
				<td class="td_br_right left"><?php echo $keywordLink?></td>
				<td class="td_br_right left"><?php echo $listInfo['website']?></td>
				<td class="td_br_right"><?php echo empty($listInfo['country_name']) ? $spText['common']["All"] : $listInfo['country_name']; ?></td>
				<td class="td_br_right"><?php echo empty($listInfo['lang_name']) ? $spText['common']["All"] : $listInfo['lang_name']; ?></td>
				<td class="td_br_right"><?php echo $listInfo['status'] ? $spText['common']["Active"] : $spText['common']["Inactive"];	?></td>
				<td class="<?php echo $rightBotClass?>" width="100px">
					<?php
						if($listInfo['status']){
							$statVal = "Inactivate";
							$statLabel = $spText['common']["Inactivate"];
						}else{
							$statVal = "Activate";
							$statLabel = $spText['common']["Activate"];
						} 
					?>
					<select name="action" id="action<?php echo $listInfo['id']?>" onchange="doAction('keywords.php', 'content', 'keywordId=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>&website_id=<?php echo $websiteId?>', 'action<?php echo $listInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<?php if($listInfo['webstatus'] && $listInfo['status']){?>
							<option value="reports"><?php echo $spText['common']['Reports']?></option>
						<?php }?>
						<option value="<?php echo $statVal?>"><?php echo $statLabel?></option>
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
    $actFun = "confirmSubmit('keywords.php', 'listform', 'content', '&sec=activateall&pageno=$pageNo')";
    $inactFun = "confirmSubmit('keywords.php', 'listform', 'content', '&sec=inactivateall&pageno=$pageNo')";
    $delFun = "confirmSubmit('keywords.php', 'listform', 'content', '&sec=deleteall&pageno=$pageNo')";
}   
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;">
         	<a onclick="scriptDoLoad('keywords.php', 'content', 'sec=new&website_id=<?php echo $websiteId?>')" href="javascript:void(0);" class="actionbut">
         		<?php echo $spTextKeyword['New Keyword']?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['common']["Activate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $inactFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['common']["Inactivate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="actionbut">
         		<?php echo $spText['common']['Delete']?>
         	</a>
    	</td>
	</tr>
</table>
</form>