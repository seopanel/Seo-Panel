<?php
if ($completed == 1) {
    $submitLink = scriptAJAXLinkHref('siteauditor.php', 'content', "sec=viewreports&project_id=$projectId", $spText['label']['Click Here']);
    showSuccessMsg($spTextSA['Completed project execution']."! $submitLink ".$spTextSA['to view the reports'], false);
} elseif ($completed == -1) {
    echo showErrorMsg($errorMsg, false);
} else {
    if ($projectInfo['check_pr'] || $projectInfo['check_backlinks'] || $projectInfo['check_indexed']) {
        if (SA_CRAWL_DELAY_TIME < 10) {
            $delay = 10 * 1000;
        } else {
            $delay = SA_CRAWL_DELAY_TIME * 1000;
        }
    } else {
        $delay = 2 * 1000;
    }
    echo "<b>'$crawledUrl' {$spTextSA['crawledsuccesssfullywaitfornext']} ".($delay/1000)." seconds</b>";
    ?>
	<script>
 		setTimeout('scriptDoLoad(\'siteauditor.php?sec=runproject&project_id=<?php echo $projectId?>\', \'subcontmed\')', <?php echo $delay?>);
    </script>
	<?php

}
?>
	