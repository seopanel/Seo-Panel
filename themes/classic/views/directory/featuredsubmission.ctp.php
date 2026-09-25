<?php echo showSectionHead($spTextTools['Featured Submission']); ?>
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
