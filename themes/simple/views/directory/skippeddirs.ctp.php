<?php echo showSectionHead($spTextTools['Skipped Directories']); ?>
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
		<td style="text-align: center;">
			<a href="javascript:void(0);" onclick="<?php echo $onChange?>" class="btn btn-secondary"><?php echo $spText['button']['Search']?></a>
		</td>
	</tr>
</table>
</form>

<?php
if(empty($websiteId)){
    showErrorMsg($spText['common']['nowebsites'].'!');
} 
?>

<div id='subcontent'>
<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td class="left"><?php echo $spText['common']['Id']?></td>
		<td><?php echo $spText['common']['Directory']?></td>
		<td>PR</td>
		<td width="15%"><?php echo $spText['common']['Action']?></td>
	</tr>
	<?php
	$colCount = 4; 
	if(count($list) > 0) {
		foreach($list as $i => $listInfo) {
            $argStr = "sec=unskip&id={$listInfo['id']}&pageno=$pageNo&website_id=$websiteId&search_name=".$searchInfo['search_name'];
            $includeLink = "<a href='javascript:void(0);' onclick=\"scriptDoLoad('directories.php', 'content', '$argStr')\" class='btn btn-primary btn-sm'>".$spTextDir['Add back to directory list']."</a>";
			?>
			<tr class="<?php echo $class?>">
				<td class="<?php echo $leftBotClass?>"><?php echo $i + 1?></td>
				<td class='td_br_right'  style='text-align:left;padding-left:10px;'>
					<a href="<?php echo $listInfo['submit_url']?>" target="_blank"><?php echo $listInfo['domain']?></a>
				</td>
				<td class='td_br_right'><?php echo $listInfo['pagerank']?></td>
				<td class="text-center"><?php echo $includeLink?></td>
			</tr>
			<?php
		}
	}else{
		echo showNoRecordsList($colCount-2);
	} 
	?>
</table>
</div>