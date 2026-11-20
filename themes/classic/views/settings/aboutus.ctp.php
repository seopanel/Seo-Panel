<?php
include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php');
include(SP_VIEWPATH.'/settings/_settings_styles.ctp.php');
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
	    <div class="sp-settings-subhead"><?php echo $spText['label']['Developers']?></div>
	    <div class="aiv-card">
	    	<div class="sp-settings-row">
	    		<div class="sp-settings-row-label">PHP, MYSQL, AJAX, HTML</div>
	    		<div class="sp-settings-row-control">Geo Varghese</div>
	    	</div>
	    	<div class="sp-settings-row">
	    		<div class="sp-settings-row-label">PHP, MYSQL, JQUERY</div>
	    		<div class="sp-settings-row-control">Deepthy Rao</div>
	    	</div>
	    	<div class="sp-settings-row">
	    		<div class="sp-settings-row-label">Visual Architect</div>
	    		<div class="sp-settings-row-control">Chris Sievert</div>
	    	</div>
	    </div>
    <?php }?>
    <div class="sp-settings-subhead"><?php echo $spText['label']['Sponsors']?></div>
    <div class="aiv-card">
    	<div class="sp-settings-row" style="border-bottom:none; padding-bottom:4px;">
    		<div class="sp-settings-row-control" style="font-size: 18px;">
    			<a target="_blank" href="<?php echo SP_MAIN_SITE?>/aboutus/sponsors/" class="btn btn-primary">
    			    <?php echo str_replace('$100', '$500', $spTextSettings['Click here to become a sponsor for Seo Panel']); ?>
    			    <?php echo $spTextSettings['getallpluginfree']; ?>
    		   </a>
    		</div>
    	</div>
    </div>
    <!-- $sponsors is a whole external page fetched from SP_SPONSOR_PAGE
         (see InformationController::getSponsors()), not a <tr> fragment -
         it carries its own markup/container (checked for class="contentmid"
         before being trusted), so it's left in its own plain wrapper
         rather than forced into the .aiv-card row layout above. -->
    <div class="mt-3"><?php echo $sponsors?></div>

    <?php if ($subSec != "sponsors") {?>
	    <div class="sp-settings-subhead"><?php echo $spText['label']['Translators']?></div>
	    <div class="aiv-card">
	    	<?php foreach($transList as $transInfo) {?>
	    		<div class="sp-settings-row">
	    			<div class="sp-settings-row-label"><?php echo $transInfo['lang_name']?></div>
	    			<div class="sp-settings-row-control"><?php echo $transInfo['trans_name']?>, <a href="<?php echo $transInfo['trans_website']?>" target="_blank"><?php echo $transInfo['trans_company']?></a></div>
	    		</div>
	    	<?php }?>
	    </div>
    <?php }?>

<?php }?>
