<style>
/* Score Circle for table */
.score-circle-small {
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

.score-progress-small {
	position: relative;
	width: 50px;
	height: 50px;
}

.score-progress-small svg {
	transform: rotate(-90deg);
}

.score-progress-bg-small {
	fill: none;
	stroke: #e2e8f0;
	stroke-width: 6;
}

.score-progress-bar-small {
	fill: none;
	stroke-width: 6;
	stroke-linecap: round;
	transition: stroke-dashoffset 1s ease;
}

.score-progress-bar-small.positive {
	stroke: url(#scoreGradientPositiveSmall);
}

.score-progress-bar-small.negative {
	stroke: url(#scoreGradientNegativeSmall);
}

.score-text-small {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%, -50%);
	font-size: 14px;
	font-weight: 700;
	color: #2d3748;
}
</style>

<form name="listform" id="listform">
<?php echo showSectionHead($spTextTools['Auditor Projects']); ?>
<?php if(!empty($isAdmin)){ ?>
	<table class="search">
		<tr>
			<th><?php echo $spText['common']['User']?>: </th>
			<td>
				<select name="userid" id="userid" onchange="doLoad('userid', 'siteauditor.php', 'content')" class="custom-select">
					<option value="">-- <?php echo $spText['common']['Select']?> --</option>
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] == $userId){?>
							<option value="<?php echo $userInfo['id']?>" selected><?php echo $userInfo['username']?></option>
						<?php }else{?>
							<option value="<?php echo $userInfo['id']?>"><?php echo $userInfo['username']?></option>
						<?php }?>
					<?php }?>
				</select>
			</td>
		</tr>
	</table>
<?php } ?>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td><input type="checkbox" id="checkall" onclick="checkList('checkall')"></td>
		<td><?php echo $spText['common']['Website']?></td>
		<?php if(!empty($isAdmin)){ ?>
			<td><?php echo $spText['common']['User']?></td>
		<?php } ?>
		<td><?php echo $spTextSA['Maximum Pages']?></td>
		<td><?php echo $spTextSA['Pages Found']?></td>
		<td><?php echo $spTextSA['Crawled Pages']?></td>
		<td><?php echo $spText['label']['Cron']?></td>
		<td><?php echo $spText['label']['Score']?></td>
		<td><?php echo $spText['label']['Updated']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td style="width: 10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = empty($isAdmin) ? 10 : 11; 
	if(count($list) > 0) {
		foreach($list as $listInfo) {
            $websiteLink = scriptAJAXLinkHref('siteauditor.php', 'content', "sec=edit&project_id={$listInfo['id']}", "{$listInfo['name']}")
			?>
			<tr>
				<td><input type="checkbox" name="ids[]" value="<?php echo $listInfo['id']?>"></td>
				<td class="text-left"><?php echo $websiteLink?></td>
				<?php if(!empty($isAdmin)){ ?>
					<td><?php echo $listInfo['username']?></td>
				<?php } ?>
				<td><?php echo $listInfo['max_links']?></td>
				<td><?php echo $listInfo['total_links']?></td>
				<td><?php echo $listInfo['crawled_links']?></td>
				<td><?php echo showStatusBadge($listInfo['cron'], 'yesno');?></td>
				<td style="text-align: center;">
				    <?php
			        $score = $listInfo['score'];
			        $isPositive = $score >= 0;
			        $absScore = abs($score);
			        $maxScore = 100;
			        $percentage = min(($absScore / $maxScore) * 100, 100);
			        $circumference = 2 * 3.14159 * 22;
			        $dashOffset = $circumference - ($percentage / 100) * $circumference;
			        ?>
					<div class="score-circle-small">
						<div class="score-progress-small">
							<svg width="50" height="50">
								<defs>
									<linearGradient id="scoreGradientPositiveSmall<?php echo $listInfo['id']; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
										<stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
										<stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
									</linearGradient>
									<linearGradient id="scoreGradientNegativeSmall<?php echo $listInfo['id']; ?>" x1="0%" y1="0%" x2="100%" y2="100%">
										<stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
										<stop offset="100%" style="stop-color:#dc2626;stop-opacity:1" />
									</linearGradient>
								</defs>
								<circle class="score-progress-bg-small" cx="25" cy="25" r="22"></circle>
								<circle class="score-progress-bar-small <?php echo $isPositive ? 'positive' : 'negative'; ?>"
									cx="25" cy="25" r="22"
									stroke="url(#scoreGradient<?php echo $isPositive ? 'Positive' : 'Negative'; ?>Small<?php echo $listInfo['id']; ?>)"
									stroke-dasharray="<?php echo $circumference; ?>"
									stroke-dashoffset="<?php echo $dashOffset; ?>"></circle>
							</svg>
							<div class="score-text-small"><?php echo round($score, 1); ?></div>
						</div>
					</div>
				</td>
				<td><?php echo $listInfo['last_updated']?></td>
				<td class="text-center"><?php echo showStatusBadge($listInfo['status']);	?></td>
				<td>
					<?php
						if($listInfo['status']){
							$statVal = "Inactivate";
							$statLabel = $spText['common']["Inactivate"];
						}else{
							$statVal = "Activate";
							$statLabel = $spText['common']["Activate"];
						}
					?>
					<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select" onchange="doAction('siteauditor.php', 'content', 'project_id=<?php echo $listInfo['id']?>&pageno=<?php echo $pageNo?>', 'action<?php echo $listInfo['id']?>')" style="width: 180px;">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<?php if ($listInfo['status']) {?>
    						<?php if ($listInfo['max_links'] > $listInfo['crawled_links']) {?>
    							<option value="showrunproject"><?php echo $spTextSA['Run Project']?></option>
    						<?php }?>
    						<?php if ($listInfo['total_links'] > 0) {?>
    							<option value="viewreports"><?php echo $spText['label']['View Reports']?></option>
    							<option value="recheckreport"><?php echo $spTextSA['Recheck Pages']?></option>
    						<?php }?>
    					<?php } ?>
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
</table>
<?php
if (SP_DEMO) {
    $actFun = $inactFun = $delFun = "alertDemoMsg()";
} else {
    $actFun = "confirmSubmit('siteauditor.php', 'listform', 'content', '&sec=activateall&pageno=$pageNo')";
    $inactFun = "confirmSubmit('siteauditor.php', 'listform', 'content', '&sec=inactivateall&pageno=$pageNo')";
    $delFun = "confirmSubmit('siteauditor.php', 'listform', 'content', '&sec=deleteall&pageno=$pageNo')";
}   
?>
<table class="actionSec mt-2">
	<tr>
    	<td>
         	<a onclick="scriptDoLoad('siteauditor.php', 'content', 'sec=new')" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spTextPanel['New Project']?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-success">
         		<?php echo $spText['common']["Activate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $inactFun?>" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['common']["Inactivate"]?>
         	</a>&nbsp;&nbsp;
         	<a onclick="<?php echo $delFun?>" href="javascript:void(0);" class="btn btn-danger">
         		<?php echo $spText['common']['Delete']?>
         	</a>
    	</td>
	</tr>
</table>
</form>