<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-5 col-md-7 col-sm-9">
			<div class="login-card">
				<div class="login-header">
					<div class="login-icon">
						<i class="fas fa-shield-alt"></i>
					</div>
					<h2 class="login-title">
						<?php echo ucwords($spText['common']['signin'])?>
					</h2>
					<p class="login-subtitle">Welcome back! Please login to your account</p>
				</div>

				<div class="login-body">
					<form name="loginForm" method="post" action="<?php echo SP_WEBPATH?>/login.php">
						<input type="hidden" name="sec" value="login">
						<input type="hidden" name="red_referer" value="<?php echo $post['red_referer']?>">

						<div class="form-group">
							<label for="userName" class="login-label">
								<i class="fas fa-user"></i>
								<?php echo $spText['login']['Username']?>
							</label>
							<input type="text" class="form-control login-input" id="userName" name="userName" required="required" placeholder="Enter your username" autocomplete="username">
							<?php echo $errMsg['userName']?>
						</div>

						<div class="form-group">
							<label for="password" class="login-label">
								<i class="fas fa-lock"></i>
								<?php echo $spText['login']['Password']?>
							</label>
							<input type="password" class="form-control login-input" id="password" name="password" required="required" placeholder="Enter your password" autocomplete="current-password">
							<?php echo $errMsg['password'] ? $errMsg['password']."<br>" : ""?>
						</div>

						<div class="form-group text-right">
							<a href="<?php echo SP_WEBPATH?>/login.php?sec=forgot" class="forgot-link">
								<i class="fas fa-question-circle"></i>
								<?php echo $spText['login']['Forgot password?']?>
							</a>
						</div>

						<button name="login" type="submit" class="btn btn-primary btn-login">
							<i class="fas fa-sign-in-alt"></i>
							<?php echo ucwords($spText['common']['signin'])?>
						</button>

						<?php if(!isLoggedIn() && SP_USER_REGISTRATION){ ?>
							<div class="register-section">
								<span class="register-text">Don't have an account?</span>
								<a href="<?php echo SP_WEBPATH?>/register.php" class="register-link">
									<i class="fas fa-user-plus"></i>
									<?php echo $spText['login']['Register']?>
								</a>
							</div>
						<?php }?>
					</form>
				</div>

				<div class="login-footer">
					<p class="footer-text">
						<i class="fas fa-lock"></i>
						Your information is secure and encrypted
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	scriptDoLoad("<?php echo SP_WEBPATH?>/?sec=sync_all_se", "tmp");
});
</script>