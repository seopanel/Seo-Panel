<form name="listform" id="listform" onsubmit="return false;">
<?php echo showSectionHead($spTextPanel['Search Engine Manager']); ?>
<table class="search">
	<?php $submitLink = "scriptDoLoadPost('searchengine.php', 'listform', 'content')";?>
	<tr>
		<th><?php echo $spText['common']['Search Engine']?>: </th>
		<td>
			<input type="text" name="se_name" value="<?php echo htmlentities($info['se_name'], ENT_QUOTES)?>" class="form-control">
		</td>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="stscheck" class="form-select" onchange="<?php echo $submitLink?>">
				<?php foreach($statusList as $key => $val){?>
					<?php if($info['stscheck'] == $val){?>
						<option value="<?php echo $val?>" selected><?php echo $key?></option>
					<?php }else{?>
						<option value="<?php echo $val?>"><?php echo $key?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<td>
			<a href="javascript:void(0);" onclick="<?php echo $submitLink; ?>" class="actionbut">
				<?php echo $spText['button']['Search']?>
			</a>
		</td>
	</tr>
</table>
<br>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td class="leftid"><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Name']?></td>
		<td><?php echo $spTextSE['no_of_results_page']?></td>
		<td><?php echo $spTextSE['max_results']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td class="right"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 6; 
	if(count($seList) > 0) {
	    foreach($seList as $seInfo) {
	        $seLink = scriptAJAXLinkHref('searchengine.php', 'content', "sec=edit&seId={$seInfo['id']}", "{$seInfo['domain']}")
			?>
			<tr>				
				<td><input type="checkbox" name="ids[]" value="<?php echo $seInfo['id']?>"></td>
				<td class="fs-13 text-start"><?php echo $seLink?></td>
				<td><?php echo $seInfo['no_of_results_page']?></td>
				<td><?php echo $seInfo['max_results']?></td>
				<td>
					<?php echo showStatusBadge($seInfo['status']);?>
				</td>
				<td class="<?php echo $rightBotClass?>" width="100px">
					<?php
					if($seInfo['status']) {
						$statVal = "Inactivate";
						$statLabel = $spText['common']["Inactivate"];
					} else {
						$statVal = "Activate";
						$statLabel = $spText['common']["Activate"];
					}
					?>
					<select name="action" id="action<?php echo $seInfo['id']?>" onchange="doAction('<?php echo $pageScriptPath?>', 'content', 'seId=<?php echo $seInfo['id']?>&pageno=<?php echo $pageNo?>', 'action<?php echo $seInfo['id']?>')">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="<?php echo $statVal?>"><?php echo $statLabel?></option>
						<option value="edit"><?php echo $spText['common']['Edit']?></option>
						<option value="delete"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
			</tr>
			<?php
		}
	} else {	 
		echo showNoRecordsList($colCount-2);		
	} 
	?>
</table>
<?php
if (SP_DEMO) {
    $actFun = $inactFun = $delFun = "alertDemoMsg()";
} else {
    $actFun = "confirmSubmit('searchengine.php', 'listform', 'content', '&sec=activateall&pageno=$pageNo')";
    $inactFun = "confirmSubmit('searchengine.php', 'listform', 'content', '&sec=inactivateall&pageno=$pageNo')";
    $delFun = "confirmSubmit('searchengine.php', 'listform', 'content', '&sec=deleteall&pageno=$pageNo')";
}   
?>
<table class="actionSec">
	<tr>
    	<td style="padding-top: 6px;">
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