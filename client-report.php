<?php

/***************************************************************************
 *   Copyright (C) 2009-2011 by Geo Varghese(www.seopanel.org)  	   *
 *   sendtogeo@gmail.com   												   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

// PUBLIC, UNAUTHENTICATED endpoint - the white-label client report link.
// No checkLoggedIn() - same pattern as aivisibility-collect.php. A valid
// token resolves to exactly one website_id/owner, passed into
// ReportController::showOverallReportSummary() as $cronUserId (bypasses
// isLoggedIn(), and switches the view into its existing chrome-free static
// rendering - see the function's own doc comment) and $restrictWebsiteId
// (the real security boundary - pins the report to this one website no
// matter what, closing the "switch to the agency's other clients" gap
// that $websiteList/the website selector would otherwise open up).
include_once("includes/sp-load.php");

include_once(SP_CTRLPATH."/website.ctrl.php");
include_once(SP_CTRLPATH."/report.ctrl.php");
include_once(SP_CTRLPATH."/searchengine.ctrl.php");
include_once(SP_CTRLPATH."/keyword.ctrl.php");
include_once(SP_CTRLPATH."/webmaster.ctrl.php");
include_once(SP_CTRLPATH."/social_media.ctrl.php");
include_once(SP_CTRLPATH."/review_manager.ctrl.php");
include_once(SP_CTRLPATH."/analytics.ctrl.php");

$reportCtrler = New ReportController();

$token = isset($_GET['token']) ? $_GET['token'] : '';
$tokenRow = (preg_match('/^[a-f0-9]{64}$/', $token))
	? $reportCtrler->dbHelper->getRow('report_share_tokens', "token='" . addslashes($token) . "' and revoked=0")
	: null;

$websiteInfo = empty($tokenRow) ? null : $reportCtrler->dbHelper->getRow('websites', "id=" . intval($tokenRow['website_id']));

$custSiteInfo = getCustomizerDetails();
$brandName = !empty($custSiteInfo['site_name']) ? $custSiteInfo['site_name'] : SP_COMPANY_NAME;
$brandLogo = !empty($custSiteInfo['site_logo']) ? $custSiteInfo['site_logo'] : SP_IMGPATH . "/logo_red_sm.png";

?><!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo htmlspecialchars($brandName)?><?php echo empty($websiteInfo) ? '' : ' - ' . htmlspecialchars($websiteInfo['name'])?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/css/bootstrap.min.css" media="all" />
	<link rel="stylesheet" type="text/css" href="<?php echo SP_WEBPATH?>/css/fontawesome/css/all.min.css" media="all" />
	<link rel="stylesheet" type="text/css" href="<?php echo SP_CSSPATH?>/screen.css" media="all" />
	<style>
		body { background: #f4f5f7; }
		.client-report-header { background: #fff; border-bottom: 1px solid #e2e5e9; padding: 16px 24px; margin-bottom: 24px; display: flex; align-items: center; }
		.client-report-header img { max-height: 36px; margin-right: 12px; }
		.client-report-body { max-width: 1100px; margin: 0 auto; padding: 0 16px 48px; }
		.client-report-footer { text-align: center; color: #9296a8; font-size: 12px; padding: 24px 0; }
	</style>
</head>
<body>
<?php if (empty($tokenRow) || empty($websiteInfo)) { ?>
	<div class="client-report-header">
		<img src="<?php echo htmlspecialchars($brandLogo)?>" alt="<?php echo htmlspecialchars($brandName)?>">
		<strong><?php echo htmlspecialchars($brandName)?></strong>
	</div>
	<div class="client-report-body text-center" style="padding-top:80px;">
		<h4>This link is no longer available</h4>
	</div>
<?php } else {
	// bump last_viewed_at - best-effort, never block the report on it
	$reportCtrler->dbHelper->updateRow('report_share_tokens', array('last_viewed_at' => 'NOW()'), "id=" . intval($tokenRow['id']));

	$reportCtrler->spTextTools = $reportCtrler->getLanguageTexts('seotools', $_SESSION['lang_code']);
	$reportCtrler->set('spTextTools', $reportCtrler->spTextTools);
	$reportCtrler->spTextKeyword = $reportCtrler->getLanguageTexts('keyword', $_SESSION['lang_code']);
	$reportCtrler->set('spTextKeyword', $reportCtrler->spTextKeyword);
	$reportCtrler->spTextPanel = $reportCtrler->getLanguageTexts('panel', $_SESSION['lang_code']);
	$reportCtrler->set('spTextPanel', $reportCtrler->spTextPanel);
	?>
	<div class="client-report-header">
		<img src="<?php echo htmlspecialchars($brandLogo)?>" alt="<?php echo htmlspecialchars($brandName)?>">
		<strong><?php echo htmlspecialchars($brandName)?></strong>
	</div>
	<div class="client-report-body">
		<?php
		// showOverallReportSummary() RETURNS the rendered HTML (via
		// getViewContent(), which bypasses layouts entirely - no need for
		// $this->layout here) instead of printing it whenever $cronUserId
		// is set - the same path sentEmailNotificationForReportGen() uses
		// to get report HTML to embed in an email body. Must echo it.
		echo $reportCtrler->showOverallReportSummary(
			array('doc_type' => ''),
			$websiteInfo['user_id'],
			$websiteInfo['id']
		);
		?>
	</div>
	<div class="client-report-footer">Powered by <?php echo htmlspecialchars($brandName)?></div>
<?php } ?>
</body>
</html>
