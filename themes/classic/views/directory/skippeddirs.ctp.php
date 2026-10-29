<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextTools['Skipped Directories']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#dirSkippedHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="dirSkippedHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('dirSkippedHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-ban"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Skipped Directories</div>
		<div class="aiv-intro-desc">Directories you chose to skip during Automatic Submission, per website - unskip one to make it eligible again. <a href="javascript:void(0);" data-toggle="modal" data-target="#dirSkippedHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="dirSkippedHelpModal" tabindex="-1" role="dialog" aria-labelledby="dirSkippedHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-ban"></i></div>
				<h5 class="modal-title" id="dirSkippedHelpModalLabel">About Skipped Directories</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Shows, per website, every directory you chose to skip during Automatic Submission. From here you can "unskip" one to make it eligible again next time you submit.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>A skipped directory never appears again in Automatic Submission unless you unskip it here.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>
<div class="card mb-3">
    <div class="card-body">
        <form id='search_form'>
            <div class="row">
                <div class="col-md-4">
                    <label class="font-weight-bold"><?php echo $spText['common']['Name']?>:</label>
                    <input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $onChange?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="font-weight-bold"><?php echo $spText['common']['Website']?>:</label>
                    <?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="javascript:void(0);" onclick="<?php echo $onChange?>" class="btn btn-secondary btn-block"><?php echo $spText['button']['Search']?></a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
if(empty($websiteId)){
    showErrorMsg($spText['common']['nowebsites'].'!');
}
?>

<div id='subcontent'>
<?php echo $pagingDiv?>
<table class="list">
    <tr class="listHead">
        <td class="text-center" style="width:5%"><?php echo $spText['common']['Id']?></td>
        <td><?php echo $spText['common']['Directory']?></td>
        <td class="text-center">PR</td>
        <td class="text-center" width="18%"><?php echo $spText['common']['Action']?></td>
    </tr>
    <?php
    $colCount = 4;
    if(count($list) > 0) {
        foreach($list as $i => $listInfo) {
            $argStr = "sec=unskip&id={$listInfo['id']}&pageno=$pageNo&website_id=$websiteId&search_name=".$searchInfo['search_name'];
            $includeLink = "<a href='javascript:void(0);' onclick=\"scriptDoLoad('directories.php', 'content', '$argStr')\" class='btn btn-primary btn-sm'>".$spTextDir['Add back to directory list']."</a>";
            ?>
            <tr>
                <td class="text-center"><?php echo $i + 1?></td>
                <td>
                    <a href="<?php echo $listInfo['submit_url']?>" target="_blank"><?php echo $listInfo['domain']?></a>
                </td>
                <td class="text-center"><?php echo !empty($listInfo['pagerank']) ? $listInfo['pagerank'] : '&mdash;'?></td>
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
