<?php

/**
 * @file pages/rtadmin/RTContextHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.rtadmin
 * @class RTContextHandler
 *
 * Handle Reading Tools administration requests -- contexts section.
 *
 */

// $Id$


import('rt.harvester2.HarvesterRTAdmin');

class RTContextHandler extends RTAdminHandler {
	function createContext($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$save = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);

		import('rt.harvester2.form.ContextForm');
		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$contextForm =& new ContextForm(null, $versionId, $archiveId);

		if ($save === 'save') {
			$contextForm->readInputData();
			$contextForm->execute();
			Request::redirect(null, 'contexts', array($archiveId, $versionId));
		} else {
			RTAdminHandler::setupTemplate(true, $archiveId, $version);
			$contextForm->display();
		}
	}

	function contexts($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');
		$rangeInfo = PKPHandler::getRangeInfo('contexts');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);

		if ($version) {
			RTAdminHandler::setupTemplate(true, $archiveId, $version);

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('archiveId', $archiveId);
			$templateMgr->assign_by_ref('version', $version);

			$templateMgr->assign_by_ref('contexts', new ArrayItemIterator($version->getContexts(), $rangeInfo->getPage(), $rangeInfo->getCount()));

			$templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.contexts');
			$templateMgr->display('rtadmin/contexts.tpl');
		}
		else Request::redirect(null, 'versions');
	}

	function editContext($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
			import('rt.harvester2.form.ContextForm');
			RTAdminHandler::setupTemplate(true, $archiveId, $version, $context);
			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$contextForm =& new ContextForm($contextId, $versionId, $archiveId);
			$contextForm->initData();
			$contextForm->display();
		}
		else Request::redirect(null, 'contexts', array($archiveId, $versionId));


	}

	function deleteContext($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
			$rtDao->deleteContext($contextId, $versionId);
		}

		Request::redirect(null, 'contexts', array($archiveId, $versionId));
	}

	function saveContext($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
			import('rt.harvester2.form.ContextForm');
			// FIXME: Need construction by reference or validation always fails on PHP 4.x
			$contextForm =& new ContextForm($contextId, $versionId, $archiveId);
			$contextForm->readInputData();
			$contextForm->execute();
		}

		Request::redirect(null, 'contexts', array($archiveId, $versionId));
	}

	function moveContext($args) {
		RTAdminHandler::validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
			$isDown = Request::getUserVar('dir')=='d';
			$context->setOrder($context->getOrder()+($isDown?1.5:-1.5));
			$rtDao->updateContext($context);
			$rtDao->resequenceContexts($version->getVersionId());
		}

		Request::redirect(null, 'contexts', array($archiveId, $versionId));
	}
}

?>
