<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-5 col-md-7 col-sm-9">
			<div class="login-card">
				<div class="login-header">
					<div class="login-icon">
						<i class="fas fa-mobile-alt"></i>
					</div>
					<h2 class="login-title">Two-Factor Authentication</h2>
					<p class="login-subtitle">Enter the 6-digit code from your authenticator app, or one of your backup codes.</p>
				</div>

				<div class="login-body">
					<form name="twoFactorForm" method="post" action="<?php echo SP_WEBPATH?>/login.php">
						<input type="hidden" name="sec" value="verify_2fa">

						<div class="form-group">
							<label for="code" class="login-label">
								<i class="fas fa-key"></i>
								Code
							</label>
							<input type="text" class="form-control login-input" id="code" name="code" required="required" placeholder="123456" autocomplete="one-time-code" inputmode="numeric" autofocus>
							<?php echo $errMsg['code'] ? $errMsg['code']."<br>" : ""?>
						</div>

						<button name="verify" type="submit" class="btn btn-primary btn-login">
							<i class="fas fa-check"></i>
							Verify
						</button>

						<div class="register-section">
							<a href="<?php echo SP_WEBPATH?>/login.php?sec=cancel_2fa" class="register-link">
								<i class="fas fa-arrow-left"></i>
								Back to login
							</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
