<?php

/**
 * @file ZendSearchHandler.inc.php
 *
 * Copyright (c) 2005-2008 Alec Smecher and John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SendSearchHandler
 * @ingroup plugins_generic_zendSearch
 *
 * @brief Handle requests for search functions
 */

// $Id$


import('core.Handler');

class ZendSearchHandler extends Handler {
	/**
	 * Display search page.
	 */
	function searchResults() {
		ZendSearchHandler::setupTemplate();
		$plugin =& PluginRegistry::getPlugin('generic', 'ZendSearchPlugin');
		$index =& $plugin->getIndex();

		$q = Request::getUserVar('q');

		$resultsArray = $index->find($q);
		$rangeInfo =& Handler::getRangeInfo('results');

		import('core.ArrayItemIterator');
		$resultsIterator =& ArrayItemIterator::fromRangeInfo($resultsArray, $rangeInfo);
		unset($resultsArray);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('recordDao', DAORegistry::getDAO('RecordDAO'));
		$templateMgr->assign_by_ref('results', $resultsIterator);
		$templateMgr->assign_by_ref('q', $q);
		$templateMgr->display($plugin->getTemplatePath() . 'results.tpl');
	}

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::validate();

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('pageHierachy', array(array(Request::url(null, 'theses'), 'navigation.search')));
	}
}

?>
