<?php echo showSectionHead($spTextDir['Directory Submission Reports']); ?>
<form id='search_form'>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Name']?>: </th>
		<td width="100px">
			<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $onChange?>">
		</td>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<th><?php echo $spText['common']['Status']?>: </th>
		<td>
			<select name="active" style="width:150px;" onchange="<?php echo $onChange?>">
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
		<td colspan="2">
			<a href="javascript:void(0);" onclick="<?php echo $onChange?>" class="actionbut"><?php echo $spText['button']['Search']?></a>
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
<table width="100%" border="0" cellspacing="0" cellpadding="2px;" class="list" align='center'>
	<tr>
	<td width='33%'>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Directory']?></td>
		<td><?php echo $spText['common']['Date']?></td>
		<td>PR</td>
		<td><?php echo $spTextDir['Confirmation']?></td>
		<td><?php echo $spText['common']['Status']?></td>
		<td class="right"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 6; 
	if(count($list) > 0){
		$catCount = count($list);
		$i = 0;
		foreach($list as $listInfo){
			
			$class = ($i % 2) ? "blue_row" : "white_row";
            if($catCount == ($i + 1)){
                $leftBotClass = "tab_left_bot";
                $rightBotClass = "tab_right_bot";
            }else{
                $leftBotClass = "td_left_border td_br_right";
                $rightBotClass = "td_br_right";
            }
            $confirm = empty($listInfo['status']) ? $spText['common']["No"] : $spText['common']["Yes"];
            $confirmId = "confirm_".$listInfo['id'];
            $confirmLink = "<a href='javascript:void(0);' onclick=\"scriptDoLoad('directories.php', '$confirmId', 'sec=changeconfirm&id={$listInfo['id']}&confirm=$confirm')\">$confirm</a>";
            
            $status = empty($listInfo['active']) ? $spTextDir["Pending"] : $spTextDir["Approved"];            
            $statusId = "status_".$listInfo['id'];
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>" style='text-align:left;padding-left:10px;'>
					<a href="<?php echo $listInfo['submit_url']?>" target="_blank"><?php echo $listInfo['domain']?></a>
				</td>
				<td class='td_br_right'><?php echo date('Y-m-d', $listInfo['submit_time']); ?></td>
				<td class='td_br_right'><?php echo $listInfo['pagerank']?></td>
				<td class='td_br_right' id='<?php echo $confirmId?>'><?php echo $confirmLink?></td>
				<td class='td_br_right' id='<?php echo $statusId?>'><?php echo $status?></td>
				<td class="<?php echo $rightBotClass?>">
					<select name="action" id="action<?php echo $listInfo['id']?>" onchange="doAction('<?php echo $pageScriptPath?>&pageno=<?php echo $pageNo?>', '<?php echo $statusId?>', 'id=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')">
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
	<tr class="listBot">
		<td class="left" colspan="<?php echo ($colCount-1)?>"></td>
		<td class="right"></td>
	</tr>
	</table>
	</td>
	</tr>
</table>
</div>