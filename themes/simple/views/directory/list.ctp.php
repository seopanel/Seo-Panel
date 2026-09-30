<?php echo showSectionHead($spTextPanel['Directory Manager']); ?>
<div class="card mb-3">
    <div class="card-body">
        <form id='search_form'>
            <?php $submitLink = "scriptDoLoadPost('directories.php', 'search_form', 'content', '&sec=directorymgr')";?>
            <div class="row">
                <div class="col-md-3">
                    <label class="font-weight-bold"><?php echo $spText['common']['Directory']?>:</label>
                    <input type="text" name="dir_name" value="<?php echo htmlentities($info['dir_name'], ENT_QUOTES)?>" onblur="<?php echo $submitLink?>" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold"><?php echo $spText['common']['Status']?>:</label>
                    <select name="stscheck" onchange="<?php echo $submitLink?>" class="custom-select">
                        <?php foreach($statusList as $key => $val){ ?>
                            <option value="<?php echo $val?>" <?php echo ($info['stscheck'] == $val) ? 'selected' : ''?>><?php echo $key?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold"><?php echo $spTextDir['Captcha']?>:</label>
                    <select name="capcheck" onchange="<?php echo $submitLink?>" class="custom-select">
                        <option value="">-- All --</option>
                        <?php foreach($captchaList as $key => $val){ ?>
                            <option value="<?php echo $val?>" <?php echo ($info['capcheck'] == $val) ? 'selected' : ''?>><?php echo $key?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold"><?php echo $spText['common']['Pagerank']?>:</label>
                    <select name="pagerank" onchange="<?php echo $submitLink?>" class="custom-select">
                        <option value="">-- <?php echo $spText['common']['Select']?> --</option>
                        <?php for ($i=0; $i<=10; $i++) {
                            $selected = (($info['pagerank'] != '') && ($i == $info['pagerank'])) ? "selected" : ""; ?>
                            <option value="<?php echo $i?>" <?php echo $selected?>>PR <?php echo $i?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold"><?php echo $spText['common']['lang']?>:</label>
                    <select name="lang_code" onchange="<?php echo $submitLink?>" class="custom-select">
                        <option value="">-- <?php echo $spText['common']['Select']?> --</option>
                        <?php foreach ($langList as $langInfo) {
                            $selected = ($langInfo['lang_code'] == $info['lang_code']) ? "selected" : ""; ?>
                            <option value="<?php echo $langInfo['lang_code']?>" <?php echo $selected?>><?php echo $langInfo['lang_name']?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <a href="javascript:void(0);" onclick="scriptDoLoadPost('directories.php', 'search_form', 'content', '&sec=directorymgr')" class="btn btn-secondary btn-block">
                        <?php echo $spText['button']['Show Records']?>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php echo $pagingDiv?>
<table class="list">
    <tr class="listHead">
        <td><?php echo $spText['common']['Website']?></td>
        <td class="text-center">PR</td>
        <td class="text-center"><?php echo $spText['common']['Domain Authority']?></td>
        <td class="text-center"><?php echo $spText['common']['Page Authority']?></td>
        <td class="text-center"><?php echo $spTextDir['Captcha']?></td>
        <td><?php echo $spText['common']['lang']?></td>
        <td class="text-center"><?php echo $spText['common']['Status']?></td>
        <td class="text-center" style="width: 12%"><?php echo $spText['common']['Action']?></td>
    </tr>
    <?php
    $colCount = 8;
    if(count($list) > 0){
        foreach($list as $listInfo) {
            $statusLink = $ctrler->getStatusLink($listInfo['id'], $listInfo['working']);
            $checkLink = scriptAJAXLinkHref('directories.php', "status_{$listInfo['id']}", "sec=checkdir&dir_id={$listInfo['id']}&nodebug=1&checkpr=1", $spText['button']["Check Status"], "btn btn-info btn-sm");
            $daVal = !empty($listInfo['domain_authority']) ? $listInfo['domain_authority'] : '&mdash;';
            $paVal = !empty($listInfo['page_authority']) ? $listInfo['page_authority'] : '&mdash;';
            $prVal = !empty($listInfo['pagerank']) ? $listInfo['pagerank'] : '&mdash;';
            ?>
            <tr>
                <td><a target="_blank" href="<?php echo $listInfo['submit_url']?>"><?php echo str_replace('http://', '', $listInfo['domain']); ?></a></td>
                <td class="text-center" id="pr_<?php echo $listInfo['id']?>"><?php echo $prVal?></td>
                <td class="text-center" id="da_<?php echo $listInfo['id']?>"><?php echo $daVal?></td>
                <td class="text-center" id="pa_<?php echo $listInfo['id']?>"><?php echo $paVal?></td>
                <td class="text-center" id="captcha_<?php echo $listInfo['id']?>">
                    <?php echo showStatusBadge($listInfo['is_captcha'], "yesno");?>
                </td>
                <td><?php echo $listInfo['lang_name']?></td>
                <td id="status_<?php echo $listInfo['id']?>" class="text-center"><?php echo $statusLink; ?></td>
                <td class="text-center"><?php echo $checkLink; ?></td>
            </tr>
            <?php
        }
    }else{
        echo showNoRecordsList($colCount-2);
    }
    ?>
</table>
