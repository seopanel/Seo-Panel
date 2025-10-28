<div class="col" id="home_screen">
	<?php
	$blogContent = getCustomizerPage('home');
	if (!empty($blogContent['blog_content'])) {
	    echo $blogContent['blog_content'];
	} else {

	    // add no follow option to SP links
	    $spTextHome['home_cont1'] = str_replace('<a ', '<a rel="nofollow" ', $spTextHome['home_cont1']);
	    $spTextHome['home_cont2'] = str_replace('<a ', '<a rel="nofollow" ', $spTextHome['home_cont2']);
	    $spTextHome['home_cont3'] = str_replace('<a ', '<a rel="nofollow" ', $spTextHome['home_cont3']);
    	?>

    	<!-- Hero Section -->
    	<div class="home-hero">
    		<div class="hero-content">
    			<h1 class="hero-title">
    				<i class="fas fa-chart-line"></i> <?php echo $spTextGuest['Welcome to SEO Panel']?>
    			</h1>
    			<p class="hero-subtitle"><?php echo $spTextGuest['Hero subtitle']?></p>
    			<p class="hero-description">
    				<?php echo $spTextGuest['Hero description']?>
    			</p>
    			<div class="hero-actions">
    				<a href="login.php" class="btn btn-primary btn-lg">
    					<i class="fas fa-sign-in-alt"></i> <?php echo $spTextGuest['Login to Get Started']?>
    				</a>
    				<a href="<?php echo SP_DEMO_LINK?>" target="_blank" class="btn btn-outline-primary btn-lg" rel="nofollow">
    					<i class="fas fa-desktop"></i> <?php echo $spTextGuest['View Demo']?>
    				</a>
    			</div>
    		</div>
    	</div>

    	<!-- Features Grid -->
    	<div class="features-section">
    		<h2 class="section-title">
    			<i class="fas fa-rocket"></i> <?php echo $spTextGuest['Powerful SEO Features']?>
    		</h2>

    		<div class="features-grid">
    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-search-location"></i>
    				</div>
    				<h3><?php echo $spText['seotools']['keyword-position-checker']?></h3>
    				<p><?php echo $spTextGuest['Keyword Position Checker desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-link"></i>
    				</div>
    				<h3><?php echo $spText['seotools']['backlink-checker']?></h3>
    				<p><?php echo $spTextGuest['Backlinks Checker desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-tasks"></i>
    				</div>
    				<h3><?php echo $spText['seotools']['site-auditor']?></h3>
    				<p><?php echo $spTextGuest['Site Auditor desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-chart-bar"></i>
    				</div>
    				<h3><?php echo $spText['seotools']['rank-checker']?></h3>
    				<p><?php echo $spTextGuest['Rank Checker desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-server"></i>
    				</div>
    				<h3><?php echo $spText['seotools']['saturation-checker']?></h3>
    				<p><?php echo $spTextGuest['Search Engine Saturation desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-puzzle-piece"></i>
    				</div>
    				<h3><?php echo $spTextGuest['Plugin Architecture']?></h3>
    				<p><?php echo $spTextGuest['Plugin Architecture desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-folder-open"></i>
    				</div>
    				<h3><?php echo $spText['home']['Directory Submission']?></h3>
    				<p><?php echo $spTextGuest['Directory Submission desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-share-alt"></i>
    				</div>
    				<h3><?php echo $spTextGuest['Social Media Integration']?></h3>
    				<p><?php echo $spTextGuest['Social Media Integration desc']?></p>
    			</div>
    		</div>
    	</div>

    	<!-- Why Choose Section -->
    	<div class="why-choose-section">
    		<h2 class="section-title">
    			<i class="fas fa-star"></i> <?php echo $spTextGuest['Why Choose SEO Panel?']?>
    		</h2>

    		<div class="why-grid">
    			<div class="why-card">
    				<div class="why-icon">
    					<i class="fas fa-code-branch"></i>
    				</div>
    				<h3><?php echo $spTextGuest['100% Open Source']?></h3>
    				<p><?php echo $spTextGuest['100% Open Source desc']?></p>
    			</div>

    			<div class="why-card">
    				<div class="why-icon">
    					<i class="fas fa-users"></i>
    				</div>
    				<h3><?php echo $spTextGuest['Trusted by Thousands']?></h3>
    				<p><?php echo $spTextGuest['Trusted by Thousands desc']?></p>
    			</div>

    			<div class="why-card">
    				<div class="why-icon">
    					<i class="fas fa-expand-arrows-alt"></i>
    				</div>
    				<h3><?php echo $spTextGuest['Highly Extensible']?></h3>
    				<p><?php echo $spTextGuest['Highly Extensible desc']?></p>
    			</div>

    			<div class="why-card">
    				<div class="why-icon">
    					<i class="fas fa-globe"></i>
    				</div>
    				<h3><?php echo $spTextGuest['Multi-Website Support']?></h3>
    				<p><?php echo $spTextGuest['Multi-Website Support desc']?></p>
    			</div>
    		</div>
    	</div>

    	<!-- Resources Section -->
    	<div class="resources-section">
    		<h2 class="section-title">
    			<i class="fas fa-book"></i> <?php echo $spTextGuest['Resources & Support']?>
    		</h2>

    		<div class="resources-grid">
    			<a href="<?php echo SP_DOWNLOAD_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-download"></i>
    				<span><?php echo $spTextGuest['Download SEO Panel']?></span>
    			</a>

    			<a href="<?php echo SP_HELP_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-question-circle"></i>
    				<span><?php echo $spTextGuest['Documentation']?></span>
    			</a>

    			<a href="<?php echo SP_SUPPORT_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-life-ring"></i>
    				<span><?php echo $spTextGuest['Get Support']?></span>
    			</a>

    			<a href="<?php echo SP_PLUGINSITE?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-plug"></i>
    				<span><?php echo $spTextGuest['Browse Plugins']?></span>
    			</a>

    			<a href="<?php echo SP_CONTACT_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-envelope"></i>
    				<span><?php echo $spTextGuest['Contact Us']?></span>
    			</a>

    			<a href="<?php echo SP_HOSTED_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-cloud"></i>
    				<span><?php echo $spTextGuest['Cloud Hosted']?></span>
    			</a>

    			<a href="<?php echo SP_DONATE_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-heart"></i>
    				<span><?php echo $spTextGuest['Support Development']?></span>
    			</a>
    		</div>
    	</div>

    	<?php
    }?>
</div>
