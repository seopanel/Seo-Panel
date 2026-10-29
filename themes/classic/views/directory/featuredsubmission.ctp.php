<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Featured Submission']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#dirFeaturedHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="dirFeaturedHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('dirFeaturedHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-star"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Featured Submission</div>
		<div class="aiv-intro-desc">A list of premium/featured directories worth submitting to manually, sorted by PageRank. <a href="javascript:void(0);" data-toggle="modal" data-target="#dirFeaturedHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="dirFeaturedHelpModal" tabindex="-1" role="dialog" aria-labelledby="dirFeaturedHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-star"></i></div>
				<h5 class="modal-title" id="dirFeaturedHelpModalLabel">About Featured Submission</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>A read-only, informational list of directories flagged as "featured" (paid/premium listing partners), sorted by Google PageRank.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>This page doesn't submit anything for you - it's a pointer to directories worth submitting to yourself.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<table class="list">
    <tr class="listHead">
        <td class="text-center" style="width:5%"><?php echo $spText['common']['Id']?></td>
        <td><?php echo $spText['common']['Name']?></td>
        <td class="text-center">PR</td>
        <td class="text-center" width="15%"><?php echo $spText['common']['Action']?></td>
    </tr>
    <?php
    $colCount = 4;
    if(count($list) > 0) {
        foreach($list as $i => $listInfo) {
            ?>
            <tr>
                <td class="text-center"><?php echo $i?></td>
                <td><a target="_blank" href="<?php echo addHttpToUrl($listInfo['directory_link']); ?>"><?php echo $listInfo['directory_name']?></a></td>
                <td class="text-center"><?php echo !empty($listInfo['google_pagerank']) ? $listInfo['google_pagerank'] : '&mdash;'?></td>
                <td class="text-center">
                    <a href="<?php echo $listInfo['directory_link']?>" target="_blank" class="btn btn-success btn-sm">
                        <?php echo $spText['button']['Submit']?> &rarr;
                    </a>
                </td>
            </tr>
            <?php
        }
    }else{
        echo showNoRecordsList($colCount-2);
    }
    ?>
</table>
<div class="d-flex justify-content-end mt-2">
    <a href="<?php echo SP_CONTACT_LINK?>" class="btn btn-primary" target="_blank">
        <?php echo $spTextDir['clickaddfeatureddirectory']?> &rarr;
    </a>
</div>
