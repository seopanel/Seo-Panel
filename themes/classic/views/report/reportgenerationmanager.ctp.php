<?php echo showSectionHead($spTextPanel['Report Generation Manager']); ?>
<form id='search_form'>
<table width="400px" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('cron.php', 'search_form', 'subcontent', '&sec=generate')"; ?>
		<td><a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="actionbut"><?php echo $spText['button']['Proceed']?></a></td>		
	</tr>
	<tr>
		<th nowrap="nowrap"><?php echo $spText['common']['Seo Tools']?>: </th>
		<td colspan="2" style="font-size: 12px;">
			<?php foreach ($repTools as $i => $repInfo) {
    ?>
				<input type="checkbox" name="repTools[]" value="<?php echo $repInfo['id']?>" checked="checked"> <?php echo $spTextTools[$repInfo['url_section']]?><br>				
			<?php 
}?>			
		</td>		
	</tr>
</table>
</form>

<div id='subcontent'>
	<p class='note'><?php echo $spTextTools['clickgeneratereports']?></p>
</div>