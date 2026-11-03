<?php include(SP_VIEWPATH.'/aivisibility/_styles.ctp.php'); ?>
<div class="aiv-page-head">
	<?php echo showSectionHead($spTextPanel['Audit Log'] ?? 'Audit Log'); ?>
	<a href="javascript:void(0);" data-toggle="modal" data-target="#auditLogHelpModal" class="aiv-help-btn"><i class="fas fa-question-circle"></i> What does this do?</a>
</div>

<!-- static help modal, not routed through #ajaxModalSP since there's no AJAX fetch involved -->
<div class="modal fade aiv-help-modal" id="auditLogHelpModal" tabindex="-1" role="dialog" aria-labelledby="auditLogHelpModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="aiv-help-modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="aiv-help-modal-icon"><i class="fas fa-history"></i></div>
				<h5 class="modal-title" id="auditLogHelpModalLabel">About Audit Log</h5>
			</div>
			<div class="modal-body aiv-help-modal-body">
				<p>A record of security-relevant admin actions on this install - who did what, when, and from where. Filterable by action type.</p>
				<div class="aiv-note">
					<i class="fas fa-info-circle"></i>
					<span>Read-only - this page only shows history, it doesn't let you undo or modify past actions.</span>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
			</div>
		</div>
	</div>
</div>

<p style="color:#666; font-size:13px; margin:0 0 10px;">
	A record of security-relevant admin actions - who did what, when, and from where.
</p>

<form method="get" action="javascript:void(0);" style="margin-bottom:10px;">
	<label>
		Filter by action:
		<select name="actionfilter" class="custom-select" style="width:auto; display:inline-block;" onchange="scriptDoLoad('settings.php?sec=auditlog&actionfilter=' + encodeURIComponent(this.value), 'content', 'layout=ajax')">
			<option value="">All actions</option>
			<?php foreach ($actionList as $row) { ?>
				<option value="<?php echo htmlspecialchars($row['action']); ?>" <?php echo ($actionFilter === $row['action']) ? 'selected' : ''; ?>>
					<?php echo htmlspecialchars($row['action']); ?>
				</option>
			<?php } ?>
		</select>
	</label>
</form>

<?php echo $pagingDiv?>
<table class="list">
	<tr class="listHead">
		<td>Time</td>
		<td>Actor</td>
		<td>Action</td>
		<td>Target</td>
		<td>Details</td>
		<td>IP</td>
	</tr>
	<?php if (!empty($auditLogList)) { ?>
		<?php foreach ($auditLogList as $row) { ?>
			<tr>
				<td><?php echo htmlspecialchars($row['created_at']); ?></td>
				<td><?php echo htmlspecialchars($row['actor_username'] ?? '(unknown)'); ?></td>
				<td><?php echo htmlspecialchars($row['action']); ?></td>
				<td>
					<?php if (!empty($row['target_type'])) { ?>
						<?php echo htmlspecialchars($row['target_type']); ?><?php echo !empty($row['target_label']) ? ': ' . htmlspecialchars($row['target_label']) : ''; ?>
					<?php } ?>
				</td>
				<td><?php echo htmlspecialchars($row['details'] ?? ''); ?></td>
				<td><?php echo htmlspecialchars($row['ip_address'] ?? ''); ?></td>
			</tr>
		<?php } ?>
	<?php } else { ?>
		<?php echo showNoRecordsList(6); ?>
	<?php } ?>
</table>
<?php echo $pagingDiv?>
