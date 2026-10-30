<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
$isSponsorsPage = ($subSec == "sponsors");
?>
<div class="aiv-page-head">
	<?php echo $isSponsorsPage ? showSectionHead($spText['label']['Sponsors']) : showSectionHead($spTextPanel['About Us']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#aboutUsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="aboutUsHelpModal" tabindex="-1" role="dialog" aria-labelledby="aboutUsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas <?php echo $isSponsorsPage ? 'fa-hand-holding-heart' : 'fa-info-circle'?>"></i></div>
				<h5 class="modal-title" id="aboutUsHelpModalLabel">About <?php echo $isSponsorsPage ? htmlspecialchars($spText['label']['Sponsors']) : htmlspecialchars($spTextPanel['About Us'])?></h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<?php if ($isSponsorsPage) { ?>
					<p>Lists the organizations and individuals who sponsor SEO Panel's development.</p>
				<?php } else { ?>
					<p>Background on SEO Panel itself and the developers/technologies behind it - purely informational, not a settings page.</p>
				<?php } ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<?php
if (!empty($blogContent['blog_content'])) {
    echo $blogContent['blog_content'];
} else {
    ?>

    <?php if ($subSec != "sponsors") {?>
	    <table class="list">
	    	<tr class="listHead">
	    		<td colspan="2"><?php echo $spText['label']['Developers']?></td>
	    	</tr>
	    	<tr class="white_row">
	    		<td class="td_left_col" style="width: 30%"><strong>PHP, MYSQL, AJAX, HTML</strong></td>
	    		<td class="td_right_col">Geo Varghese</td>
	    	</tr>
	    	<tr class="blue_row">
	    		<td class="td_left_col" style="width: 30%"><strong>PHP, MYSQL, JQUERY</strong></td>
	    		<td class="td_right_col">Deepthy Rao</td>
	    	</tr>
	    	<tr class="white_row">
	    		<td class="td_left_col" style="width: 30%"><strong>Visual Architect</strong></td>
	    		<td class="td_right_col">Chris Sievert</td>
	    	</tr>
	    </table>
	    <div class="mt-4"></div>
    <?php }?>
    <table class="list">
    	<tr class="listHead">
    		<td colspan="2"><?php echo $spText['label']['Sponsors']?></td>
    	</tr>
    	<tr class="white_row">
    		<td class="td_right_col" colspan="2" style="font-size: 18px;">
    			<a target="_blank" href="<?php echo SP_MAIN_SITE?>/aboutus/sponsors/" class="btn btn-primary">
    			    <?php echo str_replace('$100', '$500', $spTextSettings['Click here to become a sponsor for Seo Panel']); ?>
    			    <?php echo $spTextSettings['getallpluginfree']; ?>
    		   </a>
    		</td>
    	</tr>
    	<?php echo $sponsors?>
    </table>

    <?php if ($subSec != "sponsors") {?>
	    <div class="mt-4"></div>
	    <table class="list">
	    	<tr class="listHead">
	    		<td colspan="2"><?php echo $spText['label']['Translators']?></td>
	    	</tr>
	    	<?php
	    	$rowClass = "white_row";
	    	foreach($transList as $transInfo) {
	    		$rowClass = ($rowClass == "white_row") ? "blue_row" : "white_row";
	    		?>
	    		<tr class="<?php echo $rowClass?>">
	    			<td class="td_left_col" style="width: 30%"><strong><?php echo $transInfo['lang_name']?></strong></td>
	    			<td class="td_right_col"><?php echo $transInfo['trans_name']?>, <a href="<?php echo $transInfo['trans_website']?>" target="_blank"><?php echo $transInfo['trans_company']?></a></td>
	    		</tr>
	    	<?php }?>
	    </table>
    <?php }?>

<?php }?>
