<?php echo showSectionHead($spTextPanel['Report Generation Manager']); ?>
<form id='search_form'>
<table class="search">
	<tr>
		<th><?php echo $spText['common']['Website']?>: </th>
		<td>
			<?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
		</td>
		<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "scriptDoLoadPost('cron.php', 'search_form', 'subcontent', '&sec=generate')"; ?>
		<td><a href="javascript:void(0);" onclick="<?php echo $actFun?>" class="btn btn-secondary"><?php echo $spText['button']['Proceed']?></a></td>
	</tr>
	<tr>
		<th nowrap="nowrap"><?php echo $spText['common']['Seo Tools']?>: </th>
		<td colspan="2" style="font-size: 12px;">
			<?php foreach($repTools as $i => $repInfo){ ?>
				<div class="form-check my-3">
					<input type="checkbox" name="repTools[]" value="<?php echo $repInfo['id']?>" class="form-check-input" id="repTool_<?php echo $repInfo['id']?>">
					<label class="form-check-label" for="repTool_<?php echo $repInfo['id']?>">
						<?php echo $spTextTools[$repInfo['url_section']]?>
					</label>
				</div>
			<?php }?>
		</td>
	</tr>
</table>
</form>

<div id='subcontent'>
	<p class='note'><?php echo $spTextTools['clickgeneratereports']?></p>
	<p class='note'><?php echo $spTextTools['note_report_generation']?></p>
</div>