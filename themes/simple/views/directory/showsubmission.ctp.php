<?php echo showSectionHead($spTextDir['Semi Automatic Directory Submission Tool']); ?>
<div class="card mb-3">
    <div class="card-body">
        <form id='search_form'>
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="font-weight-bold"><?php echo $spText['common']['Website']?>:</label>
                    <?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold"><?php echo $spText['common']['Pagerank']?>:</label>
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
