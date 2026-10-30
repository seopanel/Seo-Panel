<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$headLabel = empty($headLabel) ? $spTextPanel['Report Settings'] : $headLabel;
?>
<div class="aiv-page-head">
	<?php echo showSectionHead($headLabel); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#reportSettingsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="reportSettingsHelpModal" tabindex="-1" role="dialog" aria-labelledby="reportSettingsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-sliders-h"></i></div>
				<h5 class="modal-title" id="reportSettingsHelpModalLabel">About Global Reports Settings</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Tuning for how the scheduled crawler and report-generation cron jobs behave across the whole install: crawl delay, user agent string, how many websites/keywords each cron run processes, AI Overview data retention and rolling-window settings, email notification toggles, and whether non-admin users can schedule or generate reports themselves.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>These apply site-wide, not per-user or per-website - changes here affect every scheduled crawl on this install.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<?php
// if saved successfully
if (!empty($saved)) {
    showSuccessMsg($spTextReport['reportsettingssaved'], false);
}
?>
<form id="updateSettings">
<input type="hidden" value="update" name="sec">
<input type="hidden" value="<?php echo $category?>" name="category">
<table class="list">
	<tr class="listHead">
		<td class="left" width='35%'><?php echo $headLabel?></td>
		<td class="right">&nbsp;</td>
	</tr>
	<?php
	foreach( $list as $i => $listInfo){
		$class = ($i % 2) ? "blue_row" : "white_row";
		switch($listInfo['set_type']){

			case "small":
				$width = 40;
				break;

			case "bool":
				if(empty($listInfo['set_val'])){
					$selectYes = "";
					$selectNo = "selected";
				}else{
					$selectYes = "selected";
					$selectNo = "";
				}
				break;

			case "medium":
				$width = 200;
				break;

			case "large":
			case "text":
				$width = 500;
				break;
		}
		?>
		<tr class="<?php echo $class?>">
			<td class="td_left_col"><strong><?php echo $spTextSettings[$listInfo['set_name']]?>:</strong></td>
			<td class="td_right_col">
				<?php if($listInfo['set_type'] != 'text'){?>
					<?php if($listInfo['set_type'] == 'bool'){?>
						<select name="<?php echo $listInfo['set_name']?>" class="custom-select">
							<option value="1" <?php echo $selectYes?>><?php echo $spText['common']['Yes']?></option>
							<option value="0" <?php echo $selectNo?>><?php echo $spText['common']['No']?></option>
						</select>
					<?php }else{?>
						<?php if($listInfo['set_name'] == 'SP_SYSTEM_REPORT_INTERVAL') {?>
							<select name="<?php echo $listInfo['set_name']?>" class="custom-select">
								<?php
								foreach ($scheduleList as $interval => $label) {
									$selected = ($interval == $listInfo['set_val']) ? "selected" : "";
									?>
									<option value="<?php echo $interval?>" <?php echo $selected?>><?php echo $label?></option>
									<?php
								}
								?>
							</select>
						<?php } else {?>
							<input type="text" name="<?php echo $listInfo['set_name']?>" value="<?php echo htmlspecialchars(stripslashes($listInfo['set_val']), ENT_QUOTES)?>" class="form-control" style='width:<?php echo $width?>px'>
							<?php if ($listInfo['set_name'] == 'SP_NUMBER_KEYWORDS_CRON') {?>
								<p class="mt-2 text-muted"><?php echo $spTextReport['keywordnumbercheckedcronnote']?></p>
							<?php } ?>
						<?php }?>
					<?php }?>
				<?php }else{?>
					<textarea name="<?php echo $listInfo['set_name']?>" class="form-control" style='width:<?php echo $width?>px'><?php echo htmlspecialchars(stripslashes($listInfo['set_val']))?></textarea>
				<?php }?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
<table class="actionSec mt-2 float-right">
	<tr>
    	<td>
    		<a onclick="scriptDoLoad('settings.php?sec=reportsettings', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-warning">
         		<?php echo $spText['button']['Cancel']?>
         	</a>
         	<?php $actFun = SP_DEMO ? "alertDemoMsg()" : "confirmSubmit('settings.php', 'updateSettings', 'content')"; ?>
         	<a onclick="<?php echo $actFun?>" href="javascript:void(0);" class="btn btn-primary">
         		<?php echo $spText['button']['Proceed']?>
         	</a>
    	</td>
	</tr>
</table>
</form>
