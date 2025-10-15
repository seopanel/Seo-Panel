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
    				<i class="fas fa-chart-line"></i> Welcome to SEO Panel
    			</h1>
    			<p class="hero-subtitle">World's First Open Source SEO Control Panel for Multiple Websites</p>
    			<p class="hero-description">
    				A complete open source SEO control panel for managing search engine optimization of your websites.
    				SEO Panel is a powerful toolkit that includes the latest SEO tools to increase and track the performance of your websites.
    			</p>
    			<div class="hero-actions">
    				<a href="login.php" class="btn btn-primary btn-lg">
    					<i class="fas fa-sign-in-alt"></i> Login to Get Started
    				</a>
    				<a href="<?php echo SP_DEMO_LINK?>" target="_blank" class="btn btn-outline-primary btn-lg" rel="nofollow">
    					<i class="fas fa-desktop"></i> View Demo
    				</a>
    			</div>
    		</div>
    	</div>

    	<!-- Features Grid -->
    	<div class="features-section">
    		<h2 class="section-title">
    			<i class="fas fa-rocket"></i> Powerful SEO Features
    		</h2>

    		<div class="features-grid">
    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-search-location"></i>
    				</div>
    				<h3>Keyword Position Checker</h3>
    				<p>Track your keyword rankings across multiple search engines with detailed daily reports and beautiful graphs.</p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-link"></i>
    				</div>
    				<h3>Backlinks Checker</h3>
    				<p>Monitor the number of backlinks from major search engines and track your link building progress over time.</p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-tasks"></i>
    				</div>
    				<h3>Site Auditor</h3>
    				<p>Audit all SEO factors of each page and generate XML, HTML, and TEXT sitemaps for search engines.</p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-chart-bar"></i>
    				</div>
    				<h3>Rank Checker</h3>
    				<p>Check Google PageRank, Alexa Rank, and Moz Rank with comprehensive daily tracking and reporting.</p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-server"></i>
    				</div>
    				<h3>Search Engine Saturation</h3>
    				<p>Find the number of indexed pages across different search engines and monitor your indexing progress.</p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-puzzle-piece"></i>
    				</div>
    				<h3>Plugin Architecture</h3>
    				<p>Extend functionality with powerful plugins including Article Submitter, Meta Tag Generator, and more.</p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-folder-open"></i>
    				</div>
    				<h3>Directory Submission</h3>
    				<p>Automatically submit your websites to major free and paid directories with status tracking.</p>
    			</div>

    			<div class="feature-card">
    				<div class="feature-icon">
    					<i class="fas fa-share-alt"></i>
    				</div>
    				<h3>Social Media Integration</h3>
    				<p>Integrate with Google Analytics, Search Console, and social media platforms for comprehensive reporting.</p>
    			</div>
    		</div>
    	</div>

    	<!-- Why Choose Section -->
    	<div class="why-choose-section">
    		<h2 class="section-title">
    			<i class="fas fa-star"></i> Why Choose SEO Panel?
    		</h2>

    		<div class="why-grid">
    			<div class="why-card">
    				<div class="why-icon">
    					<i class="fas fa-code-branch"></i>
    				</div>
    				<h3>100% Open Source</h3>
    				<p>Free software released under GNU GPL. Download, customize, and use without any restrictions.</p>
    			</div>

    			<div class="why-card">
    				<div class="why-icon">
    					<i class="fas fa-users"></i>
    				</div>
    				<h3>Trusted by Thousands</h3>
    				<p>Since 2010, thousands of webmasters worldwide use SEO Panel to optimize their websites.</p>
    			</div>

    			<div class="why-card">
    				<div class="why-icon">
    					<i class="fas fa-expand-arrows-alt"></i>
    				</div>
    				<h3>Highly Extensible</h3>
    				<p>Easily develop and install custom plugins to extend functionality according to your needs.</p>
    			</div>

    			<div class="why-card">
    				<div class="why-icon">
    					<i class="fas fa-globe"></i>
    				</div>
    				<h3>Multi-Website Support</h3>
    				<p>Manage SEO for unlimited websites from a single control panel with centralized reporting.</p>
    			</div>
    		</div>
    	</div>

    	<!-- Resources Section -->
    	<div class="resources-section">
    		<h2 class="section-title">
    			<i class="fas fa-book"></i> Resources & Support
    		</h2>

    		<div class="resources-grid">
    			<a href="<?php echo SP_DOWNLOAD_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-download"></i>
    				<span>Download SEO Panel</span>
    			</a>

    			<a href="<?php echo SP_DEMO_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-desktop"></i>
    				<span>Try Live Demo</span>
    			</a>

    			<a href="<?php echo SP_HELP_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-question-circle"></i>
    				<span>Documentation</span>
    			</a>

    			<a href="<?php echo SP_SUPPORT_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-life-ring"></i>
    				<span>Get Support</span>
    			</a>

    			<a href="<?php echo SP_PLUGINSITE?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-plug"></i>
    				<span>Browse Plugins</span>
    			</a>

    			<a href="<?php echo SP_CONTACT_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-envelope"></i>
    				<span>Contact Us</span>
    			</a>

    			<a href="<?php echo SP_HOSTED_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-cloud"></i>
    				<span>Cloud Hosted</span>
    			</a>

    			<a href="<?php echo SP_DONATE_LINK?>" target="_blank" class="resource-link" rel="nofollow">
    				<i class="fas fa-heart"></i>
    				<span>Support Development</span>
    			</a>
    		</div>
    	</div>

    	<?php
    }?>
</div>
