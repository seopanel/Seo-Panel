<table class="list">
    <tr class="listHead">
        <td><?php echo $spText['common']['Directory']?></td>
        <td class="text-center"><?php echo $spText['common']['Date']?></td>
        <td class="text-center"><?php echo $spTextDir['Confirmation']?></td>
        <td class="text-center"><?php echo $spText['common']['Status']?></td>
    </tr>
    <?php
    $colCount = 4;
    if(count($list) > 0) {
        foreach($list as $listInfo) {
            $confirm = showStatusBadge($listInfo['status'], "yesno");
            $statusId = "status_".$listInfo['id'];
            $checkStatusLink = "<script>scriptDoLoad('directories.php', '$statusId', 'sec=checkstatus&id={$listInfo['id']}');</script>";
            ?>
            <tr>
                <td><?php echo $listInfo['domain']?></td>
                <td class="text-center"><?php echo date('Y-m-d', $listInfo['submit_time']); ?></td>
                <td class="text-center"><?php echo $confirm?></td>
                <td class="text-center" id="<?php echo $statusId?>"><?php echo $checkStatusLink?></td>
            </tr>
            <?php
        }
    }else{
        echo showNoRecordsList($colCount-2);
    }
    ?>
</table>
