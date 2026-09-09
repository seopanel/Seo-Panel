<?php echo showSectionHead($spTextPanel['MCP Access'] ?? 'MCP Access'); ?>

<div class="alert alert-info">
	<?php echo $spTextMyAccount['mcpaccessnotice'] ?? 'Generate a personal access token to let your own AI agent (e.g. Claude Desktop) query your SEO Panel data directly - keyword rankings, backlinks, AI Visibility stats - entirely self-hosted. Nothing leaves your server.'?>
</div>

<?php if (!empty($newToken)) { ?>
	<div class="alert alert-warning">
		<strong><?php echo $spTextMyAccount['mcptokenonceNotice'] ?? 'Copy this token now - it will not be shown again.'?></strong>
		<pre style="background:#1e1e1e;color:#9cdcfe;padding:12px;border-radius:6px;margin-top:8px;word-break:break-all;"><?php echo htmlspecialchars($newToken)?></pre>
		<p style="margin-top:8px;">MCP client config:</p>
		<pre style="background:#1e1e1e;color:#9cdcfe;padding:12px;border-radius:6px;white-space:pre-wrap;word-break:break-all;">{
  "mcpServers": {
    "seopanel": {
      "url": "<?php echo htmlspecialchars(SP_WEBPATH)?>/api/mcp.php",
      "headers": { "Authorization": "Bearer <?php echo htmlspecialchars($newToken)?>" }
    }
  }
}</pre>
	</div>
<?php } ?>

<form id="mcp_create_form" onsubmit="return false;">
	<input type="hidden" name="sec" value="create">
	<input type="text" name="label" class="form-control" style="max-width:300px;display:inline-block;" placeholder="<?php echo $spTextMyAccount['Token Label'] ?? 'Token Label'?>">
	<select name="expires_in" class="custom-select" style="max-width:180px;display:inline-block;">
		<option value="never"><?php echo $spTextMyAccount['Never expires'] ?? 'Never expires'?></option>
		<option value="30d">30 <?php echo $spTextMyAccount['30 days'] ?? '30 days'?></option>
		<option value="90d">90 <?php echo $spTextMyAccount['90 days'] ?? '90 days'?></option>
		<option value="1y"><?php echo $spTextMyAccount['1 year'] ?? '1 year'?></option>
	</select>
	<a href="javascript:void(0);" onclick="scriptDoLoadPost('mcp-access.php', 'mcp_create_form', 'content')" class="btn btn-secondary">
		<?php echo $spTextMyAccount['Generate new token'] ?? 'Generate new token'?>
	</a>
</form>

<table class="list" style="margin-top:15px;">
	<tr class="listHead">
		<td><?php echo $spText['label']['Name'] ?? 'Label'?></td>
		<td><?php echo $spText['common']['Status'] ?? 'Status'?></td>
		<td><?php echo $spTextMyAccount['Expires'] ?? 'Expires'?></td>
		<td><?php echo $spTextMyAccount['Last used'] ?? 'Last used'?></td>
		<td style="width: 15%"><?php echo $spText['common']['Action'] ?? 'Action'?></td>
	</tr>
	<?php if (!empty($tokenList)) { ?>
		<?php foreach ($tokenList as $tokenInfo) {
			$isExpired = !empty($tokenInfo['expires_at']) && strtotime($tokenInfo['expires_at']) < time();
		?>
			<tr>
				<td><?php echo htmlspecialchars($tokenInfo['label'])?></td>
				<td class="text-center">
					<?php if (!empty($tokenInfo['revoked'])) { ?>
						<span class="badge badge-danger py-2 px-3 text-light">Revoked</span>
					<?php } elseif ($isExpired) { ?>
						<span class="badge badge-secondary py-2 px-3 text-light"><?php echo $spTextMyAccount['Expired'] ?? 'Expired'?></span>
					<?php } else { ?>
						<span class="badge badge-success py-2 px-3 text-light">Active</span>
					<?php } ?>
				</td>
				<td><?php echo !empty($tokenInfo['expires_at']) ? htmlspecialchars($tokenInfo['expires_at']) : ($spTextMyAccount['Never expires'] ?? 'Never expires')?></td>
				<td><?php echo !empty($tokenInfo['last_used_at']) ? htmlspecialchars($tokenInfo['last_used_at']) : ($spTextMyAccount['Never'] ?? 'Never')?></td>
				<td class="text-center">
					<?php if (empty($tokenInfo['revoked'])) { ?>
						<form id="mcp_revoke_form_<?php echo $tokenInfo['id']?>" onsubmit="return false;">
							<input type="hidden" name="sec" value="revoke">
							<input type="hidden" name="token_id" value="<?php echo $tokenInfo['id']?>">
						</form>
						<a onclick="confirmSubmit('mcp-access.php', 'mcp_revoke_form_<?php echo $tokenInfo['id']?>', 'content')" href="javascript:void(0);" class="btn btn-danger">
							<?php echo $spTextMyAccount['Revoke'] ?? 'Revoke'?>
						</a>
					<?php } ?>
				</td>
			</tr>
		<?php } ?>
	<?php } else { ?>
		<?php echo showNoRecordsList(3); ?>
	<?php } ?>
</table>
