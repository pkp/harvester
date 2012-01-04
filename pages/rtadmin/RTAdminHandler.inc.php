<?php

/**
 * @file pages/rtadmin/RTAdminHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.rtadmin
 * @class RTAdminHandler
 *
 * Handle Reading Tools administration requests.
 *
 */

// $Id$


import('classes.rt.harvester2.HarvesterRTAdmin');
import('classes.handler.Handler');

class RTAdminHandler extends Handler {

	/**
	 * Display the index page for RT administration tasks.
	 */
	function index($args = array()) {
		$this->validate();
		$templateMgr =& TemplateManager::getManager();

		$archiveId = (int) array_shift($args);
		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');

		$this->setupTemplate(false, $archiveId);

		if ($archive =& $archiveDao->getArchive($archiveId, false) || $archiveId == 0) {
			$site =& Request::getSite();
			$rtDao =& DAORegistry::getDAO('RTDAO');

			$version = $rtDao->getVersion(
				$archive?$archive->getSetting('rtVersionId'):$site->getSetting('rtVersionId'),
				$archive?$archive->getArchiveId():0
			);

			// Display the administration menu for this archive.
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('version', $version);
			$templateMgr->assign_by_ref('archiveId', $archiveId);
			$templateMgr->assign_by_ref('versions', $rtDao->getVersions($archive?$archive->getArchiveId():null));

			$templateMgr->display('rtadmin/index.tpl');
		} else {
			// List archives for the user administer.
			$rangeInfo = PKPHandler::getRangeInfo('archives');
			$archives =& $archiveDao->getArchives(false, $rangeInfo);
			$templateMgr->assign_by_ref('archives', $archives);
			$templateMgr->display('rtadmin/archives.tpl');

		}
	}

	/**
	 * Save the selected choice of version.
	 */
	function selectVersion($args) {
		$this->validate();
		$archiveId = (int) array_shift($args);
		$versionId = Request::getUserVar('versionId');

		$archiveDao =& DAORegistry::getDAO('ArchiveDAO');
		$archive =& $archiveDao->getArchive($archiveId, false);

		$rtDao =& DAORegistry::getDAO('RTDAO');
		$version = $rtDao->getVersion($versionId, $archive?$archive->getArchiveId():null);

		if ($archive) {
			$archive->updateSetting('rtVersionId', $version?$version->getVersionId():null);
		} else {
			$site =& Request::getSite();
			$site->updateSetting('rtVersionId', $version?$version->getVersionId():null);
		}
		Request::redirect('rtadmin', 'index', $archiveId);
	}

	/**
	 * Ensure that this page is available to the user.
	 */
	function validate() {
		$this->addCheck(new HandlerValidatorRoles(&$this, true, null, null, array(ROLE_ID_SITE_ADMIN)));		
		parent::validate();
	}


	//
	// General
	//

	function settings() {
		import('lib.pkp.pages.rtadmin.RTSetupHandler');
		RTSetupHandler::settings();
	}

	function saveSettings() {
		import('lib.pkp.pages.rtadmin.RTSetupHandler');
		RTSetupHandler::saveSettings();
	}

	function validateUrls($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$versionId = (int) array_shift($args);
		$archiveId = (int) array_shift($args);

		$version = $rtDao->getVersion($versionId, $archiveId);

		if ($version) {
			// Validate the URLs for a single version
			$versions = array(&$version);
			import('lib.pkp.classes.core.ArrayItemIterator');
			$versions = new ArrayItemIterator($versions, 1, 1);
		} else {
			// Validate all URLs for this archive
			$versions = $rtDao->getVersions($archiveId);
		}

		$this->setupTemplate(true, $archiveId, $version);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->register_modifier('validate_url', 'smarty_rtadmin_validate_url');
		$templateMgr->assign_by_ref('versions', $versions);
		$templateMgr->display('rtadmin/validate.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 * @param $version object The current version, if applicable
	 * @param $context object The current context, if applicable
	 * @param $search object The current search, if applicable
	 */
	function setupTemplate($subclass = false, $archiveId = 0, $version = null, $context = null, $search = null) {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();

		$pageHierarchy = array(array(Request::url('admin'), 'admin.siteAdmin'));

		if ($subclass) $pageHierarchy[] = array(Request::url('rtadmin'), 'admin.rtAdmin');

		if ($version) {
			$pageHierarchy[] = array(Request::url('rtadmin', 'versions', array($archiveId)), 'rt.versions');
			$pageHierarchy[] = array(Request::url('rtadmin', 'editVersion', array($archiveId, $version->getVersionId())), $version->getTitle(), true);
			if ($context) {
				$pageHierarchy[] = array(Request::url('rtadmin', 'contexts', array($archiveId, $version->getVersionId())), 'rt.contexts');
				$pageHierarchy[] = array(Request::url('rtadmin', 'editContext', array($archiveId, $version->getVersionId(), $context->getContextId())), $context->getAbbrev(), true);
				if ($search) {
					$pageHierarchy[] = array(Request::url('rtadmin', 'searches', array($archiveId, $version->getVersionId(), $context->getContextId())), 'rt.searches');
					$pageHierarchy[] = array(Request::url('rtadmin', 'editSearch', array($archiveId, $version->getVersionId(), $context->getContextId(), $search->getSearchId())), $search->getTitle(), true);
				}
			}
		}
		$templateMgr->assign('pageHierarchy', $pageHierarchy);
	}
}

function rtadmin_validate_url($url, $useGet = false, $redirectsAllowed = 5) {
	$data = parse_url($url);
	if(!isset($data['host'])) {
		return false;
	}

	$fp = @ fsockopen($data['host'], isset($data['port']) && !empty($data['port']) ? $data['port'] : 80, $errno, $errstr, 10);
	if (!$fp) {
		return false;
	}

	$req = sprintf("%s %s HTTP/1.0\r\nHost: %s\r\nUser-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4b) Gecko/20030516\r\n\r\n", ($useGet ? 'GET' : 'HEAD'), (isset($data['path']) && $data['path'] !== '' ? $data['path'] : '/') .  (isset($data['query']) && $data['query'] !== '' ? '?' .  $data['query'] : ''), $data['host']);

	fputs($fp, $req);

	for($res = '', $time = time(); !feof($fp) && $time >= time() - 15; ) {
		$res .= fgets($fp, 128);
	}

	fclose($fp);

	// Check result for HTTP status code.
	if(!preg_match('!^HTTP/(\d\.?\d*) (\d+)\s*(.+)[\n\r]!m', $res, $matches)) {
		return false;
	}
	list($match, $http_version, $http_status_no, $http_status_str) = $matches;

	// If HTTP status code 2XX (Success)
	if(preg_match('!^2\d\d$!', $http_status_no)) return true;

	// If HTTP status code 3XX (Moved)
	if(preg_match('!^(?:(?:Location)|(?:URI)|(?:location)): ([^\s]+)[\r\n]!m', $res, $matches)) {
		// Recursively validate the URL if an additional redirect is allowed..
		if ($redirectsAllowed >= 1) return rtadmin_validate_url(preg_match('!^https?://!', $matches[1]) ? $matches[1] : $data['scheme'] . '://' . $data['host'] . ($data['path'] !== '' && strpos($matches[1], '/') !== 0  ? $data['path'] : (strpos($matches[1], '/') === 0 ? '' : '/')) . $matches[1], $useGet, $redirectsAllowed-1);
		return false;
	}

	// If it's not found or there is an error condition
	if(($http_status_no == 403 || $http_status_no == 404 || $http_status_no == 405 || $http_status_no == 500 || strstr($res, 'Bad Request') || strstr($res, 'Bad HTTP Request') || trim($res) == '') && !$useGet) {
		return rtadmin_validate_url($url, true, $redirectsAllowed-1);
	}

	return false;
}

function smarty_rtadmin_validate_url ($search, $errors) {
	// Make sure any prior content is flushed to the user's browser.
	flush();
	ob_flush();

	if (!is_array($errors)) $errors = array();

	if (!rtadmin_validate_url($search->getUrl())) $errors[] = array('url' => $search->getUrl(), 'id' => $search->getSearchId());
	if ($search->getSearchUrl() && !rtadmin_validate_url($search->getSearchUrl())) $errors[] = array('url' => $search->getSearchUrl(), 'id' => $search->getSearchId());

	return $errors;
}
?>
