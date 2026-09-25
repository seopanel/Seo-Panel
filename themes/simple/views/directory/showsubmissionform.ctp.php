<?php if(!empty($msg)){
    $msgClass = empty($error) ? "alert-success" : "alert-danger";
    echo "<div class='alert $msgClass'>$msg</div>";
}
?>
<form id="submissionForm" name="submissionForm">
<input type="hidden" name="sec" value="submitsite"/>
<input type="hidden" name="website_id" value="<?php echo $websiteId?>"/>
<input type="hidden" name="dir_id" value="<?php echo $dirInfo['id']?>"/>
<input type="hidden" name="add_params" value="<?php echo $addParams?>">
<?php if(!empty($phpsessid)){?>
    <input type="hidden" name="phpsessid" value="<?php echo $phpsessid?>">
<?php }?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="font-weight-bold"><?php echo $spTextTools['directory-submission']?></span>
        <div>
            <?php if(!empty($dirInfo['pagerank'])){ ?>
                <span class="badge badge-secondary mr-1">PR <?php echo $dirInfo['pagerank']?></span>
            <?php } ?>
            <?php if(!empty($dirInfo['domain_authority'])){ ?>
                <span class="badge badge-info mr-1">DA <?php echo $dirInfo['domain_authority']?></span>
            <?php } ?>
            <?php if(!empty($dirInfo['page_authority'])){ ?>
                <span class="badge badge-primary">PA <?php echo $dirInfo['page_authority']?></span>
            <?php } ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group row mb-2">
                    <label class="col-sm-4 col-form-label font-weight-bold"><?php echo $spText['common']['Directory']?>:</label>
                    <div class="col-sm-8 d-flex align-items-center">
                        <a href="<?php echo $dirInfo['submit_url']?>" target="_blank" class="text-primary">
                            <?php echo $dirInfo['domain']?> <small>&#8599;</small>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group row mb-2" id="category_col">
                    <label class="col-sm-4 col-form-label font-weight-bold"><?php echo $spText['common']['Category']?>:</label>
                    <div class="col-sm-8">
                        <?php echo $categorySel?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($reciprocalDir)) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row mb-2">
                    <label class="col-sm-2 col-form-label font-weight-bold"><?php echo $spTextDir['Reciprocal Link']?>:</label>
                    <div class="col-sm-10">
                        <input type="text" name="reciprocal_url" value="<?php echo $reciprocalUrl?>" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if(!empty($captchaUrl)){ ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card border-warning mt-2">
                    <div class="card-header bg-warning text-white font-weight-bold py-2">
                        <?php echo $spTextDir['Enter the code shown']?>
                    </div>
                    <div class="card-body">
                        <?php if(!empty($imageHash)){?>
                            <input type="hidden" name="<?php echo $dirInfo['imagehash_col']?>" value="<?php echo $imageHash?>">
                        <?php }?>
                        <img src='<?php echo $captchaUrl?>' class="mb-2 d-block border rounded">
                        <?php
                        $captchaCodeError = "";
                        if (stristr($captchaCode, 'Error:')) {
                            $captchaCodeError = formatErrorMsg($captchaCode);
                            $captchaCode = "";
                        }
                        ?>
                        <input type="text" name="<?php echo $dirInfo['cptcha_col']?>" value="<?php echo $captchaCode;?>" id='captcha' class="form-control" placeholder="Enter captcha code...">
                        <?php echo $captchaCodeError?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<div class="d-flex justify-content-end mb-4">
    <a onclick="scriptDoLoad('directories.php', 'content')" href="javascript:void(0);" class="btn btn-warning mr-2">
        <?php echo $spText['button']['Cancel']?>
    </a>
    <a onclick="scriptDoLoad('directories.php?sec=skip&website_id=<?php echo $websiteId?>&dir_id=<?php echo $dirInfo['id']?>', 'subcontent')" href="javascript:void(0);" class="btn btn-secondary mr-2">
        <?php echo $spText['button']['Skip']?>
    </a>
    <a onclick="scriptDoLoad('directories.php?sec=reload&website_id=<?php echo $websiteId?>&dir_id=<?php echo $dirInfo['id']?>', 'subcontent')" href="javascript:void(0);" class="btn btn-info mr-2">
        <?php echo $spText['button']['Reload']?>
    </a>
    <a onclick="checkSubmitInfo('directories.php', 'submissionForm', 'subcontent', '<?php echo $dirInfo['category_col']?>')" href="javascript:void(0);" class="btn btn-primary px-4" id="dir_submit_but">
        <?php echo $spText['button']['Submit']?> &rarr;
    </a>
</div>
</form>

<script>
jQuery.expr[':'].icontains = function(a, i, m) {
  return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

var catSelectStr = "<?php echo $catSelectStr?>";
var catList = catSelectStr.split(",");
var found = 0;
for (var i = 0; i < catList.length; i++) {
    $('#category_col option:icontains(' + catList[i].trim() + ')').each(function() {
        $(this).attr('selected', 'selected');
        found = 1;
        return true;
    });
    if (found) break;
}

<?php if (defined("CB_ENABLE_DIR_AUTO_SUBMISSION") && CB_ENABLE_DIR_AUTO_SUBMISSION) {?>
    setTimeout(function() {
        $('#dir_submit_but').trigger('click');
    }, <?php echo CB_DIR_AUTO_SUBMISSION_INTERVAL * 1000?>);
<?php }?>
</script>
