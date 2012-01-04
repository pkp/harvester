<?php

/**
 * @file pages/rtadmin/RTSearchHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.rtadmin
 * @class RTSearchHandler
 *
 * Handle Reading Tools administration requests -- contexts section.
 *
 */

// $Id$


import('classes.rt.harvester2.HarvesterRTAdmin');
import('pages.rtadmin.RTAdminHandler');

class RTSearchHandler extends RTAdminHandler {
	function createSearch($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);
		$save = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		import('classes.rt.harvester2.form.SearchForm');
		$searchForm = new SearchForm(null, $contextId, $versionId, $archiveId);

		if ($save === 'save') {
			$searchForm->readInputData();
			$searchForm->execute();
			Request::redirect(null, 'searches', array($archiveId, $versionId, $contextId));
		} else {
			$this->setupTemplate(true, $archiveId, $version, $context);
			$searchForm->display();
		}
	}

	function searches($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');
		$rangeInfo = PKPHandler::getRangeInfo('searches');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		if ($context && $version && $context->getVersionId() == $version->getVersionId()) {
			$this->setupTemplate(true, $archiveId, $version, $context);

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('archiveId', $archiveId);
			$templateMgr->assign_by_ref('version', $version);
			$templateMgr->assign_by_ref('context', $context);
			import('lib.pkp.classes.core.ArrayItemIterator');
			$templateMgr->assign_by_ref('searches', new ArrayItemIterator($context->getSearches(), $rangeInfo->getPage(), $rangeInfo->getCount()));

			$templateMgr->display('rtadmin/searches.tpl');
		}
		else Request::redirect(null, 'versions', $archiveId);
	}

	function editSearch($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);
		$searchId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);
		$search =& $rtDao->getSearch($searchId);

		if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
			import('classes.rt.harvester2.form.SearchForm');
			$this->setupTemplate(true, $archiveId, $version, $context, $search);
			$searchForm = new SearchForm($searchId, $contextId, $versionId, $archiveId);
			$searchForm->initData();
			$searchForm->display();
		}
		else Request::redirect(null, 'searches', array($archiveId, $versionId, $contextId));
	}

	function deleteSearch($args) {
		$this->validate();

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
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);
		$searchId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);
		$search =& $rtDao->getSearch($searchId);

		if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
			import('classes.rt.harvester2.form.SearchForm');
			$searchForm = new SearchForm($searchId, $contextId, $versionId, $archiveId);
			$searchForm->readInputData();
			$searchForm->execute();
		}

		Request::redirect(null, 'searches', array($archiveId, $versionId, $contextId));
	}

	function moveSearch($args) {
		$this->validate();

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
