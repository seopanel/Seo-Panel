<div class="col" id="home_screen">
	<?php
	$blogContent = getCustomizerPage('home');
	if (!empty($blogContent['blog_content'])) {
	    echo $blogContent['blog_content'];
	} else {
    	?>
    	<!-- Hero Section -->
    	<div class="home-hero">
    		<div class="home-hero-eyebrow"><?php echo $spTextGuest['Hero eyebrow'] ?? 'Open source &middot; Self-hosted &middot; Built for the AI search era'?></div>
    		<h1 class="hero-title"><?php echo $spTextGuest['Hero title'] ?? 'The SEO &amp; AEO control room you actually own'?></h1>
    		<p class="hero-subtitle">
    			<?php echo $spTextGuest['Hero subtitle v2'] ?? 'Twelve tools in one panel &mdash; rank tracking, site audits, backlinks, and a dedicated AI Visibility suite that tracks how you show up in Google AI Overviews, ChatGPT and other AI engines. All running on your own server, for as many websites as you manage.'?>
    		</p>
    		<div class="hero-actions">
    			<a href="login.php" class="btn btn-primary btn-lg">
    				<i class="fas fa-sign-in-alt"></i> <?php echo $spTextGuest['Login to Get Started']?>
    			</a>
    			<a href="<?php echo SP_DEMO_LINK?>" target="_blank" class="btn btn-outline-primary btn-lg" rel="nofollow">
    				<i class="fas fa-desktop"></i> <?php echo $spTextGuest['View Demo']?>
    			</a>
    		</div>

    		<div class="home-stats">
    			<div>
    				<span class="home-stat-value">12</span>
    				<span class="home-stat-label"><?php echo $spTextGuest['stat-tools-label'] ?? 'SEO tools included'?></span>
    			</div>
    			<div>
    				<span class="home-stat-value">2010</span>
    				<span class="home-stat-label"><?php echo $spTextGuest['stat-since-label'] ?? 'Building in the open'?></span>
    			</div>
    			<div>
    				<span class="home-stat-value">&infin;</span>
    				<span class="home-stat-label"><?php echo $spTextGuest['stat-sites-label'] ?? 'Websites per install'?></span>
    			</div>
    			<div>
    				<span class="home-stat-value">100%</span>
    				<span class="home-stat-label"><?php echo $spTextGuest['stat-oss-label'] ?? 'Open source (GPL)'?></span>
    			</div>
    		</div>
    	</div>

    	<!-- Features Grid -->
    	<div class="features-section">
    		<div class="section-eyebrow"><?php echo $spTextGuest['toolkit-eyebrow'] ?? 'The toolkit'?></div>
    		<h2 class="section-title"><?php echo $spTextGuest['Powerful SEO Features']?></h2>
    		<p class="section-desc"><?php echo $spTextGuest['toolkit-desc'] ?? 'From daily rank tracking to how your pages show up inside AI answer engines - no separate subscriptions, no exporting between tools.'?></p>

    		<div class="features-grid">
    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-robot"></i>
    				</div>
    				<h3><?php echo $spTextTools['ai-visibility']?></h3>
    				<p><?php echo $spTextGuest['AI Visibility desc'] ?? 'See how you show up in Google AI Overviews, ChatGPT and other AI engines - referral traffic, bot crawls, and citation rate in one score.'?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-search-location"></i>
    				</div>
    				<h3><?php echo $spTextTools['keyword-position-checker']?></h3>
    				<p><?php echo $spTextGuest['Keyword Position Checker desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-tools"></i>
    				</div>
    				<h3><?php echo $spTextTools['webmaster-tools']?></h3>
    				<p><?php echo $spTextGuest['Webmaster Tools desc'] ?? 'Pull real Google Search Console clicks, impressions and average position straight into your reports.'?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-tasks"></i>
    				</div>
    				<h3><?php echo $spTextTools['site-auditor']?></h3>
    				<p><?php echo $spTextGuest['Site Auditor desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-chart-bar"></i>
    				</div>
    				<h3><?php echo $spTextTools['rank-checker']?></h3>
    				<p><?php echo $spTextGuest['Rank Checker desc v2'] ?? 'Check Domain Authority, Page Authority and Spam Score via Moz, with history tracked for every website.'?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-link"></i>
    				</div>
    				<h3><?php echo $spTextTools['backlink-checker']?></h3>
    				<p><?php echo $spTextGuest['Backlinks Checker desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-folder-open"></i>
    				</div>
    				<h3><?php echo $spTextTools['directory-submission']?></h3>
    				<p><?php echo $spTextGuest['Directory Submission desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-server"></i>
    				</div>
    				<h3><?php echo $spTextTools['saturation-checker']?></h3>
    				<p><?php echo $spTextGuest['Search Engine Saturation desc']?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-tachometer-alt"></i>
    				</div>
    				<h3><?php echo $spTextTools['pagespeed']?></h3>
    				<p><?php echo $spTextGuest['PageSpeed Insights desc'] ?? 'Measure real Google PageSpeed scores for desktop and mobile, and track performance changes over time.'?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-share-alt"></i>
    				</div>
    				<h3><?php echo $spTextTools['sm-checker']?></h3>
    				<p><?php echo $spTextGuest['Social Media Checker desc'] ?? 'Track followers, shares and engagement across your social profiles from a single dashboard.'?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-chart-area"></i>
    				</div>
    				<h3><?php echo $spTextTools['web-analytics']?></h3>
    				<p><?php echo $spTextGuest['Website Analytics desc'] ?? 'Connect Google Analytics to see traffic sources, sessions and conversions next to your SEO data.'?></p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-star"></i>
    				</div>
    				<h3><?php echo $spTextTools['review-manager']?></h3>
    				<p><?php echo $spTextGuest['Review Manager desc'] ?? 'Monitor reviews and ratings across Google, Yelp, Trustpilot and other platforms from a single view.'?></p>
    			</div>
    		</div>
    	</div>

    	<!-- Why Choose Section -->
    	<div class="why-choose-section">
    		<div class="section-eyebrow"><?php echo $spTextGuest['why-eyebrow'] ?? 'Why teams run it themselves'?></div>
    		<h2 class="section-title"><?php echo $spTextGuest['Why Choose SEO Panel?']?></h2>

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
    		<h2 class="section-title"><?php echo $spTextGuest['Resources & Support']?></h2>

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

    	<!-- Closing CTA -->
    	<div class="home-final-cta">
    		<h2><?php echo $spTextGuest['final-cta-title'] ?? 'Run your own SEO stack, starting today'?></h2>
    		<p><?php echo $spTextGuest['final-cta-desc'] ?? 'Free to install, free to extend, and it stays on your own server.'?></p>
    		<a href="login.php" class="btn">
    			<?php echo $spTextGuest['Login to Get Started']?> <i class="fas fa-arrow-right"></i>
    		</a>
    	</div>
    	<?php
    }?>
</div>
