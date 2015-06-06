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
		$this->highLight('<title>'.$info['title'].'</title>');
		$this->highLight('<meta name="description" content="'.$info['description'].'">');
		$this->highLight('<meta name="keywords" content="'.$info['keywords'].'">');
		if(!empty($info['owner_name'])) $this->highLight('<meta name="author" content="'.$info['owner_name'].'">');
		if(!empty($info['copyright'])) $this->highLight('<meta name="copyright" content="'.$info['copyright'].'">');
		if(!empty($info['owner_email'])) $this->highLight('<meta name="email" content="'.$info['owner_email'].'">');
		if(!empty($info['lang_code'])) $this->highLight('<meta http-equiv="Content-Language" content="'.$info['lang_code'].'">');
		if(!empty($info['charset'])) $this->highLight('<meta name="Charset" content="'.$info['charset'].'">');
		if(!empty($info['rating'])) $this->highLight('<meta name="Rating" content="'.$info['rating'].'">');
		if(!empty($info['distribution'])) $this->highLight('<meta name="Distribution" content="'.$info['distribution'].'">');
		if(!empty($info['robots'])) $this->highLight('<meta name="Robots" content="'.$info['robots'].'">');
		if(!empty($info['revisit-after'])) $this->highLight('<meta name="Revisit-after" content="'.$info['revisit-after'].'">');
		if(!empty($info['expires'])) $this->highLight('<meta name="expires" content="'.$info['expires'].'">');
		$this->highLight('</head>', false);
		print "<textarea style='width:600px;' rows='15'>$this->metaTags</textarea>";
		print "</p>";
	}

	function highLight($str, $padd=true){

		if($padd) $this->metaTags .= "&nbsp;&nbsp;";
		$this->metaTags .= stripslashes($str);
		$this->metaTags .= "\n";
	}
}