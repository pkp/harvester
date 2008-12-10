<?php

/**
 * @file pages/rtadmin/RTSearchHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.rtadmin
 * @class RTSearchHandler
 *
 * Handle Reading Tools administration requests -- contexts section.
 *
 */

// $Id$


import('rt.harvester2.HarvesterRTAdmin');

class RTSearchHandler extends RTAdminHandler {
	function createSearch($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);
		$save = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		import('rt.harvester2.form.SearchForm');
		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$searchForm =& new SearchForm(null, $contextId, $versionId, $archiveId);

		if ($save === 'save') {
			$searchForm->readInputData();
			$searchForm->execute();
			Request::redirect(null, 'searches', array($archiveId, $versionId, $contextId));
		} else {
			RTAdminHandler::setupTemplate(true, $archiveId, $version, $context);
			$searchForm->display();
		}
	}

	function searches($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');
		$rangeInfo = PKPHandler::getRangeInfo('searches');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		if ($context && $version && $context->getVersionId() == $version->getVersionId()) {
			RTAdminHandler::setupTemplate(true, $archiveId, $version, $context);

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('archiveId', $archiveId);
			$templateMgr->assign_by_ref('version', $version);
			$templateMgr->assign_by_ref('context', $context);
			$templateMgr->assign_by_ref('searches', new ArrayItemIterator($context->getSearches(), $rangeInfo->getPage(), $rangeInfo->getCount()));

			$templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.contexts');
			$templateMgr->display('rtadmin/searches.tpl');
		}
		else Request::redirect(null, 'versions', $archiveId);
	}

	function editSearch($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);
		$searchId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);
		$search =& $rtDao->getSearch($searchId);

		if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
			import('rt.harvester2.form.SearchForm');
			RTAdminHandler::setupTemplate(true, $archiveId, $version, $context, $search);
			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$searchForm =& new SearchForm($searchId, $contextId, $versionId, $archiveId);
			$searchForm->initData();
			$searchForm->display();
		}
		else Request::redirect(null, 'searches', array($archiveId, $versionId, $contextId));
	}

	function deleteSearch($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);
		$searchId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);
		$search =& $rtDao->getSearch($searchId);

		if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
			$rtDao->deleteSearch($searchId, $contextId);
		}

		Request::redirect(null, 'searches', array($archiveId, $versionId, $contextId));
	}

	function saveSearch($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);
		$searchId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);
		$search =& $rtDao->getSearch($searchId);

		if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
			import('rt.harvester2.form.SearchForm');
			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$searchForm =& new SearchForm($searchId, $contextId, $versionId, $archiveId);
			$searchForm->readInputData();
			$searchForm->execute();
		}

		Request::redirect(null, 'searches', array($archiveId, $versionId, $contextId));
	}

	function moveSearch($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);
		$searchId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);
		$search =& $rtDao->getSearch($searchId);

		if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
			$isDown = Request::getUserVar('dir')=='d';
			$search->setOrder($search->getOrder()+($isDown?1.5:-1.5));
			$rtDao->updateSearch($search);
			$rtDao->resequenceSearches($context->getContextId());
		}

		Request::redirect(null, 'searches', array($archiveId, $versionId, $contextId));
	}
}

?>
