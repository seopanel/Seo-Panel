<?php
echo showSectionHead($spTextPanel["Connections"]);

if ($successMsg) {
	showSuccessMsg($successMsg, false);
}

if ($errorMsg) {
	showErrorMsg($errorMsg, false);
}
?>
<table class="list">
	<tr class="listHead">
		<td><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['label']['Name']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td style="width: 15%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 4;
	if (count($list) > 0) {

		foreach ($list as $i => $listInfo) {
			?>
			<tr>
				<td><?php echo $i + 1;?></td>
				<td><?php echo ucfirst($listInfo['name'])?></td>
				<td class="text-center">
					<?php
					if ($listInfo['status']) {
						echo "<span class='badge badge-success py-2 px-3 text-light'>{$spTextMyAccount['Connected']}</span>";
					} else {
						echo "<span class='badge badge-danger py-2 px-3 text-light'>{$spTextMyAccount['Disconnected']}</span>";
					}
					?>
				</td>
				<td class="text-center">
					<?php
					if ($listInfo['status']) {
						$disconnectFun = SP_DEMO ? "alertDemoMsg()" :  "confirmLoad('connections.php', 'content', 'action=disconnect&category={$listInfo['name']}')";
						?>
						<a onclick="<?php echo $disconnectFun?>" href="javascript:void(0);" class="btn btn-danger"><?php echo $spTextMyAccount['Disconnect']?></a>
						<?php
					} else {

						// check whether auth url set
						if ($listInfo['auth_url_info']['auth_url']) {
							?>
							<a href="<?php echo $listInfo['auth_url_info']['auth_url']?>" class="btn btn-success"><?php echo $spTextMyAccount['Connect']?></a>
							<?php
						} else {
							echo "<span class='text-danger fw-bold'>{$listInfo['auth_url_info']['msg']}</span>";
						}

					}
					?>
				</td>
			</tr>
			<?php
		}
	}else{
		echo showNoRecordsList($colCount-2);
	}
	?>
</table>
