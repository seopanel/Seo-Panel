<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextDir['Semi Automatic Directory Submission Tool']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#dirAutoHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="dirAutoHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('dirAutoHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-book"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Automatic Submission</div>
		<div class="aiv-intro-desc">Walks your website through submission to directories one at a time, auto-filling each one's form as it goes. <a href="javascript:void(0);" data-toggle="modal" data-target="#dirAutoHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="dirAutoHelpModal" tabindex="-1" role="dialog" aria-labelledby="dirAutoHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-book"></i></div>
				<h5 class="modal-title" id="dirAutoHelpModalLabel">About Automatic Submission</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>Pick a website and this fetches each matching directory's real submit form live, auto-fills what it can detect (category, anti-spam fields), and submits it to the actual external directory site when you confirm.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>This makes real outbound requests to third-party sites and may need CAPTCHA solving. Submission counts are limited by your account's plan.</span>
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
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="font-weight-bold"><?php echo $spText['common']['Website']?>:</label>
                    <?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold" title="Google discontinued PageRank in 2016 - kept only for filtering legacy directory data"><?php echo $spText['common']['Pagerank']?> <small class="text-muted">(legacy)</small>:</label>
                    <select name="pagerank" class="custom-select">
                        <option value="">-- <?php echo $spText['common']['Select']?> --</option>
                        <?php for ($i=0; $i<=10; $i++) { ?>
                            <option value="<?php echo $i?>" <?php echo $selected?>><?php echo "PR $i"?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold"><?php echo $spText['common']['lang']?>:</label>
                    <select name="lang_code" class="custom-select">
                        <option value="">-- <?php echo $spText['common']['Select']?> --</option>
                        <?php foreach ($langList as $langInfo) {
                            $selected = ($_SESSION['dirsub_lang'] == $langInfo['lang_code']) ? "selected" : ""; ?>
                            <option value="<?php echo $langInfo['lang_code']?>" <?php echo $selected?>><?php echo $langInfo['lang_name']?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="d-flex flex-wrap">
                        <div class="custom-control custom-switch mr-4 mb-1">
                            <input type="checkbox" class="custom-control-input" name="no_captcha" id="no_captcha"
                                <?php echo empty($_SESSION['no_captcha']) ? "" : "checked"; ?>
                                onchange="checkDirectoryFilter('no_captcha', 'directories.php?sec=checkcaptcha', 'tmp')">
                            <label class="custom-control-label" for="no_captcha"><?php echo $spTextDir['Directories with out captcha']?></label>
                        </div>
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input" name="no_reciprocal" id="no_reciprocal"
                                <?php echo empty($_SESSION['no_reciprocal']) ? "" : "checked"; ?>
                                onchange="checkDirectoryFilter('no_reciprocal', 'directories.php?sec=checkreciprocal', 'tmp')">
                            <label class="custom-control-label" for="no_reciprocal"><?php echo $spTextDir['Directories with out Reciprocal Link']?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12 text-right">
                    <a onclick="scriptDoLoadPost('directories.php', 'search_form', 'subcontent')" href="javascript:void(0);" class="btn btn-primary px-4">
                        <i class="fa fa-arrow-right"></i> <?php echo $spText['button']['Show Details']?>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
<div id='tmp' style="display:none;"></div>
<div id='subcontent'>
    <p class='note left'><?php echo $spTextDir['selectwebsiteproceed']?>!</p>
</div>
