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
		}else{
			$websiteInfo = $info;
		}
		$this->set('websiteInfo', $websiteInfo);
		
		$this->pluginRender('showsiteinfo');
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
		$this->highLight('<title>'.htmlspecialchars($info['title']).'</title>');
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
}