<?php
class MetaTagGenerator extends SeoPluginsController{
	var $metaTags = "";
	
	function index() {
		$this->set('sectionHead', 'Meta Tag Generator');
		$userId = isLoggedIn();
		
		$websiteController = New WebsiteController();
		$this->set('websiteList', $websiteController->__getAllWebsites($userId, true));
		$this->set('websiteNull', true);
		$this->set('onChange', pluginPOSTMethod('search_form', 'subcontent', 'action=show'));
		
		$this->pluginRender('index');
	}
	
	function show($info, $error=false) {
		if(empty($info['website_id'])) {
			print( "<script>".pluginGETMethod()."</script>");
			return;
		}

		$langController = New LanguageController();
		$this->set('langList', $langController->__getAllLanguages());
		$this->set('langNull', true);

		if(empty($error)){
			$websiteController = New WebsiteController();
			$websiteInfo = $websiteController->__getWebsiteInfo($info['website_id']);
			$websiteInfo['website_id'] = $info['website_id'];
			// default the canonical url to the website's own url - the
			// common case (this page IS the canonical version of itself) -
			// the user can still override or blank it out
			if (empty($websiteInfo['canonical_url'])) {
				$websiteInfo['canonical_url'] = $websiteInfo['url'];
			}
		}else{
			$websiteInfo = $info;
		}
		$this->set('websiteInfo', $websiteInfo);

		include_once(SP_CTRLPATH . '/settings.ctrl.php');
		$this->set('localAiAvailable', SettingsController::isLocalAIEnabled());

		$this->pluginRender('showsiteinfo');
	}

	/*
	 * AJAX action: on-demand Local AI (Ollama) suggestion for this
	 * website's title/meta description - see
	 * LocalAIController::suggestMetaTags(). Never auto-fired; returns
	 * JSON for the "Suggest with AI" button in showsiteinfo.ctp.php to
	 * populate the Title/Description fields with (the user still reviews
	 * and can edit before generating/using the actual tags). Ownership is
	 * enforced by suggestMetaTags() itself, not re-checked here.
	 */
	function suggestMetaTags($info) {
		$userId = isLoggedIn();
		include_once(SP_CTRLPATH . '/localai.ctrl.php');
		$result = (new LocalAIController())->suggestMetaTags($info['website_id'], $userId);
		header('Content-Type: application/json');
		print json_encode($result);
	}
	
	function createmetatag($info) {

		$errMsg['title'] = formatErrorMsg($this->validate->checkBlank($info['title']));
		$errMsg['description'] = formatErrorMsg($this->validate->checkBlank($info['description']));
		$errMsg['keywords'] = formatErrorMsg($this->validate->checkBlank($info['keywords']));

		# error occurs
		if($this->validate->flagErr){
			$this->set('errMsg', $errMsg);
			$this->show($info, true);
			return;
		}
		print "<p><b>Meta Tags</b><br><br>";
		$this->highLight('<head>', false);
		// htmlspecialchars() on every user-supplied value below - XSS fix:
		// this used to interpolate $info[...] straight into the generated
		// markup with no escaping at all, so a title/description etc.
		// containing e.g. "><script>...</script> (or, in the textarea
		// further down, a literal </textarea> break-out) executed. Only
		// the user-data portions are escaped, never the surrounding
		// literal tag markup itself.
		// Viewport first, immediately after <head> - matches how browsers/
		// SEO tools expect to find it, and it has no user-supplied content
		// to escape at all: a fixed, essentially-universal value, opt-out
		// (checked by default) rather than opt-in, since a page missing it
		// is the unusual case today, not the normal one.
		if (!isset($info['viewport']) || !empty($info['viewport'])) {
			$this->highLight('<meta name="viewport" content="width=device-width, initial-scale=1">');
		}
		$this->highLight('<title>'.htmlspecialchars($info['title']).'</title>');
		if (!empty($info['canonical_url'])) $this->highLight('<link rel="canonical" href="'.htmlspecialchars($info['canonical_url']).'">');
		$this->highLight('<meta name="description" content="'.htmlspecialchars($info['description']).'">');
		$this->highLight('<meta name="keywords" content="'.htmlspecialchars($info['keywords']).'">');
		if(!empty($info['owner_name'])) $this->highLight('<meta name="author" content="'.htmlspecialchars($info['owner_name']).'">');
		if(!empty($info['copyright'])) $this->highLight('<meta name="copyright" content="'.htmlspecialchars($info['copyright']).'">');
		if(!empty($info['owner_email'])) $this->highLight('<meta name="email" content="'.htmlspecialchars($info['owner_email']).'">');
		if(!empty($info['lang_code'])) $this->highLight('<meta http-equiv="Content-Language" content="'.htmlspecialchars($info['lang_code']).'">');
		if(!empty($info['charset'])) $this->highLight('<meta name="Charset" content="'.htmlspecialchars($info['charset']).'">');
		if(!empty($info['rating'])) $this->highLight('<meta name="Rating" content="'.htmlspecialchars($info['rating']).'">');
		if(!empty($info['distribution'])) $this->highLight('<meta name="Distribution" content="'.htmlspecialchars($info['distribution']).'">');
		if(!empty($info['robots'])) $this->highLight('<meta name="Robots" content="'.htmlspecialchars($info['robots']).'">');
		if(!empty($info['revisit-after'])) $this->highLight('<meta name="Revisit-after" content="'.htmlspecialchars($info['revisit-after']).'">');
		if(!empty($info['expires'])) $this->highLight('<meta name="expires" content="'.htmlspecialchars($info['expires']).'">');

		// Open Graph / Twitter Card tags - what actually controls how a
		// page looks when shared on social media/Slack/etc. today, unlike
		// meta keywords/rating/distribution above (long ignored by search
		// engines, kept only for backwards compatibility). og:title/
		// og:description always emit - falling back to the main title/
		// description above when the dedicated Share Title/Share
		// Description fields are left blank - so every page gets basic OG
		// tags for free with zero extra typing. og:image/og:url have no
		// sensible fallback, so those only appear when explicitly given.
		$ogTitle = !empty($info['og_title']) ? $info['og_title'] : $info['title'];
		$ogDescription = !empty($info['og_description']) ? $info['og_description'] : $info['description'];
		$this->highLight('<meta property="og:type" content="website">');
		$this->highLight('<meta property="og:title" content="'.htmlspecialchars($ogTitle).'">');
		$this->highLight('<meta property="og:description" content="'.htmlspecialchars($ogDescription).'">');
		if (!empty($info['og_image'])) $this->highLight('<meta property="og:image" content="'.htmlspecialchars($info['og_image']).'">');
		if (!empty($info['og_url'])) $this->highLight('<meta property="og:url" content="'.htmlspecialchars($info['og_url']).'">');

		// Twitter Card tags are opt-in (twitter_card left blank = "--
		// None --" by default) since, unlike Open Graph, emitting them
		// unconditionally can change how Twitter/X actually renders a
		// share even when the site owner never intended a card at all.
		if (!empty($info['twitter_card'])) {
			$this->highLight('<meta name="twitter:card" content="'.htmlspecialchars($info['twitter_card']).'">');
			$this->highLight('<meta name="twitter:title" content="'.htmlspecialchars($ogTitle).'">');
			$this->highLight('<meta name="twitter:description" content="'.htmlspecialchars($ogDescription).'">');
			if (!empty($info['og_image'])) $this->highLight('<meta name="twitter:image" content="'.htmlspecialchars($info['og_image']).'">');
		}

		$this->highLight('</head>', false);
		print "<div style='position: relative;'>";
		print "<textarea id='metaTagsTextarea' class='form-control' rows='15'>$this->metaTags</textarea>";
		print "<button type='button' class='btn btn-sm btn-primary' style='position: absolute; top: 5px; right: 5px;' onclick='copyMetaTags()' title='Copy to clipboard'>";
		print "<i class='fa fa-copy'></i> Copy";
		print "</button>";
		print "</div>";
		print "<script>
		function copyMetaTags() {
			var textarea = document.getElementById('metaTagsTextarea');
			var text = textarea.value;

			// Try modern Clipboard API first
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(text).then(function() {
					alert('Meta tags copied to clipboard!');
				}).catch(function(err) {
					// Fallback to old method
					copyFallback(textarea);
				});
			} else {
				// Fallback for older browsers
				copyFallback(textarea);
			}
		}

		function copyFallback(textarea) {
			textarea.select();
			textarea.setSelectionRange(0, 99999);
			try {
				document.execCommand('copy');
				alert('Meta tags copied to clipboard!');
			} catch (err) {
				alert('Failed to copy. Please manually select and copy the text.');
			}
		}
		</script>";
		print "</p>";
	}
	
	function highLight($str, $padd=true){

		if($padd) $this->metaTags .= "&nbsp;&nbsp;";
		$this->metaTags .= stripslashes($str);
		$this->metaTags .= "\n";
	}

	/*
	 * func to show the website selector for the SERP/social preview tool
	 */
	function preview() {
		$this->set('sectionHead', 'SERP & Social Preview');
		$userId = isLoggedIn();

		$websiteController = New WebsiteController();
		$this->set('websiteList', $websiteController->__getAllWebsites($userId, true));
		$this->set('websiteNull', true);
		$this->set('onChange', pluginPOSTMethod('preview_search_form', 'subcontent', 'action=showPreview'));

		$this->pluginRender('preview');
	}

	/*
	 * func to show the live SERP/social preview for one website - starts
	 * from the website's own persisted title/description/url (the same
	 * fields the main Generate Meta Tags tool works from); the preview
	 * itself updates client-side as the fields are edited, no extra
	 * round-trip needed
	 */
	function showPreview($info) {
		if(empty($info['website_id'])) {
			print( "<script>".pluginGETMethod('action=preview')."</script>");
			return;
		}

		$websiteController = new WebsiteController();
		$websiteInfo = $websiteController->__getWebsiteInfo($info['website_id']);
		$websiteInfo['website_id'] = $info['website_id'];
		$this->set('websiteInfo', $websiteInfo);

		$this->pluginRender('showpreview');
	}

	/*
	 * func to show a quick per-website audit of which core meta fields
	 * (title/description/keywords) are present and within recommended
	 * SEO length ranges - only these three are checked because they're
	 * the only ones actually persisted on the websites table itself;
	 * everything else this tool generates (canonical url, viewport,
	 * Open Graph/Twitter fields) is deliberately ephemeral, entered fresh
	 * each time, so there's nothing stored to audit for those.
	 */
	function audit() {
		$this->set('sectionHead', 'Meta Tags Audit');
		$userId = isLoggedIn();

		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);

		$this->pluginRender('audit');
	}

	/*
	 * func to show the plugin's Features & Support page
	 */
	function aboutus() {
		$this->pluginRender('aboutus');
	}
}