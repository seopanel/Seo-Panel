<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextDir['Directory Submission Reports']); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#dirReportsHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- quick at-a-glance summary, visible without clicking anything -->
<div class="aiv-intro-card" data-intro-id="dirReportsHelpModal">
	<button type="button" class="aiv-intro-close" onclick="spDismissIntro('dirReportsHelpModal', this)" aria-label="Close"><i class="fas fa-times"></i></button>
	<div class="aiv-intro-icon"><i class="fas fa-table"></i></div>
	<div class="aiv-intro-text">
		<div class="aiv-intro-title">Submission Reports</div>
		<div class="aiv-intro-desc">A history of every directory submission made for your websites, with pending/approved status - filterable by website and status. <a href="javascript:void(0);" data-toggle="modal" data-target="#dirReportsHelpModal" class="aiv-intro-cta">Learn more <i class="fas fa-arrow-right"></i></a></div>
	</div>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="dirReportsHelpModal" tabindex="-1" role="dialog" aria-labelledby="dirReportsHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-table"></i></div>
				<h5 class="modal-title" id="dirReportsHelpModalLabel">About Submission Reports</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>A paginated history of every directory your websites have been submitted to, showing submit time and whether it's still pending or approved. Toggle status or delete entries from here.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>This shows submissions already made - use Automatic Submission to submit to new directories, and Check Submission Status to see if pending ones have been approved since.</span>
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
                <div class="col-md-3">
                    <label class="font-weight-bold"><?php echo $spText['common']['Name']?>:</label>
                    <input type="text" name="search_name" value="<?php echo htmlentities($searchInfo['search_name'], ENT_QUOTES)?>" onblur="<?php echo $onChange?>" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="font-weight-bold"><?php echo $spText['common']['Website']?>:</label>
                    <?php echo $this->render('website/websiteselectbox', 'ajax'); ?>
                </div>
                <div class="col-md-2">
                    <label class="font-weight-bold"><?php echo $spText['common']['Status']?>:</label>
                    <select name="active" onchange="<?php echo $onChange?>" class="custom-select">
                        <option value="">-- Select --</option>
                        <?php
                        $activeList = array('pending', 'approved');
                        foreach($activeList as $val){
                            $sel = ($val == $activeVal) ? 'selected' : ''; ?>
                            <option value="<?php echo $val?>" <?php echo $sel?>><?php echo ucfirst($val)?></option>
                        <?php } ?>
                    </select>
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
    echo "<p class='note error'>".$spText['common']['No Records Found']."!</p>";
    exit;
}
?>

<div id='subcontent'>
<?php echo $pagingDiv?>
<table class="list">
    <tr class="listHead">
        <td class="left"><?php echo $spText['common']['Directory']?></td>
        <td><?php echo $spText['common']['Date']?></td>
        <td class="text-center">PR</td>
        <td class="text-center"><?php echo $spTextDir['Confirmation']?></td>
        <td class="text-center"><?php echo $spText['common']['Status']?></td>
        <td width="10%"><?php echo $spText['common']['Action']?></td>
    </tr>
    <?php
    $colCount = 6;
    if(count($list) > 0) {
        foreach($list as $i => $listInfo) {
            $confirm = empty($listInfo['status']) ? $spText['common']["No"] : $spText['common']["Yes"];
            $confirmClass = empty($listInfo['status']) ? "btn btn-warning btn-sm" : "btn btn-success btn-sm";
            $confirmId = "confirm_".$listInfo['id'];
            $confirmLink = "<a class='$confirmClass' href='javascript:void(0);' onclick=\"scriptDoLoad('directories.php', '$confirmId', 'sec=changeconfirm&id={$listInfo['id']}&confirm=$confirm')\">$confirm</a>";

            $status = empty($listInfo['active']) ? $spTextDir["Pending"] : $spTextDir["Approved"];
            $statusClass = empty($listInfo['active']) ? "badge badge-warning" : "badge badge-success";
            $statusId = "status_".$listInfo['id'];
            ?>
            <tr>
                <td>
                    <a href="<?php echo $listInfo['submit_url']?>" target="_blank"><?php echo $listInfo['domain']?></a>
                </td>
                <td><?php echo date('Y-m-d', $listInfo['submit_time']); ?></td>
                <td class="text-center"><?php echo !empty($listInfo['pagerank']) ? $listInfo['pagerank'] : '&mdash;'?></td>
                <td id='<?php echo $confirmId?>' class="text-center"><?php echo $confirmLink?></td>
                <td id='<?php echo $statusId?>' class="text-center">
                    <span class='<?php echo $statusClass?> py-1 px-2'><?php echo $status?></span>
                </td>
                <td>
                    <select name="action" id="action<?php echo $listInfo['id']?>" onchange="doAction('<?php echo $pageScriptPath?>&pageno=<?php echo $pageNo?>', '<?php echo $statusId?>', 'id=<?php echo $listInfo['id']?>', 'action<?php echo $listInfo['id']?>')" class="custom-select custom-select-sm">
                        <option value="select">-- <?php echo $spText['common']['Select']?> --</option>
                        <option value="checkstatus"><?php echo $spText['button']['Check Status']?></option>
                        <option value="delete"><?php echo $spText['common']['Delete']?></option>
                    </select>
                </td>
            </tr>
            <?php
        }
    }else{
        echo showNoRecordsList($colCount-2);
    }
    ?>
</table>
</div>
