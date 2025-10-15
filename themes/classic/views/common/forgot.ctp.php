<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-5 col-md-7 col-sm-9">
			<div class="forgot-card">
				<div class="forgot-header">
					<div class="forgot-icon">
						<i class="fas fa-key"></i>
					</div>
					<h2 class="forgot-title">
						<?php echo $spText['login']['Forgot password?']?>
					</h2>
					<p class="forgot-subtitle">Enter your email address and we'll send you instructions to reset your password</p>
				</div>

				<div class="forgot-body">
					<form name="loginForm" method="post" action="<?php echo SP_WEBPATH?>/login.php?sec=forgot">
						<input type="hidden" name="sec" value="requestpass">

						<div class="form-group">
							<label for="email" class="forgot-label">
								<i class="fas fa-envelope"></i>
								<?php echo $spText['login']['Email']?>
							</label>
							<input type="email" name="email" value="<?php echo htmlentities($post['email'], ENT_QUOTES)?>" required="required" class="form-control forgot-input" id="email" placeholder="your@email.com" autocomplete="email">
							<?php echo $errMsg['email']?>
						</div>

						<div class="form-group">
							<label class="forgot-label">
								<i class="fas fa-shield-alt"></i>
								Verification
							</label>
							<?php if (SP_ENABLE_RECAPTCHA && !empty(SP_RECAPTCHA_SITE_KEY) && !empty(SP_RECAPTCHA_SECRET_KEY)) {?>
								<script src="https://www.google.com/recaptcha/api.js" async defer></script>
								<div class="captcha-wrapper">
									<div class="g-recaptcha" data-sitekey="<?php echo SP_RECAPTCHA_SITE_KEY?>"></div>
								</div>
							<?php } else {?>
								<div class="captcha-wrapper">
									<img src="<?php echo SP_WEBPATH?>/visual-captcha.php" class="captcha-image">
								</div>
								<input type="text" name="code" value="<?php echo $post['code']?>" required="required" class="form-control forgot-input" id="code" placeholder="Enter the code">
							<?php }?>
							<?php echo $errMsg['code']?>
						</div>

						<?php if (!isLoggedIn()) { ?>
							<div class="forgot-actions">
								<a href="<?php echo SP_WEBPATH?>/login.php" class="btn btn-secondary btn-forgot-cancel">
									<i class="fas fa-arrow-left"></i>
									<?php echo $spText['button']['Cancel']?>
								</a>
								<button name="login" type="submit" class="btn btn-primary btn-forgot-submit">
									<i class="fas fa-paper-plane"></i>
									<?php echo $spText['login']['Request Password']?>
								</button>
							</div>
						<?php }?>
					</form>
				</div>

				<div class="forgot-footer">
					<p class="footer-text">
						<i class="fas fa-info-circle"></i>
						Remember your password?
						<a href="<?php echo SP_WEBPATH?>/login.php" class="signin-link">Sign In</a>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>