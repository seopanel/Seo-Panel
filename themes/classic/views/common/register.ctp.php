<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-6 col-md-8 col-sm-10">
			<div class="register-card">
				<div class="register-header">
					<div class="register-icon">
						<i class="fas fa-user-plus"></i>
					</div>
					<h2 class="register-title">
						<?php echo $spText['login']['Create New Account']?>
					</h2>
					<p class="register-subtitle">Join us today and start optimizing your SEO</p>
				</div>

				<div class="register-body">
					<form name="loginForm" method="post" action="<?php echo SP_WEBPATH?>/register.php">
						<input type="hidden" name="sec" value="register">
						<?php
						if (!empty($_GET['failed'])) {
							showErrorMsg($spTextSubscription['internal-error-payment'], false);
						}

						if (!empty($_GET['cancel'])) {
							showErrorMsg($spTextSubscription["Your transaction cancelled"], false);
						}
						?>
		
						<?php
						// if subscription plugin is active
						if ($subscriptionActive & !empty($userTypeList)){
							?>
							<div class="register-section-title">
								<i class="fas fa-credit-card"></i>
								Subscription Details
							</div>

							<div class="form-group">
								<label for="utype_id" class="register-label">
									<i class="fas fa-tag"></i>
									<?php echo $spTextSubscription['Subscription']?>
								</label>
								<select name="utype_id" class="form-control register-input" id="utype_id">
									<?php
									foreach ($userTypeList as $userTypeInfo) {
										$selected = ($post['utype_id'] == $userTypeInfo['id']) ? "selected" : "";
										$typeLabel = ucfirst($userTypeInfo['user_type']) . " - ";

										// if user type have price
										if ($userTypeInfo['price'] > 0) {
											$typeLabel .= $currencyList[SP_PAYMENT_CURRENCY]['symbol'] . $userTypeInfo['price'] . "/" . $spText['label']['Monthly'];
										} else {
											$typeLabel .= $spText['label']['Free'];
										}
										?>
										<option value="<?php echo $userTypeInfo['id']?>" <?php echo $selected;?>><?php echo $typeLabel?></option>
										<?php
									}
									?>
								</select>
								<?php echo $errMsg['utype_id'] ? "<br>". $errMsg['utype_id'] : $errMsg['utype_id']?>
								<div style="margin-top: 8px;">
									<a href="<?php echo SP_WEBPATH . "/register.php?sec=pricing"; ?>" class="pricing-link">
										<i class="fas fa-info-circle"></i>
										<?php echo $spTextSubscription['Plans and Pricing']?>
									</a>
								</div>
							</div>

							<div class="form-group">
								<label for="quantity" class="register-label">
									<i class="fas fa-calendar-alt"></i>
									<?php echo $spTextSubscription['Term']?>
								</label>
								<select name="quantity" class="form-control register-input" id="quantity">
									<?php
									for ($i = 1; $i <= 24; $i++) {
										$qty_label = ($i == 1) ? $spText['label']['Month'] : $spText['label']['Months'];
										?>
										<option value="<?php echo $i;?>"><?php echo $i . " $qty_label";?></option>
										<?php
									}
									?>
								</select>
							</div>

							<?php $pgDisplay = count($pgList) > 1 ? "" : "display:none;";?>
							<div class="form-group" style="<?php echo $pgDisplay?>">
								<label for="pg_id" class="register-label">
									<i class="fas fa-wallet"></i>
									<?php echo $spTextSubscription['Payment Method']?>
								</label>
								<select name="pg_id" class="form-control register-input" id="pg_id">
									<?php
									foreach ($pgList as $pgInfo) {
										$checked = ($defaultPgId == $pgInfo['id']) ? "selected" : ""
										?>
										<option value="<?php echo $pgInfo['id']?>" <?php echo $checked; ?> ><?php echo $pgInfo['name']; ?></option>
										<?php
									}
									?>
								</select>
								<?php echo $errMsg['pg_id']?>
							</div>
							<?php
						} else {
							?>
							<input type="hidden" name="utype_id" value="<?php echo $defaultUserTypeId; ?>">
						<?php }	?>

						<div class="register-section-title">
							<i class="fas fa-user-circle"></i>
							Account Information
						</div>

						<div class="form-group">
							<label for="userName" class="register-label">
								<i class="fas fa-user"></i>
								<?php echo $spText['login']['Username']?>
							</label>
							<input type="text" name="userName" value="<?php echo $post['userName']?>" class="form-control register-input" id="userName" required="required" placeholder="Choose a username" autocomplete="username">
							<?php echo $errMsg['userName']?>
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="password" class="register-label">
										<i class="fas fa-lock"></i>
										<?php echo $spText['login']['Password']?>
									</label>
									<input type="password" name="password" value="" class="form-control register-input" id="password" required="required" placeholder="Enter password" autocomplete="new-password">
									<?php echo $errMsg['password']?>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="confirmPassword" class="register-label">
										<i class="fas fa-lock"></i>
										<?php echo $spText['login']['Confirm Password']?>
									</label>
									<input type="password" name="confirmPassword" value="" class="form-control register-input" id="confirmPassword" required="required" placeholder="Confirm password" autocomplete="new-password">
								</div>
							</div>
						</div>

						<div class="register-section-title">
							<i class="fas fa-id-card"></i>
							Personal Information
						</div>

						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="firstName" class="register-label">
										<i class="fas fa-user"></i>
										<?php echo $spText['login']['First Name']?>
									</label>
									<input type="text" name="firstName" value="<?php echo $post['firstName']?>" class="form-control register-input" id="firstName" required="required" placeholder="First name" autocomplete="given-name">
									<?php echo $errMsg['firstName']?>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									<label for="lastName" class="register-label">
										<i class="fas fa-user"></i>
										<?php echo $spText['login']['Last Name']?>
									</label>
									<input type="text" name="lastName" value="<?php echo $post['lastName']?>" class="form-control register-input" id="lastName" required="required" placeholder="Last name" autocomplete="family-name">
									<?php echo $errMsg['lastName']?>
								</div>
							</div>
						</div>

						<div class="form-group">
							<label for="email" class="register-label">
								<i class="fas fa-envelope"></i>
								<?php echo $spText['login']['Email']?>
							</label>
							<input type="email" name="email" value="<?php echo $post['email']?>" class="form-control register-input" id="email" required="required" placeholder="your@email.com" autocomplete="email">
							<?php echo $errMsg['email']?>
						</div>

						<div class="register-section-title">
							<i class="fas fa-shield-alt"></i>
							Verification
						</div>

						<div class="form-group">
							<?php if (SP_ENABLE_RECAPTCHA && !empty(SP_RECAPTCHA_SITE_KEY) && !empty(SP_RECAPTCHA_SECRET_KEY)) {?>
								<script src="https://www.google.com/recaptcha/api.js" async defer></script>
								<div class="captcha-wrapper">
									<div class="g-recaptcha" data-sitekey="<?php echo SP_RECAPTCHA_SITE_KEY?>"></div>
								</div>
							<?php } else {?>
								<label for="code" class="register-label">
									<i class="fas fa-key"></i>
									<?php echo $spText['login']['Enter the code as it is shown']?>
								</label>
								<div class="captcha-wrapper">
									<img src="<?php echo SP_WEBPATH?>/visual-captcha.php" class="captcha-image">
								</div>
								<input type="text" name="code" value="<?php echo $post['code']?>" class="form-control register-input" id="code" required="required" placeholder="Enter the code">
							<?php }?>
							<?php echo $errMsg['code']?>
						</div>

						<div class="register-actions">
							<a href="<?php echo SP_WEBPATH?>/login.php" class="btn btn-secondary btn-register-cancel">
								<i class="fas fa-times"></i>
								<?php echo $spText['button']['Cancel']?>
							</a>
							<button name="register" type="submit" class="btn btn-primary btn-register-submit">
								<i class="fas fa-user-plus"></i>
								<?php echo $spText['login']['Create my account']?>
							</button>
						</div>
					</form>
				</div>

				<div class="register-footer">
					<p class="footer-text">
						Already have an account?
						<a href="<?php echo SP_WEBPATH?>/login.php" class="login-link">
							<i class="fas fa-sign-in-alt"></i>
							Sign In
						</a>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>