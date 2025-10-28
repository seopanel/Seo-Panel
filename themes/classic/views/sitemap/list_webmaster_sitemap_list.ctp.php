<?php 
echo showSectionHead($spTextPanel['Sitemaps'] . "(" . $spTextTools['webmaster-tools'] . ")" );
?>

<script type="text/javascript">
$(document).ready(function() { 
    $("table").tablesorter({ 
		sortList: [[4]]
    });
});
</script>

<?php if (!$summaryPage) {?>
	<form id='search_form'>
		<?php $submitLink = "scriptDoLoadPost('websites.php', 'search_form', 'content', '&sec=listSitemap')";?>
		<table class="search">
			<tr>
				<th><?php echo $spText['common']['Website']?>: </th>
				<td>
					<select name="website_id" id="website_id" onchange="<?php echo $submitLink?>" class="custom-select">
						<option value="">-- <?php echo $spText['common']['Select']?> --</option>
						<?php foreach($websiteList as $websiteInfo){?>
							<?php if($websiteInfo['id'] == $websiteId){
								$websiteUrl = $websiteInfo['url'];
								?>
								<option value="<?php echo $websiteInfo['id']?>" selected><?php echo $websiteInfo['name']?></option>
							<?php }else{?>
								<option value="<?php echo $websiteInfo['id']?>"><?php echo $websiteInfo['name']?></option>
							<?php }?>
						<?php }?>
					</select>
				</td>
				<td>
					<a href="javascript:void(0);" onclick="<?php echo $submitLink?>" class="btn btn-secondary"><?php echo $spText['button']['Search']?></a>
				</td>
			</tr>
		</table>
	</form>
	<br>
<?php }?>

<table id="cust_tab" class="tablesorter">
	<thead>
		<tr class="listHead">
			<th><?php echo $spText['common']['Url']?></th>
			<th><?php echo $spText['common']['Total']?></th>
			<th><?php echo $spTextHome['Indexed']?></th>
			<th><?php echo $spText['common']['Errors']?></th>
			<th><?php echo $spText['common']['Warnings']?></th>
			<th><?php echo $spTextDirectory['Pending']?></th>
			<th><?php echo $spTextSitemap['Submitted']?></th>
			<th><?php echo $spTextSitemap['Downloaded']?></th>
			<?php if (!$summaryPage) {
				$colCount = 10;
				?>
				<th><?php echo $spText['common']['Action']?></th>
			<?php } else {
				$colCount = 9;
			}?>
		</tr>
	</thead>
	<tbody>
		<?php
		if (!empty($list)) {
			foreach($list as $listInfo){
				?>
				<tr>
					<td><?php echo $listInfo['path'];?></td>
					<td><?php echo $listInfo['submitted'];?></td>
					<td><?php echo $listInfo['indexed'];?></td>
					<td>
						<?php if ($listInfo['errors']) { ?>
							<a class='error' target="_blank" href="https://www.google.com/webmasters/tools/sitemap-details?siteUrl=<?php echo $websiteUrl?>&sitemapUrl=<?php echo $listInfo['path']?>">
								<font class='error bold'><?php echo $listInfo['errors']?></font>
							</a>
						<?php } else {
							echo $listInfo['errors'];
						}?>
					</td>
					<td>
						<?php echo $listInfo['warnings'] ? "<font class='bold'>{$listInfo['warnings']}</font>" : $listInfo['warnings']; ?>
					</td>
					<td>
						<?php echo $listInfo['is_pending'] ? "<font class='error bold'>{$spText['common']['Yes']}</font>" : $spText['common']['No'];?>
					</td>
					<td><?php echo $listInfo['last_submitted'];?></td>
					<td><?php echo $listInfo['last_downloaded'];?></td>
					<?php if (!$summaryPage) {?>
						<td>
							<select name="action" id="action<?php echo $listInfo['id']?>" class="custom-select" 
								onchange="doAction('websites.php', 'content', 'id=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')">
								<option value="select">-- <?php echo $spText['common']['Select']?> --</option>
								<option value="deleteSitemap"><?php echo $spText['common']['Delete']?></option>
							</select>
						</td>
					<?php }?>
				</tr>
				<?php
			}		
		} else{
			?>
			<tr><td colspan="<?php echo $colCount?>"><b><?php echo $_SESSION['text']['common']['No Records Found']?></b></tr>
			<?php	
		} 
		?>
	</tbody>
</table>

<?php if (!$summaryPage) {?>
	<table class="actionSec mt-2">
		<tr>
	    	<td>
	    		<?php if (!empty($websiteId) && !SP_HOSTED_VERSION) {?>
	    			<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmLoad('websites.php', 'content', 'sec=syncSitemaps&website_id=$websiteId')"; ?>
		    		<a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-info">
						<?php echo $spTextSitemap['Sync Sitemaps']?>
					</a>
					&nbsp;&nbsp;
				<?php }?>
	    		<a href="javascript:void(0);" onclick="scriptDoLoad('websites.php', 'content', 'sec=submitSitemap&website_id=<?php echo $websiteId?>')" class="btn btn-primary" >
					<?php echo $spTextPanel['Submit Sitemap']?>
				</a>
	    	</td>
		</tr>
	</table>
<?php }?>