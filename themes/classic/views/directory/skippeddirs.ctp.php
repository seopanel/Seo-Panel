<?php echo showSectionHead($spTextTools['Skipped Directories']); ?>
<form id='search_form'>
<table width="80%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Name']?>: </th>
		<td width="100px">
			<input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $onChange?>">
		</td>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<td colspan="2">
			<a href="javascript:void(0);" onclick="<?php echo $onChange?>" class="actionbut"><?php echo $spText['button']['Search']?></a>
		</td>
	</tr>
</table>
</form>

<?php
	if(empty($websiteId)){ showErrorMsg($spText['common']['nowebsites'].'!');} 
?>

<div id='subcontent'>
<?php echo $pagingDiv?>
<table width="100%" border="0" cellspacing="0" cellpadding="2px;" class="list" align='center'>
	<tr>
	<td width='33%'>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Directory']?></td>
		<td>PR</td>
		<td class="right"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 4; 
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

            $argStr = "sec=unskip&id={$listInfo['id']}&pageno=$pageNo&website_id=$websiteId&search_name=".$searchInfo['search_name'];
            $includeLink = "<a href='javascript:void(0);' onclick=\"scriptDoLoad('directories.php', 'content', '$argStr')\">".$spTextDir['Add back to directory list']."</a>";
            
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><?php echo $listInfo['id']?></td>
				<td class='td_br_right'  style='text-align:left;padding-left:10px;'>
					<a href="<?php echo $listInfo['submit_url']?>" target="_blank"><?php echo $listInfo['domain']?></a>
				</td>
				<td class='td_br_right'><?php echo $listInfo['pagerank']?></td>
				<td class="<?php echo $rightBotClass?>"><?php echo $includeLink?></td>
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