<form id="editSubmitInfo">
<input type="hidden" name="sec" value="updatesiteinfo"/>
<input type="hidden" name="website_id" value="<?php echo $websiteInfo['website_id']?>"/>

<div class="card mb-3">
    <div class="card-header font-weight-bold"><?php echo $spTextDir['Submission Details']?></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><?php echo $spTextDir['Owner Name']?>:</label>
                    <input type="text" name="owner_name" value="<?php echo stripslashes($websiteInfo['owner_name'])?>" class="form-control">
                    <?php echo $errMsg['owner_name']?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><?php echo $spTextDir['Owner Email']?>:</label>
                    <input type="text" name="owner_email" value="<?php echo $websiteInfo['owner_email']?>" class="form-control">
                    <?php echo $errMsg['owner_email']?>
                    <small class="text-muted"><?php echo $spTextDir['spamemailnote']?></small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><?php echo $spTextDir['Website Url']?>:</label>
                    <div class="input-group">
                        <input type="text" id="weburl" name="url" value="<?php echo $websiteInfo['url']?>" class="form-control">
                        <div class="input-group-append">
                            <a href="javascript:void(0);" onclick="crawlMetaData('websites.php?sec=crawlmeta', 'crawlstats')" class="btn btn-info">
                                <?php echo $spText['common']['Crawl Meta Data']?>
                            </a>
                        </div>
                    </div>
                    <div id="crawlstats" class="mt-1"></div>
                    <?php echo $errMsg['url']?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><?php echo $spTextDir['Website Category']?>:</label>
                    <input type="text" name="category" value="<?php echo stripslashes($websiteInfo['category'])?>" class="form-control">
                    <?php echo $errMsg['category']?>
                    <small class="text-muted"><?php echo $spTextDir['categorynote']?> &mdash; Eg: google seo tools, seo tools, seo</small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><?php echo $spTextDir['Submit Keywords']?>:</label>
                    <textarea name="keywords" id="webkeywords" class="form-control" rows="2"><?php echo stripslashes($websiteInfo['keywords'])?></textarea>
                    <?php echo $errMsg['keywords']?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><?php echo $spTextDir['Reciprocal Link']?>:</label>
                    <input type="text" name="reciprocal_url" value="<?php echo stripslashes($websiteInfo['reciprocal_url'])?>" class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold"><?php echo $spTextDir['Submit Title']?> &amp; <?php echo $spTextDir['Submit Description']?></span>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><?php echo $spTextDir['Submit Title']?>1 <span class="text-danger">*</span>:</label>
                    <input type="text" id="webtitle" name="title" value="<?php echo stripslashes($websiteInfo['title'])?>" class="form-control">
                    <?php echo $errMsg['title']?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="font-weight-bold"><?php echo $spTextDir['Submit Description']?>1 <span class="text-danger">*</span>:</label>
                    <textarea name="description" id="webdescription" class="form-control" rows="3"><?php echo stripslashes($websiteInfo['description'])?></textarea>
                    <?php echo $errMsg['description']?>
                    <small class="text-muted"><?php echo $spTextDir['desnote']?></small>
                </div>
            </div>
        </div>

        <?php if ($noTitles > 1) { ?>
        <div class="mt-2">
            <a class="btn btn-outline-secondary btn-sm" data-toggle="collapse" href="#extraTitles" role="button">
                <?php echo $spTextDir['optionalnote']?> &#9660;
            </a>
            <div class="collapse mt-3" id="extraTitles">
                <?php for($i=2; $i<=$noTitles; $i++) { ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><?php echo $spTextDir['Submit Title']?><?php echo $i?>:</label>
                            <input type="text" name="title<?php echo $i?>" value="<?php echo stripslashes($websiteInfo['title'.$i])?>" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold"><?php echo $spTextDir['Submit Description']?><?php echo $i?>:</label>
                            <textarea name="description<?php echo $i?>" class="form-control" rows="3"><?php echo stripslashes($websiteInfo['description'.$i])?></textarea>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<div class="d-flex justify-content-end mb-4">
    <a onclick="scriptDoLoad('directories.php', 'content')" href="javascript:void(0);" class="btn btn-warning mr-2">
        <?php echo $spText['button']['Cancel']?>
    </a>
    <a onclick="scriptDoLoadPost('directories.php', 'editSubmitInfo', 'subcontent')" href="javascript:void(0);" class="btn btn-primary px-4">
        <?php echo $spText['button']['Proceed']?> &rarr;
    </a>
</div>
</form>
