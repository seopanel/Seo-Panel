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

include_once(SP_CTRLPATH."/keyword.ctrl.php");
include_once(SP_CTRLPATH."/moz.ctrl.php");

# class defines all cron controller functions
class CronController extends Controller {
    
	var $cronList;			    // the array includes all tools avialable for cron
	var $repTools;			    // the array includes all tools avialable for report generation
	var $debug = true;		    // to show debug message or not
	var $layout = 'ajax';       // ajax layout or not
	var $timeStamp;             // timestamp for storing reports
	var $checkedKeywords = 0;   // the number of keywords checked in cron, this is used for split cron execution feature
	var $checkedWebsites = 0;   // the number of websites checked in cron, this is used for split cron execution feature
	var $currentRunId;          // cron_run_log.id for the in-progress run, set by startRunLog()
	var $currentRunWebsiteCount = 0; // websites routed through routeCronJob() during the current run
	var $stopChunkDrain = false;    // set true inside a drainChunkQueue() closure to stop claiming further chunks (e.g. API limit exceeded)
	var $deadline = null;           // Phase 2: epoch seconds wall-clock budget for a ping-triggered run; null = unbounded (CLI behavior, unchanged)

	/**
	 * Non-blocking MySQL advisory lock so overlapping cron.php invocations
	 * don't duplicate work. Released automatically if the connection dies
	 * (process killed, fatal error, die()), so it can never be left stuck
	 * held by a crashed process - this is why GET_LOCK is used instead of
	 * a file lock (see Zero-Setup Scheduler spec discovery notes).
	 */
	function acquireSchedulerLock($timeoutSec = 0) {
		$result = $this->db->select("SELECT GET_LOCK('seopanel_scheduler', $timeoutSec) as locked", true);
		return !empty($result['locked']);
	}

	function releaseSchedulerLock() {
		$this->db->query("SELECT RELEASE_LOCK('seopanel_scheduler')");
	}

	# func to open a cron_run_log row for the current invocation
	function startRunLog($triggerSource = 'cli') {
		// insertRow()/escapeValue() already addslashes() string values - do not pre-escape here
		$this->dbHelper->insertRow('cron_run_log', [
			'trigger_source' => $triggerSource,
			'started_at'     => 'NOW()',
			'status'         => 'running',
		]);
		$this->currentRunId = intval($this->dbHelper->dbConObj->lastInsertId);
	}

	/**
	 * Close the current run's log row. Idempotent - only the first call
	 * (whether the normal end-of-script call, or the shutdown-function
	 * fallback for a run that died mid-way) actually updates the row,
	 * since the WHERE clause only matches while status is still 'running'.
	 */
	function finishRunLog($status = 'completed') {
		if (empty($this->currentRunId)) return;
		$status = addslashes($status);
		$this->db->query("UPDATE cron_run_log
			SET finished_at = NOW(),
			    duration_ms = TIMESTAMPDIFF(MICROSECOND, started_at, NOW()) / 1000,
			    status = '$status',
			    websites_processed = " . intval($this->currentRunWebsiteCount) . "
			WHERE id = " . intval($this->currentRunId) . " AND status = 'running'");
	}

	/**
	 * Run one tool's cron method with timing + failure isolation. A
	 * Throwable here is caught and recorded rather than left to abort the
	 * rest of this website's (and every subsequent website's) cron run -
	 * today nothing catches these at all, so one tool's exception kills
	 * everything queued after it for the remainder of the process.
	 */
	function __runCronJob($websiteId, $urlSection, $methodName, $args = []) {
		$start = microtime(true);
		$status = 'success';
		$errorMessage = null;

		try {
			call_user_func_array([$this, $methodName], $args);
		} catch (Throwable $e) {
			$status = 'failed';
			$errorMessage = $e->getMessage();
			$this->debugMsg("Cron job failed - tool: $urlSection, website: $websiteId - " . $e->getMessage() . "\n");
		}

		$durationMs = intval((microtime(true) - $start) * 1000);

		if (!empty($this->currentRunId)) {
			// insertRow()/escapeValue() already addslashes() string values - do not pre-escape here
			$this->dbHelper->insertRow('cron_job_timing', [
				'run_id|int'     => $this->currentRunId,
				'website_id|int' => intval($websiteId),
				'url_section'    => $urlSection,
				'started_at'     => date('Y-m-d H:i:s', (int) $start),
				'duration_ms|int'=> $durationMs,
				'status'         => $status,
				// isDBConstantValue() only recognises the literal string
				// 'NULL' (not PHP null) as an unquoted SQL NULL
				'error_message'  => is_null($errorMessage) ? 'NULL' : $errorMessage,
			]);
		}
	}

	/*
	 * Record a scheduled report-generation cycle as successfully
	 * completed - only ever called from executeCron() after the email
	 * (when one was required) actually sent, or wasn't required in the
	 * first place. Pure DB writes, no crawling/network - independently
	 * unit-testable, unlike executeCron() itself.
	 */
	function __recordReportGenerationSuccess($reportCtrler, $userInfo, $lastGenerated) {
		$reportCtrler->updateUserReportSetting($userInfo['id'], 'last_generated', $lastGenerated);
		$reportCtrler->updateUserReportGenerationLogs($userInfo['id'], date('Y-m-d H:i:s'));

		$reportTxt = $this->getLanguageTexts('reports', $_SESSION['lang_code']);
		$alertCtrl = new AlertController();
		$alertInfo = array(
			'alert_subject' => $reportTxt["Reports Generated Successfully"],
			'alert_message' => $reportTxt['report_email_subject'],
			'alert_category' => "reports",
			'alert_url' => SP_WEBPATH,
		);
		$alertCtrl->createAlert($alertInfo, $userInfo['id']);
	}

	/*
	 * Record a scheduled report's email delivery failure. Deliberately
	 * does NOT touch last_generated/user_report_logs - leaving
	 * last_generated unchanged is what makes the NEXT cron pass retry
	 * this same period, rather than the failure being silently skipped
	 * until the following interval. createAlert() already dedupes per
	 * (user, subject, day), so repeated retries within the same day
	 * don't spam the customer with duplicate failure alerts.
	 */
	function __recordReportGenerationFailure($userInfo) {
		$reportTxt = $this->getLanguageTexts('reports', $_SESSION['lang_code']);
		$alertCtrl = new AlertController();
		$alertInfo = array(
			'alert_subject' => $reportTxt['Report Email Failed'] ?? 'Report Email Failed',
			'alert_message' => $reportTxt['report_email_failed_message'] ?? 'Your scheduled SEO report was generated but could not be emailed - it will be retried automatically.',
			'alert_category' => "reports",
			'alert_url' => SP_WEBPATH,
		);
		$alertCtrl->createAlert($alertInfo, $userInfo['id']);
		$this->debugMsg("Report email failed for user {$userInfo['id']} - last_generated left unchanged, will retry next run\n");
	}


	/**
	 * Zero-Setup Scheduler, Phase 1: resumable job queue primitives.
	 *
	 * Chunk rows are recycled by (website_id, url_section, chunk_key)
	 * rather than inserted fresh per cron cycle - enqueueChunks() only
	 * revives a 'completed' row back to 'pending' when the caller's own
	 * is-due logic decides the same chunk needs to run again, and never
	 * disturbs a 'running' row or a 'failed' row still in its backoff
	 * window. $chunkMap is [chunk_key => payload-or-null].
	 */
	function enqueueChunks($urlSection, $websiteId, array $chunkMap) {
		$websiteId = intval($websiteId);
		$urlSectionEsc = addslashes($urlSection);

		foreach ($chunkMap as $chunkKey => $payload) {
			$chunkKeyEsc = addslashes($chunkKey);
			$payloadSql = is_null($payload) ? 'NULL' : "'" . addslashes(json_encode($payload)) . "'";

			$this->db->query("
				INSERT INTO job_queue (website_id, url_section, chunk_key, payload, status, available_at)
				VALUES ($websiteId, '$urlSectionEsc', '$chunkKeyEsc', $payloadSql, 'pending', NOW())
				ON DUPLICATE KEY UPDATE
					status = IF(status = 'completed', 'pending', status),
					available_at = IF(status = 'completed', NOW(), available_at),
					payload = $payloadSql,
					updated_at = NOW()
			");
		}
	}

	# revert chunks whose claiming process died mid-way (stuck 'running' too long) back to 'pending'
	function reapStaleChunks($staleMinutes = 15) {
		$staleMinutes = intval($staleMinutes);
		$this->db->query("
			UPDATE job_queue SET status = 'pending', available_at = NOW()
			WHERE status = 'running' AND claimed_at < (NOW() - INTERVAL $staleMinutes MINUTE)
		");
	}

	/**
	 * Prune finished job_queue rows past retention - 'pending'/'running'
	 * rows are NEVER touched (that's live work). A pruned 'completed' row
	 * is functionally equivalent to keeping it: enqueueChunks()'s INSERT
	 * ... ON DUPLICATE KEY UPDATE just inserts a fresh 'pending' row next
	 * time that chunk_key is due, same as reviving the old one would have.
	 * A pruned 'failed' row is actually a small improvement over keeping
	 * it: enqueueChunks() only revives a 'completed' row to 'pending', not
	 * a 'failed' one (`status = IF(status = 'completed', 'pending',
	 * status)`), so a chunk that permanently failed had no path back to
	 * ever running again short of an admin manually deleting the row -
	 * pruning it after the retention window gives it exactly that path,
	 * for free.
	 */
	function pruneOldJobQueueRows() {
		$retentionDays = defined('SP_JOB_QUEUE_RETENTION_DAYS') ? intval(SP_JOB_QUEUE_RETENTION_DAYS) : 7;
		if ($retentionDays <= 0) return;
		$cutoff = date('Y-m-d H:i:s', strtotime("-$retentionDays days"));
		$this->db->query("DELETE FROM job_queue WHERE status IN ('completed','failed') AND updated_at < '$cutoff'");
	}

	# func to prune old cron_run_log rows past the configured retention window
	function pruneOldRunLogs() {
		$retentionDays = defined('SP_CRON_RUN_LOG_RETENTION_DAYS') ? intval(SP_CRON_RUN_LOG_RETENTION_DAYS) : 30;
		if ($retentionDays <= 0) return;
		$cutoff = date('Y-m-d H:i:s', strtotime("-$retentionDays days"));
		$this->db->query("DELETE FROM cron_run_log WHERE started_at < '$cutoff'");
	}

	// func to prune old cron_job_timing rows - the highest-volume of the
	// three scheduler tables (one row per tool per website per run), so
	// the shortest default retention; kept in sync with cron_run_log by
	// also deleting any row whose run_id no longer has a parent (covers
	// the gap between the two tables' own retention windows)
	function pruneOldJobTimingRows() {
		$retentionDays = defined('SP_CRON_JOB_TIMING_RETENTION_DAYS') ? intval(SP_CRON_JOB_TIMING_RETENTION_DAYS) : 14;
		if ($retentionDays > 0) {
			$cutoff = date('Y-m-d H:i:s', strtotime("-$retentionDays days"));
			$this->db->query("DELETE FROM cron_job_timing WHERE started_at < '$cutoff'");
		}
		$this->db->query("DELETE ct FROM cron_job_timing ct LEFT JOIN cron_run_log rl ON rl.id = ct.run_id WHERE rl.id IS NULL");
	}

	# atomically claim the oldest pending+due chunk for (website, tool); returns null when none left
	function claimNextChunk($urlSection, $websiteId) {
		$websiteId = intval($websiteId);
		$urlSectionEsc = addslashes($urlSection);

		$candidate = $this->db->select("
			SELECT id FROM job_queue
			WHERE website_id = $websiteId AND url_section = '$urlSectionEsc'
			  AND status = 'pending' AND available_at <= NOW()
			ORDER BY id ASC LIMIT 1
		", true);

		if (empty($candidate['id'])) return null;

		$chunkId = intval($candidate['id']);
		$this->db->query("
			UPDATE job_queue SET status = 'running', claimed_at = NOW(),
				claimed_by_run_id = " . intval($this->currentRunId) . ", attempts = attempts + 1
			WHERE id = $chunkId AND status = 'pending'
		");

		$row = $this->dbHelper->getRow('job_queue', "id = $chunkId");
		if (empty($row) || $row['status'] != 'running') return null;

		if (!empty($row['payload'])) $row['payload'] = json_decode($row['payload'], true);
		return $row;
	}

	function completeChunk($chunkId) {
		$this->dbHelper->updateRow('job_queue',
			['status' => 'completed', 'completed_at' => 'NOW()'], 'id=' . intval($chunkId));
	}

	# terminal 'failed' once attempts is exhausted, otherwise 'pending' with backoff (5 / 25 / 125 min)
	function failChunk($chunkId, $errorMessage) {
		$chunkId = intval($chunkId);
		$row = $this->dbHelper->getRow('job_queue', "id=$chunkId");
		if (empty($row)) return;

		if (intval($row['attempts']) >= intval($row['max_attempts'])) {
			$this->dbHelper->updateRow('job_queue',
				['status' => 'failed', 'last_error' => $errorMessage], "id=$chunkId");
			return;
		}

		$backoffMinutes = 5 * pow(5, intval($row['attempts']) - 1); // 5, 25, 125
		$errorMessageEsc = addslashes($errorMessage);
		$this->db->query("
			UPDATE job_queue SET status = 'pending',
				available_at = NOW() + INTERVAL $backoffMinutes MINUTE,
				last_error = '$errorMessageEsc'
			WHERE id = $chunkId
		");
	}

	/**
	 * Shared drain loop: claim -> process -> complete/fail, until the
	 * queue for this (tool, website) is empty. This is what every
	 * refactored *CronQueued() method calls after its own enqueueChunks().
	 * A Throwable from $processFn fails only that one chunk - it does not
	 * abort the remaining chunks, unlike the old die()/foreach model.
	 */
	function drainChunkQueue($urlSection, $websiteId, callable $processFn) {
		$this->reapStaleChunks();
		$this->stopChunkDrain = false;

		while (!$this->stopChunkDrain && (is_null($this->deadline) || microtime(true) < $this->deadline) && ($chunk = $this->claimNextChunk($urlSection, $websiteId))) {

			// SP_NUMBER_KEYWORDS_CRON split-execution cap, generalised from
			// the old die()-based limit: stop claiming new keyword chunks
			// once the per-run cap is hit, but let the run continue
			// normally (cron.php's tail cleanup still runs) - remaining
			// chunks simply stay 'pending' for the next invocation.
			if ($urlSection == 'keyword-position-checker' && SP_NUMBER_KEYWORDS_CRON > 0
				&& $this->checkedKeywords >= SP_NUMBER_KEYWORDS_CRON) {
				// chunk was already claimed (status='running') above; hand it back
				$this->dbHelper->updateRow('job_queue', ['status' => 'pending'], 'id=' . intval($chunk['id']));
				$this->debugMsg("Reached total number of allowed keywords(" . SP_NUMBER_KEYWORDS_CRON . ") in each cron job\n");
				break;
			}

			try {
				$processFn($chunk);
				$this->completeChunk($chunk['id']);
				if ($urlSection == 'keyword-position-checker') $this->checkedKeywords++;
			} catch (Throwable $e) {
				$this->failChunk($chunk['id'], $e->getMessage());
				$this->debugMsg("Chunk failed ($urlSection, website $websiteId, chunk {$chunk['chunk_key']}): {$e->getMessage()}\n");
			}
		}
	}

	# function to load all tools required for report generation 
	function loadReportGenerationTools($includeList=array()){
		$includeList = formatSQLParamList($includeList);
		$sql = "select * from seotools where status=1 and reportgen=1";
		if(count($includeList) > 0) $sql .= " and id in (".implode(',', $includeList).")";
		$this->repTools = $this->db->select($sql);
	}
	
	# function to load all tools required for cron job
	function loadCronJobTools($includeList=array()){
		$sql = "select * from seotools where status=1 and cron=1";
		if(count($includeList) > 0) $sql .= " and id in (".implode(',', $includeList).")";
		$sql .= " order by id ASC";
		$this->cronList = $this->db->select($sql);
	}	
	
	# function to show report generation manager
	function showReportGenerationManager(){
		
		$userId = isLoggedIn();
		$websiteController = New WebsiteController();
		$websiteList = $websiteController->__getAllWebsites($userId, true);
		$this->set('websiteList', $websiteList);
		$this->set('websiteNull', false);
		
		$this->loadReportGenerationTools();
		$this->set('repTools', $this->repTools);
		
		$this->render('report/reportgenerationmanager');
	}

	# function to show cron command
	function showCronCommand(){
		
		$this->render('report/croncommand');
	}
	
	# common report generation function
	function executeReportGenerationScript($info=[]) {	    
	    if(empty($info['repTools']) || count($info['repTools']) <= 0){
			showErrorMsg($this->spTextKeyword['pleaseselecttool']."!");
		}
		
		$websiteCtrler = New WebsiteController();
		if(!empty($info['website_id'])){
			$allWebsiteList[] = $websiteCtrler->__getWebsiteInfo($info['website_id']);
		}else{
			$userCtrler = New UserController();
			$userList = $userCtrler->__getAllUsers();		
			$allWebsiteList = array();
			foreach($userList as $userInfo){
				
				$websiteList = $websiteCtrler->__getAllWebsites($userInfo['id']);			
				foreach($websiteList as $websiteInfo){
					$allWebsiteList[] = $websiteInfo;				
				}
			}	
		}

		if(count($allWebsiteList) <= 0){
			showErrorMsg($_SESSION['text']['common']['nowebsites']."!");
		}
		
		$this->set('allWebsiteList', $allWebsiteList);
		$this->set('repTools', implode(':', $info['repTools']));
		$this->render('report/reportgenerator');
	}
	
	// common cron execute function
	function executeCron($includeList=array(), $userSelectList=array()) {
		$this->loadCronJobTools($includeList);
		$lastGenerated = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		
		$userCtrler = New UserController();
		
		// if user list selected is not empty
		if (!empty($userSelectList)) {
			$userList = $userSelectList;
		} else {
			$userList = $userCtrler->__getAllUsers(true, true, "utype_id DESC");
		}
		
		foreach($userList as $userInfo){
			
			// check whethere user id is existing
			if (empty($userInfo['id'])) continue;
			
			// check whether user expired 
			if (!$userCtrler->isUserExpired($userInfo['id'])) {
				continue;
			}
		    
		    // create report controller
		    $reportCtrler = New ReportController();
		    
		    // check for user report schedule
		    $repSetInfo = $reportCtrler->isGenerateReportsForUser($userInfo['id']);
		    
			if (!empty($repSetInfo['generate_report'])) {
			    $websiteCtrler = New WebsiteController();
			    $sql = "select * from websites where status=1 and user_id=" . $userInfo['id'] . " and crawled=0 order by name";
			    $websiteList = $websiteCtrler->db->select($sql);
			    $websiteCount = count($websiteList);
    			
    			// if websites are available
    			if ($websiteCount > 0) {
        			foreach($websiteList as $websiteInfo){
        				// Phase 2: stop starting new websites once a ping-triggered run's
        				// budget is spent. Mirrors the SP_NUMBER_WEBSITES_CRON cap below:
        				// return (not break) so this skips report-generation bookkeeping
        				// and the global crawled-status reset for a partial run, exactly
        				// like that cap already does - remaining websites stay un-crawled
        				// for the next invocation.
        				if (!is_null($this->deadline) && microtime(true) >= $this->deadline) {
        					return;
        				}

        				$this->websiteInfo = $websiteInfo;
        				$this->routeCronJob($websiteInfo['id'], '', true);
        				$this->checkedWebsites++;
        				
        				// change website crawl status
        				$sql = "update websites set crawled=1 where id=" . $websiteInfo['id'];
        				$websiteList = $websiteCtrler->db->query($sql);
        				
        				// if all websites checked, mark as report generated for the day
        				if ($this->checkedWebsites != $websiteCount) {
        					        				
	        				// to implement split cron execution feature
	        				if ( SP_NUMBER_WEBSITES_CRON > 0) {        					
	        					if ($this->checkedWebsites == SP_NUMBER_WEBSITES_CRON) {
	        						$this->debugMsg("Reached total number of allowed websites(" . SP_NUMBER_WEBSITES_CRON. ") in each cron job\n");
	        						return; // return (not die()) so cron.php's tail cleanup still runs on this invocation
	        					}
	        				}
	        				
        				}
        				
        			}

        			// Send the email FIRST (when required) and only record this
        			// cycle as done - advance last_generated, log it, raise the
        			// "success" alert - if it actually succeeded, or wasn't
        			// required in the first place. Previously all three were
        			// written unconditionally before sendMail() was even called,
        			// so a failed send (SMTP/SendGrid outage) was reported to the
        			// customer as success, silently dropped, and never retried -
        			// the interval gate had already advanced past it. On failure,
        			// last_generated is left untouched so the next cron pass
        			// (which, with the ping/beacon triggers, may be minutes away
        			// rather than a full interval) retries the same period, and a
        			// distinct alert is raised so the failure isn't invisible -
        			// createAlert() already dedupes per (user, subject, day), so
        			// repeated retries within the same day don't spam it.
        			$emailRequired = SP_REPORT_EMAIL_NOTIFICATION && $repSetInfo['email_notification'];
        			$emailSucceeded = true;
        			if ($emailRequired) {
        				$reportCtrler->spTextTools = $this->getLanguageTexts('seotools', $_SESSION['lang_code']);
        				$reportCtrler->set('spTextTools', $reportCtrler->spTextTools);
        				$emailSucceeded = $reportCtrler->sentEmailNotificationForReportGen($userInfo, $repSetInfo['last_generated'], $lastGenerated);
        			}

        			if ($emailSucceeded) {
        				$this->__recordReportGenerationSuccess($reportCtrler, $userInfo, $lastGenerated);
        			} else {
        				$this->__recordReportGenerationFailure($userInfo);
        			}

    			}

			}
		}
		
		// if user selected list empty
		if (empty($userSelectList)) {
			
			// reset all keywords crawl status
			$keywordCtrler = New KeywordController();
			$keywordCtrler->__changeCrawledStatus(0);
			$this->debugMsg("Reset all keywords crawl status\n");
	
			// change all website crawl status
			$sql = "update websites set crawled=0";
			$keywordCtrler->db->query($sql);
			$this->debugMsg("Change all websites crawl status\n");
		}
		
	}
	
	// function to route the cronjobs to different methods
	function routeCronJob($websiteId, $repTools='', $cron=false) {
		$websiteId = intval($websiteId);
		if(empty($this->websiteInfo)){
			$websiteCtrler = New WebsiteController();
			$this->websiteInfo = $websiteCtrler->__getWebsiteInfo($websiteId);
		}
		
		if($cron){			
			if(empty($this->cronList)){
				$this->loadCronJobTools();
			}
			
			$seoTools = $this->cronList;	
		}else{			
			$this->loadReportGenerationTools(explode(':', $repTools));
			$seoTools = $this->repTools;
		}
		
		// check whethre user access to seo tools and plugins
		$userCtrler = New UserController();
		$userInfo = $userCtrler->__getUserInfo($this->websiteInfo['user_id']);
		$userTypeCtrler = new UserTypeController();
		
		// check whethere user is admin
		if ($userInfo['utype_id'] == $userTypeCtrler->getAdminUserTypeId()) {
		    $isAdmin = true;
		} else {
		    $isAdmin = false;
		    $toolAccessList = $userTypeCtrler->getSeoToolAccessSettings($userInfo['utype_id']);
		}
		
		if ($cron) {
			$this->currentRunWebsiteCount++;
		}

		foreach ($seoTools as $cronInfo) {

		    // Phase 2: stop starting new tools once a ping-triggered run's
		    // budget is spent. Remaining tools for this website are simply
		    // untouched this pass - their own is-due checks (or job_queue
		    // pending rows) pick them up again next run.
		    if (!is_null($this->deadline) && microtime(true) >= $this->deadline) {
		        break;
		    }

		    // check whether user have acccess to the tool
		    if (!$isAdmin && empty($toolAccessList[$cronInfo['id']]['value']) ) {
		        continue;
		    }

			// Zero-Setup Scheduler, Phase 1: SP_JOB_QUEUE_ENABLED selects between
			// each tool's old monolithic body and its new enqueue+drain body.
			// Temporary dual-path for one release cycle - see plan notes.
			switch($cronInfo['url_section']){

				case "webmaster-tools":
					$method = SP_JOB_QUEUE_ENABLED ? 'webmasterToolsCronQueued' : 'webmasterToolsCron';
					$this->__runCronJob($websiteId, 'webmaster-tools', $method, [$websiteId]);
					break;

				case "keyword-position-checker":
					// Check search volumes via DataForSEO (if enabled) or SP API (if configured).
					// Run before keywordPositionCheckerCron(), which can die() early once
					// SP_NUMBER_KEYWORDS_CRON is reached, so search volume still gets a turn.
					include_once(SP_CTRLPATH . "/settings.ctrl.php");
					if (SettingsController::isDFSEnabled('search_volume') || SettingsController::isSpApiEnabled('search_volume')) {
						$svMethod = SP_JOB_QUEUE_ENABLED ? 'searchVolumeCheckerCronQueued' : 'searchVolumeCheckerCron';
						$this->__runCronJob($websiteId, 'search-volume', $svMethod, [$websiteId]);
					}
					$kpcMethod = SP_JOB_QUEUE_ENABLED ? 'keywordPositionCheckerCronQueued' : 'keywordPositionCheckerCron';
					$this->__runCronJob($websiteId, 'keyword-position-checker', $kpcMethod, [$websiteId]);
					break;

				case "rank-checker":
					$method = SP_JOB_QUEUE_ENABLED ? 'rankCheckerCronQueued' : 'rankCheckerCron';
					$this->__runCronJob($websiteId, 'rank-checker', $method, [$websiteId]);
					break;

				case "backlink-checker":
					$method = SP_JOB_QUEUE_ENABLED ? 'backlinkCheckerCronQueued' : 'backlinkCheckerCron';
					$this->__runCronJob($websiteId, 'backlink-checker', $method, [$websiteId]);
					break;

				case "saturation-checker":
					$method = SP_JOB_QUEUE_ENABLED ? 'saturationCheckerCronQueued' : 'saturationCheckerCron';
					$this->__runCronJob($websiteId, 'saturation-checker', $method, [$websiteId]);
					break;

				case "pagespeed":
					$method = SP_JOB_QUEUE_ENABLED ? 'pageSpeedCheckerCronQueued' : 'pageSpeedCheckerCron';
					$this->__runCronJob($websiteId, 'pagespeed', $method, [$websiteId]);
					break;

				case "sm-checker":
					$method = SP_JOB_QUEUE_ENABLED ? 'socialMediaCheckerCronQueued' : 'socialMediaCheckerCron';
					$this->__runCronJob($websiteId, 'sm-checker', $method, [$websiteId]);
					break;

				case "review-manager":
					$method = SP_JOB_QUEUE_ENABLED ? 'reviewCheckerCronQueued' : 'reviewCheckerCron';
					$this->__runCronJob($websiteId, 'review-manager', $method, [$websiteId]);
					break;

				case "web-analytics":
					$method = SP_JOB_QUEUE_ENABLED ? 'analyticsCronQueued' : 'analyticsCron';
					$this->__runCronJob($websiteId, 'web-analytics', $method, [$websiteId]);
					break;
			}
		}

	}
	
	# func to generate search engine saturation reports from cron
	function saturationCheckerCron($websiteId){
		
		include_once(SP_CTRLPATH."/saturationchecker.ctrl.php");
		$this->debugMsg("Starting Search engine saturation Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$saturationCtrler = New SaturationCheckerController();
		$websiteInfo = $this->websiteInfo;
		
		if (SP_MULTIPLE_CRON_EXEC && $saturationCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp)) return;
		
		$saturationCtrler->url = $websiteUrl = addHttpToUrl($websiteInfo['url']);			
		foreach ($saturationCtrler->colList as $col => $dbCol) {
			$websiteInfo[$col] = $saturationCtrler->__getSaturationRank($col, true);
		}
			
		$saturationCtrler->saveRankResults($websiteInfo, true);			
		echo "Saved Search Engine Saturation results of <b>$websiteUrl</b>.....</br>\n";
		
	}
	
	# func to generate pagespeed reports from cron
	function pageSpeedCheckerCron($websiteId){
	
		include_once(SP_CTRLPATH."/pagespeed.ctrl.php");
		$this->debugMsg("Starting page speed Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
	
		$pageSpeedCtrler = New PageSpeedController();
		$websiteInfo = $this->websiteInfo;
	
		if (SP_MULTIPLE_CRON_EXEC && $pageSpeedCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp)) return;
		
		$userCtrler = new UserController();
		$userInfo = $userCtrler->__getUserInfo($websiteInfo['user_id']);
		$langCode = $userInfo['lang_code'];
		
		$websiteUrl = addHttpToUrl($websiteInfo['url']);
		$params = array('screenshot' => false, 'strategy' => 'desktop', 'locale' => $langCode);
		$websiteInfo['desktop'] = $pageSpeedCtrler->__getPageSpeedInfo($websiteUrl, $params);
		$params = array('screenshot' => false, 'strategy' => 'mobile', 'locale' => $langCode);
		$websiteInfo['mobile'] = $pageSpeedCtrler->__getPageSpeedInfo($websiteUrl, $params);
		
		$pageSpeedCtrler->savePageSpeedResults($websiteInfo, true);
		echo "Saved page speed results of <b>$websiteUrl</b>.....</br>\n";
	
	}
	
	# func to generate social media checker reports from cron
	function socialMediaCheckerCron($websiteId){
	
		include_once(SP_CTRLPATH."/social_media.ctrl.php");
		$this->debugMsg("Starting social media Checker cron for website: {$this->websiteInfo['name']}....<br>\n");
	
		$socialMediaCtrler = New SocialMediaController();
		$websiteInfo = $this->websiteInfo;
		
		$linkList = $socialMediaCtrler->getAllLinksWithOutReports($websiteInfo['id'], date('Y-m-d', $this->timeStamp));
		if (SP_MULTIPLE_CRON_EXEC && empty($linkList)) {
			$this->debugMsg("No social media links left to generate report for website: {$this->websiteInfo['name']}....<br>\n");
			return true;
		}
		
		// loop through link list and save the data
		foreach ($linkList as $linkInfo) {
			$result = $socialMediaCtrler->getSocialMediaDetails($linkInfo['type'], $linkInfo['url']);
			
			if ($result['status']) {
				echo "Crawled social media results of <b>{$linkInfo['name']}</b>.....</br>\n";
			} else {
				echo "Failed Crawling of social media results of <b>{$linkInfo['name']}</b>.....</br>\n";
				echo $result['msg'];
			}
			
			// save the social media data
			$socialMediaCtrler->saveSocialMediaLinkResults($linkInfo['id'], $result);
			sleep(SP_CRAWL_DELAY + 5);
		}
		
		echo "Saved social media results of website id: <b>$websiteId</b>.....</br>\n";
	
	}
	
	# func to generate review checker reports from cron
	function reviewCheckerCron($websiteId) {
		include_once(SP_CTRLPATH."/review_manager.ctrl.php");
		$this->debugMsg("Starting review Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$reviewController = New ReviewManagerController();
		$websiteInfo = $this->websiteInfo;
		$reportDate = date('Y-m-d', $this->timeStamp);

		// Check if DFS Review is enabled
		$useDFS = SettingsController::isDFSEnabled('review');

		if ($useDFS) {
			// Post DFS tasks for supported platforms (google, trustpilot, tripadvisor)
			// Results will be fetched at the end of cron job via processPendingDFSTasks()
			$linksNeedingTask = $reviewController->getLinksNeedingDFSTaskPost($websiteInfo['id'], $reportDate);
			foreach ($linksNeedingTask as $linkInfo) {
				$taskResult = $reviewController->postReviewTaskToDFS($linkInfo['id'], $linkInfo['type'], $linkInfo['url'], $reportDate);
				if ($taskResult['status']) {
					echo "Posted DFS task for <b>{$linkInfo['name']}</b> ({$linkInfo['type']}).....</br>\n";
				} else {
					echo "Failed posting DFS task for <b>{$linkInfo['name']}</b>: {$taskResult['message']}.....</br>\n";
				}
				sleep(1); // Small delay between API calls
			}

			// Process Yelp links using old scraping method (not supported by DFS)
			$yelpLinks = $reviewController->getYelpLinksWithOutReports($websiteInfo['id'], $reportDate);
			foreach ($yelpLinks as $linkInfo) {
				$result = $reviewController->getReviewDetails($linkInfo['type'], $linkInfo['url']);
				if ($result['status']) {
					echo "Crawled review results of <b>{$linkInfo['name']}</b> (Yelp).....</br>\n";
				} else {
					echo "Failed Crawling of review results of <b>{$linkInfo['name']}</b> (Yelp).....</br>\n";
					echo $result['msg'];
				}
				$reviewController->saveReviewLinkResults($linkInfo['id'], $result);
				sleep(SP_CRAWL_DELAY + 5);
			}
		} else {
			// DFS not enabled - use old scraping method for all platforms
			$linkList = $reviewController->getAllLinksWithOutReports($websiteInfo['id'], $reportDate);
			if (SP_MULTIPLE_CRON_EXEC && empty($linkList)) {
				$this->debugMsg("No review links left to generate report for website: {$this->websiteInfo['name']}....<br>\n");
				return true;
			}

			// loop through link list and save the data
			foreach ($linkList as $linkInfo) {
				$result = $reviewController->getReviewDetails($linkInfo['type'], $linkInfo['url']);

				if ($result['status']) {
					echo "Crawled review results of <b>{$linkInfo['name']}</b>.....</br>\n";
				} else {
					echo "Failed Crawling of review results of <b>{$linkInfo['name']}</b>.....</br>\n";
					echo $result['msg'];
				}

				// save the review data
				$reviewController->saveReviewLinkResults($linkInfo['id'], $result);
				sleep(SP_CRAWL_DELAY + 5);
			}
		}

		echo "Saved review results of website id: <b>$websiteId</b>.....</br>\n";
	}	
	
	# func to generate backlink reports from cron
	function backlinkCheckerCron($websiteId) {
		include_once(SP_CTRLPATH."/backlink.ctrl.php");
		include_once(SP_CTRLPATH."/rank.ctrl.php");
		$this->debugMsg("Starting Backlink Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$backlinkCtrler = New BacklinkController();
		$websiteInfo = $this->websiteInfo;

		if (SP_MULTIPLE_CRON_EXEC && $backlinkCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp)) return;

		$websiteUrl = addHttpToUrl($websiteInfo['url']);
		$mozCtrler = new MozController();
		$mozRankInfo = $mozCtrler->__getMozRankInfo(array($websiteUrl));

		// Extract backlink data from Moz API (default / fallback source)
		$websiteInfo['external_pages_to_page'] = !empty($mozRankInfo[0]['external_pages_to_page']) ? $mozRankInfo[0]['external_pages_to_page'] : 0;
		$websiteInfo['external_pages_to_root_domain'] = !empty($mozRankInfo[0]['external_pages_to_root_domain']) ? $mozRankInfo[0]['external_pages_to_root_domain'] : 0;

		// DataForSEO backlink summary, when enabled, overrides the backlink-specific
		// metrics above with real link-graph data. The Moz call above still runs
		// unconditionally since Rank Checker depends on its domain/page authority
		// data separately - this only replaces the backlink-count fields.
		include_once(SP_CTRLPATH."/settings.ctrl.php");
		if (SettingsController::isDFSEnabled('backlink')) {
			include_once(SP_CTRLPATH."/dataforseo.ctrl.php");
			$dfsCtrler = new DataForSEOController();
			$dfsSummary = $dfsCtrler->__getBacklinkSummary($websiteUrl);
			if (!empty($dfsSummary)) {
				$websiteInfo['external_pages_to_page'] = $dfsSummary['backlinks'];
				$websiteInfo['external_pages_to_root_domain'] = $dfsSummary['referring_domains'];
				$websiteInfo['broken_backlinks'] = $dfsSummary['broken_backlinks'];
			}
		}

		$backlinkCtrler->saveRankResults($websiteInfo, true);
		$this->debugMsg("Saved backlink results of <b>$websiteUrl</b>.....<br>\n");

		// Also save rank data from Moz API
		$rankCtrler = New RankController();
		$websiteInfo['spam_score'] = !empty($mozRankInfo[0]['spam_score']) ? $mozRankInfo[0]['spam_score'] : 0;
		$websiteInfo['page_authority'] = !empty($mozRankInfo[0]['page_authority']) ? $mozRankInfo[0]['page_authority'] : 0;
		$websiteInfo['domain_authority'] = !empty($mozRankInfo[0]['domain_authority']) ? $mozRankInfo[0]['domain_authority'] : 0;
		$rankCtrler->saveRankResults($websiteInfo, true);
		$this->debugMsg("Saved rank results of <b>$websiteUrl</b>.....<br>\n");
	}	
	
	// func to generate rank reports from cron
	function rankCheckerCron($websiteId) {
		include_once(SP_CTRLPATH."/rank.ctrl.php");
		include_once(SP_CTRLPATH."/backlink.ctrl.php");
		$this->debugMsg("Starting Rank Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$rankCtrler = New RankController();
		$websiteInfo = $this->websiteInfo;
		if (SP_MULTIPLE_CRON_EXEC && $rankCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp)) {
		    return;
		}

		$websiteUrl = addHttpToUrl($websiteInfo['url']);
		$mozCtrler = new MozController();
		$mozRankInfo = $mozCtrler->__getMozRankInfo(array($websiteUrl));

		$websiteInfo['spam_score'] = !empty($mozRankInfo[0]['spam_score']) ? $mozRankInfo[0]['spam_score'] : 0;
		$websiteInfo['page_authority'] = !empty($mozRankInfo[0]['page_authority']) ? $mozRankInfo[0]['page_authority'] : 0;
		$websiteInfo['domain_authority'] = !empty($mozRankInfo[0]['domain_authority']) ? $mozRankInfo[0]['domain_authority'] : 0;
		$rankCtrler->saveRankResults($websiteInfo, true);
		$this->debugMsg("Saved rank results of <b>$websiteUrl</b>.....<br>\n");

		// Save backlink results from Moz data
		$backlinkCtrler = New BacklinkController();
		$websiteInfo['external_pages_to_page'] = !empty($mozRankInfo[0]['external_pages_to_page']) ? $mozRankInfo[0]['external_pages_to_page'] : 0;
		$websiteInfo['external_pages_to_root_domain'] = !empty($mozRankInfo[0]['external_pages_to_root_domain']) ? $mozRankInfo[0]['external_pages_to_root_domain'] : 0;
		$backlinkCtrler->saveRankResults($websiteInfo, true);
		$this->debugMsg("Saved backlink results of <b>$websiteUrl</b>.....<br>\n");
	}
	
	# func to check search volume for all active keywords of a website
	# Priority: DataForSEO (live) > SP API
	function searchVolumeCheckerCron($websiteId) {
		include_once(SP_CTRLPATH . "/spapi.ctrl.php");
		include_once(SP_CTRLPATH . "/settings.ctrl.php");

		$useDFS = SettingsController::isDFSEnabled('search_volume');
		$source = 'search volume';

		if ($useDFS) {
			include_once(SP_CTRLPATH . "/dataforseo.ctrl.php");
			$dfsCtrler  = new DataForSEOController();
			$source = 'DataForSEO';
		}

		$spapiCtrler = new SPAPIController();
		$this->debugMsg("Starting Search Volume cron via <b>$source</b> for website: {$this->websiteInfo['name']}....<br>\n");

		// Sync interval: 30 days, based on crawled_time (matches manager's ARCHIVE_SEARCH_VOL_SYNC_INTERVAL)
		$sql = "SELECT k.* FROM keywords k
		        LEFT JOIN keyword_search_volume sv ON sv.keyword_id = k.id AND sv.source = 'google'
		        WHERE k.website_id=" . intval($websiteId) . " AND k.status=1
		        AND (sv.crawled_time IS NULL OR (sv.crawled_time + INTERVAL 30 DAY) < NOW())
		        ORDER BY k.id";
		$keywordList = $this->db->select($sql);

		if (empty($keywordList)) {
			$this->debugMsg("Search Volume: No keywords need updating for <b>{$this->websiteInfo['name']}</b>....<br>\n");
			return;
		}

		foreach ($keywordList as $keywordInfo) {

			if ($useDFS) {
				// --- DataForSEO path ---
				$dfsResult = $dfsCtrler->getSearchVolumeFromDFS($keywordInfo);

				if ($dfsResult['status'] && !empty($dfsResult['data'])) {
					$spapiCtrler->saveKeywordSearchVolumeData($keywordInfo['id'], 'google', $dfsResult['data'], 'success');
					$sv = number_format($dfsResult['data']['search_volume'] ?? 0);
					$this->debugMsg("DFS: Search volume <b>$sv</b> for <b>{$keywordInfo['name']}</b>.....<br>\n");
				} else {
					$spapiCtrler->saveKeywordSearchVolumeData($keywordInfo['id'], 'google', null, 'fail');
					$this->debugMsg("DFS: Search volume failed for <b>{$keywordInfo['name']}</b>: {$dfsResult['message']}.....<br>\n");
				}

			} else {
				// --- SP API path ---
				$apiResult = $spapiCtrler->postSearchVolumeKeyword($keywordInfo, 'google');

				if ($apiResult['status'] && !empty($apiResult['data'])) {
					$spapiCtrler->saveSearchVolumeResult($keywordInfo['id'], $apiResult['data']);
					$status = $apiResult['data']['mapping']['last_crawl_status'] ?? 'pending';
					$this->debugMsg("SP API: Search volume ({$status}) for <b>{$keywordInfo['name']}</b>.....<br>\n");
				} else {
					$this->debugMsg("SP API: Search volume failed for <b>{$keywordInfo['name']}</b>: {$apiResult['message']}.....<br>\n");
					$spapiCtrler->saveKeywordSearchVolumeData($keywordInfo['id'], 'google', null, 'fail');

					if (stripos($apiResult['message'], 'limit exceeded') !== false || stripos($apiResult['message'], 'limit reached') !== false) {
						include_once(SP_CTRLPATH . "/alerts.ctrl.php");
						$alertCtrler = new AlertController();
						$alertCtrler->createAlert([
							'alert_subject'  => 'SP API Search Volume Limit Reached',
							'alert_message'  => 'Monthly search volume limit exceeded. Upgrade your plan to continue.',
							'alert_url'      => SP_WEBPATH . '/admin-panel.php?menu_selected=settings&start_script=settings&category=seopanel_api',
							'alert_type'     => 'warning',
							'alert_category' => 'general',
						], false, true);
						break;
					}
				}
			}

			sleep(1);
		}

		echo "Saved search volume results for website: <b>{$this->websiteInfo['name']}</b>.....</br>\n";
	}

	# func to find the keyword position checker
	function keywordPositionCheckerCron($websiteId){

		include_once(SP_CTRLPATH."/searchengine.ctrl.php");
		include_once(SP_CTRLPATH."/report.ctrl.php");

		$reportController = New ReportController();
		$keywordCtrler = New KeywordController();

		$seController = New SearchEngineController();
		$reportController->seList = $seController->__getAllCrawlFormatedSearchEngines();

		$this->debugMsg("Starting keyword position checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		// Determine SERP source: 3-tier priority (dataforseo > spapi > crawl)
		$serpSource = 'crawl';

		// Tier 2: SP API (if configured, overrides crawl)
		include_once(SP_CTRLPATH."/spapi.ctrl.php");
		if (SPAPIController::isConfigured()) {
			$serpSource = 'spapi';
		}

		// Tier 1: DataForSEO (highest priority, overrides all)
		if (SettingsController::isDFSEnabled('serp')) {
			$serpSource = 'dataforseo';
		}

		$this->debugMsg("Using SERP source: <b>$serpSource</b>....<br>\n");

		switch ($serpSource) {

			case 'dataforseo':
				// DFS enabled - Post tasks for keywords (results will be fetched at end of cron)
				include_once(SP_CTRLPATH."/dataforseo.ctrl.php");
				$dfsCtrler = new DataForSEOController();
				$reportDate = date('Y-m-d', $this->timeStamp);

				// Get keywords needing SERP task posting
				$keywordsNeedingTask = $dfsCtrler->getKeywordsNeedingSERPTaskPost($websiteId, $reportDate);

				foreach ($keywordsNeedingTask as $taskItem) {
					$keywordInfo = $taskItem['keyword_info'];
					$keywordInfo['depth'] = $taskItem['depth'];

					$taskResult = $dfsCtrler->postSERPTask($keywordInfo, $taskItem['se_id'], $taskItem['se_url'], $reportDate);
					if ($taskResult['status']) {
						echo "Posted DFS SERP task for <b>{$taskItem['keyword_name']}</b> on {$taskItem['se_name']}.....</br>\n";
					} else {
						echo "Failed posting DFS SERP task for <b>{$taskItem['keyword_name']}</b>: {$taskResult['message']}.....</br>\n";
					}
					sleep(1); // Small delay between API calls
				}

				echo "SERP tasks posted. Results will be fetched at end of cron job.....</br>\n";
				break;

			case 'spapi':
				$this->keywordPositionCheckerCronSPAPI($websiteId, $reportController, $keywordCtrler);
				break;

			case 'crawl':
			default:
				// Direct crawl / scraping method
				// get keywords not to be checked
				$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				$sql = "select distinct(keyword_id) from keywordcrontracker kc, keywords k where k.id=kc.keyword_id and k.website_id=$websiteId and time=$time";
				$keyList = $this->db->select($sql);
				$excludeKeyList = array(0);
				foreach ($keyList as $info) {
					$excludeKeyList[] = $info['keyword_id'];
				}

				// get keywords needs to be checked
				$sql = "select k.*,w.url from keywords k,websites w where k.website_id=w.id and w.id=$websiteId and k.status=1 and k.crawled=0";
				$sql .= " and k.id not in(".implode(",", $excludeKeyList).") order by k.name";
				$keywordList = $reportController->db->select($sql);
				$reportDate = date('Y-m-d', $this->timeStamp);

				// loop through each keyword
				foreach ( $keywordList as $keywordInfo ) {
					$reportController->seFound = 0;
					$crawlResult = $reportController->crawlKeyword($keywordInfo, '', true);
					foreach($crawlResult as $sengineId => $matchList){
						if($matchList['status'] && !empty($matchList['matched'])){
							foreach($matchList['matched'] as $i => $matchInfo){
								$remove = ($i == 0) ? true : false;
								$matchInfo['se_id'] = $sengineId;
								$matchInfo['keyword_id'] = $keywordInfo['id'];
								$serpData = ($i == 0 && !empty($matchList['all'])) ? $matchList['all'] : null;

								$repCtrler = New ReportController();
								$repCtrler->saveMatchedKeywordInfo($matchInfo, $remove, '', $serpData);
							}
							$this->debugMsg("Successfully crawled keyword <b>{$keywordInfo['name']}</b> results from ".$reportController->seList[$sengineId]['domain'].".....<br>\n");
						} elseif ($matchList['status']) {
							// Crawl succeeded but no matches - store rank 0
							$repCtrler = New ReportController();
							$matchInfo = [
								'keyword_id' => $keywordInfo['id'],
								'se_id' => $sengineId,
								'rank' => 0,
								'url' => '',
								'title' => '',
								'description' => '',
							];
							$serpData = !empty($matchList['all']) ? $matchList['all'] : null;
							$repCtrler->saveMatchedKeywordInfo($matchInfo, true, $reportDate, $serpData);
							$this->debugMsg("No matches for keyword <b>{$keywordInfo['name']}</b> from ".$reportController->seList[$sengineId]['domain'].", stored rank 0.....<br>\n");
						} else {
							// Crawl failed - copy yesterday's result as fallback
							$repCtrler = New ReportController();
							$copied = $repCtrler->copyYesterdayResult($keywordInfo['id'], $sengineId, $reportDate);
							if ($copied) {
								$this->debugMsg("Crawling keyword <b>{$keywordInfo['name']}</b> from ".$reportController->seList[$sengineId]['domain']." failed, copied yesterday's result.....<br>\n");
							} else {
								$this->debugMsg("Crawling keyword <b>{$keywordInfo['name']}</b> results from ".$reportController->seList[$sengineId]['domain']." failed......<br>\n");
							}
						}
					}

					$keywordCtrler->__changeCrawledStatus(1, 'id=' . $keywordInfo['id']);

					// to implement split cron execution feature
					if ( (SP_NUMBER_KEYWORDS_CRON > 0) && !empty($crawlResult) ) {
					    $this->checkedKeywords++;
					    if ($this->checkedKeywords == SP_NUMBER_KEYWORDS_CRON) {
					        die("Reached total number of allowed keywords(".SP_NUMBER_KEYWORDS_CRON.") in each cron job");
					    }
					}

					if(empty($reportController->seFound)){
						$this->debugMsg("Keyword <b>{$keywordInfo['name']}</b> not assigned to required search engines........\n");
					}
					sleep(SP_CRAWL_DELAY);
				}
				break;
		}
	}

	# func to run keyword position checker via SP API
	function keywordPositionCheckerCronSPAPI($websiteId, $reportController, $keywordCtrler) {
		$spapiCtrler = new SPAPIController();
		$reportDate = date('Y-m-d', $this->timeStamp);
		$time = strtotime($reportDate);

		// get keywords not to be checked (already tracked today)
		$sql = "select distinct(keyword_id) from keywordcrontracker kc, keywords k where k.id=kc.keyword_id and k.website_id=$websiteId and time=$time";
		$keyList = $this->db->select($sql);
		$excludeKeyList = array(0);
		foreach ($keyList as $info) {
			$excludeKeyList[] = $info['keyword_id'];
		}

		// get keywords needs to be checked
		$sql = "select k.*,w.url from keywords k,websites w where k.website_id=w.id and w.id=$websiteId and k.status=1 and k.crawled=0";
		$sql .= " and k.id not in(".implode(",", $excludeKeyList).") order by k.name";
		$keywordList = $this->db->select($sql);

		$websiteUrl = $this->websiteInfo['url'];

		$totalKeywords = count($keywordList);
		if (empty($keywordList)) {
			$this->debugMsg("SP API: No keywords to process for <b>$websiteUrl</b>.....<br>\n");
			return;
		}
		$this->debugMsg("SP API: Processing <b>$totalKeywords</b> keyword(s) for <b>$websiteUrl</b>.....<br>\n");

		foreach ($keywordList as $keywordInfo) {
			// Get search engine IDs assigned to this keyword
			$seIds = explode(':', $keywordInfo['searchengines']);
			$seIds = array_filter($seIds, function($id) use ($reportController) {
				return !empty($id) && !empty($reportController->seList[$id]);
			});

			if (empty($seIds)) {
				$this->debugMsg("Keyword <b>{$keywordInfo['name']}</b> not assigned to required search engines........\n");
				continue;
			}

			$seIds = array_values($seIds);

			// Post to SP API
			$apiResult = $spapiCtrler->postSERPKeyword($keywordInfo, $seIds);

			if ($apiResult['status'] && !empty($apiResult['data'])) {
				// Process SERP response
				$matchResults = $spapiCtrler->processSERPResponse($apiResult['data'], $keywordInfo, $websiteUrl, $reportDate);

				foreach ($seIds as $seId) {
					$seId = intval($seId);
					$matchCount = !empty($matchResults[$seId]) ? $matchResults[$seId] : 0;

					if ($matchCount > 0) {
						$this->debugMsg("SP API: Found $matchCount matches for <b>{$keywordInfo['name']}</b> on {$reportController->seList[$seId]['domain']}.....<br>\n");
					} else {
						// API succeeded but no matches - store rank 0
						$repCtrler = New ReportController();
						$matchInfo = [
							'keyword_id' => $keywordInfo['id'],
							'se_id' => $seId,
							'rank' => 0,
							'url' => '',
							'title' => '',
							'description' => '',
						];
						$repCtrler->saveMatchedKeywordInfo($matchInfo, true, $reportDate);
						$this->debugMsg("SP API: No matches for <b>{$keywordInfo['name']}</b> on {$reportController->seList[$seId]['domain']}, stored rank 0.....<br>\n");
					}

					// AI Overview is a Google-only SERP feature and is only present in the
					// spAPI response once the Google-domain mapping has actually been crawled.
					// A failure here must not abort the rest of the batch, so it is isolated.
					try {
						include_once(SP_CTRLPATH . "/dataforseo.ctrl.php");
						if (DataForSEOController::getSERPDomainCategory($reportController->seList[$seId]['domain']) == 'google') {
							include_once(SP_CTRLPATH . "/aioverview.ctrl.php");
							$aioCtrler = new AIOverviewController();
							$subdomainPolicy = defined('SP_AIO_SUBDOMAIN_MATCH') ? SP_AIO_SUBDOMAIN_MATCH : 'registrable';
							$normalized = AIOverviewController::mapSpApi($apiResult['data'], $reportDate);
							$aioCtrler->saveResult($keywordInfo['id'], $seId, $reportDate, 'spapi', $normalized, $websiteUrl, $subdomainPolicy);
						}
					} catch (Exception $e) {
						// bug fix: debugMsg() only prints when $this->debug is
						// on, never true for a real cron run - this failure
						// left no trace anywhere
						error_log("SEO Panel: AI Overview parse/save failed for keyword {$keywordInfo['id']}: " . $e->getMessage());
						$this->debugMsg("AI Overview parse/save failed for <b>{$keywordInfo['name']}</b>: {$e->getMessage()}.....<br>\n");
					}

					// Track cron execution
					$repCtrler = New ReportController();
					$repCtrler->saveCronTrackInfo($keywordInfo['id'], $seId, $time);
				}
			} else {
				// API call failed - copy yesterday's results for all SEs
				$this->debugMsg("SP API call failed for <b>{$keywordInfo['name']}</b>: {$apiResult['message']}.....<br>\n");
				foreach ($seIds as $seId) {
					$seId = intval($seId);
					$repCtrler = New ReportController();
					$repCtrler->copyYesterdayResult($keywordInfo['id'], $seId, $reportDate);
					$repCtrler->saveCronTrackInfo($keywordInfo['id'], $seId, $time);
				}

				// If monthly/SERP limit exceeded, update cache and create alert
				if (stripos($apiResult['message'], 'limit exceeded') !== false || stripos($apiResult['message'], 'limit reached') !== false) {
					include_once(SP_CTRLPATH . "/information.ctrl.php");
					include_once(SP_CTRLPATH . "/alerts.ctrl.php");
					$informationCtrler = new InformationController();
					$informationCtrler->updateTodayInformation('monthly_limit', 'spapi_check');
					$alertCtrler = new AlertController();
					$alertCtrler->createAlert([
						'alert_subject'  => 'Seo Panel API Usage Limit Reached',
						'alert_message'  => 'Monthly SERP limit exceeded. Upgrade your plan to continue.',
						'alert_url'      => SP_WEBPATH . '/admin-panel.php?menu_selected=settings&start_script=settings&category=seopanel_api',
						'alert_type'     => 'warning',
						'alert_category' => 'general',
					], false, true);
				}
			}

			$keywordCtrler->__changeCrawledStatus(1, 'id=' . $keywordInfo['id']);

			// flush output so UI shows progress live
			if (ob_get_level()) ob_flush();
			flush();

			// to implement split cron execution feature
			if (SP_NUMBER_KEYWORDS_CRON > 0) {
				$this->checkedKeywords++;
				if ($this->checkedKeywords == SP_NUMBER_KEYWORDS_CRON) {
					die("Reached total number of allowed keywords(".SP_NUMBER_KEYWORDS_CRON.") in each cron job");
				}
			}

			sleep(SP_CRAWL_DELAY);
		}
	}
	
	# func to generate webmaster tools reports from cron
	function webmasterToolsCron($websiteId){
		
		include_once(SP_CTRLPATH."/webmaster.ctrl.php");
		$this->debugMsg("Starting webmaster tools cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$wmCtrler = New WebMasterController();
		$websiteInfo = $this->websiteInfo;
		
		// check whether old reports are not generated. Then generate from it.
		for ($i=4; $i>=2; $i--) {
		
			// report date should be less than 2 days, then only reports will be generated
			$reportDate = date('Y-m-d', $this->timeStamp - ($i * 60 * 60 * 24));
			
			// loop through source list
			foreach ($wmCtrler->sourceList as $source) {
				
				// check whether reports already existing 
				if (SP_MULTIPLE_CRON_EXEC && $wmCtrler->isReportsExists($websiteInfo['id'], $reportDate, $source)) {
					$this->debugMsg("Skip webmaster tools report($reportDate) generation of <b>{$this->websiteInfo['name']}</b>.....<br>\n");
					continue;
				}
				
				// store results
				$wmCtrler->storeWebsiteAnalytics($websiteInfo['id'], $reportDate, $source);
			}		
	
			$this->debugMsg("Saved webmaster tools report($reportDate) of <b>{$this->websiteInfo['name']}</b>.....<br>\n");
		}
		
		// update webmaster tools sitemaps
		$websiteController = New WebsiteController();
		$websiteController->importWebmasterToolsSitemaps($websiteId, true);
		$this->debugMsg("Saved webmaster tools sitemaps of <b>{$this->websiteInfo['name']}</b>.....<br>\n");		
		
	}	
	
	// func to generate analytics reports from cron
	function analyticsCron($websiteId){
		
		include_once(SP_CTRLPATH."/analytics.ctrl.php");
		$this->debugMsg("Starting analytics cron for website: {$this->websiteInfo['name']}....<br>\n");
		
		$wmCtrler = New AnalyticsController();
		$websiteInfo = $this->websiteInfo;
		$reportDate = date('Y-m-d', $this->timeStamp - (60 * 60 * 24));
			
		// check whether reports already existing 
		if (SP_MULTIPLE_CRON_EXEC && $wmCtrler->isReportsExists($websiteInfo['id'], $reportDate)) {
		    $this->debugMsg("Analytics results already generated of <b>{$this->websiteInfo['name']}</b>.....<br>\n");
		    return FALSE;
		}
			
		// store results
		$wmCtrler->storeWebsiteAnalytics($websiteInfo['id'], $reportDate);
		$this->debugMsg("Saved analytics results of <b>{$this->websiteInfo['name']}</b>.....<br>\n");
	}
	

	// Zero-Setup Scheduler, Phase 1: enqueue+drain counterparts of the 9
	// tool-specific *Cron() methods above, selected by SP_JOB_QUEUE_ENABLED
	// in routeCronJob(). Same is-due guards, same underlying controller
	// calls - only the execution model (queue chunk claim/complete/fail
	// instead of a raw loop/die()) differs. Temporary dual-path; see plan.

	function analyticsCronQueued($websiteId){

		include_once(SP_CTRLPATH."/analytics.ctrl.php");
		$this->debugMsg("Starting analytics cron for website: {$this->websiteInfo['name']}....<br>\n");

		$wmCtrler = New AnalyticsController();
		$websiteInfo = $this->websiteInfo;
		$reportDate = date('Y-m-d', $this->timeStamp - (60 * 60 * 24));

		if (!(SP_MULTIPLE_CRON_EXEC && $wmCtrler->isReportsExists($websiteInfo['id'], $reportDate))) {
			$this->enqueueChunks('web-analytics', $websiteId, ['website' => null]);
		}

		$this->drainChunkQueue('web-analytics', $websiteId, function($chunk) use ($wmCtrler, $websiteInfo, $reportDate) {
			$wmCtrler->storeWebsiteAnalytics($websiteInfo['id'], $reportDate);
			$this->debugMsg("Saved analytics results of <b>{$websiteInfo['name']}</b>.....<br>\n");
		});
	}

	function saturationCheckerCronQueued($websiteId){

		include_once(SP_CTRLPATH."/saturationchecker.ctrl.php");
		$this->debugMsg("Starting Search engine saturation Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$saturationCtrler = New SaturationCheckerController();
		$websiteInfo = $this->websiteInfo;

		if (!(SP_MULTIPLE_CRON_EXEC && $saturationCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp))) {
			$this->enqueueChunks('saturation-checker', $websiteId, ['website' => null]);
		}

		$this->drainChunkQueue('saturation-checker', $websiteId, function($chunk) use ($saturationCtrler, $websiteInfo) {
			$saturationCtrler->url = $websiteUrl = addHttpToUrl($websiteInfo['url']);
			foreach ($saturationCtrler->colList as $col => $dbCol) {
				$websiteInfo[$col] = $saturationCtrler->__getSaturationRank($col, true);
			}
			$saturationCtrler->saveRankResults($websiteInfo, true);
			echo "Saved Search Engine Saturation results of <b>$websiteUrl</b>.....</br>\n";
		});
	}

	function pageSpeedCheckerCronQueued($websiteId){

		include_once(SP_CTRLPATH."/pagespeed.ctrl.php");
		$this->debugMsg("Starting page speed Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$pageSpeedCtrler = New PageSpeedController();
		$websiteInfo = $this->websiteInfo;

		if (!(SP_MULTIPLE_CRON_EXEC && $pageSpeedCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp))) {
			$this->enqueueChunks('pagespeed', $websiteId, ['website' => null]);
		}

		$this->drainChunkQueue('pagespeed', $websiteId, function($chunk) use ($pageSpeedCtrler, $websiteInfo) {
			$userCtrler = new UserController();
			$userInfo = $userCtrler->__getUserInfo($websiteInfo['user_id']);
			$langCode = $userInfo['lang_code'];

			$websiteUrl = addHttpToUrl($websiteInfo['url']);
			$params = array('screenshot' => false, 'strategy' => 'desktop', 'locale' => $langCode);
			$websiteInfo['desktop'] = $pageSpeedCtrler->__getPageSpeedInfo($websiteUrl, $params);
			$params = array('screenshot' => false, 'strategy' => 'mobile', 'locale' => $langCode);
			$websiteInfo['mobile'] = $pageSpeedCtrler->__getPageSpeedInfo($websiteUrl, $params);

			$pageSpeedCtrler->savePageSpeedResults($websiteInfo, true);
			echo "Saved page speed results of <b>$websiteUrl</b>.....</br>\n";
		});
	}

	function backlinkCheckerCronQueued($websiteId) {
		include_once(SP_CTRLPATH."/backlink.ctrl.php");
		include_once(SP_CTRLPATH."/rank.ctrl.php");
		$this->debugMsg("Starting Backlink Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$backlinkCtrler = New BacklinkController();
		$websiteInfo = $this->websiteInfo;

		if (!(SP_MULTIPLE_CRON_EXEC && $backlinkCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp))) {
			$this->enqueueChunks('backlink-checker', $websiteId, ['website' => null]);
		}

		$this->drainChunkQueue('backlink-checker', $websiteId, function($chunk) use ($backlinkCtrler, $websiteInfo) {
			$websiteUrl = addHttpToUrl($websiteInfo['url']);
			$mozCtrler = new MozController();
			$mozRankInfo = $mozCtrler->__getMozRankInfo(array($websiteUrl));

			$websiteInfo['external_pages_to_page'] = !empty($mozRankInfo[0]['external_pages_to_page']) ? $mozRankInfo[0]['external_pages_to_page'] : 0;
			$websiteInfo['external_pages_to_root_domain'] = !empty($mozRankInfo[0]['external_pages_to_root_domain']) ? $mozRankInfo[0]['external_pages_to_root_domain'] : 0;

			include_once(SP_CTRLPATH."/settings.ctrl.php");
			if (SettingsController::isDFSEnabled('backlink')) {
				include_once(SP_CTRLPATH."/dataforseo.ctrl.php");
				$dfsCtrler = new DataForSEOController();
				$dfsSummary = $dfsCtrler->__getBacklinkSummary($websiteUrl);
				if (!empty($dfsSummary)) {
					$websiteInfo['external_pages_to_page'] = $dfsSummary['backlinks'];
					$websiteInfo['external_pages_to_root_domain'] = $dfsSummary['referring_domains'];
					$websiteInfo['broken_backlinks'] = $dfsSummary['broken_backlinks'];
				}
			}

			$backlinkCtrler->saveRankResults($websiteInfo, true);
			$this->debugMsg("Saved backlink results of <b>$websiteUrl</b>.....<br>\n");

			$rankCtrler = New RankController();
			$websiteInfo['spam_score'] = !empty($mozRankInfo[0]['spam_score']) ? $mozRankInfo[0]['spam_score'] : 0;
			$websiteInfo['page_authority'] = !empty($mozRankInfo[0]['page_authority']) ? $mozRankInfo[0]['page_authority'] : 0;
			$websiteInfo['domain_authority'] = !empty($mozRankInfo[0]['domain_authority']) ? $mozRankInfo[0]['domain_authority'] : 0;
			$rankCtrler->saveRankResults($websiteInfo, true);
			$this->debugMsg("Saved rank results of <b>$websiteUrl</b>.....<br>\n");
		});
	}

	function rankCheckerCronQueued($websiteId) {
		include_once(SP_CTRLPATH."/rank.ctrl.php");
		include_once(SP_CTRLPATH."/backlink.ctrl.php");
		$this->debugMsg("Starting Rank Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$rankCtrler = New RankController();
		$websiteInfo = $this->websiteInfo;

		if (!(SP_MULTIPLE_CRON_EXEC && $rankCtrler->isReportsExists($websiteInfo['id'], $this->timeStamp))) {
			$this->enqueueChunks('rank-checker', $websiteId, ['website' => null]);
		}

		$this->drainChunkQueue('rank-checker', $websiteId, function($chunk) use ($rankCtrler, $websiteInfo) {
			$websiteUrl = addHttpToUrl($websiteInfo['url']);
			$mozCtrler = new MozController();
			$mozRankInfo = $mozCtrler->__getMozRankInfo(array($websiteUrl));

			$websiteInfo['spam_score'] = !empty($mozRankInfo[0]['spam_score']) ? $mozRankInfo[0]['spam_score'] : 0;
			$websiteInfo['page_authority'] = !empty($mozRankInfo[0]['page_authority']) ? $mozRankInfo[0]['page_authority'] : 0;
			$websiteInfo['domain_authority'] = !empty($mozRankInfo[0]['domain_authority']) ? $mozRankInfo[0]['domain_authority'] : 0;
			$rankCtrler->saveRankResults($websiteInfo, true);
			$this->debugMsg("Saved rank results of <b>$websiteUrl</b>.....<br>\n");

			$backlinkCtrler = New BacklinkController();
			$websiteInfo['external_pages_to_page'] = !empty($mozRankInfo[0]['external_pages_to_page']) ? $mozRankInfo[0]['external_pages_to_page'] : 0;
			$websiteInfo['external_pages_to_root_domain'] = !empty($mozRankInfo[0]['external_pages_to_root_domain']) ? $mozRankInfo[0]['external_pages_to_root_domain'] : 0;
			$backlinkCtrler->saveRankResults($websiteInfo, true);
			$this->debugMsg("Saved backlink results of <b>$websiteUrl</b>.....<br>\n");
		});
	}

	function socialMediaCheckerCronQueued($websiteId){

		include_once(SP_CTRLPATH."/social_media.ctrl.php");
		$this->debugMsg("Starting social media Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$socialMediaCtrler = New SocialMediaController();
		$websiteInfo = $this->websiteInfo;

		$linkList = $socialMediaCtrler->getAllLinksWithOutReports($websiteInfo['id'], date('Y-m-d', $this->timeStamp));
		$chunkMap = [];
		foreach ($linkList as $linkInfo) {
			$chunkMap[$linkInfo['id']] = $linkInfo;
		}
		$this->enqueueChunks('sm-checker', $websiteId, $chunkMap);

		$this->drainChunkQueue('sm-checker', $websiteId, function($chunk) use ($socialMediaCtrler) {
			$linkInfo = $chunk['payload'];
			$result = $socialMediaCtrler->getSocialMediaDetails($linkInfo['type'], $linkInfo['url']);

			if ($result['status']) {
				echo "Crawled social media results of <b>{$linkInfo['name']}</b>.....</br>\n";
			} else {
				echo "Failed Crawling of social media results of <b>{$linkInfo['name']}</b>.....</br>\n";
				echo $result['msg'];
			}

			$socialMediaCtrler->saveSocialMediaLinkResults($linkInfo['id'], $result);
			sleep(SP_CRAWL_DELAY + 5);
		});

		echo "Saved social media results of website id: <b>$websiteId</b>.....</br>\n";
	}

	// Chunk unit = one link, tagged by type (dfs_task / yelp / scrape) since
	// the DFS-enabled path mixes two different link groups with different
	// processing (async task post vs. synchronous scrape).
	function reviewCheckerCronQueued($websiteId) {
		include_once(SP_CTRLPATH."/review_manager.ctrl.php");
		$this->debugMsg("Starting review Checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$reviewController = New ReviewManagerController();
		$reportDate = date('Y-m-d', $this->timeStamp);
		$useDFS = SettingsController::isDFSEnabled('review');

		$chunkMap = [];
		if ($useDFS) {
			foreach ($reviewController->getLinksNeedingDFSTaskPost($websiteId, $reportDate) as $linkInfo) {
				$chunkMap['dfs:' . $linkInfo['id']] = ['type' => 'dfs_task', 'link' => $linkInfo];
			}
			foreach ($reviewController->getYelpLinksWithOutReports($websiteId, $reportDate) as $linkInfo) {
				$chunkMap['yelp:' . $linkInfo['id']] = ['type' => 'yelp', 'link' => $linkInfo];
			}
		} else {
			foreach ($reviewController->getAllLinksWithOutReports($websiteId, $reportDate) as $linkInfo) {
				$chunkMap['scrape:' . $linkInfo['id']] = ['type' => 'scrape', 'link' => $linkInfo];
			}
		}
		$this->enqueueChunks('review-manager', $websiteId, $chunkMap);

		$this->drainChunkQueue('review-manager', $websiteId, function($chunk) use ($reviewController, $reportDate) {
			$type = $chunk['payload']['type'];
			$linkInfo = $chunk['payload']['link'];

			if ($type == 'dfs_task') {
				$taskResult = $reviewController->postReviewTaskToDFS($linkInfo['id'], $linkInfo['type'], $linkInfo['url'], $reportDate);
				if ($taskResult['status']) {
					echo "Posted DFS task for <b>{$linkInfo['name']}</b> ({$linkInfo['type']}).....</br>\n";
				} else {
					echo "Failed posting DFS task for <b>{$linkInfo['name']}</b>: {$taskResult['message']}.....</br>\n";
				}
				sleep(1);
				return;
			}

			$result = $reviewController->getReviewDetails($linkInfo['type'], $linkInfo['url']);
			$label = ($type == 'yelp') ? ' (Yelp)' : '';
			if ($result['status']) {
				echo "Crawled review results of <b>{$linkInfo['name']}</b>$label.....</br>\n";
			} else {
				echo "Failed Crawling of review results of <b>{$linkInfo['name']}</b>$label.....</br>\n";
				echo $result['msg'];
			}
			$reviewController->saveReviewLinkResults($linkInfo['id'], $result);
			sleep(SP_CRAWL_DELAY + 5);
		});

		echo "Saved review results of website id: <b>$websiteId</b>.....</br>\n";
	}

	function searchVolumeCheckerCronQueued($websiteId) {
		include_once(SP_CTRLPATH . "/spapi.ctrl.php");
		include_once(SP_CTRLPATH . "/settings.ctrl.php");

		$useDFS = SettingsController::isDFSEnabled('search_volume');
		$source = 'search volume';
		$dfsCtrler = null;

		if ($useDFS) {
			include_once(SP_CTRLPATH . "/dataforseo.ctrl.php");
			$dfsCtrler = new DataForSEOController();
			$source = 'DataForSEO';
		}

		$spapiCtrler = new SPAPIController();
		$this->debugMsg("Starting Search Volume cron via <b>$source</b> for website: {$this->websiteInfo['name']}....<br>\n");

		$sql = "SELECT k.* FROM keywords k
		        LEFT JOIN keyword_search_volume sv ON sv.keyword_id = k.id AND sv.source = 'google'
		        WHERE k.website_id=" . intval($websiteId) . " AND k.status=1
		        AND (sv.crawled_time IS NULL OR (sv.crawled_time + INTERVAL 30 DAY) < NOW())
		        ORDER BY k.id";
		$keywordList = $this->db->select($sql);

		if (empty($keywordList)) {
			$this->debugMsg("Search Volume: No keywords need updating for <b>{$this->websiteInfo['name']}</b>....<br>\n");
			return;
		}

		$chunkMap = [];
		foreach ($keywordList as $keywordInfo) $chunkMap[$keywordInfo['id']] = $keywordInfo;
		$this->enqueueChunks('search-volume', $websiteId, $chunkMap);

		$this->drainChunkQueue('search-volume', $websiteId, function($chunk) use ($useDFS, $dfsCtrler, $spapiCtrler) {
			$keywordInfo = $chunk['payload'];

			if ($useDFS) {
				$dfsResult = $dfsCtrler->getSearchVolumeFromDFS($keywordInfo);
				if ($dfsResult['status'] && !empty($dfsResult['data'])) {
					$spapiCtrler->saveKeywordSearchVolumeData($keywordInfo['id'], 'google', $dfsResult['data'], 'success');
					$sv = number_format($dfsResult['data']['search_volume'] ?? 0);
					$this->debugMsg("DFS: Search volume <b>$sv</b> for <b>{$keywordInfo['name']}</b>.....<br>\n");
				} else {
					$spapiCtrler->saveKeywordSearchVolumeData($keywordInfo['id'], 'google', null, 'fail');
					$this->debugMsg("DFS: Search volume failed for <b>{$keywordInfo['name']}</b>: {$dfsResult['message']}.....<br>\n");
				}
			} else {
				$apiResult = $spapiCtrler->postSearchVolumeKeyword($keywordInfo, 'google');
				if ($apiResult['status'] && !empty($apiResult['data'])) {
					$spapiCtrler->saveSearchVolumeResult($keywordInfo['id'], $apiResult['data']);
					$status = $apiResult['data']['mapping']['last_crawl_status'] ?? 'pending';
					$this->debugMsg("SP API: Search volume ({$status}) for <b>{$keywordInfo['name']}</b>.....<br>\n");
				} else {
					$this->debugMsg("SP API: Search volume failed for <b>{$keywordInfo['name']}</b>: {$apiResult['message']}.....<br>\n");
					$spapiCtrler->saveKeywordSearchVolumeData($keywordInfo['id'], 'google', null, 'fail');

					if (stripos($apiResult['message'], 'limit exceeded') !== false || stripos($apiResult['message'], 'limit reached') !== false) {
						include_once(SP_CTRLPATH . "/alerts.ctrl.php");
						$alertCtrler = new AlertController();
						$alertCtrler->createAlert([
							'alert_subject'  => 'SP API Search Volume Limit Reached',
							'alert_message'  => 'Monthly search volume limit exceeded. Upgrade your plan to continue.',
							'alert_url'      => SP_WEBPATH . '/admin-panel.php?menu_selected=settings&start_script=settings&category=seopanel_api',
							'alert_type'     => 'warning',
							'alert_category' => 'general',
						], false, true);
						$this->stopChunkDrain = true;
					}
				}
			}

			sleep(1);
		});

		echo "Saved search volume results for website: <b>{$this->websiteInfo['name']}</b>.....</br>\n";
	}

	function keywordPositionCheckerCronQueued($websiteId){

		include_once(SP_CTRLPATH."/searchengine.ctrl.php");
		include_once(SP_CTRLPATH."/report.ctrl.php");

		$reportController = New ReportController();
		$keywordCtrler = New KeywordController();

		$seController = New SearchEngineController();
		$reportController->seList = $seController->__getAllCrawlFormatedSearchEngines();

		$this->debugMsg("Starting keyword position checker cron for website: {$this->websiteInfo['name']}....<br>\n");

		$serpSource = 'crawl';
		include_once(SP_CTRLPATH."/spapi.ctrl.php");
		if (SPAPIController::isConfigured()) {
			$serpSource = 'spapi';
		}
		if (SettingsController::isDFSEnabled('serp')) {
			$serpSource = 'dataforseo';
		}

		$this->debugMsg("Using SERP source: <b>$serpSource</b>....<br>\n");

		switch ($serpSource) {

			case 'dataforseo':
				include_once(SP_CTRLPATH."/dataforseo.ctrl.php");
				$dfsCtrler = new DataForSEOController();
				$reportDate = date('Y-m-d', $this->timeStamp);

				$keywordsNeedingTask = $dfsCtrler->getKeywordsNeedingSERPTaskPost($websiteId, $reportDate);
				$chunkMap = [];
				foreach ($keywordsNeedingTask as $taskItem) {
					$chunkMap[$taskItem['keyword_info']['id'] . ':' . $taskItem['se_id']] = $taskItem;
				}
				$this->enqueueChunks('keyword-position-checker', $websiteId, $chunkMap);

				$this->drainChunkQueue('keyword-position-checker', $websiteId, function($chunk) use ($dfsCtrler, $reportDate) {
					$taskItem = $chunk['payload'];
					$keywordInfo = $taskItem['keyword_info'];
					$keywordInfo['depth'] = $taskItem['depth'];

					$taskResult = $dfsCtrler->postSERPTask($keywordInfo, $taskItem['se_id'], $taskItem['se_url'], $reportDate);
					if ($taskResult['status']) {
						echo "Posted DFS SERP task for <b>{$taskItem['keyword_name']}</b> on {$taskItem['se_name']}.....</br>\n";
					} else {
						echo "Failed posting DFS SERP task for <b>{$taskItem['keyword_name']}</b>: {$taskResult['message']}.....</br>\n";
					}
					sleep(1);
				});

				echo "SERP tasks posted. Results will be fetched at end of cron job.....</br>\n";
				break;

			case 'spapi':
				$this->keywordPositionCheckerCronSPAPIQueued($websiteId, $reportController, $keywordCtrler);
				break;

			case 'crawl':
			default:
				$time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
				$sql = "select distinct(keyword_id) from keywordcrontracker kc, keywords k where k.id=kc.keyword_id and k.website_id=$websiteId and time=$time";
				$keyList = $this->db->select($sql);
				$excludeKeyList = array(0);
				foreach ($keyList as $info) {
					$excludeKeyList[] = $info['keyword_id'];
				}

				$sql = "select k.*,w.url from keywords k,websites w where k.website_id=w.id and w.id=$websiteId and k.status=1 and k.crawled=0";
				$sql .= " and k.id not in(".implode(",", $excludeKeyList).") order by k.name";
				$keywordList = $reportController->db->select($sql);
				$reportDate = date('Y-m-d', $this->timeStamp);

				$chunkMap = [];
				foreach ($keywordList as $keywordInfo) $chunkMap[$keywordInfo['id']] = $keywordInfo;
				$this->enqueueChunks('keyword-position-checker', $websiteId, $chunkMap);

				$this->drainChunkQueue('keyword-position-checker', $websiteId, function($chunk) use ($reportController, $keywordCtrler, $reportDate) {
					$keywordInfo = $chunk['payload'];
					$reportController->seFound = 0;
					$crawlResult = $reportController->crawlKeyword($keywordInfo, '', true);
					foreach($crawlResult as $sengineId => $matchList){
						if($matchList['status'] && !empty($matchList['matched'])){
							foreach($matchList['matched'] as $i => $matchInfo){
								$remove = ($i == 0) ? true : false;
								$matchInfo['se_id'] = $sengineId;
								$matchInfo['keyword_id'] = $keywordInfo['id'];
								$serpData = ($i == 0 && !empty($matchList['all'])) ? $matchList['all'] : null;

								$repCtrler = New ReportController();
								$repCtrler->saveMatchedKeywordInfo($matchInfo, $remove, '', $serpData);
							}
							$this->debugMsg("Successfully crawled keyword <b>{$keywordInfo['name']}</b> results from ".$reportController->seList[$sengineId]['domain'].".....<br>\n");
						} elseif ($matchList['status']) {
							$repCtrler = New ReportController();
							$matchInfo = [
								'keyword_id' => $keywordInfo['id'],
								'se_id' => $sengineId,
								'rank' => 0,
								'url' => '',
								'title' => '',
								'description' => '',
							];
							$serpData = !empty($matchList['all']) ? $matchList['all'] : null;
							$repCtrler->saveMatchedKeywordInfo($matchInfo, true, $reportDate, $serpData);
							$this->debugMsg("No matches for keyword <b>{$keywordInfo['name']}</b> from ".$reportController->seList[$sengineId]['domain'].", stored rank 0.....<br>\n");
						} else {
							$repCtrler = New ReportController();
							$copied = $repCtrler->copyYesterdayResult($keywordInfo['id'], $sengineId, $reportDate);
							if ($copied) {
								$this->debugMsg("Crawling keyword <b>{$keywordInfo['name']}</b> from ".$reportController->seList[$sengineId]['domain']." failed, copied yesterday's result.....<br>\n");
							} else {
								$this->debugMsg("Crawling keyword <b>{$keywordInfo['name']}</b> results from ".$reportController->seList[$sengineId]['domain']." failed......<br>\n");
							}
						}
					}

					$keywordCtrler->__changeCrawledStatus(1, 'id=' . $keywordInfo['id']);

					if(empty($reportController->seFound)){
						$this->debugMsg("Keyword <b>{$keywordInfo['name']}</b> not assigned to required search engines........\n");
					}
					sleep(SP_CRAWL_DELAY);
				});
				break;
		}
	}

	# Zero-Setup Scheduler queued counterpart of keywordPositionCheckerCronSPAPI().
	# Chunk unit = one keyword, spanning all its assigned search engines - the
	# inline AI Overview try/catch stays inside the chunk closure (not the
	# outer drain-loop catch) so a parse failure there never re-fails a chunk
	# that already spent a real, paid SP API call.
	function keywordPositionCheckerCronSPAPIQueued($websiteId, $reportController, $keywordCtrler) {
		$spapiCtrler = new SPAPIController();
		$reportDate = date('Y-m-d', $this->timeStamp);
		$time = strtotime($reportDate);

		$sql = "select distinct(keyword_id) from keywordcrontracker kc, keywords k where k.id=kc.keyword_id and k.website_id=$websiteId and time=$time";
		$keyList = $this->db->select($sql);
		$excludeKeyList = array(0);
		foreach ($keyList as $info) {
			$excludeKeyList[] = $info['keyword_id'];
		}

		$sql = "select k.*,w.url from keywords k,websites w where k.website_id=w.id and w.id=$websiteId and k.status=1 and k.crawled=0";
		$sql .= " and k.id not in(".implode(",", $excludeKeyList).") order by k.name";
		$keywordList = $this->db->select($sql);

		$websiteUrl = $this->websiteInfo['url'];

		if (empty($keywordList)) {
			$this->debugMsg("SP API: No keywords to process for <b>$websiteUrl</b>.....<br>\n");
			return;
		}
		$totalKeywords = count($keywordList);
		$this->debugMsg("SP API: Processing <b>$totalKeywords</b> keyword(s) for <b>$websiteUrl</b>.....<br>\n");

		$chunkMap = [];
		foreach ($keywordList as $keywordInfo) $chunkMap[$keywordInfo['id']] = $keywordInfo;
		$this->enqueueChunks('keyword-position-checker', $websiteId, $chunkMap);

		$this->drainChunkQueue('keyword-position-checker', $websiteId, function($chunk) use ($spapiCtrler, $reportController, $websiteUrl, $reportDate, $time, $keywordCtrler) {
			$keywordInfo = $chunk['payload'];

			$seIds = explode(':', $keywordInfo['searchengines']);
			$seIds = array_filter($seIds, function($id) use ($reportController) {
				return !empty($id) && !empty($reportController->seList[$id]);
			});

			if (empty($seIds)) {
				$this->debugMsg("Keyword <b>{$keywordInfo['name']}</b> not assigned to required search engines........\n");
				$keywordCtrler->__changeCrawledStatus(1, 'id=' . $keywordInfo['id']);
				return;
			}

			$seIds = array_values($seIds);
			$apiResult = $spapiCtrler->postSERPKeyword($keywordInfo, $seIds);

			if ($apiResult['status'] && !empty($apiResult['data'])) {
				$matchResults = $spapiCtrler->processSERPResponse($apiResult['data'], $keywordInfo, $websiteUrl, $reportDate);

				foreach ($seIds as $seId) {
					$seId = intval($seId);
					$matchCount = !empty($matchResults[$seId]) ? $matchResults[$seId] : 0;

					if ($matchCount > 0) {
						$this->debugMsg("SP API: Found $matchCount matches for <b>{$keywordInfo['name']}</b> on {$reportController->seList[$seId]['domain']}.....<br>\n");
					} else {
						$repCtrler = New ReportController();
						$matchInfo = [
							'keyword_id' => $keywordInfo['id'],
							'se_id' => $seId,
							'rank' => 0,
							'url' => '',
							'title' => '',
							'description' => '',
						];
						$repCtrler->saveMatchedKeywordInfo($matchInfo, true, $reportDate);
						$this->debugMsg("SP API: No matches for <b>{$keywordInfo['name']}</b> on {$reportController->seList[$seId]['domain']}, stored rank 0.....<br>\n");
					}

					try {
						include_once(SP_CTRLPATH . "/dataforseo.ctrl.php");
						if (DataForSEOController::getSERPDomainCategory($reportController->seList[$seId]['domain']) == 'google') {
							include_once(SP_CTRLPATH . "/aioverview.ctrl.php");
							$aioCtrler = new AIOverviewController();
							$subdomainPolicy = defined('SP_AIO_SUBDOMAIN_MATCH') ? SP_AIO_SUBDOMAIN_MATCH : 'registrable';
							$normalized = AIOverviewController::mapSpApi($apiResult['data'], $reportDate);
							$aioCtrler->saveResult($keywordInfo['id'], $seId, $reportDate, 'spapi', $normalized, $websiteUrl, $subdomainPolicy);
						}
					} catch (Exception $e) {
						// bug fix: debugMsg() only prints when $this->debug is
						// on, never true for a real cron run - this failure
						// left no trace anywhere
						error_log("SEO Panel: AI Overview parse/save failed for keyword {$keywordInfo['id']}: " . $e->getMessage());
						$this->debugMsg("AI Overview parse/save failed for <b>{$keywordInfo['name']}</b>: {$e->getMessage()}.....<br>\n");
					}

					$repCtrler = New ReportController();
					$repCtrler->saveCronTrackInfo($keywordInfo['id'], $seId, $time);
				}
			} else {
				$this->debugMsg("SP API call failed for <b>{$keywordInfo['name']}</b>: {$apiResult['message']}.....<br>\n");
				foreach ($seIds as $seId) {
					$seId = intval($seId);
					$repCtrler = New ReportController();
					$repCtrler->copyYesterdayResult($keywordInfo['id'], $seId, $reportDate);
					$repCtrler->saveCronTrackInfo($keywordInfo['id'], $seId, $time);
				}

				if (stripos($apiResult['message'], 'limit exceeded') !== false || stripos($apiResult['message'], 'limit reached') !== false) {
					include_once(SP_CTRLPATH . "/information.ctrl.php");
					include_once(SP_CTRLPATH . "/alerts.ctrl.php");
					$informationCtrler = new InformationController();
					$informationCtrler->updateTodayInformation('monthly_limit', 'spapi_check');
					$alertCtrler = new AlertController();
					$alertCtrler->createAlert([
						'alert_subject'  => 'Seo Panel API Usage Limit Reached',
						'alert_message'  => 'Monthly SERP limit exceeded. Upgrade your plan to continue.',
						'alert_url'      => SP_WEBPATH . '/admin-panel.php?menu_selected=settings&start_script=settings&category=seopanel_api',
						'alert_type'     => 'warning',
						'alert_category' => 'general',
					], false, true);
					$this->stopChunkDrain = true;
				}
			}

			$keywordCtrler->__changeCrawledStatus(1, 'id=' . $keywordInfo['id']);

			if (ob_get_level()) ob_flush();
			flush();

			sleep(SP_CRAWL_DELAY);
		});
	}

	// Chunk unit = one (day, source) pair, plus one extra atomic 'sitemaps'
	// chunk for the trailing importWebmasterToolsSitemaps() call so it
	// retries independently instead of being silently skipped if an earlier
	// day/source chunk throws.
	function webmasterToolsCronQueued($websiteId){

		include_once(SP_CTRLPATH."/webmaster.ctrl.php");
		$this->debugMsg("Starting webmaster tools cron for website: {$this->websiteInfo['name']}....<br>\n");

		$wmCtrler = New WebMasterController();
		$websiteInfo = $this->websiteInfo;

		$chunkMap = [];
		for ($i=4; $i>=2; $i--) {
			$reportDate = date('Y-m-d', $this->timeStamp - ($i * 60 * 60 * 24));
			foreach ($wmCtrler->sourceList as $source) {
				if (SP_MULTIPLE_CRON_EXEC && $wmCtrler->isReportsExists($websiteInfo['id'], $reportDate, $source)) {
					continue;
				}
				$chunkMap["$i:$source"] = ['reportDate' => $reportDate, 'source' => $source];
			}
		}
		$chunkMap['sitemaps'] = null;
		$this->enqueueChunks('webmaster-tools', $websiteId, $chunkMap);

		$this->drainChunkQueue('webmaster-tools', $websiteId, function($chunk) use ($wmCtrler, $websiteInfo, $websiteId) {
			if ($chunk['chunk_key'] == 'sitemaps') {
				$websiteController = New WebsiteController();
				$websiteController->importWebmasterToolsSitemaps($websiteId, true);
				$this->debugMsg("Saved webmaster tools sitemaps of <b>{$websiteInfo['name']}</b>.....<br>\n");
				return;
			}

			$reportDate = $chunk['payload']['reportDate'];
			$source = $chunk['payload']['source'];
			$wmCtrler->storeWebsiteAnalytics($websiteInfo['id'], $reportDate, $source);
			$this->debugMsg("Saved webmaster tools report($reportDate) of <b>{$websiteInfo['name']}</b>.....<br>\n");
		});
	}


	// Zero-Setup Scheduler: read-only health dashboard + Phase 2 ping trigger
	// management. Admin-only - reached via cron.php, which already guards
	// its whole request lifecycle with checkAdminLoggedIn().

	function __isSchedulerLocked() {
		$result = $this->db->select("SELECT IS_USED_LOCK('seopanel_scheduler') as locked_by", true);
		return !empty($result['locked_by']) ? $result['locked_by'] : null;
	}

	function showSchedulerHealth(){
		$lastRun = $this->db->select("SELECT * FROM cron_run_log ORDER BY id DESC LIMIT 1", true);
		$recentRuns = $this->db->select("SELECT * FROM cron_run_log ORDER BY id DESC LIMIT 20");

		$toolStats = $this->db->select("
			SELECT url_section,
				SUM(status='success') as success_count,
				SUM(status='failed') as failed_count,
				AVG(duration_ms) as avg_duration_ms
			FROM cron_job_timing
			WHERE started_at >= (NOW() - INTERVAL 7 DAY)
			GROUP BY url_section
			ORDER BY url_section
		");

		$queueBacklog = $this->db->select("
			SELECT url_section, status, COUNT(*) as cnt, MIN(available_at) as oldest_available_at
			FROM job_queue
			GROUP BY url_section, status
			ORDER BY url_section, status
		");

		$failedSamples = $this->db->select("
			SELECT url_section, chunk_key, last_error, updated_at
			FROM job_queue WHERE status='failed'
			ORDER BY updated_at DESC LIMIT 10
		");

		$this->set('lastRun', $lastRun);
		$this->set('recentRuns', $recentRuns);
		$this->set('toolStats', $toolStats);
		$this->set('queueBacklog', $queueBacklog);
		$this->set('failedSamples', $failedSamples);
		$this->set('lockedBy', $this->__isSchedulerLocked());
		$this->set('jobQueueEnabled', SP_JOB_QUEUE_ENABLED);
		$this->set('pingEnabled', defined('SP_CRON_PING_ENABLED') ? SP_CRON_PING_ENABLED : '0');
		$this->set('pingSecret', defined('SP_CRON_PING_SECRET') ? SP_CRON_PING_SECRET : '');
		$this->set('pingBudget', defined('SP_JOB_QUEUE_BUDGET_SECONDS') ? SP_JOB_QUEUE_BUDGET_SECONDS : 20);
		$this->set('pingUrl', SP_WEBPATH . '/cron-ping.php');

		$this->render('report/schedulerhealth');
	}

	// POST-only: toggle the ping trigger on/off and set its run budget.
	// Does not touch the secret - that's regeneratePingSecret()'s job, kept
	// separate so saving the budget can never accidentally rotate the key.
	function saveSchedulePingSettings($info=[]) {
		$enabled = !empty($info['ping_enabled']) ? '1' : '0';
		$budget = max(5, intval($info['ping_budget'] ?? 20));

		$this->db->query("UPDATE settings SET set_val='$enabled' WHERE set_name='SP_CRON_PING_ENABLED'");
		$this->db->query("UPDATE settings SET set_val=$budget WHERE set_name='SP_JOB_QUEUE_BUDGET_SECONDS'");

		$this->showSchedulerHealth();
	}

	// POST-only: generate a brand-new random secret, same pattern as
	// ai_visibility_sites.token (bin2hex(random_bytes(...))).
	function regeneratePingSecret() {
		$secret = bin2hex(random_bytes(16));
		$secretEsc = addslashes($secret);
		$this->db->query("UPDATE settings SET set_val='$secretEsc' WHERE set_name='SP_CRON_PING_SECRET'");

		$this->showSchedulerHealth();
	}

	/**
	 * Phase 2: bounded, externally-triggered run. Called only from
	 * cron-ping.php, which has already validated the secret before
	 * reaching here. Mirrors cron.php's CLI branch step-for-step (lock,
	 * run log, sync/alerts/executeCron()/cleanup/prune/AI Visibility bot
	 * detection+anomaly alerts+weekly digest/AI Insights/AI Perception
	 * tracking, finish log, release lock) but with a wall-clock deadline
	 * instead of running to exhaustion - executeCron()/routeCronJob()/
	 * drainChunkQueue() all check $this->deadline and stop cleanly,
	 * leaving whatever's left as pending job_queue rows (or un-crawled
	 * legacy state) for next time.
	 *
	 * IMPORTANT: any new "run once per day/week in cron.php's CLI tail"
	 * feature must ALSO be added here, or an install running only on the
	 * ping trigger (the whole point of Zero-Setup) silently never gets
	 * it - this drifted out of sync once already (bot detection/anomaly
	 * alerts/weekly digest were missing here for a release, and AI
	 * Perception tracking was added to the CLI tail without a matching
	 * call here) before being caught and fixed.
	 *
	 * Known limitation: the sync/alert/cleanup/prune steps below are not
	 * individually budgeted - only the website/tool/chunk dispatch inside
	 * executeCron() respects the deadline. A slow DFS pending-task backlog
	 * could still make one ping run long. Acceptable for a first cut since
	 * these are normally fast; revisit if it proves otherwise in practice.
	 *
	 * $triggerSource distinguishes the public, secret-gated cron-ping.php
	 * caller ('ping', the default) from cron-beacon.php's session-
	 * authenticated, no-secret opportunistic trigger ('ping-beacon') in
	 * cron_run_log - same underlying run, just worth telling apart on the
	 * Scheduler Health page.
	 */
	function runPingTrigger($triggerSource = 'ping') {
		if (empty(SP_CRON_PING_ENABLED)) {
			return;
		}

		if (!$this->acquireSchedulerLock()) {
			// another run (cli or ping) already holds the lock - normal, not an error
			return;
		}

		// this is a public, secret-gated, server-to-server endpoint (see
		// cron-ping.php) meant to return a silent 204 - but executeCron()
		// and friends echo human-readable progress text throughout
		// (inherited from the CLI path, where that's exactly the point).
		// Buffer and discard it all rather than let it leak into the HTTP
		// response, which would also send headers as an implicit 200
		// before cron-ping.php's own http_response_code(204) call ever
		// gets a chance to take effect. Also discarded on a fatal error
		// via the shutdown function below, so a mid-run crash can't leak
		// a partial buffer either.
		ob_start();

		register_shutdown_function(function() {
			if (ob_get_level() > 0) ob_end_clean();
			$this->finishRunLog('incomplete');
			$this->releaseSchedulerLock();
		});

		$this->timeStamp = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$this->deadline = microtime(true) + intval(SP_JOB_QUEUE_BUDGET_SECONDS);
		$this->startRunLog($triggerSource);

		include_once(SP_CTRLPATH . "/report.ctrl.php");
		include_once(SP_CTRLPATH . "/searchengine.ctrl.php");
		include_once(SP_CTRLPATH . "/keyword.ctrl.php");
		include_once(SP_CTRLPATH . "/moz.ctrl.php");
		include_once(SP_CTRLPATH . "/webmaster.ctrl.php");
		include_once(SP_CTRLPATH . "/social_media.ctrl.php");
		include_once(SP_CTRLPATH . "/review_manager.ctrl.php");
		include_once(SP_CTRLPATH . "/analytics.ctrl.php");
		include_once(SP_CTRLPATH . "/information.ctrl.php");

		$seCtrler = new SearchEngineController();
		$seCtrler->doSyncSearchEngines(true, true);

		$alertCtrler = new AlertController();
		$alertCtrler->updateSystemAlerts();
		$alertCtrler->updateSpApiAlerts();

		$this->executeCron();

		include_once(SP_CTRLPATH . "/crawllog.ctrl.php");
		$crawlLog = new CrawlLogController();
		$crawlLog->clearCrawlLog(SP_CRAWL_LOG_CLEAR_TIME);
		$crawlLog->clearMaillLog(SP_CRAWL_LOG_CLEAR_TIME);

		if (defined('SP_DFS_API_LOGIN') && !empty(SP_DFS_API_LOGIN)) {
			include_once(SP_CTRLPATH . "/dataforseo.ctrl.php");
			$dfsCtrler = new DataForSEOController();
			$dfsCtrler->processPendingDFSTasks(true);
		}

		include_once(SP_CTRLPATH . "/aioverview.ctrl.php");
		$aioCtrler = new AIOverviewController();
		$aioCtrler->pruneOldReferences();

		include_once(SP_CTRLPATH . "/aivisibility.ctrl.php");
		$aivCtrler = new AIVisibilityController();
		$aivCtrler->pruneOldReferrals();
		$aivCtrler->pruneOldBotHits();
		$aivCtrler->pruneRateLimitBuckets();

		// prune the scheduler's own operational tables - see cron.php's
		// CLI tail for the same three calls
		$this->pruneOldJobQueueRows();
		$this->pruneOldRunLogs();
		$this->pruneOldJobTimingRows();

		// on-premise AI bot detection from co-located websites' own access
		// logs (opt-in, admin-configured) - no-ops immediately if no
		// website has an access_log_path configured
		$aivCtrler->processAccessLogsForBotHits();

		// week-over-week AI referral/bot-crawl traffic anomaly alerts (at most once/day)
		$aivCtrler->checkTrafficAnomalies();

		// weekly AI Visibility digest email (opt-out, at most once per 7 days per user)
		$aivCtrler->sendWeeklyDigests();

		$this->refreshAllAIInsights();

		// scheduled AI Perception tracking - each (prompt, provider) pair
		// internally gated to once per AiPerceptionController::
		// TRACKING_INTERVAL_DAYS, so safe to call on every ping too
		$this->refreshAllLlmPerceptionTracking();

		ob_end_clean();
		$this->finishRunLog('completed');
		$this->releaseSchedulerLock();
	}

	/*
	 * Regenerate AI Insights (RecommendationsController) for every active
	 * website, at most once per calendar day regardless of how often this
	 * cron pass itself runs (CLI daily, or ping-triggered more often). Runs
	 * after executeCron() in both callers so insights reflect the freshest
	 * rank/auditor/AI Overview/AI bot data from this same run. Generation is
	 * pure SQL against already-collected data (no crawling, no external
	 * API calls), so unlike the tool cron jobs it does not need job_queue
	 * chunking - a single website's failure is isolated with try/catch so
	 * it can't take down the rest of the loop.
	 */
	function refreshAllAIInsights() {

		include_once(SP_CTRLPATH . "/information.ctrl.php");
		$infoCtrler = new InformationController();
		if (!empty($infoCtrler->__getTodayInformation('ai_insights_refresh'))) {
			return; // already refreshed today
		}

		include_once(SP_CTRLPATH . "/recommendations.ctrl.php");
		include_once(SP_CTRLPATH . "/report.ctrl.php");
		include_once(SP_CTRLPATH . "/user.ctrl.php");
		$recCtrler = new RecommendationsController();
		$reportCtrler = new ReportController();
		$userCtrler = new UserController();

		$newByUser = array();

		$websiteList = $this->db->select("SELECT id, name, user_id FROM websites WHERE status=1");
		foreach ($websiteList as $websiteInfo) {
			if (!$userCtrler->isUserExpired($websiteInfo['user_id'])) {
				continue;
			}
			try {
				$newRows = $recCtrler->refreshRecommendationsForWebsite($websiteInfo['id'], $websiteInfo['user_id']);
			} catch (Throwable $e) {
				// bug fix: this used to discard $e completely - a per-website
				// failure here left no trace anywhere (debugMsg() only prints
				// when $this->debug is on, never true for a real cron run)
				error_log("SEO Panel: refreshAllAIInsights() failed for website {$websiteInfo['id']}: " . $e->getMessage());
				continue; // one website's insight generation failing must not affect the rest
			}
			if (!empty($newRows)) {
				$newByUser[$websiteInfo['user_id']][$websiteInfo['id']] = array(
					'name' => $websiteInfo['name'],
					'rows' => $newRows,
				);
			}
		}

		// One aggregated digest per user, covering every website of theirs
		// with genuinely new insights today - gated the same two-layer way
		// (system-wide x per-user) as the existing report email notification.
		foreach ($newByUser as $userId => $byWebsite) {
			$repSetInfo = $reportCtrler->getUserReportSettings($userId);
			if (empty(SP_AI_INSIGHTS_EMAIL_NOTIFICATION) || empty($repSetInfo['ai_insights_email_notification'])) {
				continue;
			}
			$userInfo = $userCtrler->__getUserInfo($userId);
			if (empty($userInfo['email'])) {
				continue;
			}
			try {
				$recCtrler->sendAIInsightsDigestEmail($userInfo, $byWebsite);
			} catch (Throwable $e) {
				error_log("SEO Panel: sendAIInsightsDigestEmail() failed for user $userId: " . $e->getMessage());
				continue; // one user's email failing must not affect the rest
			}
		}

		$infoCtrler->updateTodayInformation('done', 'ai_insights_refresh');
	}

	/*
	 * Scheduled AI Perception tracking - only iterates websites that
	 * actually have at least one active tracked prompt (presence of a
	 * prompt IS the opt-in, no separate flag to check here). Each
	 * website's own refreshTrackingForWebsite() call internally gates
	 * every (prompt, provider) pair to at most once per
	 * AiPerceptionController::TRACKING_INTERVAL_DAYS, so running this
	 * every cron pass is safe - most calls no-op immediately on the date
	 * check, long before any network request would be made.
	 */
	function refreshAllLlmPerceptionTracking() {
		include_once(SP_CTRLPATH . "/aiperception.ctrl.php");
		$aipCtrler = new AiPerceptionController();

		$websiteIds = $this->db->select("SELECT DISTINCT website_id FROM llm_perception_prompts WHERE status=1");
		foreach ($websiteIds as $row) {
			try {
				$aipCtrler->refreshTrackingForWebsite(intval($row['website_id']));
			} catch (Throwable $e) {
				error_log("SEO Panel: refreshAllLlmPerceptionTracking() failed for website {$row['website_id']}: " . $e->getMessage());
				continue; // one website's tracking failing must not affect the rest
			}
		}
	}

	// func to show debug messages
	function debugMsg($msg='') {
		if($this->debug == true) print $msg;
	}
	
}
?>