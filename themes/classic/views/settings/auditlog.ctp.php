<?php
echo showSectionHead($spTextPanel['Audit Log'] ?? 'Audit Log');
?>
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
