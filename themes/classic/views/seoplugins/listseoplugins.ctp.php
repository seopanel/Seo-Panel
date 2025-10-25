<?php $submitLink = "scriptDoLoadPost('seo-plugins-manager.php', 'listform', 'content')";?>
<form name="listform" id="listform" onsubmit="<?php echo $submitLink?>;return false;">
<?php echo showSectionHead($spTextPanel['Seo Plugins Manager']); ?>
<?php
if(!empty($msg)){
	echo $error ? showErrorMsg($msg, false) : showSuccessMsg($msg, false);
}
?>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Keyword']?>: </th>
		<td>
			<input type="text" name="keyword" value="<?php echo htmlentities($info['keyword'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>" class="form-control">
		</td>
		<th class="pl-4"><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="stscheck" onchange="<?php echo $submitLink?>" class="custom-select">
				<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
				<?php foreach($statusList as $key => $val){?>
					<?php if(isset($info['stscheck']) && $info['stscheck'] === $val){?>
						<option value="<?php echo $val?>" selected><?php echo $key?></option>
					<?php }else{?>
						<option value="<?php echo $val?>"><?php echo $key?></option>
					<?php }?>
				<?php }?>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $submitLink; ?>" class="btn btn-secondary">
				<?php echo $spText['button']['Search']?>
			</a>
		</td>
	</tr>
</table>
</form>

<?php echo $pagingDiv?>
<table class="list">
	<thead>
		<tr class="listHead">
			<td><?php echo $spText['label']['Plugin']?></td>
			<td><?php echo $spText['common']['Priority']?></td>
			<td><?php echo $spText['label']['Author']?></td>
			<td><?php echo $spText['common']['Website']?></td>
			<td><?php echo $spText['common']['Status']?></td>
			<td><?php echo $spText['label']['Installation']?></td>
			<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
		</tr>
	</thead>
	<tbody>
		<?php
		$colCount = 7;
		if (!empty($list)) {
    		foreach($list as $listInfo) {
    			$statLabel = $listInfo['status'] ? $spText['common']["Active"] : $spText['common']["Inactive"];
    			$btnClass = $listInfo['status'] ? "btn btn-success" : "btn btn-danger";
                $activateLink = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoad('seo-plugins-manager.php', 'content', 'sec=changestatus&seoplugin_id={$listInfo['id']}&status={$listInfo['status']}&pageno=$pageNo')";
    			?>
    			<tr>
    				<td>
    					<a href="javascript:void(0);" onclick="scriptDoLoad('seo-plugins-manager.php?sec=listinfo&pid=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>', 'content')"><?php echo $listInfo['label']?> <?php echo $listInfo['version']?></a>
    				</td>
    				<td><?php echo $listInfo['priority']?></td>
    				<td><?php echo $listInfo['author']?></td>
    				<td><a href="<?php echo $listInfo['website']?>" target="_blank"><?php echo $listInfo['website']?></a></td>
    				<td class="text-center">
    					<a href="javascript:void(0)" onclick="<?php echo $activateLink?>" class="<?php echo $btnClass?>"><?php echo $statLabel?></a>
    				</td>
    				<td class="text-center">
    					<?php echo $listInfo['installed'] ? "<span class='badge badge-success py-2 px-3 text-light'>Success</span>" : "<span class='badge badge-danger py-2 px-3 text-light'>Failed</span>"; ?>
    				</td>
    				<td>
    					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select"
    						onchange="doAction('seo-plugins-manager.php?pageno=<?php echo $pageNo?>', 'content', 'pid=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')" style="width: 180px;">
    						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
    						<option value="edit"><?php echo $spText['common']['Edit']?></option>
    						<option value="upgrade"><?php echo $spText['label']['Upgrade']?></option>
    						<option value="reinstall"><?php echo $spText['label']['Re-install']?></option>
    					</select>
    				</td>
    			</tr>
    			<?php
    		}
		} else {
		    echo showNoRecordsList($colCount-2);
		}
		?>
	</tbody>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a href="<?php echo SP_PLUGINSITE?>" class="btn btn-info" target="_blank"><?php echo $spTextPlugin['Download Seo Panel Plugins']?> &gt;&gt;</a>
    	</td>
	</tr>
</table>
