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

/**
 * Class defines all functions for managing Site Auditor API
 *
 * Previously Site Auditor data had no standalone API category at all -
 * only reachable (partially) via website.getReportById, which bundles in
 * saturation/pagespeed but never Site Auditor. Reuses
 * SiteAuditorController::__getProjectSummaryForWebsite() - the same
 * consolidated-query summary the MCP server's get_site_auditor_summary
 * tool calls - so this stays a thin wrapper, not a second implementation.
 *
 * @author Seo panel
 *
 */
class SiteauditorAPI extends Seopanel{

	/**
	 * the main controller to get details for api
	 * @var Object
	 */
	var $ctrler;

	/**
	 * The constructor of API
	 */
	function __construct() {
		include_once(SP_CTRLPATH . "/siteauditor.ctrl.php");
		$this->ctrler = new SiteAuditorController();
	}

	/**
	 * function to get Site Auditor's crawl summary (overall score, pages
	 * crawled, broken links, HTTPS/mobile-friendly/backlink/indexed
	 * counts) for a website
	 * @param Array $info			The input details to process the api
	 * 		$info['id']				The id of the website	- Mandatory
	 * @return Array $returnInfo  	Contains the Site Auditor summary, or an error
	 */
	function getReportById($info) {

		$websiteId = intval($info['id']);
		if (empty($websiteId)) {
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = "Please provide a valid website id.";
			return $returnInfo;
		}

		$summary = $this->ctrler->__getProjectSummaryForWebsite($websiteId);
		if (empty($summary)) {
			$returnInfo['response'] = 'Error';
			$returnInfo['error_msg'] = "No Site Auditor project found for this website.";
			return $returnInfo;
		}

		$returnInfo['response'] = 'success';
		$returnInfo['result'] = $summary;
		return $returnInfo;
	}
}
?>
