<?php
echo showSectionHead('Two-Factor Authentication');

if (!empty($msg)) { showSuccessMsg($msg, false); }
?>
<table class="list">
	<tr class="listHead">
		<td class="left" width='35%'>Two-Factor Authentication</td>
		<td class="right">&nbsp;</td>
	</tr>
</table>

<?php if (!empty($justEnabled) || !empty($justRegenerated)) { ?>
	<div class="content-section" style="margin-top:15px;">
		<div class="alert alert-warning">
			<strong><?php echo !empty($justEnabled) ? 'Two-factor authentication is now enabled.' : 'Backup codes regenerated.'; ?></strong>
			Save these backup codes somewhere safe - each one can be used once to log in if you lose access to your authenticator app. They will not be shown again.
		</div>
		<div class="sh-command-box" style="background:#f8f9fa; border:1px solid #ddd; border-radius:4px; padding:15px; font-family:monospace; font-size:15px; line-height:1.8;">
			<?php foreach ($backupCodes as $code) { ?>
				<?php echo htmlspecialchars($code); ?><br>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<?php if (!empty($twoFactorEnabled)) { ?>
	<div class="content-section" style="margin-top:15px;">
		<p><i class="fas fa-check-circle" style="color:#28a745;"></i> Two-factor authentication is <strong>enabled</strong> on your account<?php echo !empty($confirmedAt) ? ' since ' . htmlspecialchars(date('d M Y', strtotime($confirmedAt))) : ''; ?>.</p>

		<form method="post" action="<?php echo SP_WEBPATH?>/users.php" style="display:inline-block; margin-right:10px;">
			<input type="hidden" name="sec" value="regenerate-backup-codes">
			<button type="submit" class="btn btn-secondary" onclick="return confirm('This replaces your existing backup codes - any old ones will stop working. Continue?');">
				<i class="fas fa-sync"></i> Regenerate backup codes
			</button>
		</form>

		<form method="post" action="<?php echo SP_WEBPATH?>/users.php" style="display:inline-block;">
			<input type="hidden" name="sec" value="disable-two-factor">
			<label style="display:inline-block; margin-right:8px;">
				Password: <input type="password" name="password" required="required" style="width:160px;">
			</label>
			<?php echo $errMsg['password'] ? $errMsg['password']."<br>" : ""?>
			<button type="submit" class="btn btn-danger" onclick="return confirm('Disable two-factor authentication on your account?');">
				<i class="fas fa-times"></i> Disable
			</button>
		</form>
	</div>
<?php } else { ?>
	<div class="content-section" style="margin-top:15px;">
		<p>Protect your account with an authenticator app (Google Authenticator, Authy, 1Password, etc.). Add this key manually in your app, then enter the 6-digit code it shows to confirm.</p>

		<table class="list">
			<tr class="blue_row">
				<td class="td_left_col" width="35%"><strong>Secret key:</strong></td>
				<td class="td_right_col">
					<code style="font-size:15px; letter-spacing:1px;"><?php echo htmlspecialchars($secretBase32); ?></code>
				</td>
			</tr>
			<tr class="white_row">
				<td class="td_left_col"><strong>Setup link:</strong></td>
				<td class="td_right_col">
					<a href="<?php echo htmlspecialchars($otpAuthUri); ?>" style="word-break:break-all;"><?php echo htmlspecialchars($otpAuthUri); ?></a>
					<div style="font-size:12px; color:#888; margin-top:4px;">Some authenticator apps/password managers can import directly from this link.</div>
				</td>
			</tr>
		</table>

		<form method="post" action="<?php echo SP_WEBPATH?>/users.php" style="margin-top:15px;">
			<input type="hidden" name="sec" value="confirm-two-factor">
			<label>
				Enter the 6-digit code from your app:
				<input type="text" name="code" required="required" placeholder="123456" autocomplete="one-time-code" inputmode="numeric" style="width:120px;">
			</label>
			<?php echo $errMsg['code'] ? "<br>".$errMsg['code'] : ""?>
			<br><br>
			<button type="submit" class="btn btn-primary">
				<i class="fas fa-check"></i> Confirm and enable
			</button>
		</form>
	</div>
<?php } ?>

<table class="actionSec mt-2 float-right">
	<tr>
		<td>
			<a onclick="scriptDoLoad('users.php?sec=my-profile', 'content', 'layout=ajax')" href="javascript:void(0);" class="btn btn-warning">
				Back to My Profile
			</a>
		</td>
	</tr>
</table>
