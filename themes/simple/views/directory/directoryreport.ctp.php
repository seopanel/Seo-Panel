<?php echo showSectionHead($spTextDir['Directory Submission Reports']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Name']?>: </th>
		<td>
			<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $onChange?>" class="form-control">
		</td>
		<th class="pl-4"><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<th class="pl-4"><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="active" onchange="<?php echo $onChange?>" class="custom-select">
				<option value="">-- Select --</option>
				<?php
				$activeList = array('pending', 'approved');
				foreach($activeList as $val){
					if($val == $activeVal){
						?>
						<option value="<?php echo $val?>" selected><?php echo ucfirst($val)?></option>
						<?php
					}else{
						?>
						<option value="<?php echo $val?>"><?php echo ucfirst($val)?></option>
						<?php
					}
				}
				?>
			</select>
		</td>
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $onChange?>" class="btn btn-secondary"><?php echo $spText['button']['Search']?></a>
		</td>
	</tr>
</table>
</form>

<?php
	if(empty($websiteId)){
		?>
		<p class='note error'><?php echo $spText['common']['No Records Found']?>!</p>
		<?php
		exit;
	} 
?>

<div id='subcontent'>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Directory']?></td>
		<td><?php echo $spText['common']['Date']?></td>
		<td>PR</td>
		<td><?php echo $spTextDir['Confirmation']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td width="10%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 6; 
	if(count($list) > 0) {
		foreach($list as $i => $listInfo) {
            $confirm = empty($listInfo['status']) ? $spText['common']["No"] : $spText['common']["Yes"];
            $confirmClass = empty($listInfo['status']) ? "btn btn-warning" : "btn btn-success";
            $confirmId = "confirm_".$listInfo['id'];
            $confirmLink = "<a class='$confirmClass' href='javascript:void(0);' onclick=\"scriptDoLoad('directories.php', '$confirmId', 'sec=changeconfirm&id={$listInfo['id']}&confirm=$confirm')\">$confirm</a>";
            
            $status = empty($listInfo['active']) ? $spTextDir["Pending"] : $spTextDir["Approved"];            
            $statusClass = empty($listInfo['active']) ? "btn btn-warning" : "btn btn-success";
            $statusId = "status_".$listInfo['id'];
			?>
			<tr>
				<td>
					<a href="<?php echo $listInfo['submit_url']?>" target="_blank"><?php echo $listInfo['domain']?></a>
				</td>
				<td><?php echo date('Y-m-d', $listInfo['submit_time']); ?></td>
				<td><?php echo $listInfo['pagerank']?></td>
				<td id='<?php echo $confirmId?>' class="text-center"><?php echo $confirmLink?></td>
				<td id='<?php echo $statusId?>' class="text-center">
					<span class='<?php echo $statusClass?> py-2 px-3 text-light'><?php echo $status?></span>
				</td>
				<td>
					<select name="action" id="action<?php echo $listInfo['id']?>" onchange="doAction('<?php echo $pageScriptPath?>&pageno=<?php echo $pageNo?>', '<?php echo $statusId?>', 'id=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')" class="custom-select" style="width: 180px;">
						<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
						<option value="checkstatus"><?php echo $spText['button']['Check Status']?></option>
						<option value="delete"><?php echo $spText['common']['Delete']?></option>
					</select>
				</td>
			</tr>
			<?php
			$i++;
		}
	}else{
		echo showNoRecordsList($colCount-2);		
	} 
	?>
</table>
</div>