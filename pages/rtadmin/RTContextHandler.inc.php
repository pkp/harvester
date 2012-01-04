<?php

/**
 * @file pages/rtadmin/RTContextHandler.inc.php
 *
 * Copyright (c) 2005-2012 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package pages.rtadmin
 * @class RTContextHandler
 *
 * Handle Reading Tools administration requests -- contexts section.
 *
 */

// $Id$


import('classes.rt.harvester2.HarvesterRTAdmin');
import('pages.rtadmin.RTAdminHandler');

class RTContextHandler extends RTAdminHandler {
	function createContext($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$save = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);

		import('classes.rt.harvester2.form.ContextForm');
		$contextForm = new ContextForm(null, $versionId, $archiveId);

		if ($save === 'save') {
			$contextForm->readInputData();
			$contextForm->execute();
			Request::redirect(null, 'contexts', array($archiveId, $versionId));
		} else {
			$this->setupTemplate(true, $archiveId, $version);
			$contextForm->display();
		}
	}

	function contexts($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');
		$rangeInfo = PKPHandler::getRangeInfo('contexts');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);

		if ($version) {
			$this->setupTemplate(true, $archiveId, $version);

			$templateMgr =& TemplateManager::getManager();

			$templateMgr->assign('archiveId', $archiveId);
			$templateMgr->assign_by_ref('version', $version);

			import('lib.pkp.classes.core.ArrayItemIterator');
			$templateMgr->assign_by_ref('contexts', new ArrayItemIterator($version->getContexts(), $rangeInfo->getPage(), $rangeInfo->getCount()));

			$templateMgr->display('rtadmin/contexts.tpl');
		}
		else Request::redirect(null, 'versions');
	}

	function editContext($args) {
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
			import('classes.rt.harvester2.form.ContextForm');
			$this->setupTemplate(true, $archiveId, $version, $context);
			$contextForm = new ContextForm($contextId, $versionId, $archiveId);
			$contextForm->initData();
			$contextForm->display();
		}
		else Request::redirect(null, 'contexts', array($archiveId, $versionId));


	}

	function deleteContext($args) {
		$this->validate();

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
		$this->validate();

		$rtDao =& DAORegistry::getDAO('RTDAO');

		$archiveId = array_shift($args);
		$versionId = array_shift($args);
		$contextId = array_shift($args);

		$version =& $rtDao->getVersion($versionId, $archiveId);
		$context =& $rtDao->getContext($contextId);

		if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
			import('classes.rt.harvester2.form.ContextForm');
			$contextForm = new ContextForm($contextId, $versionId, $archiveId);
			$contextForm->readInputData();
			$contextForm->execute();
		}

		Request::redirect(null, 'contexts', array($archiveId, $versionId));
	}

	function moveContext($args) {
		$this->validate();

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
